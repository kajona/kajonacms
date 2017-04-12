<?php
/*"******************************************************************************************************
*   (c) 2004-2006 by MulchProductions, www.mulchprod.de                                                 *
*   (c) 2007-2016 by Kajona, www.kajona.de                                                              *
*       Published under the GNU LGPL v2.1, see /system/licence_lgpl.txt                                 *
*-------------------------------------------------------------------------------------------------------*
*	$Id$                                            *
********************************************************************************************************/

namespace Kajona\Ldap\System\Usersources;

use Kajona\Ldap\System\Ldap;
use Kajona\System\System\Carrier;
use Kajona\System\System\Exception;
use Kajona\System\System\Logger;
use Kajona\System\System\Usersources\UsersourcesGroupInterface;
use Kajona\System\System\Usersources\UsersourcesUserInterface;
use Kajona\System\System\Usersources\UsersourcesUsersourceInterface;
use Kajona\System\System\UserUser;


/**
 * Global entry into the ldap-subsystem.
 * Mapps all calls and redirects them to the directory-services.
 * Since 4.8, the class is able to handle various ldap connections
 *
 * @author sidler@mulchprod.de
 * @since 3.4.1
 * @package module_ldap
 */
class UsersourcesSourceLdap implements UsersourcesUsersourceInterface
{

    private $objDB;


    /**
     * Default constructor
     */
    public function __construct()
    {
        $this->objDB = Carrier::getInstance()->getObjDB();
    }

    /**
     * Returns a readable name of the source, e.g. "Kajona" or "LDAP Company 1"
     *
     * @return mixed
     */
    public function getStrReadableName()
    {
        return Carrier::getInstance()->getObjLang()->getLang("usersource_ldap_name", "ldap");
    }

    /**
     * Tries to authenticate a user with the given credentials.
     * The password is unencrypted, each source should take care of its own encryption.
     *
     * @param UsersourcesUserInterface|UsersourcesUserLdap $objUser
     * @param string $strPassword
     *
     * @return bool
     */
    public function authenticateUser(UsersourcesUserInterface $objUser, $strPassword)
    {
        if ($objUser instanceof UsersourcesUserLdap) {
            foreach (Ldap::getAllInstances() as $objSingleLdap) {

                if ($objUser->getIntCfg() != $objSingleLdap->getIntCfgNr()) {
                    continue;
                }

                $objRealUser = new UserUser($objUser->getSystemid());

                $arrSingleUser = $objSingleLdap->getUserdetailsByName($objRealUser->getStrUsername());
                if ($arrSingleUser !== false && count($arrSingleUser) == 1) {
                    $arrSingleUser = $arrSingleUser[0];
                    $bitReturn = $objSingleLdap->authenticateUser($arrSingleUser['identifier'], $strPassword);


                    //synchronize the local data with the ldap-data
                    if ($objUser instanceof UsersourcesUserLdap) {
                        $objUser->setStrFamilyname($arrSingleUser["familyname"]);
                        $objUser->setStrGivenname($arrSingleUser["givenname"]);
                        $objUser->setStrEmail($arrSingleUser["mail"]);
                        $objUser->setStrDN($arrSingleUser["identifier"]);
                        $objUser->setIntCfg($objSingleLdap->getIntCfgNr());
                        $objUser->updateObjectToDb();
                        $this->objDB->flushQueryCache();

                    }

                    return $bitReturn;
                }


            }
        }

        return false;
    }

    /**
     * @return bool
     */
    public function getCreationOfGroupsAllowed()
    {
        return true;
    }

    /**
     * @return bool
     */
    public function getCreationOfUsersAllowed()
    {
        return false;
    }

    /**
     * @return bool
     */
    public function getMembersEditable()
    {
        return false;
    }

