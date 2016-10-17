<?php
/*"******************************************************************************************************
*   (c) 2004-2006 by MulchProductions, www.mulchprod.de                                                 *
*   (c) 2007-2016 by Kajona, www.kajona.de                                                              *
*       Published under the GNU LGPL v2.1, see /system/licence_lgpl.txt                                 *
*-------------------------------------------------------------------------------------------------------*
*   $Id$                                              *
********************************************************************************************************/

namespace Kajona\System\System;


/**
 * The top-level class for models, installers and top-level files
 * An instance of this class is used by the admin & portal object to invoke common database based methods.
 * Change with care!
 *
 * @package module_system
 * @author sidler@mulchprod.de
 */
abstract class Root
{

    const STR_MODULE_ANNOTATION = "@module";
    const STR_MODULEID_ANNOTATION = "@moduleId";
    const STR_SORTMANAGER_ANNOTATION = "@sortManager";


    /**
     *
     * @var Config
     */
    protected $objConfig = null; //Object containing config-data
    /**
     *
     * @var Database
     */
    protected $objDB = null; //Object to the database
    /**
     * @var Session
     */
    protected $objSession = null; //Object containing the session-management

    /**
     * @var Lang
     */
    private $objLang = null; //Object managing the langfiles

    /**
     * @var SortmanagerInterface
     */
    private $objSortManager = null;

    private $strAction; //current action to perform (GET/POST)
    protected $arrModule = array(); //Array containing information about the current module


    private $arrInitRow = null;             //array to be used when loading details from the database. could reduce the amount of queries if populated.

    //--- fields to be synchronized with the database ---

    /**
     * The records current systemid
     *
     * @var string
     * @templateExport
     *
     * @tableColumn system.system_id
     */
    private $strSystemid = "";

    /**
     * The records internal parent-id
     *
     * @var string
     * @versionable
     *
     * @tableColumn system.system_prev_id
     */
    private $strPrevId = -1;

    /**
     * The old prev-id, used to track hierarchical changes -> requires a rebuild of the rights-table
     *
     * @var string
     */
    private $strOldPrevId = -1;

    /**
     * The records module-number
     *
     * @var int
     * @tableColumn system.system_module_nr
     */
    private $intModuleNr = 0;

    /**
     * The records sort-position relative to the parent record
     *
     * @var int
     * @tableColumn system.system_sort
     */
    private $intSort = -1;

    /**
     * The id of the user who created the record initially
     *
     * @var string
     * @versionable
     * @templateExport
     * @templateMapper user
     * @tableColumn system.system_owner
     */
    private $strOwner = "";

    /**
     * The id of the user last who did the last changes to the current record
     *
     * @var string
     * @tableColumn system.system_lm_user
     */
    private $strLmUser = "";

    /**
     * Timestamp of the last modification
     * ATTENTION: time() based, so 32 bit integer
     *
     * @todo migrate to long-timestamp
     * @var int
     * @templateExport
     * @templateMapper datetime
     * @tableColumn system.system_lm_time
     */
    private $intLmTime = 0;

    /**
     * The id of the user locking the current record, empty otherwise
     *
     * @var string
     * @tableColumn system.system_lock_id
     */
    private $strLockId = "";

    /**
     * Time the current locking was triggered
     * ATTENTION: time() based, so 32 bit integer
     *
     * @todo migrate to long-timestamp
     * @var int
     * @tableColumn system.system_lock_time
     */
    private $intLockTime = 0;

    /**
     * The records status
     *
     * @var int
     * @versionable
     * @tableColumn system.system_status
     */
    private $intRecordStatus = 1;

    /**
     * The records previous status, used to trigger status changed events
     *
     * @var int
     */
    private $intOldRecordStatus = 1;

    /**
     * Indicates whether the object is deleted, or not
     *
     * @var int
     * @versionable
     * @tableColumn system.system_deleted
     */
    private $intRecordDeleted = 0;

    /**
     * Human readable comment describing the current record
     *
     * @var string
     * @tableColumn system.system_comment
     */
    private $strRecordComment = "";

    /**
     * Holds the current objects' class
     *
     * @var string
     * @tableColumn system.system_class
     */
    private $strRecordClass = "";

    /**
     * Long-based representation of the timestamp the record was created initially
     *
     * @var int
     * @templateExport
     * @templateMapper datetime
     * @tableColumn system.system_create_date
     */
    private $longCreateDate = 0;

    /**
     * The start-date of the date-table
     *
     * @var Date
     * @versionable
     * @templateExport
     * @templateMapper datetime
     *
     * @tableColumn system_date.system_date_start
     */
    private $objStartDate = null;

    /**
     * The end date of the date-table
     *
     * @var Date
     * @versionable
     * @templateExport
     * @templateMapper datetime
     *
     * @tableColumn system_date.system_date_end
     */
    private $objEndDate = null;

    /**
     * The special-date of the date-table
     *
     * @var Date
     * @versionable
     * @templateExport
     * @templateMapper datetime
     *
     * @tableColumn system_date.system_date_special
     */
    private $objSpecialDate = null;

    private $bitDatesChanges = false;


    /**
     * Constructor
     *
     * @param string $strSystemid
     *
     * @return Root
     */
    public function __construct($strSystemid = "")
    {

        //Generating all the needed objects. For this we use our cool cool carrier-object
        //take care of loading just the necessary objects
        $objCarrier = Carrier::getInstance();
        $this->objConfig = $objCarrier->getObjConfig();
        $this->objDB = $objCarrier->getObjDB();
        $this->objSession = $objCarrier->getObjSession();
        $this->objLang = $objCarrier->getObjLang();

        //And keep the action
        $this->strAction = $this->getParam("action");

        $this->strSystemid = $strSystemid;

        $this->setStrRecordClass(get_class($this));

        //try to load the current module-name and the moduleId by reflection
        $objReflection = new Reflection($this);
        if (!isset($this->arrModule["modul"])) {
            $arrAnnotationValues = $objReflection->getAnnotationValuesFromClass(self::STR_MODULE_ANNOTATION);
            if (count($arrAnnotationValues) > 0) {
                $this->setArrModuleEntry("modul", trim($arrAnnotationValues[0]));
                $this->setArrModuleEntry("module", trim($arrAnnotationValues[0]));
            }
        }

        if (!isset($this->arrModule["moduleId"])) {
            $arrAnnotationValues = $objReflection->getAnnotationValuesFromClass(self::STR_MODULEID_ANNOTATION);
            if (count($arrAnnotationValues) > 0) {
                $this->setArrModuleEntry("moduleId", constant(trim($arrAnnotationValues[0])));
                $this->setIntModuleNr(constant(trim($arrAnnotationValues[0])));
            }
        }

        //set up a possible sort-manager
        $arrAnnotationValues = $objReflection->getAnnotationValuesFromClass(self::STR_SORTMANAGER_ANNOTATION);
        if (count($arrAnnotationValues) > 0) {
            $strClass = trim($arrAnnotationValues[0]);
            $this->objSortManager = new $strClass($this);
        }

        if ($strSystemid != "") {
            $this->initObject();
        }
    }


    /**
     * Method to invoke object initialization.
     * In nearly all cases, this is triggered by the framework itself.
     *
     * @return void
     */
    public final function initObject()
    {
        $this->initObjectInternal();
        $this->internalInit();

        // if given, read versioning information
        if ($this instanceof VersionableInterface) {
            $objChangelog = new SystemChangelog();
            $objChangelog->readOldValues($this);
        }
    }

    /**
     * InitObjectInternal is called during an objects instantiation.
     * The default implementation tries to map all database-fields to the objects fields
     * and sets the values automatically.
     *
     * If you have a different column-property mapping or additional
     * setters to call, overwrite this method.
     * The row loaded from the database is available by calling $this->getArrInitRow().
     *
     * @return void
     */
    protected function initObjectInternal()
    {
        $objORM = new OrmObjectinit($this);
        $objORM->initObjectFromDb();
    }

