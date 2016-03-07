<?php
/*"******************************************************************************************************
*   (c) 2004-2006 by MulchProductions, www.mulchprod.de                                                 *
*   (c) 2007-2015 by Kajona, www.kajona.de                                                              *
*       Published under the GNU LGPL v2.1, see /system/licence_lgpl.txt                                 *
*-------------------------------------------------------------------------------------------------------*
*	$Id$						               	*
********************************************************************************************************/

namespace Kajona\Votings\Portal\Elements;

use Kajona\Pages\Portal\ElementPortal;
use Kajona\Pages\Portal\PortalElementInterface;
use Kajona\System\System\SystemModule;

/**
 * Portal-part of the votings-element
 *
 * @package module_votings
 * @author sidler@mulchprod.de
 * @targetTable element_universal.content_id
 */
class ElementVotingsPortal extends ElementPortal implements PortalElementInterface {


    /**
     * Loads the votings-class and passes control
     *
     * @return string
     */
    public function loadData() {
        $strReturn = "";
        //Load the data
        $objvotingsModule = SystemModule::getModuleByName("votings");
        if($objvotingsModule != null) {
            $objVotings = $objvotingsModule->getPortalInstanceOfConcreteModule($this->arrElementData);
            $strReturn = $objVotings->action();
        }
        return $strReturn;
    }

}
