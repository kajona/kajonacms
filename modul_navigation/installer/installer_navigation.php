<?php
/*"******************************************************************************************************
*   (c) 2004-2006 by MulchProductions, www.mulchprod.de                                                 *
*   (c) 2007-2008 by Kajona, www.kajona.de                                                              *
*       Published under the GNU LGPL v2.1, see /system/licence_lgpl.txt                                 *
*-------------------------------------------------------------------------------------------------------*
* 																										*
* 	installer_navigation.php																			*
* 	Installer of the navigation																			*
*																										*
*-------------------------------------------------------------------------------------------------------*
*   $Id$                                     *
********************************************************************************************************/

require_once(_systempath_."/class_installer_base.php");
require_once(_systempath_."/interface_installer.php");

/**
 * Installer of the navigation
 *
 * @package modul_navigation
 */
class class_installer_navigation extends class_installer_base implements interface_installer {

	public function __construct() {
		$arrModule["version"] 		= "3.1.1";
		$arrModule["name"] 			= "navigation";
		$arrModule["class_admin"] 	= "class_modul_navigation_admin";
		$arrModule["file_admin"] 	= "class_modul_navigation_admin.php";
		$arrModule["class_portal"] 	= "class_modul_navigation_portal";
		$arrModule["file_portal"] 	= "class_modul_navigation_portal.php";
		$arrModule["name_lang"] 	= "Module Navigation";
		$arrModule["moduleId"] 		= _navigation_modul_id_;
		$arrModule["tabellen"][]    = _dbprefix_."navigation";
		$arrModule["tabellen"][]    = _dbprefix_."elemente_navigation";

		parent::__construct($arrModule);
	}

	public function getNeededModules() {
	    return array("system", "pages");
	}
	
    public function getMinSystemVersion() {
	    return "3.0.9";
	}

	public function hasPostInstalls() {
	    $strQuery = "SELECT COUNT(*) FROM "._dbprefix_."element WHERE element_name='navigation'";
	    $arrRow = $this->objDB->getRow($strQuery);
        if($arrRow["COUNT(*)"] == 0)
            return true;

        return false;
	}

	public function getVersion() {
	    return $this->arrModule["version"];
	}


    public function install() {
		//Nur installieren, wenn noch nicht vorhanden
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
		$arrFields["navigation_target"] 	= array("char254", true);
		$arrFields["navigation_image"] 		= array("char254", true);

		if(!$this->objDB->createTable("navigation", $arrFields, array("navigation_id")))
			$strReturn .= "An error occured! ...\n";

		//register the module
		$strSystemID = $this->registerModule("navigation", _navigation_modul_id_, "class_modul_navigation_portal.php", "class_modul_navigation_admin.php", $this->arrModule["version"] , true);

		return $strReturn;

	}

	public function postInstall() {
		$strReturn = "";

		$strReturn .= "Installing navigation-element table...\n";
		
		$arrFields = array();
		$arrFields["content_id"] 			= array("char20", false);
		$arrFields["navigation_id"] 		= array("char20", true);
		$arrFields["navigation_template"] 	= array("char254", true);
		$arrFields["navigation_css"] 		= array("char254", true);
		$arrFields["navigation_mode"] 		= array("char254", true);
		
		if(!$this->objDB->createTable("element_navigation", $arrFields, array("content_id")))
			$strReturn .= "An error occured! ...\n";

		//Register the element
		$strReturn .= "Registering navigation-element...\n";
		//check, if not already existing
		$strQuery = "SELECT COUNT(*) FROM "._dbprefix_."element WHERE element_name='navigation'";
		$arrRow = $this->objDB->getRow($strQuery);
		if($arrRow["COUNT(*)"] == 0) {
			$strQuery = "INSERT INTO "._dbprefix_."element
							(element_id, element_name, element_class_portal, element_class_admin, element_repeat) VALUES
							('".$this->generateSystemid()."', 'navigation', 'class_element_navigation.php', 'class_element_navigation.php', 1)";
			$this->objDB->_query($strQuery);
			$strReturn .= "Element registered...\n";
		}
		else {
			$strReturn .= "Element already installed!...\n";
			}
		return $strReturn;
	}


	public function update() {
	    $strReturn = "";
        //check the version we have and to what version to update
        $arrModul = $this->getModuleData($this->arrModule["name"], false);

        $strReturn .= "Version found:\n\t Module: ".$arrModul["module_name"].", Version: ".$arrModul["module_version"]."\n\n";

        $arrModul = $this->getModuleData($this->arrModule["name"], false);
        if($arrModul["module_version"] == "3.0.0") {
            $strReturn .= $this->update_300_301();
        }

        $arrModul = $this->getModuleData($this->arrModule["name"], false);
        if($arrModul["module_version"] == "3.0.1") {
            $strReturn .= $this->update_301_302();
        }
        
	    $arrModul = $this->getModuleData($this->arrModule["name"], false);
        if($arrModul["module_version"] == "3.0.2") {
            $strReturn .= $this->update_302_309();
        }
        
		$arrModul = $this->getModuleData($this->arrModule["name"], false);
        if($arrModul["module_version"] == "3.0.9") {
            $strReturn .= $this->update_309_3095();
        }
        
	    $arrModul = $this->getModuleData($this->arrModule["name"], false);
        if($arrModul["module_version"] == "3.0.95") {
            $strReturn .= $this->update_3095_310();
        }

	    $arrModul = $this->getModuleData($this->arrModule["name"], false);
        if($arrModul["module_version"] == "3.1.0") {
            $strReturn .= $this->update_310_311();
        }
        
        return $strReturn."\n\n";
	}


	private function update_300_301() {
	    //Run the updates
	    $strReturn = "";
        $strReturn .= "Updating 3.0.0 to 3.0.1...\n";

        $strReturn .= "Updating module-versions...\n";
        $this->updateModuleVersion("navigation", "3.0.1");

        return $strReturn;
	}

	private function update_301_302() {
	    //Run the updates
	    $strReturn = "";
        $strReturn .= "Updating 3.0.1 to 3.0.2...\n";

        $strReturn .= "Updating module-versions...\n";
        $this->updateModuleVersion("navigation", "3.0.2");

        return $strReturn;
	}
	
    private function update_302_309() {
        //Run the updates
        $strReturn = "";
        $strReturn .= "Updating 3.0.2 to 3.0.9...\n";

        $strReturn .= "Updating module-versions...\n";
        $this->updateModuleVersion("navigation", "3.0.9");

        return $strReturn;
    }
    
	private function update_309_3095() {
        $strReturn = "";
        $strReturn .= "Updating 3.0.9 to 3.0.95...\n";

        $strReturn .= "Updating module-versions...\n";
        $this->updateModuleVersion("navigation", "3.0.95");

        return $strReturn;
    }
    
    private function update_3095_310() {
        $strReturn = "";
        $strReturn .= "Updating 3.0.95 to 3.1.0...\n";

        $strReturn .= "Updating module-versions...\n";
        $this->updateModuleVersion("navigation", "3.1.0");

        return $strReturn;
    }
    
    private function update_310_311() {
        $strReturn = "";
        $strReturn .= "Updating 3.1.0 to 3.1.1...\n";

        $strReturn .= "Updating module-versions...\n";
        $this->updateModuleVersion("navigation", "3.1.1");

        return $strReturn;
    }
}
?>