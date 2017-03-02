<?php
/*"******************************************************************************************************
*   (c) 2004-2006 by MulchProductions, www.mulchprod.de                                                 *
*   (c) 2007-2016 by Kajona, www.kajona.de                                                              *
*       Published under the GNU LGPL v2.1, see /system/licence_lgpl.txt                                 *
*-------------------------------------------------------------------------------------------------------*
*	$Id$	                                        *
********************************************************************************************************/

declare(strict_types=1);

namespace Kajona\System\System;


/**
 * Class to handle all the right-stuff concerning system-records
 *
 * @package module_system
 * @author sidler@mulchprod.de
 *
 */
class Rights
{

    public static $STR_RIGHT_INHERIT = "inherit";
    public static $STR_RIGHT_VIEW = "view";
    public static $STR_RIGHT_EDIT = "edit";
    public static $STR_RIGHT_DELETE = "delete";
    public static $STR_RIGHT_RIGHT = "right";
    public static $STR_RIGHT_RIGHT1 = "right1";
    public static $STR_RIGHT_RIGHT2 = "right2";
    public static $STR_RIGHT_RIGHT3 = "right3";
    public static $STR_RIGHT_RIGHT4 = "right4";
    public static $STR_RIGHT_RIGHT5 = "right5";
    public static $STR_RIGHT_CHANGELOG = "changelog";


    /**
     * @var Database
     */
    private $objDb = null;

    /**
     * Session instance
     *
     * @var Session
     */
    private $objSession = null; //Session Object

    private static $objRights = null;

    private $bitTestMode = false;

    private static $arrPermissionMap = array();

    /**
     * Constructor doing the usual setup things
     */
    private function __construct()
    {
        $this->objDb = Carrier::getInstance()->getObjDB();
        $this->objSession = Carrier::getInstance()->getObjSession();
    }

    /**
     * Returns one Instance of the Rights-Object, using a singleton pattern
     *
     * @return Rights
     */
    public static function getInstance()
    {
        if (self::$objRights == null) {
            self::$objRights = new Rights();
        }

        return self:: $objRights;
    }


    /**
     * Helper, shouldn't be called in regular cases.
     * Rebuilds the complete rights-structure, so saves the rights downwards.
     *
     * @param string $strStartId
     *
     * @return bool
     */
    public function rebuildRightsStructure(string $strStartId = "0"): bool
    {
        $this->flushRightsCache();
        //load rights from root-node
        $arrRootRights = $this->getPlainRightRow($strStartId);
        return $this->setRights($arrRootRights, $strStartId);
    }


    /**
     * Writes a single rights record to the database.
     *
     * @param string $strSystemid
     * @param array $arrRights
     *
     * @return bool
     */
    private function writeSingleRecord(string $strSystemid, array $arrRights, string $strRecordClass): bool
    {

        $arrOldPermissions = $this->getPlainRightRow($strSystemid);

        //Splitting up the rights
        $arrParams = array();
        $arrParams[] = (int)$arrRights[self::$STR_RIGHT_INHERIT];
        $arrParams[] = ",". trim($arrRights[self::$STR_RIGHT_VIEW], ",").",";
        $arrParams[] = ",". trim($arrRights[self::$STR_RIGHT_EDIT], ",").",";
        $arrParams[] = ",". trim($arrRights[self::$STR_RIGHT_DELETE], ",").",";
        $arrParams[] = ",". trim($arrRights[self::$STR_RIGHT_RIGHT], ",").",";
        $arrParams[] = ",". trim($arrRights[self::$STR_RIGHT_RIGHT1], ",").",";
        $arrParams[] = ",". trim($arrRights[self::$STR_RIGHT_RIGHT2], ",").",";
        $arrParams[] = ",". trim($arrRights[self::$STR_RIGHT_RIGHT3], ",").",";
        $arrParams[] = ",". trim($arrRights[self::$STR_RIGHT_RIGHT4], ",").",";
        $arrParams[] = ",". trim($arrRights[self::$STR_RIGHT_RIGHT5], ",").",";
        $arrParams[] = ",". trim($arrRights[self::$STR_RIGHT_CHANGELOG], ",").",";
        $arrParams[] = $strSystemid;

        $strQuery = "UPDATE "._dbprefix_."system
            SET right_inherit=?, right_view=?, right_edit=?, right_delete=?, right_right=?, right_right1=?, right_right2=?, right_right3=?, right_right4=?, right_right5=?, right_changelog=? WHERE system_id=?";


        if ($this->objDb->_pQuery($strQuery, $arrParams)) {
            //Flush the cache so later lookups will match the new rights
            $this->objDb->flushQueryCache();
            $this->flushRightsCache();

            CoreEventdispatcher::getInstance()->notifyGenericListeners(SystemEventidentifier::EVENT_SYSTEM_PERMISSIONSCHANGED, array($strSystemid, $arrRights, $arrOldPermissions, $strRecordClass));

            return true;
        } else {
            return false;
        }
    }

