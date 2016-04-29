<?php
/*"******************************************************************************************************
*   (c) 2004-2006 by MulchProductions, www.mulchprod.de                                                 *
*   (c) 2007-2016 by Kajona, www.kajona.de                                                              *
*       Published under the GNU LGPL v2.1, see /system/licence_lgpl.txt                                 *
*-------------------------------------------------------------------------------------------------------*
*	$Id$                                        *
********************************************************************************************************/

namespace Kajona\System\Admin;

use Kajona\System\System\Cookie;
use Kajona\System\System\Link;
use Kajona\System\System\Logger;
use Kajona\System\System\ResponseObject;
use Kajona\System\System\Session;
use Kajona\System\System\UserUser;


/**
 * This class shows a little LoginScreen if the user is net yet logged in
 *
 * @package module_system
 * @author sidler@mulchprod.de
 *
 * @module login
 * @moduleId _user_modul_id_
 */
class LoginAdmin extends AdminController implements AdminInterface
{

    const SESSION_REFERER = "LOGIN_SESSION_REFERER";
    const SESSION_PARAMS = "LOGIN_SESSION_PARAMS";
    const SESSION_LOAD_FROM_PARAMS = "LOGIN_SESSION_LOAD_FROM_PARAMS";

    public function __construct()
    {

        $this->setArrModuleEntry("template", "/login.tpl");

        parent::__construct();

        if($this->getAction() == "list") {
            $this->setAction("login");
        }
    }


    /**
     * Creates a small login-field
     *
     * @return string
     */
    protected function actionLogin()
    {

        if ($this->objSession->isLoggedin() && $this->objSession->isAdmin()) {
            $this->loadPostLoginSite();
            return;
        }

        //Save the requested URL
        if ($this->getParam("loginerror") == "") {
            //Store some of the last requests' data
            $this->objSession->setSession(self::SESSION_REFERER, getServer("QUERY_STRING"));
            $this->objSession->setSession(self::SESSION_PARAMS, getArrayPost());
        }

        //Loading a small login-form
        $arrTemplate = array();
        $strForm = "";
        $strForm .= $this->objToolkit->formHeader(Link::getLinkAdminHref($this->getArrModule("modul"), "adminLogin"));
        $strForm .= $this->objToolkit->formInputText("name", $this->getLang("login_loginUser", "user"), "", "input-large");
        $strForm .= $this->objToolkit->formInputPassword("passwort", $this->getLang("login_loginPass", "user"), "", "input-large");
        $strForm .= $this->objToolkit->formInputSubmit($this->getLang("login_loginButton", "user"));
        $strForm .= $this->objToolkit->formClose();
        $arrTemplate["form"] = $strForm;
        $arrTemplate["loginTitle"] = $this->getLang("login_loginTitle", "user");
        $arrTemplate["loginJsInfo"] = $this->getLang("login_loginJsInfo", "user");
        $arrTemplate["loginCookiesInfo"] = $this->getLang("login_loginCookiesInfo", "user");
        //An error occurred?
        if ($this->getParam("loginerror") == 1) {
            $arrTemplate["error"] = $this->getLang("login_loginError", "user");
        }

        $strReturn = $this->objTemplate->fillTemplateFile($arrTemplate, "/elements.tpl", "login_form");


        return $strReturn;
    }

    /**
     * Creates a form in order to change the password - if the authcode is valid
     *
     * @return string
     */
    protected function actionPwdReset()
    {
        $strReturn = "";

        if (!validateSystemid($this->getParam("systemid"))) {
            return $this->getLang("login_change_error", "user");
        }

        $objUser = new UserUser($this->getParam("systemid"));

        if ($objUser->getStrAuthcode() != "" && $this->getParam("authcode") == $objUser->getStrAuthcode() && $objUser->getStrUsername() != "") {
            if ($this->getParam("reset") == "") {
                //Loading a small form to change the password
                $arrTemplate = array();
                $strForm = "";
                $strForm .= $this->objToolkit->getTextRow($this->getLang("login_password_form_intro", "user"));
                $strForm .= $this->objToolkit->formHeader(Link::getLinkAdminHref($this->getArrModule("modul"), "pwdReset"));
                $strForm .= $this->objToolkit->formInputText("username", $this->getLang("login_loginUser", "user"), "", "inputTextShort");
                $strForm .= $this->objToolkit->formInputPassword("password1", $this->getLang("login_loginPass", "user"), "", "inputTextShort");
                $strForm .= $this->objToolkit->formInputPassword("password2", $this->getLang("login_loginPass2", "user"), "", "inputTextShort");
                $strForm .= $this->objToolkit->formInputSubmit($this->getLang("login_changeButton", "user"), "", "", "inputSubmitShort");
                $strForm .= $this->objToolkit->formInputHidden("reset", "reset");
                $strForm .= $this->objToolkit->formInputHidden("authcode", $this->getParam("authcode"));
                $strForm .= $this->objToolkit->formInputHidden("systemid", $this->getParam("systemid"));
                $strForm .= $this->objToolkit->formClose();
                $arrTemplate["form"] = $strForm;
                $arrTemplate["loginTitle"] = $this->getLang("login_loginTitle", "user");
                $arrTemplate["loginJsInfo"] = $this->getLang("login_loginJsInfo", "user");
                $arrTemplate["loginCookiesInfo"] = $this->getLang("login_loginCookiesInfo", "user");
                //An error occurred?
                if ($this->getParam("loginerror") == 1) {
                    $arrTemplate["error"] = $this->getLang("login_loginError", "user");
                }

                $strReturn = $this->objTemplate->fillTemplateFile($arrTemplate, "/elements.tpl", "login_form");
            }
            else {
                //check the submitted passwords.
                $strPass1 = trim($this->getParam("password1"));
                $strPass2 = trim($this->getParam("password2"));

                if ($strPass1 == $strPass2 && checkText($strPass1, 3, 200) && $objUser->getStrUsername() == $this->getParam("username")) {
                    if ($objUser->getObjSourceUser()->isPasswordResettable() && method_exists($objUser->getObjSourceUser(), "setStrPass")) {
                        $objUser->getObjSourceUser()->setStrPass($strPass1);
                        $objUser->getObjSourceUser()->updateObjectToDb();
                    }
                    $objUser->setStrAuthcode("");
                    $objUser->updateObjectToDb();
                    Logger::getInstance()->addLogRow("changed password of user ".$objUser->getStrUsername(), Logger::$levelInfo);

                    $strReturn .= $this->getLang("login_change_success", "user");
                }
                else {
                    $strReturn .= $this->getLang("login_change_error", "user");
                }
            }
        }
        else {
            $strReturn .= $this->getLang("login_change_error", "user");
        }


        return $strReturn;
    }

