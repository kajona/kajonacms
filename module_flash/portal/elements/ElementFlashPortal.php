<?php
/*"******************************************************************************************************
*   (c) 2004-2006 by MulchProductions, www.mulchprod.de                                                 *
*   (c) 2007-2016 by Kajona, www.kajona.de                                                              *
*       Published under the GNU LGPL v2.1, see /system/licence_lgpl.txt                                 *
********************************************************************************************************/

namespace Kajona\Flash\Portal\Elements;

use Kajona\Pages\Portal\ElementPortal;
use Kajona\Pages\Portal\PortalElementInterface;


/**
 * Loads the flash element and prepares it for output
 *
 * @author jschroeter@kajona.de
 * @targetTable element_universal.content_id
 */
class ElementFlashPortal extends ElementPortal implements PortalElementInterface
{


    /**
     * Loads the settings and generates the player object
     *
     * @return string the prepared html-output
     */
    public function loadData()
    {

        $arrTemplate = array();
        $arrTemplate["systemid"] = $this->getSystemid();
        $arrTemplate["file"] = $this->arrElementData["char1"];
        $arrTemplate["width"] = $this->arrElementData["int1"];
        $arrTemplate["height"] = $this->arrElementData["int2"];

        $strReturn = $this->objTemplate->fillTemplateFile($arrTemplate, "/module_flash/".$this->arrElementData["char2"], "flash");

        return $strReturn;
    }

}
