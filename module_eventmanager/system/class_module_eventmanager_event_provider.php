<?php
/*"******************************************************************************************************
*   (c) 2015 by Kajona, www.kajona.de                                                              *
*       Published under the GNU LGPL v2.1, see /system/licence_lgpl.txt                                 *
********************************************************************************************************/

/**
 * @package module_eventmanager
 */
class class_module_eventmanager_event_provider implements interface_event_provider
{
    public static function getExtensionName()
    {
        return self::EXTENSION_POINT;
    }

    public function getName()
    {
        return class_lang::getInstance()->getLang("modul_titel", "eventmanager");
    }

    public function getEventsByCategoryAndDate($strCategory, class_date $objDate)
    {
        if ($strCategory != "calendarEvent") {
            return array();
        }

        $arrResult = array();
        $objStartDate = clone $objDate;
        $objStartDate->setIntHour(0)->setIntMin(0)->setIntSec(0);
        $objEndDate = clone $objDate;
        $objEndDate->setIntHour(23)->setIntMin(59)->setIntSec(59);

        $arrEvents = class_module_eventmanager_event::getAllEvents(null, null, $objStartDate, $objEndDate);
        foreach($arrEvents as $objOneEvent) {
            if ($objOneEvent->rightView()) {
                $objEvent = new class_event_entry();
                $objEvent->setStrIcon($objOneEvent->getStrIcon());
                $objEvent->setStrCategory("calendarEvent");
                $objEvent->setStrDisplayName($objOneEvent->getStrDisplayName());
                $objEvent->setObjValidDate(new class_date($objOneEvent->getObjStartDate()));
                $objEvent->setStrHref(class_link::getLinkAdminHref("eventmanager", "edit", "&systemid=" . $objOneEvent->getStrSystemid(), "", "", "icon_edit"));

                $arrResult[] = $objEvent;
            }
        }

        return $arrResult;
    }

    public function getCategories()
    {
        $objEvent = new class_module_eventmanager_event();
        $strIcon = class_adminskin_helper::getAdminImage($objEvent->getStrIcon());

        return array(
            "calendarEvent" => $strIcon . " " . class_lang::getInstance()->getLang("calendar_type_event", "eventmanager"),
        );
    }

    public function rightView()
    {
        return class_module_system_module::getModuleByName("eventmanager")->rightView();
    }
}