    /**
     * Init the current record with the system-fields
     *
     * @return void
     */
    private final function internalInit()
    {

        if (validateSystemid($this->getSystemid())) {

            if (is_array($this->arrInitRow)) {
                $arrRow = $this->arrInitRow;
            }
            else {
                $strQuery = "SELECT *
                               FROM "._dbprefix_."system
                          LEFT JOIN "._dbprefix_."system_date
                                 ON system_id = system_date_id
                              WHERE system_id = ? ";
                $arrRow = $this->objDB->getPRow($strQuery, array($this->getSystemid()));
            }


            if (count($arrRow) > 3) {
                //$this->setStrSystemid($arrRow["system_id"]);
                $this->strPrevId = $arrRow["system_prev_id"];
                $this->intModuleNr = $arrRow["system_module_nr"];
                $this->intSort = $arrRow["system_sort"];
                $this->strOwner = $arrRow["system_owner"];
                $this->strLmUser = $arrRow["system_lm_user"];
                $this->intLmTime = $arrRow["system_lm_time"];
                $this->strLockId = $arrRow["system_lock_id"];
                $this->intLockTime = $arrRow["system_lock_time"];
                $this->intRecordStatus = $arrRow["system_status"];
                $this->strRecordComment = $arrRow["system_comment"];
                $this->longCreateDate = $arrRow["system_create_date"];
                if (isset($arrRow["system_class"])) {
                    $this->strRecordClass = $arrRow["system_class"];
                }

                if (isset($arrRow["system_deleted"])) {
                    $this->intRecordDeleted = $arrRow["system_deleted"];
                }

                $this->strOldPrevId = $this->strPrevId;
                $this->intOldRecordStatus = $this->intRecordStatus;

                if ($arrRow["system_date_start"] > 0) {
                    $this->objStartDate = new Date($arrRow["system_date_start"]);
                }

                if ($arrRow["system_date_end"] > 0) {
                    $this->objEndDate = new Date($arrRow["system_date_end"]);
                }

                if ($arrRow["system_date_special"] > 0) {
                    $this->objSpecialDate = new Date($arrRow["system_date_special"]);
                }

            }

            $this->bitDatesChanges = false;
        }
    }

    /**
     * A generic approach to load a list of objects currently available.
     * This list can be filtered via the given filterObject.
     *
     * This method is only a simple approach to determine the instances in the
     * database, if you need more specific loaders, overwrite this method or add your own
     * implementation to the derived class.
     *
     * @param FilterBase $objFilter
     * @param string $strPrevid
     * @param null $intStart
     * @param null $intEnd
     *
     * @return self[]
     */
    public static function getObjectListFiltered(FilterBase $objFilter = null, $strPrevid = "", $intStart = null, $intEnd = null)
    {
        $objORM = new OrmObjectlist();

        if ($objFilter !== null) {
            $objFilter->addWhereConditionToORM($objORM);
        }

        return $objORM->getObjectList(get_called_class(), $strPrevid, $intStart, $intEnd);
    }


    /**
     * A generic approach to load a list of objects currently available.
     * This result can be filtered via the given filterObject.
     *
     * This method is only a simple approach to determine the instances in the
     * database, if you need more specific loaders, overwrite this method or add your own
     * implementation to the derived class.
     *
     * @param FilterBase $objFilter
     * @param string $strPrevid
     * @param null $intStart
     * @param null $intEnd
     *
     * @return int
     */
    public static function getObjectCountFiltered(FilterBase $objFilter = null, $strPrevid = "")
    {
        $objORM = new OrmObjectlist();

        if ($objFilter !== null) {
            $objFilter->addWhereConditionToORM($objORM);
        }

        return $objORM->getObjectCount(get_called_class(), $strPrevid);
    }

    /**
     * Validates if the current record may be restored
     *
     * @return bool
     */
    public function isRestorable()
    {
        //validate the parent nodes' id
        $objParent = Objectfactory::getInstance()->getObject($this->getStrPrevId());
        return $objParent != null && $objParent->getIntRecordDeleted() == 0;
    }


    public function restoreObject()
    {

        /** @var $this Root|ModelInterface */
        $this->objDB->transactionBegin();

        $this->intRecordDeleted = 0;
        if ($this->objSortManager !== null) {
            $this->intSort = $this->getNextSortValue($this->getStrPrevId());
        }
        $bitReturn = $this->updateObjectToDb();

        Objectfactory::getInstance()->removeFromCache($this->getSystemid());
        OrmRowcache::removeSingleRow($this->getSystemid());
        $this->objDB->flushQueryCache();

        if ($this->objSortManager !== null) {
            $this->objSortManager->fixSortOnPrevIdChange($this->strPrevId, $this->strPrevId);
        }

        $bitReturn = $bitReturn && CoreEventdispatcher::getInstance()->notifyGenericListeners(SystemEventidentifier::EVENT_SYSTEM_RECORDRESTORED_LOGICALLY, array($this->getSystemid(), get_class($this), $this));

        if ($bitReturn) {
            Logger::getInstance()->addLogRow("successfully restored record ".$this->getSystemid()." / ".$this->getStrDisplayName(), Logger::$levelInfo);
            $this->objDB->transactionCommit();
            return true;
        }
        else {
            Logger::getInstance()->addLogRow("error restoring record ".$this->getSystemid()." / ".$this->getStrDisplayName(), Logger::$levelInfo);
            $this->objDB->transactionRollback();
            return false;
        }
    }


    /**
     * Triggers the logical delete of the current object.
     * This means the object itself is not deleted, but marked as deleted. Restoring the object is
     * possible.
     *
     * @throws Exception
     * @return bool
     */
    public function deleteObject()
    {

        if (!$this->getLockManager()->isAccessibleForCurrentUser()) {
            return false;
        }

        /** @var $this Root|ModelInterface */
        $this->objDB->transactionBegin();

        //validate, if there are subrecords, so child nodes to be deleted
        $arrChilds = $this->objDB->getPArray("SELECT system_id FROM "._dbprefix_."system where system_prev_id = ? ORDER BY system_sort DESC", array($this->getSystemid()));
        foreach ($arrChilds as $arrOneChild) {
            if (validateSystemid($arrOneChild["system_id"])) {
                $objInstance = Objectfactory::getInstance()->getObject($arrOneChild["system_id"]);
                if ($objInstance !== null) {
                    $objInstance->deleteObject();
                }
            }
        }

        $this->intRecordDeleted = 1;
        $this->intSort = -1;
        $bitReturn = $this->updateObjectToDb();

        Objectfactory::getInstance()->removeFromCache($this->getSystemid());
        OrmRowcache::removeSingleRow($this->getSystemid());
        $this->objDB->flushQueryCache();

        if ($this->objSortManager !== null) {
            $this->objSortManager->fixSortOnDelete();
        }

        $bitReturn = $bitReturn && CoreEventdispatcher::getInstance()->notifyGenericListeners(SystemEventidentifier::EVENT_SYSTEM_RECORDDELETED_LOGICALLY, array($this->getSystemid(), get_class($this)));

        if ($bitReturn) {
            Logger::getInstance()->addLogRow("successfully deleted record ".$this->getSystemid()." / ".$this->getStrDisplayName(), Logger::$levelInfo);
            $this->objDB->transactionCommit();
            return true;
        }
        else {
            Logger::getInstance()->addLogRow("error deleting record ".$this->getSystemid()." / ".$this->getStrDisplayName(), Logger::$levelInfo);
            $this->objDB->transactionRollback();
            return false;
        }

    }

