<?php
/*"******************************************************************************************************
*   (c) 2004-2006 by MulchProductions, www.mulchprod.de                                                 *
*   (c) 2007-2015 by Kajona, www.kajona.de                                                              *
*       Published under the GNU LGPL v2.1, see /system/licence_lgpl.txt                                 *
********************************************************************************************************/

namespace Kajona\Maps\Portal\Elements;

use Kajona\Pages\Portal\ElementPortal;
use Kajona\Pages\Portal\PortalElementInterface;


/**
 * Portal-Part of the maps
 *
 * @author jschroeter@kajona.de
 * @targetTable element_universal.content_id
 */
class ElementMapsPortal extends ElementPortal implements PortalElementInterface {


    /**
     * Does a little "make-up" to the contents
     *
     * @return string
     */
    public function loadData() {

        $strReturn = "";

        $strTemplate = $this->arrElementData["char3"];
        //fallback
        if($strTemplate == "") {
            $strTemplate = "maps.tpl";
        }

        $strTemplateID = $this->objTemplate->readTemplate("/module_maps/".$strTemplate, "map");

        $floatLat = "0.0";
        $floatLng = "0.0";

        $arrLatLng = explode(',', $this->arrElementData["char2"]);
        if(count($arrLatLng) == 2) {
            $floatLat = $arrLatLng[0];
            $floatLng = $arrLatLng[1];
        }

        $this->arrElementData["address"] = $this->arrElementData["char1"];
        $this->arrElementData["lat"] = $floatLat;
        $this->arrElementData["lng"] = $floatLng;
        $this->arrElementData["infotext"] = str_replace(array("\r", "\r\n", "\n"), '', $this->arrElementData["text"]);
        $this->arrElementData["systemid"] = $this->getSystemid();

        $strReturn .= $this->fillTemplate($this->arrElementData, $strTemplateID);

        return $strReturn;
    }

}
