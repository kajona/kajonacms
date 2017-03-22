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
 * DateParser
 *
 * @package module_system
 * @author christoph.kappestein@gmail.com
 */
class DateParser
{
    /**
     * Sets the current time based on the array of params passed.
     * The fieldname is the prefix of the form-elements.
     * The timestamp is generated out of the following form-elements, so element of
     * the params-array:
     * fieldname_year, fieldname_month, fieldname_day, fieldname_hour, fieldname_minute, fieldname_second
     * If a single field is not found, 00 is inserted instead.
     *
     * @param string $strFieldname
     * @param array $arrParams
     * @return \DateTime
     */
    public static function generateDateFromParams($strFieldname, $arrParams)
    {
        $objDate = new Date();

        $intYear = "0000";
        $intMonth = 00;
        $intDay = 00;
        $intHour = 00;
        $intMinute = 00;
        $intSecond = 00;

        if (isset($arrParams[$strFieldname."_year"]) && $arrParams[$strFieldname."_year"] != "") {
            $intYear = (int)$arrParams[$strFieldname."_year"];
        }

        if (isset($arrParams[$strFieldname."_month"]) && $arrParams[$strFieldname."_month"] != "") {
            $intMonth = (int)$arrParams[$strFieldname."_month"];
            if ($intMonth > 12) {
                $intMonth = 12;
            }
        }

        if (isset($arrParams[$strFieldname."_day"]) && $arrParams[$strFieldname."_day"] != "") {
            $intDay = (int)$arrParams[$strFieldname."_day"];
            if ($intDay > 31) {
                $intDay = 31;
            }
        }

        if (isset($arrParams[$strFieldname."_hour"]) && $arrParams[$strFieldname."_hour"] != "") {
            $intHour = (int)$arrParams[$strFieldname."_hour"];
            if ($intHour > 23) {
                $intHour = 23;
            }
        }

        if (isset($arrParams[$strFieldname."_minute"]) && $arrParams[$strFieldname."_minute"] != "") {
            $intMinute = (int)$arrParams[$strFieldname."_minute"];
            if ($intMinute > 59) {
                $intMinute = 59;
            }
        }

        if (isset($arrParams[$strFieldname."_second"]) && $arrParams[$strFieldname."_second"] != "") {
            $intMinute = (int)$arrParams[$strFieldname."_second"];
            if ($intMinute > 59) {
                $intMinute = 59;
            }
        }

        //see if the other parts may be read directly
        if (isset($arrParams[$strFieldname])) {

            if (strlen($arrParams[$strFieldname]) == strlen("YYYYmmddHHiiss")) {
                $objDate->setLongTimestamp($arrParams[$strFieldname]);
                return $objDate;
            }

            $objDateTime = \DateTime::createFromFormat(Carrier::getInstance()->getObjLang()->getLang("dateStyleShort", "system"), $arrParams[$strFieldname]);
            if ($objDateTime) {
                $intTimestamp = $objDateTime->getTimestamp();
                $intYear = strftime("%Y", $intTimestamp);
                $intMonth = strftime("%m", $intTimestamp);
                $intDay = strftime("%d", $intTimestamp);
            }
        }

        $objDate->setDate($intYear, $intMonth, $intDay);
        $objDate->setTime($intHour, $intMinute, $intSecond);

        return $objDate;
    }
}
