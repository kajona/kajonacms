<?php
/*"******************************************************************************************************
*   (c) 2010-2016 ARTEMEON                                                                              *
*-------------------------------------------------------------------------------------------------------*
*	$Id$                                         *
********************************************************************************************************/

namespace AGP\Statustransition\System;

use Kajona\System\System\Database;
use Kajona\System\System\Exception;
use Kajona\System\System\Model;

/**
 * Class StatustransitionHandler
 *
 * @author christoph.kappestein@artemeon.de
 * @author stefan.meyer@artemeon.de
 * @module statustransition
 */
abstract class StatustransitionHandler
{
    /**
     * @var WorkflowStatus[]
     */
    protected $arrStatus = array();

    /**
     * Adds a new status to the workflow
     *
     * @param WorkflowStatus $objStatus
     * @return WorkflowStatus
     */
    public function addStatus(WorkflowStatus $objStatus)
    {
        $this->arrStatus[] = $objStatus;

        return $objStatus;
    }

    /**
     * Handles a status transition
     *
     * @param integer $intOldStatus
     * @param integer $strTransitionKey
     * @param Model $objObject
     * @return boolean - true if transition is executed, false if not
     * @throws \Kajona\System\System\Exception
     */
    public function handleStatusTransition($intOldStatus, $strTransitionKey, Model $objObject)
    {
        try {
            Database::getInstance()->transactionBegin();

            $objStatus = $this->getStatus($intOldStatus);
            if ($objStatus !== null) {

                $objTransition = $objStatus->getTransitionByKey($strTransitionKey, $objObject);
                if($objTransition !== null) {
                    $intNewStatus = $objTransition->getIntTargetStatus();

                    if ($intNewStatus !== null && $intNewStatus != $objObject->getIntRecordStatus()) {

                        //pre transition checks
                        $objTransition->executeTransitionPreConditions($intOldStatus, $intNewStatus, $objObject);

                        // the update triggers the status change event
                        $objObject->setIntRecordStatus($intNewStatus);
                        $objObject->updateObjectToDb();

                        // execute the transition actions
                        $objTransition->executeTransitionActions($intOldStatus, $intNewStatus, $objObject);
                    }
                }
            }
            Database::getInstance()->transactionCommit();
        }
        catch(Exception $e) {
            Database::getInstance()->transactionRollback();
            return false;
        }

        return true;
    }

    /**
     * Returns the fitting status for the provided status
     *
     * @param integer $intOldStatus
     * @return WorkflowStatus|null
     */
    public function getStatus($intOldStatus)
    {
        foreach ($this->arrStatus as $objStatus) {
            if ($objStatus->getIntStatus() == $intOldStatus) {
                return $objStatus;
            }
        }
        return null;
    }
}