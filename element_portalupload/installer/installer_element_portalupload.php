<?php
/*"******************************************************************************************************
*   (c) 2004-2006 by MulchProductions, www.mulchprod.de                                                 *
*   (c) 2007-2009 by Kajona, www.kajona.de                                                              *
*       Published under the GNU LGPL v2.1, see /system/licence_lgpl.txt                                 *
*-------------------------------------------------------------------------------------------------------*
*	$Id$                          *
********************************************************************************************************/

require_once(_systempath_."/class_installer_base.php");
require_once(_systempath_."/interface_installer.php");

/**
 * Installer to install a login-element to use in the portal
 *
 * @package modul_pages
 */
class class_installer_element_portalupload extends class_installer_base implements interface_installer {

    /**
     * Constructor
     *
     */
	public function __construct() {
        $arrModule = array();
		$arrModule["version"] 		= "3.2.1";
		$arrModule["name"] 			= "element_portalupload";
		$arrModule["name_lang"] 	= "Element portalupload";
		$arrModule["nummer2"] 		= _pages_inhalte_modul_id_;
		$arrModule["tabellen"][]    = _dbprefix_."element_universal";
		parent::__construct($arrModule);
	}

	public function getNeededModules() {
	    return array("system", "pages", "downloads");
	}
	
    public function getMinSystemVersion() {
	    return "3.2.0.9";
	}

	public function hasPostInstalls() {
	    //needed:  downloads
	    try {
		    $objModule = class_modul_system_module::getModuleByName("downloads");
		}
		catch (class_exception $objE) {
		    return false;
		}

	     //check, if not already existing
	     $objElement = null;
		try {
		    $objElement = class_modul_pages_element::getElement("portalupload");
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
		    $objElement = class_modul_pages_element::getElement("portalupload");
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
        $strReturn .= "Registering portalupload-element...\n";
        //check, if not already existing
        $objElement = null;
        try {
            $objElement = class_modul_pages_element::getElement("portalupload");
        }
        catch (class_exception $objEx)  {
        }
        if($objElement == null) {
            $objElement = new class_modul_pages_element();
            $objElement->setStrName("portalupload");
            $objElement->setStrClassAdmin("class_element_portalupload.php");
            $objElement->setStrClassPortal("class_element_portalupload.php");
            $objElement->setIntCachetime(-1);
            $objElement->setIntRepeat(0);
            $objElement->setStrVersion($this->getVersion());
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

    public function postUpdate() {
        $strReturn = "";
        if(class_modul_pages_element::getElement("portalupload")->getStrVersion() == "3.2.0.9") {
            $strReturn .= $this->postUpdate_3209_321();
        }

        return $strReturn;
    }

    public function postUpdate_3209_321() {
        $strReturn = "";
        $strReturn = "Updating element portalupload to 3.2.1...\n";
        $this->updateElementVersion("portalupload", "3.2.1");
        return $strReturn;
    }
}
?>