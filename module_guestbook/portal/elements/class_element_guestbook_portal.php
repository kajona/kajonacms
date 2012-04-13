<?php
/*"******************************************************************************************************
*   (c) 2004-2006 by MulchProductions, www.mulchprod.de                                                 *
*   (c) 2007-2012 by Kajona, www.kajona.de                                                              *
*       Published under the GNU LGPL v2.1, see /system/licence_lgpl.txt                                 *
*-------------------------------------------------------------------------------------------------------*
*	$Id: class_module_navigation_tree.php 4582 2012-04-11 18:27:04Z sidler $                              *
********************************************************************************************************/

/**
 * Portal-part of the guestbook-element
 *
 * @package module_guestbook
 * @author sidler@mulchprod.de
 */
class class_element_guestbook_portal extends class_element_portal implements interface_portal_element {

    /**
     * Contructor
     *
     * @param $objElementData
     */
	public function __construct($objElementData) {
		$this->setArrModuleEntry("name", "element_guestbook");
		$this->setArrModuleEntry("table", _dbprefix_."element_guestbook");
		parent::__construct($objElementData);

        if($this->getParam("action") == "saveGuestbook")
            $this->setStrCacheAddon(generateSystemid());
	}

    /**
     * Loads the guestbook-class and passes control
     *
     * @return string
     */
	public function loadData() {
		$strReturn = "";

        $objGBModule = class_module_system_module::getModuleByName("guestbook");
		if($objGBModule != null) {
    		$objGuestbook= $objGBModule->getPortalInstanceOfConcreteModule($this->arrElementData);
            $strReturn = $objGuestbook->action();
		}

		return $strReturn;
	}

}
