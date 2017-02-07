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
use Kajona\Statustransition\System\StatustransitionFlowStepTransitionAction;
use Kajona\Statustransition\System\StatustransitionFlowStepTransitionCondition;
use Kajona\System\Admin\AdminFormgenerator;
use Kajona\System\Admin\Formentries\FormentryHidden;
use Kajona\System\Admin\Formentries\FormentryObjectlist;
use Kajona\System\System\Carrier;
use Kajona\System\System\Lang;
use Kajona\System\System\Link;
use Kajona\System\System\Objectfactory;

/**
 * StatustransitionStepTransitionFormgeneratorTrait
 *
 * @package module_statustransition
 * @author christoph.kappestein@gmail.com
 * @since 5.1
 */
trait StatustransitionStepTransitionFormgeneratorTrait
{
    /**
     * @inheritDoc
     */
    public function generateFieldsFromObject()
    {
        parent::generateFieldsFromObject();

        // add dynamic action fields
        $objSource = $this->getObjSourceobject();
        $arrParameters = null;
        if ($this->isValidSourceObject($objSource)) {
            $strClass = $objSource->getStrRecordClass();
            $arrParameters = $objSource->getArrParameters();

            if (!empty($arrParameters)) {
                foreach ($arrParameters as $strKey => $strValue) {
                    $strVal = Carrier::getInstance()->getParam($strKey);
                    if (empty($strVal)) {
                        Carrier::getInstance()->setParam($strKey, $strValue);
                    }
                }
            }
        } else {
            $strClass = Carrier::getInstance()->getParam("class");
        }

        if (class_exists($strClass)) {
            $this->addField(new FormentryHidden("", "class"))
                ->setStrValue($strClass);

            $objType = new $strClass();
            if ($this->isValidSourceObject($objType)) {
                $objType->configureForm($this);
            }
        }
    }

    public function updateSourceObject()
    {
        parent::updateSourceObject();

        $objSource = $this->getObjSourceobject();
        if ($this->isValidSourceObject($objSource)) {
            $arrParams = [];
            $arrFields = $this->getArrFields();
            foreach ($arrFields as $strName => $objField) {
                $arrParams[$strName] = $objField->getStrValue();
            }
            unset($arrParams["class"]);

            $objSource->setStrParams(json_encode((object) $arrParams));
        }
    }
}
