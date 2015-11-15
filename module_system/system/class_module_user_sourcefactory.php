<?php
/*"******************************************************************************************************
*   (c) 2004-2006 by MulchProductions, www.mulchprod.de                                                 *
*   (c) 2007-2015 by Kajona, www.kajona.de                                                              *
*       Published under the GNU LGPL v2.1, see /system/licence_lgpl.txt                                 *
********************************************************************************************************/


/**
 * The sourcefactory holds references to all subsystems and manages the global access.
 * It resolves the leightweight objects into its "real" objects provided by the subsystems
 * and takes care of global functionalities such as authentication of users.
 *
 * @author sidler@mulchprod.de
 * @since 3.4.1
 * @package module_user
 */
class class_module_user_sourcefactory {

    private $arrSubsystemsAvailable = array("kajona");

    /**
     * Default constructor
     */
    public function __construct() {

        //try to load the list of subsystems available
        $strConfig = class_carrier::getInstance()->getObjConfig()->getConfig("loginproviders");
        if($strConfig != "") {
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
     * @return class_module_user_group or null
     */
    public function getGroupByName($strName) {

        //validate if a group with the given name is available
        $strQuery = "SELECT group_id FROM " . _dbprefix_ . "user_group where group_name = ?";
        $arrRow = class_carrier::getInstance()->getObjDB()->getPRow($strQuery, array($strName));

        if(isset($arrRow["group_id"]) && validateSystemid($arrRow["group_id"])) {
            return new class_module_user_group($arrRow["group_id"]);
        }

        //nothing found
        return null;
    }

    /**
     * Returns a list of groups matching the passed query-term.
     *
     * @param string $strName
     *
     * @return class_module_user_group[]
     */
    public function getGrouplistByQuery($strName) {

        //validate if a group with the given name is available
        $strQuery = "SELECT group_id, group_subsystem FROM " . _dbprefix_ . "user_group where group_name LIKE ?";
        $arrRows = class_carrier::getInstance()->getObjDB()->getPArray($strQuery, array($strName . "%"));

        $arrReturn = array();
        foreach($arrRows as $arrOneRow) {
            if(in_array($arrOneRow["group_subsystem"], $this->arrSubsystemsAvailable)) {
                $arrReturn[] = new class_module_user_group($arrOneRow["group_id"]);
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
     * @return class_module_user_user or null
     */
    public function getUserByUsername($strName) {

        //validate if a group with the given name is available

        if(class_module_system_module::getModuleByName("user") != null && version_compare(class_module_system_module::getModuleByName("user")->getStrVersion(), "4.5", ">="))
            $strQuery = "SELECT user_id FROM " . _dbprefix_ . "user where user_username = ? AND (user_deleted = 0 OR user_deleted IS NULL)";
        else
            $strQuery = "SELECT user_id FROM " . _dbprefix_ . "user where user_username = ?";

        $arrRow = class_carrier::getInstance()->getObjDB()->getPRow($strQuery, array($strName));

        if(isset($arrRow["user_id"]) && validateSystemid($arrRow["user_id"])) {
            return new class_module_user_user($arrRow["user_id"]);
        }

        //since some login-provides may trigger additional searches, query them now
        foreach($this->arrSubsystemsAvailable as $strOneSubsystem) {
            $objUser = $this->getUsersource($strOneSubsystem)->getUserByUsername($strName);
            //convert the user to a real one
            if($objUser != null) {
                return new class_module_user_user($objUser->getSystemid());
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
     * @return class_module_user_user
     */
    public function getUserlistByUserquery($strParam) {

        $strDbPrefix = _dbprefix_;
        //validate if a group with the given name is available
        if(version_compare(class_module_system_module::getModuleByName("user")->getStrVersion(), "4.5", ">=")) {

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

        $arrRows = class_carrier::getInstance()->getObjDB()->getPArray($strQuery, $arrParams);

        $arrReturn = array();
        foreach($arrRows as $arrOneRow) {
            if(in_array($arrOneRow["user_subsystem"], $this->arrSubsystemsAvailable)) {
                $arrReturn[] = new class_module_user_user($arrOneRow["user_id"]);
            }
        }

        return $arrReturn;
    }


    /**
     * Tries to authenticate a user identified by its username and password.
     * If given the leightweight user-object is returned.
     * Otherwise null is returned AND an authentication-exception is being raised.
     *
     * @param string $strName
     * @param string $strPassword
     *
     * @throws class_authentication_exception
     * @return interface_usersources_user
     */
    public function authenticateUser($strName, $strPassword) {
        $objUser = $this->getUserByUsername($strName);
        if($objUser != null) {
            $objSubsystem = $this->getUsersource($objUser->getStrSubsystem());
            $objPlainUser = $objSubsystem->getUserById($objUser->getSystemid());

            if($objPlainUser != null && $objSubsystem->authenticateUser($objPlainUser, $strPassword)) {
                return true;
            }
        }


        throw new class_authentication_exception("user " . $strName . " could not be authenticated", class_exception::$level_ERROR);
    }

    /**
     * Returns the fully featured group-instance created by the matching subsystem.
     *
     * @param class_module_user_group $objLeightweightGroup
     *
     * @return interface_usersources_group
     */
    public function getSourceGroup(class_module_user_group $objLeightweightGroup) {
        $objSubsystem = $this->getUsersource($objLeightweightGroup->getStrSubsystem());
        $objPlainGroup = $objSubsystem->getGroupById($objLeightweightGroup->getSystemid());
        return $objPlainGroup;
    }

    /**
     * Returns the fully featured user-instance created by the matching subsystem.
     *
     * @param class_module_user_user $objLeightweightUser
     *
     * @throws class_exception
     * @return interface_usersources_user
     */
    public function getSourceUser(class_module_user_user $objLeightweightUser) {
        if($objLeightweightUser->getIntDeleted() == 1)
            throw new class_exception("User was deleted, source user no longer available", class_exception::$level_ERROR);

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
     * @throws class_exception
     * @return interface_usersources_usersource or null if not existing, an exception is raised, too.
     */
    public function getUsersource($strName) {
        $strName = trim($strName);
        if($strName == "") {
            throw new class_exception("login provider " . $strName . " not existing", class_exception::$level_ERROR);
        }

        $strClassname = "class_usersources_source_" . $strName;
        if(!class_exists($strClassname)) {
            throw new class_exception("login provider " . $strName . " not existing", class_exception::$level_ERROR);
        }

        $objSubsystem = new $strClassname();
        return $objSubsystem;
    }

    /**
     * Returns an array of all user-subsystem-identifiers available.
     *
     * @return string[]
     */
    public function getArrUsersources() {
        return $this->arrSubsystemsAvailable;
    }

}

