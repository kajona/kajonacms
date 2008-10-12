<?php
/*"******************************************************************************************************
*   (c) 2004-2006 by MulchProductions, www.mulchprod.de                                                 *
*   (c) 2007-2008 by Kajona, www.kajona.de                                                              *
*       Published under the GNU LGPL v2.1, see /system/licence_lgpl.txt                                 *
*-------------------------------------------------------------------------------------------------------*
* 																										*
* 	class_mail.php																						*
* 	Class handling mails																				*
*																										*
*-------------------------------------------------------------------------------------------------------*
*	$Id$                                               *
********************************************************************************************************/

/**
 * This Class can be used to generate an email and to send this email
 *
 * This class is able to send plaintext mails, html mails, mails with attachements and variations
 * of these. To send a mail, a call could be
 *
 * $objMail = new class_mail();
 * $objMail->setSender("test@kajona.de");
 * $objMail->addTo("sidler@localhost");
 * $objMail->setSubject("kajona testmail");
 * $objMail->setText("Dies ist der normale plain-text");
 * $objMail->setHtml("Dies ist der <br /> normale <b>html-content</b>");
 * $objMail->addAttachement("/portal/pics/kajona/logo.gif");
 * $objMail->addAttachement("/portal/pics/kajona/kajona_poweredby.png");
 * $objMail->sendMail();
 *
 * @package modul_system
 */
class class_mail {

	private $arrModul;

	private $arrayTo = array();
	private $arrayCc = array();
	private $arrayBcc = array();

	private $strSender = "";
	private $strSenderName = "";
	private $strSubject = "";
	private $strText = "";
	private $strHtml = "";
	private $arrHeader = array();
	private $arrFiles = array();

	private $bitMultipart = false;
	private $bitFileAttached = false;

	private $strEndOfLine = "\n";

	/**
	 * Constructor
	 *
	 */
	public function __construct() {
		$this->arrModul["name"] 		= "class_mail";
		$this->arrModul["author"] 		= "sidler@mulchprod.de";
		$this->arrModul["moduleId"]		= _system_modul_id_;
	}

	/**
	 * Adds a recipient to the to-list
	 *
	 * @param string $strMailaddress
	 */
	public function addTo($strMailaddress) {
		$this->arrayTo[] = $strMailaddress;
	}

	/**
	 * Adds a recipient to the cc-list
	 *
	 * @param string $strMailaddress
	 */
	public function addCc($strMailaddress) {
		$this->arrayCc[] = $strMailaddress;
	}

	/**
	 * Adds a recipient to the bcc-list
	 *
	 * @param string $strMailaddress
	 */
	public function addBcc($strMailaddress) {
		$this->arrayBcc[] = $strMailaddress;
	}

	/**
	 * Sets an email-header
	 *
	 * @param string $strHeader
	 */
	public function addHeader($strHeader) {
		$this->arrHeader[] = $strHeader;
	}

	/**
	 * Sets the text-content for the mail
	 *
	 * @param string $strText
	 */
	public function setText($strText) {
		$this->strText = $strText;
	}

	/**
	 * Sets the html-content for the mail
	 *
	 * @param string $strHtml
	 */
	public function setHtml($strHtml) {
	    $this->bitMultipart = true;
        $this->strHtml = $strHtml;
	}

	/**
	 * Sets the subject of the mail
	 *
	 * @param string $strSubject
	 */
	public function setSubject($strSubject) {
	    $strSubject = str_replace(array("\r", "\n"), array(" ", " "), $strSubject);
		$this->strSubject = $strSubject;
	}

	/**
	 * Sets the sender of the mail
	 *
	 * @param string $strSender
	 */
	public function setSender($strSender) {
		$this->strSender = $strSender;
	}

	/**
	 * Sets the name of the mails sender
	 *
	 * @param string $strSenderName
	 */
	public function setSenderName($strSenderName) {
        $this->strSenderName = $strSenderName;
	}

	/**
	 * Adds a file to the current mail
	 * if no mimetype is given, the system tries to lookup the mimetype itself
	 *
	 * @param string $strFilename
	 * @param string $strContentType
	 * @return bool
	 */
	public function addAttachement($strFilename, $strContentType = "") {
        if(is_file(_realpath_.$strFilename)) {
            $arrTemp = array();
            $arrTemp["filename"] = _realpath_.$strFilename;
            //content-type given?
            if($strContentType == "") {
                //try to find out
                include_once(_systempath_."/class_toolkit.php");
                $objToolkit = new class_toolkit();
                $arrMime = $objToolkit->mimeType($strFilename);
                $arrTemp["mimetype"] = $arrMime[0];
            }
            $this->arrFiles[] = $arrTemp;
            $this->bitFileAttached = true;
            return true;
        }
        else
            return false;
	}