    /**
     * Deletes the object from the database. The record is removed in total, so no restoring will be possible.
     *
     * @return bool
     * @throws Exception
     */
    public function deleteObjectFromDatabase()
    {
        if (!$this->getLockManager()->isAccessibleForCurrentUser()) {
            return false;
        }

        if ($this instanceof VersionableInterface) {
            $objChanges = new SystemChangelog();
            $objChanges->createLogEntry($this, SystemChangelog::$STR_ACTION_DELETE);
        }

        /** @var $this Root|ModelInterface */
        $this->objDB->transactionBegin();

        //validate, if there are subrecords, so child nodes to be deleted
        $arrChilds = $this->objDB->getPArray("SELECT system_id FROM "._dbprefix_."system where system_prev_id = ? ORDER BY system_sort DESC", array($this->getSystemid()));
        foreach ($arrChilds as $arrOneChild) {
            if (validateSystemid($arrOneChild["system_id"])) {
                $objInstance = Objectfactory::getInstance()->getObject($arrOneChild["system_id"]);
                if ($objInstance !== null) {
                    $objInstance->deleteObjectFromDatabase();
                }
            }
        }

        $objORM = new OrmObjectdelete($this);
        $bitReturn = $objORM->deleteObject();

        if ($this->objSortManager !== null) {
            $this->objSortManager->fixSortOnDelete();
        }
        $bitReturn = $bitReturn && $this->deleteSystemRecord($this->getSystemid());

        Objectfactory::getInstance()->removeFromCache($this->getSystemid());
        OrmRowcache::removeSingleRow($this->getSystemid());


        //try to call other modules, maybe wanting to delete anything in addition, if the current record
        //is going to be deleted
        $bitReturn = $bitReturn && CoreEventdispatcher::getInstance()->notifyGenericListeners(SystemEventidentifier::EVENT_SYSTEM_RECORDDELETED, array($this->getSystemid(), get_class($this)));

        if ($bitReturn) {
            Logger::getInstance()->addLogRow("successfully deleted record ".$this->getSystemid()." / ".$this->getStrDisplayName(), Logger::$levelInfo);
            $this->objDB->transactionCommit();
            $this->objDB->flushQueryCache();
            return true;
        }
        else {
            Logger::getInstance()->addLogRow("error deleting record ".$this->getSystemid()." / ".$this->getStrDisplayName(), Logger::$levelInfo);
            $this->objDB->transactionRollback();
            $this->objDB->flushQueryCache();
            return false;
        }
    }

    // --- DATABASE-SYNCHRONIZATION -------------------------------------------------------------------------


    /**
     * Saves the current object to the database. Determines, whether the current object has to be inserted
     * or updated to the database.
     * In case of an update, the objects' updateStateToDb() method is being called (as required by \Kajona\System\System\Model).
     * In the case of a new object, a blank record is being created. Therefore, all tables returned by class' doc comment
     * will be filled with a new record (using the same new systemid as the primary key).
     * The newly created systemid is being set as the current objects' one and can be used in the afterwards
     * called updateStateToDb() method to reference the correct rows.
     *
     * @param string|bool $strPrevId The prev-id of the records, either to be used for the insert or to be used during the update of the record
     *
     * @return bool
     * @since 3.3.0
     * @throws Exception
     * @see \Kajona\System\System\ModelInterface
     *
     * @todo move to OrmObjectupdate completely
     */
    public function updateObjectToDb($strPrevId = false)
    {
        $bitCommit = true;
        /** @var $this Root|ModelInterface */
        if (!$this instanceof ModelInterface) {
            throw new Exception("current object must implement ".ModelInterface::class, Exception::$level_FATALERROR);
        }

        if (!$this->getLockManager()->isAccessibleForCurrentUser()) {
            $objUser = Objectfactory::getInstance()->getObject($this->getLockManager()->getLockId());
            throw new Exception("current object is locked by user ".$objUser->getStrDisplayName(), Exception::$level_ERROR);
        }

        if (is_object($strPrevId) && $strPrevId instanceof Root) {
            $strPrevId = $strPrevId->getSystemid();
        }

        $this->objDB->transactionBegin();

        //current systemid given? if not, create a new record.
        $bitRecordCreated = false;
        if (!validateSystemid($this->getSystemid())) {
            $bitRecordCreated = true;

            if ($strPrevId === false || $strPrevId === "" || $strPrevId === null) {
                //try to find the current modules-one
                if (isset($this->arrModule["modul"])) {
                    $objModule = SystemModule::getModuleByName($this->getArrModule("modul"), true);
                    if ($objModule == null) {
                        throw new Exception("failed to load module ".$this->getArrModule("modul")."@".get_class($this), Exception::$level_FATALERROR);
                    }
                    $strPrevId = $objModule->getSystemid();
                    if (!validateSystemid($strPrevId)) {
                        throw new Exception("automatic determination of module-id failed ", Exception::$level_FATALERROR);
                    }
                }
                else {
                    throw new Exception("insert with no previd ", Exception::$level_FATALERROR);
                }
            }

            if (!validateSystemid($strPrevId) && $strPrevId !== "0") {
                throw new Exception("insert with erroneous prev-id ", Exception::$level_FATALERROR);
            }

            //create the new systemrecord
            //store date-bit temporary
            $bitDates = $this->bitDatesChanges;
            $this->createSystemRecord($strPrevId, $this->getStrDisplayName());
            $this->bitDatesChanges = $bitDates;

            if (validateSystemid($this->getStrSystemid())) {

                //Create the foreign records
                $objAnnotations = new Reflection($this);
                $arrTargetTables = $objAnnotations->getAnnotationValuesFromClass("@targetTable");
                if (count($arrTargetTables) > 0) {
                    foreach ($arrTargetTables as $strOneConfig) {
                        $arrSingleTable = explode(".", $strOneConfig);
                        $strQuery = "INSERT INTO ".$this->objDB->encloseTableName(_dbprefix_.$arrSingleTable[0])."
                                                (".$this->objDB->encloseColumnName($arrSingleTable[1]).") VALUES
                                                (?) ";

                        if (!$this->objDB->_pQuery($strQuery, array($this->getStrSystemid()))) {
                            $bitCommit = false;
                        }
                    }
                }

                if (!$this->onInsertToDb()) {
                    $bitCommit = false;
                }

            }
            else {
                throw new Exception("creation of systemrecord failed", Exception::$level_FATALERROR);
            }

            //all updates are done, start the "real" update
            Carrier::getInstance()->flushCache(Carrier::INT_CACHE_TYPE_DBQUERIES);

            //reset the old values cache for the new record
            if($this instanceof VersionableInterface) {
                $objChanges = new SystemChangelog();
                $objChanges->resetOldValues($this);
            }
        }

        //new prev-id?
        if ($strPrevId !== false && $this->getSystemid() != $strPrevId && (validateSystemid($strPrevId) || $strPrevId == "0")) {
            //validate the new prev id - it is not allowed to set a parent-node as a sub-node of its own child
            if (!$this->isSystemidChildNode($this->getSystemid(), $strPrevId)) {
                $this->setStrPrevId($strPrevId);
            }
        }

        //new comment?
        $this->setStrRecordComment($this->getStrDisplayName());

        //Keep old and new status here, status changed event is being fired after record is completely updated (so after updateStateToDb())
        $intOldStatus = $this->intOldRecordStatus;
        $intNewStatus = $this->intRecordStatus;

        //save back to the database
        $bitCommit = $bitCommit && $this->updateSystemrecord();

        //update ourselves to the database
        if ($bitCommit && !$this->updateStateToDb()) {
            $bitCommit = false;
        }

        //now fire the status changed event
        if ($intOldStatus != $intNewStatus && $intOldStatus != -1) {
            CoreEventdispatcher::getInstance()->notifyGenericListeners(SystemEventidentifier::EVENT_SYSTEM_STATUSCHANGED, array($this->getSystemid(), $this, $intOldStatus, $intNewStatus));
        }

        if ($bitCommit) {
            $this->objDB->transactionCommit();
            //unlock the record
            $this->getLockManager()->unlockRecord();
            Logger::getInstance()->addLogRow("updateObjectToDb() succeeded for systemid ".$this->getSystemid()." (".$this->getRecordComment().")", Logger::$levelInfo);

            //call the recordUpdated-Listeners
            CoreEventdispatcher::getInstance()->notifyGenericListeners(SystemEventidentifier::EVENT_SYSTEM_RECORDUPDATED, array($this, $bitRecordCreated));
        }
        else {
            $this->objDB->transactionRollback();
            Logger::getInstance()->addLogRow("updateObjectToDb() failed for systemid ".$this->getSystemid()." (".$this->getRecordComment().")", Logger::$levelWarning);
        }



        Carrier::getInstance()->flushCache(Carrier::INT_CACHE_TYPE_DBQUERIES);
        return $bitCommit;
    }


