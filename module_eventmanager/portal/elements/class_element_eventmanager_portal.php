<?php
/*"******************************************************************************************************
*   (c) 2004-2006 by MulchProductions, www.mulchprod.de                                                 *
*   (c) 2007-2012 by Kajona, www.kajona.de                                                              *
*       Published under the GNU LGPL v2.1, see /system/licence_lgpl.txt                                 *
*-------------------------------------------------------------------------------------------------------*
*	$Id: class_element_eventmanager.php 3841 2011-05-17 18:47:40Z sidler $						               	*
********************************************************************************************************/

/**
 * Portal-part of the eventmanager-element
 *
 * @package module_eventmanager
 * @author sidler@mulchprod.de
 */
class class_element_eventmanager_portal extends class_element_portal implements interface_portal_element {


    /**
     * Constructor
     *
     * @param class_module_pages_pageelement|mixed $objElementData
     */
	public function __construct($objElementData) {
        parent::__construct($objElementData);
        $this->setArrModuleEntry("table", _dbprefix_."element_universal");
    }


    /**
     * Loads the eventmanager-class and passes control
     *
     * @return string
     */
	public function loadData() {
		$strReturn = "";
		//Load the data
		$objEventmanagerModule = class_module_system_module::getModuleByName("eventmanager");
		if($objEventmanagerModule != null) {
    		$objEventmanager = $objEventmanagerModule->getPortalInstanceOfConcreteModule($this->arrElementData);
            $strReturn = $objEventmanager->action();
		}
		return $strReturn;
	}

}
