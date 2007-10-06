<?php
/*"******************************************************************************************************
*   (c) 2004-2006 by MulchProductions, www.mulchprod.de                                                 *
*   (c) 2007 by Kajona, www.kajona.de                                                                   *
*       Published under the GNU LGPL v2.1, see /system/licence_lgpl.txt                                 *
*-------------------------------------------------------------------------------------------------------*
* 																										*
* 	class_element_downloads.php																			*
* 	Portal-class of the downloads-element                                                               *
*-------------------------------------------------------------------------------------------------------*
*	$Id$                                  *
********************************************************************************************************/


//base-class
require_once(_portalpath_."/class_elemente_portal.php");
//Interface
require_once(_portalpath_."/interface_portal_element.php");

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
    		require_once(_portalpath_."/".$objDownloadsModule->getStrNamePortal());
    		$strClassName = uniStrReplace(".php", "", $objDownloadsModule->getStrNamePortal());
    		$objDownloads = new $strClassName($this->arrElementData);
            $strReturn = $objDownloads->action();
		}

		return $strReturn;
	}


}
?>