    /**
     * A default implementation for copy-operations.
     * Overwrite this method if you want to execute additional statements.
     * Please be aware that you are working on the new object afterwards!
     *
     * @param string $strNewPrevid
     * @param bool $bitChangeTitle
     * @param bool $bitCopyChilds
     *
     * @throws Exception
     * @return bool
     */
    public function copyObject($strNewPrevid = "", $bitChangeTitle = true, $bitCopyChilds = true)
    {

        $this->objDB->transactionBegin();

        $strOldSysid = $this->getSystemid();

        if ($strNewPrevid == "") {
            $strNewPrevid = $this->strPrevId;
        }

        //any date-objects to copy?
        if ($this->objStartDate != null || $this->objEndDate != null || $this->objSpecialDate != null) {
            $this->bitDatesChanges = true;
        }

        //check if there's a title field, in most cases that could be used to change the title
        if ($bitChangeTitle) {
            $objReflection = new Reflection($this);
            $strGetter = $objReflection->getGetter("strTitle");
            $strSetter = $objReflection->getSetter("strTitle");
            if ($strGetter != null && $strSetter != null) {
                $strTitle = $this->{$strGetter}();
                if ($strTitle != "") {
                    $this->{$strSetter}($strTitle."_copy");
                }
            }
        }

        //prepare the current object
        $this->unsetSystemid();
        $this->arrInitRow = null;
        $bitReturn = $this->updateObjectToDb($strNewPrevid);
        Carrier::getInstance()->getObjRights()->copyPermissions($strOldSysid, $this->getSystemid());
        //call event listeners
        $bitReturn = $bitReturn && CoreEventdispatcher::getInstance()->notifyGenericListeners(SystemEventidentifier::EVENT_SYSTEM_RECORDCOPIED, array($strOldSysid, $this->getSystemid(), $this));


        if ($bitCopyChilds) {
            //process subrecords
            //validate, if there are subrecords, so child nodes to be copied to the current record
            $arrChilds = $this->objDB->getPArray("SELECT system_id FROM "._dbprefix_."system where system_prev_id = ? ORDER BY system_sort ASC", array($strOldSysid));
            foreach ($arrChilds as $arrOneChild) {
                if (validateSystemid($arrOneChild["system_id"])) {
                    $objInstance = Objectfactory::getInstance()->getObject($arrOneChild["system_id"]);
                    if ($objInstance !== null) {
                        $objInstance->copyObject($this->getSystemid(), false);
                    }
                }
            }
        }


        if ($bitReturn) {
            $this->objDB->transactionCommit();
        }
        else {
            $this->objDB->transactionRollback();
        }

        $this->objDB->flushQueryCache();


        $bitReturn = $bitReturn && CoreEventdispatcher::getInstance()->notifyGenericListeners(SystemEventidentifier::EVENT_SYSTEM_RECORDCOPYFINISHED, array($strOldSysid, $this->getSystemid(), $this));

        return $bitReturn;
    }


    /**
     * Internal helper, checks if a child-node is the descendant of a given base-node
     *
     * @param string $strBaseId
     * @param string $strChildId
     *
     * @return bool
     */
    private function isSystemidChildNode($strBaseId, $strChildId)
    {

        while (validateSystemid($strChildId)) {
            $objCommon = new SystemCommon($strChildId);
            if ($objCommon->getSystemid() == $strBaseId) {
                return true;
            }
            else {
                return $this->isSystemidChildNode($strBaseId, $objCommon->getPrevId());
            }
        }

        return false;
    }


    /**
     * Called whenever a update-request was fired.
     * Use this method to synchronize the current object with the database.
     * Use only updates, inserts are not required to be implemented.
     * Provides a default implementation based on the current objects column mappings.
     * Override this method whenever you want to perform additional actions or escaping.
     *
     * @throws Exception
     * @return bool
     */
    protected function updateStateToDb()
    {
        $objORMMapper = new OrmObjectupdate($this);
        return $objORMMapper->updateStateToDb();
    }

    /**
     * Overwrite this method if you want to trigger additional commands during the insert
     * of an object, e.g. to create additional objects / relations
     *
     * @return bool
     */
    protected function onInsertToDb()
    {
        return true;
    }

    /**
     * Updates the current record to the database and saves all relevant fields.
     * Please note that this method is triggered internally.
     *
     * @return bool
     * @final
     * @since 3.4.1
     *
     * @todo find ussages and make private
     */
    protected final function updateSystemrecord()
    {

        if (!validateSystemid($this->getSystemid())) {
            return true;
        }

        Logger::getInstance()->addLogRow("updated systemrecord ".$this->getStrSystemid()." data", Logger::$levelInfo);

        if (SystemModule::getModuleByName("system") != null && version_compare(SystemModule::getModuleByName("system")->getStrVersion(), "4.7.5", "lt")) {
            $strQuery = "UPDATE "._dbprefix_."system
                        SET system_prev_id = ?,
                            system_module_nr = ?,
                            system_sort = ?,
                            system_owner = ?,
                            system_lm_user = ?,
                            system_lm_time = ?,
                            system_lock_id = ?,
                            system_lock_time = ?,
                            system_status = ?,
                            system_comment = ?,
                            system_class = ?,
                            system_create_date = ?
                      WHERE system_id = ? ";

            $bitReturn = $this->objDB->_pQuery(
                $strQuery,
                array(
                    $this->getStrPrevId(),
                    (int)$this->getIntModuleNr(),
                    (int)$this->getIntSort(),
                    $this->getStrOwner(),
                    $this->objSession->getUserID(),
                    time(),
                    $this->getStrLockId(),
                    (int)$this->getIntLockTime(),
                    (int)$this->getIntRecordStatus(),
                    uniStrTrim($this->getStrRecordComment(), 245),
                    $this->getStrRecordClass(),
                    $this->getLongCreateDate(),
                    $this->getSystemid()
                )
            );
        }
        else {


            $strQuery = "UPDATE "._dbprefix_."system
                        SET system_prev_id = ?,
                            system_module_nr = ?,
                            system_sort = ?,
                            system_owner = ?,
                            system_lm_user = ?,
                            system_lm_time = ?,
                            system_lock_id = ?,
                            system_lock_time = ?,
                            system_status = ?,
                            system_comment = ?,
                            system_class = ?,
                            system_create_date = ?,
                            system_deleted = ?
                      WHERE system_id = ? ";

            $bitReturn = $this->objDB->_pQuery(
                $strQuery,
                array(
                    $this->getStrPrevId(),
                    (int)$this->getIntModuleNr(),
                    (int)$this->getIntSort(),
                    $this->getStrOwner(),
                    $this->objSession->getUserID(),
                    time(),
                    $this->getStrLockId(),
                    (int)$this->getIntLockTime(),
                    (int)$this->getIntRecordStatus(),
                    uniStrTrim($this->getStrRecordComment(), 245),
                    $this->getStrRecordClass(),
                    $this->getLongCreateDate(),
                    $this->getIntRecordDeleted(),
                    $this->getSystemid()
                )
            );
        }

        if ($this->bitDatesChanges) {
            $this->processDateChanges();
        }

        Carrier::getInstance()->flushCache(Carrier::INT_CACHE_TYPE_DBQUERIES | Carrier::INT_CACHE_TYPE_ORMCACHE);

        if ($this->strOldPrevId != $this->strPrevId && $this->strOldPrevId != -1) {
            Carrier::getInstance()->getObjRights()->rebuildRightsStructure($this->getSystemid());
            CoreEventdispatcher::getInstance()->notifyGenericListeners(SystemEventidentifier::EVENT_SYSTEM_PREVIDCHANGED, array($this->getSystemid(), $this->strOldPrevId, $this->strPrevId));
        }
        if ($this->strOldPrevId != $this->strPrevId && $this->objSortManager !== null) {
            $this->objSortManager->fixSortOnPrevIdChange($this->strOldPrevId, $this->strPrevId);
        }

        $this->strOldPrevId = $this->strPrevId;
        $this->intOldRecordStatus = $this->intRecordStatus;

        return $bitReturn;
    }


