<?php
/*"******************************************************************************************************
*   (c) 2004-2006 by MulchProductions, www.mulchprod.de                                                 *
*   (c) 2007-2016 by Kajona, www.kajona.de                                                              *
*       Published under the GNU LGPL v2.1, see /system/licence_lgpl.txt                                 *
********************************************************************************************************/

namespace Kajona\Flow\System;

use Kajona\System\Admin\AdminFormgenerator;
use Kajona\System\System\Database;
use Kajona\System\System\Logger;
use Kajona\System\System\Model;
use Kajona\System\System\RedirectException;

/**
 * The status handler contains all informations about the status flow. Through the status handler we can move the model
 * to the next state and get a list of available status transitions. So we have one handler object which can have
 * multiple status options.
 *
 * @author christoph.kappestein@artemeon.de
 * @author stefan.meyer@artemeon.de
 * @module flow
 */
abstract class FlowHandlerAbstract implements FlowHandlerInterface
{
    /**
     * @var FlowManager
     */
    protected $objFlowManager;

    /**
     * @param FlowManager $objFlowManager
     */
    public function __construct(FlowManager $objFlowManager)
    {
        $this->objFlowManager = $objFlowManager;
    }

    /**
     * Handles a status transition
     *
     * @param Model $objObject
     * @param FlowTransition $objTransition
     * @return boolean - true if transition is executed, false if not
     * @throws \Kajona\System\System\Exception
     */
    public function handleStatusTransition(Model $objObject, FlowTransition $objTransition) : bool
    {
        try {
            Database::getInstance()->transactionBegin();

            $intNewStatus = $objTransition->getTargetStatus()->getIntStatus();

            if ($intNewStatus != $objObject->getIntRecordStatus()) {
                // check whether there are validation errors
                $arrErrors = $this->validateStatusTransition($objObject, $objTransition);
                if (count($arrErrors) > 0) {
                    throw new \RuntimeException("There are validation errors for this status transition");
                }

                // check whether the transition is visible
                $bitReturn = $this->isTransitionVisible($objObject, $objTransition);
                if (!$bitReturn) {
                    throw new \RuntimeException("Transition is not visible");
                }

                // check whether all assigned conditions are fulfilled
                $bitReturn = $this->validateConditions($objObject, $objTransition);
                if (!$bitReturn) {
                    throw new \RuntimeException("Condition not fulfilled");
                }

                // persist the new status
                $objObject->setIntRecordStatus($intNewStatus);
                $objObject->updateObjectToDb();

                // execute transition actions
                $this->executeActions($objObject, $objTransition);

                // execute handler actions
                $this->executeStatusTransition($objObject, $objTransition);
            }

            Database::getInstance()->transactionCommit();
        } catch (RedirectException $e) {
            Database::getInstance()->transactionCommit();

            throw $e;
        } catch (\Exception $e) {
            Database::getInstance()->transactionRollback();

            Logger::getInstance(Logger::SYSTEMLOG)->addLogRow("Status-Transition error: " . $e->getMessage() . "\n" . $e->getTraceAsString(), Logger::$levelError);
            return false;
        }

        return true;
    }

    /**
     * @param Model $objObject
     * @param FlowTransition $objTransition
     * @return array
     */
    public function validateStatusTransition(Model $objObject, FlowTransition $objTransition) : array
    {
        return [];
    }

    /**
     * Callback method which can be overridden by a handler to validate whether a status transition is possible. The
     * transition is not listed in the status drop down if this method returns false
     *
     * @param Model $objObject
     * @param FlowTransition $objTransition
     * @return bool
     */
    public function isTransitionVisible(Model $objObject, FlowTransition $objTransition) : bool
    {
        return true;
    }

    /**
     * Callback method which can be overridden by a handler to execute additional actions on a status transition
     *
     * @param Model $objObject
     * @param FlowTransition $objTransition
     */
    protected function executeStatusTransition(Model $objObject, FlowTransition $objTransition)
    {
    }

    /**
     * @param Model $objObject
     * @param FlowTransition $objTransition
     * @return boolean
     */
    private function validateConditions(Model $objObject, FlowTransition $objTransition)
    {
        $bitResult = true;
        $arrConditions = $objTransition->getArrConditions();
        if (!empty($arrConditions)) {
            foreach ($arrConditions as $objCondition) {
                if ($objCondition instanceof FlowConditionInterface) {
                    $bitResult = $bitResult && $objCondition->validateCondition($objObject, $objTransition);
                    if ($bitResult === false) {
                        break;
                    }
                }
            }
        }

        return $bitResult;
    }

    /**
     * @param Model $objObject
     * @param FlowTransition $objTransition
     */
    private function executeActions(Model $objObject, FlowTransition $objTransition)
    {
        $arrActions = $objTransition->getArrActions();
        if (!empty($arrActions)) {
            foreach ($arrActions as $objAction) {
                if ($objAction instanceof FlowActionInterface) {
                    $objAction->executeAction($objObject, $objTransition);
                }
            }
        }
    }

    /**
     * Returns the name of extension/plugin the objects wants to contribute to.
     *
     * @return string
     */
    public static function getExtensionName()
    {
        return self::EXTENSION_POINT;
    }
}