    /**
     * Loads the group identified by the passed id
     *
     * @param string $strId
     *
     * @return UsersourcesGroupInterface or null
     */
    public function getGroupById($strId)
    {
        $strQuery = "SELECT group_id FROM " . _dbprefix_ . "user_group WHERE group_id = ? AND group_subsystem = 'ldap'";

        $arrIds = $this->objDB->getPRow($strQuery, array($strId));
        if (isset($arrIds["group_id"]) && validateSystemid($arrIds["group_id"])) {
            return new UsersourcesGroupLdap($arrIds["group_id"]);
        }

        return null;
    }

    /**
     * Returns an empty group, e.g. to fetch the fields available and
     * in order to fill a new one.
     *
     * @return UsersourcesGroupInterface
     */
    public function getNewGroup()
    {
        return new UsersourcesGroupLdap();
    }

    /**
     * Returns an empty user, e.g. to fetch the fields available and
     * in order to fill a new one.
     *
     * @return UsersourcesUserInterface
     */
    public function getNewUser()
    {
        return new UsersourcesUserLdap();
    }

    /**
     * Loads the user identified by the passed id
     *
     * @param string $strId
     *
     * @param bool $bitIgnoreDeletedFlag
     * @return UsersourcesUserInterface or null
     */
    public function getUserById($strId, $bitIgnoreDeletedFlag = false)
    {
        if ($bitIgnoreDeletedFlag) {
            $strQuery = "SELECT user_id FROM " . _dbprefix_ . "user, "._dbprefix_."system WHERE user_id = system_id AND user_id = ? AND user_subsystem = 'ldap'";
        } else {
            $strQuery = "SELECT user_id FROM " . _dbprefix_ . "user, "._dbprefix_."system WHERE user_id = system_id AND user_id = ? AND user_subsystem = 'ldap' AND (system_deleted = 0 OR system_deleted IS NULL)";
        }

        $arrIds = Carrier::getInstance()->getObjDB()->getPRow($strQuery, array($strId));
        if (isset($arrIds["user_id"]) && validateSystemid($arrIds["user_id"])) {
            return new UsersourcesUserLdap($arrIds["user_id"]);
        }

        return null;
    }

