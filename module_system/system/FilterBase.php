<?php
/*"******************************************************************************************************
*   (c) 2007-2014 by Kajona, www.kajona.de                                                              *
*       Published under the GNU LGPL v2.1, see /system/licence_lgpl.txt                                 *
********************************************************************************************************/

namespace Kajona\System\System;


/**
 * Base filter class
 *
 * @package module_system
 * @author stefan.meyer@artemeon.de
 * @author christoph.kappestein@artemeon.de
 */
abstract class FilterBase
{
    const STR_ANNOTATION_FILTER_COMPARE_OPERATOR = "@filterCompareOperator";

    const STR_COMPAREOPERATOR_EQ = "EQ";
    const STR_COMPAREOPERATOR_GT = "GT";
    const STR_COMPAREOPERATOR_LT = "LT";
    const STR_COMPAREOPERATOR_GE = "GE";
    const STR_COMPAREOPERATOR_LE = "LE";
    const STR_COMPAREOPERATOR_NE = "NE";
    const STR_COMPAREOPERATOR_LIKE = "LIKE";

    /**
     * Returns the ID of the filter.
     * This ID is also being used to store the filter in the session. Please make sure to use a unique ID.
     *
     * @return string
     */
    abstract public function getFilterId();

    /**
     * Returns the module name.
     *
     * @return string
     */
    abstract public function getArrModule();

    /**
     * Generates ORM restrictions based on the properties of the filter.
     *
     * @return OrmObjectlistRestriction[]
     */
    public function getOrmRestrictions()
    {
        $arrRestriction = array();

        $objReflection = new Reflection(get_class($this));
        $arrProperties = $objReflection->getPropertiesWithAnnotation(OrmBase::STR_ANNOTATION_TABLECOLUMN);
        $arrPropertiesFilterComparator = $objReflection->getPropertiesWithAnnotation(self::STR_ANNOTATION_FILTER_COMPARE_OPERATOR);

        $arrValues = get_object_vars($this);
        foreach ($arrProperties as $strAttributeName => $strAttributeValue) {
            if (isset($arrValues[$strAttributeName])) {
                $strTableColumn = $strAttributeValue;
                $strGetter = $objReflection->getGetter($strAttributeName);

                $enumFilterCompareOperator = null;
                if (array_key_exists($strAttributeName, $arrPropertiesFilterComparator)) {
                    $enumFilterCompareOperator = $this->getFilterCompareOperator($arrPropertiesFilterComparator[$strAttributeName]);
                }

                if ($strGetter !== null) {
                    $strValue = $this->$strGetter();
                    if ($strValue !== null && $strValue !== "") {
                        $objRestriction = self::getORMRestriction($strValue, $strTableColumn, $enumFilterCompareOperator);
                        if($objRestriction !== null) {
                            $arrRestriction[] = $objRestriction;
                        }
                    }
                }
            }
        }

        return $arrRestriction;
    }

    /**
     * @param $strValue
     * @param $strTableColumn
     * @param class_orm_comparator_enum|null $enumFilterCompareOperator
     * @param string $strCondition
     * @return class_orm_objectlist_in_restriction|class_orm_objectlist_restriction|null
     * @throws class_orm_exception
     */
    public static function getORMRestriction($strValue, $strTableColumn, class_orm_comparator_enum $enumFilterCompareOperator = null, $strCondition = "AND") {

        if (is_string($strValue)) {
            if (validateSystemid($strValue)) {
                $strCompareOperator = $enumFilterCompareOperator === null ? "=" : $enumFilterCompareOperator->getEnumAsSqlString();
               return new class_orm_objectlist_restriction("$strCondition $strTableColumn $strCompareOperator ?", $strValue);
            } else {
                $strCompareOperator = $enumFilterCompareOperator === null ? "LIKE" : $enumFilterCompareOperator->getEnumAsSqlString();
               return new class_orm_objectlist_restriction("$strCondition $strTableColumn $strCompareOperator ?", "%" . $strValue . "%");
            }
        }
        elseif (is_int($strValue) || is_float($strValue)) {
            $strCompareOperator = $enumFilterCompareOperator === null ? "=" : $enumFilterCompareOperator->getEnumAsSqlString();
           return new class_orm_objectlist_restriction("$strCondition $strTableColumn $strCompareOperator ?", $strValue);
        }
        elseif (is_bool($strValue)) {
            $strCompareOperator = $enumFilterCompareOperator === null ? "=" : $enumFilterCompareOperator->getEnumAsSqlString();
           return new class_orm_objectlist_restriction("$strCondition $strTableColumn $strCompareOperator ?", $strValue ? 1 : 0);
        }
        elseif (is_array($strValue)) {
           return new class_orm_objectlist_in_restriction($strTableColumn, $strValue, $strCondition);
        }
        elseif ($strValue instanceof class_date) {
            $strValue = clone $strValue;
            $strCompareOperator = $enumFilterCompareOperator === null ? "=" : $enumFilterCompareOperator->getEnumAsSqlString();

            if($enumFilterCompareOperator !== null) {
                if ($enumFilterCompareOperator->equals(class_orm_comparator_enum::GreaterThen())
                    || $enumFilterCompareOperator->equals(class_orm_comparator_enum::GreaterThenEquals())
                ) {
                    $strValue->setBeginningOfDay();
                }
                if ($enumFilterCompareOperator->equals(class_orm_comparator_enum::LessThen())
                    || $enumFilterCompareOperator->equals(class_orm_comparator_enum::LessThenEquals())
                ) {
                    $strValue->setEndOfDay();
                }
            }

           return new class_orm_objectlist_restriction("$strCondition $strTableColumn $strCompareOperator ?", $strValue->getLongTimestamp());
        }

        return null;
    }

    /**
     * Adds all ORM restrictions to the given $objORM
     *
     * @param OrmObjectlist $objORM
     */
    public function addWhereRestrictions(OrmObjectlist $objORM)
    {
        $objRestrictions = $this->getOrmRestrictions();
        foreach ($objRestrictions as $objRestriction) {
            $objORM->addWhereRestriction($objRestriction);
        }
    }

    /**
     * Gets class_orm_comparator_enum by the given $strFilterCompareType
     *
     * @param string $strFilterCompareType
     *
     * @return OrmComparatorEnum
     */
    private function getFilterCompareOperator($strFilterCompareType)
    {

        switch ($strFilterCompareType) {
            case self::STR_COMPAREOPERATOR_EQ:
                return OrmComparatorEnum::Equal();
            case self::STR_COMPAREOPERATOR_GT:
                return OrmComparatorEnum::GreaterThen();
            case self::STR_COMPAREOPERATOR_LT:
                return OrmComparatorEnum::LessThen();
            case self::STR_COMPAREOPERATOR_GE:
                return OrmComparatorEnum::GreaterThenEquals();
            case self::STR_COMPAREOPERATOR_LE:
                return OrmComparatorEnum::LessThenEquals();
            case self::STR_COMPAREOPERATOR_NE:
                return OrmComparatorEnum::NotEqual();
            case self::STR_COMPAREOPERATOR_LIKE:
                return OrmComparatorEnum::Like();
            default:
                return null;
        }
    }

    /**
     * Overwrite method if specific form handling is required.
     * Method is being called when the form for the filter is being generated.
     *
     * @param class_admin_formgenerator_filter $objFilterForm
     */
    public function updateFilterForm(class_admin_formgenerator_filter $objFilterForm) {

    }
}
