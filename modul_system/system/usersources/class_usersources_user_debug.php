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
class class_usersources_user_debug extends class_model implements interface_model, interface_usersources_user {
    

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
		$arrModul["modul"]				= "user";

		//base class
		parent::__construct($arrModul, $strSystemid);

		//init current object
		if($strSystemid != "")
		    $this->initObject($bitLoadPassword);
    }

    /**
     * Initialises the current object, if a systemid was given
     */
    public function initObject($bitPassword = false) {

    }
    
    /**
     * Indicates if the current users' password may be reset, e.g. via a password-forgotten mail
     */
    public function isPasswortResetable() {
        return false;
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
     * Updates the current object to the database
     * <b>ATTENTION</b> If you don't want to update the password, set it to "" before!
     *
     * @return bool
     */
    public function updateObjectToDb($strPrevId = false) {
        
    }

    /**
     * Deletes a user from the systems
     *
     * @param string $strUserid
     * @return bool
     */
    public function deleteUser() {
        return true;
    }

    /**
	 * Deletes all memberships of the given USER from ALL groups
	 *
	 * @return bool
	 * @static
	 */
	private function deleteAllUserMemberships() {
		return true;
	}

    /**
	 * Returns the list of editable fields
	 * @return class_usersources_form_entry $arrParams
	 */
	public function getEditFormEntries() {
        
        $arrTemp = array();
            
        return $arrTemp;
    }

    /**
     * Returns the list of group-ids the current user is assigned to
     * @return array
     */
	public function getGroupIdsForUser() {

		$arrReturn = array();
        $arrReturn[] = _admins_group_id_;
        
        return $arrReturn;
    }
    
    /**
     * Indicates if the current user is editable or read-only
     * @return bool
     */
	public function isEditable() {
        return false;
    }

    /**
     * Sets the list of fields in order to be written to the database
     * @param class_admin_form_entry $arrParams
     */
	public function setEditFormEntries($arrParams) {
        
    }
    
    /**
     * Writes a single field to the user
     * @param string $strName
     * @param string $strValue
     */
    public function setField($strName, $strValue) {
        
    }

    
    
    // --- GETTERS / SETTERS --------------------------------------------------------------------------------

    
    public function getStrEmail() {
        return "dd@dd.ddd";
    }
    public function getStrForename() {
        return "debug";
    }
    public function getStrName() {
        return "debug";
    }
    


	
}
?>