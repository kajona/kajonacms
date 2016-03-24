<?php
/*"******************************************************************************************************
*   (c) 2007-2016 by Kajona, www.kajona.de                                                              *
*       Published under the GNU LGPL v2.1, see /system/licence_lgpl.txt                                 *
*-------------------------------------------------------------------------------------------------------*
*   $Id$                                        *
********************************************************************************************************/

namespace Kajona\System\System;


/**
 * A objectlist restriction may be used to create where restrictions for the objectList and objectCount queries.
 * Pass them using a syntax like "AND x = ?", don't add "WHERE", this is done by the mapper.
 *
 * @package module_system
 * @author sidler@mulchprod.de
 * @since 4.6
 */
class OrmObjectlistRestriction
{

    private $strWhere = "";
    protected $arrParams = array();

    private $strTargetClass = "";

    /**
     * @param string $strWhere
     * @param string|string[] $arrParams either a single value or an array of params
     */
    function __construct($strWhere, $arrParams = array())
    {

        if (!is_array($arrParams)) {
            $arrParams = array($arrParams);
        }

        $this->arrParams = $arrParams;
        $this->strWhere = " ".$strWhere." ";
    }

    /**
     * Generic method to create an ORM restriction.
     *
     * Depending on the type of the value ($strValue) a specific ORM-Restriction will be generated.
     *  e.g. if $strValue is an array, then a OrmObjectlistInRestriction will be generated
     *
     *
     * @param $strValue
     * @param $strTableColumn
     * @param OrmComparatorEnum|null $enumFilterCompareOperator
     * @param string $strCondition
     *
     * @return OrmObjectlistInRestriction|OrmObjectlistRestriction|null
     * @throws OrmException
     */
    public final static function getORMRestrictionForValue($strValue, $strTableColumn, OrmComparatorEnum $enumFilterCompareOperator = null, $strCondition = "AND")
    {

        if(is_string($strValue)) {
            if(validateSystemid($strValue)) {
                $strCompareOperator = $enumFilterCompareOperator === null ? "=" : $enumFilterCompareOperator->getEnumAsSqlString();
                return new OrmObjectlistRestriction("$strCondition $strTableColumn $strCompareOperator ?", array($strValue));
            }
            else {
                $strCompareOperator = $enumFilterCompareOperator === null ? "LIKE" : $enumFilterCompareOperator->getEnumAsSqlString();
                return new OrmObjectlistRestriction("$strCondition $strTableColumn $strCompareOperator ?", array("%".$strValue."%"));
            }
        }
        elseif(is_int($strValue) || is_float($strValue)) {
            $strCompareOperator = $enumFilterCompareOperator === null ? "=" : $enumFilterCompareOperator->getEnumAsSqlString();
            return new OrmObjectlistRestriction("$strCondition $strTableColumn $strCompareOperator ?", array($strValue));
        }
        elseif(is_bool($strValue)) {
            $strCompareOperator = $enumFilterCompareOperator === null ? "=" : $enumFilterCompareOperator->getEnumAsSqlString();
            return new OrmObjectlistRestriction("$strCondition $strTableColumn $strCompareOperator ?", $strValue ? array(1) : array(0));
        }
        elseif(is_array($strValue)) {
            $strCompareOperator = $enumFilterCompareOperator === null ? OrmObjectlistInRestriction::STR_CONDITION_IN : $enumFilterCompareOperator->getEnumAsSqlString();

            if($enumFilterCompareOperator !== null) {
                if($enumFilterCompareOperator->equals(OrmComparatorEnum::InOrEmpty())) {
                    return new OrmObjectlistInOrEmptyRestriction($strTableColumn, $strValue, $strCondition, OrmObjectlistInRestriction::STR_CONDITION_IN);
                }
                if($enumFilterCompareOperator->equals(OrmComparatorEnum::NotInOrEmpty())) {
                    return new OrmObjectlistInOrEmptyRestriction($strTableColumn, $strValue, $strCondition, OrmObjectlistInRestriction::STR_CONDITION_NOTIN);
                }
            }

            return new OrmObjectlistInRestriction($strTableColumn, $strValue, $strCondition, $strCompareOperator);
        }
        elseif($strValue instanceof Date) {
            $strValue = clone $strValue;
            $strCompareOperator = $enumFilterCompareOperator === null ? "=" : $enumFilterCompareOperator->getEnumAsSqlString();

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

            return new OrmObjectlistRestriction("$strCondition $strTableColumn $strCompareOperator ?", array($strValue->getLongTimestamp()));
        }

        return null;
    }

    /**
     * @param array $arrParams
     *
     * @return void
     */
    public function setArrParams($arrParams)
    {
        $this->arrParams = $arrParams;
    }

    /**
     * @return array
     */
    public function getArrParams()
    {
        return $this->arrParams;
    }

    /**
     * @param string $strWhere
     *
     * @return void
     */
    public function setStrWhere($strWhere)
    {
        $this->strWhere = $strWhere;
    }

    /**
     * @return string
     */
    public function getStrWhere()
    {
        return $this->strWhere;
    }

    /**
     * @return string
     */
    public function getStrTargetClass()
    {
        return $this->strTargetClass;
    }

    /**
     * @param string $strTargetClass
     */
    public function setStrTargetClass($strTargetClass)
    {
        $this->strTargetClass = $strTargetClass;
    }




}
