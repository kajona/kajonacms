<?php
/*"******************************************************************************************************
*   (c) 2004-2006 by MulchProductions, www.mulchprod.de                                                 *
*   (c) 2007-2011 by Kajona, www.kajona.de                                                              *
*       Published under the GNU LGPL v2.1, see /system/licence_lgpl.txt                                 *
*-------------------------------------------------------------------------------------------------------*
*	$Id$                                   *
********************************************************************************************************/

/**
 * Model for a user-group
 * Groups are not represented in the system-table
 *
 * @package modul_user
 * @author sidler@mulchprod.de
 */
class class_modul_user_group extends class_model implements interface_model  {

    private $strName = "";


    /**
     * Constructor to create a valid object
     *
     * @param string $strSystemid (use "" on new objects)
     */
    public function __construct($strSystemid = "") {
        $arrModul = array();
        $arrModul["name"] 				= "modul_user";
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
     * @see class_model::getObjectTables();
     * @return array
     */
    protected function getObjectTables() {
        return array();
    }

    /**
     * @see class_model::getObjectDescription();
     * @return string
     */
    protected function getObjectDescription() {
        return "user group ".$this->getStrName();
    }

    /**
     * Initalises the current object, if a systemid was given
     *
     */
    public function initObject() {
        $strQuery = "SELECT * FROM "._dbprefix_."user_group WHERE group_id=?";
        $arrRow = $this->objDB->getPRow($strQuery, array($this->getSystemid()));

        if(count($arrRow) > 0) {
            $this->setStrName($arrRow["group_name"]);
        }
    }

    /**
     * Updates the current object to the database
     *
     * @return bool
     */
    public function updateObjectToDb($strPrevId = false) {
        //mode-splitting
        if($this->getSystemid() == "") {
            class_logger::getInstance()->addLogRow("saved new group ".$this->getStrName(), class_logger::$levelInfo);
            $strGrId = generateSystemid();
            $this->setSystemid($strGrId);
            $strQuery = "INSERT INTO "._dbprefix_."user_group
                          (group_id, group_name) VALUES
                          (?, ?)";
            return $this->objDB->_pQuery($strQuery, array($strGrId, $this->getStrName()));
        }
        else {
            class_logger::getInstance()->addLogRow("updated group ".$this->getStrName(), class_logger::$levelInfo);
            $strQuery = "UPDATE "._dbprefix_."user_group
                            SET group_name=?
                            WHERE group_id=?";
            return $this->objDB->_pQuery($strQuery, array($this->getStrName(), $this->getSystemid()));
        }
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
            $arrIds = class_carrier::getInstance()->getObjDB()->getPArraySection($strQuery, array(), $intStart, $intEnd);
        else
            $arrIds = class_carrier::getInstance()->getObjDB()->getPArray($strQuery, array());
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
        $arrRow = class_carrier::getInstance()->getObjDB()->getPRow($strQuery, array());
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
     * @deprecated will be replaces by class_modul_user_group::getAllMembers()
     * @see class_modul_user_group::getAllMembers()
	 */
	public static function getGroupMembers($strGroupId, $intStart = false, $intEnd = false) {
		$objGroup = new class_modul_user_group($strGroupId);
		return $objGroup->getAllMembers($intStart, $intEnd);
	}

    /**
     * Loads all members of the current group.
     *
     * @param int $intStart
     * @param int $intEnd
	 * @return class_modul_user_user
     * @see class_modul_user_group::getGroupMembers
     */
    public function getAllMembers($intStart = false, $intEnd = false) {
        $strQuery = "SELECT user_id FROM "._dbprefix_."user,
									"._dbprefix_."user_group_members
								WHERE group_member_group_id=?
									AND user_id = group_member_user_id
                                  ORDER BY user_name ASC  ";

        if($intStart !== false && $intEnd !== false)
            $arrIds = $this->objDB->getPArraySection($strQuery, array($this->getSystemid()), $intStart, $intEnd);
        else
            $arrIds = $this->objDB->getPArray($strQuery, array($this->getSystemid()));

		$arrReturn = array();
		foreach($arrIds as $arrOneId)
		    $arrReturn[] = new class_modul_user_user($arrOneId["user_id"]);

		return $arrReturn;
    }

    /**
	 * Gets the number of members of a group
	 *
	 * @param string $strGroupId
	 * @return int
	 * @static
	 */
	public static function getGroupMembersCount($strGroupId) {
		$strQuery = "SELECT COUNT(*) FROM "._dbprefix_."user,
									"._dbprefix_."user_group_members
								WHERE group_member_group_id=?
									AND user_id = group_member_user_id";
		$arrRow = class_carrier::getInstance()->getObjDB()->getPRow($strQuery, array($strGroupId));
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
						WHERE group_member_user_id=?
						AND group_member_group_id=?";
	    $arrRow = $this->objDB->getPRow($strQuery, array($objUser->getSystemid(), $this->getSystemid())   );
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
						WHERE group_member_user_id=?";
						
	    $arrGroups = class_carrier::getInstance()->getObjDB()->getPArray($strQuery, array($strUserId));
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
	 * Deletes all users from the current group
	 *
	 * @return bool
	 */
    public function deleteAllUsersFromCurrentGroup() {
        $strQuery = "DELETE FROM "._dbprefix_."user_group_members WHERE group_member_group_id=?";
        return $this->objDB->_pQuery($strQuery, array($this->getSystemid()));
	}

	/**
	 * Deletes the given user from the current group
	 *
	 * @param class_modul_user_user $objUser
	 * @return bool
	 */
	public function deleteUserFromCurrentGroup($objUser) {
        $strQuery = "DELETE FROM "._dbprefix_."user_group_members
						WHERE group_member_group_id=?
						AND group_member_user_id=?";
        return $this->objDB->_pQuery($strQuery, array($this->getSystemid(), $objUser->getSystemid()));
	}

	/**
	 * Deletes the given group
	 *
	 * @return bool
	 */
	public function deleteGroup() {
	    class_logger::getInstance()->addLogRow("deleted group with id ".$this->getSystemid(), class_logger::$levelInfo);
        $this->deleteAllUsersFromCurrentGroup();
        $strQuery = "DELETE FROM "._dbprefix_."user_group WHERE group_id=?";
        return $this->objDB->_pQuery($strQuery, array($this->getSystemid()));
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
							(?, ?)";
    		class_carrier::getInstance()->getObjDB()->_pQuery($strQuery, array($strOneGroupId, $objUser->getSystemid()));
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