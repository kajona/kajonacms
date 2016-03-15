<?php
/*"******************************************************************************************************
*   (c) 2015-2016 by Kajona, www.kajona.de                                                         *
*       Published under the GNU LGPL v2.1, see /system/licence_lgpl.txt                                 *
********************************************************************************************************/

namespace Kajona\News\System;

use Kajona\Dashboard\System\EventEntry;
use Kajona\Dashboard\System\EventProviderInterface;
use Kajona\System\System\AdminskinHelper;
use Kajona\System\System\Lang;
use Kajona\System\System\Link;
use Kajona\System\System\SystemModule;

/**
 * @package module_news
 */
class NewsEventProvider implements EventProviderInterface
{
    public static function getExtensionName()
    {
        return self::EXTENSION_POINT;
    }

    public function getName()
    {
        return Lang::getInstance()->getLang("modul_titel", "news");
    }

    public function getEventsByCategoryAndDate($strCategory, \Kajona\System\System\Date $objStartDate, \Kajona\System\System\Date $objEndDate)
    {
        if ($strCategory != "calendarNews") {
            return array();
        }

        $arrResult = array();
        $arrNews = NewsNews::getObjectList("", null, null, $objStartDate, $objEndDate);
        foreach($arrNews as $objOneNews) {
            if ($objOneNews->rightView()) {
                $objEvent = new EventEntry();
                $objEvent->setStrIcon($objOneNews->getStrIcon());
                $objEvent->setStrCategory("calendarNews");
                $objEvent->setStrDisplayName($objOneNews->getStrDisplayName());
                $objEvent->setObjValidDate(new \Kajona\System\System\Date($objOneNews->getObjStartDate()));
                $objEvent->setStrHref(Link::getLinkAdminHref("news", "edit", "&systemid=" . $objOneNews->getStrSystemid()));

                $arrResult[] = $objEvent;
            }
        }

        return $arrResult;
    }

    public function getCategories()
    {
        $objNews = new NewsNews();
        $strIcon = AdminskinHelper::getAdminImage($objNews->getStrIcon());

        return array(
            "calendarNews" => $strIcon . " " . Lang::getInstance()->getLang("calendar_type_news", "news"),
        );
    }

    public function rightView()
    {
        return SystemModule::getModuleByName("news")->rightView();
    }
}
