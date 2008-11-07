<?php
/*"******************************************************************************************************
*   (c) 2004-2006 by MulchProductions, www.mulchprod.de                                                 *
*   (c) 2007-2008 by Kajona, www.kajona.de                                                              *
*       Published under the GNU LGPL v2.1, see /system/licence_lgpl.txt                                 *
*-------------------------------------------------------------------------------------------------------*
*	$Id$                                     *
********************************************************************************************************/

//base-class
require_once(_portalpath_."/class_elemente_portal.php");
//Interface
require_once(_portalpath_."/interface_portal_element.php");
include_once(_systempath_."/class_modul_pages_page.php");

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
		$arrModul["name"] 			= "element_absatz";
		$arrModul["author"] 		= "sidler@mulchprod.de";
		$arrModul["moduleId"] 		= _pages_elemente_modul_id_;
		$arrModul["table"]			= _dbprefix_."element_absatz";

		parent::__construct($arrModul, $objElementData);
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
			$strReturn .= "<h3>".$this->arrElementData["absatz_titel"] . "</h3>\n";
		//Inhalt?
		if($this->arrElementData["absatz_inhalt"] != "")
			$strReturn .= "<p>".$this->arrElementData["absatz_inhalt"] . "<br /><br />\n";
		//Bild?
		if($this->arrElementData["absatz_bild"] != "")
			$strReturn .= "<img src=\"".$this->arrElementData["absatz_bild"]."\" alt=\"".$this->arrElementData["absatz_bild"]."\" /><br /><br />\n";
		//Link?
		if($this->arrElementData["absatz_link"] != "") {
		    //internal page?
		    $objPage = class_modul_pages_page::getPageByName($this->arrElementData["absatz_link"]);
		    if($objPage->getStrName() != "")
			    $strReturn .= "<a href=\"".getLinkPortalRaw($this->arrElementData["absatz_link"], "")."\" target=\"_self\" >".$this->arrElementData["absatz_link"]."</a>\n";
			else
			    $strReturn .= "<a href=\"".getLinkPortalRaw("",$this->arrElementData["absatz_link"])."\" target=\"_self\" >".$this->arrElementData["absatz_link"]."</a>\n";
		}

		$strReturn .="</p>\n";
		$strReturn = "<div class=\"absatz\">".$strReturn."</div>";

		return $strReturn;
	}

}	 //class_element_absatz
?>