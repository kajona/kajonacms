<?php
/*"******************************************************************************************************
*   (c) 2004-2006 by MulchProductions, www.mulchprod.de                                                 *
*   (c) 2007-2011 by Kajona, www.kajona.de                                                              *
*       Published under the GNU LGPL v2.1, see /system/licence_lgpl.txt                                 *
*-------------------------------------------------------------------------------------------------------*
*	$Id$                                            *
********************************************************************************************************/

/**
 * Manages all those session stuff as logins or logouts and access to session vars
 *
 * @package module_system
 * @author sidler@mulchprod.de
 */
final class class_session {

	private $objDB;
	private $strKey;

	private $arrRequestArray;

	public static $intScopeSession = 1;
	public static $intScopeRequest = 2;

	private static $objSession = null;
    private $bitLazyLoaded = false;

	/**
	 * Instance of internal kajona-session
	 *
	 * @var class_modul_system_session
	 */
	private $objInternalSession = null;

    /**
     *
     * @var class_modul_user_user
     */
	private $objUser = null;


	private function __construct() 	{

		//Loading the needed Objects
		$this->objDB = class_db::getInstance();

		//Generating a session-key using a few characteristic values
		$this->strKey = md5(_realpath_.getServer("REMOTE_ADDR"));
		$this->sessionStart();
		$this->arrRequestArray = array();
	}