	/**
	 * Sends, finally, the mail
	 *
	 * @return bool
	 */
	public function sendMail() {
		$bitReturn = false;

		//Do we have all neccessary arguments?
		if(count($this->arrayTo) > 0)
			$bitReturn = true;

		if($bitReturn) {
			//Building the mail
			$strTo = implode(", ", $this->arrayTo);
			//Sender
			if($this->strSender != "") {
			    //build the from-arguments
			    if($this->strSenderName != "")
			       $strFrom = $this->strSenderName." <".$this->strSender.">";
			    else
			       $strFrom = $this->strSender;

				$this->arrHeader[] = "From: ".$strFrom.$this->strEndOfLine;
				$this->arrHeader[] = "Reply-To: ".$this->strSender.$this->strEndOfLine;
			}

			//cc
			if(count($this->arrayCc) > 0)
				$this->arrHeader[] = "Cc: ".implode(", ", $this->arrayCc).$this->strEndOfLine;

			//bcc
			if(count($this->arrayBcc) > 0)
				$this->arrHeader[] = "Bcc: ".implode(", ", $this->arrayBcc).$this->strEndOfLine;


			//Kajona Headers to avoid being marked as spam
			$this->arrHeader[] = "X-Mailer: Kajona Mailer V3".$this->strEndOfLine;
			$this->arrHeader[] = "Message-ID: <".generateSystemid()."_kajona@".getServer("SERVER_NAME").">".$this->strEndOfLine;

			//header for multipartmails?
			$strBoundary = generateSystemid();

			if($this->bitMultipart || $this->bitFileAttached) {
			    $this->arrHeader[] = 'MIME-Version: 1.0'.$this->strEndOfLine;
			    //file attached?
			    if($this->bitFileAttached)
                    $this->arrHeader[] = "Content-Type: multipart/related; boundary=\"".$strBoundary."\"".$this->strEndOfLine;
                else
                    $this->arrHeader[] = "Content-Type: multipart/alternative; boundary=\"".$strBoundary."\"".$this->strEndOfLine;
			}


			//generate the mail-body
            $strBody = "";

            //multipart mail using html?
            if($this->bitMultipart) {
                //multipart encoded mail
                $strBoundaryAlt = generateSystemid();

                //if a file should attached, a splitter is needed here
                if($this->bitFileAttached) {
                    $strBody .= "--".$strBoundary.$this->strEndOfLine;
                    $strBody .= "Content-Type: multipart/alternative; boundary=\"".$strBoundaryAlt."\"".$this->strEndOfLine;
                }
                else {
                    //no new boundary-section, use old boundary instead
                    $strBoundaryAlt = $strBoundary;
                }

                //place a body for strange mail-clients
                $strBody .= "This is a multi-part message in MIME format.".$this->strEndOfLine.$this->strEndOfLine;

                //text-version
                $strBody .= "--".$strBoundaryAlt.$this->strEndOfLine;
                $strBody .= "Content-Type: text/plain; charset=\"utf-8\"".$this->strEndOfLine;
                $strBody .= "Content-Transfer-Encoding: 8bit".$this->strEndOfLine.$this->strEndOfLine;
                $strBody .= strip_tags(($this->strText == "" ? str_replace(array("<br />", "<br />"), array("\n", "\n"), $this->strHtml) : $this->strText));
                $strBody .= $this->strEndOfLine.$this->strEndOfLine;

                //html-version
                $strBody .= "--".$strBoundaryAlt.$this->strEndOfLine;
                $strBody .= "Content-Type: text/html; charset=\"utf-8\"".$this->strEndOfLine;
                $strBody .= "Content-Transfer-Encoding: 8bit".$this->strEndOfLine.$this->strEndOfLine;
                $strBody .= $this->strHtml;
                $strBody .= $this->strEndOfLine.$this->strEndOfLine;

                if($this->bitFileAttached)
                    $strBody .= "--".$strBoundaryAlt."--".$this->strEndOfLine.$this->strEndOfLine;
            }
            else
                $strBody .= $this->strText;


            //any files to place in the mail body?
            if($this->bitFileAttached) {
                foreach($this->arrFiles as $arrOneFile) {
                    $strFileContents = chunk_split(base64_encode(file_get_contents($arrOneFile["filename"])));
                    //place file in mailbody
                    $strBody .= "--".$strBoundary.$this->strEndOfLine;
                    $strBody .= "Content-Type: ".$arrOneFile["mimetype"]."; name=\"".basename($arrOneFile["filename"])."\"".$this->strEndOfLine;
                    $strBody .= "Content-Transfer-Encoding: base64".$this->strEndOfLine;
                    $strBody .= "Content-Disposition: attachment; filename=\"".basename($arrOneFile["filename"])."\"".$this->strEndOfLine.$this->strEndOfLine;
                    $strBody .= $strFileContents;
                    $strBody .= $this->strEndOfLine.$this->strEndOfLine;
                }
            }


            //finish mail
            if($this->bitFileAttached || $this->bitMultipart)
                $strBody .= "--".$strBoundary."--".$this->strEndOfLine.$this->strEndOfLine;

			//send mail
			class_logger::getInstance()->addLogRow("sent mail to ".$strTo, class_logger::$levelInfo);
			$bitReturn = mail($strTo, $this->strSubject, $strBody, implode("", $this->arrHeader));
		}

		return $bitReturn;
	}

} // class_mail

//test-code
/*
include_once("./includes.php");
class_carrier::getInstance();
$objMail = new class_mail();
$objMail->setSender("info@kajona.de");
$objMail->setSenderName("Kajona System");
$objMail->addTo("sidler@terrarium.mulchprod.intern");

$objMail->setSubject("kajona test-mail");
$objMail->setText("Email contents in text-format");
$objMail->setHtml("Email contents in <b>html</b>-format");
$objMail->addAttachement("/portal/pics/kajona/kajona_poweredby.png");

var_dump($objMail->sendMail());
*/
?>