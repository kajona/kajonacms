<?php
/*"******************************************************************************************************
*   (c) 2004-2006 by MulchProductions, www.mulchprod.de                                                 *
*   (c) 2007-2008 by Kajona, www.kajona.de                                                              *
*       Published under the GNU LGPL v2.1, see /system/licence_lgpl.txt                                 *
*-------------------------------------------------------------------------------------------------------*
* 																										*
* 	class_element_formular.php																			*
* 	Portal-class of the portallogin element															    *
*-------------------------------------------------------------------------------------------------------*
*	$Id: class_element_portalregistration.php 1929 2008-01-24 13:46:34Z sidler $                        *
********************************************************************************************************/

//base-class
require_once(_portalpath_."/class_elemente_portal.php");
//Interface
require_once(_portalpath_."/interface_portal_element.php");

include_once(_systempath_."/class_modul_user_user.php");

/**
 * Portal Element to load the login-form, or a small "status" area, providing an logout link
 *
 * @package modul_pages
 */
class class_element_portalregistration extends class_element_portal implements interface_portal_element {

	/**
	 * Constructor
	 *
	 * @param mixed $arrElementData
	 */
	public function __construct($objElementData) {
		$arrModule["name"] 			= "element_portalregistration";
		$arrModule["author"] 		= "sidler@mulchprod.de";
		$arrModule["moduleId"] 		= _pages_elemente_modul_id_;
		$arrModule["table"]		    = _dbprefix_."element_portalregistration";
		$arrModule["modul"]		    = "elemente";

		parent::__construct($arrModule, $objElementData);
	}

    /**
     * Checks what to do and invokes the proper method
     *
     * @return string the prepared html-output
     */
	public function loadData() {
		$strReturn = "";

		if(!$this->objSession->isLoggedin()) {
	        $strReturn = $this->editUserData();
		}
		else {
		    $strReturn = $this->getText("portalregistration_errorLoggedin");
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
	    	
	    	
	        if($this->getParam("password") == "" || $this->getParam("password") != $this->getParam("password2"))
	            $arrErrors[] = $this->getText("pr_passwordsUnequal");
	        
	        if(!checkText($this->getParam("username"), 3))
	            $arrErrors[] = $this->getText("pr_noUsername");
	        
	        //username already existing?
	        if(checkText($this->getParam("username"), 3) && count(class_modul_user_user::getAllUsersByName($this->getParam("username"), false)) > 0) 
	            $arrErrors[] = $this->getText("pr_usernameGiven");	
	        
	        if(!checkEmailaddress($this->getParam("email")))
               $arrErrors[] = $this->getText("pr_invalidEmailadress");
                   
	        if(count($arrErrors) == 0)
               $bitForm = false;  
	    }
	    
	    if($bitForm) {
    	    $strTemplateID = $this->objTemplate->readTemplate("/element_portalregistration/".$this->arrElementData["portalregistration_template"], "portalregistration_userdataform");
            $arrTemplate = array();
            
            include_once(_systempath_."/class_modul_user_user.php");
            $objUser = new class_modul_user_user($this->objSession->getUserID());
            
            $arrTemplate["usernameTitle"]= $this->getText("pr_usernameTitle");
            $arrTemplate["username"] = $this->getParam("username");
            $arrTemplate["passwordTitle"] = $this->getText("pr_passwordTitle");
            $arrTemplate["passwordTitle2"] = $this->getText("pr_passwordTitle2");
            $arrTemplate["emailTitle"] = $this->getText("pr_emailTitle");
            $arrTemplate["email"] = $this->getParam("email");
            $arrTemplate["forenameTitle"] = $this->getText("pr_forenameTitle");
            $arrTemplate["forename"] = $this->getParam("forename");
            $arrTemplate["nameTitle"] = $this->getText("pr_nameTitle");
            $arrTemplate["name"] = $this->getParam("name");
            
            
            $arrTemplate["submitTitle"] = $this->getText("pr_userDataSubmit");
            $arrTemplate["formaction"] = _indexpath_."?page=".$this->getPagename()."&amp;action=portalCreateAccount";
            
            $arrTemplate["formErrors"] = "";
            if(count($arrErrors) > 0) {
                foreach ($arrErrors as $strOneError) {
                    $strErrTemplate = $this->objTemplate->readTemplate("/element_portalregistration/".$this->arrElementData["portalregistration_template"], "errorRow");
                    $arrTemplate["formErrors"] .= "".$this->objTemplate->fillTemplate(array("error" => $strOneError), $strErrTemplate);
                }
            }
    	    
    	    return $this->objTemplate->fillTemplate($arrTemplate, $strTemplateID);
	    }
	    else {
	        //create new user, inactive
	        $objUser = new class_modul_user_user();
	        $objUser->setStrUsername($this->getParam("username"));
	        $objUser->setStrEmail($this->getParam("email"));
	        $objUser->setStrForename($this->getParam("forename"));
	        $objUser->setStrName($this->getParam("name"));
	        $objUser->setStrPass($this->getParam("password"));
	        $objUser->setIntActive(0);
	        $objUser->setIntAdmin(0);
	        $objUser->setIntPortal(1);
	        
	        if($objUser->saveObjectToDb()) {
	        	
	        }
            
	    }
	}
	

}
?>