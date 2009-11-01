<?php
/*"******************************************************************************************************
*   (c) 2004-2006 by MulchProductions, www.mulchprod.de                                                 *
*   (c) 2007-2009 by Kajona, www.kajona.de                                                              *
*       Published under the GNU LGPL v2.1, see /system/licence_lgpl.txt                                 *
*-------------------------------------------------------------------------------------------------------*
*	$Id$                                   *
********************************************************************************************************/

/**
 * Model for a user-group
 *
 * @package modul_system
 */
class class_modul_user_group extends class_model implements interface_model  {

    private $strName = "";


    /**
     * Constructor to create a valid object
     *
     * @param string $strSystemid (use "" on new objets)
     */
    public function __construct($strSystemid = "") {
        $arrModul = array();
        $arrModul["name"] 				= "modul_user";
		$arrModul["author"] 			= "sidler@mulchprod.de";
		$arrModul["moduleId"] 			= _user_modul_id_;
		$arrModul["table"]       		= _dbprefix_."user_group";
		$arrModul["table2"]       		= _dbprefix_."user_group_members";
		$arrModul["modul"]				= "user";

		//base class
		parent::__construct($arrModul, $strSystemid);

		//init current object
		if($strSystemid != "")
		    $this->initObject();
    }

    /**
     * Initalises the current object, if a systemid was given
     *
     */
    public function initObject() {
        $strQuery = "SELECT * FROM "._dbprefix_."user_group WHERE group_id='".$this->objDB->dbsafeString($this->getSystemid())."'";
        $arrRow = $this->objDB->getRow($strQuery);

        if(count($arrRow) > 0) {
            $this->setStrName($arrRow["group_name"]);
        }
    }

    /**
     * Updates the current object to the database
     *
     * @return bool
     */
    public function updateObjectToDb() {
        class_logger::getInstance()->addLogRow("updated group ".$this->getStrName(), class_logger::$levelInfo);
        $strQuery = "UPDATE "._dbprefix_."user_group
						SET group_name='".$this->objDB->dbsafeString($this->getStrName())."'
						WHERE group_id='".$this->objDB->dbsafeString($this->getSystemid()). "'";
        return $this->objDB->_query($strQuery);
    }

    /**
     * Saves the current object as a new group to the database
     *
     * @return bool
     */
    public function saveObjectToDb() {
        class_logger::getInstance()->addLogRow("saved new group ".$this->getStrName(), class_logger::$levelInfo);
        $strGrId = generateSystemid();
        $this->setSystemid($strGrId);
		$strQuery = "INSERT INTO "._dbprefix_."user_group
		              (group_id, group_name) VALUES
		              ('".$this->objDB->dbsafeString($strGrId)."', '".$this->objDB->dbsafeString($this->getStrName())."')";
		return $this->objDB->_query($strQuery);
    }

    /**
	 * Returns all groups from database
	 *
     * @param int $intStart
     * @param int $intEnd
	 * @return array of class_modul_user_group
	 * @static
	 */
	public static function getAllGroups($intStart = false, $intEnd = false) {
		$strQuery = "SELECT group_id FROM "._dbprefix_."user_group";
        
        if($intStart !== false && $intEnd !== false)
            $arrIds = class_carrier::getInstance()->getObjDB()->getArraySection($strQuery, $intStart, $intEnd);
        else
            $arrIds = class_carrier::getInstance()->getObjDB()->getArray($strQuery);
		$arrReturn = array();
		foreach($arrIds as $arrOneId)
		    $arrReturn[] = new class_modul_user_group($arrOneId["group_id"]);

		return $arrReturn;
	}

    /**
     * Fetches the number of groups available
     * @return int
     */
    public static function getNumberOfGroups() {
        $strQuery = "SELECT COUNT(*) FROM "._dbprefix_."user_group";
        $arrRow = class_carrier::getInstance()->getObjDB()->getRow($strQuery);
        return $arrRow["COUNT(*)"];
    }

	/**
	 * Gets all Members off the specified group
	 *
	 * @param string $strGroupId
     * @param int $intStart
     * @param int $intEnd
	 * @return mixed array of user-objects
	 * @static
	 */
	public static function getGroupMembers($strGroupId, $intStart = false, $intEnd = false) {
		$strQuery = "SELECT user_id FROM "._dbprefix_."user,
									"._dbprefix_."user_group_members
								WHERE group_member_group_id='".class_carrier::getInstance()->getObjDB()->dbsafeString($strGroupId)."'
									AND user_id = group_member_user_id";

        if($intStart !== false && $intEnd !== false)
            $arrIds = class_carrier::getInstance()->getObjDB()->getArraySection($strQuery, $intStart, $intEnd);
        else
            $arrIds = class_carrier::getInstance()->getObjDB()->getArray($strQuery);

		$arrReturn = array();
		foreach($arrIds as $arrOneId)
		    $arrReturn[] = new class_modul_user_user($arrOneId["user_id"]);

		return $arrReturn;
	}

