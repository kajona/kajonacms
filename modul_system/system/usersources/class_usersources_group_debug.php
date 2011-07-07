<?php
/*"******************************************************************************************************
*   (c) 2004-2006 by MulchProductions, www.mulchprod.de                                                 *
*   (c) 2007-2011 by Kajona, www.kajona.de                                                              *
*       Published under the GNU LGPL v2.1, see /system/licence_lgpl.txt                                 *
*-------------------------------------------------------------------------------------------------------*
*	$Id: class_date.php 3648 2011-03-08 14:45:50Z sidler $                                            *
********************************************************************************************************/


/**
 * THIS CLASS IS FOR DEBUGGING ONLY!!!
 * 
 * @author sidler@mulchprod.de
 * @since 3.4.1
 * @package modul_usersources
 */
class class_usersources_group_debug extends class_model implements interface_model, interface_usersources_group {



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
     * Initalises the current object, if a systemid was given
     *
     */
    public function initObject() {
    }

    /**
     * Updates the current object to the database.
     * Overwrites class_roots' logic since a kajona group is not reflected in the system-table
     *
     * @return bool
     */
    public function updateObjectToDb($strPrevId = false) {
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
        $strQuery = "SELECT user_id FROM "._dbprefix_."user
								   WHERE user_subsystem= 'debug'";

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
                       FROM "._dbprefix_."user
					   WHERE user_subsystem= 'debug' ";
		$arrRow = class_carrier::getInstance()->getObjDB()->getPRow($strQuery, array($this->getSystemid()));
        return $arrRow["COUNT(*)"];
	}

	/**
	 * Deletes the given group
	 *
	 * @return bool
	 */
	public function deleteGroup() {
	    return true;
	}
    
    /**
	 * Deletes all users from the current group
	 *
	 * @return bool
	 */
    private function deleteAllUsersFromCurrentGroup() {
        return true;
	}

    /**
	 * Adds a new member to the group - if possible
	 * @param interface_usersources_user $objUser
	 */
	public function addMember(interface_usersources_user $objUser) {
        
    }
    
    
    /**
     * Defines whether the current group-properties (e.g. the name) may be edited or is read-only
     * @return bool
     */
    public function isEditable() {
        return false;
    }


    /**
	 * Removes a member from the current group - if possible.
	 * @param interface_usersources_user $objUser
	 */
    public function removeMember(interface_usersources_user $objUser) {
       
    }
    

    /**
	 * Returns the list of form-entries allowed to be modified
	 * @param class_usersources_form_entry $arrParams
	 */
    public function getEditFormEntries() {
        return array(new class_usersources_form_entry("desc", class_usersources_form_entry::$INT_TYPE_LONGTEXT, "desc", false));
        
    }


    /**
     * Writes a set of properties to the current group.
     * @param class_usersources_form_entry $arrParams
     */
    public function setEditFormEntries($arrParams) {
        
        
    }

    
    

	

}
?>