<?php
/*"******************************************************************************************************
*   (c) 2004-2006 by MulchProductions, www.mulchprod.de                                                 *
*   (c) 2007-2011 by Kajona, www.kajona.de                                                              *
*       Published under the GNU LGPL v2.1, see /system/licence_lgpl.txt                                 *
*-------------------------------------------------------------------------------------------------------*
*	$Id$                                 *
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

        if($this->getAction() == "list")
            $this->setAction("login");
	}

	
    /**
     * Logs the current user into the system
     * @return string
     */
	protected function actionLogin() {

		if($this->objSession->login($this->getParam("username"), $this->getParam("password"))) {
		    //user allowed to access admin?
		    if(!$this->objSession->isAdmin()) {
		        //no, reset session
		        $this->objSession->logout();
		    }

			return "<message><success>".xmlSafeString($this->getText("login_xml_succeess", "system"))."</success></message>";
		}
		else {
            header(class_http_statuscodes::$strSC_UNAUTHORIZED);
			return "<message><error>".xmlSafeString($this->getText("login_xml_error", "system"))."</error></message>";
		}
	}

    /**
	 * Ends the session of the current user
	 *
     * @return string
	 */
	protected function actionLogout() {
		$this->objSession->logout();
        return "<message><success>".xmlSafeString($this->getText("logout_xml", "system"))."</success></message>";
	}
    
    
    /**
     * Generates the wadl file for the current module
     * 
     * @xml
     */
    protected function actionWADL() {
        $objWadl = new class_wadlgenerator("admin", "login");
        $objWadl->addIncludeGrammars("message.xsd");
        
        $objWadl->addMethod(
                true, "login", 
                array(
                    array("username", "xsd:string", true),
                    array("password", "xsd:string", true)
                ), 
                array(), 
                array(
                    array("application/xml", "message")
                ));
        
        $objWadl->addMethod(true, "logout", array());
        return $objWadl->getDocument();
    }


}
?>