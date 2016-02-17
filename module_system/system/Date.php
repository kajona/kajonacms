<?php
/*"******************************************************************************************************
*   (c) 2004-2006 by MulchProductions, www.mulchprod.de                                                 *
*   (c) 2007-2015 by Kajona, www.kajona.de                                                              *
*       Published under the GNU LGPL v2.1, see /system/licence_lgpl.txt                                 *
*-------------------------------------------------------------------------------------------------------*
*	$Id$                                            *
********************************************************************************************************/

namespace Kajona\System\System;


/**
 * The date class is used to handle all kind of date and time related operations.
 * As soon as the most installations will run on PHP >= 5.3.0, this class will be
 * wrapper to phps' DateTime class.
 * Up till then, the class provides a few ways to handle date and convert them to
 * a long value not being limited by the 32 bit time() boundaries (> 1970 && < 2038).
 * Use this class only in cases the other way won't work, so e.g. for birthdays.
 *
 * @package module_system
 * @author sidler@mulchprod.de
 */
class Date {

    const INT_DAY_SUNDAY = 0;
    const INT_DAY_MONDAY = 1;
    const INT_DAY_TUESDAY = 2;
    const INT_DAY_WEDNESDAY = 3;
    const INT_DAY_THURSDAY = 4;
    const INT_DAY_FRIDAY = 5;
    const INT_DAY_SATURDAY = 6;

    private $strStringFormat = "YYYYmmddHHiiss";

    private $strDateTimeFormat = "YYMMDDHHIISS";

    private $strParseFormat = "YmdHis";

    private $longTimestamp;


    /**
     * Creates an instance of the Date an initialises it with the current date if no value is passed.
     * If a value is passed (int, long, Date), the value is used as the timestamp set to the new date-object.
     *
     * @param string|int|Date $longInitValue
     */
    public function __construct($longInitValue = "") {

        if(is_object($longInitValue) && $longInitValue instanceof Date)
            $longInitValue = $longInitValue->getLongTimestamp();

        if($longInitValue == "0") {
            $this->setLongTimestamp("00000000000000");
        }
        else if($longInitValue == "") {
            $this->setTimeInOldStyle(time());
        }
        else {
            if(strlen($longInitValue) == 14) {
                $this->setLongTimestamp($longInitValue);
            }
            else {
                $this->setTimeInOldStyle($longInitValue);
            }
        }
    }

    /**
     * Returns the string-based version of the long-value currently maintained.
     *
     * @return string
     */
    public function __toString() {
        return $this->longTimestamp . "";
    }

    /**
     * Compares the current date against another date and evaluates, of both dates reference the same day.
     *
     * @param Date $objDateToCompare
     *
     * @return bool
     */
    public function isSameDay(Date $objDateToCompare) {
        return uniSubstr($objDateToCompare->getLongTimestamp(), 0, 8) == uniSubstr($this->getLongTimestamp(), 0, 8);
    }

