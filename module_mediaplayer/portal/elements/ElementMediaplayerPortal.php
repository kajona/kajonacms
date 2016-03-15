<?php
/*"******************************************************************************************************
*   (c) 2004-2006 by MulchProductions, www.mulchprod.de                                                 *
*   (c) 2007-2016 by Kajona, www.kajona.de                                                              *
*       Published under the GNU LGPL v2.1, see /system/licence_lgpl.txt                                 *
********************************************************************************************************/

namespace Kajona\Mediaplayer\Portal\Elements;

use Kajona\Pages\Portal\ElementPortal;
use Kajona\Pages\Portal\PortalElementInterface;


/**
 * Loads the mediaplayer and prepares it for output
 *
 * @author sidler@mulchprod.de
 * @targetTable element_universal.content_id
 */
class ElementMediaplayerPortal extends ElementPortal implements PortalElementInterface
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
        $arrTemplate["preview"] = $this->arrElementData["char2"];
        $arrTemplate["width"] = $this->arrElementData["int1"];
        $arrTemplate["height"] = $this->arrElementData["int2"];

        $strReturn = $this->objTemplate->fillTemplateFile($arrTemplate, "/module_mediaplayer/".$this->arrElementData["char3"], "mediaplayer");

        return $strReturn;
    }

}
