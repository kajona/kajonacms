<?php
/*"******************************************************************************************************
*   (c) 2004-2006 by MulchProductions, www.mulchprod.de                                                 *
*   (c) 2007-2010 by Kajona, www.kajona.de                                                              *
*       Published under the GNU LGPL v2.1, see /system/licence_lgpl.txt                                 *
*-------------------------------------------------------------------------------------------------------*
*	$Id$                                  *
********************************************************************************************************/

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
        $arrModule = array();
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
    		$strClassName = uniStrReplace(".php", "", $objGBModule->getStrNamePortal());
    		$objGuestbook= new $strClassName($this->arrElementData);
            $strReturn = $objGuestbook->action();
		}

		return $strReturn;
	}

}	 //class_element_absatz
?>