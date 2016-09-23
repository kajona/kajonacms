<?php
/*"******************************************************************************************************
*   (c) 2004-2006 by MulchProductions, www.mulchprod.de                                                 *
*   (c) 2007-2016 by Kajona, www.kajona.de                                                              *
*       Published under the GNU LGPL v2.1, see /system/licence_lgpl.txt                                 *
********************************************************************************************************/

namespace Kajona\Statustransition\System;

use Kajona\System\System\Database;
use Kajona\System\System\Exception;
use Kajona\System\System\Model;

/**
 * StatustransitionFlowStep
 *
 * @author christoph.kappestein@artemeon.de
 * @targetTable flow_step.step_id
 * @module statustransition
 * @moduleId _statustransition_module_id_
 */
class StatustransitionFlowStep extends Model
{
    /**
     * @var string
     * @tableColumn flow_step.flow_id
     * @tableColumnDatatype char20
     */
    protected $strFlow;

    /**
     * @var string
     * @tableColumn flow_step.step_name
     * @tableColumnDatatype char254
     */
    protected $strName;

    /**
     * @var string
     * @tableColumn flow_step.step_icon
     * @tableColumnDatatype char20
     */
    protected $strIcon;

    /**
     * @var string
     * @tableColumn flow_step.step_groupid
     * @tableColumnDatatype char20
     */
    protected $strUserGroup;

    /**
     * @var string
     * @tableColumn flow_step.step_transitions
     * @tableColumnDatatype text
     * @blockEscaping
     */
    protected $strTransitions;

    /**
     * @return string
     */
    public function getStrFlow()
    {
        return $this->strFlow;
    }

    /**
     * @param string $strFlow
     */
    public function setStrFlow($strFlow)
    {
        $this->strFlow = $strFlow;
    }

    /**
     * @return string
     */
    public function getStrName()
    {
        return $this->strName;
    }

    /**
     * @param string $strName
     */
    public function setStrName($strName)
    {
        $this->strName = $strName;
    }

    /**
     * @return string
     */
    public function getStrIcon()
    {
        return $this->strIcon;
    }

    /**
     * @param string $strIcon
     */
    public function setStrIcon($strIcon)
    {
        $this->strIcon = $strIcon;
    }

    /**
     * @return string
     */
    public function getStrUserGroup()
    {
        return $this->strUserGroup;
    }

    /**
     * @param string $strUserGroup
     */
    public function setStrUserGroup($strUserGroup)
    {
        $this->strUserGroup = $strUserGroup;
    }

    /**
     * @return string
     */
    public function getStrTransitions()
    {
        return $this->strTransitions;
    }

    /**
     * @param string $strTransitions
     */
    public function setStrTransitions($strTransitions)
    {
        $this->strTransitions = $strTransitions;
    }

    /**
     * @return StatustransitionFlowStepTransition[]
     */
    public function getArrTransitions()
    {
        return array();
    }

    /**
     * @return int
     */
    public function getIntStatus()
    {
        return 0;
    }
}