    /**
     * Internal helper to fetch the next sort-id
     *
     * @param string $strPrevId
     *
     * @return int
     */
    private function getNextSortValue($strPrevId)
    {

        if ($this->objSortManager == null) {
            return -1;
        }

        //determine the correct new sort-id - append by default
        if (SystemModule::getModuleByName("system") != null && version_compare(SystemModule::getModuleByName("system")->getStrVersion(), "4.7.5", "lt")) {
            $strQuery = "SELECT COUNT(*) FROM "._dbprefix_."system WHERE system_prev_id = ? AND system_id != '0'";
        }
        else {
            $strQuery = "SELECT COUNT(*) FROM "._dbprefix_."system WHERE system_prev_id = ? AND system_id != '0' AND system_deleted = 0";

        }
        $arrRow = $this->objDB->getPRow($strQuery, array($strPrevId), 0, false);
        $intSiblings = $arrRow["COUNT(*)"];
        return (int)($intSiblings + 1);
    }

    /**
     * Generates a new SystemRecord and, if needed, the corresponding record in the rights-table (here inheritance is default)
     * Returns the systemID used for this record
     *
     * @param string $strPrevId Previous ID in the tree-structure
     * @param string $strComment Comment to identify the record
     *
     * @return string The ID used/generated
     *
     * @todo find usages and make private
     */
    private function createSystemRecord($strPrevId, $strComment)
    {

        $strSystemId = generateSystemid();

        $this->setStrSystemid($strSystemId);

        //Correct prevID
        if ($strPrevId == "") {
            $strPrevId = 0;
        }

        $this->setStrPrevId($strPrevId);

        $strComment = uniStrTrim(strip_tags($strComment), 240);


        if (SystemModule::getModuleByName("system") != null && version_compare(SystemModule::getModuleByName("system")->getStrVersion(), "4.7.5", "lt")) {
            //So, lets generate the record
            $strQuery = "INSERT INTO "._dbprefix_."system
                     ( system_id, system_prev_id, system_module_nr, system_owner, system_create_date, system_lm_user,
                       system_lm_time, system_status, system_comment, system_sort, system_class) VALUES
                     (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?)";

            //Send the query to the db
            $this->objDB->_pQuery(
                $strQuery,
                array(
                    $strSystemId,
                    $strPrevId,
                    $this->getIntModuleNr(),
                    $this->objSession->getUserID(),
                    Date::getCurrentTimestamp(),
                    $this->objSession->getUserID(),
                    time(),
                    (int)$this->getIntRecordStatus(),
                    $strComment,
                    $this->getNextSortValue($strPrevId),
                    $this->getStrRecordClass()
                )
            );
        }
        else {
            //So, lets generate the record
            $strQuery = "INSERT INTO "._dbprefix_."system
                     ( system_id, system_prev_id, system_module_nr, system_owner, system_create_date, system_lm_user,
                       system_lm_time, system_status, system_comment, system_sort, system_class, system_deleted) VALUES
                     (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?)";

            //Send the query to the db
            $this->objDB->_pQuery(
                $strQuery,
                array(
                    $strSystemId,
                    $strPrevId,
                    $this->getIntModuleNr(),
                    $this->objSession->getUserID(),
                    Date::getCurrentTimestamp(),
                    $this->objSession->getUserID(),
                    time(),
                    (int)$this->getIntRecordStatus(),
                    $strComment,
                    $this->getNextSortValue($strPrevId),
                    $this->getStrRecordClass(),
                    $this->getIntRecordDeleted()
                )
            );
        }

        //we need a Rights-Record
        $this->objDB->_pQuery("INSERT INTO "._dbprefix_."system_right (right_id, right_inherit) VALUES (?, 1)", array($strSystemId));
        //update rights to inherit
        Carrier::getInstance()->getObjRights()->setInherited(true, $strSystemId);

        Logger::getInstance()->addLogRow("new system-record created: ".$strSystemId." (".$strComment.")", Logger::$levelInfo);
        $this->objDB->flushQueryCache();
        $this->internalInit();
        //reset the old values since we're having a new record
        $this->strOldPrevId = -1;
        $this->intOldRecordStatus = -1;

        return $strSystemId;

    }

    /**
     * Process date changes handles the insert and update of date-objects.
     * It replaces the old createDate and updateDate methods
     *
     * @return bool
     */
    private function processDateChanges()
    {

        $intStart = 0;
        $intEnd = 0;
        $intSpecial = 0;

        if ($this->objStartDate != null && $this->objStartDate instanceof Date) {
            $intStart = $this->objStartDate->getLongTimestamp();
        }

        if ($this->objEndDate != null && $this->objEndDate instanceof Date) {
            $intEnd = $this->objEndDate->getLongTimestamp();
        }

        if ($this->objSpecialDate != null && $this->objSpecialDate instanceof Date) {
            $intSpecial = $this->objSpecialDate->getLongTimestamp();
        }

        $arrRow = $this->objDB->getPRow("SELECT COUNT(*) FROM "._dbprefix_."system_date WHERE system_date_id = ?", array($this->getSystemid()));
        if ($arrRow["COUNT(*)"] == 0) {
            //insert
            $strQuery = "INSERT INTO "._dbprefix_."system_date
                      (system_date_id, system_date_start, system_date_end, system_date_special) VALUES
                      (?, ?, ?, ?)";
            return $this->objDB->_pQuery($strQuery, array($this->getSystemid(), $intStart, $intEnd, $intSpecial));
        }
        else {
            $strQuery = "UPDATE "._dbprefix_."system_date
                      SET system_date_start = ?,
                          system_date_end = ?,
                          system_date_special = ?
                    WHERE system_date_id = ?";
            return $this->objDB->_pQuery($strQuery, array($intStart, $intEnd, $intSpecial, $this->getSystemid()));
        }
    }


    /**
     * Creates a record in the date table. Make sure to use a proper system-id!
     * Up from Kajona V3.3, the signature changed. Pass instances of Date instead of
     * int-values.
     *
     * @param string $strSystemid
     * @param Date $objStartDate
     * @param Date $objEndDate
     * @param Date $objSpecialDate
     *
     * @deprecated use the internal date-objects to have all dates handled automatically
     * @return bool
     */
    public function createDateRecord($strSystemid, Date $objStartDate = null, Date $objEndDate = null, Date $objSpecialDate = null)
    {
        $intStart = 0;
        $intEnd = 0;
        $intSpecial = 0;

        if ($objStartDate != null && $objStartDate instanceof Date) {
            $intStart = $objStartDate->getLongTimestamp();
        }

        if ($objEndDate != null && $objEndDate instanceof Date) {
            $intEnd = $objEndDate->getLongTimestamp();
        }

        if ($objSpecialDate != null && $objSpecialDate instanceof Date) {
            $intSpecial = $objSpecialDate->getLongTimestamp();
        }

        $strQuery = "INSERT INTO "._dbprefix_."system_date
                      (system_date_id, system_date_start, system_date_end, system_date_special) VALUES
                      (?, ?, ?, ?)";
        return $this->objDB->_pQuery($strQuery, array($strSystemid, $intStart, $intEnd, $intSpecial));
    }

    /**
     * Updates a record in the date table. Make sure to use a proper system-id!
     * Up from Kajona V3.3, the signature changed. Pass instances of Date instead of
     * int-values.
     *
     * @param string $strSystemid
     * @param Date $objStartDate
     * @param Date $objEndDate
     * @param Date $objSpecialDate
     *
     * @deprecated use the internal date-objects to have all dates handled automatically
     * @return bool
     */
    public function updateDateRecord($strSystemid, Date $objStartDate = null, Date $objEndDate = null, Date $objSpecialDate = null)
    {
        $intStart = 0;
        $intEnd = 0;
        $intSpecial = 0;

        if ($objStartDate != null && $objStartDate instanceof Date) {
            $intStart = $objStartDate->getLongTimestamp();
        }

        if ($objEndDate != null && $objEndDate instanceof Date) {
            $intEnd = $objEndDate->getLongTimestamp();
        }

        if ($objSpecialDate != null && $objSpecialDate instanceof Date) {
            $intSpecial = $objSpecialDate->getLongTimestamp();
        }

        $strQuery = "UPDATE "._dbprefix_."system_date
                      SET system_date_start = ?,
                          system_date_end = ?,
                          system_date_special = ?
                    WHERE system_date_id = ?";
        return $this->objDB->_pQuery($strQuery, array($intStart, $intEnd, $intSpecial, $strSystemid));
    }


