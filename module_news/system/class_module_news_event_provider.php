<?php
/*"******************************************************************************************************
*   (c) 2015 by Kajona, www.kajona.de                                                              *
*       Published under the GNU LGPL v2.1, see /system/licence_lgpl.txt                                 *
********************************************************************************************************/

/**
 * @package module_news
 */
class class_module_news_event_provider implements interface_event_provider
{
    public static function getExtensionName()
    {
        return self::EXTENSION_POINT;
    }

    public function getName()
    {
        return class_lang::getInstance()->getLang("modul_titel", "news");
    }

    public function getEventsByCategoryAndDate($strCategory, class_date $objStartDate, class_date $objEndDate)
    {
        if ($strCategory != "calendarNews") {
            return array();
        }

        $arrResult = array();
        $arrNews = class_module_news_news::getObjectList("", null, null, $objStartDate, $objEndDate);
        foreach($arrNews as $objOneNews) {
            if ($objOneNews->rightView()) {
                $objEvent = new class_event_entry();
                $objEvent->setStrIcon($objOneNews->getStrIcon());
                $objEvent->setStrCategory("calendarNews");
                $objEvent->setStrDisplayName($objOneNews->getStrDisplayName());
                $objEvent->setObjValidDate(new class_date($objOneNews->getObjStartDate()));
                $objEvent->setStrHref(class_link::getLinkAdminHref("news", "edit", "&systemid=" . $objOneNews->getStrSystemid()));

                $arrResult[] = $objEvent;
            }
        }

        return $arrResult;
    }

    public function getCategories()
    {
        $objNews = new class_module_news_news();
        $strIcon = class_adminskin_helper::getAdminImage($objNews->getStrIcon());

        return array(
            "calendarNews" => $strIcon . " " . class_lang::getInstance()->getLang("calendar_type_news", "news"),
        );
    }

    public function rightView()
    {
        return class_module_system_module::getModuleByName("news")->rightView();
    }
}
