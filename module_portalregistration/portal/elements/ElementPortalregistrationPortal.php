<?php
/*"******************************************************************************************************
*   (c) 2004-2006 by MulchProductions, www.mulchprod.de                                                 *
*   (c) 2007-2015 by Kajona, www.kajona.de                                                              *
*       Published under the GNU LGPL v2.1, see /system/licence_lgpl.txt                                 *
********************************************************************************************************/

namespace Kajona\Portalregistration\Portal\Elements;

use class_email_validator;
use class_link;
use class_mail;
use class_module_system_setting;
use class_module_user_group;
use class_module_user_user;
use class_scriptlet_helper;
use class_text_validator;
use Kajona\Pages\Portal\ElementPortal;
use Kajona\Pages\Portal\PortalElementInterface;


/**
 * Portal Element to allow users to register themself
 *
 * @author sidler@mulchprod.de
 *
 * @targetTable element_preg.content_id
 */
class ElementPortalregistrationPortal extends ElementPortal implements PortalElementInterface {

       /**
     * Checks what to do and invokes the proper method
     *
     * @return string the prepared html-output
     */
    public function loadData() {
        $strReturn = "";

        if(!$this->objSession->isLoggedin()) {
            if($this->getParam("action") == "portalCompleteRegistration") {
                $strReturn .= $this->completeRegistration();
            }
            else {
                $strReturn = $this->editUserData();
            }
        }
        else {
            $strReturn = $this->getLang("pr_errorLoggedin");
        }

        return $strReturn;
    }


    /**
     * Completes the registration process of a new user by activating the account
     *
     * @return string
     */
    private function completeRegistration() {
        $strReturn = "";

        if($this->getSystemid() != "") {
            $objUser = new class_module_user_user($this->getParam("systemid"));

            if($objUser->getStrEmail() != "") {
                if($objUser->getIntActive() == 0 && $objUser->getIntLogins() == 0 && $objUser->getStrAuthcode() == $this->getParam("authcode") && $objUser->getStrAuthcode() != "") {
                    $objUser->setIntActive(1);
                    $objUser->setStrAuthcode("");
                    if($objUser->updateObjectToDb()) {
                        $strReturn .= $this->getLang("pr_completionSuccess");
                        if($this->arrElementData["portalregistration_success"] != "") {
                            $this->portalReload(class_link::getLinkPortalHref($this->arrElementData["portalregistration_success"]));
                        }
                    }
                }
                else {
                    $strReturn .= $this->getLang("pr_completionErrorStatus");
                }
            }
            else {
                $strReturn .= $this->getLang("pr_completionErrorStatus");
            }
        }

        return $strReturn;
    }

    /**
     * Creates a form to collect a users data
     *
     * @return string
     */
    private function editUserData() {

        $arrErrors = array();
        $bitForm = true;
        //what to do?
        if($this->getParam("submitUserForm") != "") {

            $objTextValidator = new class_text_validator();
            $objEmailValidator = new class_email_validator();

            if($this->getParam("password") == "" || $this->getParam("password") != $this->getParam("password2")) {
                $arrErrors[] = $this->getLang("pr_passwordsUnequal");
            }

            if(!$objTextValidator->validate($this->getParam("username"))) {
                $arrErrors[] = $this->getLang("pr_noUsername");
            }

            //username already existing?
            if($objTextValidator->validate($this->getParam("username")) && count(class_module_user_user::getAllUsersByName($this->getParam("username"))) > 0) {
                $arrErrors[] = $this->getLang("pr_usernameGiven");
            }

            if(!$objEmailValidator->validate($this->getParam("email"))) {
                $arrErrors[] = $this->getLang("pr_invalidEmailadress");
            }

            //Check captachcode
            if($this->getParam("form_captcha") == "" || $this->getParam("form_captcha") != $this->objSession->getCaptchaCode()) {
                $arrErrors[] = $this->getLang("pr_captcha");
            }

            if(count($arrErrors) == 0) {
                $bitForm = false;
            }
        }

        if($bitForm) {
            $strTemplateID = $this->objTemplate->readTemplate("/module_portalregistration/" . $this->arrElementData["portalregistration_template"], "portalregistration_userdataform");
            $arrTemplate = array();


            $arrTemplate["username"] = $this->getParam("username");
            $arrTemplate["email"] = $this->getParam("email");
            $arrTemplate["forename"] = $this->getParam("forename");
            $arrTemplate["name"] = $this->getParam("name");
            $arrTemplate["formaction"] = class_link::getLinkPortalHref($this->getPagename(), "", "portalCreateAccount");

            $arrTemplate["formErrors"] = "";
            if(count($arrErrors) > 0) {
                foreach($arrErrors as $strOneError) {
                    $strErrTemplate = $this->objTemplate->readTemplate("/module_portalregistration/" . $this->arrElementData["portalregistration_template"], "errorRow");
                    $arrTemplate["formErrors"] .= "" . $this->fillTemplate(array("error" => $strOneError), $strErrTemplate);
                }
            }

            return $this->fillTemplate($arrTemplate, $strTemplateID);
        }
        else {
            //create new user, inactive
            $objUser = new class_module_user_user();
            $objUser->setStrUsername($this->getParam("username"));
            $objUser->setIntActive(0);
            $objUser->setIntAdmin(0);
            $objUser->setIntPortal(1);
            $objUser->setStrSubsystem("kajona");
            $strAuthcode = generateSystemid();
            $objUser->setStrAuthcode($strAuthcode);

            if($objUser->updateObjectToDb()) {

                $objSourceuser = $objUser->getObjSourceUser();
                $objSourceuser->setStrEmail($this->getParam("email"));
                $objSourceuser->setStrForename($this->getParam("forename"));
                $objSourceuser->setStrName($this->getParam("name"));
                $objSourceuser->setStrPass($this->getParam("password"));
                $objSourceuser->updateObjectToDb();

                //group assignments
                $objGroup = new class_module_user_group($this->arrElementData["portalregistration_group"]);
                $objGroup->getObjSourceGroup()->addMember($objUser->getObjSourceUser());
                //and to the guests to avoid conflicts
                $objGroup = new class_module_user_group(class_module_system_setting::getConfigValue("_guests_group_id_"));
                $objGroup->getObjSourceGroup()->addMember($objUser->getObjSourceUser());
                //create a mail to allow the user to activate itself

                $strMailContent = $this->getLang("pr_email_body");
                $strTemp = getLinkPortalHref($this->getPagename(), "", "portalCompleteRegistration", "&authcode=" . $strAuthcode, $objUser->getSystemid());
                $strMailContent .= html_entity_decode("<a href=\"" . $strTemp . "\">" . $strTemp . "</a>");
                $strMailContent .= $this->getLang("pr_email_footer");

                $objScriptlets = new class_scriptlet_helper();
                $strMailContent = $objScriptlets->processString($strMailContent);

                $objMail = new class_mail();
                $objMail->setSubject($this->getLang("pr_email_subject"));
                $objMail->setHtml($strMailContent);
                $objMail->addTo($this->getParam("email"));
                $objMail->sendMail();
            }

            return $this->getLang("pr_register_suc");
        }
    }

}
