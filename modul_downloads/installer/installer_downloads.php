<?php
/*"******************************************************************************************************
*   (c) 2004-2006 by MulchProductions, www.mulchprod.de                                                 *
*   (c) 2007-2011 by Kajona, www.kajona.de                                                              *
*       Published under the GNU LGPL v2.1, see /system/licence_lgpl.txt                                 *
*-------------------------------------------------------------------------------------------------------*
*	$Id$                                      *
********************************************************************************************************/

/**
 * Installer to install the downloads-module
 *
 * @package modul_downloads
 */
class class_installer_downloads extends class_installer_base implements interface_installer {

	public function __construct() {
        $arrModule = array();
		$arrModule["version"] 		= "3.4.0";
		$arrModule["name"] 			= "downloads";
		$arrModule["name_lang"] 	= "Module Downloads";
		$arrModule["moduleId"] 		= _downloads_modul_id_;
		parent::__construct($arrModule);
	}

	public function getNeededModules() {
	    return array("system", "pages");
	}

    public function getMinSystemVersion() {
	    return "3.3.1.8";
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
		$arrFields["downloads_cattype"]	 	= array("int", true);
		$arrFields["downloads_checksum"]	= array("char254", true);
		$arrFields["downloads_max_kb"] 		= array("int", true);
        $arrFields["downloads_screen_1"] 	= array("char254", true);
        $arrFields["downloads_screen_2"] 	= array("char254", true);
        $arrFields["downloads_screen_3"] 	= array("char254", true);

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
		$this->registerModule("downloads", _downloads_modul_id_, "class_modul_downloads_portal.php", "class_modul_downloads_admin.php", $this->arrModule["version"] , true, "", "class_modul_downloads_admin_xml.php");

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
		$arrFields["download_amount"]   = array("int", true);

		if(!$this->objDB->createTable("element_downloads", $arrFields, array("content_id")))
			$strReturn .= "An error occured! ...\n";

		//Register the element
		$strReturn .= "Registering downloads-element...\n";
		//check, if not already existing
        $objElement = null;
        try {
            $objElement = class_modul_pages_element::getElement("downloads");
        }
        catch (class_exception $objEx)  {
        }
        if($objElement == null) {
            $objElement = new class_modul_pages_element();
            $objElement->setStrName("downloads");
            $objElement->setStrClassAdmin("class_element_downloads.php");
            $objElement->setStrClassPortal("class_element_downloads.php");
            $objElement->setIntCachetime(3600);
            $objElement->setIntRepeat(1);
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
        $arrModul = $this->getModuleData($this->arrModule["name"], false);

        $strReturn .= "Version found:\n\t Module: ".$arrModul["module_name"].", Version: ".$arrModul["module_version"]."\n\n";

        $arrModul = $this->getModuleData($this->arrModule["name"], false);
        if($arrModul["module_version"] == "3.2.0") {
            $strReturn .= $this->update_320_3209();
        }

        $arrModul = $this->getModuleData($this->arrModule["name"], false);
        if($arrModul["module_version"] == "3.2.0.9") {
            $strReturn .= $this->update_3209_321();
        }

        $arrModul = $this->getModuleData($this->arrModule["name"], false);
        if($arrModul["module_version"] == "3.2.1") {
            $strReturn .= $this->update_321_3219();
        }

        $arrModul = $this->getModuleData($this->arrModule["name"], false);
        if($arrModul["module_version"] == "3.2.1.9") {
            $strReturn .= $this->update_3219_3291();
        }

        $arrModul = $this->getModuleData($this->arrModule["name"], false);
        if($arrModul["module_version"] == "3.2.1.9") {
            $strReturn .= $this->update_3219_3291();
        }

        $arrModul = $this->getModuleData($this->arrModule["name"], false);
        if($arrModul["module_version"] == "3.2.91") {
            $strReturn .= $this->update_3291_3292();
        }

        $arrModul = $this->getModuleData($this->arrModule["name"], false);
        if($arrModul["module_version"] == "3.2.92") {
            $strReturn .= $this->update_3292_330();
        }

        $arrModul = $this->getModuleData($this->arrModule["name"], false);
        if($arrModul["module_version"] == "3.3.0") {
            $strReturn .= $this->update_330_3301();
        }

        $arrModul = $this->getModuleData($this->arrModule["name"], false);
        if($arrModul["module_version"] == "3.3.0.1") {
            $strReturn .= $this->update_3301_331();
        }

        $arrModul = $this->getModuleData($this->arrModule["name"], false);
        if($arrModul["module_version"] == "3.3.1") {
            $strReturn .= $this->update_331_3318();
        }

        $arrModul = $this->getModuleData($this->arrModule["name"], false);
        if($arrModul["module_version"] == "3.3.1.8") {
            $strReturn .= $this->update_3318_340();
        }

        return $strReturn."\n\n";
	}

    private function update_320_3209() {
        $strReturn = "Updating 3.2.0 to 3.2.0.9...\n";

        $strReturn .= "Adding downloads_cattype column to db-schema...\n";
        $strSql = "ALTER TABLE ".$this->objDB->encloseTableName(_dbprefix_."downloads_file")."
        	               ADD ".$this->objDB->encloseColumnName("downloads_cattype")." int NULL ";

        if(!$this->objDB->_query($strSql))
            $strReturn .= "An error occured!\n";

        $strReturn .= "Updating module-versions...\n";
        $this->updateModuleVersion("downloads", "3.2.0.9");
        return $strReturn;
    }

    private function update_3209_321() {
        $strReturn = "Updating 3.2.0.9 to 3.2.1...\n";
        $strReturn .= "Updating module-versions...\n";
        $this->updateModuleVersion("downloads", "3.2.1");
        $strReturn .= "Updating element-versions...\n";
        $this->updateElementVersion("downloads", "3.2.1");
        return $strReturn;
    }

    private function update_321_3219() {
        $strReturn = "Updating 3.2.1 to 3.2.1.9...\n";

        $strReturn .= "Adding screen-rows column to db-schema...\n";
        $strSql = "ALTER TABLE ".$this->objDB->encloseTableName(_dbprefix_."downloads_file")."
        	               ADD ".$this->objDB->encloseColumnName("downloads_screen_1")." ".$this->objDB->getDatatype("char254")." NULL,
                           ADD ".$this->objDB->encloseColumnName("downloads_screen_2")." ".$this->objDB->getDatatype("char254")." NULL,
                           ADD ".$this->objDB->encloseColumnName("downloads_screen_3")." ".$this->objDB->getDatatype("char254")." NULL  ";

        if(!$this->objDB->_query($strSql))
            $strReturn .= "An error occured!\n";

        $strReturn .= "Updating module-versions...\n";
        $this->updateModuleVersion("downloads", "3.2.1.9");
        $strReturn .= "Updating element-versions...\n";
        $this->updateElementVersion("downloads", "3.2.1.9");
        return $strReturn;
    }

    private function update_3219_3291() {
        $strReturn = "Updating 3.2.1.9 to 3.2.91...\n";

        $strReturn .= "Reorganizing download-archives...\n";

        $strQuery = "SELECT module_id
                       FROM "._dbprefix_."system_module
                      WHERE module_nr = "._downloads_modul_id_."";
        $arrEntries = $this->objDB->getRow($strQuery);
        $strModuleId = $arrEntries["module_id"];

        $strQuery = "SELECT archive_id
                       FROM "._dbprefix_."downloads_archive";
        $arrEntries = $this->objDB->getArray($strQuery);

        foreach($arrEntries as $arrSingleRow) {
            $strReturn .= " ...updating archive ".$arrSingleRow["archive_id"]."";
            $strQuery = "UPDATE "._dbprefix_."system
                            SET system_prev_id = '".dbsafeString($strModuleId)."'
                          WHERE system_id = '".dbsafeString($arrSingleRow["archive_id"])."'";
            if($this->objDB->_query($strQuery))
                $strReturn .= " ...ok\n";
            else
                $strReturn .= " ...failed!!!\n";
        }

        $strReturn .= "Updating module-versions...\n";
        $this->updateModuleVersion("downloads", "3.2.91");
        $strReturn .= "Updating element-versions...\n";
        $this->updateElementVersion("downloads", "3.2.91");
        return $strReturn;
    }


    private function update_3291_3292() {
        $strReturn = "Updating 3.2.9.1 to 3.2.92...\n";

        $strReturn .= "Extending element table...\n";
        $strSql = "ALTER TABLE ".$this->objDB->encloseTableName(_dbprefix_."element_downloads")."
        	               ADD ".$this->objDB->encloseColumnName("download_amount")." ".$this->objDB->getDatatype("int")." NULL ";

        if(!$this->objDB->_query($strSql))
            $strReturn .= "An error occured!\n";


        $strReturn .= "Updating module-versions...\n";
        $this->updateModuleVersion("downloads", "3.2.92");
        $strReturn .= "Updating element-versions...\n";
        $this->updateElementVersion("downloads", "3.2.92");
        return $strReturn;
    }

    private function update_3292_330() {
        $strReturn = "Updating 3.2.9.2 to 3.3.0...\n";
        $strReturn .= "Updating module-versions...\n";
        $this->updateModuleVersion("downloads", "3.3.0");
        $strReturn .= "Updating element-versions...\n";
        $this->updateElementVersion("downloads", "3.3.0");
        return $strReturn;
    }

    private function update_330_3301() {
        $strReturn = "Updating 3.3.0 to 3.3.0.1...\n";
        $strReturn .= "Setting cache-timeouts for downloads-element...\n";
        $strQuery = "UPDATE "._dbprefix_."element
                        SET element_cachetime=3600
                      WHERE element_class_admin = 'class_element_downloads.php'";
        if(!$this->objDB->_query($strQuery))
            $strReturn .= "An error occured! ...\n";
        $strReturn .= "Updating module-versions...\n";
        $this->updateModuleVersion("downloads", "3.3.0.1");
        $strReturn .= "Updating element-versions...\n";
        $this->updateElementVersion("downloads", "3.3.0.1");
        return $strReturn;
    }


    private function update_3301_331() {
        $strReturn = "Updating 3.3.0.1 to 3.3.1...\n";
        $strReturn .= "Updating module-versions...\n";
        $this->updateModuleVersion("downloads", "3.3.1");
        $strReturn .= "Updating element-versions...\n";
        $this->updateElementVersion("downloads", "3.3.1");
        return $strReturn;
    }

    private function update_331_3318() {
        $strReturn = "Updating 3.3.1 to 3.3.1.8...\n";
        $strReturn .= "Updating module-versions...\n";
        $this->updateModuleVersion("downloads", "3.3.1.8");
        $strReturn .= "Updating element-versions...\n";
        $this->updateElementVersion("downloads", "3.3.1.8");
        return $strReturn;
    }

    private function update_3318_340() {
        $strReturn = "Updating 3.3.1.8 to 3.4.0...\n";
        $strReturn .= "Updating module-versions...\n";
        $this->updateModuleVersion("downloads", "3.4.0");
        $strReturn .= "Updating element-versions...\n";
        $this->updateElementVersion("downloads", "3.4.0");
        return $strReturn;
    }

}
?>