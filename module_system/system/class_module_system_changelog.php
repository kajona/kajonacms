<?php
/*"******************************************************************************************************
*   (c) 2007-2015 by Kajona, www.kajona.de                                                              *
*       Published under the GNU LGPL v2.1, see /system/licence_lgpl.txt                                 *
*-------------------------------------------------------------------------------------------------------*
*	$Id$                                    *
********************************************************************************************************/

/**
 * The changelog is a global wrapper to the gui-based logging.
 * Changes should reflect user-changes and not internal system-logs.
 * For logging to the logfile, see class_logger.
 * But: entries added to the changelog are copied to the systemlog leveled as information, too.
 * Changes are stored as a flat list in the database only and have no representation within the
 * system-table. This means there are no common system-id relations.
 * Have a look at the memento pattern by Gamma et al. to get a glance at the conceptional behaviour.
 *
 * @package module_system
 * @author sidler@mulchprod.de
 * @see class_logger
 *
 * @module system
 * @moduleId _system_modul_id_
 */
class class_module_system_changelog {

    const ANNOTATION_PROPERTY_VERSIONABLE = "@versionable";

    /**
     * A flag to enable / disable the changehistory programatically.
     * If not set to null, the value overwrites the global changelog constant.
     *
     * @var bool
     */
    public static $bitChangelogEnabled = null;

    private static $arrOldValueCache = array();

    /**
     * @todo: is the init value cache still required?
     * @var array
     */
    private static $arrInitValueCache = array();
    private static $arrCachedProviders = null;

    private static $arrInsertCache = array();


    public static $STR_ACTION_EDIT = "actionEdit";
    public static $STR_ACTION_DELETE = "actionDelete";


    /**
     * Checks if an objects properties changed.
     * If the second params is passed, the set of changed properties is returned, too.
     *
     * @param interface_versionable $objObject
     * @param array &$arrReducedSet
     * @param bool $bitUseInitValues if set to true, the initial values of the object will be used for comparison, not the ones of the last update
     *
     * @throws class_exception
     * @return array
     */
    public function isObjectChanged(interface_versionable $objObject, &$arrReducedSet = array(), $bitUseInitValues = false) {
        if(!$this->isVersioningAvailable($objObject))
            throw new class_exception("versioning not available", class_exception::$level_ERROR);

        //read the new values
        $arrChangeset = $this->createChangeArray($objObject, $bitUseInitValues);

        $this->createReducedChangeSet($arrReducedSet, $arrChangeset, "");
        return count($arrReducedSet) > 0;
    }


    /**
     * Reads all properties marked with the annotation @versionable.
     * The state is cached in a static array mapped to the objects systemid.
     * In consequence, this means that only objects with a valid systemid are scanned for properties under versioning.
     *
     * @param interface_versionable|class_model $objCurrentObject
     *
     * @return array|null
     */
    public function readOldValues(interface_versionable $objCurrentObject) {
        if(!$this->isVersioningAvailable($objCurrentObject))
            return null;

        if(validateSystemid($objCurrentObject->getSystemid())) {
            $arrOldValues = $this->readVersionableProperties($objCurrentObject);
            $this->setOldValuesForSystemid($objCurrentObject->getSystemid(), $arrOldValues);
            return $arrOldValues;
        }
        return null;
    }

    /**
     * Sets the passed entry for a concrete objects' property to the set of old values
     *
     * @param string $strSystemid
     * @param $strProperty
     * @param $strValue
     */
    public function setOldValueForSystemidAndProperty($strSystemid, $strProperty, $strValue) {
        if(!isset(self::$arrOldValueCache[$strSystemid]))
            self::$arrOldValueCache[$strSystemid] = array();

        self::$arrOldValueCache[$strSystemid][$strProperty] = $strValue;
    }

