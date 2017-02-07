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
 * StatustransitionFlowStepTransitionCondition
 *
 * @author christoph.kappestein@artemeon.de
 * @targetTable flow_step_transition_condition.condition_id
 * @module statustransition
 * @moduleId _statustransition_module_id_
 * @formGenerator Kajona\Statustransition\Admin\StatustransitionStepTransitionConditionFormgenerator
 */
abstract class StatustransitionFlowStepTransitionCondition extends Model implements ModelInterface, AdminListableInterface, StatustransitionConditionInterface
{
    /**
     * @var string
     * @tableColumn flow_step_transition_condition.condition_params
     * @tableColumnDatatype text
     * @blockEscaping
     */
    protected $strParams;

    /**
     * @var array
     */
    private $arrParameters;

    /**
     * @return string
     */
    public function getStrParams(): string
    {
        return $this->strParams;
    }

    /**
     * @param string $strParams
     */
    public function setStrParams(string $strParams)
    {
        $this->strParams = $strParams;
    }

    /**
     * @return array
     */
    public function getArrParameters()
    {
        return $this->arrParameters === null ? $this->arrParameters = json_decode($this->strParams, true) : $this->arrParameters;
    }

    /**
     * @param string $strName
     * @return string|null
     */
    public function getParameter(string $strName)
    {
        $arrParameters = $this->getArrParameters();
        return isset($arrParameters[$strName]) ? $arrParameters[$strName] : null;
    }

    /**
     * @return string
     */
    public function getStrIcon()
    {
        return "icon_szenario";
    }

    /**
     * @return string
     */
    public function getStrDisplayName()
    {
        return $this->getTitle();
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
