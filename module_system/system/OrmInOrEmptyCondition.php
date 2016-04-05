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
class OrmInOrEmptyCondition extends OrmInCondition
{
    const NULL_OR_EMPTY = "NULL_OR_EMPTY";
    private $bitIncludeNullOrEmptyValues = false;

    /**
     * OrmObjectlistInOrEmptyRestriction constructor.
     *
     * @param bool $bitIncludeNullOrEmptyValues
     */
    function __construct($strColumnName, array $arrParams, $strInCondition = self::STR_CONDITION_IN)
    {
        parent::__construct($strColumnName, $arrParams, $strInCondition);

        if(in_array(self::NULL_OR_EMPTY, $this->arrParams)) {
            $this->bitIncludeNullOrEmptyValues = true;
        }
    }

    public function getStrWhere()
    {
        $strWhere = parent::getStrWhere();

        if($this->bitIncludeNullOrEmptyValues) {
            return "(($strWhere) OR ($this->strColumnName IS NULL) OR ($this->strColumnName = ''))";
        }

        return $strWhere;
    }
}
