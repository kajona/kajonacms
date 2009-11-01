<?php
/*"******************************************************************************************************
*   (c) 2004-2006 by MulchProductions, www.mulchprod.de                                                 *
*   (c) 2007-2009 by Kajona, www.kajona.de                                                              *
*       Published under the GNU LGPL v2.1, see /system/licence_lgpl.txt                                 *
*-------------------------------------------------------------------------------------------------------*
*	$Id$                               *
********************************************************************************************************/

/**
 * Installer to install a form-element (provides a basic contact form)
 *
 * @package modul_pages
 */
class class_installer_element_formular extends class_installer_base implements interface_installer {

    /**
     * Constructor
     *
     */
	public function __construct() {
        $arrModule = array();
		$arrModule["version"] 		= "3.2.1";
		$arrModule["name"] 			= "element_formular";
		$arrModule["name_lang"] 	= "Element Form";
		$arrModule["nummer2"] 		= _pages_inhalte_modul_id_;
		$arrModule["tabellen"][]    = _dbprefix_."element_formular";
		parent::__construct($arrModule);
	}

	public function getNeededModules() {
	    return array("system", "pages");
	}
	
    public function getMinSystemVersion() {
	    return "3.2.0.9";
	}

	public function hasPostInstalls() {
	    //needed: pages
	    try {
		    $objModule = class_modul_system_module::getModuleByName("pages");
		}
		catch (class_exception $objE) {
		    return false;
		}

		$objElement = null;
	    try {
            $objElement = class_modul_pages_element::getElement("form");
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
		    $objElement = class_modul_pages_element::getElement("form");
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
		$strReturn .= "Installing formular-element table...\n";
		
		$arrFields = array();
		$arrFields["content_id"] 		= array("char20", false);
		$arrFields["formular_class"] 	= array("char254", true);
		$arrFields["formular_email"] 	= array("char254", true);
		$arrFields["formular_success"] 	= array("text", true);
		$arrFields["formular_error"] 	= array("text", true);
		
		if(!$this->objDB->createTable("element_formular", $arrFields, array("content_id")))
			$strReturn .= "An error occured! ...\n";

		//Register the element
		$strReturn .= "Registering formular-element...\n";
		//check, if not already existing
        $objElement = null;
        try {
            $objElement = class_modul_pages_element::getElement("form");
        }
        catch (class_exception $objEx)  {
        }
        if($objElement == null) {
            $objElement = new class_modul_pages_element();
            $objElement->setStrName("form");
            $objElement->setStrClassAdmin("class_element_formular.php");
            $objElement->setStrClassPortal("class_element_formular.php");
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
        if(class_modul_pages_element::getElement("form")->getStrVersion() == "3.2.0.9") {
            $strReturn .= $this->postUpdate_3209_321();
        }

        return $strReturn;
    }

    public function postUpdate_3209_321() {
        $strReturn = "";
        $strReturn = "Updating element form to 3.2.1...\n";
        $this->updateElementVersion("form", "3.2.1");
        return $strReturn;
    }
}
?>