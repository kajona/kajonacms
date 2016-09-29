<?php
/*"******************************************************************************************************
*   (c) 2004-2006 by MulchProductions, www.mulchprod.de                                                 *
*   (c) 2007-2016 by Kajona, www.kajona.de                                                              *
*       Published under the GNU LGPL v2.1, see /system/licence_lgpl.txt                                 *
********************************************************************************************************/

namespace Kajona\Statustransition\System;

use Kajona\System\System\Database;
use Kajona\System\System\Model;
use Kajona\System\System\Objectfactory;
use Kajona\System\System\Session;

/**
 * @author christoph.kappestein@artemeon.de
 * @module statustransition
 */
class StatustransitionManager
{
    /**
     * Returns the status transition handler for this object or null if no handler was attached
     *
     * @param Model $objObject
     * @return StatustransitionHandler|null
     */
    public function getHandler(Model $objObject)
    {
        $objFlow = $this->getFlowForObject($objObject);
        if ($objFlow instanceof StatustransitionFlow) {
            $arrSteps = $objFlow->getSteps();
            $objHandler = new StatustransitionDatabaseHandler();

            foreach ($arrSteps as $intKey => $objStep) {
                $objStatus = $objHandler->addStatus(new StatustransitionStatus($intKey, $objStep->getStrName(), $objStep->getStrIcon()));

                $arrTransitions = $objStep->getArrTransitions();
                foreach ($arrTransitions as $objTargetStatus) {
                    /** @var StatustransitionFlowStep $objTargetStatus */
                    $arrWorkflowActions = array();
                    $objRightCallback = function (Model $objModel) use ($objStep) {
                        return $objModel->rightEdit() && strpos(Session::getInstance()->getGroupIdsAsString(), $objStep->getStrUserGroup()) !== false;
                    };
                    $arrPreConditions = array();

                    $objStatus->addTransition(
                        new StatustransitionTransition(
                            $objTargetStatus->getIntStatus(),
                            $objTargetStatus->getSystemid(),
                            $objTargetStatus->getStrName(),
                            $arrWorkflowActions,
                            $objRightCallback,
                            $arrPreConditions
                        )
                    );
                }
            }

            return $objHandler;
        } else {
            return null;
        }
    }

    /**
     * Returns the flow object which should be used for this model
     *
     * @param Model $objObject
     * @return StatustransitionFlow
     */
    protected function getFlowForObject(Model $objObject)
    {
        if ($objObject instanceof StatustransitionFlowChoiceInterface) {
            return $this->getConfiguredFlowByClassAndKey(get_class($objObject), $objObject->getStatusTransitionFlow());
        }

        return null;
    }

    /**
     * @param string $strClass
     * @param string $strKey
     * @return StatustransitionFlow|null
     */
    protected function getConfiguredFlowByClassAndKey($strClass, $strKey)
    {
        $objFilter = new StatustransitionFlowAssignmentFilter();
        $objFilter->setStrClass(Database::getInstance()->escape($strClass));
        $objFilter->setStrKey($strKey);

        $arrAssignments = StatustransitionFlowAssignment::getObjectListFiltered($objFilter);
        $objAssignment = reset($arrAssignments);

        if ($objAssignment instanceof StatustransitionFlowAssignment) {
            return Objectfactory::getInstance()->getObject($objAssignment->getStrFlow());
        } else {
            return null;
        }
    }
}
