<?php
/*"******************************************************************************************************
*   (c) 2004-2006 by MulchProductions, www.mulchprod.de                                                 *
*   (c) 2007-2012 by Kajona, www.kajona.de                                                              *
*       Published under the GNU LGPL v2.1, see /system/licence_lgpl.txt                                 *
*-------------------------------------------------------------------------------------------------------*
*	$Id$                                            *
********************************************************************************************************/

/**
 * Data-Container for a single calendar-entry.
 * May be produces by classes in order to be written into the calendar.
 *
 * @package module_dashboard
 * @author sidler@mulchprod.de
 * @since 3.4
 */
class class_calendarentry {

    private $strName;
    private $strSecondLine;
    private $strClass = "calendarEvent";
    private $strSystemid = "";
    private $strHighlightId = "";

    /**
     *
     * @var class_date
     */
    private $objDate;

    public function getStrName() {
        return $this->strName;
    }

    public function setStrName($strName) {
        $this->strName = $strName;
    }

    public function getObjDate() {
        return $this->objDate;
    }

    public function setObjDate($objDate) {
        $this->objDate = $objDate;
    }

    public function getStrClass() {
        return $this->strClass;
    }

    public function setStrClass($strClass) {
        $this->strClass = $strClass;
    }

    public function getStrSecondLine() {
        return $this->strSecondLine;
    }

    public function setStrSecondLine($strSecondLine) {
        $this->strSecondLine = $strSecondLine;
    }

    public function getStrSystemid() {
        return $this->strSystemid;
    }

    public function setStrSystemid($strSystemid) {
        $this->strSystemid = $strSystemid;
    }

    public function getStrHighlightId() {
        return $this->strHighlightId;
    }

    public function setStrHighlightId($strHighlightId) {
        $this->strHighlightId = $strHighlightId;
    }





}

