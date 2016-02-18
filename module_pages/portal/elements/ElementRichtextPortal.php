<?php
/*"******************************************************************************************************
*   (c) 2004-2006 by MulchProductions, www.mulchprod.de                                                 *
*   (c) 2007-2014 by Kajona, www.kajona.de                                                              *
*       Published under the GNU LGPL v2.1, see /system/licence_lgpl.txt                                 *
*-------------------------------------------------------------------------------------------------------*
*	$Id$                                     *
********************************************************************************************************/

namespace Kajona\Pages\Portal\Elements;

use Kajona\Pages\Portal\ElementPortal;
use Kajona\Pages\Portal\PortalElementInterface;
use Kajona\Pages\System\PagesPageelement;
use Kajona\System\System\TemplateMapper;


/**
 * Portal-Part of the richtext element
 *
 * @author jschroeter@kajona.de
 * @targetTable element_universal.content_id
 */
class ElementRichtextPortal extends ElementPortal implements PortalElementInterface
{

    /**
     * Does a little "make-up" to the contents
     *
     * @return string
     */
    public function loadData()
    {

        $strTemplate = $this->arrElementData["char1"];
        //fallback
        if ($strTemplate == "") {
            $strTemplate = "richtext.tpl";
        }

        $objPageElement = new PagesPageelement($this->getSystemid());
        $objAdmin = $objPageElement->getConcreteAdminInstance();
        $objAdmin->loadElementData();

        $objMapper = new TemplateMapper($objAdmin);
        return $objMapper->writeToTemplate("/element_richtext/".$strTemplate, "richtext");
    }

}
