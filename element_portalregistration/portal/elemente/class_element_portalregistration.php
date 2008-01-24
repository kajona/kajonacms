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
*	$Id: class_element_portalregistration.php 1929 2008-01-24 13:46:34Z sidler $                               *
********************************************************************************************************/

//base-class
require_once(_portalpath_."/class_elemente_portal.php");
//Interface
require_once(_portalpath_."/interface_portal_element.php");

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
     * Notice: In case of success, a location-header is sent, too. Needed, caus otherwise the rights would not
     * be checked during the login/-logout-loading against the new user-id!
     *
     * @return string the prepared html-output
     */
	public function loadData() {
		$strReturn = "";

		if($this->getParam("action") == "portalregistration") {
		    if($this->doLogin()) {
		         if($this->arrElementData["portalregistration_success"] != "") {
		             header("Location: "._indexpath_."?page=".$this->arrElementData["portalregistration_success"]);
		         }
		         else {
		             header("Location: "._indexpath_."?page=".$this->getPagename());
		         }
		    }
		    else {
                if($this->arrElementData["portalregistration_error"] != "") {
		             header("Location: "._indexpath_."?page=".$this->arrElementData["portalregistration_error"]);
                }
		    }
		}
		elseif ($this->getParam("action") == "portalLogout") {
		    $this->doLogout();
		    if($this->arrElementData["portalregistration_logout_success"] != "") {
		        header("Location: "._indexpath_."?page=".$this->arrElementData["portalregistration_logout_success"]);
            }
            else {
		        header("Location: "._indexpath_."?page=".$this->getPagename());
		    }
		}
		

		if(!$this->objSession->isLoggedin()) {
	        $strReturn .= $this->loginForm();
		}
		else {
		    if($this->getParam("action") == "portalEditProfile")
		        $strReturn .= $this->editUserData();
		    else
		        $strReturn .= $this->statusArea();
		}



		return $strReturn;
	}


    /**
     * Creates a form to login
     * The template has to provide at least the following html-input-elements:
     * portalregistration_username, portalregistration_password, action (hidden)
     *
     * @return string
     */
	private function loginForm() {
        $strTemplateID = $this->objTemplate->readTemplate("/element_portalregistration/".$this->arrElementData["portalregistration_template"], "portalregistration_loginform");

		$arrTemplate = array();
        $arrTemplate["username"] = $this->getText("username");
        $arrTemplate["password"] = $this->getText("password");
        $arrTemplate["login"] = $this->getText("login");
        $arrTemplate["portalregistration_action"] = "portalregistration";

		$arrTemplate["action"] = _indexpath_."?page=".$this->getPagename()."";
		return $this->objTemplate->fillTemplate($arrTemplate, $strTemplateID);
	}

	/**
	 * Creates a small status-area, providing a link to logout
	 *
	 * @return string
	 */
	private function statusArea() {
        $strTemplateID = $this->objTemplate->readTemplate("/element_portalregistration/".$this->arrElementData["portalregistration_template"], "portalregistration_status");
        $arrTemplate = array();
        $arrTemplate["username"] = $this->objSession->getUsername();
        $arrTemplate["logoutlink"] = getLinkPortal($this->getPagename(), "", "", $this->getText("logoutlink"), "portalLogout");
        $arrTemplate["editprofilelink"] = getLinkPortal($this->getPagename(), "", "", $this->getText("editprofilelink"), "portalEditProfile");
	    return $this->objTemplate->fillTemplate($arrTemplate, $strTemplateID);
	}
	
	
	/**
	 * Creates a form to edit a users data
	 *
	 * @return string
	 */
	private function editUserData() {
	    
	    $arrErrors = array();
	    $bitForm = true;
	    //what to do?
	    if($this->getParam("submitUserForm") != "") {
	        if($this->getParam("password") != "") {
	            if($this->getParam("password") != $this->getParam("password2"))
	               $arrErrors[] = $this->getText("passwordsUnequal");
	        }
	        
	        if(!checkEmailaddress($this->getParam("email")))
               $arrErrors[] = $this->getText("invalidEmailadress");
                   
	        if(count($arrErrors) == 0)
               $bitForm = false;  
	    }
	    
	    if($bitForm) {
    	    $strTemplateID = $this->objTemplate->readTemplate("/element_portalregistration/".$this->arrElementData["portalregistration_template"], "portalregistration_userdataform");
            $arrTemplate = array();
            
            include_once(_systempath_."/class_modul_user_user.php");
            $objUser = new class_modul_user_user($this->objSession->getUserID());
            
            $arrTemplate["usernameTitle"]= $this->getText("usernameTitle");
            $arrTemplate["username"] = $objUser->getStrUsername();
            $arrTemplate["passwordTitle"] = $this->getText("passwordTitle");
            $arrTemplate["passwordTitle2"] = $this->getText("passwordTitle2");
            $arrTemplate["emailTitle"] = $this->getText("emailTitle");
            $arrTemplate["email"] = $objUser->getStrEmail();
            $arrTemplate["forenameTitle"] = $this->getText("forenameTitle");
            $arrTemplate["forename"] = $objUser->getStrForename();
            $arrTemplate["nameTitle"] = $this->getText("nameTitle");
            $arrTemplate["name"] = $objUser->getStrName();
            
            
            $arrTemplate["submitTitle"] = $this->getText("userDataSubmit");
            $arrTemplate["formaction"] = _indexpath_."?page=".$this->getPagename()."&amp;action=portalEditProfile";
            
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
	        include_once(_systempath_."/class_modul_user_user.php");
            $objUser = new class_modul_user_user($this->objSession->getUserID());

            $objUser->setStrEmail($this->getParam("email"));
            $objUser->setStrForename($this->getParam("forename"));
            $objUser->setStrName($this->getParam("name"));
            $objUser->setStrPass($this->getParam("password"));
            
            $objUser->updateObjectToDb();
            header("Location: "._indexpath_."?page=".$this->getPagename());
            
	    }
	}
	

    /**
     * Tries to log the user with the given credentials into the system.
     * To log in through the portal, the right "portal" has to be given!
     *
     * @return bool
     */
	private function doLogin() {
	    $strUsername = htmlToString($this->getParam("portalregistration_username"), true);
	    $strPassword = htmlToString($this->getParam("portalregistration_password"), true);

	    if($this->objSession->login($strUsername, $strPassword)) {
	        if(!$this->objSession->isPortal()) {
	            $this->objSession->logout();
	            return false;
	        }
	        else
	           return true;
	    }
	    return false;
	}


	/**
	 * Logs the user off the system
	 *
	 */
	private function doLogout() {
        $this->objSession->logout();
	}

}
?>