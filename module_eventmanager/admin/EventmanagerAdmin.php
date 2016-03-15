<?php
/*"******************************************************************************************************
*   (c) 2004-2006 by MulchProductions, www.mulchprod.de                                                 *
*   (c) 2007-2016 by Kajona, www.kajona.de                                                              *
*       Published under the GNU LGPL v2.1, see /system/licence_lgpl.txt                                 *
********************************************************************************************************/

namespace Kajona\Eventmanager\Admin;

use Kajona\Eventmanager\System\EventmanagerEvent;
use Kajona\Eventmanager\System\EventmanagerParticipant;
use Kajona\System\Admin\AdminEvensimpler;
use Kajona\System\Admin\AdminInterface;
use Kajona\System\System\ModelInterface;
use Kajona\System\System\Validators\EmailValidator;


/**
 * Admin class of the eventmanager-module. Responsible for editing events, participants and organizing them.
 *
 * @package module_eventmanager
 * @author sidler@mulchprod.de
 * @since 3.4
 *
 *
 * @objectList Kajona\Eventmanager\System\EventmanagerEvent
 * @objectNew Kajona\Eventmanager\System\EventmanagerEvent
 * @objectEdit Kajona\Eventmanager\System\EventmanagerEvent
 *
 * @objectListParticipant Kajona\Eventmanager\System\EventmanagerParticipant
 * @objectNewParticipant Kajona\Eventmanager\System\EventmanagerParticipant
 * @objectEditParticipant Kajona\Eventmanager\System\EventmanagerParticipant
 *
 *
 * @autoTestable list,new,newParticipant
 *
 * @module eventmanager
 * @moduleId _eventmanager_module_id_
 *
 */
class EventmanagerAdmin extends AdminEvensimpler implements AdminInterface
{

    const STR_CALENDAR_FILTER_EVENT = "STR_CALENDAR_FILTER_EVENT";

    public function getOutputModuleNavi()
    {
        $arrReturn = array();
        $arrReturn[] = array("view", getLinkAdmin($this->getArrModule("modul"), "list", "", $this->getLang("commons_list"), "", "", true, "adminnavi"));
        return $arrReturn;
    }

    protected function getOutputNaviEntry(ModelInterface $objInstance)
    {

        if ($objInstance instanceof EventmanagerEvent) {
            return getLinkAdmin($this->getArrModule("modul"), "listParticipant", "&systemid=".$objInstance->getSystemid(), $objInstance->getStrDisplayName());
        }
        if ($objInstance instanceof EventmanagerParticipant) {
            return getLinkAdmin($this->getArrModule("modul"), "listParticipant", "&systemid=".$objInstance->getStrPrevId(), $objInstance->getStrDisplayName());
        }

        return "";
    }


    protected function renderAdditionalActions(\Kajona\System\System\Model $objListEntry)
    {
        if ($objListEntry->rightEdit() && $objListEntry instanceof EventmanagerEvent) {
            return array(
                $this->objToolkit->listButton(getLinkAdmin($this->getArrModule("modul"), "listParticipant", "&systemid=".$objListEntry->getSystemid(), "", $this->getLang("action_list_participant"), "icon_group"))
            );
        }

        if ($objListEntry instanceof EventmanagerParticipant) {
            if ($objListEntry->rightEdit()) {
                $objValidator = new EmailValidator();
                $objEvent = new EventmanagerEvent($objListEntry->getPrevId());
                if ($objValidator->validate($objListEntry->getStrEmail())) {
                    $strPreset = "&mail_recipient=".$objListEntry->getStrEmail();
                    $strPreset .= "&mail_subject=".($this->getLang("participant_mail_subject"));
                    $strPreset .= "&mail_body=".
                        $this->getLang("participant_mail_intro")."\n".
                        $this->getLang("event_title")." ".$objEvent->getStrTitle()."\n".
                        $this->getLang("event_location")." ".$objEvent->getStrLocation()."\n".
                        $this->getLang("event_start")." ".dateToString($objEvent->getObjStartDate());

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
    protected function actionListParticipant()
    {
        $strReturn = "";
        $objEvent = new EventmanagerEvent($this->getSystemid());
        if ($objEvent->getIntRegistrationRequired() == "1" && $objEvent->getIntLimitGiven() == "1") {
            $strReturn .= $this->objToolkit->getTextRow($this->getLang("participants_info_limit").$objEvent->getIntParticipantsLimit());
        }
        else {
            $strReturn .= $this->objToolkit->getTextRow($this->getLang("participants_info_nolimit"));
        }

        $strReturn .= $this->objToolkit->divider();
        $this->setStrCurObjectTypeName("Participant");
        $this->setCurObjectClassName("Kajona\\Eventmanager\\System\\EventmanagerParticipant");

        $strReturn .= $this->actionList();
        return $strReturn;

    }

}

