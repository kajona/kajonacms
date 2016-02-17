<?php
/*"******************************************************************************************************
*   (c) 2004-2006 by MulchProductions, www.mulchprod.de                                                 *
*   (c) 2007-2015 by Kajona, www.kajona.de                                                              *
*       Published under the GNU LGPL v2.1, see /system/licence_lgpl.txt                                 *
********************************************************************************************************/

namespace Kajona\System\System;

use Kajona\System\System\Usersources\UsersourcesGroupInterface;
use Kajona\System\System\Usersources\UsersourcesUserInterface;
use Kajona\System\System\Usersources\UsersourcesUsersourceInterface;


/**
 * The sourcefactory holds references to all subsystems and manages the global access.
 * It resolves the leightweight objects into its "real" objects provided by the subsystems
 * and takes care of global functionalities such as authentication of users.
 *
 * @author sidler@mulchprod.de
 * @since 3.4.1
 * @package module_user
 */
class UserSourcefactory
{

    private $arrSubsystemsAvailable = array("kajona");

    /**
     * Default constructor
     */
    public function __construct()
    {

        //try to load the list of subsystems available
        $strConfig = Carrier::getInstance()->getObjConfig()->getConfig("loginproviders");
        if ($strConfig != "") {
            $this->arrSubsystemsAvailable = explode(",", $strConfig);
        }
    }

    /**
     * Tries to find a group identified by its name in the configured subsystems.
     * If given, the first match is returned.
     * Please note that the leightweight object is returned!
     *
     * @param string $strName
     *
     * @return UserGroup or null
     */
    public function getGroupByName($strName)
    {

        //validate if a group with the given name is available
        $strQuery = "SELECT group_id FROM "._dbprefix_."user_group where group_name = ?";
        $arrRow = Carrier::getInstance()->getObjDB()->getPRow($strQuery, array($strName));

        if (isset($arrRow["group_id"]) && validateSystemid($arrRow["group_id"])) {
            return new UserGroup($arrRow["group_id"]);
        }

        //nothing found
        return null;
    }

    /**
     * Returns a list of groups matching the passed query-term.
     *
     * @param string $strName
     *
     * @return UserGroup[]
     */
    public function getGrouplistByQuery($strName)
    {

        //validate if a group with the given name is available
        $strQuery = "SELECT group_id, group_subsystem FROM "._dbprefix_."user_group where group_name LIKE ?";
        $arrRows = Carrier::getInstance()->getObjDB()->getPArray($strQuery, array($strName."%"));

        $arrReturn = array();
        foreach ($arrRows as $arrOneRow) {
            if (in_array($arrOneRow["group_subsystem"], $this->arrSubsystemsAvailable)) {
                $arrReturn[] = new UserGroup($arrOneRow["group_id"]);
            }
        }
        return $arrReturn;
    }

    /**
     * Tries to find an user identified by its name in the configured subsystems.
     * If given, the first match is returned.
     * Please note that the lightweight object is returned!
     *
     * @param string $strName
     *
     * @return UserUser or null
     */
    public function getUserByUsername($strName)
    {

        //validate if a group with the given name is available

        if (SystemModule::getModuleByName("user") != null && version_compare(SystemModule::getModuleByName("user")->getStrVersion(), "4.5", ">=")) {
            $strQuery = "SELECT user_id FROM "._dbprefix_."user where user_username = ? AND (user_deleted = 0 OR user_deleted IS NULL)";
        }
        else {
            $strQuery = "SELECT user_id FROM "._dbprefix_."user where user_username = ?";
        }

        $arrRow = Carrier::getInstance()->getObjDB()->getPRow($strQuery, array($strName));

        if (isset($arrRow["user_id"]) && validateSystemid($arrRow["user_id"])) {
            return new UserUser($arrRow["user_id"]);
        }

        //since some login-provides may trigger additional searches, query them now
        foreach ($this->arrSubsystemsAvailable as $strOneSubsystem) {
            $objUser = $this->getUsersource($strOneSubsystem)->getUserByUsername($strName);
            //convert the user to a real one
            if ($objUser != null) {
                return new UserUser($objUser->getSystemid());
            }
        }

        //nothing found
        return null;
    }

