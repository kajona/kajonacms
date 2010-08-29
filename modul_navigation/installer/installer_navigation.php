<?php
/*"******************************************************************************************************
*   (c) 2004-2006 by MulchProductions, www.mulchprod.de                                                 *
*   (c) 2007-2010 by Kajona, www.kajona.de                                                              *
*       Published under the GNU LGPL v2.1, see /system/licence_lgpl.txt                                 *
*-------------------------------------------------------------------------------------------------------*
*   $Id$                                     *
********************************************************************************************************/

/**
 * Installer of the navigation
 *
 * @package modul_navigation
 */
class class_installer_navigation extends class_installer_base implements interface_installer {

	public function __construct() {
        $arrModule = array();
		$arrModule["version"] 		= "3.3.1";
		$arrModule["name"] 			= "navigation";
		$arrModule["name_lang"] 	= "Module Navigation";
		$arrModule["moduleId"] 		= _navigation_modul_id_;
		parent::__construct($arrModule);
	}

	public function getNeededModules() {
	    return array("system", "pages");
	}

    public function getMinSystemVersion() {
	    return "3.2.1";
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
		$strSystemID = $this->registerModule("navigation", _navigation_modul_id_, "class_modul_navigation_portal.php", "class_modul_navigation_admin.php", $this->arrModule["version"] , true, "", "class_modul_navigation_admin_xml.php");

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
		    $objElement = class_modul_pages_element::getElement("navigation");
		}
		catch (class_exception $objEx)  {
		}
		if($objElement == null) {
		    $objElement = new class_modul_pages_element();
		    $objElement->setStrName("navigation");
		    $objElement->setStrClassAdmin("class_element_navigation.php");
		    $objElement->setStrClassPortal("class_element_navigation.php");
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
        $arrModul = $this->getModuleData($this->arrModule["name"], false);

        $strReturn .= "Version found:\n\t Module: ".$arrModul["module_name"].", Version: ".$arrModul["module_version"]."\n\n";

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

        $arrModul = $this->getModuleData($this->arrModule["name"], false);
        if($arrModul["module_version"] == "3.2.1") {
            $strReturn .= $this->update_321_3291();
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

        return $strReturn."\n\n";
	}

    private function update_310_311() {
        $strReturn = "Updating 3.1.0 to 3.1.1...\n";
        $strReturn .= "Updating module-versions...\n";
        $this->updateModuleVersion("navigation", "3.1.1");

        return $strReturn;
    }

    private function update_311_319() {
        $strReturn = "Updating 3.1.1 to 3.1.9...\n";
        $strReturn .= "Removing css-column from element-table...\n";
        $strQuery = "ALTER TABLE `"._dbprefix_."element_navigation`
                        DROP `navigation_css`;";
        if(!$this->objDB->_query($strQuery))
            $strReturn .= "An error occured!!!\n";

        $strReturn .= "Updating module-versions...\n";
        $this->updateModuleVersion("navigation", "3.1.9");

        return $strReturn;
    }

    private function update_319_3195() {
        $strReturn = "Updating 3.1.9 to 3.1.95...\n";
        $strReturn .= "Updating module-versions...\n";
        $this->updateModuleVersion("navigation", "3.1.95");
        return $strReturn;
    }

    private function update_3195_320() {
        $strReturn = "Updating 3.1.95 to 3.2.0...\n";
        $strReturn .= "Updating module-versions...\n";
        $this->updateModuleVersion("navigation", "3.2.0");
        return $strReturn;
    }

    private function update_320_3209() {
        $strReturn = "Updating 3.2.0 to 3.2.0.9...\n";
        $strReturn .= "Updating module-versions...\n";

        $strReturn .= "Installing table navigation_cache...\n";

		$arrFields = array();
		$arrFields["navigation_cache_id"] 		= array("char20", false);
		$arrFields["navigation_cache_page"] 	= array("char254", true);
		$arrFields["navigation_cache_userid"] 	= array("char20", true);
		$arrFields["navigation_cache_checksum"] = array("char254", true);
		$arrFields["navigation_cache_content"] 	= array("text", true);

		if(!$this->objDB->createTable("navigation_cache", $arrFields, array("navigation_cache_id")))
			$strReturn .= "An error occured! ...\n";

        $strReturn .= "Registering systemsetting...\n";
        $this->registerConstant("_navigation_use_cache_", "false", class_modul_system_setting::$int_TYPE_BOOL, _navigation_modul_id_);

        $this->updateModuleVersion("navigation", "3.2.0.9");
        return $strReturn;
    }

    private function update_3209_321() {
        $strReturn = "Updating 3.2.0.9 to 3.2.1...\n";
        $strReturn .= "Updating module-versions...\n";
        $this->updateModuleVersion("navigation", "3.2.1");
        $strReturn .= "Updating module-versions...\n";
        $this->updateElementVersion("navigation", "3.2.1");
        return $strReturn;
    }

    private function update_321_3291() {
        $strReturn = "Updating 3.2.1 to 3.2.91...\n";

        $strReturn .= "Reorganizing navigations...\n";

        $strQuery = "SELECT module_id
                       FROM "._dbprefix_."system_module
                      WHERE module_nr = "._navigation_modul_id_."";
        $arrEntries = $this->objDB->getRow($strQuery);
        $strModuleId = $arrEntries["module_id"];

        $strQuery = "SELECT system_id
                       FROM "._dbprefix_."system
                      WHERE system_prev_id='0'
                        AND system_module_nr = "._navigation_modul_id_."
                        AND system_id != '".dbsafeString($strModuleId)."'";
        $arrEntries = $this->objDB->getArray($strQuery);

        foreach($arrEntries as $arrSingleRow) {
            $strReturn .= " ...updating navigation ".$arrSingleRow["system_id"]."";
            $strQuery = "UPDATE "._dbprefix_."system
                            SET system_prev_id = '".dbsafeString($strModuleId)."'
                          WHERE system_id = '".dbsafeString($arrSingleRow["system_id"])."'";
            if($this->objDB->_query($strQuery))
                $strReturn .= " ...ok\n";
            else
                $strReturn .= " ...failed!!!\n";
        }



        $strReturn .= "Updating module-versions...\n";
        $this->updateModuleVersion("navigation", "3.2.91");
        $strReturn .= "Updating module-versions...\n";
        $this->updateElementVersion("navigation", "3.2.91");
        return $strReturn;
    }

    private function update_3291_3292() {
        $strReturn = "Updating 3.2.91 to 3.2.92...\n";

        $strReturn .= "Registering admin-xml class...\n";
        $objModule = class_modul_system_module::getModuleByName("navigation", true);
        $objModule->setStrXmlNameAdmin("class_modul_navigation_admin_xml.php");
        if(!$objModule->updateObjectToDb())
            $strReturn .= "An error occured!\n";



        $strReturn .= "Updating module-versions...\n";
        $this->updateModuleVersion("navigation", "3.2.92");
        $strReturn .= "Updating element-versions...\n";
        $this->updateElementVersion("navigation", "3.2.92");
        return $strReturn;
    }

    private function update_3292_330() {
        $strReturn = "Updating 3.2.92 to 3.3.0...\n";
        $strReturn .= "Updating module-versions...\n";
        $this->updateModuleVersion("navigation", "3.3.0");
        $strReturn .= "Updating element-versions...\n";
        $this->updateElementVersion("navigation", "3.3.0");
        return $strReturn;
    }

    private function update_330_3301() {
        $strReturn = "Updating 3.3.0 to 3.3.0.1..\n";

        $strReturn .= "Setting cache-timeouts for navigations...\n";
        $strQuery = "UPDATE "._dbprefix_."element
                        SET element_cachetime=3600
                      WHERE element_class_admin = 'class_element_navigation.php'";
        if(!$this->objDB->_query($strQuery))
			$strReturn .= "An error occured! ...\n";

        $strReturn .= "Dropping navigations cache...\n";
        $strQuery = "DROP TABLE "._dbprefix_."navigation_cache";
        if(!$this->objDB->_query($strQuery))
			$strReturn .= "An error occured! ...\n";

        $strReturn .= "Deleting navigation cache setting...\r";
        $strQuery = "DELETE FROM "._dbprefix_."system_config
                           WHERE system_config_name = '_navigation_use_cache_'";
        if(!$this->objDB->_query($strQuery))
			$strReturn .= "An error occured! ...\n";

        $strReturn .= "Updating module-versions...\n";
        $this->updateModuleVersion("navigation", "3.3.0.1");
        $strReturn .= "Updating element-versions...\n";
        $this->updateElementVersion("navigation", "3.3.0.1");
        return $strReturn;
    }

    private function update_3301_331() {
        $strReturn = "Updating 3.3.0.1 to 3.3.1..\n";

        $strReturn .= "Deleting old systemtasks...\n";
        $objFilesystem = new class_filesystem();
        if(!$objFilesystem->fileDelete("/admin/systemtasks/class_systemtask_flushnavigationcache.php"))
            $strReturn .= "Deletion failed\n";

        $strReturn .= "Updating module-versions...\n";
        $this->updateModuleVersion("navigation", "3.3.1");
        $strReturn .= "Updating element-versions...\n";
        $this->updateElementVersion("navigation", "3.3.1");
        return $strReturn;
    }
}
?>