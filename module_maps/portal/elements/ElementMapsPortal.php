<?php
/*"******************************************************************************************************
*   (c) 2004-2006 by MulchProductions, www.mulchprod.de                                                 *
*   (c) 2007-2016 by Kajona, www.kajona.de                                                              *
*       Published under the GNU LGPL v2.1, see /system/licence_lgpl.txt                                 *
********************************************************************************************************/

namespace Kajona\Maps\Portal\Elements;

use Kajona\Pages\Portal\ElementPortal;
use Kajona\Pages\Portal\PortalElementInterface;
use Kajona\System\System\Config;


/**
 * Portal-Part of the maps
 *
 * @author jschroeter@kajona.de
 * @targetTable element_universal.content_id
 */
class ElementMapsPortal extends ElementPortal implements PortalElementInterface
{


    /**
     * Does a little "make-up" to the contents
     *
     * @return string
     */
    public function loadData()
    {

        $strReturn = "";

        $strTemplate = $this->arrElementData["char3"];
        //fallback
        if ($strTemplate == "") {
            $strTemplate = "maps.tpl";
        }

        $floatLat = "0.0";
        $floatLng = "0.0";

        $arrLatLng = explode(',', $this->arrElementData["char2"]);
        if (count($arrLatLng) == 2) {
            $floatLat = $arrLatLng[0];
            $floatLng = $arrLatLng[1];
        }

        $this->arrElementData["address"] = $this->arrElementData["char1"];
        $this->arrElementData["lat"] = $floatLat;
        $this->arrElementData["lng"] = $floatLng;
        $this->arrElementData["infotext"] = str_replace(array("\r", "\r\n", "\n"), '', $this->arrElementData["text"]);
        $this->arrElementData["systemid"] = $this->getSystemid();
        $this->arrElementData["apikey"] = Config::getInstance("module_maps")->getConfig("google_maps_apikey");

        $strReturn .= $this->objTemplate->fillTemplateFile($this->arrElementData, "/module_maps/".$strTemplate, "map");

        return $strReturn;
    }

}
