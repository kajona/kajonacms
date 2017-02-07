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
 * The status handler contains all informations about the status flow. Through the status handler we can move the model
 * to the next state and get a list of available status transitions. So we have one handler object which can have
 * multiple status options.
 *
 * @author christoph.kappestein@artemeon.de
 * @author stefan.meyer@artemeon.de
 * @module statustransition
 */
abstract class StatustransitionHandler implements StatustransitionHandlerInterface
{
    /**
     * Name of the parameter which contains the transition identifier
     *
     * @var string
     */
    const STR_PARAM_TRANSITIONKEY = "transitionkey";

    /**
     * @var StatustransitionStatus[]
     */
    protected $arrStatus = array();

    /**
     * Adds a new status to the transition handler
     *
     * @param StatustransitionStatus $objStatus
     * @return StatustransitionStatus
     */
    public function addStatus(StatustransitionStatus $objStatus)
    {
        $this->arrStatus[] = $objStatus;

        return $objStatus;
    }

    /**
     * Handles a status transition
     *
     * @param integer $intOldStatus
     * @param string $strTransitionKey
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
                if ($objTransition !== null) {
                    $intNewStatus = $objTransition->getIntTargetStatus();

                    if ($intNewStatus !== null && $intNewStatus != $objObject->getIntRecordStatus()) {
                        // pre transition checks
                        $objTransition->executeTransitionPreConditions($intOldStatus, $intNewStatus, $objObject);

                        // the update triggers the status change event
                        $objObject->setIntRecordStatus($intNewStatus);
                        $objObject->updateObjectToDb();

                        // execute the transition actions
                        $objTransition->executeTransitionActions($intOldStatus, $intNewStatus, $objObject);
                    }
                } else {
                    return false;
                }
            }
            Database::getInstance()->transactionCommit();
        } catch (Exception $e) {
            Database::getInstance()->transactionRollback();
            return false;
        }

        return true;
    }

    /**
     * Returns the fitting status for the provided status
     *
     * @param integer $intOldStatus
     * @return StatustransitionStatus|null
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