<?php
/*"******************************************************************************************************
*   (c) 2004-2006 by MulchProductions, www.mulchprod.de                                                 *
*   (c) 2007-2011 by Kajona, www.kajona.de                                                              *
*       Published under the GNU LGPL v2.1, see /system/licence_lgpl.txt                                 *
*-------------------------------------------------------------------------------------------------------*
*	$Id$                                   *
********************************************************************************************************/

/**
 * Model for a user
 * Note: Users do not use the classical system-id relation, so no entry in the system-table
 *
 * @package modul_user
 * @author sidler@mulchprod.de
 */
class class_modul_user_user extends class_model implements interface_model  {

    private $strUsername = "";
    private $strPass = "";
    private $strEmail = "";
    private $strForename = "";
    private $strName = "";
    private $strStreet = "";
    private $strPostal = "";
    private $strCity = "";
    private $strTel = "";
    private $strMobile = "";
    private $longDate = 0;
    private $intLogins = 0;
    private $intLastlogin = 0;
    private $intActive = 0;
    private $intAdmin = 0;
    private $intPortal = 0;
    private $strAdminskin = "";
    private $strAdminlanguage = "";
    private $strAuthcode = "";


    /**
     * Constructor to create a valid object
     *
     * @param string $strSystemid (use "" on new objects)
     * @param bool $bitLoadPassword
     */
    public function __construct($strSystemid = "", $bitLoadPassword = false) {
        $arrModul = array();
        $arrModul["name"] 				= "modul_user";
		$arrModul["moduleId"] 			= _user_modul_id_;
		$arrModul["table"]       		= _dbprefix_."user";
		$arrModul["modul"]				= "user";

		//base class
		parent::__construct($arrModul, $strSystemid);

		//init current object
		if($strSystemid != "")
		    $this->initObject($bitLoadPassword);
    }

    /**
     * Initialises the current object, if a systemid was given
     *
     * @param bool $bitPassword Should the password be loaded, too?
     */
    public function initObject($bitPassword = false) {
        $strQuery = "SELECT * FROM ".$this->objDB->dbsafeString($this->arrModule["table"])." WHERE user_id=?";
        $arrRow = $this->objDB->getPRow($strQuery, array($this->getSystemid()));

        if(count($arrRow) > 0) {
            $this->setStrUsername($arrRow["user_username"]);
            // Excluded due to update-problems
            if($bitPassword)
                $this->setStrPass($arrRow["user_pass"]);


            $this->setStrEmail($arrRow["user_email"]);
            $this->setStrForename($arrRow["user_forename"]);
            $this->setStrName($arrRow["user_name"]);
            $this->setStrStreet($arrRow["user_street"]);
            $this->setStrPostal($arrRow["user_postal"]);
            $this->setStrCity($arrRow["user_city"]);
            $this->setStrTel($arrRow["user_tel"]);
            $this->setStrMobile($arrRow["user_mobile"]);
            $this->setLongDate($arrRow["user_date"]);
            $this->setIntLogins($arrRow["user_logins"]);
            $this->setIntLastLogin($arrRow["user_lastlogin"]);
            $this->setIntActive($arrRow["user_active"]);
            $this->setIntAdmin($arrRow["user_admin"]);
            $this->setIntPortal($arrRow["user_portal"]);
            $this->setStrAdminskin($arrRow["user_admin_skin"]);
            $this->setStrAdminlanguage($arrRow["user_admin_language"]);
            $this->setSystemid($arrRow["user_id"]);
            $this->setStrAuthcode($arrRow["user_authcode"]);
        }
    }

