<?php
/*"******************************************************************************************************
*   (c) 2007-2013 by Kajona, www.kajona.de                                                              *
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
 */
class class_module_system_changelog extends class_model implements interface_model {

    /**
     * A flag to disable the reading of old values for the current execution.
     * If set to true, the current value of an objects' property are not loaded into the cache.
     * In consequence, the changelog-generation is disabled, too.
     *
     * @var bool
     */
    public static $bitDisableChangelog = false;

    private static $arrOldValueCache = array();
    private static $arrCachedProviders = null;


    public static $STR_ACTION_EDIT = "actionEdit";
    public static $STR_ACTION_DELETE = "actionDelete";


    /**
     * Constructor to create a valid object
     *
     * @param string $strSystemid (use "" on new objects)
     */
    public function __construct($strSystemid = "") {
        $this->setArrModuleEntry("modul", "system");
        $this->setArrModuleEntry("moduleId", _system_modul_id_);

        parent::__construct($strSystemid);

    }

    /**
     * Returns the name to be used when rendering the current object, e.g. in admin-lists.
     *
     * @return string
     */
    public function getStrDisplayName() {
        return "changelog";
    }


    /**
     * Initialises the current object, if a systemid was given
     */
    protected function initObjectInternal() {
    }


    /**
     * Reads all properties marked with the annotation
     *
     * @versionable.
     * The state is cached in a static array mapped to the objects systemid.
     * In consequence, this means that only objects with a valid systemid are scanned for properties under versioning.
     *
     * @param interface_versionable $objCurrentObject
     *
     * @return array|null
     */
    public function readOldValues(interface_versionable $objCurrentObject) {
        if(!$this->isVersioningAvailable($objCurrentObject))
            return null;

        if(validateSystemid($objCurrentObject->getSystemid())) {
            $arrOldValues = $this->readVersionableProperties($objCurrentObject);
            self::$arrOldValueCache[$objCurrentObject->getSystemid()] = $arrOldValues;
            return $arrOldValues;
        }
        return null;
    }

    /**
     * Scans the passed object and tries to find all properties marked with the annotation @versionable.
     * @param interface_versionable $objCurrentObject
     *
     * @return array|null
     */
    private function readVersionableProperties(interface_versionable $objCurrentObject) {
        if(!$this->isVersioningAvailable($objCurrentObject))
            return null;

        if(validateSystemid($objCurrentObject->getSystemid())) {
            $arrOldValues = array();

            $objReflection = new class_reflection($objCurrentObject);
            $arrProperties = $objReflection->getPropertiesWithAnnotation("@versionable");


            foreach($arrProperties as $strProperty => $strAnnotation) {

                $strValue = "";

                //all prerequisites match, start creating query
                $strGetter = $objReflection->getGetter($strProperty);
                if($strGetter !== null) {
                    $strValue = call_user_func(array($objCurrentObject, $strGetter));
                }

                $strNamedEntry = trim(uniSubstr($strAnnotation, uniStrpos($strAnnotation, "@versionable")));
                //try to read the old value and see, if it should be mapped to a new name
                if($strNamedEntry != "")
                    $strProperty = $strNamedEntry;

                $arrOldValues[$strProperty] = $strValue;
            }

            self::$arrOldValueCache[$objCurrentObject->getSystemid()] = $arrOldValues;

            return $arrOldValues;
        }
        return null;
    }

    public function getOldValuesForSystemid($strSystemid) {
        if(isset(self::$arrOldValueCache[$strSystemid]))
            return self::$arrOldValueCache[$strSystemid];
        else
            return null;
    }

    /**
     * Builds the change-array based on the old- and new values
     * @param $objSourceModel
     *
     * @return array
     */
    private function createChangeArray($objSourceModel) {
        $arrOldValues = $this->getOldValuesForSystemid($objSourceModel->getSystemid());

        //this are now the new ones
        $arrNewValues = $this->readVersionableProperties($objSourceModel);

        if($arrOldValues == null || $arrNewValues == null)
            $arrOldValues = array();

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
     * @param $strAction
     * @param $arrEntries
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

        return $this->processChangeArray($this->createChangeArray($objSourceModel), $objSourceModel, $strAction, $bitForceEntry, $bitDeleteAction);
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

        if(self::$bitDisableChangelog)
            return false;

        if(!defined("_system_changehistory_enabled_") || _system_changehistory_enabled_ == "false")
            return false;

        if(!$objSourceModel instanceof interface_versionable) {
            throw new class_exception("object passed to create changelog not implementing interface_versionable", class_logger::$levelWarning);
        }


        //changes require at least kajona 3.4.9
        $arrModul = class_module_system_module::getPlainModuleData("system");
        if(version_compare($arrModul["module_version"], "3.4.9") < 0)
            return false;

        return true;
    }

