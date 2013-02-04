<?php
/*"******************************************************************************************************
*   (c) 2004-2006 by MulchProductions, www.mulchprod.de                                                 *
*   (c) 2007-2013 by Kajona, www.kajona.de                                                              *
*       Published under the GNU LGPL v2.1, see /system/licence_lgpl.txt                                 *
*-------------------------------------------------------------------------------------------------------*
*	$Id$                              *
********************************************************************************************************/


/**
 * Admin class of the eventmanager-module. Responsible for editing events, participants and organizing them.
 *
 * @package module_eventmanager
 * @author sidler@mulchprod.de
 * @since 3.4
 *
 *
 * @objectList class_module_eventmanager_event
 * @objectNew class_module_eventmanager_event
 * @objectEdit class_module_eventmanager_event
 *
 * @objectListParticipant class_module_eventmanager_participant
 * @objectNewParticipant class_module_eventmanager_participant
 * @objectEditParticipant class_module_eventmanager_participant
 *
 *
 * @autoTestable list,new,newParticipant
 *
 */
class class_module_eventmanager_admin extends class_admin_evensimpler implements interface_admin, interface_calendarsource_admin {

    const STR_CALENDAR_FILTER_EVENT = "STR_CALENDAR_FILTER_EVENT";

    /**
     * Constructor
     */
    public function __construct() {
        $this->setArrModuleEntry("modul", "eventmanager");
        $this->setArrModuleEntry("moduleId", _eventmanager_module_id_);
        parent::__construct();

    }

    public function getOutputModuleNavi() {
        $arrReturn = array();
        $arrReturn[] = array("view", getLinkAdmin($this->getArrModule("modul"), "list", "", $this->getLang("commons_list"), "", "", true, "adminnavi"));
        $arrReturn[] = array("", "");
        $arrReturn[] = array("edit", getLinkAdmin($this->getArrModule("modul"), "newEvent", "", $this->getLang("action_new"), "", "", true, "adminnavi"));
        $arrReturn[] = array("", "");
        $arrReturn[] = array("right", getLinkAdmin("right", "change", "&changemodule=" . $this->arrModule["modul"], $this->getLang("commons_module_permissions"), "", "", true, "adminnavi"));
        return $arrReturn;
    }

    protected function getOutputNaviEntry($objInstance) {

        if($objInstance instanceof class_module_eventmanager_event) {
            return getLinkAdmin($this->getArrModule("modul"), "listParticipant", "&systemid=" . $objInstance->getSystemid(), $objInstance->getStrDisplayName());
        }
        if($objInstance instanceof class_module_eventmanager_participant) {
            return getLinkAdmin($this->getArrModule("modul"), "listParticipant", "&systemid=" . $objInstance->getStrPrevId(), $objInstance->getStrDisplayName());
        }

        return "";
    }


    protected function renderAdditionalActions(class_model $objListEntry) {
        if($objListEntry->rightEdit() && $objListEntry instanceof class_module_eventmanager_event) {
            return array(
                $this->objToolkit->listButton(getLinkAdmin($this->getArrModule("modul"), "listParticipant", "&systemid=" . $objListEntry->getSystemid(), "", $this->getLang("actionListParticipants"), "icon_folderActionOpen.png"))
            );
        }

        if($objListEntry instanceof class_module_eventmanager_participant) {
            if($objListEntry->rightEdit()) {
                $objValidator = new class_email_validator();
                $objEvent = new class_module_eventmanager_event($objListEntry->getPrevId());
                if($objValidator->validate($objListEntry->getStrEmail())) {
                    $strPreset = "&mail_recipient=" . $objListEntry->getStrEmail();
                    $strPreset .= "&mail_subject=" . urlencode($this->getLang("participant_mail_subject"));
                    $strPreset .= "&mail_body=" . urlencode(
                        $this->getLang("participant_mail_intro") . "\n" .
                        $this->getLang("event_title") . " " . $objEvent->getStrTitle() . "\n" .
                        $this->getLang("event_location") . " " . $objEvent->getStrLocation() . "\n" .
                        $this->getLang("event_start") . " " . dateToString($objEvent->getObjStartDate())
                    );
                    return array(
                        $this->objToolkit->listButton(getLinkAdminDialog("system", "mailForm", $strPreset, "", $this->getLang("participant_mail"), "icon_mail.png"))
                    );
                }
            }
        }

        return parent::renderAdditionalActions($objListEntry);
    }

    /**
     * Returns a list of all participants of the event selected before
     *
     * @return string
     * @permissions view
     */
    protected function actionListParticipant() {
        $strReturn = "";
        $objEvent = new class_module_eventmanager_event($this->getSystemid());
        if($objEvent->getIntRegistrationRequired() == "1" && $objEvent->getIntLimitGiven() == "1")
            $strReturn .= $this->objToolkit->getTextRow($this->getLang("participants_info_limit") . $objEvent->getIntParticipantsLimit());
        else
            $strReturn .= $this->objToolkit->getTextRow($this->getLang("participants_info_nolimit"));

        $strReturn .= $this->objToolkit->divider();
        $this->setStrCurObjectTypeName("Participant");
        $this->setCurObjectClassName("class_module_eventmanager_participant");

        $strReturn .= $this->actionList();
        return $strReturn;

    }


    /**
     * @see interface_calendarsource_admin::getArrCalendarEntries()
     */
    public function getArrCalendarEntries(class_date $objStartDate, class_date $objEndDate) {
        $arrEntries = array();

        if($this->objSession->getSession(self::STR_CALENDAR_FILTER_EVENT) != "disabled") {
            $arrEvents = class_module_eventmanager_event::getAllEvents(null, null, $objStartDate, $objEndDate);
            foreach($arrEvents as $objOneEvent) {

                $objEntry = new class_calendarentry();
                $strAlt = $this->getLang("calendar_type_event");

                $strTitle = $objOneEvent->getStrTitle();
                if(uniStrlen($strTitle) > 15) {
                    $strAlt = $strTitle . "<br />" . $strAlt;
                    $strTitle = uniStrTrim($strTitle, 14);
                }

                $strName = getLinkAdmin($this->arrModule["modul"], "edit", "&systemid=" . $objOneEvent->getSystemid(), $strTitle, $strAlt);
                $objEntry->setStrName($strName);
                $arrEntries[] = $objEntry;
            }
        }

        return $arrEntries;
    }

    /**
     * @see interface_calendarsource_admin::getArrLegendEntries()
     */
    public function getArrLegendEntries() {
        return array($this->getLang("calendar_type_event") => "calendarEvent");
    }

    /**
     * @see interface_calendarsource_admin::getArrFilterEntries()
     */
    public function getArrFilterEntries() {
        return array(
            self::STR_CALENDAR_FILTER_EVENT => $this->getLang("calendar_filter_event"),
        );
    }

}