    /**
     * Updates the current object to the database
     * <b>ATTENTION</b> If you don't want to update the password, set it to "" before!
     *
     * @return bool
     */
    public function updateObjectToDb($bitHtmlEntities = true) {

        if($this->getSystemid() == "") {
            $strUserid = generateSystemid();
            $this->setSystemid($strUserid);
            $strQuery = "INSERT INTO "._dbprefix_."user (
                        user_id, user_username,
                        user_pass, user_email, user_forename,
                        user_name, 	user_street,
                        user_postal, user_city,
                        user_tel, user_mobile,
                        user_date, user_active,
                        user_admin, user_portal,
                        user_admin_skin, user_admin_language,
                        user_logins, user_lastlogin, user_authcode

                        ) VALUES (?,?,?,?,?,?,?,?,?,?,?,?,?,?,?,?,?,?,?,?)";

            class_logger::getInstance()->addLogRow("new user: ".$this->getStrUsername(), class_logger::$levelInfo);

            return $this->objDB->_pQuery($strQuery, array(
                $strUserid,
                $this->getStrUsername(),
                $this->objSession->encryptPassword($this->getStrPass()),
                $this->getStrEmail(),
                $this->getStrForename(),
                $this->getStrName(),
                $this->getStrStreet(),
                $this->getStrPostal(),
                $this->getStrCity(),
                $this->getStrTel(),
                $this->getStrMobile(),
                $this->getLongDate(),
                (int)$this->getIntActive(),
                (int)$this->getIntAdmin(),
                (int)$this->getIntPortal(),
                $this->getStrAdminskin(),
                $this->getStrAdminlanguage(),
                0,
                0,
                $this->getStrAuthcode()
            ));
        }
        else {

            $strQuery = ""; $arrParams = array(); $arrEncode = array();

            if($this->getStrPass() != "") {
                $strQuery = "UPDATE "._dbprefix_."user SET
                        user_username=?, user_pass=?, user_email=?, user_forename=?, user_name=?, user_street=?, user_postal=?, user_city=?, user_tel=?, user_mobile=?,
                        user_date=?, user_active=?, user_admin=?, user_portal=?, user_admin_skin=?, user_admin_language=?, user_logins = ?, user_lastlogin = ?, user_authcode = ?
                        WHERE user_id = ?";
                   $arrParams = array(
                        $this->getStrUsername(),
                       ($this->getStrPass() != "" ? $this->objSession->encryptPassword($this->getStrPass()) : ""),
                        $this->getStrEmail(), $this->getStrForename(), $this->getStrName(), $this->getStrStreet(), $this->getStrPostal(),
                        $this->getStrCity(), $this->getStrTel(), $this->getStrMobile(), $this->getLongDate(), (int)$this->getIntActive(),
                        (int)$this->getIntAdmin(), (int)$this->getIntPortal(), $this->getStrAdminskin(), $this->getStrAdminlanguage(),
                        (int)$this->getIntLogins(), (int)$this->getIntLastLogin(), $this->getStrAuthcode(), $this->getSystemid()
                   );
                   $arrEncode = array($bitHtmlEntities, $bitHtmlEntities, $bitHtmlEntities, $bitHtmlEntities, $bitHtmlEntities, $bitHtmlEntities, $bitHtmlEntities, $bitHtmlEntities, $bitHtmlEntities, $bitHtmlEntities,
                          false, false, false, false, false, false, false, false, false, false);
            }
            else {
                $strQuery = "UPDATE "._dbprefix_."user SET
                        user_username=?, user_email=?, user_forename=?, user_name=?, user_street=?, user_postal=?, user_city=?, user_tel=?, user_mobile=?,
                        user_date=?, user_active=?, user_admin=?, user_portal=?, user_admin_skin=?, user_admin_language=?, user_logins = ?, user_lastlogin = ?, user_authcode = ?
                        WHERE user_id = ?";

                $arrParams = array(
                        $this->getStrUsername(),
                        $this->getStrEmail(), $this->getStrForename(), $this->getStrName(), $this->getStrStreet(), $this->getStrPostal(),
                        $this->getStrCity(), $this->getStrTel(), $this->getStrMobile(), $this->getLongDate(), (int)$this->getIntActive(),
                        (int)$this->getIntAdmin(), (int)$this->getIntPortal(), $this->getStrAdminskin(), $this->getStrAdminlanguage(),
                        (int)$this->getIntLogins(), (int)$this->getIntLastLogin(), $this->getStrAuthcode(), $this->getSystemid()
                   );
                   $arrEncode = array($bitHtmlEntities, $bitHtmlEntities, $bitHtmlEntities, $bitHtmlEntities, $bitHtmlEntities, $bitHtmlEntities, $bitHtmlEntities, $bitHtmlEntities, $bitHtmlEntities,
                          false, false, false, false, false, false, false, false, false, false);
            }

            class_logger::getInstance()->addLogRow("updated user ".$this->getStrUsername(), class_logger::$levelInfo);

            return $this->objDB->_pQuery($strQuery, $arrParams, $arrEncode);
        }
    }

   

    /**
     * Fetches all available users an returns them in an array
     *
     * @param int $intStart
     * @param int $intEnd
     * @return mixed
     */
    public static function getAllUsers($intStart = false, $intEnd = false) {
        $strQuery = "SELECT user_id FROM "._dbprefix_."user ORDER BY user_username ASC";

        if($intStart !== false && $intEnd !== false)
            $arrIds = class_carrier::getInstance()->getObjDB()->getPArraySection($strQuery, array(), $intStart, $intEnd);
        else
            $arrIds = class_carrier::getInstance()->getObjDB()->getPArray($strQuery, array());

		$arrReturn = array();
		foreach($arrIds as $arrOneId)
		    $arrReturn[] = new class_modul_user_user($arrOneId["user_id"]);

		return $arrReturn;
    }

    /** Fetches all available active users with the given username an returns them in an array
     *
     * @param string $strName
     * @param boolean $bitOnlyActive
     * @return mixed
     */
    public static function getAllUsersByName($strName, $bitOnlyActive = true) {
        $strQuery = "SELECT user_id FROM "._dbprefix_."user
                      WHERE user_username=?
					    ".($bitOnlyActive ? " AND user_active = 1 " : "" );

        $arrIds = class_carrier::getInstance()->getObjDB()->getPArray($strQuery, array($strName));
		$arrReturn = array();
		foreach($arrIds as $arrOneId)
		    $arrReturn[] = new class_modul_user_user($arrOneId["user_id"], true);

		return $arrReturn;
    }

    /**
     * Counts the number of users created
     * @return int
     */
    public static function getNumberOfUsers() {
        $strQuery = "SELECT COUNT(*) FROM "._dbprefix_."user ";
        $arrRow = class_carrier::getInstance()->getObjDB()->getPRow($strQuery, array());
		return $arrRow["COUNT(*)"];
    }

    /**
     * Deletes a user from the systems
     *
     * @param string $strUserid
     * @return bool
     */
    public function deleteUser() {
        class_logger::getInstance()->addLogRow("deleted user with id ".$this->getSystemid(), class_logger::$levelInfo);
        $this->deleteAllUserMemberships();
        $strQuery = "DELETE FROM "._dbprefix_."user WHERE user_id=?";
        //call other models that may be interested
        $this->additionalCallsOnDeletion($this->getSystemid());
        return $this->objDB->_pQuery($strQuery, array($this->getSystemid()));
    }


    /**
	 * Deletes all memberships of the given USER from ALL groups
	 *
	 * @return bool
	 * @static
	 */
	public function deleteAllUserMemberships() {
        $strQuery = "DELETE FROM "._dbprefix_."user_group_members WHERE group_member_user_id=?";
		return $this->objDB->_pQuery($strQuery, array($this->getSystemid()));
	}

