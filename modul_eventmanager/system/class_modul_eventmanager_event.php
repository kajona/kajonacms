<?php
/*"******************************************************************************************************
*   (c) 2004-2006 by MulchProductions, www.mulchprod.de                                                 *
*   (c) 2007-2011 by Kajona, www.kajona.de                                                              *
*       Published under the GNU LGPL v2.1, see /system/licence_lgpl.txt                                 *
*-------------------------------------------------------------------------------------------------------*
*	$Id$                         *
********************************************************************************************************/

/**
 * Business object for a single event. Holds all values to control the event
 *
 * @package modul_eventmanager
 * @author sidler@mulchprod.de
 * @since 3.4
 */
class class_modul_eventmanager_event extends class_model implements interface_model, interface_versionable  {

    private $strActionEdit = "edit";

    private $strTitle = "";
    private $strDescription = "";
    private $strLocation = "";
    private $intRegistrationRequired = 0;
    private $intLimitGiven = 0;
    private $intParticipantsLimit = 0;

    private $objStartDate;
    private $objEndDate;


    private $strOldTitle = "";
    private $strOldDescription = "";
    private $strOldLocation = "";
    private $intOldRegistrationRequired = 0;
    private $intOldLimitGiven = 0;
    private $intOldParticipantsLimit = 0;

    private $objOldStartDate;
    private $objOldEndDate;

    /**
     * Constructor to create a valid object
     *
     * @param string $strSystemid (use "" on new objects)
     */
    public function __construct($strSystemid = "") {
        $arrModul = array();
        $arrModul["name"] 				= "modul_eventmanager";
		$arrModul["moduleId"] 			= _eventmanager_modul_id_;
		$arrModul["modul"]				= "eventmanager";
		$arrModul["table"]				= _dbprefix_."em_event";

		//base class
		parent::__construct($arrModul, $strSystemid);

		//init current object
		if($strSystemid != "")
		    $this->initObject();
    }

    /**
     * @see class_model::getObjectTables();
     * @return array
     */
    protected function getObjectTables() {
        return array(_dbprefix_."em_event" => "em_ev_id");
    }

    /**
     * @see class_model::getObjectDescription();
     * @return string
     */
    protected function getObjectDescription() {
        return "eventmanager event ".$this->getStrTitle();
    }

    /**
     * Initalises the current object, if a systemid was given
     *
     */
    public function initObject() {
         $strQuery = "SELECT * 
                        FROM ".$this->arrModule["table"].",
                             "._dbprefix_."system,
                             "._dbprefix_."system_date
	                   WHERE em_ev_id = ?
                         AND system_id = system_date_id
                         AND system_id = em_ev_id ";

         $arrRow = $this->objDB->getPRow($strQuery, array($this->getSystemid()));
         if(count($arrRow) > 0) {
             $this->setStrTitle($arrRow["em_ev_title"]);
             $this->setStrDescription($arrRow["em_ev_description"]);
             $this->setStrLocation($arrRow["em_ev_location"]);
             $this->setIntRegistrationRequired($arrRow["em_ev_participant_registration"]);
             $this->setIntLimitGiven($arrRow["em_ev_participant_limit"]);
             $this->setIntParticipantsLimit($arrRow["em_ev_participant_max"]);

             if($arrRow["system_date_start"] > 0)
                 $this->setObjStartDate(new class_date($arrRow["system_date_start"]));

             if($arrRow["system_date_end"] > 0)
                 $this->setObjEndDate(new class_date($arrRow["system_date_end"]));


             $this->strOldTitle = $this->strTitle;
             $this->strOldDescription = $this->strDescription;
             $this->strOldLocation = $this->strLocation;
             $this->intOldRegistrationRequired = $this->intRegistrationRequired;
             $this->intOldLimitGiven = $this->intLimitGiven;
             $this->intOldParticipantsLimit = $this->intParticipantsLimit;

             $this->objOldStartDate = $this->objStartDate;
             $this->objOldEndDate = $this->objEndDate;
         }
    }

