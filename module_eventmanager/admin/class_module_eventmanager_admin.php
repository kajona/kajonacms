<?php
/*"******************************************************************************************************
*   (c) 2004-2006 by MulchProductions, www.mulchprod.de                                                 *
*   (c) 2007-2012 by Kajona, www.kajona.de                                                              *
*       Published under the GNU LGPL v2.1, see /system/licence_lgpl.txt                                 *
*-------------------------------------------------------------------------------------------------------*
*	$Id: class_modul_eventmanager_admin.php 3998 2011-07-15 12:18:29Z sidler $                              *
********************************************************************************************************/


/**
 * Admin class of the eventmanager-module. Responsible for editing events, participants and organizing them.
 *
 * @package module_eventmanager
 * @author sidler@mulchprod.de
 * @since 3.4
 */
class class_module_eventmanager_admin extends class_admin_simple implements interface_admin, interface_calendarsource_admin {

    public static $STR_CALENDAR_FILTER_EVENT = "STR_CALENDAR_FILTER_EVENT";

    const STR_LIST_PARTICPANTS = "STR_LIST_PARTICIPANTS";

	/**
	 * Constructor
	 *
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
        $arrReturn[] = array("edit", getLinkAdmin($this->getArrModule("modul"), "newEvent", "", $this->getLang("actionNewEvent"), "", "", true, "adminnavi"));
        $arrReturn[] = array("", "");
        $arrReturn[] = array("right", getLinkAdmin("right", "change", "&changemodule=".$this->arrModule["modul"],  $this->getLang("commons_module_permissions"), "", "", true, "adminnavi"));
        return $arrReturn;
	}

    protected function getArrOutputNaviEntries() {
        $arrPathLinks = parent::getArrOutputNaviEntries();
        $arrPath = $this->getPathArray($this->getSystemid());
        //Link to root-folder
        foreach($arrPath as $strOneElement) {

            $objInstance = class_objectfactory::getInstance()->getObject($strOneElement);

            if($objInstance instanceof class_module_eventmanager_event) {
                $arrPathLinks[] = getLinkAdmin($this->getArrModule("modul"), "listParticipants", "&systemid=".$strOneElement, $objInstance->getStrDisplayName());
            }
            if($objInstance instanceof class_module_eventmanager_participant) {
                $arrPathLinks[] = getLinkAdmin($this->getArrModule("modul"), "listParticipants", "&systemid=".$objInstance->getStrPrevId(), $objInstance->getStrDisplayName());
            }
        }

        return $arrPathLinks;
    }

    /**
     * Renders the form to create a new entry
     *
     * @return string
     * @permissions edit
     */
    protected function actionNew() {
        return $this->actionNewEvent();
    }

    /**
     * Renders the form to edit an existing entry
     *
     * @return string
     * @permissions edit
     */
    protected function actionEdit() {
        $objObject = class_objectfactory::getInstance()->getObject($this->getSystemid());
        if($objObject->rightEdit() && $objObject instanceof class_module_eventmanager_event)
            $this->adminReload(getLinkAdminHref($this->getArrModule("modul"), "editEvent", "&systemid=".$objObject->getSystemid()));

        if($objObject->rightEdit() && $objObject instanceof class_module_eventmanager_participant)
            $this->adminReload(getLinkAdminHref($this->getArrModule("modul"), "editParticipant", "&systemid=".$objObject->getSystemid()));

        return "";
    }

    protected function renderDeleteAction(interface_model $objListEntry) {
        if($objListEntry instanceof class_module_eventmanager_participant) {
            if($objListEntry->rightDelete()) {
                return $this->objToolkit->listDeleteButton(
                    $objListEntry->getStrDisplayName(),
                    $this->getLang("participant_delete_question", $this->getArrModule("modul")),
                    getLinkAdminHref($this->getArrModule("modul"), "delete", "&systemid=".$objListEntry->getSystemid())
                );
            }
        }
        else
            return parent::renderDeleteAction($objListEntry);

        return "";
    }


    protected function renderAdditionalActions(class_model $objListEntry) {
        if($objListEntry->rightEdit() && $objListEntry instanceof class_module_eventmanager_event) {
            return array(
                $this->objToolkit->listButton(getLinkAdmin($this->getArrModule("modul"), "listParticipants", "&systemid=".$objListEntry->getSystemid(), "", $this->getLang("actionListParticipants"), "icon_folderActionOpen.png"))
            );
        }

        if($objListEntry instanceof class_module_eventmanager_participant) {
            if($objListEntry->rightEdit()) {
                $objValidator = new class_email_validator();
                $objEvent = new class_module_eventmanager_event($objListEntry->getPrevId());
                if($objValidator->validate($objListEntry->getStrEmail()) ) {
                    $strPreset  = "&mail_recipient=".$objListEntry->getStrEmail();
                    $strPreset .= "&mail_subject=".urlencode($this->getLang("participant_mail_subject"));
                    $strPreset .= "&mail_body=".urlencode($this->getLang("participant_mail_intro")."\n".
                            $this->getLang("event_title")." ".$objEvent->getStrTitle()."\n".
                            $this->getLang("event_location")." ".$objEvent->getStrLocation()."\n".
                            $this->getLang("event_start")." ".  dateToString($objEvent->getObjStartDate())
                    );
                    return array(
                        $this->objToolkit->listButton(getLinkAdminDialog("system", "mailForm", $strPreset, "", $this->getLang("participant_mail"), "icon_mail.png"))
                    );
                }
            }
        }

        return parent::renderAdditionalActions($objListEntry);
    }

