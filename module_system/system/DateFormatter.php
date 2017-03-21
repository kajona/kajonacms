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
     * Formats the date object according to the standard PHP date format
     *
     * @see http://php.net/manual/en/function.date.php
     * @param string $strFormat
     * @param Date $objDate
     * @return false|string
     */
    public static function format($strFormat, Date $objDate)
    {
        return date($strFormat, $objDate->getTimeInOldStyle());
    }

    /**
     * Returns a format depending on the language of the logged in user. The string contains
     * the year, month, day, hour, minutes and seconds
     *
     * @param Date $objDate
     * @return string
     */
    public static function toLongFormat(Date $objDate)
    {
        return self::format(Lang::getInstance()->getLang("dateStyleLong", "system"), $objDate);
    }

    /**
     * Returns a format depending on the language of the logged in user. The string contains
     * the year, month and day
     *
     * @param Date $objDate
     * @return string
     */
    public static function toShortFormat(Date $objDate)
    {
        return self::format(Lang::getInstance()->getLang("dateStyleShort", "system"), $objDate);
    }

    /**
     * Returns the month name in the language of the logged in user. I.e. Januar
     *
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
     * Returns the month short name in the language of the logged in user. I.e. Jan
     *
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
     * Returns the weekday name in the language of the logged in user. I.e. Mo
     *
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
     * Returns the short weekday name in the language of the logged in user. I.e. M
     *
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
