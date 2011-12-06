<?php
/*"******************************************************************************************************
*   (c) 2004-2006 by MulchProductions, www.mulchprod.de                                                 *
*   (c) 2007-2011 by Kajona, www.kajona.de                                                              *
*       Published under the GNU LGPL v2.1, see /system/licence_lgpl.txt                                 *
*-------------------------------------------------------------------------------------------------------*
*	$Id$                                        *
********************************************************************************************************/

/**
 * This class shows a little LoginScreen if the user is net yet logged in
 *
 * @package module_system
 * @author sidler@mulchprod.de
 */
class class_module_login_admin extends class_admin implements interface_admin  {

	public function __construct() {
        $arrModule = array();
		$arrModule["name"] 			= "module_user";
		$arrModule["moduleId"] 		= _user_modul_id_;
		$arrModule["modul"]			= "login";
		$arrModule["template"]		= "/login.tpl";

		//Base-Class...
		parent::__construct($arrModule);

        if($this->getAction() != "pwdReset" || $this->getAction() != "adminLogin" || $this->getAction() != "adminLogout")
            $this->setAction("login");
	}


	/**
	 * Creates a small login-field
	 *
	 * @return unknown
	 */
	protected function actionLogin() {
		$strReturn = "";

		//Save the requested URL
		if($this->getParam("loginerror") == "")
		    $this->objSession->setSession("loginReferer", getServer("QUERY_STRING"));

		//Loading a small login-form
		$strTemplateID = $this->objTemplate->readTemplate("/elements.tpl", "login_form");
		$arrTemplate = array();
		$strForm = "";
		$strForm .= $this->objToolkit->formHeader(getLinkAdminHref($this->arrModule["modul"], "adminLogin"));
		$strForm .= $this->objToolkit->formInputText("name", $this->getText("login_loginUser", "user"), "", "inputTextShort");
		$strForm .= $this->objToolkit->formInputPassword("passwort", $this->getText("login_loginPass", "user"), "", "inputTextShort");
		$strForm .= $this->objToolkit->formInputSubmit($this->getText("login_loginButton", "user"), "", "", "inputSubmitShort");
		$strForm .= $this->objToolkit->formClose();
		$arrTemplate["form"] = $strForm;
		$arrTemplate["loginTitle"] = $this->getText("login_loginTitle", "user");
		$arrTemplate["loginJsInfo"] = $this->getText("login_loginJsInfo", "user");
		$arrTemplate["loginCookiesInfo"] = $this->getText("login_loginCookiesInfo", "user");
		//An error occurred?
		if($this->getParam("loginerror") == 1)
			$arrTemplate["error"] = $this->getText("login_loginError", "user");

		$strReturn = $this->objTemplate->fillTemplate($arrTemplate, $strTemplateID);


		return $strReturn;
	}

