<?php
/*"******************************************************************************************************
*   (c) 2004-2006 by MulchProductions, www.mulchprod.de                                                 *
*   (c) 2007-2016 by Kajona, www.kajona.de                                                              *
*       Published under the GNU LGPL v2.1, see /system/licence_lgpl.txt                                 *
********************************************************************************************************/

namespace Kajona\Portallogin\Portal\Elements;

use Kajona\Pages\Portal\ElementPortal;
use Kajona\Pages\Portal\PortalElementInterface;
use Kajona\System\System\Link;
use Kajona\System\System\Logger;
use Kajona\System\System\Mail;
use Kajona\System\System\ScriptletHelper;
use Kajona\System\System\Session;
use Kajona\System\System\UserSourcefactory;
use Kajona\System\System\Usersources\UsersourcesUserKajona;
use Kajona\System\System\UserUser;
use Kajona\System\System\Validators\EmailValidator;
use Kajona\System\System\Validators\TextValidator;


/**
 * Portal Element to load the login-form, or a small "status" area, providing an logout link
 *
 * @author sidler@mulchprod.de
 *
 * @targetTable element_plogin.content_id
 */
class ElementPortalloginPortal extends ElementPortal implements PortalElementInterface
{


    /**
     * Checks what to do and invokes the proper method
     * Notice: In case of success, a location-header is sent, too. Needed, cause otherwise the rights would not
     * be checked during the login/-logout-loading against the new user-id!
     *
     * @return string the prepared html-output
     */
    public function loadData()
    {
        $strReturn = "";

        $strOldAction = "";
        if (validateSystemid($this->getParam("pl_systemid")) && $this->getParam("pl_systemid") != $this->arrElementData["content_id"]) {
            $strOldAction = $this->getParam("action");
            $this->setParam("action", "");
        }

        if ($this->getParam("action") == "portalLogin") {
            if ($this->doLogin()) {
                if ($this->arrElementData["portallogin_success"] != "") {
                    $this->portalReload(Link::getLinkPortalHref($this->arrElementData["portallogin_success"]));
                }
                else {
                    $this->portalReload(Link::getLinkPortalHref($this->getPagename()));
                }
            }
            else {
                if ($this->arrElementData["portallogin_error"] != "") {
                    $this->portalReload(Link::getLinkPortalHref($this->arrElementData["portallogin_error"]));
                }
            }
        }
        elseif ($this->getParam("action") == "portalLogout") {
            $this->doLogout();
            if ($this->arrElementData["portallogin_logout_success"] != "") {
                $this->portalReload(Link::getLinkPortalHref($this->arrElementData["portallogin_logout_success"]));
            }
            else {
                $this->portalReload(Link::getLinkPortalHref($this->getPagename()));
            }
        }


        if (!$this->objSession->isLoggedin()) {

            if ($this->getAction() == "portalLoginReset") {
                $strReturn .= $this->resetForm();
            }
            elseif ($this->getAction() == "portalResetPwd") {
                $strReturn .= $this->newPwdForm();
            }
            else {
                $strReturn .= $this->loginForm();
            }
        }
        else {
            if ($this->getParam("action") == "portalEditProfile") {
                $strReturn .= $this->editUserData();
            }
            else {
                $strReturn .= $this->statusArea();
            }
        }

        if ($strOldAction != "") {
            $this->setParam("action", $strOldAction);
        }

        return $strReturn;
    }


