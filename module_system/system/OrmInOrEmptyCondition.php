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
    const NULL = "NULL";
    private $bitIncludeNullOrEmptyValues = false;
    private $bitIncludeNullValues = false;

    /**
     * OrmObjectlistInOrEmptyRestriction constructor.
     *
     * @param bool $bitIncludeNullOrEmptyValues
     */
    function __construct($strColumnName, array $arrParams, $strInCondition = self::STR_CONDITION_IN)
    {
        parent::__construct($strColumnName, $arrParams, $strInCondition);

        $intIndex = array_search(self::NULL_OR_EMPTY, $this->arrParams, true);
        if($intIndex !== false) {
            $this->bitIncludeNullOrEmptyValues = true;
            unset($this->arrParams[$intIndex]);
        }

        $intIndex = array_search(self::NULL, $this->arrParams, true);
        if($intIndex !== false) {
            $this->bitIncludeNullValues = true;
            unset($this->arrParams[$intIndex]);
        }

        //magic guessowrk - try to find the data-types of all params and flip the condition in case of non-string only values - then no comparison against '', plz
        if($this->bitIncludeNullOrEmptyValues && count($this->arrParams) > 0) {
            $bitItString = false;
            foreach($this->arrParams as $objOneParam) {
                if(is_string($objOneParam)) {
                    $bitItString = true;
                    break;
                }
            }
            
            if(!$bitItString) {
                $this->bitIncludeNullOrEmptyValues = false;
                $this->bitIncludeNullValues = true;
            }
        }
    }

    public function getStrWhere()
    {
        $strWhere = parent::getStrWhere();

        if($this->bitIncludeNullOrEmptyValues) {
            if($strWhere != "") {
                $strWhere = "({$strWhere}) OR ";
            }
            return "({$strWhere}($this->strColumnName IS NULL) OR ($this->strColumnName = ''))";
        }  
        
        if($this->bitIncludeNullValues) {
            if($strWhere != "") {
                $strWhere = "({$strWhere}) OR ";
            }
            return "({$strWhere}($this->strColumnName IS NULL))";
        }

        return $strWhere;
    }
}
