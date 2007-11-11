<?php
/*"******************************************************************************************************
*   (c) 2004-2006 by MulchProductions, www.mulchprod.de                                                 *
*   (c) 2007 by Kajona, www.kajona.de                                                                   *
*       Published under the GNU LGPL v2.1, see /system/licence_lgpl.txt                                 *
*-------------------------------------------------------------------------------------------------------*
* 																										*
* 	installer_element_rssfeed.php																        *
* 	Installer of the rssfeed element      															    *																										*
*																										*
*-------------------------------------------------------------------------------------------------------*
*	$Id$                               *
********************************************************************************************************/

require_once(_systempath_."/class_installer_base.php");
require_once(_systempath_."/interface_installer.php");
include_once(_systempath_."/class_modul_pages_element.php");

/**
 * Installer to install a rssfeed-element to use in the portal
 *
 * @package modul_pages
 */
class class_installer_element_rssfeed extends class_installer_base implements interface_installer {

    /**
     * Constructor
     *
     */
	public function __construct() {
		$arrModule["version"] 		= "3.0.9";
		$arrModule["name"] 			= "element_rssfeed";
		$arrModule["name_lang"] 	= "Element rssfeed";
		$arrModule["nummer2"] 		= _pages_inhalte_modul_id_;
		parent::__construct($arrModule);
	}

	public function getNeededModules() {
	    return array("system", "pages");
	}
	
    public function getMinSystemVersion() {
	    return "3.0.9";
	}

	public function hasPostInstalls() {
	    //needed: pages
	    try {
		    $objModule = class_modul_system_module::getModuleByName("pages");
		}
		catch (class_exception $objE) {
		    return false;
		}

	    //check, if not already existing
		try {
		    $objElement = class_modul_pages_element::getElement("rssfeed");
		}
		catch (class_exception $objEx)  {
		}
        if($objElement == null)
            return true;

        return false;
	}

	public function install() {
    }

    public function postInstall() {
		$strReturn = "";

		//Register the element
		$strReturn .= "Registering rssfeed-element...\n";
		//check, if not already existing
		try {
		    $objElement = class_modul_pages_element::getElement("rssfeed");
		}
		catch (class_exception $objEx)  {
		}
		if($objElement == null) {
		    $objElement = new class_modul_pages_element();
		    $objElement->setStrName("rssfeed");
		    $objElement->setStrClassAdmin("class_element_rssfeed.php");
		    $objElement->setStrClassPortal("class_element_rssfeed.php");
		    $objElement->setIntCachetime(-1);
		    $objElement->setIntRepeat(0);
			$objElement->saveObjectToDb();
			$strReturn .= "Element registered...\n";
		}
		else {
			$strReturn .= "Element already installed!...\n";
		}
		return $strReturn;
	}


	public function update() {

	}
}
?>