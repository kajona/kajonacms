<?php
/*"******************************************************************************************************
*   (c) 2004-2006 by MulchProductions, www.mulchprod.de                                                 *
*   (c) 2007-2010 by Kajona, www.kajona.de                                                              *
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
		$arrModule["version"] 		= "3.3.1";
		$arrModule["name"] 			= "element_formular";
		$arrModule["name_lang"] 	= "Element Form";
		$arrModule["nummer2"] 		= _pages_content_modul_id_;
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
		$arrFields["formular_template"] = array("char254", true);
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
        if(class_modul_pages_element::getElement("form")->getStrVersion() == "3.2.0.9") {
            $strReturn .= $this->postUpdate_3209_321();
            $this->objDB->flushQueryCache();
        }

        if(class_modul_pages_element::getElement("form")->getStrVersion() == "3.2.1") {
            $strReturn .= $this->postUpdate_321_330();
            $this->objDB->flushQueryCache();
        }

        if(class_modul_pages_element::getElement("form")->getStrVersion() == "3.3.0") {
            $strReturn .= $this->postUpdate_330_3309();
            $this->objDB->flushQueryCache();
        }

        if(class_modul_pages_element::getElement("form")->getStrVersion() == "3.3.0.9") {
            $strReturn .= $this->postUpdate_3309_331();
            $this->objDB->flushQueryCache();
        }

        return $strReturn;
    }

    public function postUpdate_3209_321() {
        $strReturn = "Updating element form to 3.2.1...\n";
        $this->updateElementVersion("form", "3.2.1");
        return $strReturn;
    }

    public function postUpdate_321_330() {
        $strReturn = "Updating element form to 3.3.0...\n";
        $this->updateElementVersion("form", "3.3.0");
        return $strReturn;
    }

    public function postUpdate_330_3309() {
        $strReturn = "Updating element form to 3.3.0.9...\n";

        $strReturn .= "Adding tempalte row to element table...\n";
        $strQuery = "ALTER TABLE ".$this->objDB->encloseTableName(_dbprefix_."element_formular")."
                       ADD ".$this->objDB->encloseColumnName("formular_template")." ".$this->objDB->getDatatype("char254")." NULL ";

        if($this->objDB->_query($strQuery))
                $strReturn .= " ...ok\n";
            else
                $strReturn .= " ...failed!!!\n";

        $this->updateElementVersion("form", "3.3.0.9");
        return $strReturn;
    }

    public function postUpdate_3309_331() {
        $strReturn = "Updating element form to 3.3.1...\n";
        $this->updateElementVersion("form", "3.3.1");
        return $strReturn;
    }
}
?>