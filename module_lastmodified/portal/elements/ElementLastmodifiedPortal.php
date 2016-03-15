<?php
/*"******************************************************************************************************
*   (c) 2004-2006 by MulchProductions, www.mulchprod.de                                                 *
*   (c) 2007-2016 by Kajona, www.kajona.de                                                              *
*       Published under the GNU LGPL v2.1, see /system/licence_lgpl.txt                                 *
********************************************************************************************************/

namespace Kajona\Lastmodified\Portal\Elements;

use Kajona\Pages\Portal\ElementPortal;
use Kajona\Pages\Portal\PortalElementInterface;
use Kajona\Pages\System\PagesPage;


/**
 * Loads the last-modified date of the current page and prepares it for output
 *
 * @author sidler@mulchprod.de
 *
 */
class ElementLastmodifiedPortal extends ElementPortal implements PortalElementInterface {

    /**
     * Looks up the last modified-date of the current page
     *
     * @return string the prepared html-output
     */
    public function loadData() {
        $strReturn = "";
        //load the current page
        $objPage = PagesPage::getPageByName($this->getPagename());
        if($objPage != null)
            $strReturn .= $this->getLang("lastmodified").timeToString($objPage->getIntLmTime());
        return $strReturn;
    }

}