    /**
     * Writes rights to the database.
     * Wrapper to the recursive function Rights::setRightsRecursive($arrRights, $strSystemid)
     * Make sure to pass short-ids only! And please think about using the official api "addGroupToRight"
     *
     * @param mixed $arrRights
     * @param string $strSystemid
     *
     * @see setRightsRecursive($arrRights, $strSystemid)
     * @internal
     * @throws Exception
     * @return bool
     */
    public function setRights(array $arrRights, string $strSystemid): bool
    {
        //start a new tx
        $this->flushRightsCache();
        $this->objDb->transactionBegin();

        $objInstance = Objectfactory::getInstance()->getObject($strSystemid);
        if ($objInstance !== null && $objInstance instanceof VersionableInterface) {
            $arrCurrPermissions = $this->getPlainRightRow($strSystemid);
            //create a changehistory entry
            $objLog = new SystemChangelog();
            $arrChanges = array(
                array("property" => "rightInherit", "oldvalue" => $arrCurrPermissions[self::$STR_RIGHT_INHERIT], "newvalue" => $arrRights[self::$STR_RIGHT_INHERIT]),
                array("property" => "rightView", "oldvalue" => $this->convertStringToSystemIdString($arrCurrPermissions[self::$STR_RIGHT_VIEW]), "newvalue" => $this->convertStringToSystemIdString($arrRights[self::$STR_RIGHT_VIEW])),
                array("property" => "rightEdit", "oldvalue" => $this->convertStringToSystemIdString($arrCurrPermissions[self::$STR_RIGHT_EDIT]), "newvalue" => $this->convertStringToSystemIdString($arrRights[self::$STR_RIGHT_EDIT])),
                array("property" => "rightDelete", "oldvalue" => $this->convertStringToSystemIdString($arrCurrPermissions[self::$STR_RIGHT_DELETE]), "newvalue" => $this->convertStringToSystemIdString($arrRights[self::$STR_RIGHT_DELETE])),
                array("property" => "rightRight", "oldvalue" => $this->convertStringToSystemIdString($arrCurrPermissions[self::$STR_RIGHT_RIGHT]), "newvalue" => $this->convertStringToSystemIdString($arrRights[self::$STR_RIGHT_RIGHT])),
                array("property" => "rightRight1", "oldvalue" => $this->convertStringToSystemIdString($arrCurrPermissions[self::$STR_RIGHT_RIGHT1]), "newvalue" => $this->convertStringToSystemIdString($arrRights[self::$STR_RIGHT_RIGHT1])),
                array("property" => "rightRight2", "oldvalue" => $this->convertStringToSystemIdString($arrCurrPermissions[self::$STR_RIGHT_RIGHT2]), "newvalue" => $this->convertStringToSystemIdString($arrRights[self::$STR_RIGHT_RIGHT2])),
                array("property" => "rightRight3", "oldvalue" => $this->convertStringToSystemIdString($arrCurrPermissions[self::$STR_RIGHT_RIGHT3]), "newvalue" => $this->convertStringToSystemIdString($arrRights[self::$STR_RIGHT_RIGHT3])),
                array("property" => "rightRight4", "oldvalue" => $this->convertStringToSystemIdString($arrCurrPermissions[self::$STR_RIGHT_RIGHT4]), "newvalue" => $this->convertStringToSystemIdString($arrRights[self::$STR_RIGHT_RIGHT4])),
                array("property" => "rightRight5", "oldvalue" => $this->convertStringToSystemIdString($arrCurrPermissions[self::$STR_RIGHT_RIGHT5]), "newvalue" => $this->convertStringToSystemIdString($arrRights[self::$STR_RIGHT_RIGHT5])),
                array("property" => "rightChangelog", "oldvalue" => $this->convertStringToSystemIdString($arrCurrPermissions[self::$STR_RIGHT_CHANGELOG]), "newvalue" => $this->convertStringToSystemIdString($arrRights[self::$STR_RIGHT_CHANGELOG]))
            );
            $objLog->processChanges($objInstance, "editPermissions", $arrChanges);
        }

        $bitSave = $this->setRightsRecursive($arrRights, $strSystemid, $objInstance->getStrRecordClass());

        if ($bitSave) {
            $this->objDb->transactionCommit();
            Logger::getInstance()->addLogRow("saving rights of record ".$strSystemid." succeeded", Logger::$levelInfo);
        } else {
            $this->objDb->transactionRollback();
            Logger::getInstance()->addLogRow("saving rights of record ".$strSystemid." failed", Logger::$levelError);
            throw new Exception("saving rights of record ".$strSystemid." failed", Exception::$level_ERROR);
        }
        
        return $bitSave;
    }

