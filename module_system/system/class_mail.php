<?php
/*"******************************************************************************************************
*   (c) 2004-2006 by MulchProductions, www.mulchprod.de                                                 *
*   (c) 2007-2012 by Kajona, www.kajona.de                                                              *
*       Published under the GNU LGPL v2.1, see /system/licence_lgpl.txt                                 *
*-------------------------------------------------------------------------------------------------------*
*	$Id$                                               *
********************************************************************************************************/

/**
 * This class can be used to generate and send emails
 *
 * This class is able to send plaintext mails, html mails, mails with attachements and variations
 * of these. To send a mail, a call could be
 *
 * $objMail = new class_mail();
 * $objMail->setSender("test@kajona.de");
 * $objMail->setSenderName("Kajona System");
 * $objMail->addTo("sidler@localhost");
 * $objMail->setSubject("Kajona test mail");
 * $objMail->setText("This is the plain text");
 * $objMail->setHtml("This is<br />the <b>html-content</b><br /><img src=\"cid:kajona_poweredby.png\" />");
 * $objMail->addAttachement("/portal/pics/kajona/login_logo.gif");
 * $objMail->addAttachement("/portal/pics/kajona/kajona_poweredby.png", "", true);
 * $objMail->sendMail();
 *
 * The subject and the receipients name are encoded by a chunked utf-8 byte string.
 * If your system runs on php >= 5.3, all text-based content will be encoded by quoted printables.
 *
 *
 * @package module_system
 * @author sidler@mulchprod.de
 */
