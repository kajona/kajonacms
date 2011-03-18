<?php
/*"******************************************************************************************************
*   (c) 2004-2006 by MulchProductions, www.mulchprod.de                                                 *
*   (c) 2007-2011 by Kajona, www.kajona.de                                                              *
*       Published under the GNU LGPL v2.1, see /system/licence_lgpl.txt                                 *
*-------------------------------------------------------------------------------------------------------*
*	$Id$	                                    *
********************************************************************************************************/

/**
 * Portal element of the search-module
 *
 * @package modul_search
 */
class class_element_search extends class_element_portal implements interface_portal_element {

    /**
     * Constructor
     *
     * @param mixed $arrElementData
     */
	public function __construct($objElementData) {
        $arrModule = array();
		$arrModule["name"] 			= "element_suche";
		$arrModule["author"] 		= "sidler@mulchprod.de";
		$arrModule["moduleId"] 		= _pages_elemente_modul_id_;
		$arrModule["table"]		    = _dbprefix_."element_search";
        parent::__construct($arrModule, $objElementData);

        $this->setStrCacheAddon(getPost("searchterm").getGet("searchterm"));
	}

 /**
     * Loads the search-class and passes control
     *
     * @return string
     */
	public function loadData() {
		$strReturn = "";
		//Load the data
		$objSearchModule = class_modul_system_module::getModuleByName("search");
		if($objSearchModule != null) {
    		$objSearch = $objSearchModule->getPortalInstanceOfConcreteModule($this->arrElementData);
            $strReturn = $objSearch->action();
		}
		return $strReturn;
	}

}
?>