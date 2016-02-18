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


/**
 * Portal-Part of the row-element
 *
 * @author sidler@mulchprod.de
 * @targetTable element_paragraph.content_id
 */
class ElementRowPortal extends ElementPortal implements PortalElementInterface
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
            $strTemplate = "row.tpl";
        }

        $strTemplateID = $this->objTemplate->readTemplate("/element_row/".$strTemplate, "row");

        $strReturn .= $this->fillTemplate($this->arrElementData, $strTemplateID);

        return $strReturn;
    }

}
