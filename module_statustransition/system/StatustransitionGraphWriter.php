<?php
/*"******************************************************************************************************
*   (c) 2004-2006 by MulchProductions, www.mulchprod.de                                                 *
*   (c) 2007-2016 by Kajona, www.kajona.de                                                              *
*       Published under the GNU LGPL v2.1, see /system/licence_lgpl.txt                                 *
********************************************************************************************************/

namespace Kajona\Statustransition\System;

/**
 * StatustransitionGraphDrawer
 *
 * @author christoph.kappestein@artemeon.de
 */
class StatustransitionGraphWriter
{
    /**
     * Generates a mermaid graph definition of the flow object
     *
     * @param StatustransitionFlow $objFlow
     * @return string
     */
    public static function write(StatustransitionFlow $objFlow)
    {
        $arrSteps = $objFlow->getSteps();
        $arrList = array("graph TD;");

        foreach ($arrSteps as $objStep) {
            /** @var StatustransitionFlowStep $objStep */
            $arrTransitions = $objStep->getArrTransitions();
            foreach ($arrTransitions as $objTransition) {
                $arrList[] = $objStep->getStrSystemid() . "[" . $objStep->getStrName() . "]-->" . $objTransition->getSystemid() . "[" . $objTransition->getStrName() . "];";
            }
        }

        return implode("\n", $arrList);
    }
}
