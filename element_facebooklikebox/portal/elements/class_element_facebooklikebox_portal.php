<?php
/*"******************************************************************************************************
*   (c) 2004-2006 by MulchProductions, www.mulchprod.de                                                 *
*   (c) 2007-2012 by Kajona, www.kajona.de                                                              *
*       Published under the GNU LGPL v2.1, see /system/licence_lgpl.txt                                 *
*-------------------------------------------------------------------------------------------------------*
*	$Id: class_element_facebooklikebox.php 4042 2011-07-25 17:37:44Z sidler $                                     *
********************************************************************************************************/

/**
 * Loads the last-modified date of the current page and prepares it for output
 *
 * @package element_facebooklikebox
 * @author jschroeter@kajona.de
 */
class class_element_facebooklikebox_portal extends class_element_portal implements interface_portal_element {

    /**
     * Constructor
     *
     * @param class_module_pages_pageelement $objElementData
     */
	public function __construct($objElementData) {
		parent::__construct($objElementData);
        $this->setArrModuleEntry("table", _dbprefix_."element_universal");
	}

    /**
     * Renders the template
     *
     * @return string the prepared html-output
     */
	public function loadData() {
        $strLanguage = $this->getStrPortalLanguage();
        //load the template
        $strTemplateID = $this->objTemplate->readTemplate("/element_facebooklikebox/".$this->arrElementData["char1"], "facebooklikebox");
        $strReturn = $this->fillTemplate(array("portallanguage" => $strLanguage), $strTemplateID);
		return $strReturn;
	}

}
