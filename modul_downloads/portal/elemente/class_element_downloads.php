<?php
/*"******************************************************************************************************
*   (c) 2004-2006 by MulchProductions, www.mulchprod.de                                                 *
*   (c) 2007-2010 by Kajona, www.kajona.de                                                              *
*       Published under the GNU LGPL v2.1, see /system/licence_lgpl.txt                                 *
*-------------------------------------------------------------------------------------------------------*
*	$Id$                                  *
********************************************************************************************************/

/**
 * Portal-part of the downloads-element
 *
 * @package modul_downloads
 */
class class_element_downloads extends class_element_portal implements interface_portal_element {

    /**
     * Contructor
     *
     * @param mixed $arrElementData
     */
	public function __construct($objElementData) {
        $arrModule = array();
		$arrModule["name"] 			= "element_downloads";
		$arrModule["author"] 		= "sidler@mulchprod.de";
		$arrModule["moduleId"] 		= _pages_elemente_modul_id_;
		$arrModule["table"]		    = _dbprefix_."element_downloads";
		parent::__construct($arrModule, $objElementData);
	}


    /**
     * Loads the downloads-class and passes control
     *
     * @return string
     */
	public function loadData() {
		$strReturn = "";

        $objDownloadsModule = class_modul_system_module::getModuleByName("downloads");
		if($objDownloadsModule != null) {
    		$strClassName = uniStrReplace(".php", "", $objDownloadsModule->getStrNamePortal());
    		$objDownloads = new $strClassName($this->arrElementData);
            $strReturn = $objDownloads->action();
		}

		return $strReturn;
	}


}
?>