<?php
/*"******************************************************************************************************
*   (c) 2004-2006 by MulchProductions, www.mulchprod.de                                                 *
*   (c) 2007-2009 by Kajona, www.kajona.de                                                              *
*       Published under the GNU LGPL v2.1, see /system/licence_lgpl.txt                                 *
*-------------------------------------------------------------------------------------------------------*
*	$Id$                                      *
********************************************************************************************************/

require_once(_systempath_."/class_installer_base.php");
require_once(_systempath_."/interface_installer.php");

/**
 * Installer to install the downloads-module
 *
 * @package modul_downloads
 */
class class_installer_downloads extends class_installer_base implements interface_installer {

	public function __construct() {
		$arrModule["version"] 		= "3.1.1";
		$arrModule["name"] 			= "downloads";
		$arrModule["class_admin"] 	= "class_modul_downloads_admin";
		$arrModule["file_admin"] 	= "class_modul_downloads_admin.php";
		$arrModule["class_portal"] 	= "class_modul_downloads_portal";
		$arrModule["file_portal"] 	= "class_modul_downloads_portal.php";
		$arrModule["name_lang"] 	= "Module Downloads";
		$arrModule["moduleId"] 		= _downloads_modul_id_;
		$arrModule["tabellen"][]    = _dbprefix_."downloads_file";
		$arrModule["tabellen"][]    = _dbprefix_."downloads_log";
		$arrModule["tabellen"][]    = _dbprefix_."downloads_archive";
		parent::__construct($arrModule);
	}

	public function getNeededModules() {
	    return array("system", "pages");
	}
	
    public function getMinSystemVersion() {
	    return "3.0.9";
	}

	public function hasPostInstalls() {
	    $strQuery = "SELECT COUNT(*) FROM "._dbprefix_."element WHERE element_name='downloads'";
	    $arrRow = $this->objDB->getRow($strQuery);
        if($arrRow["COUNT(*)"] == 0)
            return true;

        return false;
	}


   public function install() {
		$strReturn = "";

		//downloads_file-------------------------------------------------------------------------------------
		$strReturn .= "Installing table downloads_file...\n";

		$arrFields = array();
		$arrFields["downloads_id"] 			= array("char20", false);
		$arrFields["downloads_name"] 		= array("char254", true);
		$arrFields["downloads_filename"] 	= array("char254", true);
		$arrFields["downloads_description"] = array("text", true);
		$arrFields["downloads_size"] 		= array("int", true);
		$arrFields["downloads_hits"]	 	= array("int", true);
		$arrFields["downloads_type"]	 	= array("int", true);
		$arrFields["downloads_max_kb"] 		= array("int", true);
		
		if(!$this->objDB->createTable("downloads_file", $arrFields, array("downloads_id")))
			$strReturn .= "An error occured! ...\n";

		//downloads_log----------------------------------------------------------------------------------
		$strReturn .= "Installing table downloads_log...\n";
		
		$arrFields = array();
		$arrFields["downloads_log_id"] 		= array("char20", false);
		$arrFields["downloads_log_date"] 	= array("int", true);
		$arrFields["downloads_log_file"] 	= array("char254", true);
		$arrFields["downloads_log_user"] 	= array("char20", true);
		$arrFields["downloads_log_ip"] 		= array("char20", true);

		if(!$this->objDB->createTable("downloads_log", $arrFields, array("downloads_log_id")))
			$strReturn .= "An error occured! ...\n";

		//downloads_archive----------------------------------------------------------------------------------
		$strReturn .= "Installing table downloads_archive...\n";
		
		$arrFields = array();
		$arrFields["archive_id"] 		= array("char20", false);
		$arrFields["archive_path"] 		= array("char254", true);
		$arrFields["archive_title"] 	= array("char254", true);
		
		if(!$this->objDB->createTable("downloads_archive", $arrFields, array("archive_id")))
			$strReturn .= "An error occured! ...\n";


		//register the module
		$strSystemID = $this->registerModule("downloads", _downloads_modul_id_, "class_modul_downloads_portal.php", "class_modul_downloads_admin.php", $this->arrModule["version"] , true);

		$strReturn .= "Registering system-constants...\n";
		//Number of rows in the login-log
		$this->registerConstant("_downloads_suche_seite_", "downloads", class_modul_system_setting::$int_TYPE_PAGE, _downloads_modul_id_);

		return $strReturn;

	}

	public function postInstall() {
		$strReturn = "";

		//Table for page-element
		$strReturn .= "Installing downloads-element table...\n";
		
		$arrFields = array();
		$arrFields["content_id"] 		= array("char20", false);
		$arrFields["download_id"] 		= array("char20", true);
		$arrFields["download_template"] = array("char254", true);
		
		if(!$this->objDB->createTable("element_downloads", $arrFields, array("content_id")))
			$strReturn .= "An error occured! ...\n";

		//Register the element
		$strReturn .= "Registering downloads-element...\n";
		//check, if not already existing
		$strQuery = "SELECT COUNT(*) FROM "._dbprefix_."element WHERE element_name='downloads'";
		$arrRow = $this->objDB->getRow($strQuery);
		if($arrRow["COUNT(*)"] == 0) {
			$strQuery = "INSERT INTO "._dbprefix_."element
							(element_id, element_name, element_class_portal, element_class_admin, element_repeat) VALUES
							('".$this->generateSystemid()."', 'downloads', 'class_element_downloads.php', 'class_element_downloads.php', 1)";
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

        //Update the module-records
        $strReturn .= "Updating module-versions...\n";
        $this->updateModuleVersion("downloads", "3.0.1");

        return $strReturn;
	}

	private function update_301_302() {
	    //Run the updates
	    $strReturn = "";
        $strReturn .= "Updating 3.0.1 to 3.0.2...\n";

        //Update the module-records
        $strReturn .= "Updating module-versions...\n";
        $this->updateModuleVersion("downloads", "3.0.2");

        return $strReturn;
	}

    private function update_302_309() {
	    //Run the updates
	    $strReturn = "";
        $strReturn .= "Updating 3.0.2 to 3.0.9...\n";

        //Update the module-records
        $strReturn .= "Updating module-versions...\n";
        $this->updateModuleVersion("downloads", "3.0.9");

        return $strReturn;
	}
	
	private function update_309_3095() {
	    $strReturn = "";
        $strReturn .= "Updating 3.0.9 to 3.0.95...\n";

        //Update the module-records
        $strReturn .= "Updating module-versions...\n";
        $this->updateModuleVersion("downloads", "3.0.95");

        return $strReturn;
	}
	
    private function update_3095_310() {
        $strReturn = "Updating 3.0.95 to 3.1.0...\n";
        $strReturn .= "Updating module-versions...\n";
        $this->updateModuleVersion("downloads", "3.1.0");

        return $strReturn;
    }


    private function update_310_311() {
        $strReturn = "Updating 3.1.0 to 3.1.1...\n";
        
        $strReturn .= "Updating module-versions...\n";
        $this->updateModuleVersion("downloads", "3.1.1");

        return $strReturn;
    }
    
}
?>