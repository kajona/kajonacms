<?php
/*"******************************************************************************************************
*   (c) 2004-2006 by MulchProductions, www.mulchprod.de                                                 *
*   (c) 2007-2015 by Kajona, www.kajona.de                                                              *
*       Published under the GNU LGPL v2.1, see /system/licence_lgpl.txt                                 *
*-------------------------------------------------------------------------------------------------------*
*	$Id$                                            *
********************************************************************************************************/

/**
 * Global entry into the ldap-subsystem.
 * Mapps all calls and redirects them to the directory-services.
 * Since 4.8, the class is able to handle various ldap connections
 *
 * @author sidler@mulchprod.de
 * @since 3.4.1
 * @package module_ldap
 */
class class_usersources_source_ldap implements interface_usersources_usersource {

    private $objDB;

    /**
     * Default constructor
     */
    public function __construct() {
        $this->objDB = class_carrier::getInstance()->getObjDB();
    }

    /**
     * Returns a readable name of the source, e.g. "Kajona" or "LDAP Company 1"
     *
     * @return mixed
     */
    public function getStrReadableName() {
        return class_carrier::getInstance()->getObjLang()->getLang("usersource_ldap_name", "ldap");
    }

    /**
     * Tries to authenticate a user with the given credentials.
     * The password is unencrypted, each source should take care of its own encryption.
     *
     * @param interface_usersources_user|class_usersources_user_ldap $objUser
     * @param string $strPassword
     *
     * @return bool
     */
    public function authenticateUser(interface_usersources_user $objUser, $strPassword) {
        if($objUser instanceof class_usersources_user_ldap) {
            foreach(class_ldap::getAllInstances() as $objSingleLdap) {
                $bitReturn = $objSingleLdap->authenticateUser($objUser->getStrDN(), $strPassword);

                //synchronize the local data with the ldap-data
                if($bitReturn === true) {
                    $arrSingleUser = $objSingleLdap->getUserDetailsByDN($objUser->getStrDN());

                    if($arrSingleUser !== false) {
                        if($objUser instanceof class_usersources_user_ldap) {
                            $objUser->setStrFamilyname($arrSingleUser["familyname"]);
                            $objUser->setStrGivenname($arrSingleUser["givenname"]);
                            $objUser->setStrEmail($arrSingleUser["mail"]);
                            $objUser->updateObjectToDb();
                            $this->objDB->flushQueryCache();
                        }

                    }
                }

                return $bitReturn;
            }
        }

        return false;
    }

    /**
     * @return bool
     */
    public function getCreationOfGroupsAllowed() {
        return true;
    }

    /**
     * @return bool
     */
    public function getCreationOfUsersAllowed() {
        return false;
    }

    /**
     * @return bool
     */
    public function getMembersEditable() {
        return false;
    }

    /**
     * Loads the group identified by the passed id
     *
     * @param string $strId
     *
     * @return interface_usersources_group or null
     */
    public function getGroupById($strId) {
        $strQuery = "SELECT group_id FROM "._dbprefix_."user_group WHERE group_id = ? AND group_subsystem = 'ldap'";

        $arrIds = $this->objDB->getPRow($strQuery, array($strId));
        if(isset($arrIds["group_id"]) && validateSystemid($arrIds["group_id"])) {
            return new class_usersources_group_ldap($arrIds["group_id"]);
        }

        return null;
    }

    /**
     * Returns an empty group, e.g. to fetch the fields available and
     * in order to fill a new one.
     *
     * @return interface_usersources_group
     */
    public function getNewGroup() {
        return new class_usersources_group_ldap();
    }

    /**
     * Returns an empty user, e.g. to fetch the fields available and
     * in order to fill a new one.
     *
     * @return interface_usersources_user
     */
    public function getNewUser() {
        return new class_usersources_user_ldap();
    }

    /**
     * Loads the iser identified by the passed id
     *
     * @param string $strId
     *
     * @return interface_usersources_user or null
     */
    public function getUserById($strId) {
        $strQuery = "SELECT user_id FROM "._dbprefix_."user WHERE user_id = ? AND user_subsystem = 'ldap'";

        $arrIds = class_carrier::getInstance()->getObjDB()->getPRow($strQuery, array($strId));
        if(isset($arrIds["user_id"]) && validateSystemid($arrIds["user_id"])) {
            return new class_usersources_user_ldap($arrIds["user_id"]);
        }

        return null;
    }

    /**
     * Loads the user identified by the passed dn
     *
     * @param string $strUserDn
     *
     * @return interface_usersources_user or null
     */
    public function getUserByDn($strUserDn) {
        $strQuery = "SELECT user_ldap_id FROM "._dbprefix_."user_ldap WHERE user_ldap_dn = ?";

        $arrIds = class_carrier::getInstance()->getObjDB()->getPRow($strQuery, array($strUserDn));
        if(isset($arrIds["user_ldap_id"]) && validateSystemid($arrIds["user_ldap_id"])) {
            return new class_usersources_user_ldap($arrIds["user_ldap_id"]);
        }

        return null;
    }


