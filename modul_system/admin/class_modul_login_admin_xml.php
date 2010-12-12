<?php
/*"******************************************************************************************************
*   (c) 2004-2006 by MulchProductions, www.mulchprod.de                                                 *
*   (c) 2007-2010 by Kajona, www.kajona.de                                                              *
*       Published under the GNU LGPL v2.1, see /system/licence_lgpl.txt                                 *
*-------------------------------------------------------------------------------------------------------*
*	$Id: class_modul_login_admin.php 3490 2010-12-02 18:52:16Z sidler $                                 *
********************************************************************************************************/

/**
 * The login-xml part is able to fire logins or logouts via the xml-interface (e.g. to be used by a REST client).
 * In order to login, create a request schemed like xml.php?admin=1&module=login&action=login
 * Attach the params username, password either via GET params, or even better by POST params).
 *
 * @package modul_system
 */
class class_modul_login_admin_xml extends class_admin implements interface_xml_admin  {

	public function __construct() {
        $arrModule = array();
		$arrModule["name"] 			= "modul_user";
		$arrModule["author"] 		= "sidler@mulchprod.de";
		$arrModule["moduleId"] 		= _user_modul_id_;
		$arrModule["modul"]			= "login";
        
		parent::__construct($arrModule);

	}

	/**
     *
     * @param string $strAction
     * @return string
     */
	public function action($strAction = "") {
		if($strAction == "")
			$strAction = "login";
		$strReturn = "";

		if($strAction == "login")
			$strReturn = $this->actionLogin();
		elseif ($strAction == "logout")
		    $strReturn = $this->actionLogout();

		return $strReturn;
	}

	
    /**
     * Logs the current user into the system
     * @return string
     */
	private function actionLogin() {

		if($this->objSession->login($this->getParam("username"), $this->getParam("password"))) {
		    //user allowed to access admin?
		    if(!$this->objSession->isAdmin()) {
		        //no, reset session
		        $this->objSession->logout();
		    }

			return "<message><success>".xmlSafeString($this->getText("login_xml_succeess", "system"))."</success></message>";
		}
		else {
			return "<error>".xmlSafeString($this->getText("login_xml_error", "system"))."</error>";
		}
	}

    /**
	 * Ends the session of the current user
	 *
     * @return string
	 */
	private function actionLogout() {
		$this->objSession->logout();
        return "<message>".xmlSafeString($this->getText("logout_xml", "system"))."</message>";
	}


}
?>