    /**
     * Creates a form to enter the new password of the account to reset.
     *
     * @return string
     */
    private function newPwdForm()
    {
        $strReturn = "";

        if ($this->getParam("reset") != "" && getPost("reset") != "") {
            //try to load the user


            $objUser = new UserUser($this->getParam("systemid"));
            if ($objUser->getStrAuthcode() != "" && $objUser->getStrAuthcode() == $this->getParam("authcode") && $objUser->getStrUsername() != "") {
                //check the submitted passwords.
                $strPass1 = trim($this->getParam("portallogin_password1"));
                $strPass2 = trim($this->getParam("portallogin_password2"));

                $objValidator = new TextValidator();
                if ($strPass1 == $strPass2 && $objValidator->validate($strPass1)) {

                    if ($objUser->getObjSourceUser()->isPasswordResettable() && method_exists($objUser->getObjSourceUser(), "setStrPass")) {
                        $objUser->getObjSourceUser()->setStrPass($strPass1);
                        $objUser->getObjSourceUser()->updateObjectToDb();
                    }

                    $objUser->setStrAuthcode("");
                    $objUser->updateObjectToDb();

                    Logger::getInstance(Logger::USERSOURCES)->addLogRow("changed password of user ".$objUser->getStrUsername(), Logger::$levelInfo);

                    $strReturn .= $this->getLang("resetSuccess");
                }
                else {
                    $strReturn .= $this->getLang("resetError");
                }
            }
            else {
                $strReturn .= $this->getLang("resetError");
            }

        }
        else {

            $arrTemplate = array();
            //check sysid & authcode
            $objUser = new UserUser($this->getParam("systemid"));


            if ($objUser->getStrAuthcode() != "" && $objUser->getStrAuthcode() == $this->getParam("authcode")) {
                $arrTemplate["portallogin_action"] = "portalResetPwd";
                $arrTemplate["portallogin_systemid"] = $this->getParam("systemid");
                $arrTemplate["portallogin_authcode"] = $this->getParam("authcode");
                $arrTemplate["portallogin_resetHint"] = "portalLoginReset";
                $arrTemplate["portallogin_elsystemid"] = $this->arrElementData["content_id"];
                $arrTemplate["action"] = Link::getLinkPortalHref($this->getPagename());
                $strReturn .= $this->objTemplate->fillTemplateFile($arrTemplate, "/module_portallogin/".$this->arrElementData["portallogin_template"], "portallogin_newpwdform");

            }
            else {
                $strReturn .= "Permission Error";
            }
        }

        return $strReturn;
    }


    /**
     * Creates a form to enter the username of the account to reset.
     *
     * @return string
     */
    private function resetForm()
    {
        $strReturn = "";

        if ($this->getParam("reset") != "" && getPost("reset") != "") {
            //try to load the user
            $objSubsystem = new UserSourcefactory();
            $objUser = $objSubsystem->getUserByUsername($this->getParam("portallogin_username"));
            if ($objUser != null) {
                $objValidator = new EmailValidator();
                if ($objUser->getStrEmail() != "" && $objValidator->validate($objUser->getStrEmail()) && $objUser->getIntPortal() == 1 && $objUser->getIntRecordStatus() == 1) {

                    //generate an authcode and save it with the user
                    $strAuthcode = generateSystemid();
                    $objUser->setStrAuthcode($strAuthcode);
                    $objUser->updateObjectToDb();

                    $strMailContent = $this->getLang("resetemailBody");
                    $strTemp = Link::getLinkPortalHref($this->getPagename(), "", "portalResetPwd", "&authcode=".$strAuthcode, $objUser->getSystemid());
                    $strMailContent .= html_entity_decode("<a href=\"".$strTemp."\">".$strTemp."</a>");

                    $objScriptlets = new ScriptletHelper();
                    $strMailContent = $objScriptlets->processString($strMailContent);

                    //create a mail confirming the change
                    $objEmail = new Mail();
                    $objEmail->setSubject($this->getLang("resetemailTitle"));
                    $objEmail->setHtml($strMailContent);
                    $objEmail->addTo($objUser->getStrEmail());
                    $objEmail->sendMail();

                    $strReturn .= $this->getLang("resetMailSuccess");
                }
            }

        }
        else {
            $arrTemplate = array();
            $arrTemplate["portallogin_action"] = "portalLoginReset";
            $arrTemplate["portallogin_resetHint"] = "portalLoginReset";
            $arrTemplate["portallogin_elsystemid"] = $this->arrElementData["content_id"];
            $arrTemplate["action"] = Link::getLinkPortalHref($this->getPagename());
            $strReturn .= $this->objTemplate->fillTemplateFile($arrTemplate, "/module_portallogin/".$this->arrElementData["portallogin_template"], "portallogin_resetform");
        }

        return $strReturn;
    }


