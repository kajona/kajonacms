<?php
/*"******************************************************************************************************
*   (c) 2004-2006 by MulchProductions, www.mulchprod.de                                                 *
*   (c) 2007-2009 by Kajona, www.kajona.de                                                              *
*       Published under the GNU LGPL v2.1, see /system/licence_lgpl.txt                                 *
*-------------------------------------------------------------------------------------------------------*
*	$Id$                        *
********************************************************************************************************/

//base-class
require_once(_portalpath_."/class_elemente_portal.php");
//Interface
require_once(_portalpath_."/interface_portal_element.php");

include_once(_systempath_."/class_modul_user_user.php");
include_once(_systempath_."/class_modul_user_group.php");

/**
 * Portal Element to allow users to register themself
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
        $arrModule = array();
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
			if($this->getParam("action") == "portalCompleteRegistration")
			    $strReturn .= $this->completeRegistration();
			else    
	            $strReturn = $this->editUserData();
		}
		else {
		    $strReturn = $this->getText("pr_errorLoggedin");
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
	       $objUser = new class_modul_user_user($this->getParam("systemid"));
	       
	       if($objUser->getStrEmail() != "") {
	           if($objUser->getIntActive() == 0 && $objUser->getIntLogins() == 0) {
	               $objUser->setIntActive(1);
	               $objUser->setStrPass("");
	               if($objUser->updateObjectToDb()) {
	                   $strReturn .= $this->getText("pr_completionSuccess");
	                   if($this->arrElementData["portalregistration_success"] != "")
	                       $this->portalReload(getLinkPortalHref($this->arrElementData["portalregistration_success"]));
	               }
	           }
	           else
	               $strReturn .= $this->getText("pr_completionErrorStatus");
	       }
	       else
	           $strReturn .= $this->getText("pr_completionErrorStatus");
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
               
		    //Check captachcode
	        if($this->getParam("form_captcha") == "" || $this->getParam("form_captcha") != $this->objSession->getCaptchaCode()) 
	            $arrErrors[] = $this->getText("pr_captcha");
                   
	        if(count($arrErrors) == 0)
               $bitForm = false;  
	    }
	    
	    if($bitForm) {
    	    $strTemplateID = $this->objTemplate->readTemplate("/element_portalregistration/".$this->arrElementData["portalregistration_template"], "portalregistration_userdataform");
            $arrTemplate = array();
            
            include_once(_systempath_."/class_modul_user_user.php");
            $objUser = new class_modul_user_user($this->objSession->getUserID());
            
            $arrTemplate["username"] = $this->getParam("username");
            $arrTemplate["email"] = $this->getParam("email");
            $arrTemplate["forename"] = $this->getParam("forename");
            $arrTemplate["name"] = $this->getParam("name");
            $arrTemplate["formaction"] = getLinkPortalHref($this->getPagename(), "", "portalCreateAccount");
            
            $arrTemplate["formErrors"] = "";
            if(count($arrErrors) > 0) {
                foreach ($arrErrors as $strOneError) {
                    $strErrTemplate = $this->objTemplate->readTemplate("/element_portalregistration/".$this->arrElementData["portalregistration_template"], "errorRow");
                    $arrTemplate["formErrors"] .= "".$this->fillTemplate(array("error" => $strOneError), $strErrTemplate);
                }
            }
    	    
    	    return $this->fillTemplate($arrTemplate, $strTemplateID);
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
	        	//group assignments
                class_modul_user_group::addUserToGroups($objUser,array($this->arrElementData["portalregistration_group"]));
	        	//create a mail to allow the user to activate itself
	        	
                $strMailContent = $this->getText("pr_email_body");
                $strTemp = getLinkPortalHref($this->getPagename(), "", "portalCompleteRegistration", "systemid=".$objUser->getSystemid());
                $strMailContent .= html_entity_decode("<a href=\"".$strTemp."\">".$strTemp."</a>");
                $strMailContent .= $this->getText("pr_email_footer");
                
                $this->objTemplate->setTemplate($strMailContent);
                $this->objTemplate->fillConstants();
                $this->objTemplate->deletePlaceholder();
                $strMailContent = $this->objTemplate->getTemplate();
	        	
                include_once(_systempath_."/class_mail.php");
                $objMail = new class_mail();
                $objMail->setSubject($this->getText("pr_email_subject"));
                $objMail->setHtml($strMailContent);
                $objMail->addTo($this->getParam("email"));
                
                $objMail->sendMail();
	        	
	        }
	        
	        
	        return $this->getText("pr_register_suc");
            
	    }
	}
	

}
?>