    /**
     * Loads the user identified by the passed dn
     *
     * @param string $strUserDn
     *
     * @return UsersourcesUserInterface or null
     */
    public function getUserByDn($strUserDn)
    {
        $strQuery = "SELECT user_ldap_id FROM " . _dbprefix_ . "user_ldap WHERE user_ldap_dn = ?";

        $arrIds = Carrier::getInstance()->getObjDB()->getPRow($strQuery, array($strUserDn));
        if (isset($arrIds["user_ldap_id"]) && validateSystemid($arrIds["user_ldap_id"])) {
            return new UsersourcesUserLdap($arrIds["user_ldap_id"]);
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
     * @return UsersourcesUserInterface or null
     */
    public function getUserByUsername($strUsername)
    {
        $strQuery = "SELECT user_id FROM " . _dbprefix_ . "user, "._dbprefix_."system WHERE user_id = system_id AND user_username = ? AND user_subsystem = 'ldap' AND (system_deleted = 0 OR system_deleted IS NULL)";

        $arrIds = Carrier::getInstance()->getObjDB()->getPRow($strQuery, array($strUsername));
        if (isset($arrIds["user_id"]) && validateSystemid($arrIds["user_id"])) {
            return new UsersourcesUserLdap($arrIds["user_id"]);
        }

        //user not found. search for a matching user in the ldap and add a possible match to the system
        foreach (Ldap::getAllInstances() as $objSingleLdap) {
            $arrDetails = $objSingleLdap->getUserdetailsByName($strUsername);

            if ($arrDetails !== false && count($arrDetails) == 1) {
                $arrSingleUser = $arrDetails[0];
                $objUser = new UserUser();
                $objUser->setStrUsername($strUsername);
                $objUser->setStrSubsystem("ldap");
                $objUser->setIntAdmin(1);
                $objUser->updateObjectToDb();

                /** @var $objSourceUser UsersourcesUserLdap */
                $objSourceUser = $objUser->getObjSourceUser();
                if ($objSourceUser instanceof UsersourcesUserLdap) {
                    $objSourceUser->setStrDN($arrSingleUser["identifier"]);
                    $objSourceUser->setStrFamilyname($arrSingleUser["familyname"]);
                    $objSourceUser->setStrGivenname($arrSingleUser["givenname"]);
                    $objSourceUser->setStrEmail($arrSingleUser["mail"]);
                    $objSourceUser->setIntCfg($objSingleLdap->getIntCfgNr());
                    $objSourceUser->updateObjectToDb();

                    $this->objDB->flushQueryCache();

                    return $objSourceUser;
                }

            }
        }

        return null;
    }

    /**
     * Returns an array of group-ids provided by the current source.
     *
     * @return string[]
     */
    public function getAllGroupIds()
    {
        $strQuery = "SELECT group_id
                       FROM " . _dbprefix_ . "user_group_ldap,
                            " . _dbprefix_ . "user_group
                      WHERE group_id = group_ldap_id
                      ORDER BY group_name";
        $arrRows = Carrier::getInstance()->getObjDB()->getPArray($strQuery, array());
        $arrReturn = array();
        foreach ($arrRows as $arrOneRow) {
            $arrReturn[] = $arrOneRow["group_id"];
        }

        return $arrReturn;
    }

    /**
     * Returns an array of user-ids provided by the current source.
     *
     * @return string[]
     */
    public function getAllUserIds()
    {
        $strQuery = "SELECT user_id
                       FROM " . _dbprefix_ . "user_ldap,
                            " . _dbprefix_ . "user,
                            " . _dbprefix_ . "system
                      WHERE user_id = user_ldap_id
                        AND user_id = system_id
                        AND (system_deleted = 0 OR system_deleted IS NULL)
                      ORDER BY user_username";
        $arrRows = Carrier::getInstance()->getObjDB()->getPArray($strQuery, array());
        $arrReturn = array();
        foreach ($arrRows as $arrOneRow) {
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
    public function updateUserData()
    {
        //sync may take time -> increase the time available
        if (@ini_get("max_execution_time") < 500 && @ini_get("max_execution_time") > 0) {
            @ini_set("max_execution_time", "500");
        }

        //fill all groups - loads new members
        $arrGroups = $this->getAllGroupIds();

        $arrUserIds = array();

        foreach ($arrGroups as $strSingleGroupId) {
            $objGroup = new UsersourcesGroupLdap($strSingleGroupId);
            $arrUserIds = array_merge($arrUserIds, $objGroup->getUserIdsForGroup());
        }

        $arrUserIds = array_unique($arrUserIds);

        //parse all users
        $arrUsers = $this->getAllUserIds();
        foreach ($arrUsers as $strOneUserId) {
            $objUser = new UserUser($strOneUserId);
            /** @var $objSourceUser UsersourcesUserLdap */
            $objSourceUser = $objUser->getObjSourceUser();
            $arrUserDetails = false;
            try {
                $arrUserDetails = Ldap::getInstance($objSourceUser->getIntCfg())->getUserdetailsByName($objUser->getStrUsername());
                if ($arrUserDetails != false && isset($arrUserDetails[0])) {
                    $arrUserDetails = $arrUserDetails[0];
                }
            } catch (Exception $objEx) {
            }
            if ($arrUserDetails !== false && in_array($strOneUserId, $arrUserIds)) {
                $objSourceUser->setStrDN($arrUserDetails["identifier"]);
                $objSourceUser->setStrFamilyname($arrUserDetails["familyname"]);
                $objSourceUser->setStrGivenname($arrUserDetails["givenname"]);
                $objSourceUser->setStrEmail($arrUserDetails["mail"]);
                $objSourceUser->updateObjectToDb();

                $this->objDB->flushQueryCache();
            } else {
                //user seems to be deleted, remove from system, too
                $objUser->deleteObject();
                Logger::getInstance("ldapsync.log")->warning("Deleting user " . $strOneUserId . " / " . $objUser->getStrUsername() . " @ " . $objSourceUser->getStrDN());
            }
        }

        return true;
    }

}
