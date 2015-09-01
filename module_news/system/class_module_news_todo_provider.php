<?php
/*"******************************************************************************************************
*   (c) 2015 by Kajona, www.kajona.de                                                              *
*       Published under the GNU LGPL v2.1, see /system/licence_lgpl.txt                                 *
********************************************************************************************************/

/**
 * @package module_news
 */
class class_module_news_todo_provider implements interface_todo_provider
{
    public static function getExtensionName()
    {
        return self::EXTENSION_POINT;
    }

    public function getName()
    {
        return class_lang::getInstance()->getLang("modul_titel", "news");
    }

    public function getCurrentEventsByCategory($strCategory)
    {
        return array();
    }

    public function getEventsByCategoryAndDate($strCategory, class_date $objDate)
    {
        if ($strCategory != "news_news") {
            return array();
        }

        $arrResult = array();
        $objStartDate = clone $objDate;
        $objStartDate->setIntHour(0)->setIntMin(0)->setIntSec(0);
        $objEndDate = clone $objDate;
        $objEndDate->setIntHour(23)->setIntMin(59)->setIntSec(59);

        $arrNews = class_module_news_news::getObjectList("", null, null, $objStartDate, $objEndDate);
        foreach($arrNews as $objOneNews) {
            if ($objOneNews->rightView()) {
                $objTodo = new class_todo_entry();
                $objTodo->setStrIcon($objOneNews->getStrIcon());
                $objTodo->setStrCategory("news_news");
                $objTodo->setStrDisplayName($objOneNews->getStrDisplayName());
                $objTodo->setObjValidDate(new class_date($objOneNews->getObjStartDate()));
                $objTodo->setArrModuleNavi(array(
                    class_link::getLinkAdmin("news", "edit", "&systemid=" . $objOneNews->getStrSystemid(), "", "", "icon_edit")
                ));

                $arrResult[] = $objTodo;
            }
        }

        return $arrResult;
    }

    public function getCategories()
    {
        return array(
            "news_news" => class_lang::getInstance()->getLang("calendar_filter_news", "news"),
        );
    }

    public function rightView()
    {
        return class_module_system_module::getModuleByName("news")->rightView();
    }
}
