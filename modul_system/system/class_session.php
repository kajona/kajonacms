<?php
/*"******************************************************************************************************
*   (c) 2004-2006 by MulchProductions, www.mulchprod.de                                                 *
*   (c) 2007-2008 by Kajona, www.kajona.de                                                              *
*       Published under the GNU LGPL v2.1, see /system/licence_lgpl.txt                                 *
*-------------------------------------------------------------------------------------------------------*
* 																										*
* 	class_session.php																					*
* 	Session Management / Login/Logout Management														*
*																										*
*-------------------------------------------------------------------------------------------------------*
*	$Id$                                            *
********************************************************************************************************/

/**
 * Manages all those session stuff as logins or logouts and access to session vars
 *
 * @package modul_system
 */
final class class_session {

	private $arrModul;
	private $objDB;
	private $strKey;

	private static  $objSession = null;


	private function __construct() 	{
		$this->arrModul["name"] 		= "class_session";
		$this->arrModul["author"] 		= "sidler@mulchprod.de";

		//Loading the needed Objects
		$objCarrier = class_carrier::getInstance();
		$this->objDB = $objCarrier->getObjDB();

		//Generating a session-key
		$this->strKey = md5(_realpath_);

		if($this->sessionStart()) {
			if(!$this->sessionIsset("status"))
				$this->setSession("status", "loggedout");
		}
	}

	/**
	 * Returns one Instance of the Session-Object, using a singleton pattern
	 *
	 * @return class_session The Session-Object
	 */
	public static function getInstance() {
		if(self::$objSession == null) {
			self::$objSession = new class_session();
		}

		return self::$objSession;
	}

	/**
	 * Starts a session
	 *
	 * @return bool
	 */
	private function sessionStart() {
		//New seesion needes, oder using the already started?
		if(!session_id()) {
			if(session_start())
				$bitReturn = true;
			else
				$bitReturn = false;
		}
		else
			$bitReturn = true;

		return $bitReturn;
	}

	/**
	 * Writes a value to the session
	 *
	 * @param string $strKey
	 * @param string $strValue
	 * @return bool
	 */
	public function setSession($strKey, $strValue) 	{
		if($_SESSION[$this->strKey][$strKey] = $strValue)
			return true;
		else
			return false;
	}

	/**
	 * Setter for captcha-codes. use ONLY this method to set the code.
	 *
	 * @param string $strCode
	 */
	public function setCaptchaCode($strCode) {
	    $this->setSession("kajonaCaptchaCode", $strCode);
	}

	/**
	 * Returns the captcha code generated the last time.
	 * the code is being reset, so later requests will return a new systemid
	 * forcing the comparison to fail.
	 *
	 * @return string
	 */
	public function getCaptchaCode() {
	    $strCode = $this->getSession("kajonaCaptchaCode");
	    //reset code
	    $this->setSession("kajonaCaptchaCode", "");
	    if($strCode == "")
	       $strCode = generateSystemid();
	       
	    return $strCode;
	}

	/**
	 * Returns a value from the session
	 *
	 * @param string $strKey
	 * @return string
	 */
	public function getSession($strKey) {
		if(!isset($_SESSION[$this->strKey][$strKey]))
			return false;
		else
			return $_SESSION[$this->strKey][$strKey];
	}

	/**
	 * Checks if a key exists in the current session
	 *
	 * @param string $strKey
	 * @return bool
	 */
	public function sessionIsset($strKey) {
		if(isset($_SESSION[$this->strKey][$strKey]))
			return true;
		else
			return false;
	}

	/**
	 * Deletes a value from the session
	 *
	 * @param string $strKey
	 */
	public function sessionUnset($strKey) {
		if($this->sessionIsset($strKey))
			unset($_SESSION[$this->strKey][$strKey]);
	}

	/**
	 * Checks if the current user is logged in
	 *
	 * @return bool
	 */
	public function isLoggedin() {
		if($this->sessionIsset("status") && $this->getSession("status") == $this->getLoggedinKey())
			return true;
		else
			return false;
	}

	/**
	 * Cheks whether a user is an admin or not
	 *
	 * @return bool
	 */
	public function isAdmin() {
		if($this->isLoggedin()) {
		    include_once(_systempath_."/class_modul_user_user.php");
		    $objUser = new class_modul_user_user($this->getSession("userid"));

			if($objUser->getIntAdmin() == 1)
				return true;
			else
				return false;
		}
		else
			return false;
	}

