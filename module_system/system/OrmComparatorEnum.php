<?php
/*"******************************************************************************************************
*   (c) 2004-2006 by MulchProductions, www.mulchprod.de                                                 *
*   (c) 2007-2015 by Kajona, www.kajona.de                                                              *
*       Published under the GNU LGPL v2.1, see /system/licence_lgpl.txt                                 *
********************************************************************************************************/

namespace Kajona\System\System;


/**
 * A enum indicating the entry-point for the current request, e.g. INDEX or XML
 *
 * @method static OrmComparatorEnum GreaterThen()
 * @method static OrmComparatorEnum GreaterThenEquals()
 * @method static OrmComparatorEnum LessThen()
 * @method static OrmComparatorEnum LessThenEquals()
 * @method static OrmComparatorEnum Equal()
 * @method static OrmComparatorEnum NotEqual()
 * @method static OrmComparatorEnum Like()
 *
 *
 * @package module_system
 * @author sidler@mulchprod.de
 * @since 4.7
 */
class OrmComparatorEnum extends EnumBase {
    /**
     * Return the array of possible, so allowed values for the current enum
     *
     * @return string[]
     */
    protected function getArrValues() {
        return array("GreaterThen", "GreaterThenEquals", "LessThen", "LessThenEquals", "Equal", "NotEqual", "Like");
    }


    public function getEnumAsSqlString() {
        if($this->equals(OrmComparatorEnum::GreaterThen())) {
            return ">";
        }

        if($this->equals(OrmComparatorEnum::GreaterThenEquals())) {
            return ">=";
        }

        if($this->equals(OrmComparatorEnum::LessThen())) {
            return "<";
        }

        if($this->equals(OrmComparatorEnum::LessThenEquals())) {
            return "<=";
        }

        if($this->equals(OrmComparatorEnum::Equal())) {
            return "=";
        }

        if($this->equals(OrmComparatorEnum::NotEqual())) {
            return "!=";
        }

        if($this->equals(OrmComparatorEnum::Like())) {
            return "LIKE";
        }

        throw new class_orm_exception("Unknown sql comparator", Exception::$level_ERROR);
    }
}

