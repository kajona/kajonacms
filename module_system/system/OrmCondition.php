<?php
/*"******************************************************************************************************
*   (c) 2007-2016 by Kajona, www.kajona.de                                                              *
*       Published under the GNU LGPL v2.1, see /system/licence_lgpl.txt                                 *
*-------------------------------------------------------------------------------------------------------*
*   $Id$                                        *
********************************************************************************************************/

namespace Kajona\System\System;


/**
 * A orm condition may be used to create where restrictions for the objectList and objectCount queries.
 * Pass them using a syntax like "x = ?", don't add "WHERE", "AND", "OR" at the beginning, this is done by the mapper.
 *
 * @package Kajona\System\System
 * @author stefan.meyer1@yahoo.de
 * @since 5.0
 */
class OrmCondition extends OrmObjectlistRestriction
{
    const STR_CONDITION_AND = "AND";
    const STR_CONDITION_OR = "OR";

    /**
     * OrmCondition constructor.
     */
    function __construct($strWhere, $arrParams = array())
    {
        parent::__construct($strWhere, $arrParams);
    }


    /**
     * Generic method to create an ORM restriction.
     *
     * Depending on the type of the value ($strValue) a specific ORM-Condition will be generated.
     *  e.g. if $strValue is an array, then a OrmInCondition will be generated
     *
     *
     * @param $strValue
     * @param $strTableColumn
     * @param OrmComparatorEnum|null $enumFilterCompareOperator
     * @param string $strCondition
     *
     * @return OrmCondition|null
     * @throws OrmException
     *
     */
    public final static function getORMConditionForValue($strValue, $strTableColumn, OrmComparatorEnum $enumFilterCompareOperator = null)
    {

        if(is_string($strValue)) {
            if(validateSystemid($strValue)) {
                $strCompareOperator = $enumFilterCompareOperator === null ? OrmComparatorEnum::Equal : $enumFilterCompareOperator->getEnumAsSqlString();
                return new OrmCondition("$strTableColumn $strCompareOperator ?", array($strValue));
            }
            else {
                $strCompareOperator = $enumFilterCompareOperator === null ? OrmComparatorEnum::Like : $enumFilterCompareOperator->getEnumAsSqlString();
                return new OrmCondition("$strTableColumn $strCompareOperator ?", array("%".$strValue."%"));
            }
        }
        elseif(is_int($strValue) || is_float($strValue)) {
            $strCompareOperator = $enumFilterCompareOperator === null ? OrmComparatorEnum::Equal: $enumFilterCompareOperator->getEnumAsSqlString();
            return new OrmCondition("$strTableColumn $strCompareOperator ?", array($strValue));
        }
        elseif(is_bool($strValue)) {
            $strCompareOperator = $enumFilterCompareOperator === null ? OrmComparatorEnum::Equal : $enumFilterCompareOperator->getEnumAsSqlString();
            return new OrmCondition("$strTableColumn $strCompareOperator ?", $strValue ? array(1) : array(0));
        }
        elseif(is_array($strValue)) {
            $strCompareOperator = $enumFilterCompareOperator === null ? OrmInCondition::STR_CONDITION_IN : $enumFilterCompareOperator->getEnumAsSqlString();

            if($enumFilterCompareOperator !== null) {
                if($enumFilterCompareOperator->equals(OrmComparatorEnum::InOrEmpty())) {
                    return new OrmInOrEmptyCondition($strTableColumn, $strValue, OrmInCondition::STR_CONDITION_IN);
                }
                if($enumFilterCompareOperator->equals(OrmComparatorEnum::NotInOrEmpty())) {
                    return new OrmInOrEmptyCondition($strTableColumn, $strValue, OrmInCondition::STR_CONDITION_NOTIN);
                }
            }

            return new OrmInCondition($strTableColumn, $strValue, $strCompareOperator);
        }
        elseif($strValue instanceof Date) {
            $strValue = clone $strValue;
            $strCompareOperator = $enumFilterCompareOperator === null ? OrmComparatorEnum::Equal : $enumFilterCompareOperator->getEnumAsSqlString();

            if($enumFilterCompareOperator !== null) {
                if($enumFilterCompareOperator->equals(OrmComparatorEnum::GreaterThen())
                    || $enumFilterCompareOperator->equals(OrmComparatorEnum::GreaterThenEquals())
                ) {
                    $strValue->setBeginningOfDay();
                }
                if($enumFilterCompareOperator->equals(OrmComparatorEnum::LessThen())
                    || $enumFilterCompareOperator->equals(OrmComparatorEnum::LessThenEquals())
                ) {
                    $strValue->setEndOfDay();
                }
            }

            return new OrmCondition("$strTableColumn $strCompareOperator ?", array($strValue->getLongTimestamp()));
        }

        return null;
    }
}
