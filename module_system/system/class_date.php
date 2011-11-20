<?php
/*"******************************************************************************************************
*   (c) 2004-2006 by MulchProductions, www.mulchprod.de                                                 *
*   (c) 2007-2011 by Kajona, www.kajona.de                                                              *
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
 */
class class_date {

    private $strStringFormat = "YYYYmmddHHiiss";

    private $strParseFormat = "YmdHis";

    private $longTimestamp;


    private $arrModul;

	/**
	 * Creates an instance of the class_date an initialises it with the current date.
	 */
	public function __construct($longInitValue = "") {
		$this->arrModul["name"] 		= "class_date";
		$this->arrModul["author"] 		= "sidler@mulchprod.de";
		$this->arrModul["moduleId"]		= _system_modul_id_;


        if($longInitValue == "") {
            $this->setTimeInOldStyle(time());
        }
        else if($longInitValue == "0") {
            $this->setLongTimestamp("00000000000000");
        }
        else {
            if(strlen($longInitValue) == 14)
                $this->setLongTimestamp($longInitValue);
            else
                $this->setTimeInOldStyle($longInitValue);
        }
	}

    /**
     * Returns the string-based version of the long-value currently maintained.
     *
     * @return string
     */
    public function __toString() {
        return $this->longTimestamp."";
    }

    /**
     * Generates a long-timestamp of the current time
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

        if(isset($arrParams[$strFieldname."_year"]) && $arrParams[$strFieldname."_year"] != "")
            $intYear = (int)$arrParams[$strFieldname."_year"];

        if(isset($arrParams[$strFieldname."_month"]) && $arrParams[$strFieldname."_month"] != "")
            $intMonth = (int)$arrParams[$strFieldname."_month"];

        if(isset($arrParams[$strFieldname."_day"]) && $arrParams[$strFieldname."_day"] != "")
            $intDay = (int)$arrParams[$strFieldname."_day"];

        if(isset($arrParams[$strFieldname."_hour"]) && $arrParams[$strFieldname."_hour"] != "")
            $intHour = (int)$arrParams[$strFieldname."_hour"];

        if(isset($arrParams[$strFieldname."_minute"]) && $arrParams[$strFieldname."_minute"] != "")
            $intMinute = (int)$arrParams[$strFieldname."_minute"];

        if(isset($arrParams[$strFieldname."_second"]) && $arrParams[$strFieldname."_second"] != "")
            $intMinute = (int)$arrParams[$strFieldname."_second"];

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
     */
    public function setTimeInOldStyle($intTimestamp) {
        //parse timestamp in order to get schema.
        $this->longTimestamp = date($this->strParseFormat, (int)$intTimestamp);
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
     */
    public function setPreviousDay() {
        $this->setTimeInOldStyle($this->getTimeInOldStyle()-24*3600);
    }

    /**
     * Sets the current day to the next day.
     * Includes the handling of month / year shifts.
     *
     * @since 3.4
     */
    public function setNextDay() {
        $this->setTimeInOldStyle($this->getTimeInOldStyle()+24*3600);
    }

    /**
     * Swap the year part
     *
     * @param int $intYear
     */
    public function setIntYear($intYear) {
        if(uniStrlen($intYear) == 2)
            $intYear = "20".$intYear;
        if(uniStrlen($intYear) == 1)
            $intYear = "200".$intYear;

        $strYear = sprintf("%04s", $intYear);
        $this->longTimestamp = substr_replace($this->longTimestamp, $strYear, 0, 4);
    }

    /**
     * Swap the month part
     *
     * @param int $intYear
     */
    public function setIntMonth($intMonth) {
        if($intMonth < 1 || $intMonth > 12)
            return;

        $strMonth = sprintf("%02s", $intMonth);
        $this->longTimestamp = substr_replace($this->longTimestamp, $strMonth, 4, 2);
    }

    /**
     * Swap the day part
     *
     * @param int $intYear
     */
    public function setIntDay($intDay) {
        if($intDay < 1 || $intDay > 31)
            return;

        $strDay = sprintf("%02s", $intDay);
        $this->longTimestamp = substr_replace($this->longTimestamp, $strDay, 6, 2);
    }

    /**
     * Swap the hour part
     *
     * @param int $intYear
     */
    public function setIntHour($intHour, $bitForce = false) {
        if(!$bitForce && ($intHour < 0 || $intHour > 23))
            return;

        $strHour = sprintf("%02s", $intHour);
        $this->longTimestamp = substr_replace($this->longTimestamp, $strHour, 8, 2);
    }

    /**
     * Swap the minutes part
     *
     * @param int $intYear
     */
    public function setIntMin($intMin, $bitForce = false) {
        if(!$bitForce && ($intMin < 0 || $intMin > 59))
            return;

        $strMin = sprintf("%02s", $intMin);
        $this->longTimestamp = substr_replace($this->longTimestamp, $strMin, 10, 2);
    }

    /**
     * Swap the seconds part
     *
     * @param int $intYear
     */
    public function setIntSec($intSec, $bitForce = false) {
        if(!$bitForce && ($intSec < 0 || $intSec > 59))
            return;

        $strSec = sprintf("%02s", $intSec);
        $this->longTimestamp = substr_replace($this->longTimestamp, $strSec, 12, 2);
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
     */
    public function setLongTimestamp($longTimestamp) {
        if(uniEreg("([0-9]){14}", $longTimestamp))
            $this->longTimestamp = $longTimestamp;
    }



}

?>