    protected function getNewEntryAction($strListIdentifier, $bitDialog = false) {
        if($strListIdentifier == self::STR_LIST_PARTICPANTS) {
            if($this->getObjModule()->rightEdit()) {
                return array(
                    getLinkAdmin($this->getArrModule("modul"), "newParticipant", "&systemid=".$this->getSystemid(), $this->getLang("actionNewParticipant"), $this->getLang("actionNewParticipant"), "icon_new.png")
                );
            }
        }
        else
            return parent::getNewEntryAction($strListIdentifier, $bitDialog);

        return array();
    }


    /**
	 * Returns a list of all events currently available
	 *
	 * @return string
     * @permissions view
     * @autoTestable
	 */
	protected function actionList() {

        $objArraySectionIterator = new class_array_section_iterator(class_module_eventmanager_event::getObjectCount());
        $objArraySectionIterator->setPageNumber((int)($this->getParam("pv") != "" ? $this->getParam("pv") : 1));
        $objArraySectionIterator->setArraySection(class_module_eventmanager_event::getAllEvents($objArraySectionIterator->calculateStartPos(), $objArraySectionIterator->calculateEndPos()));

        return $this->renderList($objArraySectionIterator);
	}

    /**
     * @return string
     * @permissions edit
     */
    protected function actionEditEvent() {
        return $this->actionNewEvent("edit");
    }


    /**
     * Show the form to create or edit an event
     *
     * @param string $strMode
     * @param class_admin_formgenerator $objForm
     *
     * @return string
     * @permissions edit
     * @autoTestable
     */
    protected function actionNewEvent($strMode = "new", class_admin_formgenerator $objForm = null) {

        $objEvent = new class_module_eventmanager_event();
        if($strMode == "edit") {
            $objEvent = new class_module_eventmanager_event($this->getSystemid());

            if(!$objEvent->rightEdit())
                return $this->getLang("commons_error_permissions");
        }

        if($objForm == null)
            $objForm = $this->getEventAdminForm($objEvent);

        $objForm->addField(new class_formentry_hidden("", "mode"))->setStrValue($strMode);
        return $objForm->renderForm(getLinkAdminHref($this->getArrModule("modul"), "saveEvent"));
    }


    private function getEventAdminForm(class_module_eventmanager_event $objEvent) {
        $objForm = new class_admin_formgenerator("event", $objEvent);
        $objForm->generateFieldsFromObject();
        return $objForm;
    }

    /**
     * Saves the passed values as a new category to the db
     *
     * @return string "" in case of success
     * @permissions edit
     */
    protected function actionSaveEvent() {
        $objEvent = null;

        if($this->getParam("mode") == "new")
            $objEvent = new class_module_eventmanager_event();

        else if($this->getParam("mode") == "edit")
            $objEvent = new class_module_eventmanager_event($this->getSystemid());

        if($objEvent != null) {
            $objForm = $this->getEventAdminForm($objEvent);
            if(!$objForm->validateForm())
                return $this->actionNewEvent($this->getParam("mode"), $objForm);

            $objForm->updateSourceObject();
            $objEvent->updateObjectToDb();

            $this->adminReload(getLinkAdminHref($this->getArrModule("modul"), "", ($this->getParam("pe") != "" ? "&peClose=1" : "")));
            return "";
        }

        return $this->getLang("commons_error_permissions");
    }








    /**
	 * Returns a list of all participants of the event selected before
	 *
	 * @return string
     * @permissions view
	 */
	protected function actionListParticipants() {
        $strReturn = "";
        $objEvent = new class_module_eventmanager_event($this->getSystemid());
        if($objEvent->getIntRegistrationRequired() == "1" && $objEvent->getIntLimitGiven() == "1") {
            $strReturn .= $this->objToolkit->getTextRow($this->getLang("participants_info_limit").$objEvent->getIntParticipantsLimit());
        }
        else {
            $strReturn .= $this->objToolkit->getTextRow($this->getLang("participants_info_nolimit"));
        }
        $strReturn .= $this->objToolkit->divider();

        $objArraySectionIterator = new class_array_section_iterator(class_module_eventmanager_participant::getObjectCount($this->getSystemid()));
        $objArraySectionIterator->setPageNumber((int)($this->getParam("pv") != "" ? $this->getParam("pv") : 1));
        $objArraySectionIterator->setArraySection(class_module_eventmanager_participant::getAllParticipants($this->getSystemid(), $objArraySectionIterator->calculateStartPos(), $objArraySectionIterator->calculateEndPos()));

        $strReturn .= $this->renderList($objArraySectionIterator, false, self::STR_LIST_PARTICPANTS);
        return $strReturn;

	}


