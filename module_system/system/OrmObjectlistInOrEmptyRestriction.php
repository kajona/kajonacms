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
 * This restrcition creates an IN statement e.g. "AND <columnname> IN (<parameters>)"
 *
 * @package module_system
 * @author stefan.meyer1@yahoo.de
 * @since 4.8
 */
class OrmObjectlistInOrEmptyRestriction extends OrmObjectlistInRestriction
{
    const NULL_OR_EMPTY = "NULL_OR_EMPTY";

    private $bitIncludeNullOrEmptyValues = false;

    /**
     * OrmObjectlistInOrEmptyRestriction constructor.
     *
     * @param bool $bitIncludeNullOrEmptyValues
     */
    function __construct($strProperty, array $arrParams, $strCondition = "AND", $strInCondition = self::STR_CONDITION_IN)
    {
        parent::__construct($strProperty, $arrParams, $strCondition, $strInCondition);

        if(in_array(self::NULL_OR_EMPTY, $this->arrParams)) {
            $this->bitIncludeNullOrEmptyValues = true;
        }
    }


    /**
     * @param $strColumnName
     * @param $strCondition
     *
     * @return string
     */
    protected function addAdditionalConditions($strColumnName, $strCondition)
    {
        if($this->bitIncludeNullOrEmptyValues) {
            return "$strCondition ($strColumnName IS NULL OR $strColumnName = '')";
        }

        return "";
    }
}