    /**
     * Returns the bool-value for the right to view this record,
     * Systemid MUST be given, otherwise false
     *
     * @return bool
     */
    public function rightView()
    {
        return Carrier::getInstance()->getObjRights()->rightView($this->getSystemid());
    }

    /**
     * Returns the bool-value for the right to edit this record,
     * Systemid MUST be given, otherwise false
     *
     * @return bool
     */
    public function rightEdit()
    {
        return Carrier::getInstance()->getObjRights()->rightEdit($this->getSystemid());
    }

    /**
     * Returns the bool-value for the right to delete this record,
     * Systemid MUST be given, otherwise false
     *
     * @return bool
     */
    public function rightDelete()
    {
        return Carrier::getInstance()->getObjRights()->rightDelete($this->getSystemid());
    }

    /**
     * Returns the bool-value for the right to change rights of this record,
     * Systemid MUST be given, otherwise false
     *
     * @return bool
     */
    public function rightRight()
    {
        return Carrier::getInstance()->getObjRights()->rightRight($this->getSystemid());
    }

    /**
     * Returns the bool-value for the right1 of this record,
     * Systemid MUST be given, otherwise false
     *
     * @return bool
     */
    public function rightRight1()
    {
        return Carrier::getInstance()->getObjRights()->rightRight1($this->getSystemid());
    }

    /**
     * Returns the bool-value for the right2 of this record,
     * Systemid MUST be given, otherwise false
     *
     * @return bool
     */
    public function rightRight2()
    {
        return Carrier::getInstance()->getObjRights()->rightRight2($this->getSystemid());
    }

    /**
     * Returns the bool-value for the right3 of this record,
     * Systemid MUST be given, otherwise false
     *
     * @return bool
     */
    public function rightRight3()
    {
        return Carrier::getInstance()->getObjRights()->rightRight3($this->getSystemid());
    }

    /**
     * Returns the bool-value for the right4 of this record,
     * Systemid MUST be given, otherwise false
     *
     * @return bool
     */
    public function rightRight4()
    {
        return Carrier::getInstance()->getObjRights()->rightRight4($this->getSystemid());
    }

    /**
     * Returns the bool-value for the right5 of this record,
     * Systemid MUST be given, otherwise false
     *
     * @return bool
     */
    public function rightRight5()
    {
        return Carrier::getInstance()->getObjRights()->rightRight5($this->getSystemid());
    }

    /**
     * Returns the bool-value for the changelog permissions of this record,
     * Systemid MUST be given, otherwise false
     *
     * @return bool
     */
    public function rightChangelog()
    {
        return Carrier::getInstance()->getObjRights()->rightChangelog($this->getSystemid());
    }

    // --- SystemID & System-Table Methods ------------------------------------------------------------------

    /**
     * Fetches the number of siblings belonging to the passed systemid
     *
     * @param string $strSystemid
     * @param bool $bitUseCache
     *
     * @return int
     * @deprecated
     */
    public function getNumberOfSiblings($strSystemid = "", $bitUseCache = true)
    {
        if ($strSystemid == "") {
            $strSystemid = $this->getSystemid();
        }

        $strQuery = "SELECT COUNT(*)
                     FROM "._dbprefix_."system as sys1,
                          "._dbprefix_."system as sys2
                     WHERE sys1.system_id=?
                       AND sys2.system_prev_id = sys1.system_prev_id";
        $arrRow = $this->objDB->getPRow($strQuery, array($strSystemid), 0, $bitUseCache);
        return $arrRow["COUNT(*)"];

    }

    /**
     * Fetches the records placed as child nodes of the current / passed id.
     * <b> Only the IDs are fetched since the current object-context is not available!!! </b>
     *
     * @param string $strSystemid
     *
     * @return string[]
     * @deprecated
     */
    public function getChildNodesAsIdArray($strSystemid = "")
    {
        if ($strSystemid == "") {
            $strSystemid = $this->getSystemid();
        }

        $objORM = new OrmObjectlist();

        $strQuery = "SELECT system_id
                     FROM "._dbprefix_."system
                     WHERE system_prev_id=?
                       AND system_id != '0'
                       ".$objORM->getDeletedWhereRestriction()."
                     ORDER BY system_sort ASC";

        $arrReturn = array();
        $arrTemp = $this->objDB->getPArray($strQuery, array($strSystemid));

        if (count($arrTemp) > 0) {
            foreach ($arrTemp as $arrOneRow) {
                $arrReturn[] = $arrOneRow["system_id"];
            }
        }


        return $arrReturn;
    }

    /**
     * Fetches all child nodes recusrsively of the current / passed id.
     * <b> Only the IDs are fetched since the current object-context is not available!!! </b>
     *
     * @param string $strSystemid
     *
     * @return string[]
     * @deprecated
     */
    public function getAllSubChildNodesAsIdArray($strSystemid = "")
    {
        $arrReturn = $this->getChildNodesAsIdArray($strSystemid);
        if (count($arrReturn) > 0) {
            foreach ($arrReturn as $strId) {
                $arrReturn = array_merge($arrReturn, $this->getAllSubChildNodesAsIdArray($strId));
            }
        }
        return $arrReturn;
    }

    /**
     * Sets the Position of a SystemRecord in the currect level one position upwards or downwards
     *
     * @param string $strDirection upwards || downwards
     *
     * @throws Exception
     * @deprecated
     */
    public function setPosition($strDirection = "upwards")
    {
        if ($this->objSortManager !== null) {
            $this->objSortManager->setPosition($strDirection);
        }
        else {
            throw new Exception("Current instance of ".get_class($this)." is not sortable", Exception::$level_ERROR);
        }
    }

    /**
     * Sets the position of systemid using a given value.
     *
     * @param int $intNewPosition
     * @param array|bool $arrRestrictionModules If an array of module-ids is passed, the determination of siblings will be limited to the module-records matching one of the module-ids
     *
     * @throws Exception
     */
    public function setAbsolutePosition($intNewPosition, $arrRestrictionModules = false)
    {

        if ($this->objSortManager !== null) {
            $this->objSortManager->setAbsolutePosition($intNewPosition, $arrRestrictionModules);
        }
        else {
            throw new Exception("Current instance of ".get_class($this)." is not sortable", Exception::$level_ERROR);
        }
    }

    /**
     * Return a complete SystemRecord
     *
     * @param string $strSystemid
     *
     * @return mixed
     */
    public function getSystemRecord($strSystemid = "")
    {
        if ($strSystemid == "") {
            $strSystemid = $this->getSystemid();
        }
        $strQuery = "SELECT * FROM "._dbprefix_."system
                         LEFT JOIN "._dbprefix_."system_right
                              ON system_id = right_id
                         LEFT JOIN "._dbprefix_."system_date
                              ON system_id = system_date_id
                             WHERE system_id = ?";
        return $this->objDB->getPRow($strQuery, array($strSystemid));
    }

    /**
     * Returns the data for a registered module
     *
     * @param string $strName
     * @param bool $bitCache
     *
     * @return mixed
     * @deprecated
     * @see SystemModule::getPlainModuleData($strName, $bitCache)
     */
    public function getModuleData($strName, $bitCache = true)
    {
        return SystemModule::getPlainModuleData($strName, $bitCache);
    }

    /**
     * Deletes a record from the SystemTable
     *
     * @param string $strSystemid
     * @param bool $bitRight
     * @param bool $bitDate
     *
     * @return bool
     * @todo: remove first params, is always the current systemid. maybe mark as protected, currently only called by the test-classes
     *
     * * @todo find ussages and make private
     *
     */
    final public function deleteSystemRecord($strSystemid, $bitRight = true, $bitDate = true)
    {
        $bitResult = true;

        //Start a tx before deleting anything
        $this->objDB->transactionBegin();

        $strQuery = "DELETE FROM "._dbprefix_."system WHERE system_id = ?";
        $bitResult = $bitResult && $this->objDB->_pQuery($strQuery, array($strSystemid));

        if ($bitRight) {
            $strQuery = "DELETE FROM "._dbprefix_."system_right WHERE right_id = ?";
            $bitResult = $bitResult && $this->objDB->_pQuery($strQuery, array($strSystemid));
        }

        if ($bitDate) {
            $strQuery = "DELETE FROM "._dbprefix_."system_date WHERE system_date_id = ?";
            $bitResult = $bitResult && $this->objDB->_pQuery($strQuery, array($strSystemid));
        }


        //end tx
        if ($bitResult) {
            $this->objDB->transactionCommit();
            Logger::getInstance()->addLogRow("deleted system-record with id ".$strSystemid, Logger::$levelInfo);
        }
        else {
            $this->objDB->transactionRollback();
            Logger::getInstance()->addLogRow("deletion of system-record with id ".$strSystemid." failed", Logger::$levelWarning);
        }

        //flush the cache
        Carrier::getInstance()->getContainer()->offsetGet(ServiceProvider::STR_CACHE_MANAGER)->flushCache();

        return $bitResult;
    }


