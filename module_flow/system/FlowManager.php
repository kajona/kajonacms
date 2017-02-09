<?php
/*"******************************************************************************************************
*   (c) 2004-2006 by MulchProductions, www.mulchprod.de                                                 *
*   (c) 2007-2016 by Kajona, www.kajona.de                                                              *
*       Published under the GNU LGPL v2.1, see /system/licence_lgpl.txt                                 *
********************************************************************************************************/

namespace Kajona\Flow\System;

use Kajona\System\System\Model;

/**
 * @author christoph.kappestein@artemeon.de
 * @module flow
 */
class FlowManager
{
    /**
     * @var array
     */
    private $arrFlows;

    /**
     * FlowManager constructor.
     */
    public function __construct()
    {
        $this->arrFlows = [];
    }

    /**
     * Returns all available status transition for the current step
     *
     * @param Model $objObject
     * @return FlowTransition[]
     */
    public function getPossibleTransitionsForModel(Model $objObject)
    {
        $objStep = $this->getCurrentStepForModel($objObject);
        if ($objStep instanceof FlowStatus) {
            $intOldStatus = $objObject->getIntRecordStatus();
            $arrTransitions = $objStep->getArrTransitions();
            $arrResult = [];

            // filter out transitions where the condition is not valid
            foreach ($arrTransitions as $objTransition) {
                $intNewStatus = $objTransition->getTargetStatus()->getIntStatus();
                $arrConditions = $objTransition->getArrConditions();

                $bitValid = true;
                foreach ($arrConditions as $objCondition) {
                    $bitValid = $objCondition->validateCondition($intOldStatus, $intNewStatus, $objObject);
                    if ($bitValid === false) {
                        break;
                    }
                }

                if ($bitValid === true) {
                    $arrResult[] = $objTransition;
                }
            }

            return $arrResult;
        }

        return [];
    }

    /**
     * @param Model $objObject
     * @return FlowStatus|null
     */
    public function getCurrentStepForModel(Model $objObject)
    {
        $objFlow = $this->getFlowForModel($objObject);
        if ($objFlow instanceof FlowConfig) {
            return $objFlow->getStepForStatus($objObject->getIntRecordStatus());
        }
        return null;
    }

    /**
     * Returns the status transition handler for this object or null if no handler was attached
     *
     * @param Model $objObject
     * @return FlowConfig|null
     */
    public function getFlowForModel(Model $objObject)
    {
        $strClass = get_class($objObject);
        if (!isset($this->arrFlows[$strClass])) {
            $objFlow = FlowConfig::getByModelClass($strClass);
            if ($objFlow instanceof FlowConfig) {
                $this->arrFlows[$strClass] = $objFlow;
            } else {
                return null;
            }
        }
        return $this->arrFlows[$strClass];
    }
}
