<?php
/*"******************************************************************************************************
*   (c) 2004-2006 by MulchProductions, www.mulchprod.de                                                 *
*   (c) 2007-2008 by Kajona, www.kajona.de                                                              *
*       Published under the GNU LGPL v2.1, see /system/licence_lgpl.txt                                 *
*-------------------------------------------------------------------------------------------------------*
* 																										*
* 	class_element_textticker.php																		*
* 	Portal-class of the textticker element															    *
*-------------------------------------------------------------------------------------------------------*
*	$Id: class_element_textticker.php 1884 2007-12-26 15:04:48Z sidler $                                *
********************************************************************************************************/

//base-class
require_once(_portalpath_."/class_elemente_portal.php");
//Interface
require_once(_portalpath_."/interface_portal_element.php");

/**
 * Loads the last-modified date of the current page and prepares it for output
 *
 * @package modul_pages
 */
class class_element_textticker extends class_element_portal implements interface_portal_element {

	/**
	 * Constructor
	 *
	 * @param mixed $arrElementData
	 */
	public function __construct($objElementData) {
		$arrModule["name"] 			= "element_textticker";
		$arrModule["author"] 		= "sidler@mulchprod.de";
		$arrModule["moduleId"] 		= _pages_elemente_modul_id_;
		$arrModule["table"]		    = _dbprefix_."element_universal";
		$arrModule["modul"]		    = "elemente";

		parent::__construct($arrModule, $objElementData);
	}

    /**
     * Looks up the last modified-date of the current page
     *
     * @return string the prepared html-output
     */
	public function loadData() {

		$strText = uniStrReplace("<br />", "", $this->arrElementData["char1"]);
        
		$strText = "<div id=\"textticker\"><marquee scrollamount=\"2\" scrolldelay=\"30\">".$strText."</marquee></div>";
		return $strText;
	}

}
?>