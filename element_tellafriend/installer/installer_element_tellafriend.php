<?php
/*"******************************************************************************************************
*   (c) 2004-2006 by MulchProductions, www.mulchprod.de                                                 *
*   (c) 2007-2011 by Kajona, www.kajona.de                                                              *
*       Published under the GNU LGPL v2.1, see /system/licence_lgpl.txt                                 *
*-------------------------------------------------------------------------------------------------------*
*	$Id$                          *
********************************************************************************************************/

/**
 * Installer to install a tellafriend-element to use in the portal
 *
 * @package modul_pages
 */
class class_installer_element_tellafriend extends class_installer_base implements interface_installer {

    /**
     * Constructor
     *
     */
	public function __construct() {
        $arrModule = array();
		$arrModule["version"] 		= "3.4.0";
		$arrModule["name"] 			= "element_tellafriend";
		$arrModule["name_lang"] 	= "Element tellafriend";
		$arrModule["nummer2"] 		= _pages_content_modul_id_;
		parent::__construct($arrModule);
	}

	public function getNeededModules() {
	    return array("system", "pages");
	}
	
    public function getMinSystemVersion() {
	    return "3.4.0";
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
		    $objElement = class_modul_pages_element::getElement("tellafriend");
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
		    $objElement = class_modul_pages_element::getElement("tellafriend");
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

		//Table for page-element
		$strReturn .= "Installing tellafriend-element table...\n";
		
		$arrFields = array();
		$arrFields["content_id"] 			= array("char20", false);
		$arrFields["tellafriend_template"] 	= array("char254", true);
		$arrFields["tellafriend_error"] 	= array("char254", true);
		$arrFields["tellafriend_success"] 	= array("char254", true);
		
		if(!$this->objDB->createTable("element_tellafriend", $arrFields, array("content_id")))
			$strReturn .= "An error occured! ...\n";

		//Register the element
		$strReturn .= "Registering tellafriend-element...\n";
		//check, if not already existing
        $objElement = null;
		try {
		    $objElement = class_modul_pages_element::getElement("tellafriend");
		}
		catch (class_exception $objEx)  {
		}
		if($objElement == null) {
		    $objElement = new class_modul_pages_element();
		    $objElement->setStrName("tellafriend");
		    $objElement->setStrClassAdmin("class_element_tellafriend.php");
		    $objElement->setStrClassPortal("class_element_tellafriend.php");
		    $objElement->setIntCachetime(-1);
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
        if(class_modul_pages_element::getElement("tellafriend")->getStrVersion() == "3.2.0.9") {
            $strReturn .= $this->postUpdate_3209_321();
            $this->objDB->flushQueryCache();
        }

        if(class_modul_pages_element::getElement("tellafriend")->getStrVersion() == "3.2.1") {
            $strReturn .= $this->postUpdate_321_330();
            $this->objDB->flushQueryCache();
        }

        if(class_modul_pages_element::getElement("tellafriend")->getStrVersion() == "3.3.0") {
            $strReturn .= $this->postUpdate_330_331();
            $this->objDB->flushQueryCache();
        }

        if(class_modul_pages_element::getElement("tellafriend")->getStrVersion() == "3.3.1") {
            $strReturn .= $this->postUpdate_331_340();
            $this->objDB->flushQueryCache();
        }

        return $strReturn;
    }

    public function postUpdate_3209_321() {
        $strReturn = "Updating element tellafriend to 3.2.1...\n";
        $this->updateElementVersion("tellafriend", "3.2.1");
        return $strReturn;
    }

    public function postUpdate_321_330() {
        $strReturn = "Updating element tellafriend to 3.3.0...\n";
        $this->updateElementVersion("tellafriend", "3.3.0");
        return $strReturn;
    }

    public function postUpdate_330_331() {
        $strReturn = "Updating element tellafriend to 3.3.1...\n";
        $this->updateElementVersion("tellafriend", "3.3.1");
        return $strReturn;
    }

    public function postUpdate_331_340() {
        $strReturn = "Updating element tellafriend to 3.4.0...\n";
        $this->updateElementVersion("tellafriend", "3.4.0");
        return $strReturn;
    }
}
?>