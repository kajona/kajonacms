<?php
/*"******************************************************************************************************
*   (c) 2004-2006 by MulchProductions, www.mulchprod.de                                                 *
*   (c) 2007-2011 by Kajona, www.kajona.de                                                              *
*       Published under the GNU LGPL v2.1, see /system/licence_lgpl.txt                                 *
*-------------------------------------------------------------------------------------------------------*
*	$Id$                                 *
********************************************************************************************************/

/**
 * Portal-class of the navigation element, loads the navigation-portal class
 *
 * @package module_navigation
 * @author sidler@mulchprod.de
 */
class class_element_navigation_portal extends class_element_portal implements interface_portal_element {

    /**
     * Contructor
     *
     * @param mixed $arrElementData
     */
	public function __construct($objElementData) {
        $arrModule = array();
		$arrModule["name"] 			= "element_navigation";
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

        $objNaviModule = class_module_system_module::getModuleByName("navigation");
		if($objNaviModule != null) {
            $objNavigation = $objNaviModule->getPortalInstanceOfConcreteModule($this->arrElementData);
            $strReturn = $objNavigation->action();
		}

		return $strReturn;
	}

	/**
	 * no anchor here, plz
	 *
	 * @return string
	 */
    protected function getAnchorTag() {
        return "";
    }

}
