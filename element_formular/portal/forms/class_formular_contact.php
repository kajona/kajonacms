<?php
/*"******************************************************************************************************
*   (c) 2004-2006 by MulchProductions, www.mulchprod.de                                                 *
*   (c) 2007-2015 by Kajona, www.kajona.de                                                              *
*       Published under the GNU LGPL v2.1, see /system/licence_lgpl.txt                                 *
*-------------------------------------------------------------------------------------------------------*
*	$Id$                                  *
********************************************************************************************************/

/**
 * Portal-Class to provide a simple contact-form
 *
 * @package element_formular
 * @author sidler@mulchprod.de
 *
 * @module elements
 * @moduleId _pages_elemente_modul_id_
 */
class class_formular_contact extends class_portal_controller implements interface_portal {
    private $arrError = array();


    /**
     * Constructor
     *
     * @param mixed $arrElementData
     */
    public function __construct($arrElementData) {

        if(!isset($arrElementData["formular_template"]) || $arrElementData["formular_template"] == "") {
            $arrElementData["formular_template"] = "/contact.tpl";
        }

        parent::__construct($arrElementData);
    }


    /**
     * Loads the form specified
     *
     * @return string
     */
    protected function actionList() {
        $strReturn = "";

        $this->setParam("formaction", getLinkPortalHref($this->getParam("page"), "", "sendForm"));
        $arrParams = $this->getAllParams();
        foreach($arrParams as $strKey => $strValue) {
            $arrParams[$strKey] = htmlspecialchars($strValue, ENT_QUOTES, "UTF-8", false);
        }

        //any errors to print?
        if(count($this->arrError) > 0) {
            $strError = "";
            //Collect errors
            $strTemplateErrorID = $this->objTemplate->readTemplate("/element_form/" . $this->arrElementData["formular_template"], "errorrow");
            foreach($this->arrError as $strOneError) {
                $strError .= $this->fillTemplate(array("error" => $strOneError), $strTemplateErrorID);
            }
            //and the complete form
            $strTemplateErrorFormid = $this->objTemplate->readTemplate("/element_form/" . $this->arrElementData["formular_template"], "errors");
            $arrParams["formular_fehler"] = $this->fillTemplate(array("liste_fehler" => $strError), $strTemplateErrorFormid);
        }
        //and the form itself
        $strTemplateformId = $this->objTemplate->readTemplate("/element_form/" . $this->arrElementData["formular_template"], "contactform");
        //get actions

        $strReturn .= $this->fillTemplate($arrParams, $strTemplateformId);
        return $strReturn;
    }


    /**
     * checks all entered values
     *
     * @return bool
     */
    private function validate() {
        $bitReturn = true;

        $objValidator = new class_email_validator();
        if(!$objValidator->validate($this->getParam("absender_email"))) {
            $bitReturn = false;
            $this->arrError[] = $this->getLang("fehler_email");
        }

        $objValidator = new class_text_validator();
        if(!$objValidator->validate($this->getParam("absender_name"))) {
            $bitReturn = false;
            $this->arrError[] = $this->getLang("fehler_name");
        }

        if(!$objValidator->validate($this->getParam("absender_nachricht"))) {
            $bitReturn = false;
            $this->arrError[] = $this->getLang("fehler_nachricht");
        }

        //Check captachcode
        if($this->getParam("form_captcha") != $this->objSession->getCaptchaCode()) {
            $bitReturn = false;
            $this->arrError[] = $this->getLang("fehler_captcha");
        }

        return $bitReturn;
    }

    /**
     * Finally sends the mail
     *
     * @return string Error or success-message
     */
    protected function actionSendForm() {

        if(!$this->validate())
            return $this->actionList();

        //Mail-Object
        $objEmail = new class_mail();

        //Template
        $strMailTemplateID = $this->objTemplate->readTemplate("/element_form/" . $this->arrElementData["formular_template"], "email");
        $this->objTemplate->setTemplate($this->fillTemplate($this->getAllParams(), $strMailTemplateID));
        $this->objTemplate->deletePlaceholder();

        $objScriptlets = new class_scriptlet_helper();
        $strText = $objScriptlets->processString($this->objTemplate->getTemplate(), interface_scriptlet::BIT_CONTEXT_PORTAL_PAGE);


        $objEmail->setText($strText);
        $objEmail->addTo($this->arrElementData["formular_email"]);
        $objEmail->setSender($this->getParam("absender_email"));
        $objEmail->setSubject($this->getLang("formContact_mail_subject"));
        if($objEmail->sendMail()) {
            if($this->arrElementData["formular_success"] != "")
                $strReturn = $this->arrElementData["formular_success"];
            else
                $strReturn = $this->objTemplate->fillTemplate(array(), $this->objTemplate->readTemplate("/element_form/" . $this->arrElementData["formular_template"], "message_success"));
        }
        else {
            if($this->arrElementData["formular_error"] != "")
                $strReturn = $this->arrElementData["formular_error"];
            else
                $strReturn = $this->objTemplate->fillTemplate(array(), $this->objTemplate->readTemplate("/element_form/" . $this->arrElementData["formular_template"], "message_error"));
        }

        return $strReturn;
    }

}