    /**
     * Scans the passed object and tries to find all properties marked with the annotation @versionable.
     * @param interface_versionable|class_model $objCurrentObject
     *
     * @return array|null
     */
    private function readVersionableProperties(interface_versionable $objCurrentObject) {
        if(!$this->isVersioningAvailable($objCurrentObject))
            return null;

        if(validateSystemid($objCurrentObject->getSystemid())) {
            $arrCurrentValues = array();

            $objReflection = new class_reflection($objCurrentObject);
            $arrProperties = $objReflection->getPropertiesWithAnnotation(self::ANNOTATION_PROPERTY_VERSIONABLE);


            foreach($arrProperties as $strProperty => $strAnnotation) {

                $strValue = "";

                //all prerequisites match, start creating query
                $strGetter = $objReflection->getGetter($strProperty);
                if($strGetter !== null) {
                    $strValue = call_user_func(array($objCurrentObject, $strGetter));
                }

                if(is_array($strValue) || $strValue instanceof ArrayAccess) {
                    $arrNewValues = array();
                    foreach($strValue as $objOneValue) {
                        if(is_object($objOneValue)&& $objOneValue instanceof class_root) {
                            $arrNewValues[] = $objOneValue->getSystemid();
                        }
                        else {
                            $arrNewValues[] = $objOneValue."";
                        }
                    }
                    sort($arrNewValues);
                    $strValue = implode(",", $arrNewValues);
                }

                $arrCurrentValues[$strProperty] = $strValue;
            }

            return $arrCurrentValues;
        }
        return null;
    }

    /**
     * @param string $strSystemid
     *
     * @return null
     */
    public function getOldValuesForSystemid($strSystemid) {
        if(isset(self::$arrOldValueCache[$strSystemid]))
            return self::$arrOldValueCache[$strSystemid];
        else
            return null;
    }

    /**
     * @param string $strSystemid
     *
     * @return null
     */
    public function getInitValuesForSystemid($strSystemid) {
        if(isset(self::$arrInitValueCache[$strSystemid]))
            return self::$arrInitValueCache[$strSystemid];
        else
            return null;
    }

    /**
     * Sets the passed entry to the set of old values
     *
     * @param string $strSystemid
     * @param array $arrOldValues
     *
     * @return void
     */
    private function setOldValuesForSystemid($strSystemid, $arrOldValues) {
        self::$arrOldValueCache[$strSystemid] = $arrOldValues;
        if(!array_key_exists($strSystemid, self::$arrInitValueCache))
            self::$arrInitValueCache[$strSystemid] = $arrOldValues;
    }

    /**
     * Builds the change-array based on the old- and new values
     *
     * @param interface_versionable|class_root $objSourceModel
     * @param bool $bitUseInitValues
     *
     * @return array
     */
    private function createChangeArray($objSourceModel, $bitUseInitValues = false) {

        $arrOldValues = $this->getOldValuesForSystemid($objSourceModel->getSystemid());
        if($bitUseInitValues)
            $arrOldValues = $this->getInitValuesForSystemid($objSourceModel->getSystemid());

        //this are now the new ones
        $arrNewValues = $this->readVersionableProperties($objSourceModel);

        if($arrOldValues == null)
            $arrOldValues = array();

        if($arrNewValues == null)
            $arrNewValues = array();

        $arrReturn = array();
        foreach($arrNewValues as $strPropertyName => $objValue) {
            $arrReturn[] = array(
                "property" => $strPropertyName,
                "oldvalue" => isset($arrOldValues[$strPropertyName]) ? $arrOldValues[$strPropertyName] : "",
                "newvalue" => isset($arrNewValues[$strPropertyName]) ? $arrNewValues[$strPropertyName] : ""
            );
        }

        return $arrReturn;
    }

    /**
     * May be used to add changes to the change-track manually. In most cases, createLogEntry should be sufficient since
     * it takes care of everything automatically.
     * When using this method, pass an array of entries like:
     * array(
     *   array("property" => "", "oldvalue" => "", "newvalue" => ""),
     *   array("property" => "", "oldvalue" => "", "newvalue" => "")
     * )
     *
     * @param interface_versionable $objSourceModel
     * @param string $strAction
     * @param array $arrEntries
     * @param bool $bitForceEntry if set to true, an entry will be created even if the values didn't change
     *
     * @throws class_exception
     * @return bool
     */
    public function processChanges(interface_versionable $objSourceModel, $strAction, $arrEntries, $bitForceEntry = false) {
        if(!$this->isVersioningAvailable($objSourceModel))
            return true;

        return $this->processChangeArray($arrEntries, $objSourceModel, $strAction, $bitForceEntry);
    }

