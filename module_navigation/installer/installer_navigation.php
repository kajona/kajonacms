<?php
/*"******************************************************************************************************
*   (c) 2004-2006 by MulchProductions, www.mulchprod.de                                                 *
*   (c) 2007-2012 by Kajona, www.kajona.de                                                              *
*       Published under the GNU LGPL v2.1, see /system/licence_lgpl.txt                                 *
*-------------------------------------------------------------------------------------------------------*
*   $Id$                                     *
********************************************************************************************************/

/**
 * Installer of the navigation
 *
 * @package module_navigation
 */
class class_installer_navigation extends class_installer_base implements interface_installer {

	public function __construct() {
        $this->setArrModuleEntry("version", "3.4.9");
        $this->setArrModuleEntry("moduleId", _navigation_modul_id_);
        $this->setArrModuleEntry("name", "navigation");
        $this->setArrModuleEntry("name_lang", "Module Navigation");

		parent::__construct();
	}

	public function getNeededModules() {
	    return array("system", "pages");
	}

    public function getMinSystemVersion() {
	    return "3.4.1";
	}

	public function hasPostInstalls() {

        $objElement = class_module_pages_element::getElement("navigation");
        if($objElement === null)
            return true;

        return false;
	}

	public function getVersion() {
	    return $this->arrModule["version"];
	}


    public function install() {
		if(count($this->objDB->getTables()) > 0) {
			$arrModul = $this->getModuleData($this->arrModule["name"]);
			if(count($arrModul) > 0)
				return "<strong>Module already installed!!!</strong><br /><br />";
		}

		$strReturn = "Installing ".$this->arrModule["name_lang"]."...\n";
		//Tabellen anlegen

		//navigation-------------------------------------------------------------------------------------
		$strReturn .= "Installing table navigation...\n";

		$arrFields = array();
		$arrFields["navigation_id"] 		= array("char20", false);
		$arrFields["navigation_name"] 		= array("char254", true);
		$arrFields["navigation_page_e"] 	= array("char254", true);
		$arrFields["navigation_page_i"] 	= array("char254", true);
		$arrFields["navigation_folder_i"] 	= array("char20", true);
		$arrFields["navigation_target"] 	= array("char254", true);
		$arrFields["navigation_image"] 		= array("char254", true);

		if(!$this->objDB->createTable("navigation", $arrFields, array("navigation_id")))
			$strReturn .= "An error occured! ...\n";


		//register the module
		$this->registerModule("navigation", _navigation_modul_id_, "class_module_navigation_portal.php", "class_module_navigation_admin.php", $this->arrModule["version"] , true);

		return $strReturn;

	}

	public function postInstall() {
		$strReturn = "";

		$strReturn .= "Installing navigation-element table...\n";

		$arrFields = array();
		$arrFields["content_id"] 			= array("char20", false);
		$arrFields["navigation_id"] 		= array("char20", true);
		$arrFields["navigation_template"] 	= array("char254", true);
		$arrFields["navigation_mode"] 		= array("char254", true);

		if(!$this->objDB->createTable("element_navigation", $arrFields, array("content_id")))
			$strReturn .= "An error occured! ...\n";

		//Register the element
		$strReturn .= "Registering navigation-element...\n";
		//check, if not already existing
        $objElement = null;
		try {
		    $objElement = class_module_pages_element::getElement("navigation");
		}
		catch (class_exception $objEx)  {
		}
		if($objElement == null) {
		    $objElement = new class_module_pages_element();
		    $objElement->setStrName("navigation");
		    $objElement->setStrClassAdmin("class_element_navigation_admin.php");
		    $objElement->setStrClassPortal("class_element_navigation_portal.php");
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
	    $strReturn = "";
        //check installed version and to which version we can update
        $arrModul = $this->getModuleData($this->getArrModule("name"), false);

        $strReturn .= "Version found:\n\t Module: ".$arrModul["module_name"].", Version: ".$arrModul["module_version"]."\n\n";

        $arrModul = $this->getModuleData($this->arrModule["name"], false);
        if($arrModul["module_version"] == "3.4.0") {
            $strReturn .= $this->update_340_3401();
        }

        $arrModul = $this->getModuleData($this->arrModule["name"], false);
        if($arrModul["module_version"] == "3.4.0.1") {
            $strReturn .= $this->update_3401_341();
        }

        $arrModul = $this->getModuleData($this->arrModule["name"], false);
        if($arrModul["module_version"] == "3.4.1") {
            $strReturn .= $this->update_341_349();
        }

        return $strReturn."\n\n";
	}


    private function update_340_3401() {
        $strReturn = "Updating 3.4.0 to 3.4.0.1...\n";

        $strReturn .= "Deleting process-xml class...\n";
        $objFilesystem = new class_filesystem();
        if(!$objFilesystem->fileDelete("/admin/class_modul_navigation_admin_xml.php"))
            $strReturn .= "Deletion of /admin/class_modul_navigation_admin_xml.php failed!\n";

        $objModule = class_module_system_module::getModuleByName($this->arrModule["name"]);
        $objModule->setStrXmlNameAdmin("");
        $objModule->updateObjectToDb();



        $strReturn .= "Updating module-versions...\n";
        $this->updateModuleVersion("navigation", "3.4.0.1");
        $strReturn .= "Updating element-versions...\n";
        $this->updateElementVersion("navigation", "3.4.0.1");
        return $strReturn;
    }


    private function update_3401_341() {
        $strReturn = "Updating 3.4.0.1 to 3.4.1...\n";

        $strReturn .= "Updating module-versions...\n";
        $this->updateModuleVersion("navigation", "3.4.1");
        $strReturn .= "Updating element-versions...\n";
        $this->updateElementVersion("navigation", "3.4.1");
        return $strReturn;
    }

    private function update_341_349() {
        $strReturn = "Updating 3.4.1 to 3.4.9...\n";

        $strReturn .= "Adding classes for existing records...\n";
        $strReturn .= "Trees\n";
        foreach(class_module_navigation_tree::getAllNavis() as $objOneModule) {
            $strQuery = "UPDATE "._dbprefix_."system SET system_class = ? where system_id = ?";
            $this->objDB->_pQuery($strQuery, array( get_class($objOneModule), $objOneModule->getSystemid() ) );
        }

        $strReturn .= "Navigation Points\n";
        $arrRows = $this->objDB->getPArray("SELECT system_id FROM "._dbprefix_."navigation, "._dbprefix_."system WHERE system_id = navigation_id AND (system_class IS NULL OR system_class = '')", array());
        foreach($arrRows as $arrOneRow) {
            $strQuery = "UPDATE "._dbprefix_."system SET system_class = ? where system_id = ?";
            $this->objDB->_pQuery($strQuery, array( 'class_module_navigation_point', $arrOneRow["system_id"] ) );
        }

        $strReturn .= "Updating module-versions...\n";
        $this->updateModuleVersion("navigation", "3.4.9");
        $strReturn .= "Updating element-versions...\n";
        $this->updateElementVersion("navigation", "3.4.9");

        return $strReturn;
    }
}
