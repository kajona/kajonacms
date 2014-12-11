<?php
/*"******************************************************************************************************
*   (c) 2004-2006 by MulchProductions, www.mulchprod.de                                                 *
*   (c) 2007-2014 by Kajona, www.kajona.de                                                              *
*       Published under the GNU LGPL v2.1, see /system/licence_lgpl.txt                                 *
*-------------------------------------------------------------------------------------------------------*
*   $Id$                                              *
********************************************************************************************************/

/**
 * The top-level class for models, installers and top-level files
 * An instance of this class is used by the admin & portal object to invoke common database based methods.
 * Change with care!
 *
 * @package module_system
 * @author sidler@mulchprod.de
 */
abstract class class_root {

    const STR_MODULE_ANNOTATION = "@module";
    const STR_MODULEID_ANNOTATION = "@moduleId";


    /**
     * Instance of class_config
     *
     * @var class_config
     */
    protected $objConfig = null; //Object containing config-data
    /**
     * Instance of class_db
     *
     * @var class_db
     */
    protected $objDB = null; //Object to the database
    /**
     * Instance of class_session
     *
     * @var class_session
     */
    protected $objSession = null; //Object containing the session-management
    /**
     * Instance of class_rights
     *
     * @var class_rights
     */
    protected $objRights = null; //Object handling the right-stuff

    /**
     * Instance of class_lang
     *
     * @var class_lang
     */
    private $objLang = null; //Object managing the langfiles

    /**
     * @var interface_sortmanager
     */
    protected $objSortManager = null;

    private $strAction; //current action to perform (GET/POST)
    protected $arrModule = array(); //Array containing information about the current module


    private $arrInitRow = null;             //array to be used when loading details from the database. could reduce the amount of queries if populated.

    //--- fields to be synchronized with the database ---

    /**
     * The records current systemid
     * @var string
     * @templateExport
     */
    private $strSystemid = "";

    /**
     * The records internal parent-id
     * @var string
     * @versionable
     */
    private $strPrevId = -1;

    /**
     * The old prev-id, used to track hierarchical changes -> requires a rebuild of the rights-table
     * @var string
     */
    private $strOldPrevId = -1;

    /**
     * The records module-number
     * @var int
     */
    private $intModuleNr = 0;

    /**
     * The records sort-position relative to the parent record
     * @var int
     */
    private $intSort = -1;

    /**
     * The id of the user who created the record initially
     * @var string
     * @versionable
     * @templateExport
     * @templateMapper user
     */
    private $strOwner = "";

    /**
     * The id of the user last who did the last changes to the current record
     * @var string
     */
    private $strLmUser = "";

    /**
     * Timestamp of the last modification
     * ATTENTION: time() based, so 32 bit integer
     * @todo migrate to long-timestamp
     * @var int
     * @templateExport
     * @templateMapper datetime
     */
    private $intLmTime = 0;

    /**
     * The id of the user locking the current record, emtpy otherwise
     * @var string
     */
    private $strLockId = "";

    /**
     * Time the current locking was triggered
     * ATTENTION: time() based, so 32 bit integer
     * @todo migrate to long-timestamp
     * @var int
     */
    private $intLockTime = 0;

    /**
     * The records status
     * @var int
     * @versionable
     */
    private $intRecordStatus = 1;

    /**
     * Human readable comment describing the current record
     * @var string
     */
    private $strRecordComment = "";

    /**
     * Holds the current objects' class
     * @var string
     */
    private $strRecordClass = "";

    /**
     * Long-based representation of the timestamp the record was created initially
     * @var int
     * @templateExport
     * @templateMapper datetime
     */
    private $longCreateDate = 0;

    /**
     * The start-date of the date-table
     * @var class_date
     * @versionable
     * @templateExport
     * @templateMapper datetime
     */
    private $objStartDate = null;

    /**
     * The end date of the date-table
     * @var class_date
     * @versionable
     * @templateExport
     * @templateMapper datetime
     */
    private $objEndDate = null;

    /**
     * The special-date of the date-table
     * @var class_date
     * @versionable
     * @templateExport
     * @templateMapper datetime
     */
    private $objSpecialDate = null;

    private $bitDatesChanges = false;


    /**
     * Constructor
     *
     * @param string $strSystemid
     *
     * @return class_root
     */
    public function __construct($strSystemid = "") {

        //Generating all the needed objects. For this we use our cool cool carrier-object
        //take care of loading just the necessary objects
        $objCarrier = class_carrier::getInstance();
        $this->objConfig = $objCarrier->getObjConfig();
        $this->objDB = $objCarrier->getObjDB();
        $this->objSession = $objCarrier->getObjSession();
        $this->objLang = $objCarrier->getObjLang();
        $this->objRights = $objCarrier->getObjRights();

        $this->objSortManager = new class_common_sortmanager($this);

        //And keep the action
        $this->strAction = $this->getParam("action");

        $this->strSystemid = $strSystemid;

        $this->setStrRecordClass(get_class($this));

        //try to load the current module-name and the moduleId by reflection
        $objReflection = new class_reflection($this);
        if(!isset($this->arrModule["modul"])) {
            $arrAnnotationValues = $objReflection->getAnnotationValuesFromClass(self::STR_MODULE_ANNOTATION);
            if(count($arrAnnotationValues) > 0) {
                $this->setArrModuleEntry("modul", trim($arrAnnotationValues[0]));
                $this->setArrModuleEntry("module", trim($arrAnnotationValues[0]));
            }
        }

        if(!isset($this->arrModule["moduleId"])) {
            $arrAnnotationValues = $objReflection->getAnnotationValuesFromClass(self::STR_MODULEID_ANNOTATION);
            if(count($arrAnnotationValues) > 0) {
                $this->setArrModuleEntry("moduleId", constant(trim($arrAnnotationValues[0])));
                $this->setIntModuleNr(constant(trim($arrAnnotationValues[0])));
            }
        }

        if($strSystemid != "") {
            $this->initObject();
        }
    }


