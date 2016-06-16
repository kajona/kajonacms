<?php
/*"******************************************************************************************************
*   (c) 2004-2006 by MulchProductions, www.mulchprod.de                                                 *
*   (c) 2007-2016 by Kajona, www.kajona.de                                                              *
*       Published under the GNU LGPL v2.1, see /system/licence_lgpl.txt                                 *
*-------------------------------------------------------------------------------------------------------*
*	$Id$	                                    *
********************************************************************************************************/

namespace Kajona\Search\Portal\Elements;

use Kajona\Pages\Portal\ElementPortal;
use Kajona\Pages\Portal\PortalElementInterface;
use Kajona\Pages\System\PagesPageelement;
use Kajona\System\System\SystemModule;


/**
 * Portal element of the search-module
 *
 * @package module_search
 * @author sidler@mulchprod.de
 *
 * @targetTable element_search.content_id
 */
class ElementSearchPortal extends ElementPortal implements PortalElementInterface
{


    /**
     * @param PagesPageelement|mixed $objElementData
     */
    public function __construct($objElementData)
    {
        parent::__construct($objElementData);
        $this->setStrCacheAddon(getPost("searchterm").getGet("searchterm"));
    }

    /**
     * Loads the search-class and passes control
     *
     * @return string
     */
    public function loadData()
    {
        $strReturn = "";
        //Load the data
        $objSearchModule = SystemModule::getModuleByName("search");
        if ($objSearchModule != null) {
            $objSearch = $objSearchModule->getPortalInstanceOfConcreteModule($this->arrElementData);
            $strReturn = $objSearch->action();
        }
        return $strReturn;
    }

}
