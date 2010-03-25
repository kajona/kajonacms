<?php
/*"******************************************************************************************************
*   (c) 2004-2006 by MulchProductions, www.mulchprod.de                                                 *
*   (c) 2007-2010 by Kajona, www.kajona.de                                                              *
*       Published under the GNU LGPL v2.1, see /system/licence_lgpl.txt                                 *
*-------------------------------------------------------------------------------------------------------*
*	$Id$                         *
********************************************************************************************************/

/**
 * Loads the last-modified date of the current page and prepares it for output
 *
 * @package modul_pages
 */
class class_element_imagelightbox extends class_element_portal implements interface_portal_element {

	/**
	 * Constructor
	 *
	 * @param mixed $arrElementData
	 */
	public function __construct($objElementData) {
        $arrModule = array();
		$arrModule["name"] 			= "element_imagelightbox";
		$arrModule["author"] 		= "sidler@mulchprod.de";
		$arrModule["moduleId"] 		= _pages_elemente_modul_id_;
		$arrModule["table"]		    = _dbprefix_."element_universal";
		$arrModule["modul"]		    = "elemente";

		parent::__construct($arrModule, $objElementData);
	}

    /**
     * Loads the feed and displays it
     *
     * @return string the prepared html-output
     */
	public function loadData() {
		$strReturn = "";

		$strImage = $this->arrElementData["char1"];

        $arrTemplate = array();
        $arrTemplate["title"] = $this->arrElementData["char2"];
        $arrTemplate["image"] = $this->arrElementData["char1"];
        $arrTemplate["description"] = $this->arrElementData["text"];

        //fallback for old elements
        if($this->arrElementData["char3"] == "")
            $this->arrElementData["char3"] = "imagelightbox.tpl";

		

        $strTemplateID = $this->objTemplate->readTemplate("/element_imagelightbox/".$this->arrElementData["char3"], "imagelightbox");
        $strReturn .= $this->objTemplate->fillTemplate($arrTemplate, $strTemplateID);

		return $strReturn;
	}


}
?>