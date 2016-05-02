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
class OrmCondition extends OrmObjectlistRestriction implements OrmConditionInterface
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
     * @param string $strWhere
     *
     * @return void
     */
    public function setStrWhere($strWhere)
    {
        $strWhere = StringUtil::trim($strWhere);
        $this->strWhere = $strWhere;
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
     * @param OrmComparatorEnum|null $objFilterCompareOperator
     * @param string $strCondition
     *
     * @return OrmCondition|null
     * @throws OrmException
     *
     */
    public final static function getORMConditionForValue($strValue, $strTableColumn, OrmComparatorEnum $objFilterCompareOperator = null)
    {

        if(is_string($strValue)) {
            if(validateSystemid($strValue)) {
                $strCompareOperator = $objFilterCompareOperator === null ? OrmComparatorEnum::Equal : $objFilterCompareOperator->getEnumAsSqlString();
                return new OrmCondition("$strTableColumn $strCompareOperator ?", array($strValue));
            }
            else {
                $strCompareOperator = $objFilterCompareOperator === null ? OrmComparatorEnum::Like : $objFilterCompareOperator->getEnumAsSqlString();
                return new OrmCondition("$strTableColumn $strCompareOperator ?", array("%".$strValue."%"));
            }
        }
        elseif(is_int($strValue) || is_float($strValue)) {
            $strCompareOperator = $objFilterCompareOperator === null ? OrmComparatorEnum::Equal: $objFilterCompareOperator->getEnumAsSqlString();
            return new OrmCondition("$strTableColumn $strCompareOperator ?", array($strValue));
        }
        elseif(is_bool($strValue)) {
            $strCompareOperator = $objFilterCompareOperator === null ? OrmComparatorEnum::Equal : $objFilterCompareOperator->getEnumAsSqlString();
            return new OrmCondition("$strTableColumn $strCompareOperator ?", $strValue ? array(1) : array(0));
        }
        elseif(is_array($strValue)) {
            $strCompareOperator = $objFilterCompareOperator === null ? OrmInCondition::STR_CONDITION_IN : $objFilterCompareOperator->getEnumAsSqlString();

            if($objFilterCompareOperator !== null) {
                if($objFilterCompareOperator->equals(OrmComparatorEnum::InOrEmpty())) {
                    return new OrmInOrEmptyCondition($strTableColumn, $strValue, OrmInCondition::STR_CONDITION_IN);
                }
                if($objFilterCompareOperator->equals(OrmComparatorEnum::NotInOrEmpty())) {
                    return new OrmInOrEmptyCondition($strTableColumn, $strValue, OrmInCondition::STR_CONDITION_NOTIN);
                }
            }

            return new OrmInCondition($strTableColumn, $strValue, $strCompareOperator);
        }
        elseif($strValue instanceof Date) {
            $strValue = clone $strValue;
            $strCompareOperator = $objFilterCompareOperator === null ? OrmComparatorEnum::Equal : $objFilterCompareOperator->getEnumAsSqlString();

            if($objFilterCompareOperator !== null) {
                if($objFilterCompareOperator->equals(OrmComparatorEnum::GreaterThen())
                    || $objFilterCompareOperator->equals(OrmComparatorEnum::GreaterThenEquals())
                ) {
                    $strValue->setBeginningOfDay();
                }
                if($objFilterCompareOperator->equals(OrmComparatorEnum::LessThen())
                    || $objFilterCompareOperator->equals(OrmComparatorEnum::LessThenEquals())
                ) {
                    $strValue->setEndOfDay();
                }
            }

            return new OrmCondition("$strTableColumn $strCompareOperator ?", array($strValue->getLongTimestamp()));
        }

        return null;
    }
}
