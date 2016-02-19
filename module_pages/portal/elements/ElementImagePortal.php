<?php
/*"******************************************************************************************************
*   (c) 2004-2006 by MulchProductions, www.mulchprod.de                                                 *
*   (c) 2007-2015 by Kajona, www.kajona.de                                                              *
*       Published under the GNU LGPL v2.1, see /system/licence_lgpl.txt                                 *
*-------------------------------------------------------------------------------------------------------*
*	$Id$                                      *
********************************************************************************************************/

namespace Kajona\Pages\Portal\Elements;

use Kajona\Pages\Portal\ElementPortal;
use Kajona\Pages\Portal\PortalElementInterface;
use Kajona\Pages\System\PagesPage;


/**
 * Portal-Class of the picture element
 *
 * @package module_pages
 * @author sidler@mulchprod.de
 *
 * @targetTable element_image.content_id
 */
class ElementImagePortal extends ElementPortal implements PortalElementInterface
{

    /**
     * Returns the ready image-htmlcode
     *
     * @return string
     */
    public function loadData()
    {
        $strReturn = "";


        $strTemplate = $this->arrElementData["image_template"];
        //fallback
        if ($strTemplate == "") {
            $strTemplate = "image.tpl";
        }

        //choose template section
        if ($this->arrElementData["image_link"] != "") {
            $strTemplateID = $this->objTemplate->readTemplate("/element_image/".$strTemplate, "image_link");
        }
        else {
            $strTemplateID = $this->objTemplate->readTemplate("/element_image/".$strTemplate, "image");
        }

        $this->arrElementData["image_src"] = $this->arrElementData["image_image"];

        //TODO: rename db columns as well and remove this two lines
        $this->arrElementData["image_width"] = $this->arrElementData["image_x"];
        $this->arrElementData["image_height"] = $this->arrElementData["image_y"];

        //Link?
        if ($this->arrElementData["image_link"] != "") {
            //internal page?
            if (PagesPage::getPageByName($this->arrElementData["image_link"]) !== null) {
                $this->arrElementData["link_href"] = getLinkPortalHref($this->arrElementData["image_link"], "");
            }
            else {
                $this->arrElementData["link_href"] = getLinkPortalHref("", $this->arrElementData["image_link"]);
            }
        }


        $strReturn .= $this->fillTemplate($this->arrElementData, $strTemplateID);

        return $strReturn;
    }

}