    /**
     * Loads the user identified by the passed name.
     * This method may be called during the authentication of users and may be used as a hook
     * in order to create new users in the central database not yet existing.
     *
     * @param string $strUsername
     *
     * @return interface_usersources_user or null
     */
    public function getUserByUsername($strUsername) {
        $strQuery = "SELECT user_id FROM "._dbprefix_."user WHERE user_username = ? AND user_subsystem = 'ldap'";

        $arrIds = class_carrier::getInstance()->getObjDB()->getPRow($strQuery, array($strUsername));
        if(isset($arrIds["user_id"]) && validateSystemid($arrIds["user_id"])) {
            return new class_usersources_user_ldap($arrIds["user_id"]);
        }

        //user not found. search for a matching user in the ldap and add a possible match to the system
        foreach(class_ldap::getAllInstances() as $objSingleLdap) {
            $arrDetails = $objSingleLdap->getUserdetailsByName($strUsername);

            if($arrDetails !== false && count($arrDetails) == 1) {
                $arrSingleUser = $arrDetails[0];
                $objUser = new class_module_user_user();
                $objUser->setStrUsername($strUsername);
                $objUser->setStrSubsystem("ldap");
                $objUser->setIntActive(1);
                $objUser->setIntAdmin(1);
                $objUser->updateObjectToDb();

                /** @var $objSourceUser class_usersources_user_ldap */
                $objSourceUser = $objUser->getObjSourceUser();
                if($objSourceUser instanceof class_usersources_user_ldap) {
                    $objSourceUser->setStrDN($arrSingleUser["identifier"]);
                    $objSourceUser->setStrFamilyname($arrSingleUser["familyname"]);
                    $objSourceUser->setStrGivenname($arrSingleUser["givenname"]);
                    $objSourceUser->setStrEmail($arrSingleUser["mail"]);
                    $objSourceUser->updateObjectToDb();

                    $this->objDB->flushQueryCache();
                }

                return $objUser;
            }
        }

        return null;
    }

    /**
     * Returns an array of group-ids provided by the current source.
     *
     * @return string[]
     */
    public function getAllGroupIds() {
        $strQuery = "SELECT group_id
                       FROM "._dbprefix_."user_group_ldap,
                            "._dbprefix_."user_group
                      WHERE group_id = group_ldap_id
                      ORDER BY group_name";
        $arrRows = class_carrier::getInstance()->getObjDB()->getPArray($strQuery, array());
        $arrReturn = array();
        foreach($arrRows as $arrOneRow) {
            $arrReturn[] = $arrOneRow["group_id"];
        }

        return $arrReturn;
    }

    /**
     * Returns an array of user-ids provided by the current source.
     *
     * @return string[]
     */
    public function getAllUserIds() {
        $strQuery = "SELECT user_id
                       FROM "._dbprefix_."user_ldap,
                            "._dbprefix_."user
                      WHERE user_id = user_ldap_id
                      ORDER BY user_username";
        $arrRows = class_carrier::getInstance()->getObjDB()->getPArray($strQuery, array());
        $arrReturn = array();
        foreach($arrRows as $arrOneRow) {
            $arrReturn[] = $arrOneRow["user_id"];
        }

        return $arrReturn;
    }


    /**
     * Updates all user-data stored in the system.
     * This may be a long-running task, so execute this only explicitly
     * and not during common requests!
     *
     * @return bool
     */
    public function updateUserData() {
        //sync may take time -> increase the time available
        if(@ini_get("max_execution_time") < 500 && @ini_get("max_execution_time") > 0) {
            @ini_set("max_execution_time", "500");
        }

        $objLdap = class_ldap::getInstance();

        //fill all groups - loads new members
        $arrGroups = $this->getAllGroupIds();
        foreach($arrGroups as $strSingleGroupId) {
            $objGroup = new class_usersources_group_ldap($strSingleGroupId);
            $objGroup->getUserIdsForGroup();
        }

        //parse all users
        $arrUsers = $this->getAllUserIds();
        foreach($arrUsers as $strOneUserId) {
            $objUser = new class_module_user_user($strOneUserId);
            /** @var $objSourceUser class_usersources_user_ldap */
            $objSourceUser = $objUser->getObjSourceUser();
            $arrUserDetails = false;
            try {
                $arrUserDetails = $objLdap->getUserDetailsByDN($objSourceUser->getStrDN());
            }
            catch(class_exception $objEx) {
            }
            if($arrUserDetails !== false) {
                $objSourceUser->setStrDN($arrUserDetails["identifier"]);
                $objSourceUser->setStrFamilyname($arrUserDetails["familyname"]);
                $objSourceUser->setStrGivenname($arrUserDetails["givenname"]);
                $objSourceUser->setStrEmail($arrUserDetails["mail"]);
                $objSourceUser->updateObjectToDb();

                $this->objDB->flushQueryCache();
            }
            else {
                //user seems to be delete, remove from system, too
                $objUser->deleteObject();
            }
        }

        return true;

    }

}
