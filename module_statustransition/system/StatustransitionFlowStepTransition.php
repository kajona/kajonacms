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
 * StatustransitionFlowStepTransition
 *
 * @author christoph.kappestein@artemeon.de
 * @targetTable flow_step.step_id
 * @module statustransition
 * @moduleId _statustransition_module_id_
 */
class StatustransitionFlowStepTransition extends Model
{
    /**
     * @var string
     */
    protected $intTargetStatus;

    /**
     * @var string
     */
    protected $strTransitionKey;

    /**
     * @var string
     */
    protected $strChoiceLabel;

    /**
     * @return string
     */
    public function getIntTargetStatus()
    {
        return $this->intTargetStatus;
    }

    /**
     * @param string $intTargetStatus
     */
    public function setIntTargetStatus($intTargetStatus)
    {
        $this->intTargetStatus = $intTargetStatus;
    }

    /**
     * @return string
     */
    public function getStrTransitionKey()
    {
        return $this->strTransitionKey;
    }

    /**
     * @param string $strTransitionKey
     */
    public function setStrTransitionKey($strTransitionKey)
    {
        $this->strTransitionKey = $strTransitionKey;
    }

    /**
     * @return string
     */
    public function getStrChoiceLabel()
    {
        return $this->strChoiceLabel;
    }

    /**
     * @param string $strChoiceLabel
     */
    public function setStrChoiceLabel($strChoiceLabel)
    {
        $this->strChoiceLabel = $strChoiceLabel;
    }
}