    /**
     * Deletes a record from the rights-table
     *
     * @param string $strSystemid
     *
     * @return bool
     */
    public function deleteRight($strSystemid)
    {
        $strQuery = "DELETE FROM "._dbprefix_."system_right WHERE right_id = ?";
        return $this->objDB->_pQuery($strQuery, array($strSystemid));
    }

    /**
     * Generates a sorted array of systemids, reaching from the passed systemid up
     * until the assigned module-id
     *
     * @param string $strSystemid
     * @param string $strStopSystemid
     *
     * @return mixed
     */
    public function getPathArray($strSystemid = "", $strStopSystemid = "0")
    {
        $arrReturn = array();

        if ($strSystemid == "") {
            $strSystemid = $this->getSystemid();
        }

        //loop over all parent-records
        $strTempId = $strSystemid;
        while ($strTempId != "0" && $strTempId != "" && $strTempId != -1 && $strTempId != $strStopSystemid) {
            $arrReturn[] = $strTempId;

            $objCommon = Objectfactory::getInstance()->getObject($strTempId);
            if ($objCommon === null) {
                break;
            }
            $strTempId = $objCommon->getPrevId();
        }

        $arrReturn = array_reverse($arrReturn);
        return $arrReturn;
    }

    /**
     * Returns a value from the $arrModule array.
     * If the requested key not exists, returns ""
     *
     * @param string $strKey
     *
     * @return string
     */
    public function getArrModule($strKey)
    {
        if (isset($this->arrModule[$strKey])) {
            return $this->arrModule[$strKey];
        }
        else {
            return "";
        }
    }



    // --- TextMethods --------------------------------------------------------------------------------------

    /**
     * Used to load a property.
     * If you want to provide a list of parameters but no module (automatic loading), pass
     * the parameters array as the second argument (an array). In this case the module is resolved
     * internally.
     *
     * @param string $strName
     * @param string|array $strModule Either the module name (if required) or an array of parameters
     * @param array $arrParameters
     *
     * @return string
     */
    public function getLang($strName, $strModule = "", $arrParameters = array())
    {
        if (is_array($strModule)) {
            $arrParameters = $strModule;
        }

        if ($strModule == "" || is_array($strModule)) {
            $strModule = $this->getArrModule("modul");
        }

        //Now we have to ask the Text-Object to return the text
        return $this->objLang->getLang($strName, $strModule, $arrParameters);
    }

    /**
     * Returns the current Text-Object Instance
     *
     * @return Lang
     */
    protected function getObjLang()
    {
        return $this->objLang;
    }


    // --- Portal-Language ------------------------------------------------------------------------------

    /**
     * Returns the language to display contents on the portal
     *
     * @return string
     */
    final public function getStrPortalLanguage()
    {
        $objLanguage = new LanguagesLanguage();
        return $objLanguage->getPortalLanguage();
    }


    // --- Admin-Language -------------------------------------------------------------------------------

    /**
     * Returns the language to display contents or to edit contents on adminside
     * NOTE: THIS ARE THE CONTENTS, NOT THE TEXTS
     *
     * @return string
     */
    final public function getStrAdminLanguageToWorkOn()
    {
        $objLanguage = new LanguagesLanguage();
        return $objLanguage->getAdminLanguage();
    }


    // --- GETTERS / SETTERS ----------------------------------------------------------------------------

    /**
     * Sets the current SystemID
     *
     * @param string $strID
     *
     * @return bool
     */
    public function setSystemid($strID)
    {
        if (validateSystemid($strID)) {
            $this->strSystemid = $strID;
            return true;
        }
        else {
            return false;
        }
    }

    /**
     * Resets the current systemid
     *
     * @return void
     */
    public function unsetSystemid()
    {
        $this->strSystemid = "";
    }

    /**
     * Returns the current SystemID
     *
     * @return string
     */
    public function getSystemid()
    {
        return $this->strSystemid;
    }

    /**
     * @return string
     */
    public function getStrSystemid()
    {
        return $this->strSystemid;
    }

    /**
     * @param string $strSystemid
     *
     * @return void
     */
    public function setStrSystemid($strSystemid)
    {
        if (validateSystemid($strSystemid)) {
            $this->strSystemid = $strSystemid;
        }
    }

    /**
     * Gets the Prev-ID of a record
     *
     * @param string $strSystemid
     *
     * @throws Exception
     * @return string
     */
    public function getPrevId($strSystemid = "")
    {
        if ($strSystemid != "") {
            throw new Exception("unsupported param @ ".__METHOD__, Exception::$level_FATALERROR);
        }

        return $this->getStrPrevId();
    }


    /**
     * @return string
     */
    public function getStrPrevId()
    {
        return $this->strPrevId;
    }


    /**
     * @param string $strPrevId
     *
     * @return void
     */
    public function setStrPrevId($strPrevId)
    {
        if (validateSystemid($strPrevId) || $strPrevId === "0") {
            $this->strPrevId = $strPrevId;
        }
    }

    /**
     * Gets the module id / module nr of a systemRecord
     *
     * @param string $strSystemid
     *
     * @throws Exception
     * @return int
     */
    public function getRecordModuleNr($strSystemid = "")
    {
        if ($strSystemid != "") {
            throw new Exception("unsupported param @ ".__METHOD__, Exception::$level_FATALERROR);
        }

        return $this->getIntModuleNr();
    }

    /**
     * @return int
     */
    public function getIntModuleNr()
    {
        return $this->intModuleNr;
    }

    /**
     * @param int $intModuleNr
     *
     * @return void
     */
    public function setIntModuleNr($intModuleNr)
    {
        $this->intModuleNr = $intModuleNr;
    }

    /**
     * @return int
     */
    public function getIntSort()
    {
        return $this->intSort;
    }

    /**
     * @param int $intSort
     *
     * @return void
     */
    public function setIntSort($intSort)
    {
        $this->intSort = $intSort;
    }

    /**
     * Returns the name of the user who last edited the record
     *
     * @param string $strSystemid
     *
     * @throws Exception
     * @return string
     */
    public function getLastEditUser($strSystemid = "")
    {
        if ($strSystemid != "") {
            throw new Exception("unsupported param @ ".__METHOD__, Exception::$level_FATALERROR);
        }

        if (validateSystemid($this->getStrLmUser())) {
            $objUser = Objectfactory::getInstance()->getObject($this->getStrLmUser());
            return $objUser->getStrDisplayName();
        }
        else {
            return "System";
        }
    }

    /**
     * @return string string
     */
    public function getStrLmUser()
    {
        return $this->strLmUser;
    }

    /**
     * Returns the id of the user who last edited the record
     *
     * @return string
     */
    public function getLastEditUserId()
    {
        return $this->getStrLmUser();
    }

    /**
     * @param string $strLmUser
     *
     * @return void
     */
    public function setStrLmUser($strLmUser)
    {
        $this->strLmUser = $strLmUser;
    }

    /**
     * @return int
     */
    public function getIntLmTime()
    {
        return $this->intLmTime;
    }

    /**
     * @param int $strLmTime
     *
     * @return void
     */
    public function setIntLmTime($strLmTime)
    {
        $this->intLmTime = $strLmTime;
    }

    /**
     * @return string
     */
    public function getStrLockId()
    {
        return $this->strLockId;
    }

    /**
     * @param string $strLockId
     *
     * @return void
     */
    public function setStrLockId($strLockId)
    {
        $this->strLockId = $strLockId;
    }

