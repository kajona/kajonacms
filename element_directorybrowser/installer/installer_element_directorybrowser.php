<?php
/*"******************************************************************************************************
*   (c) 2004-2006 by MulchProductions, www.mulchprod.de                                                 *
*   (c) 2007-2011 by Kajona, www.kajona.de                                                              *
*       Published under the GNU LGPL v2.1, see /system/licence_lgpl.txt                                 *
*-------------------------------------------------------------------------------------------------------*
*	$Id: installer_element_directorybrowser.php 3530 2011-01-06 12:30:26Z sidler $                               *
********************************************************************************************************/

/**
 * Installer to install a directorybrowser-element to use in the portal
 *
 * @package modul_pages
 */
class class_installer_element_directorybrowser extends class_installer_base implements interface_installer {

    /**
     * Constructor
     *
     */
	public function __construct() {
        $arrModule = array();
		$arrModule["version"] 		= "1.0";
		$arrModule["name"] 			= "element_directorybrowser";
		$arrModule["name_lang"] 	= "Element directorybrowser";
		$arrModule["nummer2"] 		= _pages_content_modul_id_;
		parent::__construct($arrModule);
	}

	public function getNeededModules() {
	    return array("system", "pages");
	}
	
    public function getMinSystemVersion() {
	    return "3.3.1.8";
	}

	public function hasPostInstalls() {
	    //needed: pages
	    try {
		    class_modul_system_module::getModuleByName("pages");
		}
		catch (class_exception $objE) {
		    return false;
		}

	    //check, if not already existing
	    $objElement = null;
		try {
		    $objElement = class_modul_pages_element::getElement("directorybrowser");
		}
		catch (class_exception $objEx)  {
		}
        if($objElement == null)
            return true;

        return false;
	}

    public function hasPostUpdates() {
        $objElement = null;
		try {
		    $objElement = class_modul_pages_element::getElement("directorybrowser");
            if($objElement != null && version_compare($this->arrModule["version"], $objElement->getStrVersion(), ">"))
                return true;
		}
		catch (class_exception $objEx)  {
		}

        return false;
    }

	public function install() {
    }

    public function postInstall() {
		$strReturn = "";

		//Register the element
		$strReturn .= "Registering directorybrowser-element...\n";
		//check, if not already existing
        $objElement = null;
		try {
		    $objElement = class_modul_pages_element::getElement("directorybrowser");
		}
		catch (class_exception $objEx)  {
		}
		if($objElement == null) {
		    $objElement = new class_modul_pages_element();
		    $objElement->setStrName("directorybrowser");
		    $objElement->setStrClassAdmin("class_element_directorybrowser.php");
		    $objElement->setStrClassPortal("class_element_directorybrowser.php");
		    $objElement->setIntCachetime(3600);
		    $objElement->setIntRepeat(0);
            $objElement->setStrVersion($this->getVersion());
			$objElement->updateObjectToDb();
			$strReturn .= "Element registered...\n";
		}
		else {
			$strReturn .= "Element already installed!...\n";
		}
		return $strReturn;
	}


	public function update() {
	}

    public function postUpdate() {
        $strReturn = "";

        return $strReturn;
    }
    
}
?>