    /**
     * Internal helper to convert a id based string to a systemid based on
     * @param $strEntry
     * @return string
     */
    private function convertStringToSystemIdString($strEntry): string
    {
        if (empty($strEntry)) {
            return "";
        }
        $arrReturn = array();
        foreach (explode(",", trim($strEntry, ",")) as $strOneEntry) {
            if (is_numeric($strOneEntry)) {
                $arrReturn[] = UserGroup::getGroupIdForShortId((int)$strOneEntry);
            } elseif (validateSystemid($strOneEntry)) {
                $arrReturn = $strOneEntry;
            }
        }

        return implode(",", $arrReturn);
    }

    /**
     * Converts a systemid based permissions set to a short id based one
     *
     * @param array $arrPermissions
     * @return array
     */
    public function convertSystemidArrayToShortIdString(array $arrPermissions): array
    {
        foreach ($arrPermissions as $strPermission => $arrGroups) {
            if ($strPermission == self::$STR_RIGHT_INHERIT) {
                continue;
            }

            $arrConverted = array();
            foreach ($arrGroups as $strOneSystemid) {
                if (empty($strOneSystemid)) {
                    continue;
                }
                $arrConverted[] = UserGroup::getShortIdForGroupId($strOneSystemid);
            }

            $arrPermissions[$strPermission] = implode(",", $arrConverted);
        }

        return $arrPermissions;
    }

    /**
     * Set the rights of the passed systemrecord.
     * Writes the rights down to all records inheriting from the current one.
     *
     * @param array $arrRights
     * @param string $strSystemid
     *
     * @return bool
     */
    private function setRightsRecursive(array $arrRights, string $strSystemid, string $strRecordClass): bool
    {
        $bitReturn = true;
        $this->flushRightsCache();

        //check against root-record: here no inheritance
        if ($strSystemid == "" || $strSystemid == "0") {
            $arrRights[self::$STR_RIGHT_INHERIT] = 0;
        }

        //plain row
        $arrCurrentRow = $this->getPlainRightRow($strSystemid);
        $strPrevSystemid = $arrCurrentRow["system_prev_id"];


        //separate the two possible modes: inheritance or no inheritance
        //if set to inheritance, set the flag, load the rights from one level above and write the rights down.
        if (isset($arrRights[self::$STR_RIGHT_INHERIT]) && $arrRights[self::$STR_RIGHT_INHERIT] == 1) {
            $arrRights = $this->getPlainRightRow($strPrevSystemid);
            $arrRights[self::$STR_RIGHT_INHERIT] = 1;
        }

        $bitReturn = $bitReturn && $this->writeSingleRecord($strSystemid, $arrRights, $strRecordClass);

        //load all child records in order to update them, too.
        $arrChilds = $this->getChildNodes($strSystemid);
        foreach ($arrChilds as $strOneChildId) {
            //this check is needed for strange tree-behaviours!!! DO NOT REMOVE!
            if ($strOneChildId != $strSystemid) {
                $arrChildRights = $this->getPlainRightRow($strOneChildId);

                if ($arrChildRights[self::$STR_RIGHT_INHERIT] == 1) {
                    $arrChildRights = $arrRights;
                    $arrChildRights[self::$STR_RIGHT_INHERIT] = 1;
                    $bitReturn = $bitReturn && $this->setRightsRecursive($arrChildRights, $strOneChildId, $strRecordClass);
                }
            }
        }

        return $bitReturn;
    }

    /**
     * Looks up, whether a record intherits its' rights or not.
     * If not, false is being returned, if the record inherits the rights from another
     * record, true is returned instead.
     *
     * @param string $strSystemid
     *
     * @return bool
     */
    public function isInherited(string $strSystemid): bool
    {
        $arrRights = $this->getPlainRightRow($strSystemid);
        return $arrRights[self::$STR_RIGHT_INHERIT] == 1;
    }

    /**
     * Sets the inheritance-status for a single record
     *
     * @param bool $bitIsInherited
     * @param string $strSystemid
     *
     * @return bool
     */
    public function setInherited(bool $bitIsInherited, string $strSystemid): bool
    {
        $arrRights = $this->getPlainRightRow($strSystemid);
        $arrRights[self::$STR_RIGHT_INHERIT] = ($bitIsInherited ? 1 : 0);
        return $this->setRights($arrRights, $strSystemid);
    }