	/**
	 * Returns one instance of the Session-Object, using a singleton pattern
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
		//New session needed or using the already started one?
		if(!session_id()) {
			if(@session_start())
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
	 * @param int $intSessionScope one of class_session::$intScopeRequest or class_session::$intScopeSession
	 * @return bool
	 */
	public function setSession($strKey, $strValue, $intSessionScope = 1) 	{

		if($intSessionScope == class_session::$intScopeRequest) {
		    $this->arrRequestArray[$strKey] = $strValue;
		    return true;
		}
		else {
            //yes, it is wanted to have only one =. The condition checks the assignment.
			if($_SESSION[$this->strKey][$strKey] = $strValue)
				return true;
			else
				return false;
		}
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
	 * @param int $intScope one of class_session::$intScopeRequest or class_session::$intScopeSession
	 * @return string
	 */
	public function getSession($strKey, $intScope = 1) {
		if($intScope == class_session::$intScopeRequest) {
			if(!isset($this->arrRequestArray[$strKey]))
			    return false;
			else
    			return $this->arrRequestArray[$strKey];
		}
		else {
			if(!isset($_SESSION[$this->strKey][$strKey]))
				return false;
			else
				return $_SESSION[$this->strKey][$strKey];
		}
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
	 *
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
		if($this->getObjInternalSession() != null)
		    return $this->getObjInternalSession()->isLoggedIn();
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
			if($this->getUser() != null && $this->getUser()->getIntAdmin() == 1)
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
		//Maybe we can load the skin from the cookie
	    $objCookie = new class_cookie();
	    $strSkin = $objCookie->getCookie("adminskin");
	    if($strSkin != "" && $bitUseCookie) {
	        return $strSkin;
	    }

		if($this->isLoggedin()) {
			if($this->isAdmin()) {
				if($this->getUser() != null && $this->getUser()->getStrAdminskin() != "") {
					return $this->getUser()->getStrAdminskin();
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
		//Maybe we can load the language from the cookie
	    $objCookie = new class_cookie();
	    $strLanguage = $objCookie->getCookie("adminlanguage");
	    if($strLanguage != "" && $bitUseCookie) {
	        return $strLanguage;
	    }

		if($this->isLoggedin()) {
			if($this->isAdmin()) {
				if($this->getUser() != null && $this->getUser()->getStrAdminlanguage() != "") {
					return $this->getUser()->getStrAdminlanguage();
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
                        if(in_array($strOneLanguage, explode(",", class_carrier::getInstance()->getObjConfig()->getConfig("adminlangs")))) {
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
			if($this->getUser() != null && $this->getUser()->getIntPortal() == 1)
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
			if($this->getUser() && $this->getUser()->getIntActive() == 1)
				return true;
			else
				return false;
		}
		else
			return false;
	}


    /**
     * Tries to log a user into the system.
     * In normal cases, you'd rather user the method class_session::login($strName, $strPass).
     * This method is only useful if you have a concrete user object and want to make this user the
     * currently active one.
     *
     * @param class_modul_user_user $objUser
     * @see class_session::login($strName, $strPass)
     * @return bool
     */
    public function loginUser(class_modul_user_user $objUser) {
        return $this->internalLoginHelper($objUser);
    }


	/**
	 * Logs a user into the system if the credentials are correct
	 * and the user is active
	 *
	 * @param string $strName
	 * @param string $strPassword
	 * @return bool
	 */
	public function login($strName, $strPassword) {
	    $bitReturn = false;
		//How many users are out there with this username and being active?
        $objUsersources = new class_modul_user_sourcefactory();
        try {
            if($objUsersources->authenticateUser($strName, $strPassword)) {
                $objUser = $objUsersources->getUserByUsername($strName);
                $bitReturn = $this->internalLoginHelper($objUser);
            }
        }
        catch(class_authentication_exception $objEx) {
            $bitReturn = false;
        }


		if($bitReturn === false) {
		    class_logger::getInstance()->addLogRow("Unsuccessfull login attempt by user ".$strName, class_logger::$levelInfo);
		    class_modul_user_log::generateLog(0, $strName);
		}

		return $bitReturn;
	}

    /**
     * Does all the internal login-handling
     *
     * @param class_modul_user_user $objUser
     * @return bool
     */
    private function internalLoginHelper(class_modul_user_user $objUser) {

        $bitReturn = false;

        if($objUser->getIntActive() == 1) {

            $objUser->setIntLogins($objUser->getIntLogins()+1);
            $objUser->setIntLastLogin(time());
            $objUser->updateObjectToDb();

            $this->getObjInternalSession()->setStrLoginstatus(class_modul_system_session::$LOGINSTATUS_LOGGEDIN);
            $this->getObjInternalSession()->setStrUserid($objUser->getSystemid());

            $strGroups = implode(",", $objUser->getArrGroupIds());
            $this->getObjInternalSession()->setStrGroupids($strGroups);
            $this->getObjInternalSession()->updateObjectToDb();
            $this->objUser = $objUser;

            //Drop a line to the logger
            class_logger::getInstance()->addLogRow("User: ".$objUser->getStrUsername()." successfully logged in, login provider: ".$objUser->getStrSubsystem(), class_logger::$levelInfo);
            class_modul_user_log::generateLog();

            //right now we have the time to do a few cleanups...
            class_modul_system_session::deleteInvalidSessions();

            //Login successfull, quit
            $bitReturn = true;
        }
        else {
            //User is inactive
            $bitReturn = false;
        }

        return $bitReturn;
    }

	/**
	 * Logs a user off from the system
	 *
	 */
	public function logout() {
	    class_logger::getInstance()->addLogRow("User: ".$this->getUsername()." successfully logged out", class_logger::$levelInfo);

	    $this->getObjInternalSession()->setStrLoginstatus(class_modul_system_session::$LOGINSTATUS_LOGGEDOUT);
	    $this->getObjInternalSession()->updateObjectToDb();
	    $this->getObjInternalSession()->deleteObject();
	    $this->objInternalSession = null;
	    $this->objUser = null;
		if (isset($_COOKIE[session_name()])) {
            setcookie(session_name(), '', time()-42000);
        }
        // Finally, destroy the session.
		session_destroy();
		//start a new one
		$this->sessionStart();
		//and create a new sessid
		session_regenerate_id();
        $this->initInternalSession();
		return;
	}

	/**
	 * Returns the name of the current user
	 *
	 * @return string
	 */
	public function getUsername() {
        $strUsername = "";
		if($this->isLoggedin() && $this->getObjInternalSession() != null) {
			$strUsername = $this->getUser()->getStrUsername();
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
        $strUserid = "";
		if($this->getObjInternalSession() != null && $this->isLoggedin()) {
			$strUserid = $this->getObjInternalSession()->getStrUserid();
		}
		else {
			$strUserid = "";
		}
		return $strUserid;
	}

	/**
	 * Returns an instance of the current user or null of not given
	 *
	 * @return class_modul_user_user
	 */
	private function getUser() {
	    if($this->objUser != null)
	       return $this->objUser;

	    if($this->getUserID() != "") {
	       $this->objUser = new class_modul_user_user($this->getUserID());
	       return $this->objUser;
	    }

	    return null;
	}

	/**
	 * Resets the internal reference to the current user, e.g. to load new values from the database
	 */
	public function resetUser() {
	    if($this->getUserID() != "") {
           $this->objUser = new class_modul_user_user($this->getUserID());
        }
	}

	/**
	 * Returns the groups the user is member in as a string
	 *
	 * @return string
	 */
	public function getGroupIdsAsString() {
        $strGroupids = "";
	    if($this->getObjInternalSession() != null ) {
			$strGroupids = $this->getObjInternalSession()->getStrGroupids();
		}
		else {
			$strGroupids = _guests_group_id_;
		}
		return $strGroupids;
	}

	/**
	 * Returns the groups the user is member in as an array
	 *
	 * @return array
	 */
	public function getGroupIdsAsArray() {
        $strGroupids = "";
	    if($this->getObjInternalSession() != null ) {
			$strGroupids = $this->getObjInternalSession()->getStrGroupids();
		}
		else {
			$strGroupids = _guests_group_id_;
		}
		return explode(",", $strGroupids);
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
     * Returns the internal session id used by kajona, so NOT by php
     *
     * @return string
     */
    public function getInternalSessionId() {
        if($this->getObjInternalSession() != null)
            return $this->getObjInternalSession()->getSystemid();
        else
            return $this->getSessionId();
    }

	/**
	 * Initializes the internal kajona session
	 *
	 */
	public function initInternalSession() {

        $this->bitLazyLoaded = true;

	    $arrTables = $this->objDB->getTables();
        if(!in_array(_dbprefix_."session", $arrTables))
            return;

	    if($this->getSession("KAJONA_INTERNAL_SESSID") !== false) {
	        $this->objInternalSession = class_modul_system_session::getSessionById($this->getSession("KAJONA_INTERNAL_SESSID"));

	        if($this->objInternalSession!= null && $this->objInternalSession->isSessionValid()) {
    	        $this->objInternalSession->setIntReleasetime(time()+_system_release_time_);
    	        $this->objInternalSession->setStrLasturl(getServer("QUERY_STRING"));
    	        $this->objInternalSession->updateObjectToDb();
	        }
	        else
	           $this->objInternalSession = null;

	        if($this->objInternalSession != null)
	            return;

	    }

        //try to load the matching groups
        $strGroups = _guests_group_id_;
        if(validateSystemid($this->getUserID())) {
            $this->objUser = new class_modul_user_user($this->getUserID());
            $strGroups = implode(",", $this->objUser->getArrGroupIds() );
        }

        $objSession = new class_modul_system_session();
        $objSession->setStrPHPSessionId($this->getSessionId());
        $objSession->setStrUserid($this->getUserID());
        $objSession->setStrGroupids($strGroups);
        $objSession->setIntReleasetime(time()+_system_release_time_);
        $objSession->setStrLasturl(getServer("QUERY_STRING"));
        $objSession->updateObjectToDb();

        $this->setSession("KAJONA_INTERNAL_SESSID", $objSession->getSystemid());
        $this->objInternalSession = $objSession;

	}

    private function getObjInternalSession() {

        //lazy loading
        if($this->objInternalSession == null && !$this->bitLazyLoaded)
            $this->initInternalSession();

        return $this->objInternalSession;
    }

    public function getBitLazyLoaded() {
        return $this->bitLazyLoaded;
    }



}


?>