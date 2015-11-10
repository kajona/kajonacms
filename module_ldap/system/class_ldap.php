<?php
/*"******************************************************************************************************
*   (c) 2004-2006 by MulchProductions, www.mulchprod.de                                                 *
*   (c) 2007-2015 by Kajona, www.kajona.de                                                              *
*       Published under the GNU LGPL v2.1, see /system/licence_lgpl.txt                                 *
*-------------------------------------------------------------------------------------------------------*
*	$Id$                                               *
********************************************************************************************************/

/**
 * The class_ldap acts as a ldap-connector and is used by the usersources-subsystem as a login-provider.
 * It is configured by the ldap.php file located at /system/config.
 * Please refer to this file in order to see how source-systes may be connected.
 *
 * @package module_ldap
 * @author sidler@mulchprod.de
 * @since 3.4.1
 * @see /system/config/ldap.php
 */
class class_ldap
{

    /**
     * @var array
     */
    private $arrConfig;

    /**
     * @var Resource
     */
    private $objCx = null;

    /**
     * @var class_ldap[]
     */
    private static $arrInstances = null;

    /**
     * Constructor
     *
     * @param int $intConfigNumber
     */
    private function __construct($intConfigNumber = 0)
    {
        $this->arrConfig = class_config::getInstance("ldap.php")->getConfig($intConfigNumber);
        $this->arrConfig["nr"] = $intConfigNumber;

        $this->connect();
    }

    public function __destruct()
    {
        ldap_close($this->objCx);
    }


    /**
     * Connects to the ldap-server.
     * If no connection is possible, an exception is thrown.
     */
    private function connect()
    {
        if ($this->objCx == null) {
            $this->objCx = ldap_connect($this->arrConfig["ldap_server"], $this->arrConfig["ldap_port"]);

            class_logger::getInstance(class_logger::USERSOURCES)->addLogRow("new ldap-connection to ".$this->arrConfig["ldap_server"].":".$this->arrConfig["ldap_port"], class_logger::$levelInfo);

            $this->internalBind();
        }
    }

    /**
     * Returns an instance of class_ldap, the connection is setup on first call.
     *
     * @param int $intConfigNumber
     *
     * @return class_ldap
     */
    public static function getInstance($intConfigNumber = 0)
    {
        self::getAllInstances();
        return self::$arrInstances[$intConfigNumber];
    }

    /**
     * Returns instances for each configured ldap source
     *
     * @return class_ldap[]
     */
    public static function getAllInstances()
    {
        if (self::$arrInstances == null) {
            $intI = 0;
            while (is_array(class_config::getInstance("ldap.php")->getConfig($intI))) {
                self::$arrInstances[$intI] = new class_ldap($intI);
                $intI++;
            }
        }

        return self::$arrInstances;
    }

    /**
     * Authenticates an user against the current ldap-connection.
     * Please be aware that this method only tries to authenticate the user,
     * the binding is released immediately. Afterwards the credentials
     * given in the config-file are used again.
     *
     * @param string $strUsername
     * @param string $strPassword
     * @param string $strContext
     *
     * @return bool
     */
    public function authenticateUser($strUsername, $strPassword, $strContext = "")
    {

        if ($strContext != "") {
            $strUsername = $this->arrConfig["ldap_common_name"]."=".$strUsername.",".$strContext;
        }

        $bitBind = @ldap_bind($this->objCx, $strUsername, $strPassword);
        $this->internalBind();

        return $bitBind;
    }

    /**
     * Tries to bind to the ldap-server.
     * If no binding is possible, an exception is thrown.
     *
     * @throws class_exception
     */
    private function internalBind()
    {
        $bitBind = false;
        if ($this->arrConfig["ldap_bind_anonymous"] === true) {
            $bitBind = @ldap_bind($this->objCx);
        }
        else {
            $bitBind = @ldap_bind(
                $this->objCx,
                $this->arrConfig["ldap_bind_username"],
                $this->arrConfig["ldap_bind_userpwd"]
            );
        }

        if ($bitBind === false) {
            throw new class_exception("ldap bind failed: ".ldap_errno($this->objCx)." # ".ldap_error($this->objCx), class_exception::$level_FATALERROR);
        }
        else {
            class_logger::getInstance(class_logger::USERSOURCES)->addLogRow("ldap bind succeeded: ".$this->arrConfig["ldap_server"].":".$this->arrConfig["ldap_port"], class_logger::$levelInfo);
        }
    }