    /**
     * Generates a long-timestamp of the current time
     *
     * @return int
     */
    public static function getCurrentTimestamp() {
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
     */
    public function generateDateFromParams($strFieldname, $arrParams) {
        $intYear = "0000";
        $intMonth = 00;
        $intDay = 00;
        $intHour = 00;
        $intMinute = 00;
        $intSecond = 00;

        if(isset($arrParams[$strFieldname . "_year"]) && $arrParams[$strFieldname . "_year"] != "") {
            $intYear = (int)$arrParams[$strFieldname . "_year"];
        }

        if(isset($arrParams[$strFieldname . "_month"]) && $arrParams[$strFieldname . "_month"] != "") {
            $intMonth = (int)$arrParams[$strFieldname . "_month"];
            if($intMonth > 12) {
                $intMonth = 12;
            }
        }

        if(isset($arrParams[$strFieldname . "_day"]) && $arrParams[$strFieldname . "_day"] != "") {
            $intDay = (int)$arrParams[$strFieldname . "_day"];
            if($intDay > 31) {
                $intDay = 31;
            }
        }

        if(isset($arrParams[$strFieldname . "_hour"]) && $arrParams[$strFieldname . "_hour"] != "") {
            $intHour = (int)$arrParams[$strFieldname . "_hour"];
            if($intHour > 23) {
                $intHour = 23;
            }
        }

        if(isset($arrParams[$strFieldname . "_minute"]) && $arrParams[$strFieldname . "_minute"] != "") {
            $intMinute = (int)$arrParams[$strFieldname . "_minute"];
            if($intMinute > 59) {
                $intMinute = 59;
            }
        }

        if(isset($arrParams[$strFieldname . "_second"]) && $arrParams[$strFieldname . "_second"] != "") {
            $intMinute = (int)$arrParams[$strFieldname . "_second"];
            if($intMinute > 59) {
                $intMinute = 59;
            }
        }

        //see if the other parts may be read directly
        if(isset($arrParams[$strFieldname])) {

            if(strlen($arrParams[$strFieldname]) == strlen($this->strStringFormat)) {
                $this->setLongTimestamp($arrParams[$strFieldname]);
                return;
            }

            $objDateTime = DateTime::createFromFormat(class_carrier::getInstance()->getObjLang()->getLang("dateStyleShort", "system"), $arrParams[$strFieldname]);
            if($objDateTime) {
                $intTimestamp = $objDateTime->getTimestamp();
                $intYear = strftime("%Y", $intTimestamp);
                $intMonth = strftime("%m", $intTimestamp);
                $intDay = strftime("%d", $intTimestamp);
            }
        }

        $this->setIntYear($intYear);
        $this->setIntMonth($intMonth);
        $this->setIntDay($intDay);
        $this->setIntHour($intHour);
        $this->setIntMin($intMinute);
        $this->setIntSec($intSecond);

//        $this->validateDate();
    }


    private function validateDate() {
        if(!uniEreg("([0-9]){14}", $this->getLongTimestamp()) || $this->getLongTimestamp() < 0) {
            echo $this->__toString()."\n";
            if (function_exists("debug_backtrace")) {
                $arrStack = debug_backtrace();

                foreach ($arrStack as $intPos => $arrValue) {
                    echo (isset($arrValue["file"]) ? $arrValue["file"] : "n.a.")."\n\t Row ".(isset($arrValue["line"]) ? $arrValue["line"] : "n.a.").", function ".$arrStack[$intPos]["function"]."\n";
                }
            }

            die();
        }
    }

    /**
     * Allows to init the current class with an 32Bit int value representing the seconds since 1970.
     * PHPs' time() returns 32Bit ints, too.
     *
     * @param int $intTimestamp
     *
     * @return \Date
     */
    public function setTimeInOldStyle($intTimestamp) {
        //parse timestamp in order to get schema.
        $this->longTimestamp = date($this->strParseFormat, (int)$intTimestamp);

//        $this->validateDate();
        return $this;
    }

    /**
     * Converts the current long-timestamp to an old-fashioned int-timestamp (seconds since 1970)
     *
     * @return int
     */
    public function getTimeInOldStyle() {
        return mktime($this->getIntHour(), $this->getIntMin(), $this->getIntSec(), $this->getIntMonth(), $this->getIntDay(), $this->getIntYear());
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
    public function getIntDayOfWeek() {
        return date('w', $this->getTimeInOldStyle());
    }

    /**
     * Sets the current day to the previous day.
     * Includes the handling of month / year shifts.
     *
     * @since 3.4
     * @return \Date
     */
    public function setPreviousDay() {
        $objDate = DateTime::createFromFormat($this->strParseFormat, $this->getLongTimestamp());
        if($objDate == null)  {
            throw new class_exception("Can't parse date ".$this->getLongTimestamp(), class_exception::$level_ERROR);
        }

        $objDate->sub(DateInterval::createFromDateString('1 day'));
        $this->setTimeInOldStyle($objDate->getTimestamp());
//        $this->validateDate();
        return $this;
    }

    /**
     * Sets the current day to the next day.
     * Includes the handling of month / year shifts.
     *
     * @since 3.4
     * @return \Date
     */
    public function setNextDay() {
        $objDate = DateTime::createFromFormat($this->strParseFormat, $this->getLongTimestamp());
        $objDate->add(DateInterval::createFromDateString('1 day'));
        $this->setTimeInOldStyle($objDate->getTimestamp());
//        $this->validateDate();
        return $this;
    }

    /**
     * Shifts the current month into the future by one.
     * If the current month has 31 days, the next one only 30, the
     * logic will remain at 30.
     *
     * @return \Date
     */
    public function setNextMonth() {
        $objSourceDate = clone $this;

        $this->setNextDay();
        $intDaysAdded = 1;
        while($this->getIntDay() != $objSourceDate->getIntDay()) {
            $this->setNextDay();
            $intDaysAdded++;

            //if we skip a month border, roll back until the previous months last day
            if($intDaysAdded > 31) {
                $this->setIntDay(1);
                $this->setPreviousDay();
                //and jump out
                break;
            }
        }

        $this->setIntHour($objSourceDate->getIntHour());
        $this->setIntMin($objSourceDate->getIntMin());
        $this->setIntSec($objSourceDate->getIntSec());

//        $this->validateDate();
        return $this;
    }


    /**
     * Shifts the current month into the past by one.
     * If the current month has 31 days, the previous one only 30, the
     * logic will remain at 30.
     *
     * @return \Date
     */
    public function setPreviousMonth() {
        $objSourceDate = clone $this;

        $this->setPreviousDay();
        $intDaysSubtracted = 1;
        while($this->getIntDay() != $objSourceDate->getIntDay()) {
            $this->setPreviousDay();
            $intDaysSubtracted++;

            //if we skip a month border, roll back until the next months last day
            if($intDaysSubtracted > 31) {
                $this->setNextMonth();
                $this->setIntDay(1);
                $this->setPreviousDay();
                //and jump out
                break;
            }
        }

        $this->setIntHour($objSourceDate->getIntHour());
        $this->setIntMin($objSourceDate->getIntMin());
        $this->setIntSec($objSourceDate->getIntSec());

//        $this->validateDate();
        return $this;
    }

    /**
     * Shifts the current year into the past by one.
     *
     * @return \Date
     */
    public function setPreviousYear() {
        $intCurrentDay = $this->getIntDay();
        $this->setIntDay(1);
        for($intI = 0; $intI < 12; $intI++) {
            $this->setPreviousMonth();
        }
        $this->setIntDay($intCurrentDay);
//        $this->validateDate();
        return $this;
    }

    /**
     * Shifts the current year into the future by one.
     *
     * @return \Date
     */
    public function setNextYear() {
        $intCurrentDay = $this->getIntDay();
        $this->setIntDay(1);
        for($intI = 0; $intI < 12; $intI++) {
            $this->setNextMonth();
        }
        $this->setIntDay($intCurrentDay);
//        $this->validateDate();
        return $this;
    }

    /**
     * Shifts the current date one week into the future, so seven days
     *
     * @return \Date
     */
    public function setNextWeek() {
        for($intI = 1; $intI <= 7; $intI++)
            $this->setNextDay();

//        $this->validateDate();
        return $this;
    }

    /**
     * Shifts the current date one week into the future, so seven days
     *
     * @return \Date
     */
    public function setPreviousWeek() {
        for($intI = 1; $intI <= 7; $intI++)
            $this->setPreviousDay();

        //$this->validateDate();
        return $this;
    }

    /**
     * Sets the current time to the end of the day
     *
     * @return \Date
     */
    public function setEndOfDay() {
        return $this->setIntHour(23)->setIntMin(59)->setIntSec(59);
    }

    /**
     * Sets the current time to the beginning of the day
     *
     * @return \Date
     */
    public function setBeginningOfDay() {
        return $this->setIntHour(0)->setIntMin(0)->setIntSec(0);
    }

    /**
     * Swap the year part
     *
     * @param int $intYear
     *
     * @return \Date
     */
    public function setIntYear($intYear) {
        if($intYear < 0)
            return $this;

        if(uniStrlen($intYear) == 2) {
            $intYear = "20" . $intYear;
        }
        if(uniStrlen($intYear) == 1) {
            $intYear = "200" . $intYear;
        }

        $strYear = sprintf("%04s", $intYear);
        $this->longTimestamp = substr_replace($this->longTimestamp, $strYear, 0, 4);

//        $this->validateDate();
        return $this;
    }

    /**
     * Swap the month part
     *
     * @param int $intMonth
     *
     * @return \Date
     */
    public function setIntMonth($intMonth) {
        if($intMonth < 1 || $intMonth > 12) {
            return $this;
        }

        $strMonth = sprintf("%02s", $intMonth);
        $this->longTimestamp = substr_replace($this->longTimestamp, $strMonth, 4, 2);
//        $this->validateDate();
        return $this;
    }

    /**
     * Swap the day part
     *
     * @param int $intDay
     *
     * @return \Date
     */
    public function setIntDay($intDay) {
        if($intDay < 1 || $intDay > 31) {
            return $this;
        }

        $strDay = sprintf("%02s", $intDay);
        $this->longTimestamp = substr_replace($this->longTimestamp, $strDay, 6, 2);
//        $this->validateDate();
        return $this;
    }

    /**
     * Swap the hour part
     *
     * @param int $intHour
     * @param bool $bitForce
     *
     * @return \Date
     */
    public function setIntHour($intHour, $bitForce = false) {
        if(!$bitForce && ($intHour < 0 || $intHour > 23)) {
            return $this;
        }

        $strHour = sprintf("%02s", $intHour);
        $this->longTimestamp = substr_replace($this->longTimestamp, $strHour, 8, 2);

//        $this->validateDate();
        return $this;
    }

    /**
     * Swap the minutes part
     *
     * @param int $intMin
     * @param bool $bitForce
     *
     * @return \Date
     */
    public function setIntMin($intMin, $bitForce = false) {
        if(!$bitForce && ($intMin < 0 || $intMin > 59)) {
            return $this;
        }

        $strMin = sprintf("%02s", $intMin);
        $this->longTimestamp = substr_replace($this->longTimestamp, $strMin, 10, 2);

//        $this->validateDate();
        return $this;
    }

    /**
     * Swap the seconds part
     *
     * @param int $intSec
     * @param bool $bitForce
     *
     * @return \Date
     */
    public function setIntSec($intSec, $bitForce = false) {
        if(!$bitForce && ($intSec < 0 || $intSec > 59)) {
            return $this;
        }

        $strSec = sprintf("%02s", $intSec);
        $this->longTimestamp = substr_replace($this->longTimestamp, $strSec, 12, 2);

//        $this->validateDate();
        return $this;
    }

    /**
     * Get the year part
     *
     * @return int
     */
    public function getIntYear() {
        return substr($this->longTimestamp, 0, 4);
    }

    /**
     * Get the month part
     *
     * @return int
     */
    public function getIntMonth() {
        return substr($this->longTimestamp, 4, 2);
    }

    /**
     * Get the day part
     *
     * @return int
     */
    public function getIntDay() {
        return substr($this->longTimestamp, 6, 2);
    }

    /**
     * Get the hour part
     *
     * @return int
     */
    public function getIntHour() {
        return substr($this->longTimestamp, 8, 2);
    }

    /**
     * Get the min part
     *
     * @return int
     */
    public function getIntMin() {
        return substr($this->longTimestamp, 10, 2);
    }

    /**
     * Get the sec part
     *
     * @return int
     */
    public function getIntSec() {
        return substr($this->longTimestamp, 12, 2);
    }

    /**
     * Get the timstamp as a long value
     *
     * @return int
     */
    public function getLongTimestamp() {
        return $this->longTimestamp;
    }

    /**
     * Set the current timestamp
     *
     * @param int $longTimestamp
     *
     * @return \Date
     */
    public function setLongTimestamp($longTimestamp) {
        if(uniEreg("([0-9]){14}", $longTimestamp)) {
            $this->longTimestamp = $longTimestamp;
        }
        return $this;
    }

}

