<?php
/*"******************************************************************************************************
*   (c) 2004-2006 by MulchProductions, www.mulchprod.de                                                 *
*   (c) 2007 by Kajona, www.kajona.de                                                                   *
*       Published under the GNU LGPL v2.1, see /system/licence_lgpl.txt                                 *
*-------------------------------------------------------------------------------------------------------*
* 																										*
* 	class_element_tellafriend.php																		*
* 	Portal-class of the tellafriend element														    	*
*-------------------------------------------------------------------------------------------------------*
*	$Id$                              *
********************************************************************************************************/

//base-class
require_once(_portalpath_."/class_elemente_portal.php");
//Interface
require_once(_portalpath_."/interface_portal_element.php");

/**
 * Loads the last-modified date of the current page and prepares it for output
 *
 * @package modul_pages
 */
class class_element_tellafriend extends class_element_portal implements interface_portal_element {

    private $arrError = array();

	/**
	 * Constructor
	 *
	 * @param mixed $arrElementData
	 */
	public function __construct($objElementData) {
		$arrModule["name"] 			= "element_tellafriend";
		$arrModule["author"] 		= "sidler@mulchprod.de";
		$arrModule["moduleId"] 		= _pages_elemente_modul_id_;
		$arrModule["table"]		    = _dbprefix_."element_tellafriend";
		$arrModule["modul"]		    = "elemente";

		parent::__construct($arrModule, $objElementData);
	}

    /**
     * Looks up the last modified-date of the current page
     *
     * @return string the prepared html-output
     */
	public function loadData() {
		$strReturn = "";
        //display form or send an email?
        if($this->getParam("action") != "sendTellafriend") {
            $strReturn .= $this->tellafriendForm();
        }
        else {
            if(!$this->validateForm())
                $strReturn .= $this->tellafriendForm();
            else
                $this->sendForm();
        }
		return $strReturn;
	}

	/**
     * Creates a form
     *
     * @return string
     */
	private function tellafriendForm() {
	    $arrTemplate = array();
	    //any errors to print?
		if(count($this->arrError) > 0) {
			$strError = "";
			//Collect errors
			$strTemplateErrorID = $this->objTemplate->readTemplate("/element_tellafriend/".$this->arrElementData["tellafriend_template"], "errorrow");
			foreach($this->arrError as $strOneError) {
				$strError .= $this->objTemplate->fillTemplate(array("error" => $strOneError), $strTemplateErrorID);
			}
			//and the complete errorform
			$strTemplateErrorFormid = $this->objTemplate->readTemplate("/element_tellafriend/".$this->arrElementData["tellafriend_template"], "errors");
			$arrTemplate["tellafriend_errors"] =  $this->objTemplate->fillTemplate(array("liste_fehler" => $strError), $strTemplateErrorFormid);
		}

        $strTemplateID = $this->objTemplate->readTemplate("/element_tellafriend/".$this->arrElementData["tellafriend_template"], "tellafriend_form");


        $arrTemplate["sender"] = $this->getText("sender");
        $arrTemplate["sender_name"] = $this->getText("sender_name");
        $arrTemplate["tellafriend_sender"] = $this->getParam("tellafriend_sender");
        $arrTemplate["tellafriend_sender_name"] = $this->getParam("tellafriend_sender_name");
        $arrTemplate["receiver"] = $this->getText("receiver");
        $arrTemplate["receiver_name"] = $this->getText("receiver_name");
        $arrTemplate["tellafriend_receiver"] = $this->getParam("tellafriend_receiver");
        $arrTemplate["tellafriend_receiver_name"] = $this->getParam("tellafriend_receiver_name");
        $arrTemplate["message"] = $this->getText("message");
        $arrTemplate["tellafriend_message"] = $this->getParam("tellafriend_message");
        $arrTemplate["captcha"] = $this->getText("captcha");
        $arrTemplate["reload_captcha"] = $this->getText("reload_captcha");
        $arrTemplate["submit"] = $this->getText("submit");
        $arrTemplate["tellafriend_action"] = "sendTellafriend";

		$arrTemplate["action"] = _indexpath_."?page=".$this->getPagename()."";
		return $this->objTemplate->fillTemplate($arrTemplate, $strTemplateID);
	}

