<?php
/*"******************************************************************************************************
*   (c) 2004-2006 by MulchProductions, www.mulchprod.de                                                 *
*   (c) 2007 by Kajona, www.kajona.de                                                                   *
*       Published under the GNU LGPL v2.1, see /system/licence_lgpl.txt                                 *
*-------------------------------------------------------------------------------------------------------*
* 																										*
* 	installer_guestbook.php                                                                             *
* 	Installer of the guestbook-module                                                                   *
*																										*
*-------------------------------------------------------------------------------------------------------*
*	$Id$										*
********************************************************************************************************/

require_once(_realpath_."/installer/class_installer_base.php");
require_once(_realpath_."/installer/interface_installer.php");

/**
 * Installer of the guestbook
 *
 * @package modul_guestbook
 */
class class_installer_guestbook extends class_installer_base implements interface_installer {

	public function __construct() {
		$arrModule["version"] 		= "3.0.2";
		$arrModule["name"] 			= "guestbook";
		$arrModule["class_admin"] 	= "class_modul_guestbook_admin";
		$arrModule["file_admin"] 	= "class_modul_guestbook_admin.php";
		$arrModule["class_portal"] 	= "class_modul_guestbook_portal";
		$arrModule["file_portal"] 	= "class_modul_guestbook_portal.php";
		$arrModule["name_lang"] 	= "Module Guestbook";
		$arrModule["moduleId"] 		= _gaestebuch_modul_id_;

		$arrModule["tabellen"][]    = _dbprefix_."guestbook_buch";
		$arrModule["tabellen"][]    = _dbprefix_."guestbook_posts";
		$arrModule["tabellen"][]    = _dbprefix_."elemente_guestbook";
		parent::__construct($arrModule);
	}

	public function getNeededModules() {
	    return array("system", "pages");
	}

	public function hasPostInstalls() {
		//check, if not already existing
		$strQuery = "SELECT COUNT(*) FROM "._dbprefix_."element WHERE element_name='guestbook'";
		$arrRow = $this->objDB->getRow($strQuery);
		if($arrRow["COUNT(*)"] == 0)
            return true;


        return false;
	}


   public function install() {

		$strReturn = "";
		//Tabellen anlegen

		//guestbook-------------------------------------------------------------------------------------
		$strReturn .= "Installing table guestbook_book...\n";

		$strQuery = "CREATE TABLE IF NOT EXISTS `"._dbprefix_."guestbook_book` (
                          `guestbook_id` varchar(20) NOT NULL default '',
                          `guestbook_title` varchar(255) default NULL,
                          `guestbook_moderated` smallint(2) default NULL,
                          PRIMARY KEY  (`guestbook_id`)
                        ) ";

		if(!$this->objDB->createTable($strQuery))
			$strReturn .= "An error occured! ...\n";

		//guestbook_post----------------------------------------------------------------------------------
		$strReturn .= "Installing table guestbook_post...\n";

		$strQuery = "CREATE TABLE IF NOT EXISTS `"._dbprefix_."guestbook_post` (
                        `guestbook_post_id` VARCHAR( 20 ) NOT NULL ,
                        `guestbook_post_name` VARCHAR( 255 ) ,
                        `guestbook_post_email` VARCHAR( 255 ) ,
                        `guestbook_post_page` VARCHAR( 255 ) ,
                        `guestbook_post_text` TEXT,
                        `guestbook_post_date` INT( 40 ) ,
                          PRIMARY KEY  (`guestbook_post_id`)
                        ) ";

		if(!$this->objDB->createTable($strQuery))
			$strReturn .= "An error occured! ...\n";


		//register the module
		$strSystemID = $this->registerModule("guestbook", _gaestebuch_modul_id_, "class_modul_guestbook_portal", "class_modul_guestbook_portal.php", "class_modul_guestbook_admin", "class_modul_guestbook_admin.php", $this->arrModule["version"] , true);

		$strReturn .= "Registering system-constants...\n";
		$this->registerConstant("_guestbook_suche_seite_", "guestbook", class_modul_system_setting::$int_TYPE_PAGE, _gaestebuch_modul_id_);

		return $strReturn;

	}

	public function postInstall() {
		$strReturn = "";

		//Table for page-element
		$strReturn .= "Installing guestbook-element table...\n";
		$strQuery = "CREATE TABLE IF NOT EXISTS `"._dbprefix_."element_guestbook` (
                        `content_id` VARCHAR( 20 ) NOT NULL ,
                        `guestbook_id` VARCHAR( 20 ) ,
                        `guestbook_template` VARCHAR( 255 ) ,
                        `guestbook_amount` INT,
                        PRIMARY KEY ( `content_id` )
                        ) ";
		if(!$this->objDB->createTable($strQuery))
			$strReturn .= "An error occured! ...\n";

		//Register the element
		$strReturn .= "Registering guestbook-element...\n";
		//check, if not already existing
		$strQuery = "SELECT COUNT(*) FROM "._dbprefix_."element WHERE element_name='guestbook'";
		$arrRow = $this->objDB->getRow($strQuery);
		if($arrRow["COUNT(*)"] == 0) {
			$strQuery = "INSERT INTO "._dbprefix_."element
							(element_id, element_name, element_class_portal, element_class_admin, element_repeat) VALUES
							('".$this->generateSystemid()."', 'guestbook', 'class_element_guestbook.php', 'class_element_guestbook.php', 1)";
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

        $strReturn .= "Registering system-constants...\n";
		$this->registerConstant("_guestbook_suche_seite_", "guestbook", 3, _gaestebuch_modul_id_);

        $strReturn .= "Updating module-versions...\n";
        $this->updateModuleVersion("guestbook", "3.0.0");

        return $strReturn;
	}

	private function update_300_301() {
	    //Run the updates
	    $strReturn = "";
        $strReturn .= "Updating 3.0.0 to 3.0.1...\n";

        $strReturn .= "Updating module-versions...\n";
        $this->updateModuleVersion("guestbook", "3.0.1");

        return $strReturn;
	}

	private function update_301_302() {
	    //Run the updates
	    $strReturn = "";
        $strReturn .= "Updating 3.0.1 to 3.0.2...\n";

        $strReturn .= "Updating module-versions...\n";
        $this->updateModuleVersion("guestbook", "3.0.2");

        return $strReturn;
	}
}
?>