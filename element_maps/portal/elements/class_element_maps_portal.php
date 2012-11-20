<?php
/*"******************************************************************************************************
*   (c) 2004-2006 by MulchProductions, www.mulchprod.de                                                 *
*   (c) 2007-2012 by Kajona, www.kajona.de                                                              *
*       Published under the GNU LGPL v2.1, see /system/licence_lgpl.txt                                 *
*-------------------------------------------------------------------------------------------------------*
*	$Id$                                     *
********************************************************************************************************/

/**
 * Portal-Part of the maps
 *
 * @package element_maps
 * @author jschroeter@kajona.de
 */
class class_element_maps_portal extends class_element_portal implements interface_portal_element {

    /**
     * Constructor
     *
     * @param class_module_pages_pageelement|mixed $objElementData
     */
	public function __construct($objElementData) {
        parent::__construct($objElementData);
        $this->setArrModuleEntry("table", _dbprefix_."element_universal");
	}


	/**
	 * Does a little "make-up" to the contents
	 *
	 * @return string
	 */
	public function loadData() {

		$strReturn = "";

        $strTemplate = $this->arrElementData["char3"];
        //fallback
        if($strTemplate == "")
            $strTemplate = "maps.tpl";

        $strTemplateID = $this->objTemplate->readTemplate("/element_maps/".$strTemplate, "map");

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
        
        $strReturn .= $this->fillTemplate($this->arrElementData, $strTemplateID);

        return $strReturn;
	}

}