	/**
	 * Returns the name of the current skin, if the user is logged in and admin
	 *
	 * @param bool $bitUseCookie
	 * @return string
	 */
	public function getAdminSkin($bitUseCookie = true) {
		$strReturn = "";

		//Maybe we can load the skin from the cookie
	    require_once(_systempath_."/class_cookie.php");
	    $objCookie = new class_cookie();
	    $strSkin = $objCookie->getCookie("adminskin");
	    if($strSkin != "" && $bitUseCookie) {
	        return $strSkin;
	    }

		if($this->isLoggedin()) {
			if($this->isAdmin()) {
			    include_once(_systempath_."/class_modul_user_user.php");
		        $objUser = new class_modul_user_user($this->getSession("userid"));

				if($objUser->getStrAdminskin() != "") {
					return $objUser->getStrAdminskin();
				}
			}
		}

		return _admin_skin_default_;
	}

	/**
	 * Returns the language the user set for the administration
	 * NOTE: THIS IS FOR THE TEXTS, NOT THE CONTENTS
	 *
	 * @param bool $bitUseCookie
	 * @return string
	 */
	public function getAdminLanguage($bitUseCookie = true) {
		$strReturn = "";

		//Maybe we can load the language from the cookie
	    require_once(_systempath_."/class_cookie.php");
	    $objCookie = new class_cookie();
	    $strLanguage = $objCookie->getCookie("adminlanguage");
	    if($strLanguage != "" && $bitUseCookie) {
	        return $strLanguage;
	    }

		if($this->isLoggedin()) {
			if($this->isAdmin()) {
			    include_once(_systempath_."/class_modul_user_user.php");
		        $objUser = new class_modul_user_user($this->getSession("userid"));

				if($objUser->getStrAdminlanguage() != "") {
					return $objUser->getStrAdminlanguage();
				}
			}
		}
		else {
		    //try to load a language the user requested
            $strUserLanguages = str_replace(";", ",", getServer("HTTP_ACCEPT_LANGUAGE"));
            if(uniStrlen($strUserLanguages) > 0) {
                $arrLanguages = explode(",", $strUserLanguages);
                //check, if one of the requested languages is available on our system
                foreach ($arrLanguages as $strOneLanguage) {
                    if(!preg_match("#q\=[0-9]\.[0-9]#i", $strOneLanguage)) {
                        if(in_array($strOneLanguage, $this->arrLanguages = explode(",", class_carrier::getInstance()->getObjConfig()->getConfig("adminlangs")))) {
                            return $strOneLanguage;
                        }
                    }
                }
            }
		}

		return "";
	}

	/**
	 * Checks if a user is allowed in portal or not
	 *
	 * @return bool
	 */
	public function isPortal() 	{
		if($this->isLoggedin()) {
		    include_once(_systempath_."/class_modul_user_user.php");
		    $objUser = new class_modul_user_user($this->getSession("userid"));

			if($objUser->getIntPortal() == 1)
				return true;
			else
				return false;

		}
		else
			return false;
	}

	/**
	 * Checks if a user is set active or not
	 *
	 * @return unknown
	 */
	public function isActive() {
		if($this->isLoggedin()) {
		    include_once(_systempath_."/class_modul_user_user.php");
		    $objUser = new class_modul_user_user($this->getSession("userid"));

			if($objUser->getIntActive() == 1)
				return true;
			else
				return false;
		}
		else
			return false;
	}