    /**
     * saves the current object with all its params back to the database
     *
     * @return bool
     */
    protected function updateStateToDb() {
        class_logger::getInstance()->addLogRow("updated ".$this->getObjectDescription(), class_logger::$levelInfo);

        //create change-logs
        $objChanges = new class_modul_system_changelog();
        $objChanges->createLogEntry($this, $this->strActionEdit);

        $this->updateDateRecord($this->getSystemid(), $this->getObjStartDate(), $this->getObjEndDate());

        $strQuery = "UPDATE ".$this->arrModule["table"]."
                        SET em_ev_title = ?,
                            em_ev_description = ?,
                            em_ev_location = ?,
                            em_ev_participant_registration = ?,
                            em_ev_participant_limit = ?,
                            em_ev_participant_max = ?
                      WHERE em_ev_id = ?";
        
        return $this->objDB->_pQuery($strQuery, array(
            $this->getStrTitle(),
            $this->getStrDescription(),
            $this->getStrLocation(),
            $this->getIntRegistrationRequired(),
            $this->getIntLimitGiven(),
            $this->getIntParticipantsLimit(),
            $this->getSystemid()
        ), array(true, false, true, true, true, true, true));
    }

    /**
     * Creates the initial date-entries
     */
    protected function onInsertToDb() {
        return $this->createDateRecord($this->getSystemid());
    }
    

	/**
	 * Deletes the current event and all participants from the database
	 *
	 * @param string $strSystemid
	 * @return bool
	 */
	public function deleteEvent() {

        $arrParticipants = class_modul_eventmanager_participant::getAllParticipants($this->getSystemid());
        foreach($arrParticipants as $objOneParticipant) {
            $objOneParticipant->deleteParticipant();
        }

	    class_logger::getInstance()->addLogRow("deleted ".$this->getObjectDescription(), class_logger::$levelInfo);
        $strQuery = "DELETE FROM ".$this->arrModule["table"]." WHERE em_ev_id = ?";
        if($this->objDB->_pQuery($strQuery, array($this->getSystemid()))) {
            if($this->deleteSystemRecord($this->getSystemid()))
                return true;
        }
	    return false;
	}

    /**
     * Returns a list of events available
     *
     * @param int $intStart
     * @param int $intEnd
     * @param class_date $objStartDate
     * @param class_Date $objEndDate
     * @param bool $bitOnlyActive
     * @param int $intOrder
     * @return class_modul_eventmanager_event
     */
    public static function getAllEvents($intStart = null, $intEnd = null, class_date $objStartDate = null, class_date $objEndDate = null, $bitOnlyActive = false, $intOrder = 0) {

        $strAddon = "";
        $arrParams = array();
        if($objStartDate != null && $objEndDate != null) {
            $strAddon = "AND (system_date_start > ? AND system_date_start <= ?) ";
            $arrParams[] = $objStartDate->getLongTimestamp();
            $arrParams[] = $objEndDate->getLongTimestamp();
        }

        $strQuery = "SELECT system_id
                       FROM "._dbprefix_."em_event,
                            "._dbprefix_."system,
                            "._dbprefix_."system_date
                      WHERE system_id = em_ev_id
                        AND system_id = system_date_id
                        ".$strAddon."
                        ".($bitOnlyActive ? " AND system_status = 1 " : "")."    
                      ORDER BY system_date_start ".($intOrder == "1" ? " ASC " : " DESC ").", em_ev_title ASC";
        $arrQuery = class_carrier::getInstance()->getObjDB()->getPArray($strQuery, $arrParams);

        $arrReturn = array();
        foreach($arrQuery as $arrSingleRow)
            $arrReturn[] = new class_modul_eventmanager_event($arrSingleRow["system_id"]);

        return $arrReturn;
    }

    /**
     * Returns the total number of events available
     * @return int
     */
    public static function getAllEventsCount() {
        $strQuery = "SELECT COUNT(*)
                       FROM "._dbprefix_."em_event,
                            "._dbprefix_."system,
                            "._dbprefix_."system_date
                      WHERE system_id = em_ev_id
                        AND system_id = system_date_id
                      ORDER BY system_date_start DESC, em_ev_title ASC";
        $arrRow = class_carrier::getInstance()->getObjDB()->getPRow($strQuery, array());
        return $arrRow["COUNT(*)"];
    }




