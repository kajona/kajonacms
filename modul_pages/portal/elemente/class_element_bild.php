<?php
/*"******************************************************************************************************
*   (c) 2004-2006 by MulchProductions, www.mulchprod.de                                                 *
*   (c) 2007-2008 by Kajona, www.kajona.de                                                              *
*       Published under the GNU LGPL v2.1, see /system/licence_lgpl.txt                                 *
*-------------------------------------------------------------------------------------------------------*
* 																										*
* 	class_element_bild.php																				*
* 	Portal-class of the picture element																	*
*-------------------------------------------------------------------------------------------------------*
*	$Id$                                       *
********************************************************************************************************/

//Base Class
require_once(_portalpath_."/class_elemente_portal.php");
//Interface
require_once(_portalpath_."/interface_portal_element.php");
include_once(_systempath_."/class_modul_pages_page.php");

/**
 * Portal-Class of the picture element
 *
 * @package modul_pages
 */
class class_element_bild extends class_element_portal implements interface_portal_element  {

	/**
	 * Constructor
	 *
	 * @param mixed $arrElementData
	 */
	public function __construct($objElementData) {
		$arrModul["name"] 			= "element_bild";
		$arrModul["author"] 		= "sidler@mulchprod.de";
		$arrModul["moduleId"] 		= _pages_elemente_modul_id_;
		$arrModul["table"]			= _dbprefix_."element_bild";

		parent::__construct($arrModul, $objElementData);
	}


	/**
	 * Returns the ready image-htmlcode
	 *
	 * @return string
	 */
	public function loadData() {
		$strReturn = "";
		$strTitle = "";
		$strImage = "";
		$strReturn .= "<p>\n";
		//Titel gegeben?
		if($this->arrElementData["bild_titel"] != "")
			$strTitle .= $this->arrElementData["bild_titel"] ;
		//Bild?
		if($this->arrElementData["bild_bild"] != "")
			$strImage .= "<img src=\""._webpath_.$this->arrElementData["bild_bild"]."\" alt=\"".$strTitle."\" />";

		//Link?
		if($this->arrElementData["bild_link"] != "") {
		    //internal page?
		    $objPage = class_modul_pages_page::getPageByName($this->arrElementData["bild_link"]);
		    if($objPage->getStrName() != "")
			    $strReturn .= "<a href=\"".getLinkPortalRaw($this->arrElementData["bild_link"], "")."\" target=\"_self\" >".$strImage.$strTitle."</a>\n";
			else
			    $strReturn .= "<a href=\"".getLinkPortalRaw("",$this->arrElementData["bild_link"])."\" target=\"_self\" >".$strImage.$strTitle."</a>\n";
		}
		else
			$strReturn .= $strImage.$strTitle;

		$strReturn .="</p>\n";
		$strReturn = "<div class=\"bild\">".$strReturn."</div>";

		return $strReturn;
	}

}	 //class_element_bild
?>