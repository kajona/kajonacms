<?php
/*"******************************************************************************************************
*   (c) 2004-2006 by MulchProductions, www.mulchprod.de                                                 *
*   (c) 2007-2012 by Kajona, www.kajona.de                                                              *
*       Published under the GNU LGPL v2.1, see /system/licence_lgpl.txt                                 *
*-------------------------------------------------------------------------------------------------------*
*	$Id: class_modul_eventmanager_event.php 4111 2011-09-21 13:15:43Z sidler $                         *
********************************************************************************************************/

/**
 * Business object for a single event. Holds all values to control the event
 *
 * @package module_eventmanager
 * @author sidler@mulchprod.de
 * @since 3.4
 *
 * @targetTable em_event.em_ev_id
 */
class class_module_eventmanager_event extends class_model implements interface_model, interface_versionable, interface_admin_listable  {

    /**
     * @var string
     * @tableColumn em_event.em_ev_title
     * @versionable
     */
    private $strTitle = "";

    /**
     * @var string
     * @tableColumn em_event.em_ev_description
     * @versionable
     * @blockEscaping
     */
    private $strDescription = "";

    /**
     * @var string
     * @tableColumn em_event.em_ev_location
     * @versionable
     */
    private $strLocation = "";

    /**
     * @var int
     * @tableColumn em_event.em_ev_participant_registration
     * @versionable
     */
    private $intRegistrationRequired = 0;

    /**
     * @var int
     * @tableColumn em_event.em_ev_participant_limit
     * @versionable
     */
    private $intLimitGiven = 0;

    /**
     * @var int
     * @tableColumn em_event.em_ev_participant_max
     * @versionable
     */
    private $intParticipantsLimit = 0;

    /**
     * @var int
     * @versionable
     */
    private $objStartDate;

    /**
     * @var int
     * @versionable
     */
    private $objEndDate;



    /**
     * Constructor to create a valid object
     *
     * @param string $strSystemid (use "" on new objects)
     */
    public function __construct($strSystemid = "") {
        $this->setArrModuleEntry("moduleId", _eventmanager_module_id_);
        $this->setArrModuleEntry("modul", "eventmanager");
        parent::__construct($strSystemid);
    }

    /**
     * Returns the icon the be used in lists.
     * Please be aware, that only the filename should be returned, the wrapping by getImageAdmin() is
     * done afterwards.
     *
     * @return string the name of the icon, not yet wrapped by getImageAdmin(). Alternatively, you may return an array containing
     *         [the image name, the alt-title]
     */
    public function getStrIcon() {
        return "icon_event.png";
    }

    /**
     * In nearly all cases, the additional info is rendered left to the action-icons.
     *
     * @return string
     */
    public function getStrAdditionalInfo() {
        $strCenter = "(".dateToString($this->getObjStartDate());
        if($this->getObjEndDate() != null)
            $strCenter .= " - ".dateToString($this->getObjEndDate());

        if($this->getIntRegistrationRequired()) {
            $strCenter .= ", ". class_module_eventmanager_participant::getAllParticipantsCount($this->getSystemid())." ".$this->getLang("event_participant");
        }

        $strCenter .= ")";
        return $strCenter;
    }

    /**
     * If not empty, the returned string is rendered below the common title.
     *
     * @return string
     */
    public function getStrLongDescription() {
        return "";
    }

    /**
     * Returns the name to be used when rendering the current object, e.g. in admin-lists.
     *
     * @return string
     */
    public function getStrDisplayName() {
        return $this->getStrTitle();
    }


    public function initObjectInternal() {
        parent::initObjectInternal();
        $arrRow = $this->getArrInitRow();

        if($arrRow["system_date_start"] > 0) {
            $this->setObjStartDate(new class_date($arrRow["system_date_start"]));
        }

        if($arrRow["system_date_end"] > 0) {
            $this->setObjEndDate(new class_date($arrRow["system_date_end"]));
        }

    }

    /**
     * saves the current object with all its params back to the database
     *
     * @return bool
     */
    protected function updateStateToDb() {
        $this->updateDateRecord($this->getSystemid(), $this->getObjStartDate(), $this->getObjEndDate());
        return parent::updateStateToDb();


    }

    /**
     * Creates the initial date-entries
     */
    protected function onInsertToDb() {
        return $this->createDateRecord($this->getSystemid());
    }