    /**
     * Method to invoke object initialization.
     * In nearly all cases, this is triggered by the framework itself.
     * @return void
     */
    public final function initObject() {
        $this->initObjectInternal();
        $this->internalInit();

        //if given, read versioning information
        if($this instanceof interface_versionable) {
            $objChangelog = new class_module_system_changelog();
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
     * @return void
     */
    protected function initObjectInternal() {
        $objORM = new class_orm_objectinit($this);
        $objORM->initObjectFromDb();
    }

    /**
     * Init the current record with the system-fields
     * @return void
     */
    private final function internalInit() {

        if(validateSystemid($this->getSystemid())) {

            if(is_array($this->arrInitRow)) {
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


            if(count($arrRow) > 3) {
                //$this->setStrSystemid($arrRow["system_id"]);
                $this->strPrevId =$arrRow["system_prev_id"];
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
                if(isset($arrRow["system_class"]))
                    $this->strRecordClass = $arrRow["system_class"];

                $this->strOldPrevId = $this->strPrevId;

                if($arrRow["system_date_start"] > 0)
                    $this->objStartDate = new class_date($arrRow["system_date_start"]);

                if($arrRow["system_date_end"] > 0)
                    $this->objEndDate = new class_date($arrRow["system_date_end"]);

                if($arrRow["system_date_special"] > 0)
                    $this->objSpecialDate = new class_date($arrRow["system_date_special"]);

            }

            $this->bitDatesChanges = false;
        }
    }


    /**
     * A generic approach to count the number of object currently available.
     * This method is only a simple approach to determine the number of instances in the
     * database, if you need more specific counts, overwrite this method or add your own
     * implementation to the derived class.
     *
     * @param string $strPrevid
     *
     * @return int
     */
    public static function getObjectCount($strPrevid = "") {
        $objORM = new class_orm_objectlist();
        return $objORM->getObjectCount(get_called_class(), $strPrevid);
    }

    /**
     * A generic approach to load a list of objects currently available.
     * This method is only a simple approach to determine the instances in the
     * database, if you need more specific loaders, overwrite this method or add your own
     * implementation to the derived class.
     *
     * @param string $strPrevid
     * @param null|int $intStart
     * @param null|int $intEnd
     *
     * @return self[]
     */
    public static function getObjectList($strPrevid = "", $intStart = null, $intEnd = null) {
        $objORM = new class_orm_objectlist();
        return $objORM->getObjectList(get_called_class(), $strPrevid, $intStart, $intEnd);
    }

    /**
     * Deletes the current object from the system.
     * By default, all entries are delete from  all tables indicated by the class-doccomment.
     * If you want to trigger additional deletes, overwrite this method.
     * The system-record itself is being deleted automatically, too.
     * @return bool
     */
    protected function deleteObjectInternal() {
        $bitReturn = true;

        if($this instanceof interface_versionable) {
            $objChanges = new class_module_system_changelog();
            $objChanges->createLogEntry($this, class_module_system_changelog::$STR_ACTION_DELETE);
        }

        $objAnnotations = new class_reflection($this);
        $arrTargetTables = $objAnnotations->getAnnotationValuesFromClass("@targetTable");

        if(count($arrTargetTables) > 0) {
            foreach($arrTargetTables as  $strOneTable) {
                $arrSingleTable = explode(".", $strOneTable);
                $strQuery = "DELETE FROM ".$this->objDB->encloseTableName(_dbprefix_.$arrSingleTable[0])."
                                   WHERE ".$this->objDB->encloseColumnName($arrSingleTable[1])." = ? ";

                $bitReturn = $bitReturn && $this->objDB->_pQuery($strQuery, array($this->getSystemid()));
            }
        }
        return $bitReturn;
    }

    /**
     * Removes the current object from the system.
     *
     * @throws class_exception
     * @return bool
     */
    public function deleteObject() {

        if(!$this->getLockManager()->isAccessibleForCurrentUser())
            return false;

        if(!$this instanceof interface_model)
            throw new class_exception("delete operation required interface_model to be implemented", class_exception::$level_ERROR);

        if($this instanceof interface_versionable) {
            $objChanges = new class_module_system_changelog();
            $objChanges->createLogEntry($this, class_module_system_changelog::$STR_ACTION_DELETE);
        }

        /** @var $this class_root|interface_model */
        $this->objDB->transactionBegin();

        //validate, if there are subrecords, so child nodes to be deleted
        $arrChilds = $this->objDB->getPArray("SELECT system_id FROM "._dbprefix_."system where system_prev_id = ?", array($this->getSystemid()));
        foreach($arrChilds as $arrOneChild) {
            if(validateSystemid($arrOneChild["system_id"])) {
                $objInstance = class_objectfactory::getInstance()->getObject($arrOneChild["system_id"]);
                if($objInstance !== null)
                    $objInstance->deleteObject();
            }
        }

        $bitReturn = $this->deleteObjectInternal();
        $bitReturn = $bitReturn && $this->deleteSystemRecord($this->getSystemid());

        if($bitReturn) {
            class_logger::getInstance()->addLogRow("successfully deleted record ".$this->getSystemid()." / ".$this->getStrDisplayName(), class_logger::$levelInfo);
            $this->objDB->transactionCommit();
            $this->objDB->flushQueryCache();
            return true;
        }
        else {
            class_logger::getInstance()->addLogRow("error deleting record ".$this->getSystemid()." / ".$this->getStrDisplayName(), class_logger::$levelInfo);
            $this->objDB->transactionRollback();
            $this->objDB->flushQueryCache();
            return false;
        }
    }

    // --- DATABASE-SYNCHRONIZATION -------------------------------------------------------------------------


    /**
     * Saves the current object to the database. Determines, whether the current object has to be inserted
     * or updated to the database.
     * In case of an update, the objects' updateStateToDb() method is being called (as required by class_model).
     * In the case of a new object, a blank record is being created. Therefore, all tables returned by class' doc comment
     * will be filled with a new record (using the same new systemid as the primary key).
     * The newly created systemid is being set as the current objects' one and can be used in the afterwards
     * called updateStateToDb() method to reference the correct rows.
     *
     * @param string|bool $strPrevId The prev-id of the records, either to be used for the insert or to be used during the update of the record
     * @return bool
     * @since 3.3.0
     * @throws class_exception
     * @see interface_model
     */
    public function updateObjectToDb($strPrevId = false) {
        $bitCommit = true;
        /** @var $this class_root|interface_model */
        if(!$this instanceof interface_model)
            throw new class_exception("current object must implemented interface_model", class_exception::$level_FATALERROR);

        if(!$this->getLockManager()->isAccessibleForCurrentUser()) {
            $objUser  = new class_module_user_user($this->getLockManager()->getLockId());
            throw new class_exception("current object is locked by user ".$objUser->getStrDisplayName(), class_exception::$level_ERROR);
        }

        if(is_object($strPrevId) && $strPrevId instanceof class_root)
            $strPrevId = $strPrevId->getSystemid();

        $this->objDB->transactionBegin();

        //current systemid given? if not, create a new record.
        if(!validateSystemid($this->getSystemid())) {

            if($strPrevId === false || $strPrevId === "" || $strPrevId === null) {
                //try to find the current modules-one
                if(isset($this->arrModule["modul"])) {
                    $strPrevId = class_module_system_module::getModuleByName($this->getArrModule("modul"), true)->getSystemid();
                    if(!validateSystemid($strPrevId))
                        throw new class_exception("automatic determination of module-id failed ", class_exception::$level_FATALERROR);
                }
                else
                    throw new class_exception("insert with no previd ", class_exception::$level_FATALERROR);
            }

            if(!validateSystemid($strPrevId) && $strPrevId !== "0") {
                throw new class_exception("insert with erroneous prev-id ", class_exception::$level_FATALERROR);
            }

            //create the new systemrecord
            //store date-bit temporary
            $bitDates = $this->bitDatesChanges;
            $this->createSystemRecord($strPrevId, $this->getStrDisplayName());
            $this->bitDatesChanges = $bitDates;

            if(validateSystemid($this->getStrSystemid())) {

                //Create the foreign records
                $objAnnotations = new class_reflection($this);
                $arrTargetTables = $objAnnotations->getAnnotationValuesFromClass("@targetTable");
                if(count($arrTargetTables) > 0) {
                    foreach($arrTargetTables as $strOneConfig) {
                        $arrSingleTable = explode(".", $strOneConfig);
                        $strQuery = "INSERT INTO ".$this->objDB->encloseTableName(_dbprefix_.$arrSingleTable[0])."
                                                (".$this->objDB->encloseColumnName($arrSingleTable[1]).") VALUES
                                                (?) ";

                        if(!$this->objDB->_pQuery($strQuery, array($this->getStrSystemid())))
                            $bitCommit = false;
                    }
                }

                if(!$this->onInsertToDb())
                    $bitCommit = false;

            }
            else
                throw new class_exception("creation of systemrecord failed", class_exception::$level_FATALERROR);

            //all updates are done, start the "real" update
            class_carrier::getInstance()->flushCache(class_carrier::INT_CACHE_TYPE_DBQUERIES);
        }

        //new prev-id?
        if($strPrevId !== false && $this->getSystemid() != $strPrevId && (validateSystemid($strPrevId) || $strPrevId == "0")) {
            //validate the new prev id - it is not allowed to set a parent-node as a sub-node of its own child
            if(!$this->isSystemidChildNode($this->getSystemid(), $strPrevId))
                $this->setStrPrevId($strPrevId);
        }

        //new comment?
        $this->setStrRecordComment($this->getStrDisplayName());

        //save back to the database
        $bitCommit = $bitCommit & $this->updateSystemrecord();

        //update ourselves to the database
        if(!$this->updateStateToDb())
            $bitCommit = false;

        if($bitCommit) {
            $this->objDB->transactionCommit();
            //unlock the record
            $this->getLockManager()->unlockRecord();
            $bitReturn = true;
            class_logger::getInstance()->addLogRow("updateObjectToDb() succeeded for systemid ".$this->getSystemid()." (".$this->getRecordComment().")", class_logger::$levelInfo);
        }
        else {
            $this->objDB->transactionRollback();
            $bitReturn = false;
            class_logger::getInstance()->addLogRow("updateObjectToDb() failed for systemid ".$this->getSystemid()." (".$this->getRecordComment().")", class_logger::$levelWarning);
        }


        //call the recordUpdated-Listeners
        //TODO: remove legacy call
        class_core_eventdispatcher::notifyRecordUpdatedListeners($this);
        class_core_eventdispatcher::getInstance()->notifyGenericListeners(class_system_eventidentifier::EVENT_SYSTEM_RECORDUPDATED, array($this));

        class_carrier::getInstance()->flushCache(class_carrier::INT_CACHE_TYPE_DBQUERIES);
        return $bitReturn;
    }


    /**
     * A default implementation for copy-operations.
     * Overwrite this method if you want to execute additional statements.
     * Please be aware that you are working on the new object afterwards!
     *
     * @param string $strNewPrevid
     * @param bool $bitChangeTitle
     *
     * @throws class_exception
     * @return bool
     */
    public function copyObject($strNewPrevid = "", $bitChangeTitle = true) {

        $this->objDB->transactionBegin();

        $strOldSysid = $this->getSystemid();

        if($strNewPrevid == "")
            $strNewPrevid = $this->strPrevId;

        //any date-objects to copy?
        if($this->objStartDate != null || $this->objEndDate != null || $this->objSpecialDate != null)
            $this->bitDatesChanges = true;

        //check if there's a title field, in most cases that could be used to change the title
        if($bitChangeTitle) {
            $objReflection = new class_reflection($this);
            $strGetter = $objReflection->getGetter("strTitle");
            $strSetter = $objReflection->getSetter("strTitle");
            if($strGetter != null && $strSetter != null) {
                $strTitle = call_user_func(array($this, $strGetter));
                if($strTitle != "") {
                    call_user_func(array($this, $strSetter), $strTitle."_copy");
                }
            }
        }

        //prepare the current object
        $this->unsetSystemid();
        $this->arrInitRow = null;
        $bitReturn = $this->updateObjectToDb($strNewPrevid);
        //call event listeners
        //TODO: remove legacy call
        $bitReturn = $bitReturn && class_core_eventdispatcher::notifyRecordCopiedListeners($strOldSysid, $this->getSystemid());
        $bitReturn = $bitReturn && class_core_eventdispatcher::getInstance()->notifyGenericListeners(class_system_eventidentifier::EVENT_SYSTEM_RECORDCOPIED, array($strOldSysid, $this->getSystemid(), $this));


        //process subrecords
        //validate, if there are subrecords, so child nodes to be copied to the current record
        $arrChilds = $this->objDB->getPArray("SELECT system_id FROM "._dbprefix_."system where system_prev_id = ? ORDER BY system_sort ASC", array($strOldSysid));
        foreach($arrChilds as $arrOneChild) {
            if(validateSystemid($arrOneChild["system_id"])) {
                $objInstance = class_objectfactory::getInstance()->getObject($arrOneChild["system_id"]);
                if($objInstance !== null)
                    $objInstance->copyObject($this->getSystemid(), false);
            }
        }


        if($bitReturn)
            $this->objDB->transactionCommit();
        else
            $this->objDB->transactionRollback();

        $this->objDB->flushQueryCache();


        $bitReturn = $bitReturn && class_core_eventdispatcher::getInstance()->notifyGenericListeners(class_system_eventidentifier::EVENT_SYSTEM_RECORDCOPYFINISHED, array($strOldSysid, $this->getSystemid(), $this));

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
    private function isSystemidChildNode($strBaseId, $strChildId) {

        while(validateSystemid($strChildId)) {
            $objCommon = new class_module_system_common($strChildId);
            if($objCommon->getSystemid() == $strBaseId)
                return true;
            else
                return $this->isSystemidChildNode($strBaseId, $objCommon->getPrevId());
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
     * @throws class_exception
     * @return bool
     */
    protected function updateStateToDb() {
        $objORMMapper = new class_orm_objectupdate($this);
        return $objORMMapper->updateStateToDb();
    }

    /**
     * Overwrite this method if you want to trigger additional commands during the insert
     * of an object, e.g. to create additional objects / relations
     *
     * @return bool
     */
    protected function onInsertToDb() {
        return true;
    }

    /**
     * Updates the current record to the database and saves all relevant fields.
     * Please note that this method is triggered internally.
     *
     * @return bool
     * @final
     * @since 3.4.1
     */
    protected final function updateSystemrecord() {

        if(!validateSystemid($this->getSystemid()))
            return true;

        class_logger::getInstance()->addLogRow("updated systemrecord ".$this->getStrSystemid()." data", class_logger::$levelInfo);

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


        if($this->bitDatesChanges) {
            $this->processDateChanges();
        }

        $this->objDB->flushQueryCache();

        if($this->strOldPrevId != $this->strPrevId) {
            class_carrier::getInstance()->flushCache(class_carrier::INT_CACHE_TYPE_DBQUERIES | class_carrier::INT_CACHE_TYPE_ORMCACHE);
            $this->objRights->rebuildRightsStructure($this->getSystemid());
            $this->objSortManager->fixSortOnPrevIdChange($this->strOldPrevId, $this->strPrevId);
            //TODO: remove legacy call
            class_core_eventdispatcher::notifyPrevidChangedListeners($this->getSystemid(), $this->strOldPrevId, $this->strPrevId);
            class_core_eventdispatcher::getInstance()->notifyGenericListeners(class_system_eventidentifier::EVENT_SYSTEM_PREVIDCHANGED, array($this->getSystemid(), $this->strOldPrevId, $this->strPrevId));
            $this->strOldPrevId = $this->strPrevId;
        }

        return $bitReturn;
    }


    /**
     * Generates a new SystemRecord and, if needed, the corresponding record in the rights-table (here inheritance is default)
     * Returns the systemID used for this record
     *
     * @param string $strPrevId    Previous ID in the tree-structure
     * @param string $strComment Comment to identify the record
     * @param bool $bitRight Should the right-record be generated?
     * @return string The ID used/generated
     */
    public function createSystemRecord($strPrevId, $strComment, $bitRight = true) {

        $strSystemId = generateSystemid();

        $this->setStrSystemid($strSystemId);

        //Correct prevID
        if($strPrevId == "")
            $strPrevId = 0;

        $this->setStrPrevId($strPrevId);

        //determine the correct new sort-id - append by default
        $strQuery = "SELECT COUNT(*) FROM "._dbprefix_."system WHERE system_prev_id = ? AND system_id != '0'";
        $arrRow = $this->objDB->getPRow($strQuery, array($strPrevId), 0, false);
        $intSiblings = $arrRow["COUNT(*)"];

        $strComment = uniStrTrim(strip_tags($strComment), 240);

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
                class_date::getCurrentTimestamp(),
                $this->objSession->getUserID(),
                time(),
                (int)$this->getIntRecordStatus(),
                $strComment,
                (int)($intSiblings+1),
                $this->getStrRecordClass()
            )
        );

        //Do we need a Rights-Record?
        if($bitRight) {
            $strQuery = "INSERT INTO "._dbprefix_."system_right (right_id, right_inherit) VALUES (?, 1)";

            $this->objDB->_pQuery($strQuery, array($strSystemId));
            //update rights to inherit
            $this->objRights->setInherited(true, $strSystemId);
        }

        class_logger::getInstance()->addLogRow("new system-record created: ".$strSystemId ." (".$strComment.")", class_logger::$levelInfo);

        $this->objDB->flushQueryCache();
        $this->internalInit();
        //reset the old previd since we're having a new record
        $this->strOldPrevId = -1;

        return $strSystemId;

    }

    /**
     * Process date changes handles the insert and update of date-objects.
     * It replaces the old createDate and updateDate methods
     * @return bool
     */
    private function processDateChanges() {

        $intStart = 0;
        $intEnd = 0;
        $intSpecial = 0;

        if($this->objStartDate != null && $this->objStartDate instanceof class_date)
            $intStart = $this->objStartDate->getLongTimestamp();

        if($this->objEndDate != null && $this->objEndDate instanceof class_date)
            $intEnd = $this->objEndDate->getLongTimestamp();

        if($this->objSpecialDate != null && $this->objSpecialDate instanceof class_date)
            $intSpecial = $this->objSpecialDate->getLongTimestamp();

        $strQuery = "SELECT COUNT(*) FROM "._dbprefix_."system_date WHERE system_date_id = ?";
        $arrRow = $this->objDB->getPRow($strQuery, array($this->getSystemid()));
        if($arrRow["COUNT(*)"] == 0) {
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
     * Up from Kajona V3.3, the signature changed. Pass instances of class_date instead of
     * int-values.
     *
     * @param string $strSystemid
     * @param class_date $objStartDate
     * @param class_date $objEndDate
     * @param class_date $objSpecialDate
     * @deprecated use the internal date-objects to have all dates handled automatically
     * @return bool
     */
    public function createDateRecord($strSystemid, class_date $objStartDate = null, class_date $objEndDate = null, class_date $objSpecialDate = null) {
        $intStart = 0;
        $intEnd = 0;
        $intSpecial = 0;

        if($objStartDate != null && $objStartDate instanceof class_date)
            $intStart = $objStartDate->getLongTimestamp();

        if($objEndDate != null && $objEndDate instanceof class_date)
            $intEnd = $objEndDate->getLongTimestamp();

        if($objSpecialDate != null && $objSpecialDate instanceof class_date)
            $intSpecial = $objSpecialDate->getLongTimestamp();

        $strQuery = "INSERT INTO "._dbprefix_."system_date
                      (system_date_id, system_date_start, system_date_end, system_date_special) VALUES
                      (?, ?, ?, ?)";
        return $this->objDB->_pQuery($strQuery, array($strSystemid, $intStart, $intEnd, $intSpecial));
    }

    /**
     * Updates a record in the date table. Make sure to use a proper system-id!
     * Up from Kajona V3.3, the signature changed. Pass instances of class_date instead of
     * int-values.
     *
     * @param string $strSystemid
     * @param class_date $objStartDate
     * @param class_date $objEndDate
     * @param class_date $objSpecialDate
     * @deprecated use the internal date-objects to have all dates handled automatically
     * @return bool
     */
    public function updateDateRecord($strSystemid, class_date $objStartDate = null, class_date $objEndDate = null, class_date $objSpecialDate = null) {
        $intStart = 0;
        $intEnd = 0;
        $intSpecial = 0;

        if($objStartDate != null && $objStartDate instanceof class_date)
            $intStart = $objStartDate->getLongTimestamp();

        if($objEndDate != null && $objEndDate instanceof class_date)
            $intEnd = $objEndDate->getLongTimestamp();

        if($objSpecialDate != null && $objSpecialDate instanceof class_date)
            $intSpecial = $objSpecialDate->getLongTimestamp();

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
    public function rightView() {
        return $this->objRights->rightView($this->getSystemid());
    }

    /**
     * Returns the bool-value for the right to edit this record,
     * Systemid MUST be given, otherwise false
     *
     * @return bool
     */
    public function rightEdit() {
        return $this->objRights->rightEdit($this->getSystemid());
    }

    /**
     * Returns the bool-value for the right to delete this record,
     * Systemid MUST be given, otherwise false
     *
     * @return bool
     */
    public function rightDelete() {
        return $this->objRights->rightDelete($this->getSystemid());
    }

    /**
     * Returns the bool-value for the right to change rights of this record,
     * Systemid MUST be given, otherwise false
     *
     * @return bool
     */
    public function rightRight() {
        return $this->objRights->rightRight($this->getSystemid());
    }

    /**
     * Returns the bool-value for the right1 of this record,
     * Systemid MUST be given, otherwise false
     *
     * @return bool
     */
    public function rightRight1() {
        return $this->objRights->rightRight1($this->getSystemid());
    }

    /**
     * Returns the bool-value for the right2 of this record,
     * Systemid MUST be given, otherwise false
     *
     * @return bool
     */
    public function rightRight2() {
        return $this->objRights->rightRight2($this->getSystemid());
    }

    /**
     * Returns the bool-value for the right3 of this record,
     * Systemid MUST be given, otherwise false
     *
     * @return bool
     */
    public function rightRight3() {
        return $this->objRights->rightRight3($this->getSystemid());
    }

    /**
     * Returns the bool-value for the right4 of this record,
     * Systemid MUST be given, otherwise false
     *
     * @return bool
     */
    public function rightRight4() {
        return $this->objRights->rightRight4($this->getSystemid());
    }

    /**
     * Returns the bool-value for the right5 of this record,
     * Systemid MUST be given, otherwise false
     *
     * @return bool
     */
    public function rightRight5() {
        return $this->objRights->rightRight5($this->getSystemid());
    }

    /**
     * Returns the bool-value for the right5 of this record,
     * Systemid MUST be given, otherwise false
     *
     * @return bool
     */
    public function rightChangelog() {
        return $this->objRights->rightChangelog($this->getSystemid());
    }

    // --- SystemID & System-Table Methods ------------------------------------------------------------------

    /**
     * Fetches the number of siblings belonging to the passed systemid
     *
     * @param string $strSystemid
     * @param bool $bitUseCache
     * @return int
     */
    public function getNumberOfSiblings($strSystemid = "", $bitUseCache = true) {
        if($strSystemid == "")
            $strSystemid = $this->getSystemid();

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
     * @return string[]
     */
    public function getChildNodesAsIdArray($strSystemid = "") {
        if($strSystemid == "")
            $strSystemid = $this->getSystemid();

        $strQuery = "SELECT system_id
                     FROM "._dbprefix_."system
                     WHERE system_prev_id=?
                       AND system_id != '0'
                     ORDER BY system_sort ASC";

        $arrReturn = array();
        $arrTemp =  $this->objDB->getPArray($strQuery, array($strSystemid));

        if(count($arrTemp) > 0)
            foreach($arrTemp as $arrOneRow)
                $arrReturn[] = $arrOneRow["system_id"];


        return $arrReturn;
    }

    /**
     * Fetches all child nodes recusrsively of the current / passed id.
     * <b> Only the IDs are fetched since the current object-context is not available!!! </b>
     *
     * @param string $strSystemid
     * @return string[]
     */
    public function getAllSubChildNodesAsIdArray($strSystemid = "") {
        $arrReturn = $this->getChildNodesAsIdArray($strSystemid);
        if(count($arrReturn) > 0) {
            foreach($arrReturn as $strId) {
                $arrReturn = array_merge($arrReturn, $this->getAllSubChildNodesAsIdArray($strId));
            }
        }
        return $arrReturn;
    }

    /**
     * Sets the Position of a SystemRecord in the currect level one position upwards or downwards
     *
     * @param string $strDirection upwards || downwards
     * @return void
     * @deprecated
     */
    public function setPosition($strDirection = "upwards") {
        $this->objSortManager->setPosition($strDirection);
    }

    /**
     * Sets the position of systemid using a given value.
     *
     * @param int $intNewPosition
     * @param array|bool $arrRestrictionModules If an array of module-ids is passed, the determination of siblings will be limited to the module-records matching one of the module-ids
     *
     * @return void
     */
    public function setAbsolutePosition($intNewPosition, $arrRestrictionModules = false) {
        $this->objSortManager->setAbsolutePosition($intNewPosition, $arrRestrictionModules);
    }

    /**
     * Return a complete SystemRecord
     *
     * @param string $strSystemid
     * @return mixed
     */
    public function getSystemRecord($strSystemid = "") {
        if($strSystemid == "")
            $strSystemid = $this->getSystemid();
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
     * @return mixed
     * @deprecated
     * @see class_module_system_module::getPlainModuleData($strName, $bitCache)
     */
    public function getModuleData($strName, $bitCache = true) {
        return class_module_system_module::getPlainModuleData($strName, $bitCache);
    }

    /**
     * Deletes a record from the SystemTable
     *
     * @param string $strSystemid
     * @param bool $bitRight
     * @param bool $bitDate
     * @return bool
     * @todo: remove first params, is always the current systemid. maybe mark as protected, currently only called by the test-classes
     *
     */
    public final function deleteSystemRecord($strSystemid, $bitRight = true, $bitDate = true) {
        $bitResult = true;

        //Start a tx before deleting anything
        $this->objDB->transactionBegin();

        $this->objSortManager->fixSortOnDelete($strSystemid);

        $strQuery = "DELETE FROM "._dbprefix_."system WHERE system_id = ?";
        $bitResult = $bitResult &&  $this->objDB->_pQuery($strQuery, array($strSystemid));

        if($bitRight) {
            $strQuery = "DELETE FROM "._dbprefix_."system_right WHERE right_id = ?";
            $bitResult = $bitResult &&  $this->objDB->_pQuery($strQuery, array($strSystemid));
        }

        if($bitDate) {
            $strQuery = "DELETE FROM "._dbprefix_."system_date WHERE system_date_id = ?";
            $bitResult = $bitResult &&  $this->objDB->_pQuery($strQuery, array($strSystemid));
        }

        //try to call other modules, maybe wanting to delete anything in addition, if the current record
        //is going to be deleted
        //TODO: remove legacy call
        $bitResult = $bitResult && class_core_eventdispatcher::notifyRecordDeletedListeners($strSystemid, get_class($this));
        $bitResult = $bitResult && class_core_eventdispatcher::getInstance()->notifyGenericListeners(class_system_eventidentifier::EVENT_SYSTEM_RECORDDELETED, array($strSystemid, get_class($this)));

        //end tx
        if($bitResult) {
            $this->objDB->transactionCommit();
            class_logger::getInstance()->addLogRow("deleted system-record with id ".$strSystemid, class_logger::$levelInfo);
        }
        else {
            $this->objDB->transactionRollback();;
            class_logger::getInstance()->addLogRow("deletion of system-record with id ".$strSystemid." failed", class_logger::$levelWarning);
        }

        //flush the cache
        $this->flushCompletePagesCache();

        return $bitResult;
    }


    /**
     * Deletes a record from the rights-table
     *
     * @param string $strSystemid
     * @return bool
     */
    public function deleteRight($strSystemid) {
        $strQuery = "DELETE FROM "._dbprefix_."system_right WHERE right_id = ?";
        return $this->objDB->_pQuery($strQuery, array($strSystemid));
    }

    /**
     * Generates a sorted array of systemids, reaching from the passed systemid up
     * until the assigned module-id
     *
     * @param string $strSystemid
     * @param string $strStopSystemid
     * @return mixed
     */
    public function getPathArray($strSystemid = "", $strStopSystemid = "0") {
        $arrReturn = array();

        if($strSystemid == "") {
            $strSystemid = $this->getSystemid();
        }

        //loop over all parent-records
        $strTempId = $strSystemid;
        while($strTempId != "0" && $strTempId != "" && $strTempId != -1 && $strTempId != $strStopSystemid) {
            $arrReturn[] = $strTempId;

            $objCommon = new class_module_system_common($strTempId);
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
     * @return string
     */
    public function getArrModule($strKey) {
        if(isset($this->arrModule[$strKey]))
            return $this->arrModule[$strKey];
        else
            return "";
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
    public function getLang($strName, $strModule = "", $arrParameters = array()) {
        if(is_array($strModule))
            $arrParameters = $strModule;

        if($strModule == "" || is_array($strModule)) {
            $strModule = $this->getArrModule("modul");
        }

        //Now we have to ask the Text-Object to return the text
        return $this->objLang->getLang($strName, $strModule, $arrParameters);
    }

    /**
     * Returns the current Text-Object Instance
     *
     * @return class_lang
     */
    protected function getObjLang() {
        return $this->objLang;
    }




    // --- PageCache Features -------------------------------------------------------------------------------

    /**
     * Deletes the complete Pages-Cache
     *
     * @return bool
     */
    public function flushCompletePagesCache() {
        return class_cache::flushCache("class_element_portal");
    }

    /**
     * Removes one page from the cache
     *
     * @param string $strPagename
     * @return bool
     */
    public function flushPageFromPagesCache($strPagename) {
        return class_cache::flushCache("class_element_portal", $strPagename);
    }


    // --- Portal-Language ----------------------------------------------------------------------------------

    /**
     * Returns the language to display contents on the portal
     *
     * @return string
     */
    public final function getStrPortalLanguage() {
        $objLanguage = new class_module_languages_language();
        return $objLanguage->getPortalLanguage();
    }



    // --- Admin-Language ----------------------------------------------------------------------------------

    /**
     * Returns the language to display contents or to edit contents on adminside
     * NOTE: THIS ARE THE CONTENTS, NOT THE TEXTS
     *
     * @return string
     */
    public final function getStrAdminLanguageToWorkOn() {
        $objLanguage = new class_module_languages_language();
        return $objLanguage->getAdminLanguage();
    }




    // --- GETTERS / SETTERS ----------------------------------------------------------------------------

    /**
     * Sets the current SystemID
     *
     * @param string $strID
     * @return bool
     */
    public function setSystemid($strID) {
        if(validateSystemid($strID)) {
            $this->strSystemid = $strID;
            return true;
        }
        else
            return false;
    }

    /**
     * Resets the current systemid
     * @return void
     */
    protected function unsetSystemid() {
        $this->strSystemid = "";
    }

    /**
     * Returns the current SystemID
     *
     * @return string
     */
    public function getSystemid() {
        return $this->strSystemid;
    }

    /**
     * @return string
     */
    public function getStrSystemid() {
        return $this->strSystemid;
    }

    /**
     * @param string $strSystemid
     * @return void
     */
    public function setStrSystemid($strSystemid) {
        if(validateSystemid($strSystemid)) {
            $this->strSystemid = $strSystemid;
        }
    }

    /**
     * Gets the Prev-ID of a record
     *
     * @param string $strSystemid
     *
     * @throws class_exception
     * @return string
     */
    public function getPrevId($strSystemid = "") {
        if($strSystemid != "")
            throw new class_exception("unsupported param @ ".__METHOD__, class_exception::$level_FATALERROR);

        return $this->getStrPrevId();
    }


    /**
     * @return string
     */
    public function getStrPrevId() {
        return $this->strPrevId;
    }

    /**
     * @return string
     */
    public function getStrOldPrevId() {
        return $this->strOldPrevId;
    }

    /**
     * @param string $strPrevId
     * @return void
     */
    public function setStrPrevId($strPrevId) {
        if(validateSystemid($strPrevId) || $strPrevId === "0") {
            $this->strPrevId = $strPrevId;
        }
    }

    /**
     * Gets the module id / module nr of a systemRecord
     *
     * @param string $strSystemid
     *
     * @throws class_exception
     * @return int
     */
    public function getRecordModuleNr($strSystemid = "") {
        if($strSystemid != "")
            throw new class_exception("unsupported param @ ".__METHOD__, class_exception::$level_FATALERROR);

        return $this->getIntModuleNr();
    }

    /**
     * @return int
     */
    public function getIntModuleNr() {
        return $this->intModuleNr;
    }

    /**
     * @param int $intModuleNr
     * @return void
     */
    public function setIntModuleNr($intModuleNr) {
        $this->intModuleNr = $intModuleNr;
    }

    /**
     * @return int
     */
    public function getIntSort() {
        return $this->intSort;
    }

    /**
     * @param int $intSort
     * @return void
     */
    public function setIntSort($intSort) {
        $this->intSort = $intSort;
    }

    /**
     * Returns the name of the user who last edited the record
     *
     * @param string $strSystemid
     *
     * @throws class_exception
     * @return string
     */
    public function getLastEditUser($strSystemid = "") {
        if($strSystemid != "")
            throw new class_exception("unsupported param @ ".__METHOD__, class_exception::$level_FATALERROR);

        if(validateSystemid($this->getStrLmUser())) {
            $objUser = new class_module_user_user($this->getStrLmUser());
            return $objUser->getStrUsername();
        }
        else
            return "System";
    }

    /**
     * @return string string
     */
    public function getStrLmUser() {
        return $this->strLmUser;
    }

    /**
     * Returns the id of the user who last edited the record
     *
     * @return string
     */
    public function getLastEditUserId() {
        return $this->getStrLmUser();
    }

    /**
     * @param string $strLmUser
     * @return void
     */
    public function setStrLmUser($strLmUser) {
        $this->strLmUser = $strLmUser;
    }

    /**
     * @return int
     */
    public function getIntLmTime() {
        return $this->intLmTime;
    }

    /**
     * @param int $strLmTime
     * @return void
     */
    public function setIntLmTime($strLmTime) {
        $this->intLmTime = $strLmTime;
    }

    /**
     * @return string
     */
    public function getStrLockId() {
        return $this->strLockId;
    }

    /**
     * @param string $strLockId
     * @return void
     */
    public function setStrLockId($strLockId) {
        $this->strLockId = $strLockId;
    }

    /**
     * @return int
     */
    public function getIntLockTime() {
        return $this->intLockTime;
    }

    /**
     * @param int $intLockTime
     * @return void
     */
    public function setIntLockTime($intLockTime) {
        $this->intLockTime = $intLockTime;
    }

    /**
     * @return int
     */
    public function getLongCreateDate() {
        return $this->longCreateDate;
    }

    /**
     * Returns the creation-date of the current record
     *
     * @param string $strSystemid
     *
     * @throws class_exception
     * @return class_date
     */
    public function getObjCreateDate($strSystemid = "") {
        if($strSystemid != "")
            throw new class_exception("unsupported param @ ".__METHOD__, class_exception::$level_FATALERROR);

        return new class_date($this->getLongCreateDate());
    }

    /**
     * @param int $longCreateDate
     * @return void
     */
    public function setLongCreateDate($longCreateDate) {
        $this->longCreateDate = $longCreateDate;
    }

    /**
     * @return string
     */
    public function getStrOwner() {
        return $this->strOwner;
    }

    /**
     * @param string $strOwner
     * @return void
     */
    public function setStrOwner($strOwner) {
        $this->strOwner = $strOwner;
    }

    /**
     * Gets the id of the user currently being the owner of the record
     *
     * @param string $strSystemid
     *
     * @throws class_exception
     * @return string
     */
    public final function getOwnerId($strSystemid = "") {
        if($strSystemid != "")
            throw new class_exception("unsupported param @ ".__METHOD__, class_exception::$level_FATALERROR);

        return $this->getStrOwner();
    }

    /**
     * Sets the id of the user who owns this record.
     * Please note that since 4.4, setting an owner-id no longer fires an updateObjectToDb()!
     *
     * @param string $strOwner
     * @param string $strSystemid
     * @deprecated
     *
     * @throws class_exception
     * @return bool
     */
    public final function setOwnerId($strOwner, $strSystemid = "") {
        if($strSystemid != "")
            throw new class_exception("unsupported param @ ".__METHOD__, class_exception::$level_FATALERROR);

        $this->setStrOwner($strOwner);
        return true;
    }

    /**
     * @return int
     */
    public function getIntRecordStatus() {
        return $this->intRecordStatus;
    }

    /**
     * Gets the status of a systemRecord
     *
     * @param string $strSystemid
     *
     * @throws class_exception
     * @return int
     * @deprecated use class_root::getIntRecordStatus() instead
     * @see class_root::getIntRecordStatus()
     */
    public function getStatus($strSystemid = "") {
        if($strSystemid != "")
            throw new class_exception("unsupported param @ ".__METHOD__, class_exception::$level_FATALERROR);

        return $this->getIntRecordStatus();
    }


    /**
     * Sets the internal status. Fires a status-changed event.
     *
     * @param int $intRecordStatus
     * @param bool $bitFireStatusChangeEvent
     * @return bool
     */
    public function setIntRecordStatus($intRecordStatus, $bitFireStatusChangeEvent = true) {
        $intPrevStatus = $this->intRecordStatus;
        $this->intRecordStatus = $intRecordStatus;

        $bitReturn = true;

        if($intPrevStatus != $intRecordStatus && $intPrevStatus != -1) {
            if(validateSystemid($this->getSystemid())) {
                $this->updateObjectToDb();
                class_carrier::getInstance()->flushCache(class_carrier::INT_CACHE_TYPE_ORMCACHE | class_carrier::INT_CACHE_TYPE_DBQUERIES);
            }
            if($bitFireStatusChangeEvent && validateSystemid($this->getSystemid())) {
                $bitReturn = class_core_eventdispatcher::notifyStatusChangedListeners($this->getSystemid(), $intRecordStatus);
            }
        }

        return $bitReturn;

    }

    /**
     * Gets comment saved with the record
     *
     * @param string $strSystemid
     *
     * @throws class_exception
     * @return string
     */
    public function getRecordComment($strSystemid = "") {
        if($strSystemid != "")
            throw new class_exception("unsupported param @ ".__METHOD__, class_exception::$level_FATALERROR);

        return $this->getStrRecordComment();
    }


    /**
     * @return string
     */
    public function getStrRecordComment() {
        return $this->strRecordComment;
    }

    /**
     * @param string $strRecordComment
     * @return void
     */
    public function setStrRecordComment($strRecordComment) {
        if(uniStrlen($strRecordComment) > 254)
            $strRecordComment = uniStrTrim($strRecordComment, 250);
        $this->strRecordComment = $strRecordComment;
    }

    /**
     * @param string $strRecordClass
     * @return void
     */
    public function setStrRecordClass($strRecordClass) {
        $this->strRecordClass = $strRecordClass;
    }

    /**
     * @return string
     * @return void
     */
    public function getStrRecordClass() {
        return $this->strRecordClass;
    }


    /**
     * Writes a value to the params-array
     *
     * @param string $strKey
     * @param mixed $mixedValue Value
     * @return void
     */
    public function setParam($strKey, $mixedValue) {
        class_carrier::getInstance()->setParam($strKey, $mixedValue);
    }

    /**
     * Returns a value from the params-Array
     *
     * @param string $strKey
     * @return string else ""
     */
    public function getParam($strKey) {
        return class_carrier::getInstance()->getParam($strKey);
    }

    /**
     * Returns the complete Params-Array
     *
     * @return mixed
     */
    public final function getAllParams() {
        return class_carrier::getAllParams();
    }

    /**
     * returns the action used for the current request
     *
     * @return string
     */
    public final function getAction() {
        return (string)$this->strAction;
    }

    /**
     * Returns the current instance of the class_rights
     *
     * @return object
     */
    public function getObjRights() {
        return $this->objRights;
    }

    /**
     * Returns an instance of the lockmanager, initialized
     * with the current systemid.
     *
     * @return class_lockmanager
     */
    public function getLockManager() {
        return new class_lockmanager($this->getSystemid(), $this);
    }


    /**
     * Writes a key-value-pair to the arrModule
     *
     * @param string $strKey
     * @param mixed $strValue
     * @return void
     */
    public function setArrModuleEntry($strKey, $strValue) {
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
     * @return void
     */
    public function setArrInitRow($arrInitRow) {
        if(isset($arrInitRow["system_id"])) {
            $this->arrInitRow = $arrInitRow;
            $this->objRights->addRowToCache($arrInitRow);
        }
    }

    /**
     * Returns the set of internal values marked as init-values
     * @return null|array
     */
    public function getArrInitRow() {
        return $this->arrInitRow;
    }

    /**
     * @param \class_date $objEndDate
     * @return void
     */
    public function setObjEndDate($objEndDate = null) {
        if(!$objEndDate instanceof class_date && $objEndDate != "" && $objEndDate != null)
            $objEndDate = new class_date($objEndDate);

        $this->objEndDate = $objEndDate;
        $this->bitDatesChanges = true;
    }

    /**
     * @return class_date
     */
    public function getObjEndDate() {
        return $this->objEndDate;
    }

    /**
     * @param \class_date $objSpecialDate
     * @return void
     */
    public function setObjSpecialDate($objSpecialDate = null) {
        if(!$objSpecialDate instanceof class_date && $objSpecialDate != "" && $objSpecialDate != null)
            $objSpecialDate = new class_date($objSpecialDate);

        $this->objSpecialDate = $objSpecialDate;
        $this->bitDatesChanges = true;
    }

    /**
     * @return class_date
     */
    public function getObjSpecialDate() {
        return $this->objSpecialDate;
    }

    /**
     * @param class_date $objStartDate
     * @return void
     */
    public function setObjStartDate($objStartDate = null) {
        if(!$objStartDate instanceof class_date && $objStartDate != "" && $objStartDate != null)
            $objStartDate = new class_date($objStartDate);

        $this->bitDatesChanges = true;
        $this->objStartDate = $objStartDate;
    }

    /**
     * @return class_date
     */
    public function getObjStartDate() {
        return $this->objStartDate;
    }



}