    /**
     * Creates a list of all users matching the current query.
     * Only active users may be returned!
     *
     * @param string $strParam
     *
     * @return UserUser
     */
    public function getUserlistByUserquery($strParam)
    {

        $strDbPrefix = _dbprefix_;
        //validate if a group with the given name is available
        if (version_compare(SystemModule::getModuleByName("user")->getStrVersion(), "4.5", ">=")) {

            $strQuery = "SELECT user_tbl.user_id, user_tbl.user_subsystem
                          FROM {$strDbPrefix}user AS user_tbl
                          LEFT JOIN {$strDbPrefix}user_kajona AS user_kajona ON user_tbl.user_id = user_kajona.user_id
                          WHERE
                              (user_tbl.user_username LIKE ? OR user_kajona.user_forename LIKE ? OR user_kajona.user_name LIKE ?)

                              AND (user_tbl.user_deleted = 0 OR user_tbl.user_deleted IS NULL)
                              AND user_active = 1
                          ORDER BY user_tbl.user_username, user_tbl.user_subsystem ASC";

            $arrParams = array("%".$strParam."%", "%".$strParam."%", "%".$strParam."%");
        }
        else {
            $strQuery = "SELECT user_id, user_subsystem FROM {$strDbPrefix}user where user_username LIKE ? AND user_active = 1";
            $arrParams = array("%".$strParam."%");
        }

        $arrRows = Carrier::getInstance()->getObjDB()->getPArray($strQuery, $arrParams);

        $arrReturn = array();
        foreach ($arrRows as $arrOneRow) {
            if (in_array($arrOneRow["user_subsystem"], $this->arrSubsystemsAvailable)) {
                $arrReturn[] = new UserUser($arrOneRow["user_id"]);
            }
        }

        return $arrReturn;
    }


    /**
     * Tries to authenticate a user identified by its username and password.
     * If given the leightweight user-object is returned.
     * Otherwise null is returned AND an authentication-exception is being raised.
     * Only logins with username and password are allowed. This avoids problems with
     * mis-configured systems such as MS AD.
     *
     * @param string $strName
     * @param string $strPassword
     *
     * @throws AuthenticationException
     * @return UsersourcesUserInterface
     */
    public function authenticateUser($strName, $strPassword)
    {
        if(empty($strName) || empty($strPassword)) {
            throw new AuthenticationException("user ".$strName." could not be authenticated", AuthenticationException::$level_ERROR);
        }

        $objUser = $this->getUserByUsername($strName);
        if ($objUser != null) {
            //validate if the user is assigned to at least a single group
            if(empty($objUser->getArrGroupIds())) {
                throw new AuthenticationException("user ".$strName." is not assigned to at least a single group", AuthenticationException::$level_ERROR);
            }

            $objSubsystem = $this->getUsersource($objUser->getStrSubsystem());
            $objPlainUser = $objSubsystem->getUserById($objUser->getSystemid());


            if ($objPlainUser != null && $objSubsystem->authenticateUser($objPlainUser, $strPassword)) {
                return true;
            }
        }

        throw new AuthenticationException("user ".$strName." could not be authenticated", AuthenticationException::$level_ERROR);
    }

    /**
     * Returns the fully featured group-instance created by the matching subsystem.
     *
     * @param UserGroup $objLeightweightGroup
     *
     * @return UsersourcesGroupInterface
     */
    public function getSourceGroup(UserGroup $objLeightweightGroup)
    {
        $objSubsystem = $this->getUsersource($objLeightweightGroup->getStrSubsystem());
        $objPlainGroup = $objSubsystem->getGroupById($objLeightweightGroup->getSystemid());
        return $objPlainGroup;
    }

    /**
     * Returns the fully featured user-instance created by the matching subsystem.
     *
     * @param UserUser $objLeightweightUser
     *
     * @throws Exception
     * @return UsersourcesUserInterface
     */
    public function getSourceUser(UserUser $objLeightweightUser)
    {
        if ($objLeightweightUser->getIntDeleted() == 1) {
            throw new Exception("User was deleted, source user no longer available", Exception::$level_ERROR);
        }

        $objSubsystem = $this->getUsersource($objLeightweightUser->getStrSubsystem());
        $objPlainUser = $objSubsystem->getUserById($objLeightweightUser->getSystemid());
        return $objPlainUser;
    }

    /**
     * Tries to resolve the subsystem identified by the passed name.
     * Returns an instance of the usersource identified by its classname.
     * The classname is build by the schema class_usersources_source_$strName
     *
     * @param string $strName
     *
     * @throws Exception
     * @return UsersourcesUsersourceInterface or null if not existing, an exception is raised, too.
     */
    public function getUsersource($strName)
    {
        $strName = trim($strName);
        if ($strName == "") {
            throw new Exception("login provider ".$strName." not existing", Exception::$level_ERROR);
        }

        $strClassname = "class_usersources_source_".$strName;
        if (!class_exists($strClassname)) {
            throw new Exception("login provider ".$strName." not existing", Exception::$level_ERROR);
        }

        $objSubsystem = new $strClassname();
        return $objSubsystem;
    }

    /**
     * Returns an array of all user-subsystem-identifiers available.
     *
     * @return string[]
     */
    public function getArrUsersources()
    {
        return $this->arrSubsystemsAvailable;
    }

}

