<?php
/*"******************************************************************************************************
*   (c) 2004-2006 by MulchProductions, www.mulchprod.de                                                 *
*   (c) 2007-2016 by Kajona, www.kajona.de                                                              *
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
 * @method static OrmComparatorEnum In()
 * @method static OrmComparatorEnum NotIn()
 * @method static OrmComparatorEnum InOrEmpty()
 * @method static OrmComparatorEnum NotInOrEmpty()
 *
 *
 * @package module_system
 * @author sidler@mulchprod.de
 * @since 4.7
 */
class OrmComparatorEnum extends EnumBase
{
    const GreaterThen = ">";
    const GreaterThenEquals = ">=";
    const LessThen = "<";
    const LessThenEquals = "<=";
    const Equal = "=";
    const NotEqual = "!=";
    const Like = "LIKE";
    const In = "IN";
    const NotIn = "NOT IN";
    const InOrEmpty = "IN_OR_EMPTY";
    const NotInOrEmpty = "NOTIN_OR_EMPTY";

    /**
     * Return the array of possible, so allowed values for the current enum
     *
     * @return string[]
     */
    protected function getArrValues()
    {
        return array("GreaterThen", "GreaterThenEquals", "LessThen", "LessThenEquals", "Equal", "NotEqual", "Like", "In", "NotIn", "InOrEmpty", "NotInOrEmpty");
    }


    public function getEnumAsSqlString()
    {
        if ($this->equals(OrmComparatorEnum::GreaterThen())) {
            return self::GreaterThen;
        }

        if ($this->equals(OrmComparatorEnum::GreaterThenEquals())) {
            return self::GreaterThenEquals;
        }

        if ($this->equals(OrmComparatorEnum::LessThen())) {
            return self::LessThen;
        }

        if ($this->equals(OrmComparatorEnum::LessThenEquals())) {
            return self::LessThenEquals;
        }

        if ($this->equals(OrmComparatorEnum::Equal())) {
            return self::Equal;
        }

        if ($this->equals(OrmComparatorEnum::NotEqual())) {
            return self::NotEqual;
        }

        if ($this->equals(OrmComparatorEnum::Like())) {
            return self::Like;
        }

        if ($this->equals(OrmComparatorEnum::In())) {
            return self::In;
        }

        if ($this->equals(OrmComparatorEnum::NotIn())) {
            return self::NotIn;
        }

        if ($this->equals(OrmComparatorEnum::InOrEmpty())) {
            return self::InOrEmpty;
        }

        if ($this->equals(OrmComparatorEnum::NotInOrEmpty())) {
            return self::NotInOrEmpty;
        }

        throw new class_orm_exception("Unknown sql comparator", Exception::$level_ERROR);
    }
}

