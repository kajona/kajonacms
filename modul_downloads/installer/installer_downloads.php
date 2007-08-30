<?php
/*"******************************************************************************************************
*   (c) 2004-2006 by MulchProductions, www.mulchprod.de                                                 *
*   (c) 2007 by Kajona, www.kajona.de                                                                   *
*       Published under the GNU LGPL v2.1, see /system/licence_lgpl.txt                                 *
*-------------------------------------------------------------------------------------------------------*
* 																										*
* 	installer_downlodas.php																				*
* 	Installer of the downloads																			*																										*
*																										*
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
		$arrModule["version"] 		= "3.0.2";
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

		$strQuery = "CREATE TABLE IF NOT EXISTS `"._dbprefix_."downloads_file` (
                            `downloads_id` VARCHAR( 20 ) NOT NULL ,
                            `downloads_name` VARCHAR( 255 ) ,
                            `downloads_filename` VARCHAR( 255 ) ,
                            `downloads_description` TEXT,
                            `downloads_size` INT,
                            `downloads_hits` INT,
                            `downloads_type` INT( 2 ) ,
                            `downloads_max_kb` INT,
                            PRIMARY KEY ( `downloads_id` )
                            ) ";

		if(!$this->objDB->createTable($strQuery))
			$strReturn .= "An error occured! ...\n";

		//downloads_log----------------------------------------------------------------------------------
		$strReturn .= "Installing table downloads_log...\n";

		$strQuery = "CREATE TABLE IF NOT EXISTS `"._dbprefix_."downloads_log` (
                        `downloads_log_id` VARCHAR( 20 ) NOT NULL ,
                        `downloads_log_date` INT,
                        `downloads_log_file` VARCHAR( 255 ) ,
                        `downloads_log_user` VARCHAR( 20 ) ,
                        `downloads_log_ip` VARCHAR( 20 ) ,
                        PRIMARY KEY ( `downloads_log_id` )
                        ) ";

		if(!$this->objDB->createTable($strQuery))
			$strReturn .= "An error occured! ...\n";

		//downloads_archive----------------------------------------------------------------------------------
		$strReturn .= "Installing table downloads_archive...\n";

		$strQuery = "CREATE TABLE `"._dbprefix_."downloads_archive` (
                        `archive_id` VARCHAR( 20 ) NOT NULL ,
                        `archive_path` VARCHAR( 255 ) ,
                        `archive_title` VARCHAR( 255 ) ,
                        PRIMARY KEY ( `archive_id` )
                        ) ";

		if(!$this->objDB->createTable($strQuery))
			$strReturn .= "An error occured! ...\n";


		//register the module
		$strSystemID = $this->registerModule("downloads", _downloads_modul_id_, "class_modul_downloads_portal", "class_modul_downloads_portal.php", "class_modul_downloads_admin", "class_modul_downloads_admin.php", $this->arrModule["version"] , true);

		$strReturn .= "Registering system-constants...\n";
		//Number of rows in the login-log
		$this->registerConstant("_downloads_suche_seite_", "downloads", class_modul_system_setting::$int_TYPE_PAGE, _downloads_modul_id_);

		return $strReturn;

	}

	public function postInstall() {
		$strReturn = "";

		//Table for page-element
		$strReturn .= "Installing downloads-element table...\n";
		$strQuery = "CREATE TABLE IF NOT EXISTS `"._dbprefix_."element_downloads` (
                        `content_id` VARCHAR( 20 ) NOT NULL ,
                        `download_id` VARCHAR( 20 ) ,
                        `download_template` VARCHAR( 255 ) ,
                        PRIMARY KEY ( `content_id` )
                        ) ";
		if(!$this->objDB->createTable($strQuery))
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
        if($arrModul["module_version"] == "2.2.0.0") {
            $strReturn .= $this->update_2200_300();
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

	private function update_2200_300() {
	    //Run the updates
	    $strReturn = "";
        $strReturn .= "Updating 2.2.0.0 to 3.0.0...\n";

        //Update the module-records to 2.2.0.0
        $strReturn .= "Updating module-versions...\n";
        $this->updateModuleVersion("downloads", "3.0.0");

        return $strReturn;
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


}
?>