// --- GETTERS / SETTERS --------------------------------------------------------------------------------

    public function getStrUsername() {
        return $this->strUsername;
    }
    public function getStrPass() {
        return $this->strPass;
    }
    public function getStrEmail() {
        return $this->strEmail;
    }
    public function getStrForename() {
        return $this->strForename;
    }
    public function getStrName() {
        return $this->strName;
    }
    public function getStrStreet() {
        return $this->strStreet;
    }
    public function getStrPostal() {
        return $this->strPostal;
    }
    public function getStrCity() {
        return $this->strCity;
    }
    public function getStrTel() {
        return $this->strTel;
    }
    public function getStrMobile() {
        return $this->strMobile;
    }
    public function getLongDate() {
        return $this->longDate;
    }
    public function getIntLogins() {
        return $this->intLogins;
    }
    public function getIntLastLogin() {
        return $this->intLastlogin;
    }
    public function getIntActive() {
        return $this->intActive;
    }
    public function getIntAdmin() {
        return $this->intAdmin;
    }
    public function getIntPortal() {
        return $this->intPortal;
    }
    public function getStrAdminskin() {
        return $this->strAdminskin;
    }
    public function getStrAdminlanguage() {
        return $this->strAdminlanguage;
    }


    public function setStrUsername($strUsername) {
        $this->strUsername = trim($strUsername);
    }
    public function setStrPass($strPass) {
        $this->strPass = trim($strPass);
    }
    public function setStrEmail($strEmail) {
        $this->strEmail = trim($strEmail);
    }
    public function setStrForename($strForename) {
        $this->strForename = $strForename;
    }
    public function setStrName($strName) {
        $this->strName = $strName;
    }
    public function setStrStreet($strStreet) {
        $this->strStreet = $strStreet;
    }
    public function setStrPostal($strPostal) {
        $this->strPostal = $strPostal;
    }
    public function setStrCity($strCity) {
        $this->strCity = $strCity;
    }
    public function setStrTel($strTel) {
        $this->strTel = $strTel;
    }
    public function setStrMobile($strMobile) {
        $this->strMobile = $strMobile;
    }
    public function setLongDate($longDate) {
        if($longDate == "")
            $longDate = 0;
        $this->longDate = $longDate;
    }
    public function setIntLogins($intLogins) {
        if($intLogins == "")
            $intLogins = 0;
        $this->intLogins = $intLogins;
    }
    public function setIntLastLogin($intLastLogin) {
        if($intLastLogin == "")
            $intLastLogin = 0;
        $this->intLastlogin = $intLastLogin;
    }
    public function setIntActive($intActive) {
        if($intActive == "")
            $intActive = 0;
        $this->intActive = $intActive;
    }
    public function setIntAdmin($intAdmin) {
        if($intAdmin == "")
            $intAdmin = 0;
        $this->intAdmin = $intAdmin;
    }
    public function setIntPortal($intPortal) {
        if($intPortal == "")
            $intPortal = 0;
        $this->intPortal = $intPortal;
    }
    public function setStrAdminskin($strAdminskin) {
        $this->strAdminskin = $strAdminskin;
    }
    public function setStrAdminlanguage($strAdminlanguage) {
        $this->strAdminlanguage = $strAdminlanguage;
    }

    public function getStrAuthcode() {
        return $this->strAuthcode;
    }

    public function setStrAuthcode($strAuthcode) {
        $this->strAuthcode = $strAuthcode;
    }



}
?>