    /**
	 * Gets the number of members of a group
	 *
	 * @param string $strGroupId
	 * @return mixed array of user-objects
	 * @static
	 */
	public static function getGroupMembersCount($strGroupId) {
		$strQuery = "SELECT COUNT(*) FROM "._dbprefix_."user,
									"._dbprefix_."user_group_members
								WHERE group_member_group_id='".class_carrier::getInstance()->getObjDB()->dbsafeString($strGroupId)."'
									AND user_id = group_member_user_id";
		$arrRow = class_carrier::getInstance()->getObjDB()->getRow($strQuery);
        return $arrRow["COUNT(*)"];
	}

	/**
	 * Checks, whether a user is member of the current group, or not
	 *
	 * @param class_modul_user_user $objUserid
	 * @return bool
	 */
	public function isUserMemberInGroup($objUser) {
	    if($objUser->getSystemid() == "" )
	       return false;
	    $strQuery = "SELECT COUNT(*)
						FROM "._dbprefix_."user_group_members
						WHERE group_member_user_id='".$this->objDB->dbsafeString($objUser->getSystemid())."'
						AND group_member_group_id='".$this->objDB->dbsafeString($this->getSystemid())."'";
	    $arrRow = $this->objDB->getRow($strQuery);
	    return ($arrRow["COUNT(*)"] != 0);
	}
	
	/**
	 * Returns an array of groupids the passed user is member
	 *
	 * @param string $strUserId
	 * @return array
	 */
	public static function getAllGroupIdsForUser($strUserId) {
	    if($strUserId == "")
	       return array(_guests_group_id_);
	       
	    $strQuery = "SELECT group_member_group_id 
						FROM "._dbprefix_."user_group_members
						WHERE group_member_user_id='".dbsafeString($strUserId)."'";
						
	    $arrGroups = class_carrier::getInstance()->getObjDB()->getArray($strQuery);   
	    $arrReturn = array();
	    foreach ($arrGroups as $arrOneGroup)
	        $arrReturn[] = $arrOneGroup["group_member_group_id"];
	       
	    return $arrReturn;   
	}

    /**
	 * Returns an array of groupids the passed user is member
	 *
	 * @param string $strUserId
	 * @return string
	 */
	public static function getAllGroupIdsForUserAsString($strUserId) {
        $arrGroups = class_modul_user_group::getAllGroupIdsForUser($strUserId);
	    return implode(",", $arrGroups);
	}

	/**
	 * Deletes all memberships of the given USER from ALL groups
	 *
	 * @param class_modul_user_user $objUser
	 * @return bool
	 * @static
	 */
	public static function deleteAllUserMemberships($objUser) {
        $strQuery = "DELETE FROM "._dbprefix_."user_group_members WHERE group_member_user_id='".class_carrier::getInstance()->getObjDB()->dbsafeString($objUser->getSystemid())."'";
		return class_carrier::getInstance()->getObjDB()->_query($strQuery);
	}

	/**
	 * Deletes all users from the current group
	 *
	 * @return bool
	 */
    public function deleteAllUsersFromCurrentGroup() {
        $strQuery = "DELETE FROM "._dbprefix_."user_group_members WHERE group_member_group_id='".dbsafeString($this->getSystemid())."'";
        return $this->objDB->_query($strQuery);
	}

	/**
	 * Deletes the given user from the current group
	 *
	 * @param class_modul_user_user $objUser
	 * @return bool
	 */
	public function deleteUserFromCurrentGroup($objUser) {
        $strQuery = "DELETE FROM "._dbprefix_."user_group_members
						WHERE group_member_group_id='".$this->objDB->dbsafeString($this->getSystemid())."'
						AND group_member_user_id='".$this->objDB->dbsafeString($objUser->getSystemid())."'";
        return $this->objDB->_query($strQuery);
	}

	/**
	 * Deletes the given group
	 *
	 * @param string $strGroupid
	 * @return bool
	 */
	public static function deleteGroup($strGroupid) {
	    class_logger::getInstance()->addLogRow("deleted group with id ".$strGroupid, class_logger::$levelInfo);
        $objGroup = new class_modul_user_group($strGroupid);
        $objGroup->deleteAllUsersFromCurrentGroup();
        $strQuery = "DELETE FROM "._dbprefix_."user_group WHERE group_id='".dbsafeString($strGroupid)."'";
        return class_carrier::getInstance()->getObjDB()->_query($strQuery);
	}

	/**
	 * Adds the given user to the groupsids passed
	 *
	 * @param class_modul_user_user $objUser
	 * @param array $arrWantedGroupIds
	 */
	public static function addUserToGroups($objUser, $arrWantedGroupIds) {
	    foreach($arrWantedGroupIds as $strOneGroupId) {
    	    $strQuery = "INSERT INTO "._dbprefix_."user_group_members
						   (group_member_group_id, group_member_user_id) VALUES
							('".dbsafeString($strOneGroupId)."', '".dbsafeString($objUser->getSystemid())."')";
    		class_carrier::getInstance()->getObjDB()->_query($strQuery);
	    }
	}

// --- GETTERS / SETTERS --------------------------------------------------------------------------------
    public function getStrName() {
        return $this->strName;
    }

    public function setStrName($strName) {
        $this->strName = $strName;
    }
}
?>