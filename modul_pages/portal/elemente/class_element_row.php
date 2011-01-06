<?php
/*"******************************************************************************************************
*   (c) 2004-2006 by MulchProductions, www.mulchprod.de                                                 *
*   (c) 2007-2011 by Kajona, www.kajona.de                                                              *
*       Published under the GNU LGPL v2.1, see /system/licence_lgpl.txt                                 *
*-------------------------------------------------------------------------------------------------------*
*	$Id$                                      *
********************************************************************************************************/

/**
 * Portal-Part of the row-element
 *
 * @package modul_pages
 */
class class_element_row extends class_element_portal implements  interface_portal_element {

	/**
	 * Constructor
	 *
	 * @param mixed $arrElementData
	 */
	public function __construct($objElementData) {
		$arrModul["name"] 			= "element_row";
		$arrModul["author"] 		= "sidler@mulchprod.de";
		$arrModul["moduleId"] 		= _pages_elemente_modul_id_;
		$arrModul["table"]			= _dbprefix_."element_paragraph";

		parent::__construct($arrModul, $objElementData);
	}


	/**
	 * Does a little "make-up" to the contents
	 *
	 * @return string
	 */
	public function loadData() {

		$strReturn = "";

        $strTemplate = $this->arrElementData["paragraph_template"];

        //fallback
        if($strTemplate == "")
            $strTemplate = "row.tpl";

        $strTemplateID = $this->objTemplate->readTemplate("/element_row/".$strTemplate, "row");

        $strReturn .= $this->fillTemplate($this->arrElementData, $strTemplateID);

		return $strReturn;
	}

}
?>