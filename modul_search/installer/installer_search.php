<?php
/*"******************************************************************************************************
*   (c) 2004-2006 by MulchProductions, www.mulchprod.de                                                 *
*   (c) 2007 by Kajona, www.kajona.de                                                                   *
*       Published under the GNU LGPL v2.1, see /system/licence_lgpl.txt                                 *
*-------------------------------------------------------------------------------------------------------*
* 																										*
* 	installer_search.php																				*
* 	Installer providing the installation of the search module											*																										*
*																										*
*-------------------------------------------------------------------------------------------------------*
*	$Id$                                         *
********************************************************************************************************/

require_once(_systempath_."/class_installer_base.php");
require_once(_systempath_."/interface_installer.php");


/**
 * Class providing the installer of the search-module
 *
 * @package modul_search
 */
class class_installer_search extends class_installer_base implements interface_installer {
	/**
	 * Constructor
	 *
	 */
    public function __construct() {
		$arrModule["version"] 		= "3.0.2";
		$arrModule["name"] 			= "search";
		$arrModule["class_admin"] 	= "";
		$arrModule["file_admin"] 	= "";
		$arrModule["class_portal"] 	= "class_modul_search_portal";
		$arrModule["file_portal"] 	= "class_modul_search_portal.php";
		$arrModule["name_lang"] 	= "Module Search";
		$arrModule["moduleId"] 		= _suche_modul_id_;
		$arrModule["tabellen"][]    = _dbprefix_."element_search";
		parent::__construct($arrModule);
	}

	public function getNeededModules() {
	    return array("system", "pages");
	}
	
    public function getMinSystemVersion() {
	    return "3.0.2";
	}

	public function hasPostInstalls() {
	    $strQuery = "SELECT COUNT(*) FROM "._dbprefix_."element WHERE element_name='search'";
	    $arrRow = $this->objDB->getRow($strQuery);
        if($arrRow["COUNT(*)"] == 0)
            return true;

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
		$strReturn .= "Installing search-log table...\n";
		$strQuery = "CREATE TABLE IF NOT EXISTS `"._dbprefix_."search_log` (
                        `search_log_id` VARCHAR( 20 ) NOT NULL ,
                        `search_log_date` INT ,
                        `search_log_query` VARCHAR( 255 ),
                        PRIMARY KEY ( `search_log_id` )
                        ) ";
		if(!$this->objDB->createTable($strQuery))
			$strReturn .= "An error occured! ...\n";

		$strReturn .= "Registering module...\n";
		//register the module
		$strSystemID = $this->registerModule("search", _suche_modul_id_, "class_modul_search_portal", "class_modul_search_portal.php", "", "", $this->arrModule["version"] , false, "class_modul_search_portal_xml.php");

		return $strReturn;

	}

	public function postInstall() {
		$strReturn = "";

		//Table for page-element
		$strReturn .= "Installing search-element table...\n";
		$strQuery = "CREATE TABLE IF NOT EXISTS `"._dbprefix_."element_search` (
                        `content_id` VARCHAR( 20 ) NOT NULL ,
                        `search_template` VARCHAR( 255 ) ,
                        `search_amount` INT,
                        `search_page` VARCHAR( 255 ) ,
                        PRIMARY KEY ( `content_id` )
                        ) ";
		if(!$this->objDB->createTable($strQuery))
			$strReturn .= "An error occured! ...\n";

		//Register the element
		$strReturn .= "Registering search-element...\n";
		//check, if not already existing
		$strQuery = "SELECT COUNT(*) FROM "._dbprefix_."element WHERE element_name='search'";
		$arrRow = $this->objDB->getRow($strQuery);
		if($arrRow["COUNT(*)"] == 0) {
			$strQuery = "INSERT INTO "._dbprefix_."element
							(element_id, element_name, element_class_portal, element_class_admin, element_repeat) VALUES
							('".$this->generateSystemid()."', 'search', 'class_element_search.php', 'class_element_search.php', 1)";
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
        if($arrModul["module_version"] == "2.2.0.0") {
            $strReturn .= $this->update_2200_221();
        }

        $arrModul = $this->getModuleData($this->arrModule["name"], false);
        if($arrModul["module_version"] == "2.2.1") {
            $strReturn .= $this->update_221_300();
        }

        $arrModul = $this->getModuleData($this->arrModule["name"], false);
        if($arrModul["module_version"] == "3.0.0") {
            $strReturn .= $this->update_300_301();
        }
        
        $arrModul = $this->getModuleData($this->arrModule["name"], false);
        if($arrModul["module_version"] == "3.0.1") {
            $strReturn .= $this->update_301_302();
        }

        return $strReturn."\n\n";
	}

	private function update_2200_221() {
	    //Run the updates
	    $strReturn = "";
        $strReturn .= "Updating 2.2.0.0 to 2.2.1...\n";

         $strReturn .= "Adding xml-classes to module...\n";
		 $strQuery = "UPDATE "._dbprefix_."system_module
		            SET module_xmlfilenameportal = 'class_modul_search_portal_xml.php',
		                module_xmlfilenameadmin = ''
		            WHERE module_name = 'search'";

		if(!$this->objDB->_query($strQuery))
			$strReturn .= "An error occured! ...\n";


        $strReturn .= "Updating module-versions...\n";
        $this->updateModuleVersion("search", "2.2.1");

        return $strReturn;
	}

	private function update_221_300() {
	    //Run the updates
	    $strReturn = "";
        $strReturn .= "Updating 2.2.0.0 to 3.0.0...\n";

        $strReturn .= "Updating module-versions...\n";
        $this->updateModuleVersion("search", "3.0.0");

        return $strReturn;
	}

	private function update_300_301() {
	    //Run the updates
	    $strReturn = "";
        $strReturn .= "Updating 3.0.0 to 3.0.1...\n";

        $strReturn .= "Updating module-versions...\n";
        $this->updateModuleVersion("search", "3.0.1");

        return $strReturn;
	}
	
	
	private function update_301_302() {
	    //Run the updates
	    $strReturn = "";
        $strReturn .= "Updating 3.0.1 to 3.0.2...\n";
        
        //Tabellen anlegen
		$strReturn .= "Installing search-log table...\n";
		$strQuery = "CREATE TABLE IF NOT EXISTS `"._dbprefix_."search_log` (
                        `search_log_id` VARCHAR( 20 ) NOT NULL ,
                        `search_log_date` INT ,
                        `search_log_query` VARCHAR( 255 ),
                        PRIMARY KEY ( `search_log_id` )
                        ) ";
		if(!$this->objDB->createTable($strQuery))
			$strReturn .= "An error occured! ...\n";


        $strReturn .= "Updating module-versions...\n";
        $this->updateModuleVersion("search", "3.0.2");

        return $strReturn;
	}
}
?>