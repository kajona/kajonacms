<?php
/*"******************************************************************************************************
*   (c) 2004-2006 by MulchProductions, www.mulchprod.de                                                 *
*   (c) 2007-2016 by Kajona, www.kajona.de                                                              *
*       Published under the GNU LGPL v2.1, see /system/licence_lgpl.txt                                 *
*-------------------------------------------------------------------------------------------------------*
*	$Id$                                            *
********************************************************************************************************/

namespace Kajona\System\System;

use DateInterval;

/**
 * The date class is used to handle all kind of date and time related operations.
 * It extends from the PHP \DateTime class because of that it depends on the
 * ini timezone setting
 *
 * @package module_system
 * @author sidler@mulchprod.de
 * @author christoph.kappestein@gmail.com
 */
class Date extends \DateTime
{
    const INT_DAY_SUNDAY = 0;
    const INT_DAY_MONDAY = 1;
    const INT_DAY_TUESDAY = 2;
    const INT_DAY_WEDNESDAY = 3;
    const INT_DAY_THURSDAY = 4;
    const INT_DAY_FRIDAY = 5;
    const INT_DAY_SATURDAY = 6;

    /**
     * Creates an instance of the Date an initialises it with the current date if no value is passed.
     * If a value is passed (int, long, Date), the value is used as the timestamp set to the new date-object.
     *
     * @param string|int|Date $longInitValue
     */
    public function __construct($longInitValue = "")
    {
        if ($longInitValue instanceof Date) {
            $strTime = $longInitValue->format("Y-m-d\\TH:i:s");
        } elseif ($longInitValue === "0" || $longInitValue === 0) {
            $strTime = "0001-01-01T00:00:00";
        } elseif ($longInitValue == "") {
            $strTime = "now";
        } elseif (strlen($longInitValue) == 14) {
            list($year, $month, $day, $hour, $minute, $second) = self::splitLongTimestamp($longInitValue);
            $strTime = "{$year}-{$month}-{$day}T{$hour}:{$minute}:{$second}";
        } else {
            $strTime = "@" . $longInitValue;
        }

        parent::__construct($strTime);
    }

    /**
     * Returns the string-based version of the long-value currently maintained.
     *
     * @return string
     */
    public function __toString()
    {
        return $this->format("YmdHis");
    }

    /**
     * Compares the current date against another date and evaluates, of both dates reference the same day.
     *
     * @param Date $objDateToCompare
     *
     * @return bool
     */
    public function isSameDay(Date $objDateToCompare)
    {
        return $this->format("Ymd") == $objDateToCompare->format("Ymd");
    }

    /**
     * Generates a long-timestamp of the current time
     *
     * @return int
     */
    public static function getCurrentTimestamp()
    {
        return date("YmdHis");
    }

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
     * @deprecated
     */
    public function generateDateFromParams($strFieldname, $arrParams)
    {
        $objDate = DateParser::generateDateFromParams($strFieldname, $arrParams);
        $this->setTimestamp($objDate->getTimestamp());
    }

    /**
     * Allows to init the current class with an 32Bit int value representing the seconds since 1970.
     * PHPs' time() returns 32Bit ints, too.
     *
     * @deprecated
     * @see setTimestamp
     * @param int $intTimestamp
     * @return Date
     */
    public function setTimeInOldStyle($intTimestamp)
    {
        $this->setTimestamp($intTimestamp);
        return $this;
    }

    /**
     * Converts the current long-timestamp to an old-fashioned int-timestamp (seconds since 1970)
     *
     * @deprecated
     * @see getTimestamp
     * @return int
     */
    public function getTimeInOldStyle()
    {
        return $this->getTimestamp();
    }

    /**
     * Returns the integer-based number of the day of the week.
     * 0 is sunday whereas 6 is the saturday.
     * This leads to:
     *   0 => Sunday
     *   1 => Monday
     *   2 => Tuesday
     *   3 => Wednesday
     *   4 => Thursday
     *   5 => Friday
     *   6 => Saturday
     *
     * @return int
     */
    public function getIntDayOfWeek()
    {
        return $this->format("w");
    }

