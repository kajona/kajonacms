<?php
/*"******************************************************************************************************
*   (c) 2004-2006 by MulchProductions, www.mulchprod.de                                                 *
*   (c) 2007-2016 by Kajona, www.kajona.de                                                              *
*       Published under the GNU LGPL v2.1, see /system/licence_lgpl.txt                                 *
********************************************************************************************************/

namespace Kajona\Navigation\Portal\Elements;

use Kajona\Pages\Portal\ElementPortal;
use Kajona\Pages\Portal\PortalElementInterface;
use Kajona\System\System\SystemModule;

/**
 * Portal-class of the navigation element, loads the navigation-portal class
 *
 * @package module_navigation
 * @author sidler@mulchprod.de
 *
 * @targetTable element_navigation.content_id
 */
class ElementNavigationPortal extends ElementPortal implements PortalElementInterface {

    /**
     * Loads the navigation-class and passes control
     *
     * @return string
     */
    public function loadData() {
        $strReturn = "";

        $objNaviModule = SystemModule::getModuleByName("navigation");
        if($objNaviModule != null) {
            $objNavigation = $objNaviModule->getPortalInstanceOfConcreteModule($this->arrElementData);
            $strReturn = $objNavigation->action();
        }

        return $strReturn;
    }

    /**
     * no anchor here, plz
     *
     * @return string
     */
    protected function getAnchorTag() {
        return "";
    }

}
