<?php
/*"******************************************************************************************************
*   (c) 2007-2014 by Kajona, www.kajona.de                                                              *
*       Published under the GNU LGPL v2.1, see /system/licence_lgpl.txt                                 *
********************************************************************************************************/

namespace Kajona\System\System;

use DateTime;
use Kajona\System\Admin\AdminFormgeneratorFilter;
use ReflectionClass;


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
    const STR_COMPAREOPERATOR_IN_OR_EMPTY = "IN_OR_EMPTY";
    const STR_COMPAREOPERATOR_NOTIN_OR_EMPTY = "NOTIN_OR_EMPTY";

    /**
     * bit to indicate if a redirect should be executed
     * Value is set to true if a filter is being submitted (filter or reset)
     *
     * @var bool
     */
    private $bitFilterUpdated = false;

    /**
     * @var null
     */
    private $strFilterId = null;

    /**
     * Returns the ID of the filter.
     * This ID is also being used to store the filter in the session. Please make sure to use a unique ID.
     * By Default the class name (in lower case) is being returned
     *
     * @return string
     */
    final public function getFilterId()
    {
        if($this->strFilterId === null) {
            $objClass = new \ReflectionClass(get_called_class());
            $this->strFilterId = StringUtil::toLowerCase($objClass->getShortName());
        }

        return $this->strFilterId;
    }

    /**
     * Returns the module name.
     * The module name is being retrieved via the class annotation @ module
     *
     * @param $strKey
     *
     * @return mixed
     */
    public function getArrModule($strKey = "") {
        $objReflection = new Reflection($this);
        $arrAnnotationValues = $objReflection->getAnnotationValuesFromClass(AbstractController::STR_MODULE_ANNOTATION);
        if (count($arrAnnotationValues) > 0) {
            return trim($arrAnnotationValues[0]);
        }

        throw new Exception("Missing ".AbstractController::STR_MODULE_ANNOTATION." annotation for class ".__CLASS__);
    }


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
        $strFilterId = $objFilter->getFilterId();

        /*
         * Check if filter form was submitted.
         * If not try to get filter from session
         */
        if(Carrier::getInstance()->getParam($objFilter->getFullParamName(AdminFormgeneratorFilter::STR_FORM_PARAM_FILTER)) != "") {
            $objFilter->setBitFilterUpdated(true);

            /*
             * In case filter was reset reset, remove from session
             */
            if(Carrier::getInstance()->getParam(AdminFormgeneratorFilter::STR_FORM_PARAM_RESET) != "") {
                Session::getInstance()->sessionUnset($strFilterId);
            }
            else {
                //If no reset was triggered -> Update filter with params which have been set
                $objFilter->updateFilterPropertiesFromParams();
            }
        }
        else {
            /*
             * Get objFilter from Session and create a clone of the filter
             * (reason to return a clone: changes in the object are not reflected to the session object )
             */
            if(Session::getInstance()->sessionIsset($strFilterId)) {
                $objFilter = Session::getInstance()->getSession($strFilterId);
                $objFilter = clone $objFilter;
            }
        }

        /*
         * Write filter to session
         */
        $objFilter->writeFilterToSession();

        return $objFilter;
    }


    /**
     * Updates the filter object with the values of the passed parameters
     *
     */
    public function updateFilterPropertiesFromParams()
    {
        //get properties
        $objReflection = new Reflection($this);
        $arrProperties = $objReflection->getPropertiesWithAnnotation(OrmBase::STR_ANNOTATION_TABLECOLUMN);

        //get params
        $arrParams = Carrier::getAllParams();

        //set param vlaues to filter object
        foreach($arrProperties as $strPropertyName => $strColumnName) {
            $strSetter = $objReflection->getSetter($strPropertyName);
            if($strSetter === null) {
                throw new Exception("unable to find setter for property ".$strPropertyName."@".get_class($this), Exception::$level_ERROR);
            }

            //create param key string
            $strPropertyWithoutPrefix = Lang::getInstance()->propertyWithoutPrefix($strPropertyName);
            $strPropertyWithoutPrefix = $this->getFullParamName($strPropertyWithoutPrefix);

            //set values to filter object
            if(array_key_exists($strPropertyWithoutPrefix, $arrParams)) {
                $strValueToSet = $this->convertParamValue($strPropertyWithoutPrefix, $arrParams);
                $this->$strSetter($strValueToSet);
            }
        }
    }

    /**
     * Converts the param value to the expected date
     *
     * if $strParamName contains the word "date", then $arrParams[$strParamName] will be converted to Date
     * if $strParamName"_id" exists, take this as the value
     *
     *
     * @param $strParamName
     * @param $arrParams
     *
     * @return Date|null
     */
    protected function convertParamValue($strParamName, $arrParams)
    {
        $strValue = $arrParams[$strParamName] == "" ? null : $arrParams[$strParamName];

        //check if _id param exists, if yes take that one
        if(array_key_exists($strParamName."_id", $arrParams)) {
            $strValue = $arrParams[$strParamName."_id"] == "" ? null : $arrParams[$strParamName."_id"];
        }

        //if no value is set, return null
        if($strValue === null) {
            return $strValue;
        }

        //if paramname contains the word "date" -> convert to date
        if(StringUtil::indexOf($strParamName, "date") !== false) {
            $objDate = new Date();
            $objDate->generateDateFromParams($strParamName, $arrParams);
            return $objDate;
        }

        return $strValue;
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
        return OrmObjectlistRestriction::getORMRestrictionForValue($strValue, $strTableColumn, $enumFilterCompareOperator, $strCondition);
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
            case self::STR_COMPAREOPERATOR_IN_OR_EMPTY:
                return OrmComparatorEnum::InOrEmpty();
            case self::STR_COMPAREOPERATOR_NOTIN_OR_EMPTY:
                return OrmComparatorEnum::NotInOrEmpty();
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
     * Write the filter to the session.
     * A clone of the filter is being written to the session.
     *
     * @throws Exception
     */
    public function writeFilterToSession()
    {
        $objFilter = clone $this;
        $objFilter->setBitFilterUpdated(false);

        $strSessionId = $objFilter->getFilterId();
        Session::getInstance()->setSession($strSessionId, $objFilter);
    }

    /**
     * Method to get the full param name (inlcuding filter id)
     *
     * @param $strParam
     *
     * @return string
     */
    public function getFullParamName($strParam)
    {
        return $this->getFilterId()."_".$strParam;
    }

    /**
     * @return boolean
     */
    public function getBitFilterUpdated()
    {
        return $this->bitFilterUpdated;
    }

    /**
     * @param boolean $bitFilterUpdated
     */
    public function setBitFilterUpdated($bitFilterUpdated)
    {
        $this->bitFilterUpdated = $bitFilterUpdated;
    }
}