    /**
     * Fetches the records placed as child nodes of the current / passed id.
     *
     * @param string $strSystemid
     *
     * @return string[]
     */
    private function getChildNodes(string $strSystemid): array
    {

        $strQuery = "SELECT system_id
                     FROM "._dbprefix_."system
                     WHERE system_prev_id=?
                       AND system_id != '0'
                     ORDER BY system_sort ASC";

        $arrReturn = array();
        $arrTemp = $this->objDb->getPArray($strQuery, array($strSystemid));

        foreach ($arrTemp as $arrOneRow) {
            $arrReturn[] = $arrOneRow["system_id"];
        }

        return $arrReturn;
    }


    /**
     * Looks up the rights for a given SystemID and going up the tree if needed (inheritance!)
     * The array contains short-ids, no systemids!
     *
     * @param string $strSystemid
     *
     * @return array
     */
    private function getPlainRightRow(string $strSystemid): array
    {

        if (OrmRowcache::getCachedInitRow($strSystemid) != null) {
            $arrRow = OrmRowcache::getCachedInitRow($strSystemid);
        } else {
            $strQuery = "SELECT *
                            FROM "._dbprefix_."system
                            WHERE system_id = ?";

            $arrRow = $this->objDb->getPRow($strQuery, array($strSystemid));
        }

        $arrRights = array();
        if (isset($arrRow["system_id"])) {
            $arrRights[self::$STR_RIGHT_VIEW] = $arrRow["right_view"];
            $arrRights[self::$STR_RIGHT_EDIT] = $arrRow["right_edit"];
            $arrRights[self::$STR_RIGHT_DELETE] = $arrRow["right_delete"];
            $arrRights[self::$STR_RIGHT_RIGHT] = $arrRow["right_right"];
            $arrRights[self::$STR_RIGHT_RIGHT1] = $arrRow["right_right1"];
            $arrRights[self::$STR_RIGHT_RIGHT2] = $arrRow["right_right2"];
            $arrRights[self::$STR_RIGHT_RIGHT3] = $arrRow["right_right3"];
            $arrRights[self::$STR_RIGHT_RIGHT4] = $arrRow["right_right4"];
            $arrRights[self::$STR_RIGHT_RIGHT5] = $arrRow["right_right5"];
            $arrRights[self::$STR_RIGHT_CHANGELOG] = isset($arrRow["right_changelog"]) ? $arrRow["right_changelog"] : "";
            $arrRights[self::$STR_RIGHT_INHERIT] = (int)$arrRow["right_inherit"];
            $arrRights["system_prev_id"] = $arrRow["system_prev_id"];
            $arrRights["system_id"] = $arrRow["system_id"];
        } else {
            $arrRights[self::$STR_RIGHT_VIEW] = "";
            $arrRights[self::$STR_RIGHT_EDIT] = "";
            $arrRights[self::$STR_RIGHT_DELETE] = "";
            $arrRights[self::$STR_RIGHT_RIGHT] = "";
            $arrRights[self::$STR_RIGHT_RIGHT1] = "";
            $arrRights[self::$STR_RIGHT_RIGHT2] = "";
            $arrRights[self::$STR_RIGHT_RIGHT3] = "";
            $arrRights[self::$STR_RIGHT_RIGHT4] = "";
            $arrRights[self::$STR_RIGHT_RIGHT5] = "";
            $arrRights[self::$STR_RIGHT_CHANGELOG] = "";
            $arrRights[self::$STR_RIGHT_INHERIT] = 1;
            $arrRights["system_prev_id"] = "";
            $arrRights["system_id"] = "";
        }


        return $arrRights;
    }


