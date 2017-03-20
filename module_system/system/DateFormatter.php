<?php
/*"******************************************************************************************************
*   (c) 2004-2006 by MulchProductions, www.mulchprod.de                                                 *
*   (c) 2007-2016 by Kajona, www.kajona.de                                                              *
*       Published under the GNU LGPL v2.1, see /system/licence_lgpl.txt                                 *
*-------------------------------------------------------------------------------------------------------*
*	$Id$                                            *
********************************************************************************************************/

namespace Kajona\System\System;

/**
 * Class which helps to generate date representations from an date object
 *
 * @package module_system
 * @author christoph.kappestein@gmail.com
 */
class DateFormatter
{
    /**
     * @param string $strFormat
     * @param Date $objDate
     * @return false|string
     */
    public static function format($strFormat, Date $objDate)
    {
        return date($strFormat, $objDate->getTimeInOldStyle());
    }

    /**
     * @param Date $objDate
     * @return string
     */
    public static function toLongFormat(Date $objDate)
    {
        return self::format(Lang::getInstance()->getLang("dateStyleLong", "system"), $objDate);
    }

    /**
     * @param Date $objDate
     * @return string
     */
    public static function toShortFormat(Date $objDate)
    {
        return self::format(Lang::getInstance()->getLang("dateStyleShort", "system"), $objDate);
    }

    /**
     * @param Date $objDate
     * @return string
     */
    public static function getMonthName(Date $objDate)
    {
        $strMonth = Lang::getInstance()->getLang("toolsetCalendarMonth", "system");
        $arrMonth = array_map(function($strValue){ return trim($strValue, " \""); }, explode(",", $strMonth));
        $intMonth = (int) $objDate->getIntMonth();

        return isset($arrMonth[$intMonth - 1]) ? $arrMonth[$intMonth - 1] : null;
    }

    /**
     * @param Date $objDate
     * @return string
     */
    public static function getMonthShortName(Date $objDate)
    {
        $arrMonth = Lang::getInstance()->getLang("toolsetCalendarMonthShort", "system");
        $intMonth = (int) $objDate->getIntMonth();

        return isset($arrMonth[$intMonth - 1]) ? $arrMonth[$intMonth - 1] : null;
    }

    /**
     * @param Date $objDate
     * @return string
     */
    public static function getWeekdayName(Date $objDate)
    {
        $strWeek = Lang::getInstance()->getLang("toolsetCalendarWeekday", "system");
        $arrWeek = array_map(function($strValue){ return trim($strValue, " \""); }, explode(",", $strWeek));
        $intWeek = (int) $objDate->getIntDayOfWeek();

        return isset($arrWeek[$intWeek]) ? $arrWeek[$intWeek] : null;
    }

    /**
     * @param Date $objDate
     * @return string
     */
    public static function getWeekdayShortName(Date $objDate)
    {
        $arrWeek = Lang::getInstance()->getLang("toolsetCalendarWeekdayShort", "system");
        $intWeek = (int) $objDate->getIntDayOfWeek();

        return isset($arrWeek[$intWeek]) ? $arrWeek[$intWeek] : null;
    }
}