    /**
     * Loads all members of the passed group-identifier.
     * This list may not be limited to users, all members are returned.
     *
     * @param string $strGroupDN
     *
     * @throws class_exception
     * @return string[] array of distinguished names
     */
    public function getMembersOfGroup($strGroupDN)
    {
        $arrReturn = array();

        //search the group itself
        $objResult = @ldap_search($this->objCx, $strGroupDN, $this->arrConfig["ldap_group_filter"]);

        if ($objResult !== false) {
            class_logger::getInstance(class_logger::USERSOURCES)->addLogRow("ldap-search found ".ldap_count_entries($this->objCx, $objResult)." entries", class_logger::$levelInfo);

            $arrResult = @ldap_first_entry($this->objCx, $objResult);
            while ($arrResult !== false) {
                $arrValues = @ldap_get_values($this->objCx, $arrResult, $this->arrConfig["ldap_group_attribute_member"]);
                if(!empty($arrValues)) {
                    foreach ($arrValues as $strKey => $strSingleValue) {
                        if ($strKey !== "count") {
                            $arrReturn[] = utf8_encode($strSingleValue);
                        }
                    }
                }

                $arrResult = @ldap_next_entry($this->objCx, $arrResult);
            }
        }
        else {
            throw new class_exception("loading of group members failed: ".ldap_errno($this->objCx)." # ".ldap_error($this->objCx), class_exception::$level_FATALERROR);
        }

        return $arrReturn;
    }

    /**
     * Counts the number of group-members
     * This list may not be limited to users, all members are returned as defined by the filter
     *
     * @param string $strGroupDN
     *
     * @throws class_exception
     * @return int
     */
    public function getNumberOfGroupMembers($strGroupDN)
    {
        //search the group itself
        $objResult = @ldap_search($this->objCx, $strGroupDN, $this->arrConfig["ldap_group_filter"]);

        if ($objResult !== false) {
            class_logger::getInstance(class_logger::USERSOURCES)->addLogRow("ldap-search found ".ldap_count_entries($this->objCx, $objResult)." entries", class_logger::$levelInfo);
            $arrResult = @ldap_first_entry($this->objCx, $objResult);
            if ($arrResult !== false) {
                $arrValues = @ldap_get_values($this->objCx, $arrResult, $this->arrConfig["ldap_group_attribute_member"]);
                return $arrValues["count"];
            }
        }
        else {
            throw new class_exception("loading of number of group failed: ".ldap_errno($this->objCx)." # ".ldap_error($this->objCx), class_exception::$level_FATALERROR);
        }
        return -1;
    }

    /**
     * Validates if a single user is member of a given group
     *
     * @param string $strUserDN
     * @param string $strGroupDN
     *
     * @throws class_exception
     * @return boolean
     */
    public function isUserMemberOfGroup($strUserDN, $strGroupDN)
    {
        $bitReturn = false;

        //search the group itself
        $strQuery = $this->arrConfig["ldap_group_isUserMemberOf"];
        //double encode backslashes
        $strUserDN = uniStrReplace("\\,", "\\\\,", utf8_decode($strUserDN));
        $strQuery = uniStrReplace("?", $strUserDN, $strQuery);
        $objResult = @ldap_search($this->objCx, $strGroupDN, $strQuery);

        if ($objResult !== false) {
            $intCount = ldap_count_entries($this->objCx, $objResult);
            if ($intCount == 1) {
                $bitReturn = true;
            }
            else {
                $bitReturn = false;
            }

        }
        else {
            throw new class_exception("loading of group-memberships failed: ".ldap_errno($this->objCx)." # ".ldap_error($this->objCx), class_exception::$level_FATALERROR);
        }

        return $bitReturn;
    }

    /**
     * Useful to trigger a manual search query
     *
     * @param $strBaseDn
     * @param $strQuery
     *
     * @param $arrReturnValues
     *
     * @return array
     * @throws class_exception
     */
    public function customSearch($strBaseDn, $strQuery, $arrReturnValues)
    {
        $objResult = @ldap_search($this->objCx, $strBaseDn, $strQuery);
        if ($objResult !== false) {
            $arrResult = @ldap_first_entry($this->objCx, $objResult);

            $arrReturn = array();
            while ($arrResult !== false) {
                $arrRow= array();
                foreach($arrReturnValues as $strOneAttribute) {
                    $arrRow[$strOneAttribute] = $this->getStrAttribute($arrResult, $strOneAttribute);
                }
                $arrReturn[] = $arrRow;
                $arrResult = @ldap_next_entry($this->objCx, $arrResult);
            }

            return $arrReturn;
        }
        else {
            throw new class_exception("loading of custom search failed: ".ldap_errno($this->objCx)." # ".ldap_error($this->objCx), class_exception::$level_FATALERROR);
        }

    }


