<?php
/*"******************************************************************************************************
*   (c) 2007-2016 by Kajona, www.kajona.de                                                              *
*       Published under the GNU LGPL v2.1, see /system/licence_lgpl.txt                                 *
*-------------------------------------------------------------------------------------------------------*
*   $Id$                                        *
********************************************************************************************************/

namespace Kajona\System\System;


/**
 * A OrmPropertyCondition may be used to create where condition for the objectList and objectCount queries.
 * Therefore you may pass the name of a property, the comparator and, finally, the expecteed value of the property.
 * Example:
 *  $objQuery->addWhereRestriction(new OrmPropertyCondition("strTitle", OrmComparatorEnum::Equal(), "abc"));
 *
 * @package Kajona\System\System
 * @author stefan.meyer1@yahoo.de
 * @since 5.0
 */
class OrmPropertyCondition extends OrmCondition
{
    private $strProperty = "";

    /**
     * @var OrmComparatorEnum
     */
    private $objComparator = "";

    /**
     * @param string $strProperty
     * @param OrmComparatorEnum $objComparator
     * @param $strValue
     */
    public function __construct($strProperty, OrmComparatorEnum $objComparator, $strValue)
    {
        $this->arrParams = array($strValue);
        $this->objComparator = $objComparator;
        $this->strProperty = $strProperty;
    }

    /**
     * @param array $arrParams
     *
     * @throws OrmException
     */
    public function setArrParams($arrParams)
    {
        throw new OrmException("Setting params for property restrictions is not supported", OrmException::$level_ERROR);
    }

    /**
     * @param string $strWhere
     *
     * @throws OrmException
     */
    public function setStrWhere($strWhere)
    {
        throw new OrmException("Setting a where restriction for property restrictions is not supported", OrmException::$level_ERROR);
    }

    /**
     * Here comes the magic, generation a where restriction out of the passed property name and the comparator
     *
     * @return string
     * @throws OrmException
     */
    public function getStrWhere()
    {
        $objReflection = new Reflection($this->getStrTargetClass());

        $strPropertyValue = $objReflection->getAnnotationValueForProperty($this->strProperty, OrmBase::STR_ANNOTATION_TABLECOLUMN);

        if ($strPropertyValue == null) {
            throw new OrmException("Failed to load annotation ".OrmBase::STR_ANNOTATION_TABLECOLUMN." for property ".$this->strProperty."@".$this->getStrTargetClass(), OrmException::$level_ERROR);
        }

        return $strPropertyValue." ".$this->objComparator->getEnumAsSqlString()." ?";
    }
}