    /**
     * Sets the current day to the previous day.
     * Includes the handling of month / year shifts.
     *
     * @since 3.4
     * @return Date
     */
    public function setPreviousDay()
    {
        $this->sub(DateInterval::createFromDateString('1 day'));
        return $this;
    }

    /**
     * Sets the current day to the next day.
     * Includes the handling of month / year shifts.
     *
     * @since 3.4
     * @return Date
     */
    public function setNextDay()
    {
        $this->add(DateInterval::createFromDateString('1 day'));
        return $this;
    }

    /**
     * Shifts the current month into the future by one.
     * If the current month has 31 days, the next one only 30, the
     * logic will remain at 30.
     *
     * @return Date
     */
    public function setNextMonth()
    {
        $objSourceDate = clone $this;

        $this->setNextDay();
        $intDaysAdded = 1;
        while ($this->getIntDay() != $objSourceDate->getIntDay()) {
            $this->setNextDay();
            $intDaysAdded++;

            //if we skip a month border, roll back until the previous months last day
            if ($intDaysAdded > 31) {
                $this->setIntDay(1);
                $this->setPreviousDay();
                //and jump out
                break;
            }
        }

        $this->setTime($objSourceDate->getIntHour(), $objSourceDate->getIntMin(), $objSourceDate->getIntSec());
        return $this;
    }


    /**
     * Shifts the current month into the past by one.
     * If the current month has 31 days, the previous one only 30, the
     * logic will remain at 30.
     *
     * @return Date
     */
    public function setPreviousMonth()
    {
        $objSourceDate = clone $this;

        $this->setPreviousDay();
        $intDaysSubtracted = 1;
        while ($this->getIntDay() != $objSourceDate->getIntDay()) {
            $this->setPreviousDay();
            $intDaysSubtracted++;

            //if we skip a month border, roll back until the next months last day
            if ($intDaysSubtracted > 31) {
                $this->setNextMonth();
                $this->setIntDay(1);
                $this->setPreviousDay();
                //and jump out
                break;
            }
        }

        $this->setTime($objSourceDate->getIntHour(), $objSourceDate->getIntMin(), $objSourceDate->getIntSec());
        return $this;
    }

    /**
     * Shifts the current year into the past by one.
     *
     * @return Date
     */
    public function setPreviousYear()
    {
        $intCurrentDay = $this->getIntDay();
        $this->setIntDay(1);
        for ($intI = 0; $intI < 12; $intI++) {
            $this->setPreviousMonth();
        }

        $this->setIntDay($intCurrentDay);
        return $this;
    }

    /**
     * Shifts the current year into the future by one.
     *
     * @return Date
     */
    public function setNextYear()
    {
        $intCurrentDay = $this->getIntDay();
        $this->setIntDay(1);
        for ($intI = 0; $intI < 12; $intI++) {
            $this->setNextMonth();
        }

        $this->setIntDay($intCurrentDay);
        return $this;
    }

    /**
     * Shifts the current date one week into the future, so seven days
     *
     * @return Date
     */
    public function setNextWeek()
    {
        for ($intI = 1; $intI <= 7; $intI++) {
            $this->setNextDay();
        }

        return $this;
    }

    /**
     * Shifts the current date one week into the future, so seven days
     *
     * @return Date
     */
    public function setPreviousWeek()
    {
        for ($intI = 1; $intI <= 7; $intI++) {
            $this->setPreviousDay();
        }

        return $this;
    }

    /**
     * Sets the current time to the end of the day
     *
     * @return Date
     */
    public function setEndOfDay()
    {
        $this->setTime(23, 59, 59);
        return $this;
    }

    /**
     * Sets the current time to the beginning of the day
     *
     * @return Date
     */
    public function setBeginningOfDay()
    {
        $this->setTime(0, 0, 0);
        return $this;
    }

