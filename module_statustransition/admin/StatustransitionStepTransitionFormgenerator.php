<?php
/*"******************************************************************************************************
*   (c) 2004-2006 by MulchProductions, www.mulchprod.de                                                 *
*   (c) 2007-2016 by Kajona, www.kajona.de                                                              *
*       Published under the GNU LGPL v2.1, see /system/licence_lgpl.txt                                 *
*-------------------------------------------------------------------------------------------------------*
*	$Id$                                   *
********************************************************************************************************/

namespace Kajona\Statustransition\Admin;

use Kajona\Statustransition\System\StatustransitionFlowStep;
use Kajona\Statustransition\System\StatustransitionFlowStepTransition;
use Kajona\System\Admin\AdminFormgenerator;
use Kajona\System\Admin\Formentries\FormentryObjectlist;
use Kajona\System\System\Carrier;
use Kajona\System\System\Lang;
use Kajona\System\System\Link;
use Kajona\System\System\Objectfactory;

/**
 * Formgenerator for a statustransition flow entry
 *
 * @package module_statustransition
 * @author christoph.kappestein@gmail.com
 * @since 5.1
 */
class StatustransitionStepTransitionFormgenerator extends AdminFormgenerator
{
    /**
     * @inheritDoc
     */
    public function generateFieldsFromObject()
    {
        parent::generateFieldsFromObject();

        // target steps
        $objField = $this->getField("targetstep");
        $strSystemId = Carrier::getInstance()->getParam("systemid");

        $objStep = Objectfactory::getInstance()->getObject($strSystemId);
        if ($objStep instanceof StatustransitionFlowStepTransition) {
            $objStep = Objectfactory::getInstance()->getObject($objStep->getPrevId());
        }
        if ($objStep instanceof StatustransitionFlowStep) {
            $arrSteps = StatustransitionFlowStep::getObjectListFiltered(null, $objStep->getPrevId());
            $arrValues = [];
            foreach ($arrSteps as $objStep) {
                $arrValues[$objStep->getSystemid()] = $objStep->getStrName();
            }
            $objField->setArrKeyValues($arrValues);
        }
    }
}
