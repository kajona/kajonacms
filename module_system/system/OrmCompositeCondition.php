<?php
/*"******************************************************************************************************
*   (c) 2007-2016 by Kajona, www.kajona.de                                                              *
*       Published under the GNU LGPL v2.1, see /system/licence_lgpl.txt                                 *
*-------------------------------------------------------------------------------------------------------*
*   $Id$                                        *
********************************************************************************************************/

namespace Kajona\System\System;

/**
 * A orm condition to to store several orm conditions.
 * They will connected via given condition connect.
 * e.g.
 *  ( (<restriction_1>) AND (<restriction_2>) AND (<restriction_3>) )
 *  ( (<restriction_1>) OR (<restriction_2>) OR (<restriction_3>) )
 *
 * @package Kajona\System\System
 * @author stefan.meyer1@yahoo.de
 * @since 5.0
 */
class OrmCompositeCondition extends OrmCondition
{
    /**
     * @var OrmCondition[]
     */
    private $arrConditions = "";

    /**
     * @var string
     */
    private $strConditionConnect = self::STR_CONDITION_AND;

    /**
     * OrmCompositeCondition constructor.
     *
     * @param OrmCondition[] $arrConditions
     * @param array|string|\string[] $strConditionConnect
     */
    public function __construct(array $arrConditions = array(), $strConditionConnect = self::STR_CONDITION_AND)
    {
        parent::__construct("");
        $this->arrConditions = $arrConditions;
        $this->setStrConditionConnect($strConditionConnect);
    }

    /**
     * @return string
     */
    public function getStrConditionConnect()
    {
        return $this->strConditionConnect;
    }

    /**
     * @param string $strConditionConnect
     *
     * @return $this
     * @throws OrmException
     */
    public function setStrConditionConnect($strConditionConnect)
    {
        if ($strConditionConnect !== self::STR_CONDITION_AND && $strConditionConnect !== self::STR_CONDITION_OR) {
            throw new OrmException("strConditionConnect must have value AND or OR. Current value is ".$strConditionConnect, Exception::$level_FATALERROR);
        }

        $this->strConditionConnect = $strConditionConnect;
        return $this;
    }

    public function addCondition(OrmCondition $objRestriction)
    {
        $this->arrConditions[] = $objRestriction;
        return $this;
    }

    public function hasConditions()
    {
        return count($this->arrConditions) > 0;
    }

    public function getStrWhere()
    {
        $arrWhere = array();
        foreach ($this->arrConditions as $objCondition) {
            if (!($objCondition instanceof OrmCondition)) {
                throw new OrmException("no valid OrmCondition instance: ".get_class($objCondition), Exception::$level_FATALERROR);
            }

            //only add if where is not empty
            $strWhere = $objCondition->getStrWhere();
            if ($strWhere != "") {
                $arrWhere[] = $objCondition->getStrWhere();
            }
        }

        $strWhere = "";
        if (count($arrWhere) > 0) {
            $strWhere = implode(") ".$this->strConditionConnect." (", $arrWhere);

            if (count($arrWhere) == 1) {
                $strWhere = "(".$strWhere.")";
            } else {
                $strWhere = "( (".$strWhere.") )";
            }
        }

        return $strWhere;
    }

    public function getArrParams()
    {
        $arrParams = array();
        foreach ($this->arrConditions as $objCondition) {
            if (!($objCondition instanceof OrmCondition)) {
                throw new OrmException("no valid OrmCondition instance: ".get_class($objCondition), Exception::$level_FATALERROR);
            }

            //only add if where is not empty
            $strWhere = $objCondition->getStrWhere();
            if ($strWhere != "") {
                $arrParams = array_merge($arrParams, $objCondition->getArrParams());
            }
        }

        return $arrParams;
    }

}
