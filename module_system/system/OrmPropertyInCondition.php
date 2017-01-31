<?php
/*"******************************************************************************************************
*   (c) 2007-2016 by Kajona, www.kajona.de                                                              *
*       Published under the GNU LGPL v2.1, see /system/licence_lgpl.txt                                 *
*-------------------------------------------------------------------------------------------------------*
*   $Id$                                        *
********************************************************************************************************/

namespace Kajona\System\System;

/**
 * A orm condition may be used to create where conditions for the objectList and objectCount queries.
 * This condition creates an IN statement e.g. "AND <columnname> IN (<parameters>)"
 *
 * @package Kajona\System\System
 * @author stefan.meyer1@yahoo.de
 * @since 5.0
 */
class OrmPropertyInCondition extends OrmInCondition
{
    /**
     * @inheritdoc
     */
    public function __construct($strProperty, array $arrParams, $strInCondition = self::STR_CONDITION_IN)
    {
        parent::__construct($strProperty, $arrParams, $strInCondition);
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

        $strPropertyValue = $objReflection->getAnnotationValueForProperty($this->strColumnName, OrmBase::STR_ANNOTATION_TABLECOLUMN);

        if ($strPropertyValue == null) {
            throw new OrmException("Failed to load annotation ".OrmBase::STR_ANNOTATION_TABLECOLUMN." for property ".$this->strColumnName."@".$this->getStrTargetClass(), OrmException::$level_ERROR);
        }


        return $this->getInStatement($strPropertyValue);
    }
}
