<?php
/*"******************************************************************************************************
*   (c) 2004-2006 by MulchProductions, www.mulchprod.de                                                 *
*   (c) 2007-2015 by Kajona, www.kajona.de                                                              *
*       Published under the GNU LGPL v2.1, see /system/licence_lgpl.txt                                 *
********************************************************************************************************/

/**
 * A enum indicating the entry-point for the current request, e.g. INDEX or XML
 *
 * @method static class_orm_comparator_enum GreaterThen()
 * @method static class_orm_comparator_enum GreaterThenEquals()
 * @method static class_orm_comparator_enum LessThen()
 * @method static class_orm_comparator_enum LessThenEquals()
 * @method static class_orm_comparator_enum Equal()
 * @method static class_orm_comparator_enum NotEqual()
 * @method static class_orm_comparator_enum Like()
 *
 *
 * @package module_system
 * @author sidler@mulchprod.de
 * @since 4.7
 */
class class_orm_comparator_enum extends class_enum {
    /**
     * Return the array of possible, so allowed values for the current enum
     *
     * @return string[]
     */
    protected function getArrValues() {
        return array("GreaterThen", "GreaterThenEquals", "LessThen", "LessThenEquals", "Equal", "NotEqual", "Like");
    }


    public function getEnumAsSqlString() {
        if($this->equals(class_orm_comparator_enum::GreaterThen())) {
            return ">";
        }

        if($this->equals(class_orm_comparator_enum::GreaterThenEquals())) {
            return ">=";
        }

        if($this->equals(class_orm_comparator_enum::LessThen())) {
            return "<";
        }

        if($this->equals(class_orm_comparator_enum::LessThenEquals())) {
            return "<=";
        }

        if($this->equals(class_orm_comparator_enum::Equal())) {
            return "=";
        }

        if($this->equals(class_orm_comparator_enum::NotEqual())) {
            return "!=";
        }

        if($this->equals(class_orm_comparator_enum::Like())) {
            return "LIKE";
        }

        throw new class_orm_exception("Unknown sql comparator", class_exception::$level_ERROR);
    }
}

