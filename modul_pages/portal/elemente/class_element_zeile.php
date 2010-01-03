<?php
/*"******************************************************************************************************
*   (c) 2004-2006 by MulchProductions, www.mulchprod.de                                                 *
*   (c) 2007-2010 by Kajona, www.kajona.de                                                              *
*       Published under the GNU LGPL v2.1, see /system/licence_lgpl.txt                                 *
*-------------------------------------------------------------------------------------------------------*
*	$Id$                                      *
********************************************************************************************************/

/**
 * Portal-Part of the row-element
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


		$strReturn = "<h1>".$strReturn."</h1>";

		return $strReturn;
	}

}	 //class_element_absatz
?>