    /**
     * Returns a 2-dimensional Array containing the short group ids and the assigned rights.
     *
     * @param string $strSystemid
     *
     * @param string $strPermissionFilter may be used to return only the set for a given permission, this reduces the number of explodes
     *
     * @return mixed
     */
    private function getArrayRightsShortIds(string $strSystemid, string $strPermissionFilter = ""): array
    {
        $arrReturn = array();

        $arrRow = $this->getPlainRightRow($strSystemid);

        if ($strPermissionFilter != "") {
            return array($strPermissionFilter => explode(",", $arrRow[$strPermissionFilter]));
        }

        //Exploding the array
        $arrReturn[self::$STR_RIGHT_VIEW]       = explode(",", $arrRow[self::$STR_RIGHT_VIEW]);
        $arrReturn[self::$STR_RIGHT_EDIT]       = explode(",", $arrRow[self::$STR_RIGHT_EDIT]);
        $arrReturn[self::$STR_RIGHT_DELETE]     = explode(",", $arrRow[self::$STR_RIGHT_DELETE]);
        $arrReturn[self::$STR_RIGHT_RIGHT]      = explode(",", $arrRow[self::$STR_RIGHT_RIGHT]);
        $arrReturn[self::$STR_RIGHT_RIGHT1]     = explode(",", $arrRow[self::$STR_RIGHT_RIGHT1]);
        $arrReturn[self::$STR_RIGHT_RIGHT2]     = explode(",", $arrRow[self::$STR_RIGHT_RIGHT2]);
        $arrReturn[self::$STR_RIGHT_RIGHT3]     = explode(",", $arrRow[self::$STR_RIGHT_RIGHT3]);
        $arrReturn[self::$STR_RIGHT_RIGHT4]     = explode(",", $arrRow[self::$STR_RIGHT_RIGHT4]);
        $arrReturn[self::$STR_RIGHT_RIGHT5]     = explode(",", $arrRow[self::$STR_RIGHT_RIGHT5]);
        $arrReturn[self::$STR_RIGHT_CHANGELOG]  = explode(",", $arrRow[self::$STR_RIGHT_CHANGELOG]);

        $arrReturn[self::$STR_RIGHT_INHERIT] = (int)$arrRow[self::$STR_RIGHT_INHERIT];

        return $arrReturn;
    }


    /**
     * Returns a 2-dimensional Array containing the group ids and the assigned rights.
     *
     * @param string $strSystemid
     *
     * @param string $strPermissionFilter may be used to return only the set for a given permission, this reduces the number of explodes
     *
     * @return mixed
     */
    public function getArrayRights(string $strSystemid, string $strPermissionFilter = ""): array
    {
        $arrReturn = $this->getArrayRightsShortIds($strSystemid, $strPermissionFilter);

        //convert short to long ids
        foreach ($arrReturn as $strPermission => $arrGroups) {
            if ($strPermission == self::$STR_RIGHT_INHERIT) {
                continue;
            }

            $arrConverted = array();
            foreach ($arrGroups as $intOneShortId) {
                if (empty($intOneShortId)) {
                    continue;
                }
                $arrConverted[] = UserGroup::getGroupIdForShortId((int)$intOneShortId);
            }

            $arrReturn[$strPermission] = $arrConverted;
        }

        return $arrReturn;
    }

    /**
     * Checks if the user has the right to view the record
     *
     * @param string $strSystemid
     * @param string $strUserid
     *
     * @return bool
     */
    public function rightView(string $strSystemid, string $strUserid = ""): bool
    {
        return $this->checkPermissionForUserId($strUserid, self::$STR_RIGHT_VIEW, $strSystemid);
    }

    /**
     * Checks if the user has the right to edit the record
     *
     * @param string $strSystemid
     * @param string $strUserid
     *
     * @return bool
     */
    public function rightEdit(string $strSystemid, string $strUserid = ""): bool
    {
        return $this->checkPermissionForUserId($strUserid, self::$STR_RIGHT_EDIT, $strSystemid);
    }


    /**
     * Checks if the user has the right to delete the record
     *
     * @param string $strSystemid
     * @param string $strUserid
     *
     * @return bool
     */
    public function rightDelete(string $strSystemid, string $strUserid = ""): bool
    {
        return $this->checkPermissionForUserId($strUserid, self::$STR_RIGHT_DELETE, $strSystemid);
    }


    /**
     * Checks if the user has the right to edit the rights of the record
     *
     * @param string $strSystemid
     * @param string $strUserid
     *
     * @return bool
     */
    public function rightRight(string $strSystemid, string $strUserid = ""): bool
    {
        return $this->checkPermissionForUserId($strUserid, self::$STR_RIGHT_RIGHT, $strSystemid);
    }


    /**
     * Checks if the user has the right to edit the right1 of the record
     *
     * @param string $strSystemid
     * @param string $strUserid
     *
     * @return bool
     */
    public function rightRight1(string $strSystemid, string $strUserid = ""): bool
    {
        return $this->checkPermissionForUserId($strUserid, self::$STR_RIGHT_RIGHT1, $strSystemid);
    }


    /**
     * Checks if the user has the right to edit the right2 of the record
     *
     * @param string $strSystemid
     * @param string $strUserid
     *
     * @return bool
     */
    public function rightRight2(string $strSystemid, string $strUserid = ""): bool
    {
        return $this->checkPermissionForUserId($strUserid, self::$STR_RIGHT_RIGHT2, $strSystemid);
    }


