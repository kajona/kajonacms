<?php
/*"******************************************************************************************************
*   (c) 2004-2006 by MulchProductions, www.mulchprod.de                                                 *
*   (c) 2007-2016 by Kajona, www.kajona.de                                                              *
*       Published under the GNU LGPL v2.1, see /system/licence_lgpl.txt                                 *
********************************************************************************************************/

namespace Kajona\Pages\Portal\Elements;

use Kajona\Pages\Portal\ElementPortal;
use Kajona\Pages\Portal\PortalElementInterface;
use Kajona\Pages\System\PagesPage;


/**
 * Portal-Part of the paragraph
 *
 * @author sidler@mulchprod.de
 * @targetTable element_paragraph.content_id
 */
class ElementParagraphPortal extends ElementPortal implements PortalElementInterface
{


    /**
     * Does a little "make-up" to the contents
     *
     * @return string
     */
    public function loadData()
    {

        $strReturn = "";

        $strTemplate = $this->arrElementData["paragraph_template"];
        //fallback
        if ($strTemplate == "") {
            $strTemplate = "paragraph.tpl";
        }




        if ($this->arrElementData["paragraph_image"] != "") {
            //remove the webpath (was added for paragraphs saved pre 3.3.0)
            $this->arrElementData["paragraph_image"] = str_replace("_webpath_", "", $this->arrElementData["paragraph_image"]);
        }

        if ($this->arrElementData["paragraph_link"] != "") {
            //internal page?
            if (PagesPage::getPageByName($this->arrElementData["paragraph_link"]) !== null) {
                $this->arrElementData["paragraph_link"] = getLinkPortalHref($this->arrElementData["paragraph_link"]);
            }
            else {
                $this->arrElementData["paragraph_link"] = getLinkPortalHref("", $this->arrElementData["paragraph_link"]);
            }
        }

        if ($this->arrElementData["paragraph_title"] != "") {
            $this->arrElementData["paragraph_title_tag"] = $this->objTemplate->fillTemplateFile($this->arrElementData, "/element_paragraph/".$strTemplate, "paragraph_title_tag");
        }


        //choose template section
        $strTemplateSection = "paragraph";
        if ($this->arrElementData["paragraph_image"] != "" && $this->arrElementData["paragraph_link"] != "") {
            $strTemplateSection = "paragraph_image_link";
        }
        elseif ($this->arrElementData["paragraph_image"] != "" && $this->arrElementData["paragraph_link"] == "") {
            $strTemplateSection = "paragraph_image";
        }
        elseif ($this->arrElementData["paragraph_image"] == "" && $this->arrElementData["paragraph_link"] != "") {
            $strTemplateSection = "paragraph_link";
        }

        $strReturn .= $this->objTemplate->fillTemplateFile($this->arrElementData, "/element_paragraph/".$strTemplate, $strTemplateSection);

        return $strReturn;
    }

}
