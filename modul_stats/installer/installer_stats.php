<?php
/*"******************************************************************************************************
*   (c) 2004-2006 by MulchProductions, www.mulchprod.de                                                 *
*   (c) 2007-2009 by Kajona, www.kajona.de                                                              *
*       Published under the GNU LGPL v2.1, see /system/licence_lgpl.txt                                 *
*-------------------------------------------------------------------------------------------------------*
*	$Id$                                         *
********************************************************************************************************/

require_once(_systempath_."/class_installer_base.php");
require_once(_systempath_."/interface_installer.php");


/**
 * Installer handling the installation of the stats module
 *
 * @package modul_stats
 */
class class_installer_stats extends class_installer_base implements interface_installer {

    /**
     * Constructor
     *
     */
	public function __construct() {
        $arrModule = array();
		$arrModule["version"] 		= "3.2.1";
		$arrModule["name"] 			= "stats";
		$arrModule["class_admin"] 	= "class_modul_stats_admin";
		$arrModule["file_admin"] 	= "class_modul_stats_admin.php";
		$arrModule["class_portal"] 	= "class_modul_stats_portal";
		$arrModule["file_portal"] 	= "class_modul_stats_portal.php";
		$arrModule["name_lang"] 	= "Module Stats";
		$arrModule["moduleId"] 		= _stats_modul_id_;
		$arrModule["tabellen"][]    = _dbprefix_."stats_daten";
		parent::__construct($arrModule);

	}

	public function getNeededModules() {
	    return array("system", "pages");
	}
	
    public function getMinSystemVersion() {
	    return "3.2.0.9";
	}

	public function hasPostInstalls() {
        return false;
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

		//Stats table -----------------------------------------------------------------------------------
		$strReturn .= "Installing table stats...\n";
		
		$arrFields = array();
		$arrFields["stats_id"] 		= array("char20", false);
		$arrFields["stats_ip"] 		= array("char20", true);
		$arrFields["stats_hostname"]= array("char254", true);
		$arrFields["stats_date"] 	= array("int", true);
		$arrFields["stats_page"] 	= array("char254", true);
		$arrFields["stats_language"]= array("char10", true);
		$arrFields["stats_referer"] = array("char254", true);
		$arrFields["stats_browser"] = array("char254", true);
		$arrFields["stats_session"] = array("char100", true);

		if(!$this->objDB->createTable("stats_data", $arrFields, array("stats_id"), array("stats_date"), false))
			$strReturn .= "An error occured! ...\n";

        $strReturn .= "Installing table ip2country...\n";
        
        $arrFields = array();
		$arrFields["ip2c_ip"] 		= array("char20", false);
		$arrFields["ip2c_name"] 	= array("char100", false);

		if(!$this->objDB->createTable("stats_ip2country", $arrFields, array("ip2c_ip"), array(), false))
			$strReturn .= "An error occured! ...\n";


		//register module
		$this->registerModule("stats", _stats_modul_id_, "class_modul_stats_portal.php", "class_modul_stats_admin.php", $this->arrModule["version"], true);

		$strReturn .= "Registering system-constants...\n";
		//Number of rows in the login-log
		$this->registerConstant("_stats_nrofrecords_", "25", class_modul_system_setting::$int_TYPE_INT, _stats_modul_id_);
		$this->registerConstant("_stats_duration_online_", "300", class_modul_system_setting::$int_TYPE_INT, _stats_modul_id_);
		$this->registerConstant("_stats_exclusionlist_", _webpath_, class_modul_system_setting::$int_TYPE_STRING, _stats_modul_id_);
		return $strReturn;
	}

	public function postInstall() {
    }

    

	public function update() {
	    $strReturn = "";
        //check installed version and to which version we can update
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
        
	    $arrModul = $this->getModuleData($this->arrModule["name"], false);
        if($arrModul["module_version"] == "3.1.1") {
            $strReturn .= $this->update_311_319();
        }

        $arrModul = $this->getModuleData($this->arrModule["name"], false);
        if($arrModul["module_version"] == "3.1.9") {
            $strReturn .= $this->update_319_3195();
        }

        $arrModul = $this->getModuleData($this->arrModule["name"], false);
        if($arrModul["module_version"] == "3.1.95") {
            $strReturn .= $this->update_3195_320();
        }

        $arrModul = $this->getModuleData($this->arrModule["name"], false);
        if($arrModul["module_version"] == "3.2.0") {
            $strReturn .= $this->update_320_3209();
        }

        $arrModul = $this->getModuleData($this->arrModule["name"], false);
        if($arrModul["module_version"] == "3.2.0.9") {
            $strReturn .= $this->update_3209_321();
        }

        return $strReturn."\n\n";
	}