    /**
     * Swap the year part
     *
     * @param int $intYear
     *
     * @return Date
     */
    public function setIntYear($intYear)
    {
        if ($intYear < 0) {
            return $this;
        }

        if (StringUtil::length($intYear) == 2) {
            $intYear = (int) "20".$intYear;
        }
        if (StringUtil::length($intYear) == 1) {
            $intYear = (int) "200".$intYear;
        }

        $this->setDate($intYear, $this->getIntMonth(), $this->getIntDay());
        return $this;
    }

    /**
     * Swap the month part
     *
     * @param int $intMonth
     *
     * @return Date
     */
    public function setIntMonth($intMonth)
    {
        if ($intMonth < 1 || $intMonth > 12) {
            return $this;
        }

        $this->setDate($this->getIntYear(), $intMonth, $this->getIntDay());
        return $this;
    }

    /**
     * Swap the day part
     *
     * @param int $intDay
     *
     * @return Date
     */
    public function setIntDay($intDay)
    {
        if ($intDay < 1 || $intDay > 31) {
            return $this;
        }

        $this->setDate($this->getIntYear(), $this->getIntMonth(), $intDay);
        return $this;
    }

    /**
     * Swap the hour part
     *
     * @param int $intHour
     * @param bool $bitForce
     *
     * @return Date
     */
    public function setIntHour($intHour, $bitForce = false)
    {
        if (!$bitForce && ($intHour < 0 || $intHour > 23)) {
            return $this;
        }

        $this->setTime($intHour, $this->getIntMin(), $this->getIntSec());
        return $this;
    }

    /**
     * Swap the minutes part
     *
     * @param int $intMin
     * @param bool $bitForce
     *
     * @return Date
     */
    public function setIntMin($intMin, $bitForce = false)
    {
        if (!$bitForce && ($intMin < 0 || $intMin > 59)) {
            return $this;
        }

        $this->setTime($this->getIntHour(), $intMin, $this->getIntSec());
        return $this;
    }

    /**
     * Swap the seconds part
     *
     * @param int $intSec
     * @param bool $bitForce
     *
     * @return Date
     */
    public function setIntSec($intSec, $bitForce = false)
    {
        if (!$bitForce && ($intSec < 0 || $intSec > 59)) {
            return $this;
        }

        $this->setTime($this->getIntHour(), $this->getIntMin(), $intSec);
        return $this;
    }

    /**
     * Get the year part
     *
     * @return int
     */
    public function getIntYear()
    {
        return $this->format("Y");
    }

    /**
     * Get the month part
     *
     * @return int
     */
    public function getIntMonth()
    {
        return $this->format("m");
    }

    /**
     * Get the day part
     *
     * @return int
     */
    public function getIntDay()
    {
        return $this->format("d");
    }

    /**
     * Get the hour part
     *
     * @return int
     */
    public function getIntHour()
    {
        return $this->format("H");
    }

    /**
     * Get the min part
     *
     * @return int
     */
    public function getIntMin()
    {
        return $this->format("i");
    }

    /**
     * Get the sec part
     *
     * @return int
     */
    public function getIntSec()
    {
        return $this->format("s");
    }

    /**
     * Get the timstamp as a long value
     *
     * @return int
     */
    public function getLongTimestamp()
    {
        return $this->format("YmdHis");
    }

    /**
     * Set the current timestamp
     *
     * @param int $longTimestamp
     * @return Date
     */
    public function setLongTimestamp($longTimestamp)
    {
        list($year, $month, $day, $hour, $minute, $second) = self::splitLongTimestamp($longTimestamp);

        $this->setDate($year, $month, $day);
        $this->setTime($hour, $minute, $second);

        return $this;
    }

    /**
     * Splits the long format into the components
     *
     * @param string $longTimestamp
     * @return array
     */
    private static function splitLongTimestamp($longTimestamp)
    {
        return [
            substr($longTimestamp, 0, 4),
            substr($longTimestamp, 4, 2),
            substr($longTimestamp, 6, 2),
            substr($longTimestamp, 8, 2),
            substr($longTimestamp, 10, 2),
            substr($longTimestamp, 12, 2)
        ];
    }
}

