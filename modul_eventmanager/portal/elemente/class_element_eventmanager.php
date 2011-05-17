<?php
/*"******************************************************************************************************
*   (c) 2004-2006 by MulchProductions, www.mulchprod.de                                                 *
*   (c) 2007-2011 by Kajona, www.kajona.de                                                              *
*       Published under the GNU LGPL v2.1, see /system/licence_lgpl.txt                                 *
*-------------------------------------------------------------------------------------------------------*
*	$Id$						               	*
********************************************************************************************************/

/**
 * Portal-part of the eventmanager-element
 *
 * @package modul_eventmanager
 * @author sidler@mulchprod.de
 */
class class_element_eventmanager extends class_element_portal implements interface_portal_element {


	 /**
     * Contructor
     *
     * @param mixed $arrElementData
     */
	public function __construct($objElementData) {
        $arrModule = array();
		$arrModule["name"] 			= "element_eventmanager";
		$arrModule["moduleId"] 		= _pages_elemente_modul_id_;
		$arrModule["table"]	    	= _dbprefix_."element_universal";

        parent::__construct($arrModule, $objElementData);
	}


    /**
     * Loads the eventmanager-class and passes control
     *
     * @return string
     */
	public function loadData() {
		$strReturn = "";
		//Load the data
		$objEventmanagerModule = class_modul_system_module::getModuleByName("eventmanager");
		if($objEventmanagerModule != null) {
    		$objEventmanager = $objEventmanagerModule->getPortalInstanceOfConcreteModule($this->arrElementData);
            $strReturn = $objEventmanager->action();
		}
		return $strReturn;
	}

}
?>