	private function update_300_301() {
	    $strReturn = "";
        $strReturn .= "Updating 3.0.0 to 3.0.1...\n";
        $strReturn .= "Updating module-versions...\n";
        $this->updateModuleVersion("stats", "3.0.1");

        return $strReturn;
	}


    private function update_301_302() {
	    $strReturn = "";
        $strReturn .= "Updating 3.0.1 to 3.0.2...\n";

        $strReturn .= "Installing table ip2country...\n";

		$arrFields = array();
		$arrFields["ip_from"] 		= array("double", false);
		$arrFields["ip_to"] 		= array("double", false);
		$arrFields["country_code2"] = array("char10", false);
		$arrFields["country_code3"] = array("char10", false);
		$arrFields["country_name"] 	= array("char100", false);

		if(!$this->objDB->createTable("stats_ip2country", $arrFields, array("ip_from", "ip_to"), array(), false))
			$strReturn .= "An error occured! ...\n";


        $strReturn .= "Updating module-versions...\n";
        $this->updateModuleVersion("stats", "3.0.2");

        return $strReturn;
	}
	
    private function update_302_309() {
        $strReturn = "";
        $strReturn .= "Updating 3.0.2 to 3.0.9...\n";
        
        $strReturn .= "Updating module-versions...\n";
        $this->updateModuleVersion("stats", "3.0.9");

        return $strReturn;
    }
    
	private function update_309_3095() {
        $strReturn = "";
        $strReturn .= "Updating 3.0.9 to 3.0.95...\n";
        
        $strReturn .= "Updating module-versions...\n";
        $this->updateModuleVersion("stats", "3.0.95");

        return $strReturn;
    }
    
    private function update_3095_310() {
        $strReturn = "";
        $strReturn .= "Updating 3.0.95 to 3.1.0...\n";
        
        $strReturn .= "Removing old ip2country table...\n";
        $strQuery = "DROP TABLE "._dbprefix_."stats_ip2country";
        if(!$this->objDB->_query($strQuery))
            $strReturn .= "An error occured! ... \n";
        
        $strReturn .= "Creating new ip2country cache-table...\n";
       
        $arrFields = array();
        $arrFields["ip2c_ip"]       = array("char20", false);
        $arrFields["ip2c_name"]     = array("char100", false);

        if(!$this->objDB->createTable("stats_ip2country", $arrFields, array("ip2c_ip"), array(), false))
            $strReturn .= "An error occured! ...\n";
        
        
        $strReturn .= "Updating module-versions...\n";
        $this->updateModuleVersion("stats", "3.1.0");

        return $strReturn;
    }
    
    private function update_310_311() {
        $strReturn = "";
        $strReturn .= "Updating 3.1.0 to 3.1.1...\n";
        $strReturn .= "Updating module-versions...\n";
        $this->updateModuleVersion("stats", "3.1.1");

        return $strReturn;
    }
    
    private function update_311_319() {
        $strReturn = "";
        $strReturn .= "Updating 3.1.1 to 3.1.9...\n";

        $strReturn .= "Updating system-constants...\n";
        $objConstant = class_modul_system_setting::getConfigByName("_stats_anzahl_liste_");
        $objConstant->renameConstant("_stats_nrofrecords_");
        
        $objConstant = class_modul_system_setting::getConfigByName("_stats_zeitraum_online_");
        $objConstant->renameConstant("_stats_duration_online_");
        
        $objConstant = class_modul_system_setting::getConfigByName("_stats_ausschluss_");
        $objConstant->renameConstant("_stats_exclusionlist_");
       
        $strReturn .= "Updating module-versions...\n";
        $this->updateModuleVersion("stats", "3.1.9");
        return $strReturn;
    }

    private function update_319_3195() {
        $strReturn = "";
        $strReturn .= "Updating 3.1.9 to 3.1.95...\n";
        $strReturn .= "Updating module-versions...\n";
        $this->updateModuleVersion("stats", "3.1.95");
        return $strReturn;
    }
    
    private function update_3195_320() {
        $strReturn = "";
        $strReturn .= "Updating 3.1.95 to 3.2.0...\n";
        $strReturn .= "Updating module-versions...\n";
        $this->updateModuleVersion("stats", "3.2.0");
        return $strReturn;
    }

    private function update_320_3209() {
        $strReturn = "";
        $strReturn .= "Updating 3.2.0 to 3.2.0.9...\n";
        $strReturn .= "Updating module-versions...\n";
        $this->updateModuleVersion("stats", "3.2.0.9");
        return $strReturn;
    }

    private function update_3209_321() {
        $strReturn = "";
        $strReturn .= "Updating 3.2.0.9 to 3.2.1...\n";
        $strReturn .= "Updating module-versions...\n";
        $this->updateModuleVersion("stats", "3.2.1");
        return $strReturn;
    }

    
}
?>