    /**
	 * Creates a form in order to change the password - if the authcode is valid
	 *
	 * @return unknown
	 */
	protected function actionPwdReset() {
		$strReturn = "";

        if(validateSystemid($this->getParam("systemid"))) {
            $objUser = new class_modul_user_user($this->getParam("systemid"));

            if($objUser->getStrAuthcode() != "" && $this->getParam("authcode") == $objUser->getStrAuthcode() && $objUser->getStrUsername() != "") {
                if($this->getParam("reset") == "") {
                    //Loading a small form to change the password
                    $strTemplateID = $this->objTemplate->readTemplate("/elements.tpl", "login_form");
                    $arrTemplate = array();
                    $strForm = "";
                    $strForm .= $this->objToolkit->getTextRow($this->getText("login_password_form_intro", "user"));
                    $strForm .= $this->objToolkit->formHeader(getLinkAdminHref($this->arrModule["modul"], "pwdReset"));
                    $strForm .= $this->objToolkit->formInputText("username", $this->getText("login_loginUser", "user"), "", "inputTextShort");
                    $strForm .= $this->objToolkit->formInputPassword("password1", $this->getText("login_loginPass", "user"), "", "inputTextShort");
                    $strForm .= $this->objToolkit->formInputPassword("password2", $this->getText("login_loginPass2", "user"), "", "inputTextShort");
                    $strForm .= $this->objToolkit->formInputSubmit($this->getText("login_changeButton", "user"), "", "", "inputSubmitShort");
                    $strForm .= $this->objToolkit->formInputHidden("reset", "reset");
                    $strForm .= $this->objToolkit->formInputHidden("authcode", $this->getParam("authcode"));
                    $strForm .= $this->objToolkit->formInputHidden("systemid", $this->getParam("systemid"));
                    $strForm .= $this->objToolkit->formClose();
                    $arrTemplate["form"] = $strForm;
                    $arrTemplate["loginTitle"] = $this->getText("login_loginTitle", "user");
                    $arrTemplate["loginJsInfo"] = $this->getText("login_loginJsInfo", "user");
                    $arrTemplate["loginCookiesInfo"] = $this->getText("login_loginCookiesInfo", "user");
                    //An error occured?
                    if($this->getParam("loginerror") == 1)
                        $arrTemplate["error"] = $this->getText("login_loginError", "user");

                    $strReturn = $this->objTemplate->fillTemplate($arrTemplate, $strTemplateID);
                }
                else {
                    //check the submitted passwords.
                    $strPass1 = trim($this->getParam("password1"));
                    $strPass2 = trim($this->getParam("password2"));

                    if($strPass1 == $strPass2 && checkText($strPass1, 3, 200) && $objUser->getStrUsername() == $this->getParam("username") ) {
                        if($objUser->getObjSourceUser()->isPasswortResetable() && method_exists($objUser->getObjSourceUser(), "setStrPass")) {
                            $objUser->getObjSourceUser()->setStrPass($strPass1);
                            $objUser->getObjSourceUser()->updateObjectToDb();
                        }
                        $objUser->setStrAuthcode("");
                        $objUser->updateObjectToDb();
                        class_logger::getInstance()->addLogRow("changed password of user ".$objUser->getStrUsername(), class_logger::$levelInfo);

                        $strReturn .= $this->getText("login_change_success", "user");
                    }
                    else
                        $strReturn .= $this->getText("login_change_error", "user");
                }
            }
            else
                $strReturn .= $this->getText("login_change_error", "user");

        }
        else
            $strReturn .= $this->getText("login_change_error", "user");


		return $strReturn;
	}

    /**
     * Returns a skin based info-box about the current users' login-status.
     *
     * @return string
     */
	public function getLoginStatus() {
		$arrTemplate = array();
		$arrTemplate["name"] = $this->objSession->getUsername();
		$arrTemplate["profile"] = getLinkAdminHref("user", "edit", "userid=".$this->objSession->getUserID());
		$arrTemplate["logout"] = getLinkAdminHref($this->arrModule["modul"], "adminLogout");
		$arrTemplate["dashboard"] = getLinkAdminHref("dashboard");
		$arrTemplate["statusTitle"] = $this->getText("login_statusTitle", "user");
		$arrTemplate["profileTitle"] = $this->getText("login_profileTitle", "user");
		$arrTemplate["logoutTitle"] = $this->getText("login_logoutTitle", "user");
		$arrTemplate["dashboardTitle"] = $this->getText("login_dashboard", "user");
		$arrTemplate["printLink"] = getLinkAdminManual("href=\"#\" onclick=\"KAJONA.admin.openPrintView()\"", $this->getText("login_printview", "user"));

		return $this->objToolkit->getLoginStatus($arrTemplate);
	}

    /**
     * Generates the form to fetch the credentials required to authenticate a user
     *
     * @return string
     */
	protected function actionAdminLogin() {

		if($this->objSession->login($this->getParam("name"), $this->getParam("passwort"))) {
		    //user allowed to access admin?
		    if(!$this->objSession->isAdmin()) {
		        //no, reset session
		        $this->objSession->logout();
		    }
			//save the current skin as a cookie
    	    $objCookie = new class_cookie();
    		$objCookie->setCookie("adminskin", $this->objSession->getAdminSkin(false));
    		$objCookie->setCookie("adminlanguage", $this->objSession->getAdminLanguage(false));
    		//any url to redirect?
    		if($this->objSession->getSession("loginReferer") != "")
			    header("Location: "._indexpath_."?".$this->objSession->getSession("loginReferer"));
			else
			    header("Location: "._indexpath_."?admin=1");


			return true;
		}
		else {
			header("Location: "._indexpath_."?admin=1&loginerror=1");
			return false;
		}
	}

    /**
	 * Ends the session of the current user and
     * redirects back to the login-screen
	 *
	 */
	protected function actionAdminlogout() {
		$this->objSession->logout();
		header("Location: "._indexpath_."?admin=1");
	}


}
