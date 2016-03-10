<?php
/*"******************************************************************************************************
*   (c) 2004-2006 by MulchProductions, www.mulchprod.de                                                 *
*   (c) 2007-2015 by Kajona, www.kajona.de                                                              *
*       Published under the GNU LGPL v2.1, see /system/licence_lgpl.txt                                 *
********************************************************************************************************/

namespace Kajona\Portalregistration\Portal\Elements;

use Kajona\Pages\Portal\ElementPortal;
use Kajona\Pages\Portal\PortalElementInterface;
use Kajona\System\System\Link;
use Kajona\System\System\Mail;
use Kajona\System\System\ScriptletHelper;
use Kajona\System\System\SystemSetting;
use Kajona\System\System\UserGroup;
use Kajona\System\System\UserUser;
use Kajona\System\System\Validators\EmailValidator;
use Kajona\System\System\Validators\TextValidator;


/**
 * Portal Element to allow users to register themself
 *
 * @author sidler@mulchprod.de
 *
 * @targetTable element_preg.content_id
 */
class ElementPortalregistrationPortal extends ElementPortal implements PortalElementInterface
{

    /**
     * Checks what to do and invokes the proper method
     *
     * @return string the prepared html-output
     */
    public function loadData()
    {
        $strReturn = "";

        if (!$this->objSession->isLoggedin()) {
            if ($this->getParam("action") == "portalCompleteRegistration") {
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
    private function completeRegistration()
    {
        $strReturn = "";

        if ($this->getSystemid() != "") {
            $objUser = new UserUser($this->getParam("systemid"));

            if ($objUser->getStrEmail() != "") {
                if ($objUser->getIntActive() == 0 && $objUser->getIntLogins() == 0 && $objUser->getStrAuthcode() == $this->getParam("authcode") && $objUser->getStrAuthcode() != "") {
                    $objUser->setIntActive(1);
                    $objUser->setStrAuthcode("");
                    if ($objUser->updateObjectToDb()) {
                        $strReturn .= $this->getLang("pr_completionSuccess");
                        if ($this->arrElementData["portalregistration_success"] != "") {
                            $this->portalReload(Link::getLinkPortalHref($this->arrElementData["portalregistration_success"]));
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
    private function editUserData()
    {

        $arrErrors = array();
        $bitForm = true;
        //what to do?
        if ($this->getParam("submitUserForm") != "") {

            $objTextValidator = new TextValidator();
            $objEmailValidator = new EmailValidator();

            if ($this->getParam("password") == "" || $this->getParam("password") != $this->getParam("password2")) {
                $arrErrors[] = $this->getLang("pr_passwordsUnequal");
            }

            if (!$objTextValidator->validate($this->getParam("username"))) {
                $arrErrors[] = $this->getLang("pr_noUsername");
            }

            //username already existing?
            if ($objTextValidator->validate($this->getParam("username")) && count(UserUser::getAllUsersByName($this->getParam("username"))) > 0) {
                $arrErrors[] = $this->getLang("pr_usernameGiven");
            }

            if (!$objEmailValidator->validate($this->getParam("email"))) {
                $arrErrors[] = $this->getLang("pr_invalidEmailadress");
            }

            //Check captachcode
            if ($this->getParam("form_captcha") == "" || $this->getParam("form_captcha") != $this->objSession->getCaptchaCode()) {
                $arrErrors[] = $this->getLang("pr_captcha");
            }

            if (count($arrErrors) == 0) {
                $bitForm = false;
            }
        }

        if ($bitForm) {
            $arrTemplate = array();

            $arrTemplate["username"] = $this->getParam("username");
            $arrTemplate["email"] = $this->getParam("email");
            $arrTemplate["forename"] = $this->getParam("forename");
            $arrTemplate["name"] = $this->getParam("name");
            $arrTemplate["formaction"] = Link::getLinkPortalHref($this->getPagename(), "", "portalCreateAccount");

            $arrTemplate["formErrors"] = "";
            if (count($arrErrors) > 0) {
                foreach ($arrErrors as $strOneError) {
                    $arrTemplate["formErrors"] .= "".$this->objTemplate->fillTemplateFile(array("error" => $strOneError), "/module_portalregistration/".$this->arrElementData["portalregistration_template"], "errorRow");
                }
            }

            return $this->objTemplate->fillTemplateFile($arrTemplate, "/module_portalregistration/".$this->arrElementData["portalregistration_template"], "portalregistration_userdataform");
        }
        else {
            //create new user, inactive
            $objUser = new UserUser();
            $objUser->setStrUsername($this->getParam("username"));
            $objUser->setIntActive(0);
            $objUser->setIntAdmin(0);
            $objUser->setIntPortal(1);
            $objUser->setStrSubsystem("kajona");
            $strAuthcode = generateSystemid();
            $objUser->setStrAuthcode($strAuthcode);

            if ($objUser->updateObjectToDb()) {

                $objSourceuser = $objUser->getObjSourceUser();
                $objSourceuser->setStrEmail($this->getParam("email"));
                $objSourceuser->setStrForename($this->getParam("forename"));
                $objSourceuser->setStrName($this->getParam("name"));
                $objSourceuser->setStrPass($this->getParam("password"));
                $objSourceuser->updateObjectToDb();

                //group assignments
                $objGroup = new UserGroup($this->arrElementData["portalregistration_group"]);
                $objGroup->getObjSourceGroup()->addMember($objUser->getObjSourceUser());
                //and to the guests to avoid conflicts
                $objGroup = new UserGroup(SystemSetting::getConfigValue("_guests_group_id_"));
                $objGroup->getObjSourceGroup()->addMember($objUser->getObjSourceUser());
                //create a mail to allow the user to activate itself

                $strMailContent = $this->getLang("pr_email_body");
                $strTemp = Link::getLinkPortalHref($this->getPagename(), "", "portalCompleteRegistration", "&authcode=".$strAuthcode, $objUser->getSystemid());
                $strMailContent .= html_entity_decode("<a href=\"".$strTemp."\">".$strTemp."</a>");
                $strMailContent .= $this->getLang("pr_email_footer");

                $objScriptlets = new ScriptletHelper();
                $strMailContent = $objScriptlets->processString($strMailContent);

                $objMail = new Mail();
                $objMail->setSubject($this->getLang("pr_email_subject"));
                $objMail->setHtml($strMailContent);
                $objMail->addTo($this->getParam("email"));
                $objMail->sendMail();
            }

            return $this->getLang("pr_register_suc");
        }
    }

}