    /**
     * Creates a form to login
     * The template has to provide at least the following html-input-elements:
     * portallogin_username, portallogin_password, action (hidden)
     *
     * @return string
     */
    private function loginForm()
    {
        $arrTemplate = array();
        $arrTemplate["portallogin_action"] = "portalLogin";
        $arrTemplate["portallogin_elsystemid"] = $this->arrElementData["content_id"];

        $strPwdPage = $this->arrElementData["portallogin_pwdforgot"] != "" ? $this->arrElementData["portallogin_pwdforgot"] : $this->getPagename();
        $arrTemplate["portallogin_forgotpwdlink"] = Link::getLinkPortal($strPwdPage, "", "", $this->getLang("pwdForgotLink"), "portalLoginReset", "&pl_systemid=".$this->arrElementData["content_id"]);
        $arrTemplate["portallogin_forgotpwdlinksimple"] = Link::getLinkPortal($strPwdPage, "", "", $this->getLang("pwdForgotLink"), "portalLoginReset");

        $arrTemplate["action"] = Link::getLinkPortalHref($this->getPagename());
        return $this->objTemplate->fillTemplateFile($arrTemplate, "/module_portallogin/".$this->arrElementData["portallogin_template"], "portallogin_loginform");
    }

    /**
     * Creates a small status-area, providing a link to logout
     *
     * @return string
     */
    private function statusArea()
    {
        $arrTemplate = array();
        $arrTemplate["loggedin_label"] = $this->getLang("loggedin_label");
        $arrTemplate["username"] = $this->objSession->getUsername();
        $arrTemplate["logoutlink"] = Link::getLinkPortal($this->getPagename(), "", "", $this->getLang("logoutlink"), "portalLogout", "&pl_systemid=".$this->arrElementData["content_id"]);
        $arrTemplate["logoutlinksimple"] = Link::getLinkPortal($this->getPagename(), "", "", $this->getLang("logoutlink"), "portalLogout");

        $strProfileeditpage = $this->getPagename();
        if ($this->arrElementData["portallogin_profile"] != "") {
            $strProfileeditpage = $this->arrElementData["portallogin_profile"];
        }

        $arrTemplate["editprofilelink"] = Link::getLinkPortal($strProfileeditpage, "", "", $this->getLang("editprofilelink"), "portalEditProfile", "&pl_systemid=".$this->arrElementData["content_id"]);
        $arrTemplate["editprofilelinksimple"] = Link::getLinkPortal($strProfileeditpage, "", "", $this->getLang("editprofilelink"), "portalEditProfile");
        return $this->objTemplate->fillTemplateFile($arrTemplate, "/module_portallogin/".$this->arrElementData["portallogin_template"], "portallogin_status");
    }


