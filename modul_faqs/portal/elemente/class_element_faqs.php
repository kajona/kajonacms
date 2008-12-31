<?php
/*"******************************************************************************************************
*   (c) 2004-2006 by MulchProductions, www.mulchprod.de                                                 *
*   (c) 2007-2009 by Kajona, www.kajona.de                                                              *
*       Published under the GNU LGPL v2.1, see /system/licence_lgpl.txt                                 *
*-------------------------------------------------------------------------------------------------------*
*	$Id$						               	*
********************************************************************************************************/


//base-class
require_once(_portalpath_."/class_elemente_portal.php");
//Interface
require_once(_portalpath_."/interface_portal_element.php");

/**
 * Portal-part of the faqs-element
 *
 * @package modul_faqs
 */
class class_element_faqs extends class_element_portal implements interface_portal_element {


	 /**
     * Contructor
     *
     * @param mixed $arrElementData
     */
	public function __construct($objElementData) {
		$arrModule["name"] 			= "element_faqs";
		$arrModule["author"] 		= "sidler@mulchprod.de";
		$arrModule["moduleId"] 		= _pages_elemente_modul_id_;
		$arrModule["table"]	    	= _dbprefix_."element_faqs";

        parent::__construct($arrModule, $objElementData);
	}


    /**
     * Loads the faqs-class and passes control
     *
     * @return string
     */
	public function loadData() {
		$strReturn = "";
		//Load the data
		$objFaqsModule = class_modul_system_module::getModuleByName("faqs");
		if($objFaqsModule != null) {
    		require_once(_portalpath_."/".$objFaqsModule->getStrNamePortal());
    		$strClassName = uniStrReplace(".php", "", $objFaqsModule->getStrNamePortal());
    		$objFaqs = new $strClassName($this->arrElementData);
            $strReturn = $objFaqs->action();
		}
		return $strReturn;
	}

}
?>