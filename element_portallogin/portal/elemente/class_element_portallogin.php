<?php
/*"******************************************************************************************************
*   (c) 2004-2006 by MulchProductions, www.mulchprod.de                                                 *
*   (c) 2007-2010 by Kajona, www.kajona.de                                                              *
*       Published under the GNU LGPL v2.1, see /system/licence_lgpl.txt                                 *
*-------------------------------------------------------------------------------------------------------*
*	$Id$                               *
********************************************************************************************************/

/**
 * Portal Element to load the login-form, or a small "status" area, providing an logout link
 *
 * @package modul_pages
 */
class class_element_portallogin extends class_element_portal implements interface_portal_element {

	/**
	 * Constructor
	 *
	 * @param mixed $arrElementData
	 */
	public function __construct($objElementData) {
        $arrModule = array();
		$arrModule["name"] 			= "element_portallogin";
		$arrModule["author"] 		= "sidler@mulchprod.de";
		$arrModule["moduleId"] 		= _pages_elemente_modul_id_;
		$arrModule["table"]		    = _dbprefix_."element_portallogin";
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

		if($this->getParam("action") == "portalLogin") {
		    if($this->doLogin()) {
		         if($this->arrElementData["portallogin_success"] != "") {
		             $this->portalReload(getLinkPortalHref($this->arrElementData["portallogin_success"]));
		         }
		         else {
		             $this->portalReload(getLinkPortalHref($this->getPagename()));
		         }
		    }
		    else {
                if($this->arrElementData["portallogin_error"] != "") {
		             $this->portalReload(getLinkPortalHref($this->arrElementData["portallogin_error"]));
                }
		    }
		}
		elseif ($this->getParam("action") == "portalLogout") {
		    $this->doLogout();
		    if($this->arrElementData["portallogin_logout_success"] != "") {
		        $this->portalReload(getLinkPortalHref($this->arrElementData["portallogin_logout_success"]));
            }
            else {
		        $this->portalReload(getLinkPortalHref($this->getPagename()));
		    }
		}


		if(!$this->objSession->isLoggedin()) {

            if($this->getAction() == "portalLoginReset") {
                $strReturn .= $this->resetForm();
            }
            elseif ($this->getAction() == "portalResetPwd") {
                $strReturn .= $this->newPwdForm();
            }
            else
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
     * Creates a form to enter the new password of the account to reset.
     *
     * @return string
     */
	private function newPwdForm() {
        $strReturn = "";

        if($this->getParam("reset") != "" && getPost("reset") != "") {
            //try to load the user
            
            
            $objUser = new class_modul_user_user($this->getParam("systemid"));
            if($objUser->getStrAuthcode() != "" && $objUser->getStrAuthcode() == $this->getParam("authcode") && $objUser->getStrUsername() != "") {
                //check the submitted passwords.
                $strPass1 = trim($this->getParam("portallogin_password1"));
                $strPass2 = trim($this->getParam("portallogin_password1"));

                if($strPass1 == $strPass2 && checkText($strPass1, 3, 200)) {

                    
                    $objUser->setStrPass($strPass1);
                    $objUser->setStrAuthcode("");
                    $objUser->updateObjectToDb();
                    
                    class_logger::getInstance()->addLogRow("changed password of user ".$objUser->getStrUsername(), class_logger::$levelInfo);

                    $strReturn .= $this->getText("resetSuccess");
                }
                else
                    $strReturn .= $this->getText("resetError");
            }
            else
                $strReturn .= $this->getText("resetError");


        }
        else {

            $strTemplateID = $this->objTemplate->readTemplate("/element_portallogin/".$this->arrElementData["portallogin_template"], "portallogin_newpwdform");
            $arrTemplate = array();

            //check sysid & authcode
            $objUser = new class_modul_user_user($this->getParam("systemid"));
            
            
            if($objUser->getStrAuthcode() != "" && $objUser->getStrAuthcode() == $this->getParam("authcode")) {
            
                $arrTemplate["portallogin_action"] = "portalResetPwd";
                $arrTemplate["portallogin_systemid"] = $this->getParam("systemid");
                $arrTemplate["portallogin_authcode"] = $this->getParam("authcode");
                $arrTemplate["portallogin_resetHint"] = "portalLoginReset";
                $arrTemplate["action"] = getLinkPortalHref($this->getPagename());
                $strReturn .= $this->fillTemplate($arrTemplate, $strTemplateID);

            }
            else
                $strReturn .= "Permission Error";
        }

		return $strReturn;
	}


    /**
     * Creates a form to enter the username of the account to reset.
     *
     * @return string
     */
	private function resetForm() {
        $strReturn = "";

        if($this->getParam("reset") != "" && getPost("reset") != "") {
            //try to load the user
            $arrUser = class_modul_user_user::getAllUsersByName($this->getParam("portallogin_username"), true);
            if(count($arrUser) == 1) {
                $objUser = $arrUser[0];

                if($objUser->getStrEmail() != "" && checkEmailaddress($objUser->getStrEmail()) && $objUser->getIntPortal() == 1) {

                    //generate an authcode and save it with the user
                    $strAuthcode = generateSystemid();
                    $objUser->setStrPass("");
                    $objUser->setStrAuthcode($strAuthcode);
                    $objUser->updateObjectToDb();


                    $strMailContent = $this->getText("resetemailBody");
                    $strTemp = getLinkPortalHref($this->getPagename(), "", "portalResetPwd", "systemid=".$objUser->getSystemid()."&authcode=".$strAuthcode);
                    $strMailContent .= html_entity_decode("<a href=\"".$strTemp."\">".$strTemp."</a>");

                    $this->objTemplate->setTemplate($strMailContent);
                    $this->objTemplate->fillConstants();
                    $this->objTemplate->deletePlaceholder();
                    $strMailContent = $this->objTemplate->getTemplate();

                    //create a mail confirming the change
                    $objEmail = new class_mail();
                    $objEmail->setSubject($this->getText("resetemailTitle"));
                    $objEmail->setHtml($strMailContent);
                    $objEmail->addTo($objUser->getStrEmail());

                    $objEmail->sendMail();

                    $strReturn .= $this->getText("resetMailSuccess");
                }
            }
            
        }
        else {

            $strTemplateID = $this->objTemplate->readTemplate("/element_portallogin/".$this->arrElementData["portallogin_template"], "portallogin_resetform");
            $arrTemplate = array();
            $arrTemplate["portallogin_action"] = "portalLoginReset";
            $arrTemplate["portallogin_resetHint"] = "portalLoginReset";
            $arrTemplate["action"] = getLinkPortalHref($this->getPagename());
            $strReturn .= $this->fillTemplate($arrTemplate, $strTemplateID);
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
	private function loginForm() {
        $strTemplateID = $this->objTemplate->readTemplate("/element_portallogin/".$this->arrElementData["portallogin_template"], "portallogin_loginform");

		$arrTemplate = array();
        $arrTemplate["portallogin_action"] = "portalLogin";

        $strPwdPage = $this->arrElementData["portallogin_pwdforgot"] != "" ? $this->arrElementData["portallogin_pwdforgot"]  : $this->getPagename();
        $arrTemplate["portallogin_forgotpwdlink"] = getLinkPortal($strPwdPage, "", "", $this->getText("pwdForgotLink"), "portalLoginReset");

		$arrTemplate["action"] = getLinkPortalHref($this->getPagename());
		return $this->fillTemplate($arrTemplate, $strTemplateID);
	}

	/**
	 * Creates a small status-area, providing a link to logout
	 *
	 * @return string
	 */
	private function statusArea() {
        $strTemplateID = $this->objTemplate->readTemplate("/element_portallogin/".$this->arrElementData["portallogin_template"], "portallogin_status");
        $arrTemplate = array();
        $arrTemplate["loggedin_label"] = $this->getText("loggedin_label");
        $arrTemplate["username"] = $this->objSession->getUsername();
        $arrTemplate["logoutlink"] = getLinkPortal($this->getPagename(), "", "", $this->getText("logoutlink"), "portalLogout");

        $strProfileeditpage = $this->getPagename();
        if($this->arrElementData["portallogin_profile"] != "")
            $strProfileeditpage = $this->arrElementData["portallogin_profile"];

        $arrTemplate["editprofilelink"] = getLinkPortal($strProfileeditpage, "", "", $this->getText("editprofilelink"), "portalEditProfile");
	    return $this->fillTemplate($arrTemplate, $strTemplateID);
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
    	    $strTemplateID = $this->objTemplate->readTemplate("/element_portallogin/".$this->arrElementData["portallogin_template"], "portallogin_userdataform");
            $arrTemplate = array();

            
            $objUser = new class_modul_user_user($this->objSession->getUserID());

            $arrTemplate["username"] = $objUser->getStrUsername();
            $arrTemplate["email"] = $objUser->getStrEmail();
            $arrTemplate["forename"] = $objUser->getStrForename();
            $arrTemplate["name"] = $objUser->getStrName();
            $arrTemplate["formaction"] = getLinkPortalHref($this->getPagename(), "", "portalEditProfile");

            $arrTemplate["formErrors"] = "";
            if(count($arrErrors) > 0) {
                foreach ($arrErrors as $strOneError) {
                    $strErrTemplate = $this->objTemplate->readTemplate("/element_portallogin/".$this->arrElementData["portallogin_template"], "errorRow");
                    $arrTemplate["formErrors"] .= "".$this->fillTemplate(array("error" => $strOneError), $strErrTemplate);
                }
            }

    	    return $this->fillTemplate($arrTemplate, $strTemplateID);
	    }
	    else {
            $objUser = new class_modul_user_user($this->objSession->getUserID());

            $objUser->setStrEmail($this->getParam("email"));
            $objUser->setStrForename($this->getParam("forename"));
            $objUser->setStrName($this->getParam("name"));
            $objUser->setStrPass($this->getParam("password"));

            $objUser->updateObjectToDb();
            $this->portalReload(getLinkPortalHref($this->getPagename()));

	    }
	}


    /**
     * Tries to log the user with the given credentials into the system.
     * To log in through the portal, the right "portal" has to be given!
     *
     * @return bool
     */
	private function doLogin() {
	    $strUsername = htmlToString($this->getParam("portallogin_username"), true);
	    $strPassword = htmlToString($this->getParam("portallogin_password"), true);

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