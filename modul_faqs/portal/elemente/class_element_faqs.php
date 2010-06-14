<?php
/*"******************************************************************************************************
*   (c) 2004-2006 by MulchProductions, www.mulchprod.de                                                 *
*   (c) 2007-2010 by Kajona, www.kajona.de                                                              *
*       Published under the GNU LGPL v2.1, see /system/licence_lgpl.txt                                 *
*-------------------------------------------------------------------------------------------------------*
*	$Id$						               	*
********************************************************************************************************/

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
        $arrModule = array();
		$arrModule["name"] 			= "element_faqs";
		$arrModule["author"] 		= "sidler@mulchprod.de";
		$arrModule["moduleId"] 		= _pages_elemente_modul_id_;
		$arrModule["table"]	    	= _dbprefix_."element_faqs";

        parent::__construct($arrModule, $objElementData);

        //we support ratings, so add cache-busters
        $this->setStrCacheAddon(getCookie("kj_ratingHistory"));
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
    		$strClassName = uniStrReplace(".php", "", $objFaqsModule->getStrNamePortal());
    		$objFaqs = new $strClassName($this->arrElementData);
            $strReturn = $objFaqs->action();
		}
		return $strReturn;
	}

}
?>