    /**
     * @return int
     */
    public function getIntLockTime()
    {
        return $this->intLockTime;
    }

    /**
     * @param int $intLockTime
     *
     * @return void
     */
    public function setIntLockTime($intLockTime)
    {
        $this->intLockTime = $intLockTime;
    }

    /**
     * @return int
     */
    public function getLongCreateDate()
    {
        return $this->longCreateDate;
    }

    /**
     * Returns the creation-date of the current record
     *
     * @param string $strSystemid
     *
     * @throws Exception
     * @return Date
     */
    public function getObjCreateDate($strSystemid = "")
    {
        if ($strSystemid != "") {
            throw new Exception("unsupported param @ ".__METHOD__, Exception::$level_FATALERROR);
        }

        return new Date($this->getLongCreateDate());
    }

    /**
     * @param int $longCreateDate
     *
     * @return void
     */
    public function setLongCreateDate($longCreateDate)
    {
        $this->longCreateDate = $longCreateDate;
    }

    /**
     * @return string
     */
    public function getStrOwner()
    {
        return $this->strOwner;
    }

    /**
     * @param string $strOwner
     *
     * @return void
     */
    public function setStrOwner($strOwner)
    {
        $this->strOwner = $strOwner;
    }

    /**
     * Gets the id of the user currently being the owner of the record
     *
     * @param string $strSystemid
     *
     * @throws Exception
     * @return string
     */
    public final function getOwnerId($strSystemid = "")
    {
        if ($strSystemid != "") {
            throw new Exception("unsupported param @ ".__METHOD__, Exception::$level_FATALERROR);
        }

        return $this->getStrOwner();
    }

    /**
     * Sets the id of the user who owns this record.
     * Please note that since 4.4, setting an owner-id no longer fires an updateObjectToDb()!
     *
     * @param string $strOwner
     * @param string $strSystemid
     *
     * @deprecated
     *
     * @throws Exception
     * @return bool
     */
    final public function setOwnerId($strOwner, $strSystemid = "")
    {
        if ($strSystemid != "") {
            throw new Exception("unsupported param @ ".__METHOD__, Exception::$level_FATALERROR);
        }

        $this->setStrOwner($strOwner);
        return true;
    }

    /**
     * @return int
     */
    public function getIntRecordStatus()
    {
        return (int)$this->intRecordStatus;
    }

    /**
     * @return int
     */
    public function getIntRecordDeleted()
    {
        return (int)$this->intRecordDeleted;
    }

    /**
     * Gets the status of a systemRecord
     *
     * @param string $strSystemid
     *
     * @throws Exception
     * @return int
     * @deprecated use Root::getIntRecordStatus() instead
     * @see Root::getIntRecordStatus()
     */
    public function getStatus($strSystemid = "")
    {
        if ($strSystemid != "") {
            throw new Exception("unsupported param @ ".__METHOD__, Exception::$level_FATALERROR);
        }

        return $this->getIntRecordStatus();
    }


    /**
     * Sets the internal status. Fires a status-changed event.
     *
     * @param int $intRecordStatus
     */
    public function setIntRecordStatus($intRecordStatus)
    {
        $this->intRecordStatus = $intRecordStatus;
    }

    /**
     * Gets comment saved with the record
     *
     * @param string $strSystemid
     *
     * @throws Exception
     * @return string
     */
    public function getRecordComment($strSystemid = "")
    {
        if ($strSystemid != "") {
            throw new Exception("unsupported param @ ".__METHOD__, Exception::$level_FATALERROR);
        }

        return $this->getStrRecordComment();
    }


    /**
     * @return string
     */
    public function getStrRecordComment()
    {
        return $this->strRecordComment;
    }

    /**
     * @param string $strRecordComment
     *
     * @return void
     */
    public function setStrRecordComment($strRecordComment)
    {
        if (uniStrlen($strRecordComment) > 254) {
            $strRecordComment = uniStrTrim($strRecordComment, 250);
        }
        $this->strRecordComment = $strRecordComment;
    }

    /**
     * @param string $strRecordClass
     *
     * @return void
     */
    public function setStrRecordClass($strRecordClass)
    {
        $this->strRecordClass = $strRecordClass;
    }

    /**
     * @return string
     * @return void
     */
    public function getStrRecordClass()
    {
        return $this->strRecordClass;
    }


    /**
     * Writes a value to the params-array
     *
     * @param string $strKey
     * @param mixed $mixedValue Value
     *
     * @return void
     */
    public function setParam($strKey, $mixedValue)
    {
        Carrier::getInstance()->setParam($strKey, $mixedValue);
    }

    /**
     * Returns a value from the params-Array
     *
     * @param string $strKey
     *
     * @return string else ""
     */
    public function getParam($strKey)
    {
        return Carrier::getInstance()->getParam($strKey);
    }

    /**
     * Returns the complete Params-Array
     *
     * @return mixed
     */
    public final function getAllParams()
    {
        return Carrier::getAllParams();
    }

    /**
     * returns the action used for the current request
     *
     * @return string
     */
    public final function getAction()
    {
        return (string)$this->strAction;
    }

    /**
     * Returns an instance of the lockmanager, initialized
     * with the current systemid.
     *
     * @return Lockmanager
     */
    public function getLockManager()
    {
        return new Lockmanager($this->getSystemid(), $this);
    }


    /**
     * Writes a key-value-pair to the arrModule
     *
     * @param string $strKey
     * @param mixed $strValue
     *
     * @return void
     */
    public function setArrModuleEntry($strKey, $strValue)
    {
        $this->arrModule[$strKey] = $strValue;
    }

    /**
     * Use this method to set an array of values, e.g. fetched by your own init-method.
     * If given, the root-class uses this array to set the internal fields instead of
     * triggering another query to the database.
     * On high-performance systems or large object-nets, this could reduce the amount of database-queries
     * fired drastically.
     * For best performance, include the matching row of the tables system, system_date and system_rights
     *
     * @param array $arrInitRow
     *
     * @return void
     */
    public function setArrInitRow($arrInitRow)
    {
        if (isset($arrInitRow["system_id"])) {
            $this->arrInitRow = $arrInitRow;
        }
    }

    /**
     * Returns the set of internal values marked as init-values
     *
     * @return null|array
     */
    public function getArrInitRow()
    {
        return $this->arrInitRow;
    }

    /**
     * @param Date $objEndDate
     *
     * @return void
     */
    public function setObjEndDate($objEndDate = null)
    {

        if ($objEndDate === 0 || $objEndDate === "0") {
            $objEndDate = null;
        }


        if (!$objEndDate instanceof Date && $objEndDate != "" && $objEndDate != null) {
            $objEndDate = new Date($objEndDate);
        }

        $this->objEndDate = $objEndDate;
        $this->bitDatesChanges = true;
    }

    /**
     * @return Date
     */
    public function getObjEndDate()
    {
        return $this->objEndDate;
    }

    /**
     * @param Date $objSpecialDate
     *
     * @return void
     */
    public function setObjSpecialDate($objSpecialDate = null)
    {

        if ($objSpecialDate === 0 || $objSpecialDate === "0") {
            $objSpecialDate = null;
        }

        if (!$objSpecialDate instanceof Date && $objSpecialDate != "" && $objSpecialDate != null) {
            $objSpecialDate = new Date($objSpecialDate);
        }

        $this->objSpecialDate = $objSpecialDate;
        $this->bitDatesChanges = true;
    }

    /**
     * @return Date
     */
    public function getObjSpecialDate()
    {
        return $this->objSpecialDate;
    }

    /**
     * @param Date $objStartDate
     *
     * @return void
     */
    public function setObjStartDate($objStartDate = null)
    {

        if ($objStartDate === 0 || $objStartDate === "0") {
            $objStartDate = null;
        }

        if (!$objStartDate instanceof Date && $objStartDate != "" && $objStartDate != null) {
            $objStartDate = new Date($objStartDate);
        }

        $this->bitDatesChanges = true;
        $this->objStartDate = $objStartDate;
    }

    /**
     * @return Date
     */
    public function getObjStartDate()
    {
        return $this->objStartDate;
    }

}
