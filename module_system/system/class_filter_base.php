<?php
/*"******************************************************************************************************
*   (c) 2007-2014 by Kajona, www.kajona.de                                                              *
*       Published under the GNU LGPL v2.1, see /system/licence_lgpl.txt                                 *
********************************************************************************************************/

/**
 * Base filter class
 *
 * @package module_system
 * @author stefan.meyer@artemeon.de
 * @author christoph.kappestein@artemeon.de
 */
abstract class class_filter_base
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
     * @return class_orm_objectlist_restriction[]
     */
    public function getOrmRestrictions()
    {
        $arrRestriction = array();

        $objReflection = new class_reflection(get_class($this));
        $arrProperties = $objReflection->getPropertiesWithAnnotation(class_orm_base::STR_ANNOTATION_TABLECOLUMN);
        $arrPropertiesFilterComparator = $objReflection->getPropertiesWithAnnotation(self::STR_ANNOTATION_FILTER_COMPARE_OPERATOR);

        $arrValues = get_object_vars($this);
        foreach($arrProperties as $strAttributeName => $strAttributeValue) {
            if (isset($arrValues[$strAttributeName])) {
                $strTableColumn = $strAttributeValue;
                $strGetter = $objReflection->getGetter($strAttributeName);

                $enumFilterCompareOperator = null;
                if(array_key_exists($strAttributeName, $arrPropertiesFilterComparator)) {
                    $enumFilterCompareOperator = $this->getFilterCompareOperator($arrPropertiesFilterComparator[$strAttributeName]);
                }

                if ($strGetter !== null) {
                    $strValue = $this->$strGetter();
                    if ($strValue !== null && $strValue !== "") {
                        if (is_string($strValue)) {
                            $strCompareOperator = $enumFilterCompareOperator === null ? "LIKE" : $enumFilterCompareOperator->getEnumAsSqlString();
                            if (validateSystemid($strValue)) {
                                $arrRestriction[] = new class_orm_objectlist_restriction("AND " . $strTableColumn . " $strCompareOperator ?", $strValue);
                            } else {
                                $arrRestriction[] = new class_orm_objectlist_restriction("AND " . $strTableColumn . " $strCompareOperator ?", "%" . $strValue . "%");
                            }
                        } elseif (is_int($strValue) || is_float($strValue)) {
                            $strCompareOperator = $enumFilterCompareOperator === null ? "=" : $enumFilterCompareOperator->getEnumAsSqlString();
                            $arrRestriction[] = new class_orm_objectlist_restriction("AND " . $strTableColumn . " $strCompareOperator ?", $strValue);
                        } elseif (is_bool($strValue)) {
                            $strCompareOperator = $enumFilterCompareOperator === null ? "=" : $enumFilterCompareOperator->getEnumAsSqlString();
                            $arrRestriction[] = new class_orm_objectlist_restriction("AND " . $strTableColumn . " $strCompareOperator ?", $strValue ? 1 : 0);
                        } elseif (is_array($strValue)) {
                            $arrRestriction[] = new class_orm_objectlist_in_restriction($strTableColumn, $strValue);
                        } elseif ($strValue instanceof class_date) {
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

                            $arrRestriction[] = new class_orm_objectlist_restriction("AND " . $strTableColumn . " $strCompareOperator ?", $strValue->getLongTimestamp());
                        }
                    }
                }
            }
        }

        return $arrRestriction;
    }

    /**
     * Adds all ORM restrictions to the given $objORM
     *
     * @param class_orm_objectlist $objORM
     */
    public function addWhereRestrictions(class_orm_objectlist $objORM)
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
     * @return class_orm_comparator_enum
     */
    private function getFilterCompareOperator($strFilterCompareType) {

        switch($strFilterCompareType) {
            case self::STR_COMPAREOPERATOR_EQ:
                return class_orm_comparator_enum::Equal();
            case self::STR_COMPAREOPERATOR_GT:
                return class_orm_comparator_enum::GreaterThen();
            case self::STR_COMPAREOPERATOR_LT:
                return class_orm_comparator_enum::LessThen();
            case self::STR_COMPAREOPERATOR_GE:
                return class_orm_comparator_enum::GreaterThenEquals();
            case self::STR_COMPAREOPERATOR_LE:
                return class_orm_comparator_enum::LessThenEquals();
            case self::STR_COMPAREOPERATOR_NE:
                return class_orm_comparator_enum::NotEqual();
            case self::STR_COMPAREOPERATOR_LIKE:
                return class_orm_comparator_enum::Like();
            default:
                return null;
        }
    }
}
