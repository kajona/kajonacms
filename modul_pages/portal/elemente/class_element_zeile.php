<?php
/*"******************************************************************************************************
*   (c) 2004-2006 by MulchProductions, www.mulchprod.de                                                 *
*   (c) 2007-2008 by Kajona, www.kajona.de                                                              *
*       Published under the GNU LGPL v2.1, see /system/licence_lgpl.txt                                 *
*-------------------------------------------------------------------------------------------------------*
* 																										*
* 	class_element_zeile.php																				*
* 	Portal-Klasse des Zeilen-Elements																	*
*-------------------------------------------------------------------------------------------------------*
*	$Id$                                      *
********************************************************************************************************/

//base-class
require_once(_portalpath_."/class_elemente_portal.php");
//Interface
require_once(_portalpath_."/interface_portal_element.php");

/**
 * Portal-Part of the paragraph
 *
 * @package modul_pages
 */
class class_element_zeile extends class_element_portal implements  interface_portal_element {

	/**
	 * Constructor
	 *
	 * @param mixed $arrElementData
	 */
	public function __construct($objElementData) {
		$arrModul["name"] 			= "element_zeile";
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
			$strReturn .= $this->arrElementData["absatz_titel"];


		$strReturn = "<h2>".$strReturn."</h2>";

		return $strReturn;
	}

}	 //class_element_absatz
?>