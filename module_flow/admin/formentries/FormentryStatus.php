<?php
/*"******************************************************************************************************
*   (c) 2007-2016 by Kajona, www.kajona.de                                                              *
*       Published under the GNU LGPL v2.1, see /system/licence_lgpl.txt                                 *
********************************************************************************************************/

namespace Kajona\Flow\Admin\Formentries;

use Kajona\Flow\System\FlowManager;
use Kajona\Flow\System\ServiceProvider;
use Kajona\System\Admin\Formentries\FormentryDropdown;
use Kajona\System\Admin\Formentries\FormentryToggleButtonbar;
use Kajona\System\System\Carrier;
use Kajona\System\System\Reflection;

/**
 * @author christoph.kappestein@gmail.de
 * @since 5.2
 * @package module_flow
 */
class FormentryStatus extends FormentryToggleButtonbar
{
    /**
     * This annotation can be used to specify a concrete model class in case the formentry is used in a filter.
     * Otherwise the class of the source object is used
     */
    const STR_MODEL_ANNOTATION = "@fieldModelClass";

    /**
     * Overwritten in order to load key-value pairs declared by annotations
     */
    protected function updateValue()
    {
        parent::updateValue();

        $objSourceObject = $this->getObjSourceObject();
        if ($objSourceObject !== null) {
            /** @var FlowManager $objFlowManager */
            $objFlowManager = Carrier::getInstance()->getContainer()->offsetGet(ServiceProvider::STR_MANAGER);

            // try to find the matching source property
            $strSourceProperty = $this->getCurrentProperty(self::STR_MODEL_ANNOTATION);
            if ($strSourceProperty == null) {
                return;
            }

            // get model class
            $objReflection = new Reflection($objSourceObject);
            $strModelClass = $objReflection->getAnnotationValueForProperty($strSourceProperty, self::STR_MODEL_ANNOTATION);
            if (empty($strModelClass)) {
                $strModelClass = get_class($objSourceObject);
            }

            $this->setArrKeyValues($objFlowManager->getPossibleStatusForClass($strModelClass));
        }
    }
}