    /**
     * Generates a new entry in the modification log storing all relevant information.
     * Creates an entry in the systemlog leveled as information, too.
     * By default entries with same old- and new-values are dropped.
     * The passed object has to implement interface_versionable.
     * If $bitDeleteAction isset to true, the change will behave in a way like deleting a record. This means the new-value will be empty on save.
     * If not set manually, the system will try to detect if it's a delete operation based on the current action.
     *
     * @param interface_versionable $objSourceModel
     * @param string $strAction
     * @param bool $bitForceEntry if set to true, an entry will be created even if the values didn't change
     * @param bool $bitDeleteAction if set to true, the change will behave in a way like deleting a record. This means the new-value will be empty on save.
     *             If not set manually, the system will try to detect if it's a delete operation based on the current action.
     *
     * @throws class_exception
     * @return bool
     */
    public function createLogEntry(interface_versionable $objSourceModel, $strAction, $bitForceEntry = false, $bitDeleteAction = null) {
        if(!$this->isVersioningAvailable($objSourceModel))
            return true;


        $arrChanges = $this->createChangeArray($objSourceModel);
        $bitReturn = $this->processChangeArray($arrChanges, $objSourceModel, $strAction, $bitForceEntry, $bitDeleteAction);
        $this->readOldValues($objSourceModel);
        return $bitReturn;
    }

    /**
     * Checks if version is enabled in general and for the passed object
     *
     * @param interface_versionable $objSourceModel
     *
     * @return bool
     * @throws class_exception
     */
    private function isVersioningAvailable(interface_versionable $objSourceModel) {

        if(self::$bitChangelogEnabled !== null)
            return self::$bitChangelogEnabled;

//        if(class_module_system_setting::getConfigValue("_system_changehistory_enabled_") != "true") {
//            self::$bitChangelogEnabled = false;
//            return false;
//        }

        if(!$objSourceModel instanceof interface_versionable) {
            throw new class_exception("object passed to create changelog not implementing interface_versionable", class_logger::$levelWarning);
        }


        //changes require at least kajona 3.4.9
        $arrModul = class_module_system_module::getPlainModuleData("system");
        if(version_compare($arrModul["module_version"], "3.4.9") < 0) {
            self::$bitChangelogEnabled = false;
            return false;
        }

        self::$bitChangelogEnabled = true;
        return true;
    }

    /**
     * Processes the internal change-array and creates all related records.
     *
     * @param array $arrChanges
     * @param interface_versionable|class_model $objSourceModel
     * @param string $strAction
     * @param bool $bitForceEntry
     * @param bool $bitDeleteAction
     *
     * @return bool
     */
    private function processChangeArray(array $arrChanges, interface_versionable $objSourceModel, $strAction, $bitForceEntry = false, $bitDeleteAction = null) {
        $bitReturn = true;

        if(is_array($arrChanges)) {

            $arrReducedChanges = array();
            $this->createReducedChangeSet($arrReducedChanges, $arrChanges, $strAction, $bitForceEntry, $bitDeleteAction);

            //collect all values in order to create a batch query
            foreach($arrReducedChanges as $arrChangeSet) {
                $strOldvalue = $arrChangeSet["oldvalue"];
                $strNewvalue = $arrChangeSet["newvalue"];
                $strProperty = $arrChangeSet["property"];


                class_logger::getInstance()->addLogRow(
                    "change in class ".get_class($objSourceModel)."@".$strAction." systemid: ".$objSourceModel->getSystemid()." property: ".$strProperty." old value: "
                    .uniStrTrim($strOldvalue, 60)." new value: ".uniStrTrim($strNewvalue, 60),
                    class_logger::$levelInfo
                );

                $arrValues = array(
                    generateSystemid(),
                    class_date::getCurrentTimestamp(),
                    $objSourceModel->getSystemid(),
                    $objSourceModel->getPrevid(),
                    class_carrier::getInstance()->getObjSession()->getUserID(),
                    get_class($objSourceModel),
                    $strAction,
                    $strProperty,
                    $strOldvalue,
                    $strNewvalue
                );

                self::$arrInsertCache[self::getTableForClass(get_class($objSourceModel))][] = $arrValues;
            }


        }
        return $bitReturn;
    }

    /**
     * Helper to process outstanding changelog entries.
     * Use class_carrier::getInstance()->flushCache(class_carrier::INT_CACHE_TYPE_CHANGELOG) in order to trigger this method.
     * @return bool
     * @see class_carrier::getInstance()->flushCache(class_carrier::INT_CACHE_TYPE_CHANGELOG)
     */
    public function processCachedInserts() {
        $bitReturn = true;
        foreach(self::$arrInsertCache as $strTable => $arrRows) {
            if(count($arrRows) > 0) {
                $bitReturn = class_carrier::getInstance()->getObjDB()->multiInsert(
                    $strTable,
                    array("change_id", "change_date", "change_systemid", "change_system_previd", "change_user", "change_class", "change_action", "change_property", "change_oldvalue", "change_newvalue"),
                    $arrRows
                ) && $bitReturn;

                self::$arrInsertCache[$strTable] = array();
            }
        }
        return $bitReturn;
    }