    /**
     * Checks if the user has the right to edit the right3 of the record
     *
     * @param string $strSystemid
     * @param string $strUserid
     *
     * @return bool
     */
    public function rightRight3(string $strSystemid, string $strUserid = ""): bool
    {
        return $this->checkPermissionForUserId($strUserid, self::$STR_RIGHT_RIGHT3, $strSystemid);
    }

    /**
     * Checks if the user has the right to edit the right4 of the record
     *
     * @param string $strSystemid
     * @param string $strUserid
     *
     * @return bool
     */
    public function rightRight4(string $strSystemid, string $strUserid = ""): bool
    {
        return $this->checkPermissionForUserId($strUserid, self::$STR_RIGHT_RIGHT4, $strSystemid);
    }


    /**
     * Checks if the user has the right to edit the right5 of the record
     *
     * @param string $strSystemid
     * @param string $strUserid
     *
     * @return bool
     */
    public function rightRight5(string $strSystemid, string $strUserid = ""): bool
    {
        return $this->checkPermissionForUserId($strUserid, self::$STR_RIGHT_RIGHT5, $strSystemid);
    }

    /**
     * Checks if the user has the right to edit the right5 of the record
     *
     * @param string $strSystemid
     * @param string $strUserid
     *
     * @return bool
     */
    public function rightChangelog(string $strSystemid, string $strUserid = ""): bool
    {
        return $this->checkPermissionForUserId($strUserid, self::$STR_RIGHT_CHANGELOG, $strSystemid);
    }

    /**
     * Checks if a given user-id is granted the passed permission for the passed systemid.
     *
     * @param string $strUserid
     * @param string $strPermission
     * @param string $strSystemid
     *
     * @return bool
     */
    public function checkPermissionForUserId(string $strUserid, string $strPermission, string $strSystemid): bool
    {
        if ($strSystemid == "") {
            return false;
        }

        if ($this->bitTestMode) {
            return true;
        }

        if (isset(self::$arrPermissionMap[$strSystemid][$strUserid][$strPermission])) {
            return self::$arrPermissionMap[$strSystemid][$strUserid][$strPermission];
        }

        $arrGroupIds = array();

        if (validateSystemid($strUserid)) {
            if ($strUserid == $this->objSession->getUserID()) {
                $arrGroupIds = $this->objSession->getGroupIdsAsArray();
            } else {
                /** @var UserUser $objUser */
                $objUser = Objectfactory::getInstance()->getObject($strUserid);
                $arrGroupIds = $objUser->getArrGroupIds();
            }
        } elseif (validateSystemid($this->objSession->getUserID())) {
            $arrGroupIds = $this->objSession->getGroupIdsAsArray();
        } else {
            $arrGroupIds[] = SystemSetting::getConfigValue("_guests_group_id_");
        }

        foreach ($arrGroupIds as $strOneGroupId) {
            if ($this->checkPermissionForGroup($strOneGroupId, $strPermission, $strSystemid)) {
                self::$arrPermissionMap[$strSystemid][$strUserid][$strPermission] = true;
                return true;
            }
        }

        self::$arrPermissionMap[$strSystemid][$strUserid][$strPermission] = false;
        return false;
    }


    /**
     * Validates, if a single group is granted a permission for a given systemid.
     *
     * @param string $strGroupId
     * @param string $strPermission
     * @param string $strSystemid
     *
     * @return bool
     */
    public function checkPermissionForGroup(string $strGroupId, string $strPermission, string $strSystemid): bool
    {
        if ($strSystemid == "") {
            return false;
        }

        if ($this->bitTestMode) {
            return true;
        }

        //map the groupid on a short id
        $intShortId = UserGroup::getShortIdForGroupId($strGroupId);
        if (empty($intShortId)) {
            return false;
        }

        $arrRights = $this->getArrayRightsShortIds($strSystemid, $strPermission);
        return in_array($intShortId, $arrRights[$strPermission]);
    }

    /**
     * Copies permissions from one record to another record.
     * Please be aware, that permissions are only copied in case the source-record has custom permissions.
     * If the source record inherits permissions, the permissions won't be copied to the target record.
     *
     * @param $strSourceSystemid
     * @param $strTargetSystemid
     *
     * @return bool
     */
    public function copyPermissions(string $strSourceSystemid, string $strTargetSystemid): bool
    {
        $arrSourceRow = $this->getPlainRightRow($strSourceSystemid);
        if ($arrSourceRow[self::$STR_RIGHT_INHERIT] == 0) {
            return $this->setRights($arrSourceRow, $strTargetSystemid);
        }

        return true;
    }


