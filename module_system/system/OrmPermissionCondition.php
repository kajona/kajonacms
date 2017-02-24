<?php
/*"******************************************************************************************************
*   (c) 2007-2016 by Kajona, www.kajona.de                                                              *
*       Published under the GNU LGPL v2.1, see /system/licence_lgpl.txt                                 *
********************************************************************************************************/

namespace Kajona\System\System;


/**
 * A orm condition to filter by rights and usergroup assignment.
 *
 *
 * @package Kajona\System\System
 * @author stefan.meyer1@yahoo.de
 * @since 6.0
 */
class OrmPermissionCondition extends OrmCondition
{

    private $arrUserGroupIds = null;
    private $strPermission = null;
    private $strColumn = null;
    private $objCompositeCondition = null;

    /**
     * OrmPermissionCondition constructor.
     *
     * @param string $strPermission
     * @param array|null $arrUserGroupIds
     * @param null $strColumn - optional, set if different column is being used
     */
    public function __construct($strPermission, array $arrUserGroupIds = null, $strColumn = null)
    {
        parent::__construct("", array());

        $this->arrUserGroupIds = $arrUserGroupIds;
        $this->strPermission = $strPermission;

        if ($this->arrUserGroupIds === null) {
            $this->arrUserGroupIds = Carrier::getInstance()->getObjSession()->getGroupIdsAsArray();
        }

        if ($this->strColumn == null) {
            $this->strColumn = "right_".$strPermission;
        }
    }

    /**
     * Generates the compound condition for the condition
     *
     * @return OrmCompositeCondition
     */
    private function generateCompundCondition()
    {
        $strLikeOperator = OrmComparatorEnum::Like;

        $objCompound = new OrmCompositeCondition(array(), OrmCondition::STR_CONDITION_OR);
        foreach ($this->arrUserGroupIds as $strUserGroupId) {
            $objCompound->addCondition(new OrmCondition("{$this->strColumn} {$strLikeOperator}  ?", array("%,".UserGroup::getShortIdForGroupId($strUserGroupId).",%")));
        }

        return $objCompound;
    }

    /**
     * @inheritdoc
     */
    public function getStrWhere()
    {
        if ($this->objCompositeCondition === null) {
            $this->objCompositeCondition = $this->generateCompundCondition();
        }

        return $this->objCompositeCondition->getStrWhere();
    }

    /**
     * @inheritdoc
     */
    public function getArrParams()
    {
        if ($this->objCompositeCondition === null) {
            $this->objCompositeCondition = $this->generateCompundCondition();
        }

        return $this->objCompositeCondition->getArrParams();
    }


}