    /**
     * Returns a list of events available
     *
     * @param bool|int $intStart
     * @param bool|int $intEnd
     * @param class_date $objStartDate
     * @param class_Date $objEndDate
     * @param bool $bitOnlyActive
     * @param int $intOrder
     *
     * @return class_module_eventmanager_event[]
     */
    public static function getAllEvents($intStart = false, $intEnd = false, class_date $objStartDate = null, class_date $objEndDate = null, $bitOnlyActive = false, $intOrder = 0) {

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
        $arrQuery = class_carrier::getInstance()->getObjDB()->getPArray($strQuery, $arrParams, $intStart, $intEnd);

        $arrReturn = array();
        foreach($arrQuery as $arrSingleRow)
            $arrReturn[] = new class_module_eventmanager_event($arrSingleRow["system_id"]);

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

    /**
     * Returns a human readable name of the action stored with the changeset.
     *
     * @param string $strAction the technical actionname
     *
     * @return string the human readable name
     */
    public function getVersionActionName($strAction) {
        if($strAction == class_module_system_changelog::$STR_ACTION_EDIT)
            return $this->getLang("event_edit");

        return $strAction;
    }

    /**
     * Returns a human readable name of the record / object stored with the changeset.
     *
     * @return string the human readable name
     */
    public function getVersionRecordName() {
        return $this->getLang("change_object_participant");
    }

    /**
     * Returns a human readable name of the property-name stored with the changeset.
     *
     * @param string $strProperty the technical property-name
     *
     * @return string the human readable name
     */
    public function getVersionPropertyName($strProperty) {
        return $strProperty;
    }

    /**
     * Renders a stored value. Allows the class to modify the value to display, e.g. to
     * replace a timestamp by a readable string.
     *
     * @param string $strProperty
     * @param string $strValue
     *
     * @return string
     */
    public function renderVersionValue($strProperty, $strValue) {
        if( ($strProperty == "enddate" || $strProperty == "startdate") && $strValue != "") {
            return dateToString(new class_date($strValue));
        }
        if($strProperty == "limitGiven" || $strProperty == "registrationRequired") {
            return $this->getLang("event_yesno_".$strValue, "eventmanager");
        }
        return $strValue;
    }




    /**
     * @return string
     * @fieldType text
     * @fieldMandatory
     * @fieldLabel commons_title
     */
    public function getStrTitle() {
        return $this->strTitle;
    }

    public function setStrTitle($strTitle) {
        $this->strTitle = $strTitle;
    }

    /**
     * @return string
     * @fieldType wysiwygsmall
     * @fieldLabel commons_description
     */
    public function getStrDescription() {
        return $this->strDescription;
    }

    public function setStrDescription($strDescription) {
        $this->strDescription = $strDescription;
    }

    /**
     * @return string
     * @fieldType textarea
     * @fieldLabel event_location
     */
    public function getStrLocation() {
        return $this->strLocation;
    }

    public function setStrLocation($strLocation) {
        $this->strLocation = $strLocation;
    }

    /**
     * @return string
     * @fieldType yesno
     * @fieldMandatory
     * @fieldLabel event_registration
     */
    public function getIntRegistrationRequired() {
        return $this->intRegistrationRequired;
    }

    public function setIntRegistrationRequired($intRegistration) {
        $this->intRegistrationRequired = $intRegistration;
    }

    /**
     * @return string
     * @fieldType yesno
     * @fieldLabel event_limitparticipants
     */
    public function getIntLimitGiven() {
        return $this->intLimitGiven;
    }

    public function setIntLimitGiven($intLimitGiven) {
        $this->intLimitGiven = $intLimitGiven;
    }

    /**
     * @return string
     * @fieldType text
     * @fieldValidator numeric
     * @fieldLabel event_maxparticipants
     */
    public function getIntParticipantsLimit() {
        return $this->intParticipantsLimit;
    }

    public function setIntParticipantsLimit($intParticipantsLimit) {
        $this->intParticipantsLimit = (int)$intParticipantsLimit;
    }


    /**
     * @return class_date
     * @fieldType datetime
     * @fieldMandatory
     * @fieldLabel event_start
     */
    public function getLongStartDate() {
        if($this->objStartDate instanceof class_date)
            return $this->objStartDate->getLongTimestamp();
        return "";
    }

    public function setLongStartDate($longStartDate) {
        if($longStartDate != "")
            $this->objStartDate = new class_date($longStartDate);
        else
            $this->objStartDate = null;

    }

    public function getObjStartDate() {
        return $this->objStartDate;
    }

    public function setObjStartDate($objStartDate) {
        $this->objStartDate = $objStartDate;
    }

    /**
     * @return class_date
     * @fieldType datetime
     * @fieldLabel event_end
     */
    public function getLongEndDate() {
        if($this->objEndDate instanceof class_date)
            return $this->objEndDate->getLongTimestamp();
        return "";
    }

    public function setLongEndDate($longEndDate) {
        if($longEndDate != "")
            $this->objEndDate = new class_date($longEndDate);
        else
            $this->objEndDate = null;
    }

    public function getObjEndDate() {
        return $this->objEndDate;
    }

    public function setObjEndDate($objEndDate) {
        $this->objEndDate = $objEndDate;
    }


    
}