    /**
     * Adds a group for a right at a given systemid
     * <b>NOTE: By setting rights using this method, inheritance is set to false!!!</b>
     *
     * @param string $strGroupId
     * @param string $strSystemid
     * @param string $strRight one of view, edit, delete, right, right1, right2, right3, right4, right5
     *
     * @return bool
     */
    public function addGroupToRight(string $strGroupId, string $strSystemid, string $strRight): bool
    {

        $this->objDb->flushQueryCache();
        $this->flushRightsCache();

        //Load the current rights
        $arrRights = $this->getArrayRightsShortIds($strSystemid);

        //rights not given, add now, disabling inheritance
        $arrRights[self::$STR_RIGHT_INHERIT] = 0;

        //map the groupid on a short id
        $intShortId = UserGroup::getShortIdForGroupId($strGroupId);

        //add the group to the row
        if (!in_array($intShortId, $arrRights[$strRight])) {
            $arrRights[$strRight][] = $intShortId;
        }

        //build a one-dim array
        $arrRights[self::$STR_RIGHT_VIEW]       =implode(",", $arrRights[self::$STR_RIGHT_VIEW]);
        $arrRights[self::$STR_RIGHT_EDIT]       =implode(",", $arrRights[self::$STR_RIGHT_EDIT]);
        $arrRights[self::$STR_RIGHT_DELETE]     =implode(",", $arrRights[self::$STR_RIGHT_DELETE]);
        $arrRights[self::$STR_RIGHT_RIGHT]      =implode(",", $arrRights[self::$STR_RIGHT_RIGHT]);
        $arrRights[self::$STR_RIGHT_RIGHT1]     =implode(",", $arrRights[self::$STR_RIGHT_RIGHT1]);
        $arrRights[self::$STR_RIGHT_RIGHT2]     =implode(",", $arrRights[self::$STR_RIGHT_RIGHT2]);
        $arrRights[self::$STR_RIGHT_RIGHT3]     =implode(",", $arrRights[self::$STR_RIGHT_RIGHT3]);
        $arrRights[self::$STR_RIGHT_RIGHT4]     =implode(",", $arrRights[self::$STR_RIGHT_RIGHT4]);
        $arrRights[self::$STR_RIGHT_RIGHT5]     =implode(",", $arrRights[self::$STR_RIGHT_RIGHT5]);
        $arrRights[self::$STR_RIGHT_CHANGELOG]  =implode(",", $arrRights[self::$STR_RIGHT_CHANGELOG]);


        //and save the row
        $bitReturn = $this->setRights($arrRights, $strSystemid);

        return $bitReturn;
    }

    /**
     * Removes a group from a right at a given systemid
     * <b>NOTE: By setting rights using this method, inheritance is set to false!!!</b>
     *
     * @param string $strGroupId
     * @param string $strSystemid
     * @param string $strRight one of view, edit, delete, right, right1, right2, right3, right4, right5
     *
     * @return bool
     */
    public function removeGroupFromRight(string $strGroupId, string $strSystemid, string $strRight): bool
    {

        Carrier::getInstance()->flushCache(Carrier::INT_CACHE_TYPE_DBQUERIES | Carrier::INT_CACHE_TYPE_ORMCACHE);

        //Load the current rights
        $arrRights = $this->getArrayRightsShortIds($strSystemid);

        //rights not given, add now, disabling inheritance
        $arrRights[self::$STR_RIGHT_INHERIT] = 0;

        //map the groupid on a short id
        $intShortId = UserGroup::getShortIdForGroupId($strGroupId);

        //remove the group
        if (in_array($intShortId, $arrRights[$strRight])) {
            foreach ($arrRights[$strRight] as $intKey => $strSingleGroup) {
                if ($strSingleGroup == $intShortId) {
                    unset($arrRights[$strRight][$intKey]);
                }
            }
        }

        //build a one-dim array
        $arrRights[self::$STR_RIGHT_VIEW]       =implode(",", $arrRights[self::$STR_RIGHT_VIEW]);
        $arrRights[self::$STR_RIGHT_EDIT]       =implode(",", $arrRights[self::$STR_RIGHT_EDIT]);
        $arrRights[self::$STR_RIGHT_DELETE]     =implode(",", $arrRights[self::$STR_RIGHT_DELETE]);
        $arrRights[self::$STR_RIGHT_RIGHT]      =implode(",", $arrRights[self::$STR_RIGHT_RIGHT]);
        $arrRights[self::$STR_RIGHT_RIGHT1]     =implode(",", $arrRights[self::$STR_RIGHT_RIGHT1]);
        $arrRights[self::$STR_RIGHT_RIGHT2]     =implode(",", $arrRights[self::$STR_RIGHT_RIGHT2]);
        $arrRights[self::$STR_RIGHT_RIGHT3]     =implode(",", $arrRights[self::$STR_RIGHT_RIGHT3]);
        $arrRights[self::$STR_RIGHT_RIGHT4]     =implode(",", $arrRights[self::$STR_RIGHT_RIGHT4]);
        $arrRights[self::$STR_RIGHT_RIGHT5]     =implode(",", $arrRights[self::$STR_RIGHT_RIGHT5]);
        $arrRights[self::$STR_RIGHT_CHANGELOG]  =implode(",", $arrRights[self::$STR_RIGHT_CHANGELOG]);

        //and save the row
        $bitReturn = $this->setRights($arrRights, $strSystemid);

        return $bitReturn;
    }

