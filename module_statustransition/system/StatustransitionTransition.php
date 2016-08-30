<?php
/*"******************************************************************************************************
*   (c) 2004-2006 by MulchProductions, www.mulchprod.de                                                 *
*   (c) 2007-2016 by Kajona, www.kajona.de                                                              *
*       Published under the GNU LGPL v2.1, see /system/licence_lgpl.txt                                 *
********************************************************************************************************/

namespace Kajona\Statustransition\System;

use Kajona\System\System\Exception;
use Kajona\System\System\Model;
use Kajona\System\System\RequestEntrypointEnum;
use Kajona\System\System\ResponseObject;

/**
 * The status transition represents a transition from status A to B. If this happens the provided actions and conditions
 * are executed. The right callback checks whether a user is allowed to executed this specific transition.
 *
 * @author christoph.kappestein@artemeon.de
 * @author stefan.meyer@artemeon.de
 * @module statustransition
 */
class StatustransitionTransition
{
    /**
     * @var integer
     */
    private $intTargetStatus;

    /**
     * @var string
     */
    private $strTransitionKey;

    /**
     * @var string
     */
    private $strChoiceLabel;

    /**
     * @var array
     */
    private $arrWorkflowActions;

    /**
     * @var \Closure|null
     */
    private $objRightCallback;

    /**
     * @var array
     */
    private $arrPreConditions;

    /**
     * WorkflowTransition constructor.
     *
     * @param $intTargetStatus - target status of the transition
     * @param $strTransitionKey
     * @param $strChoiceLabel - Title of the choice in dropdown
     * @param array $arrWorkflowActions - action to be called when transition is executed
     * @param \Closure|null $objRightCallback - callback for checking if the user is allowed to perform this transition
     * @param array $arrPreConditions - conditions to be called before the transition is being fired. If one condition results into false, hte transition is not being executed
     */
    public function __construct($intTargetStatus, $strTransitionKey, $strChoiceLabel, array $arrWorkflowActions = array(), \Closure $objRightCallback = null, array $arrPreConditions = array())
    {
        $this->intTargetStatus = $intTargetStatus;
        $this->strTransitionKey = $strTransitionKey;
        $this->strChoiceLabel = $strChoiceLabel;
        $this->arrWorkflowActions = $arrWorkflowActions;
        $this->objRightCallback = $objRightCallback;
        $this->arrPreConditions = $arrPreConditions;
    }

    /**
     * Checks if the user has the right to execute this transition
     *
     * @param Model $objModel
     * @return bool
     */
    public function bitCheckTransitionRight(Model $objModel)
    {
        if (ResponseObject::getInstance()->getObjEntrypoint()->equals(RequestEntrypointEnum::INSTALLER())) {
            return true;
        } else {
            return $this->getObjRightCallback() === null || call_user_func_array($this->getObjRightCallback(), array($objModel)) === true;
        }
    }

    /**
     * @param $oldStatus
     * @param $newStatus
     * @param Model $objModel
     */
    public function executeTransitionActions($oldStatus, $newStatus, Model $objModel)
    {
        $arrActions = $this->getArrWorkflowActions();
        if (!empty($arrActions)) {
            foreach ($arrActions as $objAction) {
                if ($objAction instanceof StatustransitionActionInterface) {
                    $objAction->executeAction($oldStatus, $newStatus, $objModel);
                }
            }
        }
    }

    /**
     * @param $oldStatus
     * @param $newStatus
     * @param Model $objModel
     * @throws Exception
     */
    public function executeTransitionPreConditions($oldStatus, $newStatus, Model $objModel)
    {
        $arrActions = $this->getArrPreConditions();
        if (!empty($arrActions)) {
            foreach ($arrActions as $objAction) {
                if ($objAction instanceof StatustransitionConditionInterface) {
                    $bitResult = $objAction->validateCondition($oldStatus, $newStatus, $objModel);

                    if (!$bitResult) {
                        throw new Exception("Validation of condition failed", Exception::$level_ERROR);
                    }
                }
            }
        }
    }

    /**
     * @return null
     */
    public function getIntTargetStatus()
    {
        return $this->intTargetStatus;
    }

    /**
     * @param null $intTargetStatus
     */
    public function setIntTargetStatus($intTargetStatus)
    {
        $this->intTargetStatus = $intTargetStatus;
    }

    /**
     * @return null
     */
    public function getStrChoiceLabel()
    {
        return $this->strChoiceLabel;
    }

    /**
     * @param null $strChoiceLabel
     */
    public function setStrChoiceLabel($strChoiceLabel)
    {
        $this->strChoiceLabel = $strChoiceLabel;
    }

    /**
     * @return array|null
     */
    public function getArrWorkflowActions()
    {
        return $this->arrWorkflowActions;
    }

    /**
     * @param array|null $arrWorkflowActions
     */
    public function setArrWorkflowActions($arrWorkflowActions)
    {
        $this->arrWorkflowActions = $arrWorkflowActions;
    }

    /**
     * @return \Closure|null
     */
    public function getObjRightCallback()
    {
        return $this->objRightCallback;
    }

    /**
     * @param \Closure|null $objRightCallback
     */
    public function setObjRightCallback($objRightCallback)
    {
        $this->objRightCallback = $objRightCallback;
    }

    /**
     * @return null
     */
    public function getStrTransitionKey()
    {
        return $this->strTransitionKey;
    }

    /**
     * @param null $strTransitionKey
     */
    public function setStrTransitionKey($strTransitionKey)
    {
        $this->strTransitionKey = $strTransitionKey;
    }

    /**
     * @return array|null
     */
    public function getArrPreConditions()
    {
        return $this->arrPreConditions;
    }

    /**
     * @param array|null $arrPreConditions
     */
    public function setArrPreConditions($arrPreConditions)
    {
        $this->arrPreConditions = $arrPreConditions;
    }
}
