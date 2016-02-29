<?php
/*"******************************************************************************************************
*   (c) 2004-2006 by MulchProductions, www.mulchprod.de                                                 *
*   (c) 2007-2015 by Kajona, www.kajona.de                                                              *
*       Published under the GNU LGPL v2.1, see /system/licence_lgpl.txt                                 *
*-------------------------------------------------------------------------------------------------------*
*	$Id$                                  *
********************************************************************************************************/


namespace Kajona\Formular\Portal\Forms;

use Kajona\System\Portal\PortalController;
use Kajona\System\Portal\PortalInterface;
use Kajona\System\System\Link;
use Kajona\System\System\Mail;
use Kajona\System\System\ScriptletHelper;
use Kajona\System\System\ScriptletInterface;
use Kajona\System\System\Validators\EmailValidator;
use Kajona\System\System\Validators\TextValidator;

/**
 * Portal-Class to provide a simple contact-form
 *
 * @author sidler@mulchprod.de
 *
 * @module elements
 * @moduleId _formular_module_id_
 */
class FormularContact extends PortalController implements PortalInterface
{
    private $arrError = array();


    /**
     * Constructor
     *
     * @param mixed $arrElementData
     */
    public function __construct($arrElementData)
    {

        if (!isset($arrElementData["formular_template"]) || $arrElementData["formular_template"] == "") {
            $arrElementData["formular_template"] = "/contact.tpl";
        }

        parent::__construct($arrElementData);
    }


    /**
     * Loads the form specified
     *
     * @return string
     */
    protected function actionList()
    {
        $strReturn = "";

        $this->setParam("formaction", Link::getLinkPortalHref($this->getParam("page"), "", "sendForm"));
        $arrParams = $this->getAllParams();
        foreach ($arrParams as $strKey => $strValue) {
            $arrParams[$strKey] = htmlspecialchars($strValue, ENT_QUOTES, "UTF-8", false);
        }

        //any errors to print?
        if (count($this->arrError) > 0) {
            $strError = "";
            $arrErrorFields = array();
            //Collect errors
            $strTemplateErrorID = $this->objTemplate->readTemplate("/module_form/" . $this->arrElementData["formular_template"], "errorrow");
            foreach($this->arrError as $strKey => $strOneError) {
                $strError .= $this->fillTemplate(array("error" => $strOneError), $strTemplateErrorID);
                $arrErrorFields[] = "'{$strKey}'";
            }
            //and the complete form
            $strTemplateErrorFormid = $this->objTemplate->readTemplate("/module_form/" . $this->arrElementData["formular_template"], "errors");
            $arrParams["error_list"] = $this->fillTemplate(array("error_list" => $strError), $strTemplateErrorFormid);

            $arrParams["error_fields"] = implode(",", $arrErrorFields);

        }
        //and the form itself
        $strTemplateformId = $this->objTemplate->readTemplate("/module_form/".$this->arrElementData["formular_template"], "contactform");
        //get actions

        $strReturn .= $this->fillTemplate($arrParams, $strTemplateformId);
        return $strReturn;
    }


    /**
     * checks all entered values
     *
     * @return bool
     */
    private function validate()
    {
        $bitReturn = true;

        $objValidator = new EmailValidator();
        if (!$objValidator->validate($this->getParam("absender_email"))) {
            $bitReturn = false;
            $this->arrError["absender_email"] = $this->getLang("fehler_email");
        }

        $objValidator = new TextValidator();
        if (!$objValidator->validate($this->getParam("absender_name"))) {
            $bitReturn = false;
            $this->arrError["absender_name"] = $this->getLang("fehler_name");
        }

        if (!$objValidator->validate($this->getParam("absender_nachricht"))) {
            $bitReturn = false;
            $this->arrError["absender_nachricht"] = $this->getLang("fehler_nachricht");
        }

        //Check captachcode
        if ($this->getParam("form_captcha") != $this->objSession->getCaptchaCode()) {
            $bitReturn = false;
            $this->arrError["form_captcha"] = $this->getLang("fehler_captcha");
        }

        return $bitReturn;
    }

    /**
     * Finally sends the mail
     *
     * @return string Error or success-message
     */
    protected function actionSendForm()
    {

        if (!$this->validate()) {
            return $this->actionList();
        }

        //Mail-Object
        $objEmail = new Mail();

        //Template
        $strMailTemplateID = $this->objTemplate->readTemplate("/module_form/".$this->arrElementData["formular_template"], "email");
        $this->objTemplate->setTemplate($this->fillTemplate($this->getAllParams(), $strMailTemplateID));
        $this->objTemplate->deletePlaceholder();

        $objScriptlets = new ScriptletHelper();
        $strText = $objScriptlets->processString($this->objTemplate->getTemplate(), ScriptletInterface::BIT_CONTEXT_PORTAL_PAGE);


        $objEmail->setText($strText);
        $objEmail->addTo($this->arrElementData["formular_email"]);
        $objEmail->setSender($this->getParam("absender_email"));
        $objEmail->setSubject($this->getLang("formContact_mail_subject"));
        if ($objEmail->sendMail()) {
            if ($this->arrElementData["formular_success"] != "") {
                $strReturn = $this->arrElementData["formular_success"];
            }
            else {
                $strReturn = $this->objTemplate->fillTemplate(array(), $this->objTemplate->readTemplate("/module_form/".$this->arrElementData["formular_template"], "message_success"));
            }
        }
        else {
            if ($this->arrElementData["formular_error"] != "") {
                $strReturn = $this->arrElementData["formular_error"];
            }
            else {
                $strReturn = $this->objTemplate->fillTemplate(array(), $this->objTemplate->readTemplate("/module_form/".$this->arrElementData["formular_template"], "message_error"));
            }
        }

        return $strReturn;
    }

}
