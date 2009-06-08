<?php
/*"******************************************************************************************************
*   (c) 2004-2006 by MulchProductions, www.mulchprod.de                                                 *
*   (c) 2007-2009 by Kajona, www.kajona.de                                                              *
*       Published under the GNU LGPL v2.1, see /system/licence_lgpl.txt                                 *
*-------------------------------------------------------------------------------------------------------*
*	$Id: class_cookie.php 2353 2008-12-31 15:22:01Z sidler $                                            *
********************************************************************************************************/

/**
 * The date class is used to handle all kind of date and time related operations.
 * As soon as the most installations will run on PHP >= 5.3.0, this class will be
 * wrapper to phps' DateTime class.
 * Up till then, the class provides a few ways to handle date and convert them to
 * a long value not being limited by the 32 Bit time() boundaries (> 1970 && < 2038).
 * Use this class only in cases the other way won't work, so e.g. for birthdays.
 *
 * @package modul_system
 */
class class_date {

    private $strStringFormat = "YYYYmmddHHiiss";

    private $strParseFormat = "YmdHis";

    private $longTimestamp;
    

    private $arrModul;

	/**
	 * Contructor
	 */
	public function __construct() {
		$this->arrModul["name"] 		= "class_date";
		$this->arrModul["author"] 		= "sidler@mulchprod.de";
		$this->arrModul["moduleId"]		= _system_modul_id_;
	}

    /**
     * Returns the string-based version of the long-value currently maintained.
     *
     * @return string
     */
    public function __toString() {
        return $this->longTimestamp;
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
     * Swap the year part
     *
     * @param int $intYear
     */
    public function setIntYear($intYear) {
        $strYear = sprintf("%04s", $intYear);
        $this->longTimestamp = substr_replace($this->longTimestamp, $strYear, 0, 4);
    }

    /**
     * Swap the month part
     *
     * @param int $intYear
     */
    public function setIntMonth($intMonth) {
        $strMonth = sprintf("%02s", $intMonth);
        $this->longTimestamp = substr_replace($this->longTimestamp, $strMonth, 4, 2);
    }

    /**
     * Swap the day part
     *
     * @param int $intYear
     */
    public function setIntDay($intDay) {
        $strDay = sprintf("%02s", $intDay);
        $this->longTimestamp = substr_replace($this->longTimestamp, $strDay, 6, 2);
    }

    /**
     * Swap the hour part
     *
     * @param int $intYear
     */
    public function setIntHour($intHour) {
        $strHour = sprintf("%02s", $intHour);
        $this->longTimestamp = substr_replace($this->longTimestamp, $strHour, 8, 2);
    }

    /**
     * Swap the minutes part
     *
     * @param int $intYear
     */
    public function setIntMin($intMin) {
        $strMin = sprintf("%02s", $intMin);
        $this->longTimestamp = substr_replace($this->longTimestamp, $strMin, 10, 2);
    }

    /**
     * Swap the seconds part
     *
     * @param int $intYear
     */
    public function setIntSec($intSec) {
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
        $this->longTimestamp = $longTimestamp;
    }


	
} 

?>