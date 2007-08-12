<?php
/*"******************************************************************************************************
*   (c) 2004-2006 by MulchProductions, www.mulchprod.de                                                 *
*   (c) 2007 by Kajona, www.kajona.de                                                                   *
*       Published under the GNU LGPL v2.1, see /system/licence_lgpl.txt                                 *
*-------------------------------------------------------------------------------------------------------*
* 																										*
* 	class_element_guestbook.php  																		*
* 	Portal-class of the guestbook element                                                               *
*-------------------------------------------------------------------------------------------------------*
*	$Id$                                  *
********************************************************************************************************/

//base-class
require_once(_portalpath_."/class_elemente_portal.php");
//Interface
require_once(_portalpath_."/interface_portal_element.php");

/**
 * Portal-part of the guestbook-element
 *
 * @package modul_guestbook
 */
class class_element_guestbook extends class_element_portal implements interface_portal_element {

    /**
     * Contructor
     *
     * @param mixed $arrElementData
     */
	public function __construct($objElementData) {
		$arrModule["name"] 			= "element_guestbook";
		$arrModule["author"] 		= "sidler@mulchprod.de";
		$arrModule["moduleId"] 		= _pages_elemente_modul_id_;
		$arrModule["table"]		    = _dbprefix_."element_guestbook";
		parent::__construct($arrModule, $objElementData);
	}

    /**
     * Loads the guestbook-class and passes control
     *
     * @return string
     */
	public function loadData() {
		$strReturn = "";

        $objGBModule = class_modul_system_module::getModuleByName("guestbook");
		if($objGBModule != null) {
    		require_once(_portalpath_."/".$objGBModule->getStrNamePortal());
    		$strClassName = $objGBModule->getStrClassPortal();
    		$objGuestbook= new $strClassName($this->arrElementData);
            $strReturn = $objGuestbook->action();
		}

		return $strReturn;
	}

}	 //class_element_absatz
?>