    /**
     * Returns an array of user-details for the user identified by the passed username.
     * Since there could be multiple hits, an array of arrays is returned
     *
     * @param string $strUsername
     *
     * @throws class_exception
     * @return string array of hits, each hit an array details, false in case of errors
     */
    public function getUserDetailsByDN($strUsername)
    {
        $arrReturn = false;

        //search the group itself
        $objResult = @ldap_search($this->objCx, utf8_decode($strUsername), $this->arrConfig["ldap_user_filter"]);

        if ($objResult !== false) {
            $arrReturn = array();
            class_logger::getInstance(class_logger::USERSOURCES)->addLogRow("ldap-search found ".ldap_count_entries($this->objCx, $objResult)." entries", class_logger::$levelInfo);

            $arrResult = @ldap_first_entry($this->objCx, $objResult);
            while ($arrResult !== false) {

                $arrReturn = array();
                $arrReturn["username"] = $this->getStrAttribute($arrResult, $this->arrConfig["ldap_user_attribute_username"]);
                $arrReturn["mail"] = $this->getStrAttribute($arrResult, $this->arrConfig["ldap_user_attribute_mail"]);
                if ($arrReturn["mail"] == "") {
                    $arrReturn["mail"] = $this->getStrAttribute($arrResult, $this->arrConfig["ldap_user_attribute_mail_fallback"]);
                }
                $arrReturn["familyname"] = $this->getStrAttribute($arrResult, $this->arrConfig["ldap_user_attribute_familyname"]);
                $arrReturn["givenname"] = $this->getStrAttribute($arrResult, $this->arrConfig["ldap_user_attribute_givenname"]);
                $arrReturn["identifier"] = $this->getStrAttribute($arrResult, $this->arrConfig["ldap_common_identifier"]);


                $arrResult = ldap_next_entry($this->objCx, $arrResult);
            }
        }
        else {
            throw new class_exception("loading of user failed: ".ldap_errno($this->objCx)." # ".ldap_error($this->objCx), class_exception::$level_FATALERROR);
        }

        return $arrReturn;
    }

    /**
     * Searches for an user identified by the passed username.
     * The result is limited to the path set up via the config-file.
     *
     * @param string $strUsername
     *
     * @throws class_exception
     * @return string array of userdetails, false in case of errors
     */
    public function getUserdetailsByName($strUsername)
    {
        $arrReturn = false;

        $strUserFilter = $this->arrConfig["ldap_user_search_filter"];
        $strUserFilter = uniStrReplace("?", utf8_decode($strUsername), $strUserFilter);


        //search the group itself
        $objResult = @ldap_search($this->objCx, $this->arrConfig["ldap_user_base_dn"], $strUserFilter);

        if ($objResult !== false) {
            class_logger::getInstance(class_logger::USERSOURCES)->addLogRow("ldap-search found ".ldap_count_entries($this->objCx, $objResult)." entries", class_logger::$levelInfo);

            $arrResult = @ldap_first_entry($this->objCx, $objResult);
            while ($arrResult !== false) {

                $arrTemp = array();
                $arrTemp["username"] = $this->getStrAttribute($arrResult, $this->arrConfig["ldap_user_attribute_username"]);
                $arrTemp["mail"] = $this->getStrAttribute($arrResult, $this->arrConfig["ldap_user_attribute_mail"]);
                if ($arrTemp["mail"] == "") {
                    $arrTemp["mail"] = $this->getStrAttribute($arrResult, $this->arrConfig["ldap_user_attribute_mail_fallback"]);
                }
                $arrTemp["familyname"] = $this->getStrAttribute($arrResult, $this->arrConfig["ldap_user_attribute_familyname"]);
                $arrTemp["givenname"] = $this->getStrAttribute($arrResult, $this->arrConfig["ldap_user_attribute_givenname"]);
                $arrTemp["identifier"] = $this->getStrAttribute($arrResult, $this->arrConfig["ldap_common_identifier"]);

                $arrReturn[] = $arrTemp;

                $arrResult = ldap_next_entry($this->objCx, $arrResult);
            }
        }
        else {
            throw new class_exception("loading of user failed: ".ldap_errno($this->objCx)." # ".ldap_error($this->objCx), class_exception::$level_FATALERROR);
        }

        return $arrReturn;
    }

    /**
     * Loads a single attribute from a given resultset
     *
     * @param Resource $arrResult
     * @param string $strKey
     *
     * @return string
     */
    private function getStrAttribute($arrResult, $strKey)
    {
        $strReturn = "";

        $arrValues = @ldap_get_values($this->objCx, $arrResult, $strKey);
        if ($arrValues["count"] > 0) {
            $strReturn = utf8_encode($arrValues[0]);
        }

        return $strReturn;
    }

    public function getIntCfgNr()
    {
        return $this->arrConfig["nr"];
    }

    public function getStrCfgName()
    {
        return $this->arrConfig["ldap_alias"];
    }
}
