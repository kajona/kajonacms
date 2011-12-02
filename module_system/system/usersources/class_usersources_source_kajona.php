<?php
/*"******************************************************************************************************
*   (c) 2004-2006 by MulchProductions, www.mulchprod.de                                                 *
*   (c) 2007-2011 by Kajona, www.kajona.de                                                              *
*       Published under the GNU LGPL v2.1, see /system/licence_lgpl.txt                                 *
*-------------------------------------------------------------------------------------------------------*
*	$Id$                                            *
********************************************************************************************************/

/**
 * The kajona usersource is the global entry and factory / facade for the classical kajona usersystem
 *
 * @author sidler@mulchprod.de
 * @since 3.4.1
 * @package module_usersource
 */
class class_usersources_source_kajona  implements interface_usersources_usersource {

    private $objDB;

    public function __construct() {
        $this->objDB = class_carrier::getInstance()->getObjDB();
    }

    /**
     * Tries to authenticate a user with the given credentials.
     * The password is unencrypted, each source should take care of its own encryption.
     *
     * @param interface_usersources_user $objUser
     * @param $strPassword
     * @return bool
     */
	public function authenticateUser(interface_usersources_user $objUser, $strPassword) {
        if($objUser instanceof class_usersources_user_kajona) {
            $bitMD5Encryption = false;
            if(uniStrlen($objUser->getStrFinalPass()) == 32)
                $bitMD5Encryption = true;
            if($objUser->getStrFinalPass() == self::encryptPassword($strPassword, $bitMD5Encryption) )
                 return true;
        }

        return false;
    }

    public function getCreationOfGroupsAllowed() {
        return true;
    }

    public function getCreationOfUsersAllowed() {
        return true;
    }

	public function getMembersEditable() {
        return true;
    }

    /**
	 * Loads the group identified by the passed id
     *
	 * @param string $strId
     * @return interface_usersources_group or null
	 */
    public function getGroupById($strId) {
        $strQuery = "SELECT group_id FROM "._dbprefix_."user_group_kajona WHERE group_id = ?";

        $arrIds = $this->objDB->getPRow($strQuery, array($strId));
		if(isset($arrIds["group_id"]) && validateSystemid($arrIds["group_id"]))
            return new class_usersources_group_kajona($arrIds["group_id"]);

		return null;
    }

    /**
     * Returns an empty group, e.g. to fetch the fields available and
     * in order to fill a new one.
     *
     * @return interface_usersources_group
     */
    public function getNewGroup() {
        return new class_usersources_group_kajona();
    }

    /**
     * Returns an empty user, e.g. to fetch the fields available and
     * in order to fill a new one.
     *
     * @return interface_usersources_user
     */
    public function getNewUser() {
        return new class_usersources_user_kajona();
    }

    /**
	 * Loads the iser identified by the passed id
     *
	 * @param string $strId
     * @return interface_usersources_user or null
	 */
    public function getUserById($strId) {
        $strQuery = "SELECT user_id FROM "._dbprefix_."user_kajona  WHERE user_id = ? ";

        $arrIds = class_carrier::getInstance()->getObjDB()->getPRow($strQuery, array($strId));
        if(isset($arrIds["user_id"]) && validateSystemid($arrIds["user_id"]))
            return new class_usersources_user_kajona($arrIds["user_id"]);

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
        $strQuery = "SELECT user_id FROM "._dbprefix_."user  WHERE user_username = ? AND user_subsystem = 'kajona'";

        $arrIds = class_carrier::getInstance()->getObjDB()->getPRow($strQuery, array($strUsername));
        if(isset($arrIds["user_id"]) && validateSystemid($arrIds["user_id"]))
            return new class_usersources_user_kajona($arrIds["user_id"]);

		return null;
    }


    /**
     * Encrypts the password, e.g. in order to be validated during logins
     *
     * @param string $strPassword
     * @param bool $bitMD5Encryption
     * @return string
     */
    public static function encryptPassword($strPassword, $bitMD5Encryption = false) {
        if($bitMD5Encryption) //TODO: trigger warning message in logfile
            return md5($strPassword);

        return sha1($strPassword);
    }



    /**
     * Returns an array of group-ids provided by the current source.
     * @return string
     */
    public function getAllGroupIds() {
        $strQuery = "SELECT gk.group_id as group_id
                       FROM "._dbprefix_."user_group_kajona AS gk,
                            "._dbprefix_."user_group AS g
                      WHERE g.group_id = gk.group_id
                      ORDER BY g.group_name";
        $arrRows = class_carrier::getInstance()->getObjDB()->getPArray($strQuery, array());
        $arrReturn = array();
        foreach($arrRows as $arrOneRow) {
            $arrReturn[] = $arrOneRow["group_id"];
        }

        return $arrReturn;
    }



}