    /**
     * Processes the internal change-array and creates all related records.
     *
     * @param array $arrChanges
     * @param interface_versionable $objSourceModel
     * @param $strAction
     * @param bool $bitForceEntry
     * @param bool $bitDeleteAction
     *
     * @return bool
     */
    private function processChangeArray(array $arrChanges, interface_versionable $objSourceModel, $strAction, $bitForceEntry = false, $bitDeleteAction = null) {
        $bitReturn = true;

        if(is_array($arrChanges) && in_array(_dbprefix_."changelog", $this->objDB->getTables())) {
            foreach($arrChanges as $arrChangeSet) {


                $strOldvalue = "";
                if(isset($arrChangeSet["oldvalue"]))
                    $strOldvalue = $arrChangeSet["oldvalue"];

                $strNewvalue = "";
                if(isset($arrChangeSet["newvalue"]))
                    $strNewvalue = $arrChangeSet["newvalue"];

                $strProperty = $arrChangeSet["property"];


                //array may be processed automatically, too
                if(is_array($strOldvalue) && is_array($strNewvalue)) {

                    $arrArrayChanges = array();
                    foreach($strNewvalue as $strOneId) {
                        if(!in_array($strOneId, $strOldvalue))
                            $arrArrayChanges[] = array("property" => $strProperty, "oldvalue" => "", "newvalue" => $strOneId);
                    }

                    foreach($strOldvalue as $strOneId) {
                        if(!in_array($strOneId, $strNewvalue))
                            $arrArrayChanges[] = array("property" => $strProperty, "oldvalue" => $strOneId, "newvalue" => "");
                    }

                    $this->processChangeArray($arrArrayChanges, $objSourceModel, $strAction, $bitForceEntry, $bitDeleteAction);
                    continue;
                }


                if($strOldvalue instanceof class_date)
                    $strOldvalue = $strOldvalue->getLongTimestamp();

                if($strNewvalue instanceof class_date)
                    $strNewvalue = $strNewvalue->getLongTimestamp();

                if($bitDeleteAction || ($bitDeleteAction === null && $strAction == self::$STR_ACTION_DELETE))
                    $strNewvalue = "";

                if(!$bitForceEntry && ($strOldvalue === $strNewvalue))
                    continue;

                class_logger::getInstance()->addLogRow(
                    "change in class ".get_class($objSourceModel)."@".$strAction." systemid: ".$objSourceModel->getSystemid()." property: ".$strProperty." old value: "
                    .uniStrTrim($strOldvalue, 60)." new value: ".uniStrTrim($strNewvalue, 60),
                    class_logger::$levelInfo
                );

                $strQuery = "INSERT INTO "._dbprefix_.self::getTableForClass(get_class($objSourceModel))."
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

                $bitReturn = $bitReturn && $this->objDB->_pQuery(
                    $strQuery,
                    array(
                        generateSystemid(),
                        class_date::getCurrentTimestamp(),
                        $objSourceModel->getSystemid(),
                        $objSourceModel->getPrevid(),
                        $this->objSession->getUserID(),
                        get_class($objSourceModel),
                        $strAction,
                        $strProperty,
                        $strOldvalue,
                        $strNewvalue
                    )
                );
            }
        }
        return $bitReturn;
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
     * @static
     *
     * @param $strSystemid
     * @param class_date $objNewDate
     *
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
     * @param $strSystemid
     * @param $strAction
     * @param $strProperty
     * @param null $strPrevid
     * @param $strClass
     * @param null $strUser
     * @param $strNewValue
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
     * @static
     *
     * @param $strSystemid
     * @param $strProperty
     * @param class_date $objDate
     *
     * @return bool
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
     * Deletes the current object from the system
     * Overwrite!
     *
     * @return bool
     */
    public function deleteObject() {
        return true;
    }


    /**
     * Called whenever a update-request was fired.
     * Use this method to synchronize yourselves with the database.
     * Use only updates, inserts are not required to be implemented.
     *
     * @return bool
     */
    protected function updateStateToDb() {
        return true;
    }


    /**
     * Returns a list of objects implementing the changelog-provider-interface
     * @return interface_changelog_provider[]
     */
    public static function getAdditionalProviders() {

        if(self::$arrCachedProviders != null)
            return self::$arrCachedProviders;

        $arrSystemClasses = class_resourceloader::getInstance()->getFolderContent("/system", array(".php"));
        $arrReturn = array();
        foreach($arrSystemClasses as $strOneFile) {
            if(uniStrpos($strOneFile, "class_changelog_provider") !== false) {
                $objReflection = new ReflectionClass(uniSubstr($strOneFile, 0, -4));
                if($objReflection->implementsInterface("interface_changelog_provider"))
                    $arrReturn[] = $objReflection->newInstance();
            }
        }

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
     * @param $strClass
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

    public function getStrSystemid() {
        return $this->strSystemid;
    }

    public function getStrUserId() {
        return $this->strUserId;
    }

    public function getStrUsername() {
        $objUser = new class_module_user_user($this->getStrUserId());
        return $objUser->getStrUsername();
    }

    public function getStrClass() {
        return $this->strClass;
    }

    public function getStrAction() {
        return $this->strAction;
    }

    public function getStrOldValue() {
        return $this->strOldValue;
    }

    public function getStrNewValue() {
        return $this->strNewValue;
    }

    public function getStrProperty() {
        return $this->strProperty;
    }

}