	/**
	 * Logs a user into the system if the credentials are correct
	 * and the user is active
	 *
	 * @param string $strName
	 * @param string $tsrPass
	 * @return bool
	 */
	public function login($strName, $strPass) {
	    $bitReturn = false;
	    include_once(_systempath_."/class_modul_user_user.php");
	    include_once(_systempath_."/class_modul_user_log.php");
		//How many users are out there with this username and being active?
		$arrUsers = class_modul_user_user::getAllUsersByName($strName);

		if(count($arrUsers) != 0) {
			foreach ($arrUsers as $objOneUser)  {
                //Revalidate username

				if($objOneUser->getStrUsername() == $strName) {
					if($this->checkPassword($strPass, $objOneUser->getStrPass())) {
						//Hit! User found, BUT: active?
						if($objOneUser->getIntActive() == 1) {
							$this->setSession("status", $this->getLoggedinKey());
							$this->setSession("userid", $objOneUser->getSystemid());
							$this->setSession("username", $strName);
							$objOneUser->setIntLogins($objOneUser->getIntLogins()+1);
							$objOneUser->setIntLastLogin(time());
							//Set pass = "" to avoid update conflicts
							$objOneUser->setStrPass("");
							//No htmlentitiesencoding here !!!
							$objOneUser->updateObjectToDb(false);

							//Drop a line to the logger
							class_logger::getInstance()->addLogRow("User: ".$strName." successfully logged in", class_logger::$levelInfo);
							class_modul_user_log::generateLog();

    						//Login successfull, quit
    						$bitReturn = true;
						    break;
						}
						else {
							//User is inactive
							$bitReturn = false;
							break;
						}
					}
				}
			}

		}
		else {
			//No matching username found
			$bitReturn = false;
		}

		if($bitReturn === false) {
		    class_logger::getInstance()->addLogRow("Unsuccessfull login attempt by user ".$strName, class_logger::$levelInfo);
		    class_modul_user_log::generateLog(0, $strName);
		}

		return $bitReturn;
	}

	/**
	 * Logs a user off from the system
	 *
	 */
	public function logout() {
	    class_logger::getInstance()->addLogRow("User: ".$this->getUsername()." successfully logged out", class_logger::$levelInfo);
		$this->setSession("status", "loggedout");
		$this->sessionUnset("userid");
		$this->sessionUnset("username");
		if (isset($_COOKIE[session_name()])) {
            setcookie(session_name(), '', time()-42000);
        }
        // Finally, destroy the session.
		session_destroy();
		//start a new one
		$this->sessionStart();
		//and create a new sessid
		session_regenerate_id();

		return;
	}

	/**
	 * Returns the name of the current user
	 *
	 * @return string
	 */
	public function getUsername() {
		if($this->isLoggedin()) {
			$strUsername = $this->getSession("username");
		}
		else {
			$strUsername = "Guest";
		}
		return $strUsername;
	}

	/**
	 * Returns the userid or '' in case of guest of the current user
	 *
	 * @return string
	 */
	public function getUserID() {
		if($this->isLoggedin()) {
			$strUserid = $this->getSession("userid");
		}
		else {
			$strUserid = "";
		}
		return $strUserid;
	}

	/**
	 * Returns the current Session-ID used by php
	 *
	 * @return string
	 */
	public function getSessionId() {
	    return session_id();
	}

	/**
	 * Generates the key to identify a user as being logged in
	 *
	 * @return string
	 */
	public function getLoggedinKey() {
	    //Logged-in key is user-specific.
	    //To avoid session-stealing, use a ip-dependant key
	    //include the systems-module-id to generate a system-dependant key
	    $strAddKey = "";
        try {
        	include_once(_systempath_."/class_modul_system_module.php");
            $objModule = class_modul_system_module::getModuleByName("system", true);
            $strAddKey = $objModule->getSystemid();
        }
        catch (class_exception $objException) {
        }
        
	    $strKey = md5(_systempath_."loggedin".getServer("REMOTE_ADDR").$strAddKey);
	    return $strKey;
	}

	/**
	 * Encrypts a password using the current hashing-algorithm
	 *
	 * @param string $strPassword
	 * @return string
	 */
	public function encryptPassword($strPassword) {
	    return sha1($strPassword);
	}

	/**
	 * Validates a password. Takes a plaintext password and an encrypted one and compares them.
	 * The functions takes care of using the correct hashing-algorithm
	 *
	 * @param string $strPlainPassword
	 * @param string $strEncryptedPassword
	 * @return bool
	 */
	private function checkPassword($strPlainPassword, $strEncryptedPassword) {
	    //md5
        if((int)strlen($strEncryptedPassword) == 32)
            return $strEncryptedPassword == md5($strPlainPassword);

        //sha1
        if((int)strlen($strEncryptedPassword) == 40)
            return $strEncryptedPassword == sha1($strPlainPassword);

	    return false;
	}

}