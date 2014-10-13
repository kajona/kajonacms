<?php
/*"******************************************************************************************************
*   (c) 2004-2006 by MulchProductions, www.mulchprod.de                                                 *
*   (c) 2007-2014 by Kajona, www.kajona.de                                                              *
*       Published under the GNU LGPL v2.1, see /system/licence_lgpl.txt                                 *
*-------------------------------------------------------------------------------------------------------*
*	$Id$                                            *
********************************************************************************************************/

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
class class_date {

    private $strStringFormat = "YYYYmmddHHiiss";

    private $strParseFormat = "YmdHis";

    private $longTimestamp;


    /**
     * Creates an instance of the class_date an initialises it with the current date if no value is passed.
     * If a value is passed (int, long, class_date), the value is used as the timestamp set to the new date-object.
     *
     * @param string|int|class_date $longInitValue
     */
    public function __construct($longInitValue = "") {

        if(is_object($longInitValue) && $longInitValue instanceof class_date)
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
     * @param class_date $objDateToCompare
     *
     * @return bool
     */
    public function isSameDay(class_date $objDateToCompare) {
        return uniSubstr($objDateToCompare->getLongTimestamp(), 0, 8) == uniSubstr($this->getLongTimestamp(), 0, 8);
    }

    /**
     * Generates a long-timestamp of the current time
     *
     * @return long
     */
    public static function getCurrentTimestamp() {
        $objDate = new class_date();
        return $objDate->getLongTimestamp();
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
        }

        if(isset($arrParams[$strFieldname . "_day"]) && $arrParams[$strFieldname . "_day"] != "") {
            $intDay = (int)$arrParams[$strFieldname . "_day"];
        }

        if(isset($arrParams[$strFieldname . "_hour"]) && $arrParams[$strFieldname . "_hour"] != "") {
            $intHour = (int)$arrParams[$strFieldname . "_hour"];
        }

        if(isset($arrParams[$strFieldname . "_minute"]) && $arrParams[$strFieldname . "_minute"] != "") {
            $intMinute = (int)$arrParams[$strFieldname . "_minute"];
        }

        if(isset($arrParams[$strFieldname . "_second"]) && $arrParams[$strFieldname . "_second"] != "") {
            $intMinute = (int)$arrParams[$strFieldname . "_second"];
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
    }


    /**
     * Allows to init the current class with an 32Bit int value representing the seconds since 1970.
     * PHPs' time() returns 32Bit ints, too.
     *
     * @param int $intTimestamp
     *
     * @return \class_date
     */
    public function setTimeInOldStyle($intTimestamp) {
        //parse timestamp in order to get schema.
        $this->longTimestamp = date($this->strParseFormat, (int)$intTimestamp);
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
     * @return \class_date
     */
    public function setPreviousDay() {
        $this->setTimeInOldStyle($this->getTimeInOldStyle() - 24 * 3600);
        return $this;
    }

    /**
     * Sets the current day to the next day.
     * Includes the handling of month / year shifts.
     *
     * @since 3.4
     * @return \class_date
     */
    public function setNextDay() {
        $this->setTimeInOldStyle($this->getTimeInOldStyle() + 24 * 3600);
        return $this;
    }

    /**
     * Shifts the current month into the future by one.
     * If the current month has 31 days, the next one only 30, the
     * logic will remain at 30.
     *
     * @return \class_date
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

        return $this;
    }


    /**
     * Shifts the current month into the past by one.
     * If the current month has 31 days, the previous one only 30, the
     * logic will remain at 30.
     *
     * @return \class_date
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

        return $this;
    }

    /**
     * Shifts the current date one week into the future, so seven days
     *
     * @return \class_date
     */
    public function setNextWeek() {
        for($intI = 1; $intI <= 7; $intI++)
            $this->setNextDay();

        return $this;
    }

    /**
     * Shifts the current date one week into the future, so seven days
     *
     * @return \class_date
     */
    public function setPreviousWeek() {
        for($intI = 1; $intI <= 7; $intI++)
            $this->setPreviousDay();

        return $this;
    }

    /**
     * Swap the year part
     *
     * @param int $intYear
     *
     * @return \class_date
     */
    public function setIntYear($intYear) {
        if(uniStrlen($intYear) == 2) {
            $intYear = "20" . $intYear;
        }
        if(uniStrlen($intYear) == 1) {
            $intYear = "200" . $intYear;
        }

        $strYear = sprintf("%04s", $intYear);
        $this->longTimestamp = substr_replace($this->longTimestamp, $strYear, 0, 4);
        return $this;
    }

    /**
     * Swap the month part
     *
     * @param int $intMonth
     *
     * @return \class_date
     */
    public function setIntMonth($intMonth) {
        if($intMonth < 1 || $intMonth > 12) {
            return $this;
        }

        $strMonth = sprintf("%02s", $intMonth);
        $this->longTimestamp = substr_replace($this->longTimestamp, $strMonth, 4, 2);
        return $this;
    }

    /**
     * Swap the day part
     *
     * @param int $intDay
     *
     * @return \class_date
     */
    public function setIntDay($intDay) {
        if($intDay < 1 || $intDay > 31) {
            return $this;
        }

        $strDay = sprintf("%02s", $intDay);
        $this->longTimestamp = substr_replace($this->longTimestamp, $strDay, 6, 2);
        return $this;
    }

    /**
     * Swap the hour part
     *
     * @param int $intHour
     * @param bool $bitForce
     *
     * @return \class_date
     */
    public function setIntHour($intHour, $bitForce = false) {
        if(!$bitForce && ($intHour < 0 || $intHour > 23)) {
            return $this;
        }

        $strHour = sprintf("%02s", $intHour);
        $this->longTimestamp = substr_replace($this->longTimestamp, $strHour, 8, 2);
        return $this;
    }

    /**
     * Swap the minutes part
     *
     * @param int $intMin
     * @param bool $bitForce
     *
     * @return \class_date
     */
    public function setIntMin($intMin, $bitForce = false) {
        if(!$bitForce && ($intMin < 0 || $intMin > 59)) {
            return $this;
        }

        $strMin = sprintf("%02s", $intMin);
        $this->longTimestamp = substr_replace($this->longTimestamp, $strMin, 10, 2);
        return $this;
    }

    /**
     * Swap the seconds part
     *
     * @param int $intSec
     * @param bool $bitForce
     *
     * @return \class_date
     */
    public function setIntSec($intSec, $bitForce = false) {
        if(!$bitForce && ($intSec < 0 || $intSec > 59)) {
            return $this;
        }

        $strSec = sprintf("%02s", $intSec);
        $this->longTimestamp = substr_replace($this->longTimestamp, $strSec, 12, 2);
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
     * @return long
     */
    public function getLongTimestamp() {
        return $this->longTimestamp;
    }

    /**
     * Set the current timestamp
     *
     * @param long $longTimestamp
     *
     * @return \class_date
     */
    public function setLongTimestamp($longTimestamp) {
        if(uniEreg("([0-9]){14}", $longTimestamp)) {
            $this->longTimestamp = $longTimestamp;
        }

        return $this;
    }

}

