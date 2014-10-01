<?php
/*"******************************************************************************************************
*   (c) 2007-2014 by Kajona, www.kajona.de                                                              *
*       Published under the GNU LGPL v2.1, see /system/licence_lgpl.txt                                 *
********************************************************************************************************/

/**
 * The enum base class may be used to create php-based enum approaches.
 * Use it as following:
 *
 * To define a new enum extend this class, defining all possible values in protected static $arrValues
 *
 * class_test_enum extends class_enum {
 *   protected static $arrValues("a", "b", "c")
 * }
 *
 * Later on you may access all possible enums using magical static methods, returning a
 * $objEnum = class_test_enum::a()
 *
 * Compare it using the internal equals:
 * $objEnum->equals(class_test_enum::a())
 *
 * @package module_system
 * @author sidler@mulchprod.de
 * @since 4.6
 */
abstract class class_enum {

    private $strValue = null;

    protected static $arrAllowedValues = array();

    private function __construct($strCurValue) {
        $this->strValue = $strCurValue;
    }

    /**
     * Helper to generate possible instances of the current enum values.
     *
     * @param $strName
     *
     * @throws class_exception
     * @return class_enum
     */
    public static function __callStatic($strName, $arrArguments) {
        if(in_array($strName, static::$arrAllowedValues)) {
            return new static($strName);
        }
        throw new class_exception($strName ." is not allowed for enum ".get_called_class(), class_exception::$level_FATALERROR);
    }

    /**
     * Use this method to compare enum-instances
     * @param class_enum $objB
     *
     * @return bool
     */
    public function equals(class_enum $objB) {
        return $this->strValue == $objB->strValue && get_class($this) == get_class($objB);
    }

    public function __toString() {
        return $this->strValue."";
    }



}

