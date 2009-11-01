<?php
/*"******************************************************************************************************
*   (c) 2004-2006 by MulchProductions, www.mulchprod.de                                                 *
*   (c) 2007-2009 by Kajona, www.kajona.de                                                              *
*       Published under the GNU LGPL v2.1, see /system/licence_lgpl.txt                                 *
*-------------------------------------------------------------------------------------------------------*
*   $Id$                                              *
********************************************************************************************************/

/**
 * The top-level class for models, installers and top-level files
 * An instance of this class is used by the admin & portal object to invoke common database based methods.
 * Change with care!
 *
 * @package modul_system
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
	 * Instance of class_toolkit_<area>
	 *
	 * @var class_toolkit_admin
	 */
	protected $objToolkit = null;			//Toolkit-Object
	/**
	 * Instance of class_session
	 *
	 * @var class_session
	 */
	protected $objSession = null;			//Object containting the session-management
	/**
	 * Instance of class_template
	 *
	 * @var class_template
	 */
	protected $objTemplate = null;			//Object to handle templates
	/**
	 * Instance of class_rights
	 *
	 * @var class_rights
	 */
	protected $objRights = null;			//Object handling the right-stuff

	/**
	 * Instance of class_texte
	 *
	 * @var class_texte
	 */
	private   $objText = null;				//Object managing the textfiles

	private   $strAction;			        //current action to perform (GET/POST)
	private   $strSystemid;			        //current systemid
	private   $arrParams;			        //array containing other GET / POST / FILE variables
	private   $strArea;				        //String containing the current Area - admin or portal or installer or download
	protected $arrModule;			        //Array containing Infos about the current modul
	protected $strTemplateArea;		        //String containing the current Area for the templateobject



	/**
	 * Constructor
	 *
	 * @param array $arrModul
	 * @param string $strSystemid
	 * @param string $strArea
	 */
	public function __construct($arrModule, $strSystemid = "", $strArea = "portal") {
		$this->arrModule["r_name"] 			= "class_root";
		$this->arrModule["r_author"] 		= "sidler@mulchprod.de";
		$this->arrModule["r_nummer"] 		= _system_modul_id_;


		//Verifying area
		if($strArea == "installer" || $strArea == "download" || $strArea == "model")
			$this->strArea = $strArea;
		else
			$this->strArea = "portal";


		//Merging Module-Data
		$this->arrModule = array_merge($this->arrModule, $arrModule);


		//GET / POST / FILE Params
		$this->arrParams = getAllPassedParams();
		//Setting SystemID
		if($strSystemid == "") {
			if(isset($this->arrParams["systemid"]))
				$this->setSystemid($this->arrParams["systemid"]);
			else
				$this->strSystemid = "";
		}
		else
			$this->setSystemid($strSystemid);


		//Generating all the needed objects. For this we use our cool cool carrier-object
		//take care of loading just the necessary objects
		$objCarrier = class_carrier::getInstance();
		$this->objConfig = $objCarrier->getObjConfig();
		$this->objDB = $objCarrier->getObjDB();
	    $this->objToolkit = $objCarrier->getObjToolkit($this->strArea);
		$this->objSession = $objCarrier->getObjSession();
   	    $this->objText = $objCarrier->getObjText();
		//a template instance is not needed in case of models
		if($this->strArea != "model")
   	        $this->objTemplate = $objCarrier->getObjTemplate();
		$this->objRights = $objCarrier->getObjRights();

		//Setting template area LATERON THE SKIN IS BEING SET HERE
		$this->setTemplateArea("");

		//And keep the action
		$this->strAction = $this->getParam("action");
	}

	/**
	 * Generates a new SystemRecord and, if needed, the corresponding record in the rights-table (here inherintance is default)
	 * Returns the systemID used for this record
	 *
	 * @param string $strPrevId	Previous ID in the tree-structure
	 * @param string $strComment Comment to indentify the record
	 * @param bool $bitRight Should the right-record be generated?
	 * @param int $intModulNr Number of the module this record belonges to
	 * @param string $strSystemId SystemID to be used
	 * @param string $intStatus	Active (1)/Inactive (0)?
	 * @return string The ID used/generated
	 */
	public function createSystemRecord($strPrevId, $strComment, $bitRight = true, $intModulNr = "", $strSystemId = "", $intStatus = 1) {
		//Do we need a new SystemID?
		if($strSystemId == "")
			$strSystemId = $this->generateSystemid();
		//Given a ModuleNr?
		if($intModulNr == "")
			$intModulNr = $this->arrModule["moduleId"];
		//Correct prevID
		if($strPrevId == "")
			$strPrevId = 0;


		//So, lets generate the record
		$strQuery = "INSERT INTO "._dbprefix_."system
					 ( system_id, system_prev_id, system_module_nr, system_owner, system_lm_user, system_lm_time, system_status, system_comment) VALUES
					 ('".$this->objDB->dbsafeString($strSystemId)."', 
                      '".$this->objDB->dbsafeString($strPrevId)."',
                       ".(int)$intModulNr." ,
                      '".$this->objDB->dbsafeString($this->objSession->getUserID())."',
                      '".$this->objDB->dbsafeString($this->objSession->getUserID())."' ,
                       ".time()." ,
                       ".(int)$intStatus.",
                      '".$this->objDB->dbsafeString($strComment)."')";
        
		//Send the query to the db
		$this->objDB->_query($strQuery);

		//Do we need a Rights-Record?
		if($bitRight) {
			$strQuery = "INSERT INTO "._dbprefix_."system_right
						 (right_id, right_inherit) VALUES
						 ('".$this->objDB->dbsafeString($strSystemId)."', 1)";
            
			$this->objDB->_query($strQuery);
            //update rights to inherit
            $this->objRights->setInherited(true, $strSystemId);
		}

		class_logger::getInstance()->addLogRow("new system-record created: ".$strSystemId ."(".$strComment.")", class_logger::$levelInfo);

		return $strSystemId;

	}

	/**
	 * Creates a record in the date table. Make sure to use a proper system-id!
	 *
	 * @param string $strSystemid
	 * @param int $intStart
	 * @param int $intEnd
	 * @param int $intSpecial
	 * @return bool
	 */
	public function createDateRecord($strSystemid, $intStart = 0, $intEnd = 0, $intSpecial = 0) {
	    $strQuery = "INSERT INTO "._dbprefix_."system_date
	                  (system_date_id, system_date_start, system_date_end, system_date_special) VALUES
	                  ('".$this->objDB->dbsafeString($strSystemid)."', '".(int)$intStart."', '".(int)$intEnd."', '".(int)$intSpecial."')";
	    return $this->objDB->_query($strQuery);
	}

    /**
	 * Updates a record in the date table. Make sure to use a proper system-id!
	 *
	 * @param string $strSystemid
	 * @param int $intStart
	 * @param int $intEnd
	 * @param int $intSpecial
	 * @return bool
	 */
	public function updateDateRecord($strSystemid, $intStart = 0, $intEnd = 0, $intSpecial = 0) {
	    $strQuery = "UPDATE "._dbprefix_."system_date
	                  SET system_date_start = ".(int)$intStart.",
	                      system_date_end = ".(int)$intEnd.",
	                      system_date_special = ".(int)$intSpecial."
	                  WHERE system_date_id = '".$this->objDB->dbsafeString($strSystemid)."'";
	    return $this->objDB->_query($strQuery);
	}


	/**
	 * Writes a value to the params-array
	 *
	 * @param string $strName Key
	 * @param mixed $mixedValue Value
	 */
	public function setParam($strKey, $mixedValue) {
		$this->arrParams[$strKey] = $mixedValue;
	}

	/**
	 * Returns a value from the params-Array
	 *
	 * @param string $strKey
	 * @return string else ""
	 */
	public function getParam($strKey) {
		if(isset($this->arrParams[$strKey]))
			return $this->arrParams[$strKey];
		else
			return "";
	}

	/**
	 * Returns the complete Params-Array
	 *
	 * @return mixed
	 */
	public final function getAllParams() {
	    return $this->arrParams;
	}

	/**
	 * returns the action used for the current request
	 *
	 * @return string
	 */
	public final function getAction() {
	    return (string)$this->strAction;
	}

