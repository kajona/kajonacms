<?php
/*"******************************************************************************************************
*   (c) 2004-2006 by MulchProductions, www.mulchprod.de                                                 *
*   (c) 2007-2012 by Kajona, www.kajona.de                                                              *
*       Published under the GNU LGPL v2.1, see /system/licence_lgpl.txt                                 *
*-------------------------------------------------------------------------------------------------------*
*	$Id$                         *
********************************************************************************************************/

/**
 * Loads the mediaplayer and prepares it for output
 *
 * @package element_mediaplayer
 * @author sidler@mulchprod.de
 */
class class_element_mediaplayer_portal extends class_element_portal implements interface_portal_element {

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
     * Loads the settings and generates the player object
     *
     * @return string the prepared html-output
     */
	public function loadData() {

        $arrTemplate = array();
        $arrTemplate["systemid"] = $this->getSystemid();
        $arrTemplate["file"] = $this->arrElementData["char1"];
        $arrTemplate["preview"] = $this->arrElementData["char2"];
        $arrTemplate["width"] = $this->arrElementData["int1"];
        $arrTemplate["height"] = $this->arrElementData["int2"];

		$strTemplateID = $this->objTemplate->readTemplate("/element_mediaplayer/".$this->arrElementData["char3"], "mediaplayer");
        $strReturn = $this->fillTemplate($arrTemplate, $strTemplateID);

		return $strReturn;
	}

}
