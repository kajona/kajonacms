<?php
/*"******************************************************************************************************
*   (c) 2015 by Kajona, www.kajona.de                                                              *
*       Published under the GNU LGPL v2.1, see /system/licence_lgpl.txt                                 *
********************************************************************************************************/

namespace Kajona\Eventmanager\System;
use class_event_entry;
use interface_event_provider;
use Kajona\System\System\AdminskinHelper;
use Kajona\System\System\Lang;
use Kajona\System\System\Link;
use Kajona\System\System\SystemModule;


/**
 * @package module_eventmanager
 */
class EventmanagerEventProvider implements interface_event_provider
{
    public static function getExtensionName()
    {
        return self::EXTENSION_POINT;
    }

    public function getName()
    {
        return Lang::getInstance()->getLang("modul_titel", "eventmanager");
    }

    public function getEventsByCategoryAndDate($strCategory, \Kajona\System\System\Date $objStartDate, \Kajona\System\System\Date $objEndDate)
    {
        if ($strCategory != "calendarEvent") {
            return array();
        }

        $arrResult = array();
        $arrEvents = EventmanagerEvent::getAllEvents(null, null, $objStartDate, $objEndDate);
        foreach($arrEvents as $objOneEvent) {
            if ($objOneEvent->rightView()) {
                $objEvent = new class_event_entry();
                $objEvent->setStrIcon($objOneEvent->getStrIcon());
                $objEvent->setStrCategory("calendarEvent");
                $objEvent->setStrDisplayName($objOneEvent->getStrDisplayName());
                $objEvent->setObjValidDate(new \Kajona\System\System\Date($objOneEvent->getObjStartDate()));
                $objEvent->setStrHref(Link::getLinkAdminHref("eventmanager", "edit", "&systemid=" . $objOneEvent->getStrSystemid(), "", "", "icon_edit"));

                $arrResult[] = $objEvent;
            }
        }

        return $arrResult;
    }

    public function getCategories()
    {
        $objEvent = new EventmanagerEvent();
        $strIcon = AdminskinHelper::getAdminImage($objEvent->getStrIcon());

        return array(
            "calendarEvent" => $strIcon . " " . Lang::getInstance()->getLang("calendar_type_event", "eventmanager"),
        );
    }

    public function rightView()
    {
        return SystemModule::getModuleByName("eventmanager")->rightView();
    }
}
