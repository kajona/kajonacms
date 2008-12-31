<?php
/*"******************************************************************************************************
*   (c) 2004-2006 by MulchProductions, www.mulchprod.de                                                 *
*   (c) 2007-2009 by Kajona, www.kajona.de                                                              *
*       Published under the GNU LGPL v2.1, see /system/licence_lgpl.txt                                 *
*-------------------------------------------------------------------------------------------------------*
*	$Id$	                                    *
********************************************************************************************************/

//base-class
require_once(_portalpath_."/class_elemente_portal.php");
//Interface
require_once(_portalpath_."/interface_portal_element.php");

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
		$arrModule["name"] 			= "element_suche";
		$arrModule["author"] 		= "sidler@mulchprod.de";
		$arrModule["moduleId"] 		= _pages_elemente_modul_id_;
		$arrModule["table"]		    = _dbprefix_."element_search";
        parent::__construct($arrModule, $objElementData);
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
    		require_once(_portalpath_."/".$objSearchModule->getStrNamePortal());
    		$strClassName = uniStrReplace(".php", "", $objSearchModule->getStrNamePortal());
    		$objSearch = new $strClassName($this->arrElementData);
            $strReturn = $objSearch->action();
		}
		return $strReturn;
	}

}	 //class_element_absatz
?>