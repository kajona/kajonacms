<?php
/*"******************************************************************************************************
*   (c) 2004-2006 by MulchProductions, www.mulchprod.de                                                 *
*   (c) 2007-2016 by Kajona, www.kajona.de                                                              *
*       Published under the GNU LGPL v2.1, see /system/licence_lgpl.txt                                 *
********************************************************************************************************/

namespace Kajona\Statustransition\System;

use Kajona\System\System\AdminListableInterface;
use Kajona\System\System\Model;
use Kajona\System\System\ModelInterface;
use Kajona\System\System\Objectfactory;

/**
 * StatustransitionFlowStepTransition
 *
 * @author christoph.kappestein@artemeon.de
 * @targetTable flow_step_transition.transition_id
 * @module statustransition
 * @moduleId _statustransition_module_id_
 * @formGenerator Kajona\Statustransition\Admin\StatustransitionStepTransitionFormgenerator
 */
class StatustransitionFlowStepTransition extends Model implements ModelInterface, AdminListableInterface
{
    /**
     * @var string
     * @tableColumn flow_step_transition.target_step
     * @tableColumnDatatype char254
     * @fieldType Kajona\System\Admin\Formentries\FormentryDropdown
     * @fieldMandatory
     */
    protected $strTargetStep;

    /**
     * @return string
     */
    public function getStrTargetStep()
    {
        return $this->strTargetStep;
    }

    /**
     * @param string $strTargetStep
     */
    public function setStrTargetStep(string $strTargetStep)
    {
        $this->strTargetStep = $strTargetStep;
    }

    /**
     * @return StatustransitionFlowStep
     */
    public function getTargetStep()
    {
        return Objectfactory::getInstance()->getObject($this->strTargetStep);
    }

    /**
     * @return StatustransitionFlowStepTransitionAction[]
     */
    public function getArrActions()
    {
        return StatustransitionFlowStepTransitionAction::getObjectListFiltered(null, $this->getSystemid());
    }

    /**
     * @return StatustransitionFlowStepTransitionCondition[]
     */
    public function getArrConditions()
    {
        return StatustransitionFlowStepTransitionCondition::getObjectListFiltered(null, $this->getSystemid());
    }

    /**
     * @return string
     */
    public function getStrIcon()
    {
        return $this->getTargetStep()->getStrIcon();
    }

    /**
     * @return string
     */
    public function getStrDisplayName()
    {
        return $this->getTargetStep()->getStrName();
    }

    public function getStrAdditionalInfo()
    {
        return "";
    }

    public function getStrLongDescription()
    {
        return "";
    }
}
