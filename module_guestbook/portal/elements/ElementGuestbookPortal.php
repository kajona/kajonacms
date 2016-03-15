<?php
/*"******************************************************************************************************
*   (c) 2004-2006 by MulchProductions, www.mulchprod.de                                                 *
*   (c) 2007-2016 by Kajona, www.kajona.de                                                              *
*       Published under the GNU LGPL v2.1, see /system/licence_lgpl.txt                                 *
********************************************************************************************************/

namespace Kajona\Guestbook\Portal\Elements;

use Kajona\Pages\Portal\ElementPortal;
use Kajona\Pages\Portal\PortalElementInterface;
use Kajona\System\System\SystemModule;


/**
 * Portal-part of the guestbook-element
 *
 * @package module_guestbook
 * @author sidler@mulchprod.de
 * @targetTable element_guestbook.content_id
 */
class ElementGuestbookPortal extends ElementPortal implements PortalElementInterface
{

    /**
     * Contructor
     *
     * @param $objElementData
     */
    public function __construct($objElementData)
    {
        parent::__construct($objElementData);

        if ($this->getParam("action") == "saveGuestbook") {
            $this->setStrCacheAddon(generateSystemid());
        }
    }

    /**
     * Loads the guestbook-class and passes control
     *
     * @return string
     */
    public function loadData()
    {
        $strReturn = "";

        $objGBModule = SystemModule::getModuleByName("guestbook");
        if ($objGBModule != null) {
            $objGuestbook = $objGBModule->getPortalInstanceOfConcreteModule($this->arrElementData);
            $strReturn = $objGuestbook->action();
        }

        return $strReturn;
    }

}
