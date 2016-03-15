<?php
/*"******************************************************************************************************
*   (c) 2004-2006 by MulchProductions, www.mulchprod.de                                                 *
*   (c) 2007-2016 by Kajona, www.kajona.de                                                              *
*       Published under the GNU LGPL v2.1, see /system/licence_lgpl.txt                                 *
********************************************************************************************************/

namespace Kajona\Tagto\Portal\Elements;

use Kajona\Pages\Portal\ElementPortal;
use Kajona\Pages\Portal\PortalElementInterface;


/**
 * Loads the last-modified date of the current page and prepares it for output
 *
 * @author sidler@mulchprod.de
 * @targetTable element_universal.content_id
 */
class ElementTagtoPortal extends ElementPortal implements PortalElementInterface
{


    /**
     * Looks up the last modified-date of the current page
     *
     * @return string the prepared html-output
     */
    public function loadData()
    {
        //actions or systemids passed? pagename?
        $strSystemid = $this->getParam("systemid");
        $strActions = $this->getParam("action");
        $strPageName = $this->getPagename();

        //load the template
        $strLink = getLinkPortalHref($strPageName, "", $strActions, "", $strSystemid);
        $strReturn = $this->objTemplate->fillTemplateFile(array("pageurl" => $strLink), "/module_tagto/".$this->arrElementData["char1"], "tagtos");

        return $strReturn;
    }

}