    /**
     * Creates a form to edit a users data
     *
     * @return string
     */
    private function editUserData()
    {

        $arrErrors = array();
        $bitForm = true;
        //what to do?
        if ($this->getParam("submitUserForm") != "") {
            if ($this->getParam("password") != "") {
                if ($this->getParam("password") != $this->getParam("password2")) {
                    $arrErrors[] = $this->getLang("passwordsUnequal");
                }
            }

            $objValidator = new EmailValidator();
            if (!$objValidator->validate($this->getParam("email"))) {
                $arrErrors[] = $this->getLang("invalidEmailadress");
            }

            if (count($arrErrors) == 0) {
                $bitForm = false;
            }
        }

        if ($bitForm) {
            $arrTemplate = array();


            $objUser = Session::getInstance()->getUser();
            if ($objUser->getObjSourceUser()->isEditable() && $objUser->getStrSubsystem() == "kajona" && $objUser->getObjSourceUser() instanceof UsersourcesUserKajona) {

                $arrTemplate["username"] = $objUser->getStrUsername();
                $arrTemplate["email"] = $objUser->getObjSourceUser()->getStrEmail();
                $arrTemplate["forename"] = $objUser->getObjSourceUser()->getStrForename();
                $arrTemplate["name"] = $objUser->getObjSourceUser()->getStrName();

                $arrTemplate["street"] = $objUser->getObjSourceUser()->getStrStreet();
                $arrTemplate["postal"] = $objUser->getObjSourceUser()->getStrPostal();
                $arrTemplate["city"] = $objUser->getObjSourceUser()->getStrCity();
                $arrTemplate["phone"] = $objUser->getObjSourceUser()->getStrTel();
                $arrTemplate["mobile"] = $objUser->getObjSourceUser()->getStrMobile();
                $arrTemplate["portallogin_elsystemid"] = $this->arrElementData["content_id"];



                $arrTemplate["formaction"] = Link::getLinkPortalHref($this->getPagename(), "", "portalEditProfile");

                $arrTemplate["formErrors"] = "";
                if (count($arrErrors) > 0) {
                    foreach ($arrErrors as $strOneError) {
                        $arrTemplate["formErrors"] .= "".$this->objTemplate->fillTemplateFile(array("error" => $strOneError), "/module_portallogin/".$this->arrElementData["portallogin_template"], "errorRow");
                    }
                }

                return $this->objTemplate->fillTemplateFile($arrTemplate, "/module_portallogin/".$this->arrElementData["portallogin_template"], $this->arrElementData["portallogin_editmode"] == 1 ? "portallogin_userdataform_complete" : "portallogin_userdataform_minimal");
            }
            else {
                return "Login provider not supported.";
            }
        }
        else {
            $objUser = Session::getInstance()->getUser();

            if ($objUser->getObjSourceUser() instanceof UsersourcesUserKajona) {

                $objUser->getObjSourceUser()->setStrEmail($this->getParam("email"));
                $objUser->getObjSourceUser()->setStrForename($this->getParam("forename"));
                $objUser->getObjSourceUser()->setStrName($this->getParam("name"));
                $objUser->getObjSourceUser()->setStrPass($this->getParam("password"));

                if ($this->arrElementData["portallogin_editmode"] == 1) {
                    $objUser->getObjSourceUser()->setStrStreet($this->getParam("street"));
                    $objUser->getObjSourceUser()->setStrPostal($this->getParam("postal"));
                    $objUser->getObjSourceUser()->setStrCity($this->getParam("city"));
                    $objUser->getObjSourceUser()->setStrTel($this->getParam("phone"));
                    $objUser->getObjSourceUser()->setStrMobile($this->getParam("mobile"));


                }

                $objUser->getObjSourceUser()->updateObjectToDb();

            }
            $this->portalReload(Link::getLinkPortalHref($this->getPagename()));

        }
        return "";
    }


    /**
     * Tries to log the user with the given credentials into the system.
     * To log in through the portal, the right "portal" has to be given!
     *
     * @return bool
     */
    private function doLogin()
    {
        $strUsername = htmlToString($this->getParam("portallogin_username"), true);
        $strPassword = htmlToString($this->getParam("portallogin_password"), true);

        if ($this->objSession->login($strUsername, $strPassword)) {
            if (!$this->objSession->isPortal()) {
                $this->objSession->logout();
                return false;
            }
            else {
                return true;
            }
        }
        return false;
    }


    /**
     * Logs the user out off the system
     */
    private function doLogout()
    {
        $this->objSession->logout();
    }

}