    /**
     * Reduces the passed change-array to only the entries which really changed.
     *
     * @param array &$arrReturn
     * @param array $arrChanges
     * @param string $strAction
     * @param bool $bitForceEntry
     * @param null $bitDeleteAction
     * @return void
     */
    private function createReducedChangeSet(array &$arrReturn, array $arrChanges, $strAction, $bitForceEntry = false, $bitDeleteAction = null) {

        foreach($arrChanges as $arrChangeSet) {


            $strOldvalue = "";
            if(isset($arrChangeSet["oldvalue"]))
                $strOldvalue = $arrChangeSet["oldvalue"];

            $strNewvalue = "";
            if(isset($arrChangeSet["newvalue"]))
                $strNewvalue = $arrChangeSet["newvalue"];

            $strProperty = $arrChangeSet["property"];


            //array may be processed automatically, too
            if((is_array($strOldvalue) || $strOldvalue instanceof ArrayAccess) && (is_array($strNewvalue) || $strNewvalue instanceof ArrayAccess)) {

                $arrArrayChanges = array();
                foreach($strNewvalue as $strOneId) {
                    if(!in_array($strOneId, $strOldvalue))
                        $arrArrayChanges[] = array("property" => $strProperty, "oldvalue" => "", "newvalue" => $strOneId);
                }

                foreach($strOldvalue as $strOneId) {
                    if(!in_array($strOneId, $strNewvalue))
                        $arrArrayChanges[] = array("property" => $strProperty, "oldvalue" => $strOneId, "newvalue" => "");
                }

                $this->createReducedChangeSet($arrReturn, $arrArrayChanges,  $strAction, $bitForceEntry, $bitDeleteAction);
                continue;
            }


            if($strOldvalue instanceof class_date)
                $strOldvalue = $strOldvalue->getLongTimestamp();

            if($strNewvalue instanceof class_date)
                $strNewvalue = $strNewvalue->getLongTimestamp();

            if($bitDeleteAction || ($bitDeleteAction === null && $strAction == self::$STR_ACTION_DELETE))
                $strNewvalue = "";

            if(is_numeric($strOldvalue) || is_numeric($strNewvalue)) {
                $strOldvalue .= "";
                $strNewvalue .= "";
            }

            if(!$bitForceEntry && ($strOldvalue === $strNewvalue))
                continue;

            //update the values
            $arrChangeSet["oldvalue"] = $strOldvalue;
            $arrChangeSet["newvalue"] = $strNewvalue;


            //add entry right here
            $arrReturn[] = $arrChangeSet;
        }

    }


    /**
     * Creates the list of logentries, either without a systemid-based filter
     * or limited to the given systemid.
     *
     * @param string $strSystemidFilter
     * @param null|int $intStart
     * @param null|int $intEnd
     *
     * @return class_changelog_container[]
     */
    public static function getLogEntries($strSystemidFilter = "", $intStart = null, $intEnd = null) {

        $arrParams = array();

        if(validateSystemid($strSystemidFilter)) {

            $strQuery = "SELECT change_date, change_systemid, change_user, change_class, change_action, change_property, change_oldvalue, change_newvalue
                           FROM "._dbprefix_.self::getTableForClass(class_objectfactory::getInstance()->getClassNameForId($strSystemidFilter))."
                           WHERE change_systemid = ? ";

            $arrParams[] = $strSystemidFilter;

        }
        else {
            $strQuery = "SELECT change_date, change_systemid, change_user, change_class, change_action, change_property, change_oldvalue, change_newvalue
                           FROM "._dbprefix_."changelog";

            foreach(self::getAdditionalTables() as $strOneTable) {
                $strQuery .= " UNION ALL SELECT change_date, change_systemid, change_user, change_class, change_action, change_property, change_oldvalue, change_newvalue FROM "._dbprefix_.$strOneTable." ";
            }

        }
        $strQuery .= "ORDER BY change_date DESC";

        $arrRows = class_carrier::getInstance()->getObjDB()->getPArray($strQuery, $arrParams, $intStart, $intEnd);

        $arrReturn = array();
        foreach($arrRows as $arrRow)
            $arrReturn[] = new class_changelog_container(
                $arrRow["change_date"],
                $arrRow["change_systemid"],
                $arrRow["change_user"],
                $arrRow["change_class"],
                $arrRow["change_action"],
                $arrRow["change_property"],
                $arrRow["change_oldvalue"],
                $arrRow["change_newvalue"]
            );

        return $arrReturn;
    }

