<?php
/*"******************************************************************************************************
*   (c) 2004-2006 by MulchProductions, www.mulchprod.de                                                 *
*   (c) 2007-2008 by Kajona, www.kajona.de                                                              *
*       Published under the GNU LGPL v2.1, see /system/licence_lgpl.txt                                 *
*-------------------------------------------------------------------------------------------------------*
*	$Id$                                    *
********************************************************************************************************/


//base-class
require_once(_portalpath_."/class_elemente_portal.php");
//Interface
require_once(_portalpath_."/interface_portal_element.php");

/**
 * Portal-part of the gallery-element
 *
 * @package modul_gallery
 */
class class_element_gallery extends class_element_portal implements interface_portal_element {


    /**
     * Contructor
     *
     * @param mixed $arrElementData
     */
	public function __construct($objElementData) {
		$arrModule["name"] 			= "element_gallery";
		$arrModule["author"] 		= "sidler@mulchprod.de";
		$arrModule["moduleId"] 		= _pages_elemente_modul_id_;
		$arrModule["table"]  		= _dbprefix_."element_gallery";
		parent::__construct($arrModule, $objElementData);
	}


    /**
     * Loads the gallery-class and passes control
     *
     * @return string
     */
	public function loadData() {
		$strReturn = "";

        $objGalleryModule = class_modul_system_module::getModuleByName("gallery");
		if($objGalleryModule != null) {
    		require_once(_portalpath_."/".$objGalleryModule->getStrNamePortal());
    		$strClassName = uniStrReplace(".php", "", $objGalleryModule->getStrNamePortal());
    		$objGallery = new $strClassName($this->arrElementData);
            $strReturn = $objGallery->action();
		}

		return $strReturn;
	}

}
?>