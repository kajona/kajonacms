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
        if ($strCategory != "eventmanager_events") {
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
                $objTodo = new class_todo_entry();
                $objTodo->setStrIcon($objOneEvent->getStrIcon());
                $objTodo->setStrCategory("eventmanager_events");
                $objTodo->setStrDisplayName($objOneEvent->getStrDisplayName());
                $objTodo->setObjValidDate(new class_date($objOneEvent->getObjStartDate()));
                $objTodo->setArrModuleNavi(array(
                    class_link::getLinkAdmin("eventmanager", "edit", "&systemid=" . $objOneEvent->getStrSystemid(), "", "", "icon_edit")
                ));

                $arrResult[] = $objTodo;
            }
        }

        return $arrResult;
    }

    public function getCategories()
    {
        return array(
            "eventmanager_events" => class_lang::getInstance()->getLang("calendar_type_event", "eventmanager"),
        );
    }

    public function rightView()
    {
        return class_module_system_module::getModuleByName("eventmanager")->rightView();
    }
}