    /**
     * Counts the number of logentries available
     *
     * @param string $strSystemidFilter
     *
     * @return int
     */
    public static function getLogEntriesCount($strSystemidFilter = "") {

        $arrParams = array();

        if(validateSystemid($strSystemidFilter)) {

            $strQuery = "SELECT COUNT(*)
                           FROM "._dbprefix_.self::getTableForClass(class_objectfactory::getInstance()->getClassNameForId($strSystemidFilter))."
                          WHERE change_systemid = ? ";

            $arrParams[] = $strSystemidFilter;

        }
        else {

            $strQuery = "SELECT COUNT(*)
                FROM (SELECT * FROM "._dbprefix_."changelog";

            if($strSystemidFilter != "")
                $arrParams[] = $strSystemidFilter;

            foreach(self::getAdditionalTables() as $strOneTable) {
                $strQuery .= " UNION ALL SELECT * FROM "._dbprefix_.$strOneTable." ";
            }
            $strQuery .= " ) as tem";

        }

        $arrRow = class_carrier::getInstance()->getObjDB()->getPRow($strQuery, $arrParams);
        return $arrRow["COUNT(*)"];
    }


    /**
     * Creates the list of logentries, based on a flexible but specific filter-list
     *
     * @param string $strSystemidFilter
     * @param string $strActionFilter
     * @param string $strPropertyFilter
     * @param string $strOldvalueFilter
     * @param string $strNewvalueFilter
     *
     * @return class_changelog_container[]
     */
    public static function getSpecificEntries($strSystemidFilter = null, $strActionFilter = null, $strPropertyFilter = null, $strOldvalueFilter = null, $strNewvalueFilter = null) {

        $arrWhere = array();
        if($strSystemidFilter !== null)
            $arrWhere[] = " change_systemid = ? ";
        if($strActionFilter !== null)
            $arrWhere[] = " change_action = ? ";
        if($strPropertyFilter !== null)
            $arrWhere[] = " change_property = ? ";
        if($strOldvalueFilter !== null)
            $arrWhere[] = " change_oldvalue = ? ";
        if($strNewvalueFilter !== null)
            $arrWhere[] = " change_newvalue = ? ";

        $strTable = "changelog";
        if($strSystemidFilter != null) {
            $strTable = self::getTableForClass(class_objectfactory::getInstance()->getClassNameForId($strSystemidFilter));
        }

        $strQuery = "SELECT *
                       FROM "._dbprefix_.$strTable."
                      ".(count($arrWhere) > 0 ? " WHERE ".implode("AND", $arrWhere) : "")."
                   ORDER BY change_date DESC";

        $arrParams = array();
        if($strSystemidFilter !== null)
            $arrParams[] = $strSystemidFilter;

        if($strActionFilter !== null)
            $arrParams[] = $strActionFilter;

        if($strPropertyFilter !== null)
            $arrParams[] = $strPropertyFilter;

        if($strOldvalueFilter !== null)
            $arrParams[] = $strOldvalueFilter;

        if($strNewvalueFilter !== null)
            $arrParams[] = $strNewvalueFilter;

        $arrRows = class_carrier::getInstance()->getObjDB()->getPArray($strQuery, $arrParams);

        $arrReturn = array();
        foreach($arrRows as $arrRow)
            $arrReturn[] = new class_changelog_container(
                $arrRow["change_date"],
                $arrRow["change_systemid"],
                $arrRow["change_user"],
                $arrRow["change_class"],
                $arrRow["change_action"],
                $arrRow["change_property"],
                $arrRow["change_oldvalue"],
                $arrRow["change_newvalue"]
            );

        return $arrReturn;
    }

    /**
     * Shifts the entries for a given system-id to a new date.
     * Please be aware of the consequences when shifting change-records!
     *
     * @param string $strSystemid
     * @param class_date $objNewDate
     *
     * @static
     * @return bool
     */
    public static function shiftLogEntries($strSystemid, $objNewDate) {
        $strQuery = "UPDATE "._dbprefix_.self::getTableForClass(class_objectfactory::getInstance()->getClassNameForId($strSystemid))."
                        SET change_date = ?
                      WHERE change_systemid = ? ";
        return class_carrier::getInstance()->getObjDB()->_pQuery($strQuery, array($objNewDate->getLongTimestamp(), $strSystemid));

    }


    /**
     * This method tries to change the value of a property for a given interval.
     * Therefore the records at the start / end date are loaded and adjusted.
     * All changes within the interval will be removed.
     * Example:
     * Time: 0  1   2   3   4   5   6
     * Old:  x      y       y   z   u
     * New:  x  w           w   z   u
     * --> w was injected from 1 to 4, including.
     *
     * @param string $strSystemid
     * @param string $strAction
     * @param string $strProperty
     * @param null|string $strPrevid
     * @param string $strClass
     * @param null|string $strUser
     * @param string $strNewValue
     * @param class_date $objStartDate
     * @param class_date $objEndDate
     *
     * @return void
     */
    public static function changeValueForInterval($strSystemid, $strAction, $strProperty, $strPrevid, $strClass, $strUser, $strNewValue, class_date $objStartDate, class_date $objEndDate) {

        class_logger::getInstance()->addLogRow("changed time-based history-entry: ".$strSystemid."/".$strProperty." to ".$strNewValue." from ".$objStartDate. " until ".$objEndDate, class_logger::$levelWarning);

        $strQuery = "SELECT *
                       FROM "._dbprefix_.self::getTableForClass($strClass)."
                      WHERE change_systemid = ?
                        AND change_property = ?
                        AND change_date <= ?
                   ORDER BY change_date DESC";

        $arrStartRow = class_carrier::getInstance()->getObjDB()->getPRow($strQuery, array($strSystemid, $strProperty, $objStartDate->getLongTimestamp()));

        $strQuery = "SELECT *
                       FROM "._dbprefix_.self::getTableForClass($strClass)."
                      WHERE change_systemid = ?
                        AND change_property = ?
                        AND change_date >= ?
                   ORDER BY change_date ASC";

        $arrEndRow = class_carrier::getInstance()->getObjDB()->getPRow($strQuery, array($strSystemid, $strProperty, $objEndDate->getLongTimestamp()));

        //drop all changes between the start / end date
        $strQuery = "DELETE FROM "._dbprefix_.self::getTableForClass($strClass)."
                           WHERE change_systemid = ?
                             AND change_property = ?
                             AND change_date >= ?
                             AND change_date <= ?";
        class_carrier::getInstance()->getObjDB()->_pQuery($strQuery, array($strSystemid, $strProperty, $objStartDate->getLongTimestamp(), $objEndDate->getLongTimestamp()));


        //adjust the start-row, see if the dates are matching (update vs insert)
        $strQuery = "INSERT INTO "._dbprefix_.self::getTableForClass($strClass)."
                 (change_id,
                  change_date,
                  change_systemid,
                  change_system_previd,
                  change_user,
                  change_class,
                  change_action,
                  change_property,
                  change_oldvalue,
                  change_newvalue) VALUES
                 (?,?,?,?,?,?,?,?,?,?)";

        class_carrier::getInstance()->getObjDB()->_pQuery(
            $strQuery,
            array(
                generateSystemid(),
                $objStartDate->getLongTimestamp(),
                $strSystemid,
                $strPrevid,
                $strUser,
                $strClass,
                $strAction,
                $strProperty,
                (isset($arrStartRow["change_newvalue"]) ? $arrStartRow["change_newvalue"] : ""),
                $strNewValue
            )
        );

        //adjust the end-row, update vs insert
        $strQuery = "INSERT INTO "._dbprefix_.self::getTableForClass($strClass)."
                 (change_id,
                  change_date,
                  change_systemid,
                  change_system_previd,
                  change_user,
                  change_class,
                  change_action,
                  change_property,
                  change_oldvalue,
                  change_newvalue) VALUES
                 (?,?,?,?,?,?,?,?,?,?)";

        class_carrier::getInstance()->getObjDB()->_pQuery(
            $strQuery,
            array(
                generateSystemid(),
                $objEndDate->getLongTimestamp(),
                $strSystemid,
                $strPrevid,
                $strUser,
                $strClass,
                $strAction,
                $strProperty,
                $strNewValue,
                (isset($arrEndRow["change_oldvalue"]) ? $arrEndRow["change_oldvalue"] : "")
            )
        );



        class_carrier::getInstance()->getObjDB()->flushQueryCache();
    }

    /**
     * Fetches a single value from the change-sets, if not unique the latest value for the specified date is returned.
     *
     * @param string $strSystemid
     * @param string $strProperty
     * @param class_date $objDate
     *
     * @static
     * @return string
     */
    public static function getValueForDate($strSystemid, $strProperty, class_date $objDate) {
        $strQuery = "SELECT change_newvalue
                       FROM "._dbprefix_.self::getTableForClass(class_objectfactory::getInstance()->getClassNameForId($strSystemid))."
                      WHERE change_systemid = ?
                        AND change_property = ?
                        AND change_date <= ?
                   ORDER BY change_date DESC ";

        $arrRow = class_carrier::getInstance()->getObjDB()->getPArray($strQuery, array($strSystemid, $strProperty, $objDate->getLongTimestamp()), 0, 1);
        if(isset($arrRow[0]["change_newvalue"]))
            return $arrRow[0]["change_newvalue"];
        else
            return false;
    }

    /**
     * Fetches all change-sets within the specified period for the given property.
     *
     * @param string $strSystemid
     * @param string $strProperty
     * @param class_date $objDateFrom
     * @param class_date $objDateTo
     *
     * @static
     * @return array
     */
    public static function getValuesForDateRange($strSystemid, $strProperty, class_date $objDateFrom, class_date $objDateTo) {
        $strQuery = "SELECT change_oldvalue, change_newvalue
                       FROM "._dbprefix_.self::getTableForClass(class_objectfactory::getInstance()->getClassNameForId($strSystemid))."
                      WHERE change_systemid = ?
                        AND change_property = ?
                        AND change_date >= ?
                        AND change_date <= ?
                   ORDER BY change_date DESC ";

        $arrRow = class_carrier::getInstance()->getObjDB()->getPArray($strQuery, array($strSystemid, $strProperty, $objDateFrom->getLongTimestamp(), $objDateTo->getLongTimestamp()), 0, 1);
        return $arrRow;
    }

    /**
     * Returns the count of an specific class and property for a given date range in the changelog. Counts optional
     * only the entries which are available in $arrNewValues
     *
     * @param $strClass
     * @param $strProperty
     * @param class_date $objDateFrom
     * @param class_date $objDateTo
     * @param $arrNewValues
     * @return int
     */
    public static function getCountForDateRange($strClass, $strProperty, class_date $objDateFrom, class_date $objDateTo, array $arrNewValues = null, array $arrAllowedSystemIds = null) {
        $strQuery = "SELECT COUNT(DISTINCT change_systemid) AS num
                       FROM "._dbprefix_.self::getTableForClass($strClass)."
                      WHERE change_class = ?
                        AND change_property = ?
                        AND change_date >= ?
                        AND change_date <= ?";

        $arrParameters = array($strClass, $strProperty);
        $arrParameters[] = $objDateFrom->getLongTimestamp();
        $arrParameters[] = $objDateTo->getLongTimestamp();

        if(!empty($arrNewValues)) {
            if(count($arrNewValues) > 1) {
                $objRestriction = new class_orm_objectlist_in_restriction("change_newvalue", $arrNewValues);
                $strQuery.= " " . $objRestriction->getStrWhere();
                $arrParameters = array_merge($arrParameters, $objRestriction->getArrParams());
            }
            else {
                $strQuery.= " AND change_newvalue = ?";
                $arrParameters[] = current($arrNewValues);
            }
        }

        if($arrAllowedSystemIds !== null) {
            $objRestriction = new class_orm_objectlist_in_restriction("change_systemid", $arrAllowedSystemIds);
            $strQuery.= " " . $objRestriction->getStrWhere();
            $arrParameters = array_merge($arrParameters, $objRestriction->getArrParams());
        }

        $arrRow = class_carrier::getInstance()->getObjDB()->getPArray($strQuery, $arrParameters, 0, 1);
        return isset($arrRow[0]["num"]) ? $arrRow[0]["num"] : 0;
    }

    /**
     * Returns the new values for an specific class and property in a given date range. Groups the result by systemid so
     * that only the latest value is returned (in the given date range)
     *
     * @param $strClass
     * @param $strProperty
     * @param class_date $objDateFrom
     * @param class_date $objDateTo
     * @return array
     */
    public static function getNewValuesForDateRange($strClass, $strProperty, class_date $objDateFrom, class_date $objDateTo, array $arrAllowedSystemIds = null) {

        $strQuery = "SELECT change_newvalue,
                            change_systemid
                       FROM "._dbprefix_.self::getTableForClass($strClass)."
                      WHERE change_date >= ?
                        AND change_date <= ?
                        AND change_class = ?
                        AND change_property = ?";

        $arrParameters = array($objDateFrom->getLongTimestamp(), $objDateTo->getLongTimestamp(), $strClass, $strProperty);

        if($arrAllowedSystemIds !== null) {
            $objRestriction = new class_orm_objectlist_in_restriction("change_systemid", $arrAllowedSystemIds);
            $strQuery.= " " . $objRestriction->getStrWhere();
            $arrParameters = array_merge($arrParameters, $objRestriction->getArrParams());
        }

        $strQuery.= "GROUP BY change_systemid ORDER BY change_date DESC";

        return class_carrier::getInstance()->getObjDB()->getPArray($strQuery, $arrParameters);
    }

    /**
     * Returns a list of objects implementing the changelog-provider-interface
     * @return interface_changelog_provider[]
     */
    public static function getAdditionalProviders() {

        if(self::$arrCachedProviders != null)
            return self::$arrCachedProviders;

        $arrReturn = class_resourceloader::getInstance()->getFolderContent("/system", array(".php"), false, function($strOneFile) {
            if(uniStrpos($strOneFile, "class_changelog_provider") === false)
                return false;

            $objReflection = new ReflectionClass(uniSubstr($strOneFile, 0, -4));
            if($objReflection->implementsInterface("interface_changelog_provider")) {
                return true;
            }

            return false;

        },
        function(&$strOneFile) {
            $objReflection = new ReflectionClass(uniSubstr($strOneFile, 0, -4));
            $strOneFile = $objReflection->newInstance();
        });

        self::$arrCachedProviders = $arrReturn;

        return $arrReturn;
    }

    /**
     * Retuns a list of additional objects mapped to tables
     * @return array (class => table)
     */
    public static function getAdditionalTables() {
        $arrTables = array();
        foreach(self::getAdditionalProviders() as $objOneProvider) {
            foreach($objOneProvider->getHandledClasses() as $strOneClass)
                $arrTables[$strOneClass] = $objOneProvider->getTargetTable();
        }
        return $arrTables;
    }

    /**
     * Returns the target-table for a single class
     * or the default table if not found.
     *
     * @param string $strClass
     *
     * @return string
     */
    public static function getTableForClass($strClass) {
        $arrTables = self::getAdditionalTables();

        if($strClass != null && $strClass != "" && isset($arrTables[$strClass]))
            return $arrTables[$strClass];

        else return "changelog";
    }
}


/**
 * Simple data-container for logentries.
 * Has no regular use.
 */
final class class_changelog_container {
    private $objDate;
    private $strSystemid;
    private $strUserId;
    private $strClass;
    private $strAction;
    private $strProperty;
    private $strOldValue;
    private $strNewValue;

