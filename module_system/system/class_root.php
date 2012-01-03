<?php
/*"******************************************************************************************************
*   (c) 2004-2006 by MulchProductions, www.mulchprod.de                                                 *
*   (c) 2007-2012 by Kajona, www.kajona.de                                                              *
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
    /**
     * Instance of class_config
     *
     * @var class_config
     */
	protected $objConfig = null;			//Object containing config-data
	/**
	 * Instance of class_db
	 *
	 * @var class_db
	 */
	protected $objDB = null;				//Object to the database
	/**
	 * Instance of class_session
	 *
	 * @var class_session
	 */
	protected $objSession = null;			//Object containing the session-management
	/**
	 * Instance of class_rights
	 *
	 * @var class_rights
	 */
	protected $objRights = null;			//Object handling the right-stuff

	/**
	 * Instance of class_lang
	 *
	 * @var class_lang
	 */
	private   $objLang = null;				//Object managing the langfiles

	private   $strAction;			        //current action to perform (GET/POST)
	protected $arrModule = array();	        //Array containing information about the current module


    //--- fields to be synchronized with the database ---

    /**
     * Boolean indicator to trigger the loading of the current records details.
     * If not accessed, they won't be loaded: lazy loading.
     * @var bool
     */
    private $bitDetailsLoaded = false;

    /**
     * The records current systemid
     * @var string
     */
    private $strSystemid;
    /**
     * The records internal parent-id
     * @var string
     */
    private $strPrevId = -1;
    /**
     * The old prev-id, used to track hierarchical changes -> requires a rebuild of the rights-table
     * @var type
     */
    private $strOldPrevId = -1;
    /**
     * The records module-number
     * @var int
     */
    private $intModuleNr;
    /**
     * The records sort-position relative to the parent record
     * @var int
     */
    private $intSort;
    /**
     * The id of the user who created the record initially
     * @var string
     */
    private $strOwner;
    /**
     * The id of the user last who did the last changes to the current record
     * @var type
     */
    private $strLmUser;
    /**
     * Timestamp of the last modification
     * ATTENTION: time() based, so 32 bit integer
     * @todo migrate to long-timestamp
     * @var int
     */
    private $intLmTime;
    /**
     * The id of the user locking the current record, emtpy otherwise
     * @var string
     */
    private $strLockId;
    /**
     * Time the current locking was triggered
     * ATTENTION: time() based, so 32 bit integer
     * @todo migrate to long-timestamp
     * @var int
     */
    private $intLockTime;
    /**
     * The records status
     * @var int
     */
    private $intRecordStatus;
    /**
     * Human readable comment describing the current record
     * @var string
     */
    private $strRecordComment;
    /**
     * Holds the current objects' class
     * @var string
     */
    private $strRecordClass;
    /**
     * Long-based representation of the timestamp the record was created initially
     * @var long
     */
    private $longCreateDate;


    /**
     * Constructor
     *
     * @param string $strSystemid
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

		//And keep the action
		$this->strAction = $this->getParam("action");

        $this->strSystemid = $strSystemid;

        if($strSystemid != "")
            $this->initObject();
	}

    /**
     * Init the current record with the system-fields
     * @todo: add dates?
     * @param string $strSystemid
     */
    private final function internalInit($strSystemid) {

        if($this->bitDetailsLoaded == true)
            return;


        if(validateSystemid($strSystemid)) {

            $this->bitDetailsLoaded = true;

            $this->strSystemid = $strSystemid;

            $strQuery = "SELECT *
                           FROM "._dbprefix_."system
                          WHERE system_id = ? ";
            $arrRow = $this->objDB->getPRow($strQuery, array($strSystemid));

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
                $this->strRecordClass = $arrRow["system_class"];

                $this->strOldPrevId = $this->strPrevId;


            }
        }
    }

    /**
     * Forces to reinit the object from the database
     *
     * @see interface_model::initObject()
     */
    public function loadDataFromDb() {
        $this->internalInit($this->getStrSystemid());
        $this->initObjectInternal();
    }

    /**
     * Method to invoke object initialization.
     * In nearly all cases, this is triggered by the framework itself.
     */
    public final function initObject() {
        $this->initObjectInternal();
    }

    /**
     * responsible to create a valid object. being called at time of
     * object creation, if systemid given.
     * Use this lifecycle-method in order to load
     * all fields from the database.
     *
     */
    protected abstract function initObjectInternal();



   /**
    * Deletes the current object from the system.
    * Overwrite this method in order to remove the current object from the system.
    * The system-record itself is being delete automatically.
    *
    * @abstract
    * @return bool
    */
    protected abstract function deleteObjectInternal();

    /**
     * Removes the current object from the system.
     *
     * @return bool
     */
    public function deleteObject() {
        $this->objDB->transactionBegin();

        $bitReturn = $this->deleteObjectInternal();
        $bitReturn .= $this->deleteSystemRecord($this->getSystemid());

        if($bitReturn) {
            class_logger::getInstance()->addLogRow("successfully deleted record ".$this->getSystemid()." / ".$this->getStrDisplayName(), class_logger::$levelInfo);
            $this->objDB->transactionCommit();
            return true;
        }
        else {
            class_logger::getInstance()->addLogRow("error deleting record ".$this->getSystemid()." / ".$this->getStrDisplayName(), class_logger::$levelInfo);
            $this->objDB->transactionRollback();
            return false;
        }
    }

    // --- DATABASE-SYNCHRONIZATION -------------------------------------------------------------------------

    /**
     * Returns a list of tables the current object is persisted to.
     * A new record is created in each table, as soon as a save-/update-request was triggered by the framework.
     * The array should contain the name of the table as the key and the name
     * of the primary-key (so the column name) as the matching value.
     * E.g.: array(_dbprefix_."pages" => "page_id)
     *
     * @abstract
     * @return array [table => primary row name]
     */
    protected abstract function getObjectTables();


    /**
     * Saves the current object to the database. Determines, whether the current object has to be inserted
     * or updated to the database.
     * In case of an update, the objects' updateStateToDb() method is being called (as required by class_model).
     * In the case of a new object, a blank record is being created. Therefore, all tables returned by the
     * objects' getObjectTables() method will be filled with a new record (using the same new systemid as the
     * primary key). The newly created systemid is being set as the current objects' one and can be used in the afterwards
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

        if(!$this instanceof interface_model)
            throw new class_exception("current object must implemented interface_model", class_exception::$level_FATALERROR);

        $this->objDB->transactionBegin();

        //current systemid given? if not, create a new record.
        if(!validateSystemid($this->getSystemid())) {

            if($strPrevId === false || $strPrevId === "") {
                //try to find the current modules-one
                if(isset($this->arrModule["modul"])) {
                    $strPrevId = $this->getModuleSystemid($this->arrModule["modul"]);
                    if(!validateSystemid($strPrevId))
                        throw new class_exception("automatic determination of module-id failed ", class_exception::$level_FATALERROR);
                }
                else
                    throw new class_exception("insert with no previd ", class_exception::$level_FATALERROR);
            }

            //create the new systemrecord
            $this->createSystemRecord($strPrevId, $this->getStrDisplayName());

            if(validateSystemid($this->getStrSystemid())) {
                //$this->setStrSystemid($strNewSystemid);

                //Create the foreign records
                $arrTables = $this->getObjectTables();
                if(is_array($arrTables)) {
                    foreach($arrTables as $strTable => $strColumn) {
                        $strQuery = "INSERT INTO ".$this->objDB->encloseTableName($strTable)."
                                                (".$this->objDB->encloseColumnName($strColumn).") VALUES
                                                (?) ";

                        if(!$this->objDB->_pQuery($strQuery, array($this->getStrSystemid()) ))
                            $bitCommit = false;
                    }
                }

                if(!$this->onInsertToDb())
                    $bitCommit = false;

            }
            else
                throw new class_exception("creation of systemrecord failed", class_exception::$level_FATALERROR);

            //all updates are done, start the "real" update
            $this->objDB->flushQueryCache();
        }

        //update ourselves to the database
        if(!$this->updateStateToDb())
            $bitCommit = false;

        //new prev-id?
        if($strPrevId !== false && $this->getSystemid() != $strPrevId && (validateSystemid($strPrevId) || $strPrevId = "0"))
            $this->setStrPrevId($strPrevId);

        //new comment?
        $this->setStrRecordComment($this->getStrDisplayName());

        //save back to the database
        $bitCommit = $bitCommit && $this->updateSystemrecord();

        if($bitCommit) {
            $this->objDB->transactionCommit();
            $bitReturn = true;
            class_logger::getInstance()->addLogRow("updateObjectToDb() succeeded for systemid ".$this->getSystemid()." (".$this->getRecordComment().")", class_logger::$levelInfo);
        }
        else {
            $this->objDB->transactionRollback();
            $bitReturn = false;
            class_logger::getInstance()->addLogRow("updateObjectToDb() failed for systemid ".$this->getSystemid()." (".$this->getRecordComment().")", class_logger::$levelWarning);
        }


        return $bitReturn;
    }

    /**
     * Called whenever a update-request was fired.
     * Use this method to synchronize the current object with the database.
     * Use only updates, inserts are not required to be implemented.
     *
     * @abstract
     * @return bool
     */
    protected abstract function updateStateToDb();

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

        $bitReturn = $this->objDB->_pQuery($strQuery, array(
                    $this->getStrPrevId(),
                    (int)$this->getIntModuleNr(),
                    (int)$this->getIntSort(),
                    $this->getStrOwner(),
                    $this->objSession->getUserID(),
                    time(),
                    $this->getStrLockId(),
                    (int)$this->getIntLockTime(),
                    (int)$this->getIntRecordStatus(),
                    $this->getStrRecordComment(),
                    $this->getStrRecordClass(),
                    $this->getLongCreateDate(),
                    $this->getSystemid()
        ));

        $this->objDB->flushQueryCache();

        if($this->strOldPrevId != $this->strPrevId) {
            $this->objDB->flushQueryCache();
            $this->objRights->flushRightsCache();
            $this->objRights->rebuildRightsStructure($this->getSystemid());
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
     * @param int|string $intModuleNr Number of the module this record belongs to
     * @param string $strSystemId SystemID to be used
     * @param int $intStatus    Active (1)/Inactive (0)?
     * @param null|string $strClass
     * @return string The ID used/generated
     */
	public function createSystemRecord($strPrevId, $strComment, $bitRight = true, $intModuleNr = "", $strSystemId = "", $intStatus = 1, $strClass = null) {
		//Do we need a new SystemID?
		if($strSystemId == "")
			$strSystemId = generateSystemid();

        $this->setStrSystemid($strSystemId);

        if($strClass === null)
            $strClass = get_class($this);

		//Given a ModuleNr?
		if($intModuleNr == "")
			$intModuleNr = $this->arrModule["moduleId"];
		//Correct prevID
		if($strPrevId == "")
			$strPrevId = 0;

        //determine the correct new sort-id - append by default
        $strQuery = "SELECT COUNT(*) FROM "._dbprefix_."system WHERE system_prev_id = ?";
        $arrRow = $this->objDB->getPRow($strQuery, array($strPrevId), 0, false);
        $intSiblings = $arrRow["COUNT(*)"];

        $strComment = uniStrTrim($strComment, 253);


		//So, lets generate the record
		$strQuery = "INSERT INTO "._dbprefix_."system
					 ( system_id, system_prev_id, system_module_nr, system_owner, system_create_date, system_lm_user,
					   system_lm_time, system_status, system_comment, system_sort, system_class) VALUES
					 (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?)";

		//Send the query to the db
		$this->objDB->_pQuery($strQuery, array(
            $strSystemId,
            $strPrevId,
            (int)$intModuleNr,
            $this->objSession->getUserID(),
            class_date::getCurrentTimestamp(),
            $this->objSession->getUserID(),
            time(),
            (int)$intStatus,
            $strComment,
            (int)($intSiblings+1),
            $strClass
        ));

		//Do we need a Rights-Record?
		if($bitRight) {
			$strQuery = "INSERT INTO "._dbprefix_."system_right
						 (right_id, right_inherit) VALUES
						 (?, 1)";

			$this->objDB->_pQuery($strQuery, array($strSystemId));
            //update rights to inherit
            $this->objRights->setInherited(true, $strSystemId);
		}

		class_logger::getInstance()->addLogRow("new system-record created: ".$strSystemId ." (".$strComment.")", class_logger::$levelInfo);

        $this->objDB->flushQueryCache();
        $this->internalInit($this->getSystemid());

		return $strSystemId;

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

    // --- RIGHTS-METHODS -----------------------------------------------------------------------------------

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
	 * @return int
	 */
	public function getChildNodesAsIdArray($strSystemid = "") {
	    if($strSystemid == "")
			$strSystemid = $this->getSystemid();

	    $strQuery = "SELECT system_id
					 FROM "._dbprefix_."system
					 WHERE system_prev_id=?
                     ORDER BY system_sort ASC";

        $arrReturn = array();
        $arrTemp =  $this->objDB->getPArray($strQuery, array($strSystemid));

        if(count($arrTemp) > 0)
            foreach($arrTemp as $arrOneRow)
                $arrReturn[] = $arrOneRow["system_id"];


	    return $arrReturn;
	}

	/**
	 * Sets the Position of a SystemRecord in the currect level one position upwards or downwards
	 *
	 * @param string $strIdToShift
	 * @param string $strDirection upwards || downwards
	 * @return void
	 */
	public function setPosition($strIdToShift, $strDirection = "upwards") {
		//Load all elements on the same level, so at first get the prev id
        $objCommon = new class_module_system_common($strIdToShift);
		$strPrevID = $objCommon->getPrevId();
		$strQuery = "SELECT *
						 FROM "._dbprefix_."system
						 WHERE system_prev_id=?
						 ORDER BY system_sort ASC, system_comment ASC";

		//No caching here to allow mutliple shiftings per request
		$arrElements = $this->objDB->getPArray($strQuery, array($strPrevID), false);

		//Iterate to move the element
		$bitSaveToDb = false;
		for($intI = 0; $intI < count($arrElements); $intI++) {
			if($arrElements[$intI]["system_id"] == $strIdToShift) {
				//Shift the elements around
				if($strDirection == "upwards") {
					//Valid action requested?
					if($intI != 0 || $arrElements[$intI]["system_sort"] == 0) {
						//Shift it one position up
						$arrTemp = $arrElements[$intI-1];
						$arrElements[$intI-1] = $arrElements[$intI];
						$arrElements[$intI] = $arrTemp;
						$bitSaveToDb = true;
						break;
					}
				}
				elseif ($strDirection == "downwards") {
					//Valid Action requested
					if($intI != (count($arrElements)-1) || $arrElements[$intI]["system_sort"] == 0) {
						//Shift it one position down
						$arrTemp = $arrElements[$intI+1];
						$arrElements[$intI+1] = $arrElements[$intI];
						$arrElements[$intI] = $arrTemp;
						$bitSaveToDb = true;
						break;
					}
				}
			}
		}
		//Do we have to save to the db?
		if($bitSaveToDb) {
			foreach($arrElements as $intKey => $arrOneElement) {
				//$intKey+1 forces new elements to be at the top of lists
				$strQuery = "UPDATE "._dbprefix_."system
								SET system_sort=?
								WHERE system_id=?";
				$this->objDB->_pQuery($strQuery, array( (((int)$intKey)+1), $arrOneElement["system_id"]));
			}
		}

        //flush the cache
        $this->flushCompletePagesCache();

        if($strIdToShift == $this->getSystemid()) {
            $this->objDB->flushQueryCache();
            $this->internalInit($this->getSystemid());
        }
	}

	/**
	 * Sets the position of systemid using a given value.
	 *
	 * @param string $strIdToSet
	 * @param int $intPosition
	 */
	public function setAbsolutePosition($strIdToSet, $intPosition) {
	    class_logger::getInstance()->addLogRow("move ".$strIdToSet." to new pos ".$intPosition, class_logger::$levelInfo);

		//to have a better array-like handling, decrease pos by one.
		//remind to add at the end when saving to db
		$intPosition--;

		//Load all elements on the same level, so at first get the prev id
        $objCommon = new class_module_system_common($strIdToSet);
		$strPrevID = $objCommon->getPrevId();
		$strQuery = "SELECT *
						 FROM "._dbprefix_."system
						 WHERE system_prev_id=?
						 ORDER BY system_sort ASC, system_comment ASC";

		//No caching here to allow mutliple shiftings per request
		$arrElements = $this->objDB->getPArray($strQuery, array($strPrevID), false);

		//more than one record to set?
		if(count($arrElements) <= 1)
			return;

		//senseless new pos?
		if($intPosition < 0 || $intPosition >= count($arrElements))
		    return;

		//create inital sorts?
		if($arrElements[0]["system_sort"] == 0) {
		    $this->setPosition($arrElements[0]["system_id"], "downwards");
		    $this->setPosition($arrElements[0]["system_id"], "upwards");
		    $this->objDB->flushQueryCache();
		}

		//searching the current element to get to know if element should be
		//sorted up- or downwards
		$bitSortDown = false;
		$bitSortUp = false;
		$intHitKey = 0;
		for($intI = 0; $intI < count($arrElements); $intI++) {
			if($arrElements[$intI]["system_id"] == $strIdToSet) {
				if($intI < $intPosition)
					$bitSortDown = true;
				if($intI >= $intPosition+1)
					$bitSortUp = true;

				$intHitKey = $intI;
			}
		}

		//sort up?
		if($bitSortUp) {
			//move the record to be shifted to the wanted pos
			$strQuery = "UPDATE "._dbprefix_."system
								SET system_sort=?
								WHERE system_id=?";
			$this->objDB->_pQuery($strQuery, array(((int)$intPosition+1), $strIdToSet));

			//start at the pos to be reached and move all one down
			for($intI = 0; $intI < count($arrElements); $intI++) {
				//move all other one pos down, except the last in the interval:
				//already moved...
				if($intI >= $intPosition && $intI < $intHitKey) {
					$strQuery = "UPDATE "._dbprefix_."system
								SET system_sort=system_sort+1
								WHERE system_id=?";
					$this->objDB->_pQuery($strQuery, array($arrElements[$intI]["system_id"]));
				}
			}
		}

		if($bitSortDown) {
			//move the record to be shifted to the wanted pos
			$strQuery = "UPDATE "._dbprefix_."system
								SET system_sort=?
								WHERE system_id=?";
			$this->objDB->_pQuery($strQuery, array(((int)$intPosition+1), $strIdToSet));

			//start at the pos to be reached and move all one down
			for($intI = 0; $intI < count($arrElements); $intI++) {
				//move all other one pos down, except the last in the interval:
				//already moved...
				if($intI > $intHitKey && $intI <= $intPosition) {
					$strQuery = "UPDATE "._dbprefix_."system
								SET system_sort=system_sort-1
								WHERE system_id=?";
					$this->objDB->_pQuery($strQuery, array($arrElements[$intI]["system_id"]));
				}
			}
		}

        //flush the cache
        $this->flushCompletePagesCache();

        if($strIdToSet == $this->getSystemid()) {
            $this->objDB->flushQueryCache();
            $this->internalInit($this->getSystemid());
        }
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
	 */
	public function getModuleData($strName, $bitCache = true) {
		$strQuery = "SELECT * FROM "._dbprefix_."system_module ORDER BY module_nr";
		$arrModules = $this->objDB->getPArray($strQuery, array(), $bitCache);

		foreach($arrModules as $arrOneModule) {
		    if($arrOneModule["module_name"] == $strName)
		       return $arrOneModule;
		}

        return array();
	}

	/**
	 * Returns the SystemID of a installed module
	 *
	 * @param string $strModule
	 * @return string
	 */
	public function getModuleSystemid($strModule) {
		$arrModule = $this->getModuleData($strModule);
		if(isset($arrModule["module_id"]))
			return $arrModule["module_id"];
		else
			return "";
	}

	/**
	 * Deletes a record from the SystemTable
	 *
	 * @param string $strSystemid
	 * @param bool $bitRight
	 * @param bool $bitDate
	 * @return bool
     * @todo: remove first params, is always the current systemid. maybe mark as protected.
     *
	 */
	public function deleteSystemRecord($strSystemid, $bitRight = true, $bitDate = true) {

		//try to call other modules, maybe wanting to delete anything in addition, if the current record
		//is going to be deleted
        $bitResult = class_core_eventdispatcher::notifyRecordDeletedListeners($strSystemid);

		//Start a tx before deleting anything
		$this->objDB->transactionBegin();

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
	 * Used to get Text out of Textfiles
	 *
	 * @param string $strName
	 * @param string $strModule
	 * @return string
	 */
	public function getLang($strName, $strModule = "") {
		if($strModule == "")
			$strModule = $this->arrModule["modul"];

		//Now we have to ask the Text-Object to return the text
		return $this->objLang->getLang($strName, $strModule);
	}

	/**
	 * Returns the current Text-Object Instance
	 *
	 * @return obj
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
            if($this->strSystemid != $strID)
                $this->bitDetailsLoaded = false;

			$this->strSystemid = $strID;
			return true;
		}
		else
			return false;
	}

    /**
     * Resets the current systemid
     */
    protected function unsetSystemid() {
        $this->strSystemid = "";
        $this->bitDetailsLoaded = false;
    }

	/**
	 * Returns the current SystemID
	 *
	 * @return string
	 */
	public function getSystemid() {
		return $this->strSystemid;
	}

    public function getStrSystemid() {
        return $this->strSystemid;
    }

    public function setStrSystemid($strSystemid) {
        if(validateSystemid($strSystemid)) {
            if($this->strSystemid != $strSystemid)
                $this->bitDetailsLoaded = false;

            $this->strSystemid = $strSystemid;
        }
    }

    /**
	 * Gets the Prev-ID of a record
	 *
	 * @param string $strSystemid
	 * @return string
	 */
	public function getPrevId($strSystemid = "") {
        $this->internalInit($this->strSystemid);
        if($strSystemid != "")
            throw new class_exception("unsupported param @ ".__METHOD__, class_exception::$level_FATALERROR);

        return $this->getStrPrevId();
	}


    public function getStrPrevId() {
        $this->internalInit($this->strSystemid);
        return $this->strPrevId;
    }

    public function setStrPrevId($strPrevId) {
        $this->internalInit($this->strSystemid);
        $this->strPrevId = $strPrevId;
    }

    /**
	 * Gets the module id / module nr of a systemRecord
	 *
	 * @param string $strSystemid
	 * @return int
	 */
	public function getRecordModuleNr($strSystemid = "") {
        $this->internalInit($this->strSystemid);
        if($strSystemid != "")
            throw new class_exception("unsupported param @ ".__METHOD__, class_exception::$level_FATALERROR);

        return $this->getIntModuleNr();
	}

    public function getIntModuleNr() {
        $this->internalInit($this->strSystemid);
        return $this->intModuleNr;
    }

    public function setIntModuleNr($intModuleNr) {
        $this->internalInit($this->strSystemid);
        $this->intModuleNr = $intModuleNr;
    }

    public function getIntSort() {
        $this->internalInit($this->strSystemid);
        return $this->intSort;
    }

    public function setIntSort($intSort) {
        $this->internalInit($this->strSystemid);
        $this->intSort = $intSort;
    }

    /**
	 * Returns the name of the user who last edited the record
	 *
	 * @param string $strSystemid
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

    public function getStrLmUser() {
        $this->internalInit($this->strSystemid);
        return $this->strLmUser;
    }

    /**
	 * Returns the id of the user who last edited the record
	 *
	 * @param string $strSystemid
	 * @return string
	 */
	public function getLastEditUserId() {
        $this->internalInit($this->strSystemid);
        return $this->getStrLmUser();
	}

    public function setStrLmUser($strLmUser) {
        $this->internalInit($this->strSystemid);
        $this->strLmUser = $strLmUser;
    }

    public function getIntLmTime() {
        $this->internalInit($this->strSystemid);
        return $this->intLmTime;
    }

    public function setIntLmTime($strLmTime) {
        $this->internalInit($this->strSystemid);
        $this->intLmTime = $strLmTime;
    }

    public function getStrLockId() {
        $this->internalInit($this->strSystemid);
        return $this->strLockId;
    }

    public function setStrLockId($strLockId) {
        $this->internalInit($this->strSystemid);
        $this->strLockId = $strLockId;
    }

    public function getIntLockTime() {
        $this->internalInit($this->strSystemid);
        return $this->intLockTime;
    }

    public function setIntLockTime($intLockTime) {
        $this->internalInit($this->strSystemid);
        $this->intLockTime = $intLockTime;
    }

    public function getLongCreateDate() {
        $this->internalInit($this->strSystemid);
        return $this->longCreateDate;
    }

    /**
     * Returns the creation-date of the current record
     *
     * @param string $strSystemid
     * @return class_date
     */
    public function getObjCreateDate($strSystemid = "") {
        $this->internalInit($this->strSystemid);
        if($strSystemid != "")
            throw new class_exception("unsupported param @ ".__METHOD__, class_exception::$level_FATALERROR);

        return new class_date($this->getLongCreateDate());
    }

    public function setLongCreateDate($longCreateDate) {
        $this->internalInit($this->strSystemid);
        $this->longCreateDate = $longCreateDate;
    }

    public function getStrOwner() {
        $this->internalInit($this->strSystemid);
        return $this->strOwner;
    }

    public function setStrOwner($strOwner) {
        $this->internalInit($this->strSystemid);
        $this->strOwner = $strOwner;
    }

    /**
     * Gets the id of the user currently being the owner of the record
     *
     * @param string $strSystemid
     * @return string
     */
    public final function getOwnerId($strSystemid = "") {
        $this->internalInit($this->strSystemid);
        if($strSystemid != "")
            throw new class_exception("unsupported param @ ".__METHOD__, class_exception::$level_FATALERROR);

        return $this->getStrOwner();
    }

    /**
     * Sets the id of the user who owns this record
     *
     * @param string $strOwner
     * @param string $strSystemid
     * @return bool
     */
    public final function setOwnerId($strOwner, $strSystemid = "") {
        $this->internalInit($this->strSystemid);
        if($strSystemid != "")
            throw new class_exception("unsupported param @ ".__METHOD__, class_exception::$level_FATALERROR);

        $this->setStrOwner($strOwner);
        return $this->updateSystemrecord();
    }

    public function getIntRecordStatus() {
        $this->internalInit($this->strSystemid);
        return $this->intRecordStatus;
    }

    /**
	 * Gets the status of a systemRecord
	 *
	 * @param string $strSystemid
	 * @return int
	 */
	public function getStatus($strSystemid = "") {
        $this->internalInit($this->strSystemid);
        if($strSystemid != "")
            throw new class_exception("unsupported param @ ".__METHOD__, class_exception::$level_FATALERROR);

        return $this->getIntRecordStatus();
	}

    /**
     * If a defined status is passed, it will be set. Ohterwise, it
     * negates the status of a systemRecord.
     *
     * @param string $strSystemid
     * @param bool $intStatus
     * @return bool
     * @todo: systemid param handling
     */
	public function setStatus($strSystemid = "", $intStatus = false) {
        $this->internalInit($this->strSystemid);
		if($strSystemid == "")
			$strSystemid = $this->getSystemid();

        $intNewStatus = $intStatus;
        if($intStatus === false) {
            $intStatus = $this->getIntRecordStatus();
            if($intStatus == 0)
                $intNewStatus = 1;
            else
                $intNewStatus = 0;
        }

        $bitReturn = $this->setIntRecordStatus($intNewStatus);
        $this->updateSystemrecord();

        return $bitReturn;
	}

    /**
     * Sets the internal status. Triggers a db-update and fires a status-changed event
     *
     * @param int $intRecordStatus
     * @param bool $bitFireStatusChangeEvent
     * @return bool
     */
    public function setIntRecordStatus($intRecordStatus, $bitFireStatusChangeEvent = true) {
        $this->internalInit($this->strSystemid);
        $intPrevStatus = $this->intRecordStatus;
        $this->intRecordStatus = $intRecordStatus;

        $bitReturn = true;

        if($intPrevStatus != $intRecordStatus && $intPrevStatus != -1) {
            $this->updateSystemrecord();
            if($bitFireStatusChangeEvent) {
                $bitReturn = class_core_eventdispatcher::notifyStatusChangedListeners($this->getSystemid(), $intRecordStatus);
            }
        }

        return $bitReturn;

    }

    /**
	 * Gets comment saved with the record
	 *
	 * @param string $strSystemid
	 * @return string
	 */
	public function getRecordComment($strSystemid = "") {
        $this->internalInit($this->strSystemid);
        if($strSystemid != "")
            throw new class_exception("unsupported param @ ".__METHOD__, class_exception::$level_FATALERROR);

        return $this->getStrRecordComment();
	}

    /**
	 * Sets the comment saved with a record
	 *
	 * @param string $strNewComment
	 * @return bool
     * @deprecated
	 */
	public function setRecordComment($strNewComment) {
        $this->internalInit($this->strSystemid);
        $this->setStrRecordComment($strNewComment);
        return $this->updateSystemrecord();
	}

    public function getStrRecordComment() {
        $this->internalInit($this->strSystemid);
        return $this->strRecordComment;
    }

    public function setStrRecordComment($strRecordComment) {
        $this->internalInit($this->strSystemid);
        if(uniStrlen($strRecordComment) > 254)
            $strRecordComment = uniStrTrim($strRecordComment, 250);
        $this->strRecordComment = $strRecordComment;
    }

    /**
     * @param string $strRecordClass
     */
    public function setStrRecordClass($strRecordClass) {
        $this->strRecordClass = $strRecordClass;
    }

    /**
     * @return string
     */
    public function getStrRecordClass() {
        return $this->strRecordClass;
    }


    /**
     * Writes a value to the params-array
     *
     * @param string $strKey
     * @param mixed $mixedValue Value
     */
	public function setParam($strKey, $mixedValue) {
        class_carrier::getInstance()->setParam($strKey, $mixedValue);
//		$this->arrParams[$strKey] = $mixedValue;
	}

	/**
	 * Returns a value from the params-Array
	 *
	 * @param string $strKey
	 * @return string else ""
	 */
	public function getParam($strKey) {
        return class_carrier::getInstance()->getParam($strKey);
//		if(isset($this->arrParams[$strKey]))
//			return $this->arrParams[$strKey];
//		else
//			return "";
	}

	/**
	 * Returns the complete Params-Array
	 *
	 * @return mixed
	 */
	public final function getAllParams() {
        return class_carrier::getAllParams();
//	    return $this->arrParams;
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
        return new class_lockmanager($this->getSystemid());
    }


    /**
     * Writes a key-value-pair to the arrModule
     *
     * @param string $strKey
     * @param mixed $strValue
     */
    public function setArrModuleEntry($strKey, $strValue) {
        $this->arrModule[$strKey] = $strValue;
    }



}
