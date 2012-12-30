<?php
/*"******************************************************************************************************
*   (c) 2004-2006 by MulchProductions, www.mulchprod.de                                                 *
*   (c) 2007-2013 by Kajona, www.kajona.de                                                              *
*       Published under the GNU LGPL v2.1, see /system/licence_lgpl.txt                                 *
*-------------------------------------------------------------------------------------------------------*
*	$Id$                                            *
********************************************************************************************************/

/**
 * Data-container for fields provided by the usersource object.
 * Used by the admin-class in order to render the form.
 *
 * @author sidler@mulchprod.de
 * @since 3.4.1
 * @package module_usersource
 */
class class_usersources_form_entry {

    public static $INT_TYPE_TEXT = 1;
    public static $INT_TYPE_EMAIL = 2;
    public static $INT_TYPE_PASSWORD = 3;
    public static $INT_TYPE_LONGTEXT = 4;
    public static $INT_TYPE_DATE = 5;


	private $strName;
	private $intType;
	private $strValue;
    private $bitRequired;

    function __construct($strName, $intTyp, $strValue, $bitRequired) {
        $this->strName = $strName;
        $this->intType = $intTyp;
        $this->strValue = $strValue;
        $this->bitRequired = $bitRequired;
    }

    public function getStrName() {
        return $this->strName;
    }

    public function setStrName($strName) {
        $this->strName = $strName;
    }

    public function getIntType() {
        return $this->intType;
    }

    public function setIntType($intType) {
        $this->intType = $intType;
    }

    public function getStrValue() {
        return $this->strValue;
    }

    public function setStrValue($strValue) {
        $this->strValue = $strValue;
    }

    public function getBitRequired() {
        return $this->bitRequired;
    }

    public function setBitRequired($bitRequired) {
        $this->bitRequired = $bitRequired;
    }






}
