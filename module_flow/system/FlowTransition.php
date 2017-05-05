<?php
/*"******************************************************************************************************
*   (c) 2004-2006 by MulchProductions, www.mulchprod.de                                                 *
*   (c) 2007-2016 by Kajona, www.kajona.de                                                              *
*       Published under the GNU LGPL v2.1, see /system/licence_lgpl.txt                                 *
********************************************************************************************************/

namespace Kajona\Flow\System;

use Kajona\System\System\AdminListableInterface;
use Kajona\System\System\Model;
use Kajona\System\System\ModelInterface;
use Kajona\System\System\Objectfactory;

/**
 * FlowTransition
 *
 * @author christoph.kappestein@artemeon.de
 * @targetTable flow_step_transition.transition_id
 * @module flow
 * @moduleId _flow_module_id_
 * @formGenerator Kajona\Flow\Admin\FlowTransitionFormgenerator
 */
class FlowTransition extends Model implements ModelInterface, AdminListableInterface
{
    /**
     * @var string
     * @tableColumn flow_step_transition.target_step
     * @tableColumnDatatype char254
     * @fieldType Kajona\System\Admin\Formentries\FormentryDropdown
     * @fieldMandatory
     */
    protected $strTargetStatus;

    /**
     * @return string
     */
    public function getStrTargetStatus()
    {
        return $this->strTargetStatus;
    }

    /**
     * @param string $strTargetStatus
     */
    public function setStrTargetStatus(string $strTargetStatus)
    {
        $this->strTargetStatus = $strTargetStatus;
    }

    /**
     * @return FlowStatus
     */
    public function getParentStatus()
    {
        return Objectfactory::getInstance()->getObject($this->getPrevId());
    }

    /**
     * @return FlowStatus
     */
    public function getTargetStatus()
    {
        return Objectfactory::getInstance()->getObject($this->strTargetStatus);
    }

    /**
     * @return FlowActionAbstract[]
     */
    public function getArrActions()
    {
        return FlowActionAbstract::getObjectListFiltered(null, $this->getSystemid());
    }

    /**
     * @return FlowConditionAbstract[]
     */
    public function getArrConditions()
    {
        return FlowConditionAbstract::getObjectListFiltered(null, $this->getSystemid());
    }

    /**
     * @return string
     */
    public function getStrIcon()
    {
        return $this->getTargetStatus() ? $this->getTargetStatus()->getStrIcon() : null;
    }

    /**
     * @return string
     */
    public function getStrDisplayName()
    {
        return $this->getTargetStatus() ? $this->getTargetStatus()->getStrName() : null;
    }

    public function getStrAdditionalInfo()
    {
        return "";
    }

    public function getStrLongDescription()
    {
        return "";
    }

    /**
     * Checks whether the transition moves the status forward or backwards in the flow
     *
     * @TODO: this is maybe not the best solution since this depends on the sorting of the status list
     */
    public function isForwarding()
    {
        $objParentStatus = $this->getParentStatus();
        $arrStatus = $objParentStatus->getFlowConfig()->getArrStatus();
        $arrStatus = array_map(function(FlowStatus $objStatus){
            return $objStatus->getIntIndex();
        }, $arrStatus);

        $intCurrentIndex = $objParentStatus->getIntIndex();
        $intFutureIndex = $this->getTargetStatus()->getIntIndex();

        return array_search($intFutureIndex, $arrStatus) > array_search($intCurrentIndex, $arrStatus);
    }
}
