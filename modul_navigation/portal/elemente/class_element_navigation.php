<?php
/*"******************************************************************************************************
*   (c) 2004-2006 by MulchProductions, www.mulchprod.de                                                 *
*   (c) 2007 by Kajona, www.kajona.de                                                                   *
*       Published under the GNU LGPL v2.1, see /system/licence_lgpl.txt                                 *
*-------------------------------------------------------------------------------------------------------*
* 																										*
* 	class_element_navigation.php																		*
* 	Portal-class of the navigation element                                                           	*
*-------------------------------------------------------------------------------------------------------*
*	$Id$                                 *
********************************************************************************************************/

//base-class
require_once(_portalpath_."/class_elemente_portal.php");
//Interface
require_once(_portalpath_."/interface_portal_element.php");


/**
 * Portal-class of the navigation element, loads the navigation-portal class
 *
 * @package modul_navigation
 */
class class_element_navigation extends class_element_portal implements interface_portal_element {

    /**
     * Contructor
     *
     * @param mixed $arrElementData
     */
	public function __construct($objElementData) {
		$arrModule["name"] 			= "element_navigation";
		$arrModule["author"] 		= "sidler@mulchprod.de";
		$arrModule["moduleId"] 		= _pages_elemente_modul_id_;
		$arrModule["table"]		    = _dbprefix_."element_navigation";
		parent::__construct($arrModule, $objElementData);
	}

    /**
     * Loads the navigation-class and passes control
     *
     * @return string
     */
	public function loadData() {
		$strReturn = "";

        $objNaviModule = class_modul_system_module::getModuleByName("navigation");
		if($objNaviModule != null) {
    		require_once(_portalpath_."/".$objNaviModule->getStrNamePortal());
    		$strClassName = $objNaviModule->getStrClassPortal();
    		$objNavigation = new $strClassName($this->arrElementData);
            $strReturn = $objNavigation->action();
		}

		return $strReturn;
	}

}	 //class_element_absatz
?>