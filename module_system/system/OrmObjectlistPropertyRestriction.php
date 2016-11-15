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
 * Therefore you may pass the name of a property, the comparator and, finally, the expecteed value of the property.
 * Example:
 *  $objQuery->addWhereRestriction(new OrmObjectlistPropertyRestriction("strTitle", OrmComparatorEnum::Equal(), "abc"));
 *
 * @package module_system
 * @author sidler@mulchprod.de
 * @since 4.7
 *
 * @deprecated
 */
class OrmObjectlistPropertyRestriction extends OrmObjectlistRestriction
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
     *
     * @deprecated
     */
    public function __construct($strProperty, OrmComparatorEnum $objComparator, $strValue)
    {
        parent::__construct("", array($strValue));
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

        return " AND ".$strPropertyValue." ".$this->objComparator->getEnumAsSqlString()." ? ";
    }


}