    /**
     * Returns a skin based info-box about the current users' login-status.
     *
     * @return string
     */
    public function getLoginStatus()
    {
        $arrTemplate = array();
        $arrTemplate["name"] = $this->objSession->getUsername();
        $arrTemplate["profile"] = Link::getLinkAdminHref("user", "edit", "userid=".$this->objSession->getUserID());
        $arrTemplate["logout"] = Link::getLinkAdminHref($this->getArrModule("modul"), "adminLogout");
        $arrTemplate["dashboard"] = Link::getLinkAdminHref("dashboard");
        $arrTemplate["sitemap"] = Link::getLinkAdminHref("dashboard", "sitemap");
        $arrTemplate["statusTitle"] = $this->getLang("login_statusTitle", "user");
        $arrTemplate["profileTitle"] = $this->getLang("login_profileTitle", "user");
        $arrTemplate["logoutTitle"] = $this->getLang("login_logoutTitle", "user");
        $arrTemplate["dashboardTitle"] = $this->getLang("login_dashboard", "user");
        $arrTemplate["sitemapTitle"] = $this->getLang("login_sitemap", "user");
        $arrTemplate["printLink"] = Link::getLinkAdminManual("href=\"#\" onclick=\"window.print();\"", $this->getLang("login_printview", "user"));
        $arrTemplate["printTitle"] = $this->getLang("login_print", "user");

        return $this->objToolkit->getLoginStatus($arrTemplate);
    }

    /**
     * Generates the form to fetch the credentials required to authenticate a user
     *
     * @return string
     */
    protected function actionAdminLogin()
    {

        if ($this->objSession->login($this->getParam("name"), $this->getParam("passwort"))) {
            //user allowed to access admin?
            if (!$this->objSession->isAdmin()) {
                //no, reset session
                $this->objSession->logout();
            }
            //save the current skin as a cookie
            $objCookie = new Cookie();
            $objCookie->setCookie("adminskin", $this->objSession->getAdminSkin(false, true));
            $objCookie->setCookie("adminlanguage", $this->objSession->getAdminLanguage(false, true));

            $this->loadPostLoginSite();

            return true;
        }
        else {
            ResponseObject::getInstance()->setStrRedirectUrl(Link::getLinkAdminHref("login", "login", "&loginerror=1"));
            return false;
        }
    }

    /**
     * Ends the session of the current user and
     * redirects back to the login-screen
     */
    protected function actionAdminlogout()
    {
        $this->objSession->logout();
        ResponseObject::getInstance()->setStrRedirectUrl(Link::getLinkAdminHref("login"));
    }


    private function loadPostLoginSite()
    {
        //any url to redirect?
        if ($this->objSession->getSession(self::SESSION_REFERER) != "" && $this->objSession->getSession(self::SESSION_REFERER) != "admin=1") {
            ResponseObject::getInstance()->setStrRedirectUrl(_indexpath_."?".$this->objSession->getSession(self::SESSION_REFERER));
            $this->objSession->sessionUnset(self::SESSION_REFERER);
            $this->objSession->setSession(self::SESSION_LOAD_FROM_PARAMS, "true");
        }
        else {
            //route to the default module
            $strModule = "dashboard";
            if (Session::getInstance()->isLoggedin()) {
                $objUser = new UserUser(Session::getInstance()->getUserID());
                if ($objUser->getStrAdminModule() != "") {
                    $strModule = $objUser->getStrAdminModule();
                }
            }
            ResponseObject::getInstance()->setStrRedirectUrl(Link::getLinkAdminHref($strModule));
        }
    }
}