    /**
     * Flushes the internal rights cache
     *
     * @return void
     */
    private function flushRightsCache()
    {
        self::$arrPermissionMap = array();
        Carrier::getInstance()->flushCache(Carrier::INT_CACHE_TYPE_ORMCACHE);
    }

    /**
     * Enables the internal testing mode.
     * Only possible if the current context is triggered out of a phpunit-context
     *
     * @param bool $bitTestMode
     *
     * @return void
     */
    public function setBitTestMode(bool $bitTestMode)
    {
        $this->bitTestMode = $bitTestMode && _autotesting_;
    }


    /**
     * Validates a set of permissions for a single object.
     * The string of permissions is a comma-separated list, whereas the entries may be one of
     * view, edit, delete, right, right1, right2, right3, right4, right5
     * If at least a single permission is given, true is returned, otherwise false.
     *
     * @param string $strPermissions
     * @param Model $objObject
     *
     * @return bool
     * @throws Exception
     * @since 4.0
     */
    public function validatePermissionString(string $strPermissions, Model $objObject): bool
    {

        if (!$objObject instanceof Model) {
            throw new Exception("automated permission-check only for instances of ".Model::class, Exception::$level_ERROR);
        }

        if (trim($strPermissions) == "") {
            return false;
        }

        $arrPermissions = explode(",", $strPermissions);

        foreach ($arrPermissions as $strOnePermissions) {
            $strOnePermissions = trim($strOnePermissions);

            switch (trim($strOnePermissions)) {
                case self::$STR_RIGHT_VIEW:
                    if ($objObject->rightView()) {
                        return true;
                    }
                    break;
                case self::$STR_RIGHT_EDIT:
                    if ($objObject->rightEdit()) {
                        return true;
                    }
                    break;
                case self::$STR_RIGHT_DELETE:
                    if ($objObject->rightDelete()) {
                        return true;
                    }
                    break;
                case self::$STR_RIGHT_RIGHT:
                    if ($objObject->rightRight()) {
                        return true;
                    }
                    break;
                case self::$STR_RIGHT_RIGHT1:
                    if ($objObject->rightRight1()) {
                        return true;
                    }
                    break;
                case self::$STR_RIGHT_RIGHT2:
                    if ($objObject->rightRight2()) {
                        return true;
                    }
                    break;
                case self::$STR_RIGHT_RIGHT3:
                    if ($objObject->rightRight3()) {
                        return true;
                    }
                    break;
                case self::$STR_RIGHT_RIGHT4:
                    if ($objObject->rightRight4()) {
                        return true;
                    }
                    break;
                case self::$STR_RIGHT_RIGHT5:
                    if ($objObject->rightRight5()) {
                        return true;
                    }
                    break;
                case self::$STR_RIGHT_CHANGELOG:
                    if ($objObject->rightChangelog()) {
                        return true;
                    }
                    break;
                default:
                    break;
            }
        }

        return false;
    }

    /**
     * Adds a row to the internal cache.
     * Only to be used in combination with Root::setArrInitRow.
     *
     * @param array $arrRow
     *
     * @deprecated use the orm-rowcache instead to avoid multiple cache locations
     * @return void
     */
    public function addRowToCache(array $arrRow)
    {
    }


    /**
     * Filters the given array of objects by the given permissions.
     *
     * @param array $arrObjects
     * @param string $strPermissions
     *
     * @return array
     */
    public function filterObjectsByRight(array $arrObjects, string $strPermissions): array
    {
        return array_filter($arrObjects, function ($objObject) use ($strPermissions) {
            return Rights::getInstance()->getInstance()->validatePermissionString($strPermissions, $objObject);
        });
    }
}


