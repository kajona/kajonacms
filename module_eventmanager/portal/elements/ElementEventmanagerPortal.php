<?php
/*"******************************************************************************************************
*   (c) 2004-2006 by MulchProductions, www.mulchprod.de                                                 *
*   (c) 2007-2015 by Kajona, www.kajona.de                                                              *
*       Published under the GNU LGPL v2.1, see /system/licence_lgpl.txt                                 *
*-------------------------------------------------------------------------------------------------------*
*	$Id$						               	*
********************************************************************************************************/

namespace Kajona\Eventmanager\Portal\Elements;
use Kajona\Pages\Portal\ElementPortal;
use Kajona\Pages\Portal\PortalElementInterface;
use Kajona\System\System\SystemModule;


/**
 * Portal-part of the eventmanager-element
 *
 * @package module_eventmanager
 * @author sidler@mulchprod.de
 *
 * @targetTable element_universal.content_id
 */
class ElementEventmanagerPortal extends ElementPortal implements PortalElementInterface {


    /**
     * Loads the eventmanager-class and passes control
     *
     * @return string
     */
    public function loadData() {
        $strReturn = "";
        //Load the data
        $objEventmanagerModule = SystemModule::getModuleByName("eventmanager");
        if($objEventmanagerModule != null) {
            $objEventmanager = $objEventmanagerModule->getPortalInstanceOfConcreteModule($this->arrElementData);
            $strReturn = $objEventmanager->action();
        }
        return $strReturn;
    }

}
