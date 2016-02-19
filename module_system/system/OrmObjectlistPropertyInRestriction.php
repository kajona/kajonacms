<?php
/*"******************************************************************************************************
*   (c) 2007-2015 by Kajona, www.kajona.de                                                              *
*       Published under the GNU LGPL v2.1, see /system/licence_lgpl.txt                                 *
*-------------------------------------------------------------------------------------------------------*
*   $Id$                                        *
********************************************************************************************************/

namespace Kajona\System\System;


/**
 * A objectlist restriction may be used to create where restrictions for the objectList and objectCount queries.
 * This restrcition creates an IN statement e.g. "AND <columnname> IN (<parameters>)"
 *
 * @package module_system
 * @author stefan.meyer1@yahoo.de
 * @since 4.8
 */
class OrmObjectlistPropertyInRestriction extends OrmObjectlistInRestriction
{

    private $strPropertyName;

    function __construct($strProperty, array $arrParams, $strCondition = "AND")
    {
        parent::__construct($strProperty, $arrParams, $strCondition);

        $this->strPropertyName = $strProperty;
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

        $strPropertyValue = $objReflection->getAnnotationValueForProperty($this->strPropertyName, OrmBase::STR_ANNOTATION_TABLECOLUMN);

        if ($strPropertyValue == null) {
            throw new OrmException("Failed to load annotation ".OrmBase::STR_ANNOTATION_TABLECOLUMN." for property ".$this->strPropertyName."@".$this->getStrTargetClass(), OrmException::$level_ERROR);
        }


        return $this->getInStatement($strPropertyValue);
    }

}
