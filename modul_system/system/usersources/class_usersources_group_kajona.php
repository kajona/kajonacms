<?php
/*"******************************************************************************************************
*   (c) 2004-2006 by MulchProductions, www.mulchprod.de                                                 *
*   (c) 2007-2011 by Kajona, www.kajona.de                                                              *
*       Published under the GNU LGPL v2.1, see /system/licence_lgpl.txt                                 *
*-------------------------------------------------------------------------------------------------------*
*	$Id$                                            *
********************************************************************************************************/


/**
 * This class represents a group based on the internal authentication system.
 * Since groups are NOT reflected in the system-table, all relevant methods have to be overwritten and
 * reimplemented.
 * 
 * @author sidler@mulchprod.de
 * @since 3.4.1
 * @package modul_usersources
 */
class class_usersources_group_kajona extends class_model implements interface_model, interface_usersources_group {


    private $strDesc = "";


    /**
     * Constructor to create a valid object
     *
     * @param string $strSystemid (use "" on new objects)
     */
    public function __construct($strSystemid = "") {
        $arrModul = array();
        $arrModul["name"] 				= "modul_user";
		$arrModul["moduleId"] 			= _user_modul_id_;
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
        return "kajona user group ".$this->getStrName();
    }

    /**
     * Initalises the current object, if a systemid was given
     *
     */
    public function initObject() {
        $strQuery = "SELECT * FROM "._dbprefix_."user_group_kajona WHERE group_id=?";
        $arrRow = $this->objDB->getPRow($strQuery, array($this->getSystemid()));

        if(count($arrRow) > 0) {
            $this->setStrDesc($arrRow["group_desc"]);
            $this->setSystemid($arrRow["group_id"]);
        }
    }

    /**
     * Updates the current object to the database.
     * Overwrites class_roots' logic since a kajona group is not reflected in the system-table
     *
     * @return bool
     */
    public function updateObjectToDb($strPrevId = false) {
        //mode-splitting
        if($this->getSystemid() == "") {
            class_logger::getInstance()->addLogRow("saved new kajona group ".$this->getStrSystemid(), class_logger::$levelInfo);
            $strGrId = generateSystemid();
            $this->setSystemid($strGrId);
            $strQuery = "INSERT INTO "._dbprefix_."user_group_kajona
                          (group_id, group_desc) VALUES
                          (?, ?)";
            return $this->objDB->_pQuery($strQuery, array($strGrId, $this->getStrDesc()));
        }
        else {
            class_logger::getInstance()->addLogRow("updated kajona group ".$this->getSystemid(), class_logger::$levelInfo);
            $strQuery = "UPDATE "._dbprefix_."user_group_kajona
                            SET group_desc=?
                          WHERE group_id=?";
            return $this->objDB->_pQuery($strQuery, array($this->getStrDesc(), $this->getSystemid()));
        }
    }
    
    /**
     * Passes a new system-id to the object.
     * This id has to be used for newly created objects,
     * otherwise the mapping of kajona-users to users in the
     * subsystem may fail.
     * 
     * @param string $strId
     * @return void
     */
    public function setNewRecordId($strId) {
        $strQuery = "UPDATE "._dbprefix_."user_group_kajona SET group_id = ? WHERE group_id = ?";
        $this->objDB->_pQuery($strQuery, array($strId, $this->getSystemid()));
        $this->setSystemid($strId);
    }

    /**
     * Returns an array of user-ids associated with the current group.
     * If possible, pageing should be supported
     * 
     * @param int $intStart
     * @param int $intEnd
     * @return array
     */
	public function getUserIdsForGroup($intStart = null, $intEnd = null) {
        $strQuery = "SELECT k_user.user_id FROM "._dbprefix_."user_kajona as k_user,
                                         "._dbprefix_."user as user2,
									     "._dbprefix_."user_kajona_members
								   WHERE group_member_group_kajona_id= ?
								  	 AND k_user.user_id = group_member_user_kajona_id
                                     AND k_user.user_id = user2.user_id
                                   ORDER BY user2.user_username ASC  ";

        if($intStart != null && $intEnd != null)
            $arrIds = $this->objDB->getPArraySection($strQuery, array($this->getSystemid()), $intStart, $intEnd);
        else
            $arrIds = $this->objDB->getPArray($strQuery, array($this->getSystemid()));

		$arrReturn = array();
		foreach($arrIds as $arrOneId)
		    $arrReturn[] = $arrOneId["user_id"];

		return $arrReturn;
    }