// --- SystemID & System-Table Methods ------------------------------------------------------------------


	/**
	 * Sets the current SystemID
	 *
	 * @param string $strID
	 * @return bool
	 */
	public function setSystemid($strID) {
		if($this->validateSystemid($strID)) {
			$this->strSystemid = $strID;
			return true;
		}
		else
			return false;
	}

	/**
	 * Checks a systemid for the correct syntax
	 *
	 * @param string $strtID
	 * @return bool
	 */
	public function validateSystemid($strID) {
	    return validateSystemid($strID);
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
	 * Generates a new SystemID
	 *
	 * @return string The new SystemID
	 */
	public function generateSystemid() {
		return generateSystemid();
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
	 * Negates the status of a systemRecord
	 *
	 * @param string $strSystemid
	 * @return bool
	 */
	public function setStatus($strSystemid = "") {
		if($strSystemid == "")
			$strSystemid = $this->getSystemid();

		$intStatus = $this->getStatus($strSystemid);
		if($intStatus == 0)
			$intNewStatus = 1;
		else
			$intNewStatus = 0;
			
		$this->setEditDate($strSystemid);	

		//Upate the record
		$strQuery = "UPDATE "._dbprefix_."system
					SET system_status = ".(int)$intNewStatus."
					WHERE system_id = '".$this->objDB->dbsafeString($strSystemid)."'";
		if($this->objDB->_query($strQuery))
			return true;
		else
			return false;
	}


	/**
	 * Gets the status of a systemRecord
	 *
	 * @param string $strSystemid
	 * @return int
	 */
	public function getStatus($strSystemid = "") {
		if($strSystemid == "0")
			$strSystemid = $this->getSystemid();

		//Get the current status
		$arrRow = $this->getSystemRecord($strSystemid);
		if(count($arrRow) > 1)
			return $arrRow["system_status"];
		else
			return 0;

	}

	/**
	 * Returns the userid locking the record
	 *
	 * @param string $strSystemid
	 * @return string
	 */
	public function getLockId($strSystemid = "") {
		if($strSystemid == "")
			$strSystemid = $this->getSystemid();

		$arrRow = $this->getSystemRecord($strSystemid);
		if($arrRow["system_lock_id"] == "")
			$arrRow["system_lock_id"] = "0";
			
		return $arrRow["system_lock_id"];
	}

	/**
	 * Locks a systemrecord for the current user
	 *
	 * @param string $strSystemid
	 * @return bool
	 */
	public function lockRecord($strSystemid = "") {
		if($strSystemid == "")
			$strSystemid = $this->getSystemid();

		$strQuery = "UPDATE "._dbprefix_."system
						SET system_lock_id='".$this->objDB->dbsafeString($this->objSession->getUserID())."',
						    system_lock_time = '".dbsafeString(time())."'
						WHERE system_id ='".$this->objDB->dbsafeString($strSystemid)."'";
		return $this->objDB->_query($strQuery);
	}

	/**
	 * Unlocks a dataRecord
	 *
	 * @param string $strSystemid
	 * @return bool
	 */
	public function unlockRecord($strSystemid = "")	{
		if($strSystemid == "")
			$strSystemid = $this->getSystemid();

		$strQuery = "UPDATE "._dbprefix_."system
						SET system_lock_id='0'
						WHERE system_id='".$this->objDB->dbsafeString($strSystemid)."'";
		return $this->objDB->_query($strQuery);
	}


	/**
	 * Unlocks records locked passed the defined max-locktime
	 *
	 * @return true
	 */
	public function unlockOldRecords() {
	    $intMinTime = time() - _system_lock_maxtime_;
	    $strQuery = "UPDATE "._dbprefix_."system
						SET system_lock_id='0'
						WHERE system_lock_time <='".$this->objDB->dbsafeString($intMinTime)."'";
	    return $this->objDB->_query($strQuery);
	}


    /**
	 * Gets comment saved with the record
	 *
	 * @param string $strSystemid
	 * @return string
	 */
	public function getRecordComment($strSystemid = "") {
		if($strSystemid == "")
			$strSystemid = $this->getSystemid();

		$arrRow = $this->getSystemRecord($strSystemid);
		if(isset($arrRow["system_comment"]))
			return $arrRow["system_comment"];
		else
			return "n.a.";
	}

	/**
	 * Returns the name of the user who last edited the record
	 *
	 * @param string $strSystemid
	 * @return string
	 */
	public function getLastEditUser($strSystemid = "") {
		if($strSystemid == "")
			$strSystemid = $this->getSystemid();
		$strQuery = "SELECT user_username
					FROM "._dbprefix_."system ,
					"._dbprefix_."user
					WHERE user_id = system_lm_user
						AND system_id = '".$this->objDB->dbsafeString($strSystemid)."'";
		$arrRow = $this->objDB->getRow($strQuery);
		if(count($arrRow) != 0)
		    return $arrRow["user_username"];
		else
		    return "System";
	}

    /**
	 * Returns the id of the user who last edited the record
	 *
	 * @param string $strSystemid
	 * @return string
	 */
	public function getLastEditUserId($strSystemid = "") {
		if($strSystemid == "")
			$strSystemid = $this->getSystemid();

        $arrRow = $this->getSystemRecord($strSystemid);
        return $arrRow["system_lm_user"];
	}
	
    /**
     * Sets the name of the user last editing the current record
     *
     * @param string $strSystemid
     * @return string $strUserid
     */
    public function setLastEditUser($strSystemid = "", $strUserid = "") {
        if($strSystemid == "")
            $strSystemid = $this->getSystemid();
        if($strUserid == "")
            $strUserid = $this->objSession->getUserID();
                
        $strQuery = "UPDATE "._dbprefix_."system 
                        SET system_lm_user = '".dbsafeString($strUserid)."'
                      WHERE system_id = '".dbsafeString($strSystemid)."'";
        return $this->objDB->_query($strQuery);
        
    }

	/**
	 * Returns the time the record was last edited
	 *
	 * @param string $strSystemid
	 * @return int
	 */
	public function getEditDate($strSystemid = "") 	{
		if($strSystemid == "")
			$strSystemid = $this->getSystemid();

		$arrRow = $this->getSystemRecord($strSystemid);
		return $arrRow["system_lm_time"];
	}


	/**
	 * Sets the current date as the edit-date of a system record.
	 * Updates the last-edit-user, too
	 *
	 * @param string $strSystemid
	 * @return bool
	 */
	public function setEditDate($strSystemid = "") {
		if($strSystemid == "")
			$strSystemid = $this->getSystemid();

		$strQuery = "UPDATE "._dbprefix_."system
					SET system_lm_user = '".$this->objDB->dbsafeString($this->objSession->getUserID())."',
						system_lm_time= ".(int)time()."
					WHERE system_id = '".$this->objDB->dbsafeString($strSystemid)."'";

		if($this->objDB->_query($strQuery))
			return true;
		else
			return false;
	}

    /**
     * Gets the id of the user currently being the owner of the record
     *
     * @param string $strSystemid
     * @return string
     */
    public final function getOwnerId($strSystemid = "") {
        if($strSystemid == "")
			$strSystemid = $this->getSystemid();

		$arrRow = $this->getSystemRecord($strSystemid);
		return $arrRow["system_owner"];
    }

    /**
     * Sets the id of the user who owns this record
     *
     * @param string $strOwner
     * @param string $strSystemid
     * @return bool
     */
    public final function setOwnerId($strOwner, $strSystemid = "") {
        if($strSystemid == "")
			$strSystemid = $this->getSystemid();

		$strQuery = "UPDATE "._dbprefix_."system
					SET system_owner = '".$this->objDB->dbsafeString($strOwner)."',
						system_lm_time= ".(int)time()."
					WHERE system_id = '".$this->objDB->dbsafeString($strSystemid)."'";

		if($this->objDB->_query($strQuery))
			return true;
		else
			return false;
    }

	/**
	 * Gets the Prev-ID of a record
	 *
	 * @param string $strSystemid
	 * @return string
	 */
	public function getPrevId($strSystemid = "") {
		if($strSystemid == "")
			$strSystemid = $this->getSystemid();

		$arrRow = $this->getSystemRecord($strSystemid);
		if(isset($arrRow["system_prev_id"]))
			return $arrRow["system_prev_id"];
		else
			return -1;
	}

    /**
	 * Sets the Prev-ID of a record
	 *
	 * @param string $strNewPrevId
	 * @param string $strSystemid If not given, the current objects' systemid is used
	 * @return bool
	 */
	public function setPrevId($strNewPrevId, $strSystemid = "") {
		if($strSystemid == "")
			$strSystemid = $this->getSystemid();

        $this->objDB->flushQueryCache();
        $this->objRights->flushRightsCache();
        $strQuery = "UPDATE "._dbprefix_."system
                        SET system_prev_id='".dbsafeString($strNewPrevId)."'
                      WHERE system_id = '".dbsafeString($strSystemid)."' ";

        $bitReturn = $this->objDB->_query($strQuery);

        if($bitReturn)
            $this->objRights->rebuildRightsStructure($strSystemid);

        return $bitReturn;
	}


	/**
	 * Fetches the number of siblings belonging to the passed systemid
	 *
	 * @param string $strSystemid
	 * @return int
	 */
	public function getNumberOfSiblings($strSystemid = "") {
	    if($strSystemid == "")
			$strSystemid = $this->getSystemid();

	    $strQuery = "SELECT COUNT(*)
					 FROM "._dbprefix_."system as sys1,
					      "._dbprefix_."system as sys2
					 WHERE sys1.system_id='".$this->objDB->dbsafeString($strSystemid)."'
					 AND sys2.system_prev_id = sys1.system_prev_id";
	    $arrRow = $this->objDB->getRow($strQuery);
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
					 WHERE system_prev_id='".$this->objDB->dbsafeString($strSystemid)."'";
        
        $arrReturn = array();
        $arrTemp =  $this->objDB->getArray($strQuery);

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
		$strPrevID = $this->getPrevId($strIdToShift);
		$strQuery = "SELECT *
						 FROM "._dbprefix_."system
						 WHERE system_prev_id='".$this->objDB->dbsafeString($strPrevID)."'
						 ORDER BY system_sort ASC, system_comment ASC";

		//No caching here to allow mutliple shiftings per request
		$arrElements = $this->objDB->getArray($strQuery, false);

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
								SET system_sort=".(((int)$intKey)+1)."
								WHERE system_id='".$this->objDB->dbsafeString($arrOneElement["system_id"])."'";
				$this->objDB->_query($strQuery);
			}
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
		$strReturn = "";

		//to have a better array-like handling, decrease pos by one.
		//remind to add at the end when saving to db
		$intPosition--;

		//Load all elements on the same level, so at first get the prev id
		$strPrevID = $this->getPrevId($strIdToSet);
		$strQuery = "SELECT *
						 FROM "._dbprefix_."system
						 WHERE system_prev_id='".$this->objDB->dbsafeString($strPrevID)."'
						 ORDER BY system_sort ASC, system_comment ASC";

		//No caching here to allow mutliple shiftings per request
		$arrElements = $this->objDB->getArray($strQuery, false);

		//more than one record to set?
		if(count($arrElements) <= 1)
			return;

		//sensless new pos?
		if($intPosition < 0 || $intPosition >= count($arrElements))
		    return;

		//create inital sorts?
		if($arrElements[0]["system_sort"] == 0) {
		    $this->setPosition($arrElements[0]["system_id"], "downwards");
		    $this->setPosition($arrElements[0]["system_id"], "upwards");
		    $this->objDB->flushQueryCache();
		}

		//searching the current element to get to know, if element should be
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
								SET system_sort=".((int)$intPosition+1)."
								WHERE system_id='".dbsafeString($strIdToSet)."'";
			$this->objDB->_query($strQuery);

			//start at the pos to be reached a move all one down
			for($intI = 0; $intI < count($arrElements); $intI++) {
				//move all other one pos down, except the last in the interval:
				//already moved...
				if($intI >= $intPosition && $intI < $intHitKey) {
					$strQuery = "UPDATE "._dbprefix_."system
								SET system_sort=system_sort+1
								WHERE system_id='".dbsafeString($arrElements[$intI]["system_id"])."'";
					$this->objDB->_query($strQuery);
				}
			}
		}

		if($bitSortDown) {
			//move the record to be shifted to the wanted pos
			$strQuery = "UPDATE "._dbprefix_."system
								SET system_sort=".((int)$intPosition+1)."
								WHERE system_id='".dbsafeString($strIdToSet)."'";
			$this->objDB->_query($strQuery);

			//start at the pos to be reached a move all one down
			for($intI = 0; $intI < count($arrElements); $intI++) {
				//move all other one pos down, except the last in the interval:
				//already moved...
				if($intI > $intHitKey && $intI <= $intPosition) {
					$strQuery = "UPDATE "._dbprefix_."system
								SET system_sort=system_sort-1
								WHERE system_id='".dbsafeString($arrElements[$intI]["system_id"])."'";
					$this->objDB->_query($strQuery);
				}
			}
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
					WHERE system_id = '".$this->objDB->dbsafeString($strSystemid)."'";
		return $this->objDB->getRow($strQuery);
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
		$arrModules = $this->objDB->getArray($strQuery, $bitCache);

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
	 */
	public function deleteSystemRecord($strSystemid, $bitRight = true, $bitDate = true) {
		$bit1 = true;
		$bit2 = true;
		$bit3 = true;
		$bit4 = true;

		//try to call other modules, maybe wanting to delete anything in addition, if the current record
		//is going to be deleted
		$bit4 = $this->additionalCallsOnDeletion($strSystemid);

		//Start a tx before deleting anything
		$this->objDB->transactionBegin();

		$strQuery = "DELETE FROM "._dbprefix_."system WHERE system_id = '".$this->objDB->dbsafeString($strSystemid)."'";
		$bit1 = $this->objDB->_query($strQuery);

		if($bitRight) {
			$strQuery = "DELETE FROM "._dbprefix_."system_right WHERE right_id = '".$this->objDB->dbsafeString($strSystemid)."'";
			$bit2 = $this->objDB->_query($strQuery);
		}

        if($bitDate) {
			$strQuery = "DELETE FROM "._dbprefix_."system_date WHERE system_date_id = '".$this->objDB->dbsafeString($strSystemid)."'";
			$bit3 = $this->objDB->_query($strQuery);
		}

		$bitResult = $bit1 && $bit2 && $bit3 && $bit4;

		//end tx
		if($bitResult) {
		    $this->objDB->transactionCommit();
		    class_logger::getInstance()->addLogRow("deleted system-record with id ".$strSystemid, class_logger::$levelInfo);
		}
		else {
		    $this->objDB->transactionRollback();;
		    class_logger::getInstance()->addLogRow("deletion of system-record with id ".$strSystemid." failed", class_logger::$levelWarning);
		}


		return $bitResult;
	}


	/**
	 * Calls other model-classes to be able to do additional cleanups, if a systemrecord is deleted
	 * by invoking class_root::deleteSystemRecord before.
	 * To be called, a model-class has to overwrite class_model::doAdditionalCleanupsOnDeletion
	 *
	 * @param string $strSystemid
	 * @return bool
	 * @see class_root::deleteSystemRecord, class_model::doAdditionalCleanupsOnDeletion
	 */
	protected final function additionalCallsOnDeletion($strSystemid) {
	    $bitReturn = true;

	    //Look up classes extending class_model
	    $objFilesystem = new class_filesystem();
	    $arrFiles = $objFilesystem->getFilelist(_systempath_, array(".php"));

	    foreach ($arrFiles as $strOneFile) {
	        //just match classes starting with "class_modul"
	        if(strpos($strOneFile, "class_modul") !== false) {

	            $strClassname = uniStrReplace(".php", "", $strOneFile);
	            //create instance
	            $objModel = new $strClassname;
	            if ($objModel instanceof class_model) {
	                if(method_exists($objModel, "doAdditionalCleanupsOnDeletion")) {
	                    class_logger::getInstance()->addLogRow("calling ".$strClassname." for additional deletions", class_logger::$levelInfo);
	                    $bitReturn &= $objModel->doAdditionalCleanupsOnDeletion($strSystemid);
	                }
	            }
	        }
	    }

	    return $bitReturn;
	}


	/**
	 * Delets a record from the rights-table
	 *
	 * @param string $strSystemid
	 * @return bool
	 */
	public function deleteRight($strSystemid) {
		$strQuery = "DELETE FROM "._dbprefix_."system_right WHERE right_id = '".$this->objDB->dbsafeString($strSystemid)."'";
		return $this->objDB->_query($strQuery);
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
			$strTempId = $this->getPrevId($strTempId);
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
	 * @param string $strArea
	 * @return string
	 */
	public function getText($strName, $strModule = "", $strArea = "") {
		if($strModule == "")
			$strModule = $this->arrModule["modul"];

		if($strArea == "")
			$strArea = $this->strArea;

		//Now we have to ask the Text-Object to return the text
		return $this->objText->getText($strName, $strModule, $strArea);
	}

	/**
	 * Returns the current Text-Object Instance
	 *
	 * @return obj
	 */
	protected function getObjText() {
	    return $this->objText;
	}

	/**
	 * Sets the current area in the template object to function as expected
	 *
	 * @param string $strArea
	 */
	protected  function setTemplateArea($strArea) {
	    if($this->objTemplate != null)
		    $this->objTemplate->setArea($this->strArea.$strArea);
	}


// --- PageCache Features -------------------------------------------------------------------------------

	/**
	 * Deletes the complete Pages-Cache
	 *
	 * @return bool
	 */
	public function flushCompletePagesCache() {
	    $objPagecache = new class_modul_pages_pagecache();
        return $objPagecache->flushCompletePagesCache();
	}

	/**
	 * Removes one page from the cache
	 *
	 * @param string $strPagename
	 * @return bool
	 */
	public function flushPageFromPagesCache($strPagename) {
	    $objPagecache = new class_modul_pages_pagecache();
	    return $objPagecache->flushPageFromPagesCache($strPagename);
	}


// --- Portal-Language ----------------------------------------------------------------------------------

    /**
     * Returns the language to display contents on the portal
     *
     * @return string 
     */
    public final function getStrPortalLanguage() {
        $objLanguage = new class_modul_languages_language();
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
        $objLanguage = new class_modul_languages_language();
        return $objLanguage->getAdminLanguage();
    }

}
?>