    /**
     * @param int $intDate
     * @param string $strSystemid
     * @param string $strUserId
     * @param string $strClass
     * @param string $strAction
     * @param string $strProperty
     * @param string $strOldValue
     * @param string $strNewValue
     */
    function __construct($intDate, $strSystemid, $strUserId, $strClass, $strAction, $strProperty, $strOldValue, $strNewValue) {
        $this->objDate = new class_date($intDate);
        $this->strSystemid = $strSystemid;
        $this->strUserId = $strUserId;
        $this->strClass = $strClass;
        $this->strAction = $strAction;
        $this->strProperty = $strProperty;
        $this->strOldValue = $strOldValue;
        $this->strNewValue = $strNewValue;
    }

    /**
     * @return interface_versionable
     */
    public function getObjTarget() {
        if(class_exists($this->strClass))
            return new $this->strClass($this->strSystemid);
        else
            return null;
    }

    /**
     * @return class_date
     */
    public function getObjDate() {
        return $this->objDate;
    }

    /**
     * @return mixed
     */
    public function getStrSystemid() {
        return $this->strSystemid;
    }

    /**
     * @return mixed
     */
    public function getStrUserId() {
        return $this->strUserId;
    }

    /**
     * @return string
     */
    public function getStrUsername() {
        $objUser = new class_module_user_user($this->getStrUserId());
        return $objUser->getStrDisplayName();
    }

    /**
     * @return mixed
     */
    public function getStrClass() {
        return $this->strClass;
    }

    /**
     * @return mixed
     */
    public function getStrAction() {
        return $this->strAction;
    }

    /**
     * @return mixed
     */
    public function getStrOldValue() {
        return $this->strOldValue;
    }

    /**
     * @return mixed
     */
    public function getStrNewValue() {
        return $this->strNewValue;
    }

    /**
     * @return mixed
     */
    public function getStrProperty() {
        return $this->strProperty;
    }

}
