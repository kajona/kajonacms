<?php
/*"******************************************************************************************************
*   (c) 2004-2006 by MulchProductions, www.mulchprod.de                                                 *
*   (c) 2007-2016 by Kajona, www.kajona.de                                                              *
*       Published under the GNU LGPL v2.1, see /system/licence_lgpl.txt                                 *
********************************************************************************************************/

namespace Kajona\Flow\System;

use Kajona\System\System\Database;
use Kajona\System\System\Logger;
use Kajona\System\System\Model;

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
     * @var FlowConfig
     */
    private $objFlow;

    /**
     * Handles a status transition
     *
     * @param Model $objObject
     * @param FlowTransition $objTransition
     * @return boolean - true if transition is executed, false if not
     * @throws \Kajona\System\System\Exception
     */
    public function handleStatusTransition(Model $objObject, FlowTransition $objTransition)
    {
        try {
            Database::getInstance()->transactionBegin();

            $intOldStatus = $objObject->getIntRecordStatus();
            $intNewStatus = $objTransition->getTargetStep()->getIntStatus();

            if ($intNewStatus != $objObject->getIntRecordStatus()) {
                // validate handler conditions
                $bitReturn = $this->validateStatusTransition($intOldStatus, $intNewStatus, $objObject, $objTransition);
                if ($bitReturn === false) {
                    throw new \RuntimeException("Condition not fulfilled");
                }

                // validate transition conditions
                $bitReturn = $this->validateConditions($intOldStatus, $intNewStatus, $objObject, $objTransition);
                if ($bitReturn === false) {
                    throw new \RuntimeException("Condition not fulfilled");
                }

                // persist the new status
                $objObject->setIntRecordStatus($intNewStatus);
                $objObject->updateObjectToDb();

                // execute handler actions
                $this->executeStatusTransition($intOldStatus, $intNewStatus, $objObject, $objTransition);

                // execute transition actions
                $this->executeActions($intOldStatus, $intNewStatus, $objObject, $objTransition);
            }

            Database::getInstance()->transactionCommit();
        } catch (\Exception $e) {
            Database::getInstance()->transactionRollback();

            Logger::getInstance(Logger::SYSTEMLOG)->addLogRow("Status-Transition error: " . $e->getMessage() . "\n" . $e->getTraceAsString(), Logger::$levelError);
            return false;
        }

        return true;
    }

    /**
     * Returns the fitting status for the provided status
     *
     * @param integer $intOldStatus
     * @return FlowStatus|null
     */
    public function getStatus($intOldStatus)
    {
        $arrStatus = FlowStatus::getObjectListFiltered(null, $this->objFlow->getSystemid());
        foreach ($arrStatus as $objStatus) {
            /** @var FlowStatus $objStatus */
            if ($objStatus->getIntStatus() == $intOldStatus) {
                return $objStatus;
            }
        }
        return null;
    }

    /**
     * Callback method which can be overridden by a handler to validate whether a status transition is possible
     *
     * @param int $intOldStatus
     * @param int $intNewStatus
     * @param Model $objModel
     * @param FlowTransition $objTransition
     * @return bool
     */
    protected function validateStatusTransition($intOldStatus, $intNewStatus, Model $objModel, FlowTransition $objTransition)
    {
        return true;
    }

    /**
     * Callback method which can be overridden by a handler to execute additional actions on a status transition
     *
     * @param int $intOldStatus
     * @param int $intNewStatus
     * @param Model $objModel
     * @param FlowTransition $objTransition
     */
    protected function executeStatusTransition($intOldStatus, $intNewStatus, Model $objModel, FlowTransition $objTransition)
    {
    }

    /**
     * @param int $intOldStatus
     * @param int $intNewStatus
     * @param Model $objModel
     * @param FlowTransition $objTransition
     * @return boolean
     */
    private function validateConditions($intOldStatus, $intNewStatus, Model $objModel, FlowTransition $objTransition)
    {
        $bitResult = true;
        $arrConditions = $objTransition->getArrConditions();
        if (!empty($arrConditions)) {
            foreach ($arrConditions as $objCondition) {
                if ($objCondition instanceof FlowConditionInterface) {
                    $bitResult = $bitResult && $objCondition->validateCondition($intOldStatus, $intNewStatus, $objModel);
                    if ($bitResult === false) {
                        break;
                    }
                }
            }
        }

        return $bitResult;
    }

    /**
     * @param integer $intOldStatus
     * @param integer $intNewStatus
     * @param Model $objModel
     * @param FlowTransition $objTransition
     */
    private function executeActions($intOldStatus, $intNewStatus, Model $objModel, FlowTransition $objTransition)
    {
        $arrActions = $objTransition->getArrActions();
        if (!empty($arrActions)) {
            foreach ($arrActions as $objAction) {
                if ($objAction instanceof FlowActionInterface) {
                    $objAction->executeAction($intOldStatus, $intNewStatus, $objModel);
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

