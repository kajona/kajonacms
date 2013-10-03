<?php
/*"******************************************************************************************************
*   (c) 2004-2006 by MulchProductions, www.mulchprod.de                                                 *
*   (c) 2007-2013 by Kajona, www.kajona.de                                                              *
*       Published under the GNU LGPL v2.1, see /system/licence_lgpl.txt                                 *
*-------------------------------------------------------------------------------------------------------*
*	$Id$                         *
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
     *
     * @fieldType text
     * @fieldMandatory
     * @fieldLabel commons_title
     */
    private $strTitle = "";

    /**
     * @var string
     * @tableColumn em_event.em_ev_description
     * @versionable
     * @blockEscaping
     *
     * @fieldType wysiwygsmall
     * @fieldLabel commons_description
     */
    private $strDescription = "";

    /**
     * @var string
     * @tableColumn em_event.em_ev_location
     * @versionable
     *
     * @fieldType textarea
     * @fieldLabel event_location
     */
    private $strLocation = "";

    /**
     * @var int
     * @tableColumn em_event.em_ev_eventstatus
     * @versionable
     *
     * @fieldType dropdown
     * @fieldDDValues [1 => event_status_1],[2 => event_status_2],[3 => event_status_3],[4 => event_status_4]
     */
    private $intEventStatus;

    /**
     * @var int
     * @tableColumn em_event.em_ev_participant_registration
     * @versionable
     *
     * @fieldType yesno
     * @fieldMandatory
     * @fieldLabel event_registration
     */
    private $intRegistrationRequired = 0;

    /**
     * @var int
     * @tableColumn em_event.em_ev_participant_limit
     * @versionable
     *
     * @fieldType yesno
     * @fieldLabel event_limitparticipants
     */
    private $intLimitGiven = 0;

    /**
     * @var int
     * @tableColumn em_event.em_ev_participant_max
     * @versionable
     *
     * @fieldType text
     * @fieldValidator numeric
     * @fieldLabel event_maxparticipants
     */
    private $intParticipantsLimit = 0;

    /**
     * For form generation only
     * @var class_date
     * @versionable
     * @fieldType datetime
     * @fieldLabel event_start
     * @fieldMandatory
     */
    private $objStartDate;

    /**
     * For form-generation only
     * @var class_date
     * @versionable
     * @fieldType datetime
     * @fieldLabel event_end
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
        return "icon_event";
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
            $strCenter .= ", ". class_module_eventmanager_participant::getObjectCount($this->getSystemid())." ".$this->getLang("event_participant");
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

    public function isParticipant($strUserid) {
        return class_module_eventmanager_participant::getParticipantByUserid($strUserid, $this->getSystemid()) !== null;
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
     * @param null $intStatusFilter
     *
     * @return class_module_eventmanager_event[]
     */
    public static function getAllEvents($intStart = false, $intEnd = false, class_date $objStartDate = null, class_date $objEndDate = null, $bitOnlyActive = false, $intOrder = 0, $intStatusFilter = null) {

        $strAddon = "";
        $arrParams = array();
        if($objStartDate != null && $objEndDate != null) {
            $strAddon .= "AND (system_date_start > ? AND system_date_start <= ?) ";
            $arrParams[] = $objStartDate->getLongTimestamp();
            $arrParams[] = $objEndDate->getLongTimestamp();
        }

        if($intStatusFilter != null) {
            $strAddon .= "AND em_ev_eventstatus = ? ";
            $arrParams[] = $intStatusFilter;
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
        if( ($strProperty == "objEndDate" || $strProperty == "objStartDate") && $strValue != "") {
            return dateToString(new class_date($strValue));
        }
        if($strProperty == "limitGiven" || $strProperty == "registrationRequired") {
            return $this->getLang("event_yesno_".$strValue, "eventmanager");
        }
        return $strValue;
    }




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

    public function setIntEventStatus($intEventStatus) {
        $this->intEventStatus = $intEventStatus;
    }

    public function getIntEventStatus() {
        return $this->intEventStatus;
    }


}
