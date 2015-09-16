<?php
/*"******************************************************************************************************
*   (c) 2007-2015 by Kajona, www.kajona.de                                                              *
*       Published under the GNU LGPL v2.1, see /system/licence_lgpl.txt                                 *
********************************************************************************************************/

/**
 * Enum for periods
 *
 * @package module_search
 * @author stefan.meyer1@yahoo.de.de
 * @since 4.7
 *
 * @method static class_date_period_enum WEEK()
 * @method static class_date_period_enum MONTH()
 * @method static class_date_period_enum QUARTER()
 * @method static class_date_period_enum HALFYEAR()
 * @method static class_date_period_enum YEAR()
 */
class class_date_period_enum extends class_enum {
    /**
     * Return the array of possible, so allowed values for the current enum
     *
     * @return string[]
     */
    protected function getArrValues() {
        return array("WEEK", "MONTH", "QUARTER", "HALFYEAR", "YEAR");
    }

}