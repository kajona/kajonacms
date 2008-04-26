<?php
/*"******************************************************************************************************
*   (c) 2004-2006 by MulchProductions, www.mulchprod.de                                                 *
*   (c) 2007-2008 by Kajona, www.kajona.de                                                              *
*       Published under the GNU LGPL v2.1, see /system/licence_lgpl.txt                                 *
*-------------------------------------------------------------------------------------------------------*
* 																										*
* 	class_login_admin.php																				*
* 	Shows a litte login-screen																			*
*																										*
*-------------------------------------------------------------------------------------------------------*
*	$Id$                                        *
********************************************************************************************************/

//Base-Class
include_once(_adminpath_."/class_admin.php");
//Interface to implement
include_once(_adminpath_."/interface_admin.php");

include_once(_systempath_."/class_modul_user_log.php");

/**
 * This class shows a little LoginScreen if the user is net yet loggedin
 *
 * @package modul_system
 */
class class_login_admin extends class_admin implements interface_admin  {

	private $strTemp;

	public function __construct() {
		$arrModule["name"] 			= "modul_user";
		$arrModule["author"] 		= "sidler@mulchprod.de";
		$arrModule["moduleId"] 		= _user_modul_id_;
		$arrModule["modul"]			= "login";
		$arrModule["template"]		= "/login.tpl";

		//Base-Class...
		parent::__construct($arrModule);

	}

	/**
	* @return void
	* @desc Waehlt die Methode der entsprechenden Aktion
	*/
	public function action($strAction = "") {
		if($strAction == "")
			$strAction = "login";
		$strReturn = "";

		if($strAction == "login")
			$strReturn = $this->actionLoginForm();
		elseif ($strAction == "adminLogout")
		    $strReturn = $this->actionAdminlogout();
		elseif ($strAction == "adminLogin")
		    $strReturn = $this->actionAdminLogin();

		$this->strTemp = $strReturn;
	}

	/**
	 * Creates a small login-field
	 *
	 * @return unknown
	 */
	private function actionLoginForm() {
		$strReturn = "";

		//Save the requested URL
		if($this->getParam("loginerror") == "")
		    $this->objSession->setSession("loginReferer", getServer("QUERY_STRING"));

		//Loading a small login-form
		$strTemplateID = $this->objTemplate->readTemplate("/elements.tpl", "login_form");
		$arrTemplate = array();
		$arrTemplate["action"] = _indexpath_."?admin=1&amp;module=login&amp;action=adminLogin";
		$strForm = "";
		$strForm .= $this->objToolkit->formHeader(_indexpath_."?admin=1&amp;module=login&amp;action=adminLogin");
		$strForm .= $this->objToolkit->formInputText("name", $this->getText("login_loginUser", "user"), "", "inputTextShort");
		$strForm .= $this->objToolkit->formInputPassword("passwort", $this->getText("login_loginPass", "user"), "", "inputTextShort");
		$strForm .= $this->objToolkit->formInputSubmit($this->getText("login_loginButton", "user"), "", "", "inputSubmitShort");
		$strForm .= $this->objToolkit->formClose();
		$arrTemplate["form"] = $strForm;
		$arrTemplate["loginTitle"] = $this->getText("login_loginTitle", "user");
		$arrTemplate["loginJsInfo"] = $this->getText("login_loginJsInfo", "user");
		$arrTemplate["loginCookiesInfo"] = $this->getText("login_loginCookiesInfo", "user");
		//An error occured?
		if($this->getParam("loginerror") == 1)
			$arrTemplate["error"] = $this->getText("login_loginError", "user");

		$strReturn = $this->objTemplate->fillTemplate($arrTemplate, $strTemplateID);


		return $strReturn;
	}

	public function getOutputContent() {
		return $this->strTemp;
	}

	public function getLoginStatus() {
		$strReturn = "";

		//Loading a small login-form
		$strTemplateID = $this->objTemplate->readTemplate("/elements.tpl", "logout_form");
		$arrTemplate = array();
		$arrTemplate["name"] = $this->objSession->getUsername();
		$arrTemplate["profile"] = _indexpath_."?admin=1&amp;module=user&amp;action=edit&amp;userid=".$this->objSession->getSession("userid");
		$arrTemplate["logout"] = _indexpath_."?admin=1&amp;module=login&amp;action=adminLogout";
		$arrTemplate["dashboard"] = _indexpath_."?admin=1&amp;module=dashboard";
		$arrTemplate["statusTitle"] = $this->getText("login_statusTitle", "user");
		$arrTemplate["profileTitle"] = $this->getText("login_profileTitle", "user");
		$arrTemplate["logoutTitle"] = $this->getText("login_logoutTitle", "user");
		$arrTemplate["dashboardTitle"] = $this->getText("login_dashboard", "user");

		$strReturn = $this->objTemplate->fillTemplate($arrTemplate, $strTemplateID);

		return $strReturn;
	}

	public function actionAdminLogin() {
        if($this->objSession->sessionIsset("status"))
			$this->objSession->setSession("status", "loggedout");

		$this->objSession->sessionUnset("username");
		$this->objSession->sessionUnset("userid");

		if($this->objSession->login($this->getParam("name"), $this->getParam("passwort"))) {
		    //user allowed to access admin?
		    if(!$this->objSession->isAdmin()) {
		        //no, reset session
		        $this->objSession->logout();
		    }
			//save the current skin as a cookie
            require_once(_systempath_."/class_cookie.php");
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
	 * Ends the session of the current user
	 *
	 */
	public function actionAdminlogout() {
		$this->objSession->logout();
		header("Location: "._indexpath_."?admin=1");
	}


} //class_login_admin()
?>