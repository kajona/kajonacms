<?php
/*"******************************************************************************************************
*   (c) 2004-2006 by MulchProductions, www.mulchprod.de                                                 *
*   (c) 2007-2010 by Kajona, www.kajona.de                                                              *
*       Published under the GNU LGPL v2.1, see /system/licence_lgpl.txt                                 *
*-------------------------------------------------------------------------------------------------------*
*	$Id$                                     *
********************************************************************************************************/

/**
 * Portal-Part of the paragraph
 *
 * @package modul_pages
 */
class class_element_absatz extends class_element_portal implements  interface_portal_element {

	/**
	 * Constructor
	 *
	 * @param mixed $arrElementData
	 */
	public function __construct($objElementData) {
        $arrModule = array();
		$arrModule["name"] 			= "element_absatz";
		$arrModule["author"] 		= "sidler@mulchprod.de";
		$arrModule["moduleId"] 		= _pages_elemente_modul_id_;
		$arrModule["table"]			= _dbprefix_."element_absatz";

		parent::__construct($arrModule, $objElementData);
	}


	/**
	 * Does a little "make-up" to the contents
	 *
	 * @return string
	 */
	public function loadData() {

		$strReturn = "";
		//Titel gegeben?
		if($this->arrElementData["absatz_titel"] != "")
			$strReturn .= "<h2>".$this->arrElementData["absatz_titel"] . "</h2>\n";
		//Inhalt?
		if($this->arrElementData["absatz_inhalt"] != "")
			$strReturn .= "<p>".$this->arrElementData["absatz_inhalt"] . "<p>\n";
		//Bild?
		if($this->arrElementData["absatz_bild"] != "")
			$strReturn .= "<img src=\"".$this->arrElementData["absatz_bild"]."\" alt=\"".$this->arrElementData["absatz_bild"]."\" /><br /><br />\n";
		//Link?
		if($this->arrElementData["absatz_link"] != "") {
		    //internal page?
		    $objPage = class_modul_pages_page::getPageByName($this->arrElementData["absatz_link"]);
		    if($objPage->getStrName() != "")
			    $strReturn .= "<a href=\"".getLinkPortalHref($this->arrElementData["absatz_link"], "")."\" target=\"_self\" >".$this->arrElementData["absatz_link"]."</a>\n";
			else
			    $strReturn .= "<a href=\"".getLinkPortalHref("",$this->arrElementData["absatz_link"])."\" target=\"_self\" >".$this->arrElementData["absatz_link"]."</a>\n";
		}


		return $strReturn;
	}

}	 //class_element_absatz
?>