<?php
/*"******************************************************************************************************
*   (c) 2007-2016 by Kajona, www.kajona.de                                                              *
*       Published under the GNU LGPL v2.1, see /system/licence_lgpl.txt                                 *
********************************************************************************************************/

namespace Kajona\System\System;


/**
 * The enum base class may be used to create php-based enum approaches.
 * Use it as following:
 *
 * To define a new enum extend this class, defining all possible values in static function getArrValues().
 * Use @ method annotations to get full autocomplete support in your IDE
 *
 * /**
 *  *
 *  * @ method static TestEnum A()
 *  * @ method static TestEnum B()
 *  *
 * class TestEnum extends EnumBase {
 *   protected function getArrValues() { return array("A", "B"); }
 * }
 *
 * Later on you may access all possible enums using magical static methods, returning A
 * $objEnum = TestEnum::A()
 *
 * Compare it using the internal equals:
 * $objEnum->equals(TestEnum::A())
 *
 * @package module_system
 * @author sidler@mulchprod.de
 * @since 4.6
 */
abstract class EnumBase
{


    /**
     * Return the array of possible, so allowed values for the current enum
     *
     * @return string[]
     */
    abstract protected function getArrValues();

    /**
     * @var string
     */
    private $strValue = null;

    /**
     * No direct instances, plz.
     * Create instances using the static magic method call.
     *
     * @param $strCurValue
     */
    private function __construct($strCurValue)
    {
        $this->strValue = $strCurValue;
    }

    /**
     * Helper to generate possible instances of the current enum values.
     *
     * @param string $strName
     *
     * @throws Exception
     * @return EnumBase
     */
    public static function __callStatic($strName, $arrArguments)
    {
        $objEnum = new static($strName);
        if (!in_array($strName, $objEnum->getArrValues())) {
            throw new Exception($strName." is not allowed for enum ".get_called_class(), Exception::$level_FATALERROR);
        }

        return $objEnum;
    }

    /**
     * Use this method to compare enum-instances
     *
     * @param EnumBase $objB
     *
     * @return bool
     */
    public function equals(EnumBase $objB)
    {
        return $this->strValue == $objB->strValue && get_class($this) == get_class($objB);
    }

    /**
     * Prints the enums current value
     *
     * @return string
     */
    public function __toString()
    {
        return $this->strValue."";
    }

}

