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
 * StatustransitionFlowStep
 *
 * @author christoph.kappestein@artemeon.de
 * @targetTable flow_step.step_id
 * @module statustransition
 * @moduleId _statustransition_module_id_
 * @formGenerator Kajona\Statustransition\Admin\StatustransitionStepFormgenerator
 */
class StatustransitionFlowStep extends Model implements ModelInterface, AdminListableInterface
{
    /**
     * @var string
     * @tableColumn flow_step.step_name
     * @tableColumnDatatype char254
     * @fieldType Kajona\System\Admin\Formentries\FormentryText
     * @fieldMandatory
     * @listOrder ASC
     */
    protected $strName;

    /**
     * @var string
     * @tableColumn flow_step.step_icon
     * @tableColumnDatatype char20
     * @fieldType Kajona\System\Admin\Formentries\FormentryDropdown
     * @fieldDDValues [icon_flag_black => flow_step_icon_0],[icon_flag_blue => flow_step_icon_1],[icon_flag_brown => flow_step_icon_2],[icon_flag_green => flow_step_icon_3],[icon_flag_grey => flow_step_icon_4],[icon_flag_orange => flow_step_icon_5],[icon_flag_purple => flow_step_icon_6],[icon_flag_red => flow_step_icon_7],[icon_flag_yellow => flow_step_icon_8]
     * @fieldMandatory
     */
    protected $strIcon;

    /**
     * @var string
     * @tableColumn flow_step.step_groupid
     * @tableColumnDatatype char20
     * @fieldType Kajona\System\Admin\Formentries\FormentryUser
     * @fieldMandatory
     **/
    protected $strUserGroup;

    /**
     * @var string
     * @fieldType Kajona\System\Admin\Formentries\FormentryObjectlist
     * @objectList flow_step_transition (source="step_id", target="transition_id")
     */
    protected $arrTransitions;

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
     * @return StatustransitionFlowStep[]
     */
    public function getArrTransitions()
    {
        return $this->arrTransitions;
    }

    /**
     * @param string $arrTransitions
     */
    public function setArrTransitions($arrTransitions)
    {
        $this->arrTransitions = $arrTransitions;
    }

    /**
     * Return the status int for this step. At the moment this is simply the 0-indexed position of the step in the flow
     * This int is inserted as record status
     *
     * @return int
     */
    public function getIntStatus()
    {
        /** @var StatustransitionFlow $objFlow */
        $objFlow = Objectfactory::getInstance()->getObject($this->getPrevId());
        $arrSteps = $objFlow->getSteps();

        foreach ($arrSteps as $intKey => $objStep) {
            if ($this->getStrSystemid() == $objStep->getStrSystemid()) {
                return $intKey;
            }
        }

        return 0;
    }

    /**
     * @return string
     */
    public function getStrDisplayName()
    {
        return $this->strName;
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
