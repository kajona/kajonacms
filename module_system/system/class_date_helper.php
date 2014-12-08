<?php
/*"******************************************************************************************************
*   (c) 20014-2014 by Kajona, www.kajona.de                                                             *
*       Published under the GNU LGPL v2.1, see /system/licence_lgpl.txt                                 *
********************************************************************************************************/

/**
 * A general helper in order to calculate special dates, like easter or s.th. else
 * @package module_system
 * @author sidler@mulchprod.de
 * @since 4.6
 */
class class_date_helper {

    /**
     * Validates if the passed day is a easter bank holiday.
     * This includes: friday before easter, easter saturday and sunday, easter monday
     *
     * @param class_date $objDate
     *
     * @return bool
     */
    public function isEasterHoliday(class_date $objDate) {
        $objEasterSunday = $this->calcEasterSunday($objDate->getIntYear());
        $objEasterSaturday = clone $objEasterSunday; $objEasterSaturday->setPreviousDay();
        $objEasterFriday = clone $objEasterSaturday; $objEasterFriday->setPreviousDay();
        $objEasterMonday = clone $objEasterSunday; $objEasterMonday->setNextDay();

        return $objDate->isSameDay($objEasterFriday)
            || $objDate->isSameDay($objEasterSaturday)
            || $objDate->isSameDay($objEasterSunday)
            || $objDate->isSameDay($objEasterMonday);

    }


    /**
     * Target 2 is the european payments network.
     * Payments are possible for regular workdays (monday till friday)
     * except for some bank holidays. Those include:
     *  New Year:        1. January
     *  Karfreitag
     *  Ostermontag
     *  Labor Day        1. May
     *  1st Xmas Day    25. December
     *  2nd Xmas Day    26. December
     * TARGET = Trans-European Automated Real-Time Gross Settlement Express Transfer
     *
     * @param class_date $objDate
     *
     * @return bool
     */
    public function isValidTarget2Day(class_date $objDate) {
        if($objDate->getIntDayOfWeek() == class_date::INT_DAY_SATURDAY)
            return false;

        if($objDate->getIntDayOfWeek() == class_date::INT_DAY_SUNDAY)
            return false;

        $objCompare = clone $objDate;

        //1st of january
        if($objDate->isSameDay($objCompare->setIntDay(1)->setIntMonth(1)))
            return false;

        //1st of may
        if($objDate->isSameDay($objCompare->setIntDay(1)->setIntMonth(5)))
            return false;

        //25th of december
        if($objDate->isSameDay($objCompare->setIntDay(25)->setIntMonth(12)))
            return false;

        //26th of december
        if($objDate->isSameDay($objCompare->setIntDay(26)->setIntMonth(12)))
            return false;

        //easter
        if($this->isEasterHoliday($objDate))
            return false;

        return true;
    }

    /**
     * Calculates the date of easter-sunday for the passed year.
     * In best cases, this may be done by easter_date, but since this requires a special extension,
     * we calc the date on our own just to be sure.
     *
     * @param $intYear
     *
     * @return class_date
     * @see http://php.net/manual/de/function.easter-date.php#68874
     */
    private function calcEasterSunday($intYear) {


        $intMarch21DayOffset = date('z', mktime(0, 0, 0, 3, 21, $intYear));

        $intDaysAfterMarch = ((15 + $intYear/100 - $intYear/400 - (8 * $intYear/100 + 13) / 25)%30 + 19 * ($intYear%19))%30;

        if ($intDaysAfterMarch==29) {
            $intTargetDay = 28;
        }
        elseif($intDaysAfterMarch==28 && ($intYear%17)>=11) {
            $intTargetDay = 27;
        }
        else
            $intTargetDay = $intDaysAfterMarch;

        $intOffset = (2 * ($intYear%4) + 4 *($intYear%7) + 6 * $intTargetDay + (6 + $intYear/100 - $intYear/400 - 2)%7)%7;

        $intEasterSundayYearOffset= $intOffset + $intTargetDay + 1 + $intMarch21DayOffset;

        if($this->isLeapYear($intYear)) {
            $intEasterSundayYearOffset -= 1;
        }

        //offset per year, so calc back to the current year
        $objDateTime = DateTime::createFromFormat('z Y', strval($intEasterSundayYearOffset) . ' ' . strval($intYear));
        $objDate = new class_date($objDateTime->getTimestamp());
        return $objDate->setIntHour(0)->setIntMin(0)->setIntSec(0);
    }

    /**
     * Checks if a year is a leap year.
     *
     * @param $intYear
     *
     * @return bool
     * @see http://davidwalsh.name/checking-for-leap-year-using-php
     */
    public function isLeapYear($intYear) {
        return ((($intYear % 4) == 0) && ((($intYear % 100) != 0) || (($intYear %400) == 0)));
    }


}

