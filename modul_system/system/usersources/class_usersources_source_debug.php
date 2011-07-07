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
class class_usersources_source_debug  implements interface_usersources_usersource {
    
    private $objDB;
    
    public function __construct() {
        $this->objDB = class_carrier::getInstance()->getObjDB();
    }

    /**
	 * Tries to authenticate a user with the given credentials.
     * The password is unencrypted, each source should take care of its own encryption.
     * 
	 * @param interface_usersources_user $objUser
     * @return bool
	 */
	public function authenticateUser(interface_usersources_user $objUser, $strPassword) {
        if($objUser instanceof class_usersources_user_debug) {
            if($strPassword == "debug" )
                 return true;
        }
        
        return false;
    }

    public function getCreationOfGroupsAllowed() {
        return false;
    }

    public function getCreationOfUsersAllowed() {
        return false;
    }
    
	public function getMembersEditable() {
        return false;
    }

    /**
	 * Loads the group identified by the passed id
     * 
	 * @param string $strId
     * @return interface_usersources_group or null
	 */
    public function getGroupById($strId) {
        $strQuery = "SELECT group_id FROM "._dbprefix_."user_group WHERE group_id = ?";
        
        $arrIds = $this->objDB->getPRow($strQuery, array($strId));
		if(isset($arrIds["group_id"]) && validateSystemid($arrIds["group_id"]))
            return new class_usersources_group_debug($arrIds["group_id"]);

		return null;
    }

    /**
     * Returns an empty group, e.g. to fetch the fields available and
     * in order to fill a new one.
     * 
     * @return interface_usersources_group
     */
    public function getNewGroup() {
        return new class_usersources_group_debug();
    }

    /**
     * Returns an empty user, e.g. to fetch the fields available and
     * in order to fill a new one.
     * 
     * @return interface_usersources_user
     */
    public function getNewUser() {
        return new class_usersources_user_debug();
    }

    /**
	 * Loads the iser identified by the passed id
     * 
	 * @param string $strId
     * @return interface_usersources_user or null
	 */
    public function getUserById($strId) {
        $strQuery = "SELECT user_id FROM "._dbprefix_."user WHERE user_id = ? AND user_subsystem = 'debug'";

        $arrIds = class_carrier::getInstance()->getObjDB()->getPRow($strQuery, array($strId));
        if(isset($arrIds["user_id"]) && validateSystemid($arrIds["user_id"]))
            return new class_usersources_user_debug($arrIds["user_id"]);

		return null;
    }
    
    
    /**
	 * Loads the user identified by the passed name.
     * This method may be called during the authentication of users and may be used as a hook
     * in order to create new users in the central database not yet existing.
     * 
	 * @param string $strUsername
     * @return interface_usersources_user or null
	 */
	public function getUserByUsername($strUsername) {
        $strQuery = "SELECT user_id FROM "._dbprefix_."user  WHERE user_username = ? AND user_subsystem = 'debug'";

        $arrIds = class_carrier::getInstance()->getObjDB()->getPRow($strQuery, array($strUsername));
        if(isset($arrIds["user_id"]) && validateSystemid($arrIds["user_id"]))
            return new class_usersources_user_debug($arrIds["user_id"]);
        
        //user not found. create a new one
        $objUser = new class_modul_user_user();
        $objUser->setStrUsername($strUsername);
        $objUser->setStrSubsystem("debug");
        $objUser->setIntActive(1);
        $objUser->setIntAdmin(1);
        $objUser->updateObjectToDb();

		return $objUser;
    }

    
    
    /**
     * Returns an array of group-ids provided by the current source.
     * return string
     */
    public function getAllGroupIds() {
        
        $arrReturn = array();
        return $arrReturn;
    }
    


}
?>