    /**
     * Returns the number of members of the current group.
     * @return int
     */
    public function getNumberOfMembers() {
		$strQuery = "SELECT COUNT(*) 
                       FROM "._dbprefix_."user_kajona_members
					   WHERE group_member_group_kajona_id= ?";
		$arrRow = class_carrier::getInstance()->getObjDB()->getPRow($strQuery, array($this->getSystemid()));
        return $arrRow["COUNT(*)"];
	}

	/**
	 * Deletes the given group
	 *
	 * @return bool
	 */
	public function deleteGroup() {
	    class_logger::getInstance()->addLogRow("deleted kajona group with id ".$this->getSystemid(), class_logger::$levelInfo);
        $this->deleteAllUsersFromCurrentGroup();
        $strQuery = "DELETE FROM "._dbprefix_."user_group_kajona WHERE group_id=?";
        $this->additionalCallsOnDeletion($this->getSystemid());
        return $this->objDB->_pQuery($strQuery, array($this->getSystemid()));
	}
    
    /**
	 * Deletes all users from the current group
	 *
	 * @return bool
	 */
    private function deleteAllUsersFromCurrentGroup() {
        $strQuery = "DELETE FROM "._dbprefix_."user_kajona_members WHERE group_member_group_kajona_id=?";
        return $this->objDB->_pQuery($strQuery, array($this->getSystemid()));
	}

    /**
	 * Adds a new member to the group - if possible
	 * @param interface_usersources_user $objUser
	 */
	public function addMember(interface_usersources_user $objUser) {
         $strQuery = "INSERT INTO "._dbprefix_."user_kajona_members
                       (group_member_group_kajona_id, group_member_user_kajona_id) VALUES
                         (?, ?)";
    	 return $this->objDB->_pQuery($strQuery, array($this->getSystemid(), $objUser->getSystemid()));
    }
    
    
    /**
     * Defines whether the current group-properties (e.g. the name) may be edited or is read-only
     * @return bool
     */
    public function isEditable() {
        return true;
    }


    /**
	 * Removes a member from the current group - if possible.
	 * @param interface_usersources_user $objUser
	 */
    public function removeMember(interface_usersources_user $objUser) {
        $strQuery = "DELETE FROM "._dbprefix_."user_kajona_members
						WHERE group_member_group_kajona_id=?
						  AND group_member_user_kajona_id=?";
        return $this->objDB->_pQuery($strQuery, array($this->getSystemid(), $objUser->getSystemid()));
    }
    

    /**
	 * Returns the list of form-entries allowed to be modified
	 * @param class_usersources_form_entry $arrParams
	 */
    public function getEditFormEntries() {
        return array(new class_usersources_form_entry("desc", class_usersources_form_entry::$INT_TYPE_LONGTEXT, $this->getStrDesc(), false));
        
    }


    /**
     * Writes a set of properties to the current group.
     * @param class_usersources_form_entry $arrParams
     */
    public function setEditFormEntries($arrParams) {
        foreach($arrParams as $objOneField) {
            if($objOneField->getStrName() == "desc")
                $this->setStrDesc($objOneField->getStrValue());
        }
        
    }

// --- GETTERS / SETTERS --------------------------------------------------------------------------------
    public function getStrDesc() {
        return $this->strDesc;
    }

    public function setStrDesc($strDesc) {
        $this->strDesc = $strDesc;
    }
    
    
    
    
    

	

}
?>