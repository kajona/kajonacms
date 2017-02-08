<?php
/*"******************************************************************************************************
*   (c) 2004-2006 by MulchProductions, www.mulchprod.de                                                 *
*   (c) 2007-2016 by Kajona, www.kajona.de                                                              *
*       Published under the GNU LGPL v2.1, see /system/licence_lgpl.txt                                 *
*-------------------------------------------------------------------------------------------------------*
*	$Id$                                   *
********************************************************************************************************/

namespace Kajona\Flow\Admin;

use Kajona\Flow\System\FlowStatus;
use Kajona\Flow\System\FlowTransition;
use Kajona\System\Admin\AdminFormgenerator;
use Kajona\System\System\Carrier;
use Kajona\System\System\Objectfactory;

/**
 * Formgenerator for a flow entry
 *
 * @package module_flow
 * @author christoph.kappestein@gmail.com
 * @since 5.1
 */
class FlowTransitionFormgenerator extends AdminFormgenerator
{
    /**
     * @inheritDoc
     */
    public function generateFieldsFromObject()
    {
        parent::generateFieldsFromObject();

        // target steps
        $objField = $this->getField("targetstatus");
        $strSystemId = Carrier::getInstance()->getParam("systemid");

        $objStep = Objectfactory::getInstance()->getObject($strSystemId);
        if ($objStep instanceof FlowTransition) {
            $objStep = Objectfactory::getInstance()->getObject($objStep->getPrevId());
        }
        if ($objStep instanceof FlowStatus) {
            $arrSteps = FlowStatus::getObjectListFiltered(null, $objStep->getPrevId());
            $arrValues = [];
            foreach ($arrSteps as $objStep) {
                $arrValues[$objStep->getSystemid()] = $objStep->getStrName();
            }
            $objField->setArrKeyValues($arrValues);
        }
    }
}
