<?php
/*"******************************************************************************************************
*   (c) 2004-2006 by MulchProductions, www.mulchprod.de                                                 *
*   (c) 2007-2016 by Kajona, www.kajona.de                                                              *
*       Published under the GNU LGPL v2.1, see /system/licence_lgpl.txt                                 *
********************************************************************************************************/

namespace Kajona\Statustransition\System;

use Kajona\System\System\Model;

/**
 * @author christoph.kappestein@artemeon.de
 * @module statustransition
 */
class StatustransitionManager
{
    /**
     * @var array
     */
    private $arrFlows;

    /**
     * StatustransitionManager constructor.
     */
    public function __construct()
    {
        $this->arrFlows = [];
    }

    /**
     * Returns all available status transition for the current step
     *
     * @param Model $objObject
     * @return StatustransitionFlowStepTransition[]
     */
    public function getPossibleTransitionsForModel(Model $objObject)
    {
        $objStep = $this->getCurrentStepForModel($objObject);
        if ($objStep instanceof StatustransitionFlowStep) {
            return $objStep->getArrTransitions();
        }
        return [];
    }

    /**
     * @param Model $objObject
     * @return StatustransitionFlowStep|null
     */
    public function getCurrentStepForModel(Model $objObject)
    {
        $objFlow = $this->getFlowForModel($objObject);
        if ($objFlow instanceof StatustransitionFlow) {
            return $objFlow->getStepForStatus($objObject->getIntRecordStatus());
        }
        return null;
    }

    /**
     * Returns the status transition handler for this object or null if no handler was attached
     *
     * @param Model $objObject
     * @return StatustransitionFlow|null
     */
    public function getFlowForModel(Model $objObject)
    {
        $strClass = get_class($objObject);
        if (!isset($this->arrFlows[$strClass])) {
            $objFlow = StatustransitionFlow::getByModelClass($strClass);
            if ($objFlow instanceof StatustransitionFlow) {
                $this->arrFlows[$strClass] = $objFlow;
            } else {
                return null;
            }
        }
        return $this->arrFlows[$strClass];
    }
}
