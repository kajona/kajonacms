<?php
/*"******************************************************************************************************
*   (c) 2004-2006 by MulchProductions, www.mulchprod.de                                                 *
*   (c) 2007-2011 by Kajona, www.kajona.de                                                              *
*       Published under the GNU LGPL v2.1, see /system/licence_lgpl.txt                                 *
*-------------------------------------------------------------------------------------------------------*
*	$Id$                                   *
********************************************************************************************************/

/**
 * Portal Element to load the formular specified in the admin
 *
 * @package modul_pages
 */
class class_element_formular extends class_element_portal implements interface_portal_element {

	/**
	 * Constructor
	 *
	 * @param mixed $arrElementData
	 */
	public function __construct($objElementData) {
        $arrModule = array();
		$arrModule["name"] 			= "element_formular";
		$arrModule["author"] 		= "sidler@mulchprod.de";
		$arrModule["moduleId"] 		= _pages_elemente_modul_id_;
		$arrModule["table"]		    = _dbprefix_."element_formular";

		parent::__construct($arrModule, $objElementData);
	}

    /**
     * Loads the navigation-class and passes control
     *
     * @return string
     */
	public function loadData() {
		$strReturn = "";
		//Load the data

		require_once(_portalpath_."/forms/".$this->arrElementData["formular_class"]);
		$strClassname = uniStrReplace(".php", "", $this->arrElementData["formular_class"]);
		$objForm = new $strClassname($this->arrElementData);
        $strReturn = $objForm->action();

		return $strReturn;
	}

}
?>