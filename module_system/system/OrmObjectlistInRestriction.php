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
class OrmObjectlistInRestriction extends OrmObjectlistRestriction
{

    const MAX_IN_VALUES = 950;

    const STR_CONDITION_IN = "IN";
    const STR_CONDITION_NOTIN = "NOT IN";

    private $strColumnName = "";
    private $arrParams = array();
    private $strCondition = "";
    private $strInCondition = self::STR_CONDITION_IN;

    function __construct($strProperty, array $arrParams, $strCondition = "AND", $strInCondition = self::STR_CONDITION_IN)
    {
        if ($strInCondition !== self::STR_CONDITION_IN && $strInCondition !== self::STR_CONDITION_NOTIN) {
            throw new Exception("Wrong condition set", Exception::$level_ERROR);
        }

        $this->arrParams = $arrParams;
        $this->strCondition = $strCondition;
        $this->strColumnName = $strProperty;
        $this->strInCondition = $strInCondition;
    }

    /**
     * @param array $arrParams
     *
     * @throws class_orm_exception
     */
    public function setArrParams($arrParams)
    {
        throw new OrmException("Setting params for property IN restrictions is not supported", OrmException::$level_ERROR);
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
     * @throws class_orm_exception
     */
    public function setStrWhere($strWhere)
    {
        throw new OrmException("Setting a where restriction for property IN restrictions is not supported", OrmException::$level_ERROR);
    }

    /**
     * Here comes the magic, generation a where restriction out of the passed property name and the comparator
     *
     * @return string
     * @throws class_orm_exception
     */
    public function getStrWhere()
    {
        return " ".$this->getInStatement($this->strColumnName)." ";
    }

    protected function getInStatement($strColumnName)
    {

        if (is_array($this->arrParams) && count($this->arrParams) > 0) {
            if (count($this->arrParams) > self::MAX_IN_VALUES) {
                $intCount = ceil(count($this->arrParams) / self::MAX_IN_VALUES);
                $arrParts = array();

                for ($intI = 0; $intI < $intCount; $intI++) {
                    $arrParams = array_slice($this->arrParams, $intI * self::MAX_IN_VALUES, self::MAX_IN_VALUES);
                    $arrParamsPlaceholder = array_map(function ($objParameter) {
                        return "?";
                    }, $arrParams);
                    $strPlaceholder = implode(",", $arrParamsPlaceholder);
                    if (!empty($strPlaceholder)) {
                        $arrParts[] = "{$strColumnName} {$this->strInCondition} ({$strPlaceholder})";
                    }
                }

                if (count($arrParts) > 0) {
                    return $this->strCondition." (".implode(" OR ", $arrParts).")";
                }
            }
            else {
                $arrParamsPlaceholder = array_map(function ($objParameter) {
                    return "?";
                }, $this->arrParams);
                $strPlaceholder = implode(",", $arrParamsPlaceholder);

                if (!empty($strPlaceholder)) {
                    return "{$this->strCondition} {$strColumnName} {$this->strInCondition} ({$strPlaceholder})";
                }
            }
        }

        return "";
    }
}
