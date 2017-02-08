<?php
/*"******************************************************************************************************
*   (c) 2004-2006 by MulchProductions, www.mulchprod.de                                                 *
*   (c) 2007-2016 by Kajona, www.kajona.de                                                              *
*       Published under the GNU LGPL v2.1, see /system/licence_lgpl.txt                                 *
********************************************************************************************************/

namespace Kajona\Flow\System;

/**
 * FlowGraphWriter
 *
 * @author christoph.kappestein@artemeon.de
 */
class FlowGraphWriter
{
    /**
     * Generates a mermaid graph definition of the flow object
     *
     * @param FlowConfig $objFlow
     * @return string
     */
    public static function write(FlowConfig $objFlow)
    {
        $arrStatus = $objFlow->getArrStatus();
        $arrList = array("graph TD;");

        foreach ($arrStatus as $objStatus) {
            /** @var FlowStatus $objStatus */
            $arrTransitions = $objStatus->getArrTransitions();
            foreach ($arrTransitions as $objTransition) {
                /** @var $objTransition FlowTransition */
                $arrActions = $objTransition->getArrActions();
                $arrActionNames = [];
                foreach ($arrActions as $objAction) {
                    $arrActionNames[] = $objAction->getTitle();
                }
                $arrConditions = $objTransition->getArrConditions();
                $objTargetStatus = $objTransition->getTargetStatus();
                if ($objTargetStatus instanceof FlowStatus) {
                    $arrList[] = $objStatus->getStrSystemid() . "[" . $objStatus->getStrName() . "]-->" . $objTargetStatus->getSystemid() . "[" . $objTargetStatus->getStrName() . "];";
                }
            }
        }

        return implode("\n", $arrList);
    }
}