class class_mail {


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
		$this->strText = html_entity_decode($strText);
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
	 * If no mimetype is given, the system tries to lookup the mimetype itself.
	 * Use $bitInline if the attachment shouldn't appear in the list of attachments in the mail client.
	 * Inline-attachments can be used in html-emails like <img src="cid:your-filename.jpg" />
	 *
	 * @param string $strFilename
	 * @param string $strContentType
	 * @param bool $bitInline
	 * @return bool
	 */
	public function addAttachement($strFilename, $strContentType = "", $bitInline = false) {
        if(is_file(_realpath_.$strFilename)) {
            $arrTemp = array();
            $arrTemp["filename"] = _realpath_.$strFilename;
            //content-type given?
            if($strContentType == "") {
                //try to find out
                $objToolkit = new class_toolkit();
                $arrMime = $objToolkit->mimeType($strFilename);
                $arrTemp["mimetype"] = $arrMime[0];
            }

            //attach as inline-attachment?
            $arrTemp["inline"] = $bitInline;

            $this->arrFiles[] = $arrTemp;
            $this->bitFileAttached = true;
            $this->bitMultipart = true;
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
            if($this->strSender == "") {
                //try to load the current users' mail adress
                if(validateSystemid(class_carrier::getInstance()->getObjSession()->getUserID())) {
                    $objUser = new class_module_user_user(class_carrier::getInstance()->getObjSession()->getUserID());
                    if(checkEmailaddress($objUser->getStrEmail()))
                        $this->strSender = $objUser->getStrEmail();
                }

            }

            if($this->strSender == "")
                $this->strSender = _system_admin_email_;

			if($this->strSender != "") {
			    //build the from-arguments
			    if($this->strSenderName != "")
                    $strFrom = $this->encodeText($this->strSenderName)." <".$this->strSender.">";
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
                $strBody .= "Content-Type: text/plain; charset=UTF-8".$this->strEndOfLine;

                $strText = strip_tags(($this->strText == "" ? str_replace(array("<br />", "<br />"), array("\n", "\n"), $this->strHtml) : $this->strText));
                if(function_exists("quoted_printable_encode")) {
                    $strBody .= "Content-Transfer-Encoding: quoted-printable".$this->strEndOfLine.$this->strEndOfLine;
                    $strBody .= quoted_printable_encode($strText);
                }
                else {
                    $strBody .= "Content-Transfer-Encoding: 8bit".$this->strEndOfLine.$this->strEndOfLine;
                    $strBody .= $strText;
                }

                $strBody .= $this->strEndOfLine.$this->strEndOfLine;


                //html-version
                if($this->strHtml != "") {
                    $strBody .= "--".$strBoundaryAlt.$this->strEndOfLine;
                    $strBody .= "Content-Type: text/html; charset=UTF-8".$this->strEndOfLine;
                    $strBody .= "Content-Transfer-Encoding: 8bit".$this->strEndOfLine.$this->strEndOfLine;
                    $strBody .= $this->strHtml;
                    $strBody .= $this->strEndOfLine.$this->strEndOfLine;
                }

                if($this->bitFileAttached)
                    $strBody .= "--".$strBoundaryAlt."--".$this->strEndOfLine.$this->strEndOfLine;
            }
            else {
                $this->arrHeader[] = "Content-Type: text/plain; charset=UTF-8".$this->strEndOfLine;

                if(function_exists("quoted_printable_encode")) {
                    $this->arrHeader[] = "Content-Transfer-Encoding: quoted-printable".$this->strEndOfLine;
                    $strBody .= quoted_printable_encode($this->strText);
                }
                else {
                    $strBody .= $this->strText;;
                }
            }



            //any files to place in the mail body?
            if($this->bitFileAttached) {
                foreach($this->arrFiles as $arrOneFile) {
                    $strFileContents = chunk_split(base64_encode(file_get_contents($arrOneFile["filename"])));
                    //place file in mailbody
                    $strBody .= "--".$strBoundary.$this->strEndOfLine;
                    $strBody .= "Content-Type: ".$arrOneFile["mimetype"]."; name=\"".basename($arrOneFile["filename"])."\"".$this->strEndOfLine;
                    $strBody .= "Content-Transfer-Encoding: base64".$this->strEndOfLine;
                    if ($arrOneFile["inline"] === true) {
                        $strBody .= "Content-Disposition: inline; filename=\"".basename($arrOneFile["filename"])."\"".$this->strEndOfLine;
                        $strBody .= "Content-ID: <".basename($arrOneFile["filename"]).">".$this->strEndOfLine.$this->strEndOfLine;
                    } else {
                        $strBody .= "Content-Disposition: attachment; filename=\"".basename($arrOneFile["filename"])."\"".$this->strEndOfLine.$this->strEndOfLine;
                    }
                    $strBody .= $strFileContents;
                    $strBody .= $this->strEndOfLine.$this->strEndOfLine;
                }
            }

            //finish mail
            if($this->bitFileAttached || $this->bitMultipart)
                $strBody .= "--".$strBoundary."--".$this->strEndOfLine.$this->strEndOfLine;

			//send mail
            // in some cases, the optional param "-f test@kajona.de" may be added as mail()s' 5th param
			class_logger::getInstance()->addLogRow("sent mail to ".$strTo, class_logger::$levelInfo);
            $bitReturn = mail(
                $strTo,
                $this->encodeText($this->strSubject),
                $strBody,
                implode("", $this->arrHeader)
            );

		}

		return $bitReturn;
	}


    /**
     * Encodes some text to be places as encoded, chunked text-stream.
     * All input must be encoded in UTF-8
     * @param $strText
     * @return string
     * @see http://www.php.net/manual/en/function.mail.php#27997, credits got to gordon at kanazawa-gu dot ac dot jp
     */
    private function encodeText($strText) {

        if(function_exists("mb_encode_mimeheader")) {
            return mb_encode_mimeheader($strText, "UTF-8", "Q", "\r\n", strlen("subject: "));
        }

        $strStart = "=?UTF-8?B?";
        $strEnd = "?=";
        $strSpacer = $strEnd."\r\n ".$strStart;
        $intLength = 74 - strlen($strStart) - strlen($strEnd) - strlen("subject: ");
        $intLength = $intLength - ($intLength % 4);


        $strText = chunk_split(base64_encode($strText), $intLength, $strSpacer);
        $strText = $strStart . $strText . $strEnd;

        return $strText;
    }


}

