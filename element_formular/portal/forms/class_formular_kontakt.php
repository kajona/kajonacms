<?php
/*"******************************************************************************************************
*   (c) 2004-2006 by MulchProductions, www.mulchprod.de                                                 *
*   (c) 2007-2009 by Kajona, www.kajona.de                                                              *
*       Published under the GNU LGPL v2.1, see /system/licence_lgpl.txt                                 *
*-------------------------------------------------------------------------------------------------------*
*	$Id$                                   *
********************************************************************************************************/
//Include der Mutter-Klasse
include_once(_portalpath_."/class_portal.php");
include_once(_portalpath_."/interface_portal.php");

/**
 * Portal-Class to provide a simple contact-form
 *
 * @package modul_pages
 */
class class_formular_kontakt extends class_portal implements interface_portal {
	private $arrError	= array();

	/**
	 * Contructor
	 *
	 * @param mixed $arrEelementData
	 */
	public function __construct($arrElementData) {
		$arrModule["name"] 				= "formular_kontakt";
		$arrModule["author"] 			= "sidler@mulchprod.de";
		$arrModule["moduleId"] 			= _pages_inhalte_modul_id_;
		$arrModule["modul"]				= "pages";
		$arrModule["template"]			= "/element_form/contact.tpl";

		//base class
		parent::__construct($arrModule, $arrElementData);
	}

	/**
	 * Action-Block to control the behaviour
	 *
	 * @return string
	 */
	public function action() {
		$strReturn = "";
		$strAction = "";

		if($this->getParam("action") != "")
			$strAction = $this->getParam("action");

		if ($strAction == "sendForm") {
			if($this->validate())
				$strReturn = $this->actionSendForm();
			else
				$strReturn = $this->actionForm();
		}
        else
            $strReturn	= $this->actionForm();

		return $strReturn;

	}

//---Aktionsfunktionen-----------------------------------------------------------------------------------

	/**
	 * Loads the form specified by the template set in the header-infos
	 *
	 * @return string
	 */
	private function actionForm() {
		$strReturn = "";

		//any errors to print?
		if(count($this->arrError) > 0) {
			$strError = "";
			//Collect errors
			$strTemplateErrorID = $this->objTemplate->readTemplate($this->arrModule["template"], "errorrow");
			foreach($this->arrError as $strOneError) {
				$strError .= $this->fillTemplate(array("error" => $strOneError), $strTemplateErrorID);
			}
			//and the complete form
			$strTemplateErrorFormid = $this->objTemplate->readTemplate($this->arrModule["template"], "errors");
			$this->setParam("formular_fehler", $this->fillTemplate(array("liste_fehler" => $strError), $strTemplateErrorFormid));
		}
		//and the form itself
		$strTemplateformId = $this->objTemplate->readTemplate($this->arrModule["template"], "contactform");
		//get actions
		$this->setParam("formaction", getLinkPortalHref($this->getParam("page"), "", "sendForm"));
		$strReturn .= $this->fillTemplate($this->getAllParams(), $strTemplateformId);
		return $strReturn;
	}


	/**
	 * checks all entered values
	 *
	 * @return unknown
	 */
	private function validate() {
		$bitReturn = true;

		if(!checkEmailaddress($this->getParam("absender_email"))) {
			$bitReturn = false;
			$this->arrError[] = $this->getText("fehler_email");
		}

		if(!checkText($this->getParam("absender_name"), 3, 80)) {
			$bitReturn = false;
			$this->arrError[] = $this->getText("fehler_name");
		}

		if(!checkText($this->getParam("absender_nachricht"), 3, 800)) {
			$bitReturn = false;
			$this->arrError[] = $this->getText("fehler_nachricht");
		}

		//Check captachcode
		if($this->getParam("form_captcha") != $this->objSession->getCaptchaCode()) {
			$bitReturn = false;
			$this->arrError[] = $this->getText("fehler_captcha");
		}

		return $bitReturn;
	}

	/**
	 * Finally sends the mail
	 *
	 * @return string Error or success-message
	 */
	private function actionSendForm() {
		$strReturn = "";
		//Mail-Object
		include_once(_systempath_."/class_mail.php");
		$objEmail = new class_mail();

		//Template
		$strMailTemplateID = $this->objTemplate->readTemplate($this->arrModule["template"], "email");
		$this->objTemplate->setTemplate($this->fillTemplate($this->getAllParams(), $strMailTemplateID));
		$this->objTemplate->deletePlaceholder();

		$objEmail->setText($this->objTemplate->getTemplate());
		$objEmail->addTo($this->arrElementData["formular_email"]);
		$objEmail->setSender($this->getParam("absender_email"));
		$objEmail->setSubject("Nachricht per Kontaktformular");
		if($objEmail->sendMail())
			$strReturn = $this->arrElementData["formular_success"];
		else
			$strReturn = $this->arrElementData["formular_error"];

		return $strReturn;
	}

}
?>