    protected function actionEditParticipant() {
        return $this->actionNewParticipant("edit");
    }


    /**
     * Show the form to create or edit a participant
     *
     * @param string $strMode
     * @param class_admin_formgenerator $objForm
     *
     * @return string
     * @permissions edit
     * @autoTestable
     */
    protected function actionNewParticipant($strMode = "new", class_admin_formgenerator $objForm = null) {

        $objParticipant = new class_module_eventmanager_participant();
        if($strMode == "edit") {
            $objParticipant = new class_module_eventmanager_participant($this->getSystemid());

            if(!$objParticipant->rightEdit())
                return $this->getLang("commons_error_permissions");
        }

        if($objForm == null)
            $objForm = $this->getParticipantAdminForm($objParticipant);

        if(!validateSystemid($this->getParam("eventid"))) {
            if($strMode == "new")
                $this->setParam("eventid", $this->getSystemid());
            else
                $this->setParam("eventid", $objParticipant->getPrevId());
        }

        $objForm->addField(new class_formentry_hidden("", "mode"))->setStrValue($strMode);
        $objForm->addField(new class_formentry_hidden("", "eventid"))->setStrValue($this->getParam("eventid"));
        return $objForm->renderForm(getLinkAdminHref($this->getArrModule("modul"), "saveParticipant"));
    }


    private function getParticipantAdminForm(class_module_eventmanager_participant $objEvent) {
        $objForm = new class_admin_formgenerator("participant", $objEvent);
        $objForm->generateFieldsFromObject();
        return $objForm;
    }

    /**
     * Saves the passed values as a new category to the db
     *
     * @return string "" in case of success
     * @permissions edit
     */
    protected function actionSaveParticipant() {
        $objParticipant = null;

        if($this->getParam("mode") == "new")
            $objParticipant = new class_module_eventmanager_participant();

        else if($this->getParam("mode") == "edit")
            $objParticipant = new class_module_eventmanager_participant($this->getSystemid());

        if($objParticipant != null) {
            $objForm = $this->getParticipantAdminForm($objParticipant);
            if(!$objForm->validateForm())
                return $this->actionNewParticipant($this->getParam("mode"), $objForm);

            $objForm->updateSourceObject();
            $objParticipant->updateObjectToDb($this->getParam("eventid"));

            $this->adminReload(getLinkAdminHref($this->getArrModule("modul"), "listParticipants", "&systemid=".$this->getParam("eventid").($this->getParam("pe") != "" ? "&peClose=1" : "")));
            return "";
        }

        return $this->getLang("commons_error_permissions");
    }





    /**
     *
     * @see interface_calendarsource_admin::getArrCalendarEntries()
     */
    public function getArrCalendarEntries(class_date $objStartDate, class_date $objEndDate) {
        $arrEntries = array();


        if($this->objSession->getSession(self::$STR_CALENDAR_FILTER_EVENT) != "disabled") {
            $arrEvents = class_module_eventmanager_event::getAllEvents(null, null, $objStartDate, $objEndDate);
            foreach($arrEvents as $objOneEvent) {

                $objEntry = new class_calendarentry();
                $strAlt = $this->getLang("calendar_type_event");

                $strTitle = $objOneEvent->getStrTitle();
                if(uniStrlen($strTitle)> 15) {
                    $strAlt = $strTitle."<br />".$strAlt;
                    $strTitle = uniStrTrim($strTitle, 14);
                }

                $strName = getLinkAdmin($this->arrModule["modul"], "editEvent", "&systemid=".$objOneEvent->getSystemid(), $strTitle, $strAlt);
                $objEntry->setStrName($strName);
                $arrEntries[] = $objEntry;
            }
        }


        return $arrEntries;
    }

    /**
     *
     * @see interface_calendarsource_admin::getArrLegendEntries()
     */
    public function getArrLegendEntries() {
        return array($this->getLang("calendar_type_event") => "calendarEvent");
    }

    /**
     *
     * @see interface_calendarsource_admin::getArrFilterEntries()
     */
    public function getArrFilterEntries() {
        return array(
            self::$STR_CALENDAR_FILTER_EVENT => $this->getLang("calendar_filter_event"),
        );
    }

}

