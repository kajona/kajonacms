<?php
/*"******************************************************************************************************
*   (c) 2007-2015 by Kajona, www.kajona.de                                                              *
*       Published under the GNU LGPL v2.1, see /system/licence_lgpl.txt                                 *
********************************************************************************************************/

namespace Kajona\System\System;


/**
 * Enum for periods
 *
 * @package module_search
 * @author stefan.meyer1@yahoo.de.de
 * @since 4.7
 *
 * @method static DatePeriodEnum DAY()
 * @method static DatePeriodEnum WEEK()
 * @method static DatePeriodEnum MONTH()
 * @method static DatePeriodEnum QUARTER()
 * @method static DatePeriodEnum HALFYEAR()
 * @method static DatePeriodEnum YEAR()
 */
class DatePeriodEnum extends EnumBase
{
    /**
     * Return the array of possible, so allowed values for the current enum
     *
     * @return string[]
     */
    protected function getArrValues()
    {
        return array("DAY", "WEEK", "MONTH", "QUARTER", "HALFYEAR", "YEAR");
    }

}