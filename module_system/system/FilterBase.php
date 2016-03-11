<?php
/*"******************************************************************************************************
*   (c) 2007-2014 by Kajona, www.kajona.de                                                              *
*       Published under the GNU LGPL v2.1, see /system/licence_lgpl.txt                                 *
********************************************************************************************************/

namespace Kajona\System\System;

use Kajona\Jsonapi\System\ObjectSerializer;
use Kajona\System\Admin\AdminFormgeneratorFilter;
use Serializable;


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
    const STR_COMPAREOPERATOR_IN = "IN";
    const STR_COMPAREOPERATOR_NOTIN = "NOTIN";

    /**
     * Returns the ID of the filter.
     * This ID is also being used to store the filter in the session. Please make sure to use a unique ID.
     *
     * @return string
     */
    final public static function getFilterId() {
        return get_called_class();
    }

    /**
     * Returns the module name.
     *
     * @param $strKey
     *
     * @return mixed
     */
    abstract public function getArrModule($strKey = "");


    /**
     * Creates a new filter object or retrieves a filter object from the session.
     * If retrieved from session a clone is being returned.
     *
     * @return self
     */
    public static function getOrCreateFromSession()
    {
        /** @var FilterBase $objFilter */
        $strCalledClass = get_called_class();
        $objFilter = new $strCalledClass();
        $strSessionId = $objFilter::getFilterId();

        if(Carrier::getInstance()->getParam("reset") != "") {
            Session::getInstance()->sessionUnset($strSessionId);
        }

        if(Session::getInstance()->sessionIsset($strSessionId)) {
            $objFilter = Session::getInstance()->getSession($strSessionId);
            $objFilter = clone $objFilter;
        }

        return $objFilter;
    }



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

        foreach($arrProperties as $strAttributeName => $strTableColumn) {
            $strGetter = $objReflection->getGetter($strAttributeName);

            $enumFilterCompareOperator = null;
            if(array_key_exists($strAttributeName, $arrPropertiesFilterComparator)) {
                $enumFilterCompareOperator = $this->getFilterCompareOperator($arrPropertiesFilterComparator[$strAttributeName]);
            }

            if($strGetter !== null) {
                $strValue = $this->$strGetter();
                if($strValue !== null && $strValue !== "") {
                    $objRestriction = $this->getSingleOrmRestriction($strAttributeName, $strValue, $strTableColumn, $enumFilterCompareOperator);
                    if($objRestriction !== null) {
                        $arrRestriction[] = $objRestriction;
                    }
                }
            }
        }

        return $arrRestriction;
    }

    /**
     * Override this method to add specific logic for certain filter attributes
     *
     * @param $strAttributeName
     * @param $strValue
     * @param $strTableColumn
     * @param OrmComparatorEnum|null $enumFilterCompareOperator
     * @param string $strCondition
     *
     * @return OrmObjectlistInRestriction|OrmObjectlistRestriction|null
     */
    protected function getSingleOrmRestriction($strAttributeName, $strValue, $strTableColumn, OrmComparatorEnum $enumFilterCompareOperator = null, $strCondition = "AND")
    {
        return self::getORMRestriction($strValue, $strTableColumn, $enumFilterCompareOperator, $strCondition);
    }

    /**
     * @param $strValue
     * @param $strTableColumn
     * @param OrmComparatorEnum|null $enumFilterCompareOperator
     * @param string $strCondition
     *
     * @return OrmObjectlistInRestriction|OrmObjectlistRestriction|null
     * @throws class_orm_exception
     */
    public final static function getORMRestriction($strValue, $strTableColumn, OrmComparatorEnum $enumFilterCompareOperator = null, $strCondition = "AND")
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
     * Adds all ORM restrictions to the given $objORM
     *
     * @param OrmObjectlist $objORM
     */
    public function addWhereRestrictions(OrmObjectlist $objORM)
    {
        $objRestrictions = $this->getOrmRestrictions();
        foreach($objRestrictions as $objRestriction) {
            $objORM->addWhereRestriction($objRestriction);
        }
    }

    /**
     * Gets OrmComparatorEnum by the given $strFilterCompareType
     *
     * @param string $strFilterCompareType
     *
     * @return OrmComparatorEnum
     */
    private function getFilterCompareOperator($strFilterCompareType)
    {

        switch($strFilterCompareType) {
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
            case self::STR_COMPAREOPERATOR_IN:
                return OrmComparatorEnum::In();
            case self::STR_COMPAREOPERATOR_NOTIN:
                return OrmComparatorEnum::NotIn();
            default:
                return null;
        }
    }

    /**
     * Overwrite method if specific form handling is required.
     * Method is being called when the form for the filter is being generated.
     *
     * @param AdminFormgeneratorFilter $objFilterForm
     */
    public function updateFilterForm(AdminFormgeneratorFilter $objFilterForm)
    {

    }

    /**
     * Generates a filter form based on the filter object.
     *
     * @param string $strAction
     * @param null $strModule
     *
     * @return string
     */
    public function renderForm($strAction = "list", $strModule= null, $strAdditionalParams = "", $bitEncodedAmpersand = true)
    {
        if($strModule === null) {
            $strModule = $this->getArrModule();
        }

        //1. Create filter form
        $objFilterForm = new AdminFormgeneratorFilter("filter", $this);//do not change form name because of property generation
        $strTargetURI = Link::getLinkAdminHref($strModule, $strAction, $strAdditionalParams, $bitEncodedAmpersand);
        $strFilter = $objFilterForm->renderForm($strTargetURI);

        //2. Update session
        $this->writeFilterToSession();

        return $strFilter;
    }

    /**
     * Write the filter to the session.
     * A clone of the filter is being written to the session.
     *
     * @throws Exception
     */
    public function writeFilterToSession() {
        $objFilter = clone $this;
        $strSessionId = $objFilter::getFilterId();
        Session::getInstance()->setSession($strSessionId, $objFilter);
    }
}
