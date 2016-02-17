<?php
/*"******************************************************************************************************
*   (c) 2004-2006 by MulchProductions, www.mulchprod.de                                                 *
*   (c) 2007-2015 by Kajona, www.kajona.de                                                              *
*       Published under the GNU LGPL v2.1, see /system/licence_lgpl.txt                                 *
********************************************************************************************************/
use Kajona\System\System\ModelInterface;


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
 * @module eventmanager
 * @moduleId _eventmanager_module_id_
 *
 */
class class_module_eventmanager_admin extends class_admin_evensimpler implements interface_admin {

    const STR_CALENDAR_FILTER_EVENT = "STR_CALENDAR_FILTER_EVENT";

    public function getOutputModuleNavi() {
        $arrReturn = array();
        $arrReturn[] = array("view", getLinkAdmin($this->getArrModule("modul"), "list", "", $this->getLang("commons_list"), "", "", true, "adminnavi"));
        return $arrReturn;
    }

    protected function getOutputNaviEntry(ModelInterface $objInstance) {

        if($objInstance instanceof class_module_eventmanager_event) {
            return getLinkAdmin($this->getArrModule("modul"), "listParticipant", "&systemid=" . $objInstance->getSystemid(), $objInstance->getStrDisplayName());
        }
        if($objInstance instanceof class_module_eventmanager_participant) {
            return getLinkAdmin($this->getArrModule("modul"), "listParticipant", "&systemid=" . $objInstance->getStrPrevId(), $objInstance->getStrDisplayName());
        }

        return "";
    }


    protected function renderAdditionalActions(\Kajona\System\System\Model $objListEntry) {
        if($objListEntry->rightEdit() && $objListEntry instanceof class_module_eventmanager_event) {
            return array(
                $this->objToolkit->listButton(getLinkAdmin($this->getArrModule("modul"), "listParticipant", "&systemid=" . $objListEntry->getSystemid(), "", $this->getLang("action_list_participant"), "icon_group"))
            );
        }

        if($objListEntry instanceof class_module_eventmanager_participant) {
            if($objListEntry->rightEdit()) {
                $objValidator = new class_email_validator();
                $objEvent = new class_module_eventmanager_event($objListEntry->getPrevId());
                if($objValidator->validate($objListEntry->getStrEmail())) {
                    $strPreset = "&mail_recipient=" . $objListEntry->getStrEmail();
                    $strPreset .= "&mail_subject=" . ($this->getLang("participant_mail_subject"));
                    $strPreset .= "&mail_body=" .
                        $this->getLang("participant_mail_intro") . "\n" .
                        $this->getLang("event_title") . " " . $objEvent->getStrTitle() . "\n" .
                        $this->getLang("event_location") . " " . $objEvent->getStrLocation() . "\n" .
                        $this->getLang("event_start") . " " . dateToString($objEvent->getObjStartDate());

                    return array(
                        $this->objToolkit->listButton(getLinkAdminDialog("system", "mailForm", $strPreset, "", $this->getLang("participant_mail"), "icon_mail"))
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

}