    public function getActionName($strAction) {
        if($strAction == $this->strActionEdit)
            return $this->getText("event_edit", "eventmanager", "admin");

        return $strAction;
    }

    public function getChangedFields($strAction) {
        if($strAction == $this->strActionEdit) {
            return array(
                array("property" => "limitGiven",  "oldvalue" => $this->intOldLimitGiven, "newvalue" => $this->getIntLimitGiven()),
                array("property" => "maxParticipants",  "oldvalue" => $this->intOldParticipantsLimit, "newvalue" => $this->getIntParticipantsLimit()),
                array("property" => "registrationRequired",  "oldvalue" => $this->intOldRegistrationRequired, "newvalue" => $this->getIntRegistrationRequired()),
                array("property" => "location",  "oldvalue" => $this->strOldLocation, "newvalue" => $this->getStrLocation()),
                array("property" => "title",  "oldvalue" => $this->strOldTitle, "newvalue" => $this->getStrTitle()),
                array("property" => "description",  "oldvalue" => $this->strOldDescription, "newvalue" => $this->getStrDescription()),
                array("property" => "startdate",  "oldvalue" => $this->objOldStartDate, "newvalue" => $this->getObjStartDate()),
                array("property" => "enddate",  "oldvalue" => $this->objOldEndDate, "newvalue" => $this->getObjEndDate())
            );
        }
    }

    public function renderValue($strProperty, $strValue) {
        if( ($strProperty == "enddate" || $strProperty == "startdate") && $strValue != "") {
            return dateToString(new class_date($strValue));
        }
        if($strProperty == "limitGiven" || $strProperty == "registrationRequired") {
            return class_carrier::getInstance()->getObjText()->getText("event_yesno_".$strValue, "eventmanager", "admin");
        }
        return $strValue;
    }

    public function getClassname() {
        return __CLASS__;
    }

    public function getModuleName() {
        return $this->arrModule["modul"];
    }

    public function getPropertyName($strProperty) {
        return $strProperty;
    }

    public function getRecordName() {
        return class_carrier::getInstance()->getObjText()->getText("change_object_participant", "eventmanager", "admin");
    }

// --- GETTERS / SETTERS --------------------------------------------------------------------------------

    public function getStrTitle() {
        return $this->strTitle;
    }

    public function setStrTitle($strTitle) {
        $this->strTitle = $strTitle;
    }

    public function getStrDescription() {
        return $this->strDescription;
    }

    public function setStrDescription($strDescription) {
        $this->strDescription = $strDescription;
    }

    public function getStrLocation() {
        return $this->strLocation;
    }

    public function setStrLocation($strLocation) {
        $this->strLocation = $strLocation;
    }

    public function getIntRegistrationRequired() {
        return $this->intRegistrationRequired;
    }

    public function setIntRegistrationRequired($intRegistration) {
        $this->intRegistrationRequired = $intRegistration;
    }

    public function getIntLimitGiven() {
        return $this->intLimitGiven;
    }

    public function setIntLimitGiven($intLimitGiven) {
        $this->intLimitGiven = $intLimitGiven;
    }

    public function getIntParticipantsLimit() {
        return $this->intParticipantsLimit;
    }

    public function setIntParticipantsLimit($intParticipantsLimit) {
        $this->intParticipantsLimit = (int)$intParticipantsLimit;
    }

    /**
     *
     * @return class_date
     */
    public function getObjStartDate() {
        return $this->objStartDate;
    }

    public function setObjStartDate($objStartDate) {
        $this->objStartDate = $objStartDate;
    }

    /**
     *
     * @return class_date
     */
    public function getObjEndDate() {
        return $this->objEndDate;
    }

    public function setObjEndDate($objEndDate) {
        $this->objEndDate = $objEndDate;
    }


    
}
?>