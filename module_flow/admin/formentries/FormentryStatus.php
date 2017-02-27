<?php
/*"******************************************************************************************************
*   (c) 2007-2016 by Kajona, www.kajona.de                                                              *
*       Published under the GNU LGPL v2.1, see /system/licence_lgpl.txt                                 *
********************************************************************************************************/

namespace Kajona\Flow\Admin\Formentries;

use Kajona\Flow\System\FlowManager;
use Kajona\Flow\System\ServiceProvider;
use Kajona\System\Admin\Formentries\FormentryDropdown;
use Kajona\System\System\Carrier;

/**
 * @author christoph.kappestein@gmail.de
 * @since 5.2
 * @package module_flow
 */
class FormentryStatus extends FormentryDropdown
{
    /**
     * @var FlowManager
     */
    protected $objFlowManager;

    public function __construct($strFormName, $strSourceProperty, $objSourceObject = null)
    {
        parent::__construct($strFormName, $strSourceProperty, $objSourceObject);

        $this->objFlowManager = Carrier::getInstance()->getContainer()->offsetGet(ServiceProvider::STR_MANAGER);
    }

    /**
     * Overwritten in order to load key-value pairs declared by annotations
     */
    protected function updateValue()
    {
        parent::updateValue();

        if ($this->getObjSourceObject() != null && $this->getStrSourceProperty() != "") {
            $this->setArrKeyValues($this->objFlowManager->getPossibleStatusForModel($this->getObjSourceObject()));
        }
    }
}
