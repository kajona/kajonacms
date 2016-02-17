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

    public function getEventsByCategoryAndDate($strCategory, \Kajona\System\System\Date $objStartDate, \Kajona\System\System\Date $objEndDate)
    {
        if ($strCategory != "calendarEvent") {
            return array();
        }

        $arrResult = array();
        $arrEvents = class_module_eventmanager_event::getAllEvents(null, null, $objStartDate, $objEndDate);
        foreach($arrEvents as $objOneEvent) {
            if ($objOneEvent->rightView()) {
                $objEvent = new class_event_entry();
                $objEvent->setStrIcon($objOneEvent->getStrIcon());
                $objEvent->setStrCategory("calendarEvent");
                $objEvent->setStrDisplayName($objOneEvent->getStrDisplayName());
                $objEvent->setObjValidDate(new \Kajona\System\System\Date($objOneEvent->getObjStartDate()));
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