	/**
	 * Validates all elements sent before
	 *
	 * @return bool
	 */
	private function validateForm() {
	    $bitReturn = true;

		if(!checkEmailaddress($this->getParam("tellafriend_sender"))) {
			$bitReturn = false;
			$this->arrError[] = $this->getText("tellafriend_sender");
		}

		if(!checkEmailaddress($this->getParam("tellafriend_receiver"))) {
			$bitReturn = false;
			$this->arrError[] = $this->getText("tellafriend_receiver");
		}

		if(!checkText($this->getParam("tellafriend_sender_name"), 3)) {
		    $bitReturn = false;
		    $this->arrError[] =$this->getText("tellafriend_sender_name");
		}

		if(!checkText($this->getParam("tellafriend_receiver_name"), 3)) {
		    $bitReturn = false;
		    $this->arrError[] =$this->getText("tellafriend_receiver_name");
		}

		//Check captachcode
		if($this->getParam("form_captcha") != $this->objSession->getCaptchaCode()) {
			$bitReturn = false;
			$this->arrError[] = $this->getText("fehler_captcha");
		}

		return $bitReturn;
	}


	/**
	 * Creates an email to send to a friend
	 *
	 */
	private function sendForm() {
	    //load url the user visited before
	    $strUrl = $this->getHistory(2);
	    $arrUrl = explode("&", $strUrl);
	    $strPage = "";
	    $strSystemid = "";
	    $strParams = "";
	    $strAction = "";
	    foreach ($arrUrl as $arrOnePart) {
	        $arrPair = explode("=", $arrOnePart);
	    	if($arrPair[0] == "page")
	    	    $strPage = $arrPair[1];
	    	else if($arrPair[0] == "sytemid")
	    	    $strSystemid = $arrPair[1];
	    	else if($arrPair[0] == "action")
	    	    $strAction= $arrPair[1];
	    	//everything but the language command
	    	else if($arrPair[0] != "language")
	    	    $strParams .= "&".$arrPair[0]."=".$arrPair[1];

	    }

	    $strHref = getLinkPortalRaw($strPage, "", $strAction, $strParams, $strSystemid);
	    $arrMessage = array();
	    $arrMessage["tellafriend_url"] = "<a href=\"".$strHref."\">".$strHref."</a>";
	    $arrMessage["tellafriend_receiver_name"] = $this->getParam("tellafriend_receiver_name");
	    $arrMessage["tellafriend_sender_name"] = $this->getParam("tellafriend_sender_name");
	    $arrMessage["tellafriend_message"] = $this->getParam("tellafriend_message");
	    $strMailTemplateID = $this->objTemplate->readTemplate("/element_tellafriend/".$this->arrElementData["tellafriend_template"], "email_html");
	    $strEmailBody = $this->objTemplate->fillTemplate($arrMessage, $strMailTemplateID, true);
	    $this->objTemplate->setTemplate($strEmailBody);
	    $this->objTemplate->fillConstants();
	    $strEmailBody = $this->objTemplate->getTemplate();

	    $strSubject = $this->objTemplate->fillTemplate(array(), $this->objTemplate->readTemplate("/element_tellafriend/".$this->arrElementData["tellafriend_template"], "email_subject"));

	    include_once(_systempath_."/class_mail.php");
	    $objEmail = new class_mail();
	    $objEmail->setSender($this->getParam("tellafriend_sender"));
	    $objEmail->setSenderName($this->getParam("tellafriend_sender_name"));
	    $objEmail->addTo($this->getParam("tellafriend_receiver"));
	    $objEmail->setSubject($strSubject);
	    $objEmail->setHtml($strEmailBody);

	    if($objEmail->sendMail())
	       header("Location: "._indexpath_."?page=".$this->arrElementData["tellafriend_success"]);
	    else
	       header("Location: "._indexpath_."?page=".$this->arrElementData["tellafriend_error"]);
	}
}
?>