<?php
/*"******************************************************************************************************
*   (c) 2004-2006 by MulchProductions, www.mulchprod.de                                                 *
*   (c) 2007 by Kajona, www.kajona.de                                                                   *
*       Published under the GNU LGPL v2.1, see /system/licence_lgpl.txt                                 *
*-------------------------------------------------------------------------------------------------------*
* 																										*
* 	installer_pages.php																					*
* 	Installer of the pages-module																		*																										*
*																										*
*-------------------------------------------------------------------------------------------------------*
*   $Id$                                     *
********************************************************************************************************/

require_once(_systempath_."/class_installer_base.php");
require_once(_systempath_."/interface_installer.php");

/**
 * Installer of the pages-module
 *
 * @package modul_pages
 */
class class_installer_pages extends class_installer_base implements interface_installer {

	public function __construct() {

		$arrModule["version"] 		= "3.0.2";
		$arrModule["name"] 			= "pages";
		$arrModule["name2"] 		= "pages_content";
		$arrModule["name3"] 		= "folderview";
		$arrModule["class_admin3"] 	= "class_folderview";
		$arrModule["file_admin3"] 	= "class_folderview.php";
		$arrModule["class_portal2"] = "";
		$arrModule["class_portal3"] = "";
		$arrModule["file_portal2"] 	= "";
		$arrModule["file_portal3"] 	= "";
		$arrModule["name_lang"] 	= "Module Pages";
		$arrModule["name_lang2"] 	= "Module Pages Content";
		$arrModule["name_lang3"] 	= "Module Folderview";
		$arrModule["moduleId"] 		= _pages_modul_id_;
		$arrModule["nummer2"] 		= _pages_inhalte_modul_id_;
		$arrModule["nummer3"] 		= _pages_inhalte_modul_id_;

		$arrModule["tabellen"][] 	= _dbprefix_."pages";
		$arrModule["tabellen"][] 	= _dbprefix_."pages_elemente";
		$arrModule["tabellen"][] 	= _dbprefix_."elemente_absatz";
		$arrModule["tabellen"][] 	= _dbprefix_."elemente_bild";

		parent::__construct($arrModule);
	}

	public function getNeededModules() {
	    return array("system");
	}
	
    public function getMinSystemVersion() {
	    return "3.0.2";
	}

	public function hasPostInstalls() {
		//check, if elements not already existing
		$strQuery = "SELECT COUNT(*) FROM "._dbprefix_."element WHERE element_name='paragraph'";
		$arrRow = $this->objDB->getRow($strQuery);
		if($arrRow["COUNT(*)"] == 0)
		    return true;

		$strQuery = "SELECT COUNT(*) FROM "._dbprefix_."element WHERE element_name='row'";
		$arrRow = $this->objDB->getRow($strQuery);
		if($arrRow["COUNT(*)"] == 0)
		    return true;

		$strQuery = "SELECT COUNT(*) FROM "._dbprefix_."element WHERE element_name='image'";
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

		//Pages -----------------------------------------------------------------------------------------
		$strReturn .= "Installing table pages...\n";

		$strQuery = "CREATE TABLE `"._dbprefix_."page` (
						`page_id` VARCHAR( 41 ) NOT NULL ,
						`page_name` VARCHAR( 254 ) ,
						PRIMARY KEY ( `page_id` )
						) ";

		if(!$this->objDB->createTable($strQuery))
			$strReturn .= "An error occured! ...\n";

		//Pages_properties ------------------------------------------------------------------------------
		$strReturn .= "Installing table pages_properties...\n";

		$strQuery = "CREATE TABLE `"._dbprefix_."page_properties` (
						`pageproperties_id` VARCHAR( 41 ) NOT NULL ,
						`pageproperties_browsername` VARCHAR( 255 ) ,
						`pageproperties_keywords` VARCHAR( 254 ) ,
						`pageproperties_description` VARCHAR( 254 ) ,
						`pageproperties_template` VARCHAR( 120 ) ,
						`pageproperties_seostring` VARCHAR( 255 ) ,
						`pageproperties_language` VARCHAR( 100 ) ,
						PRIMARY KEY ( `pageproperties_id`, `pageproperties_language` )
						) ";

		if(!$this->objDB->createTable($strQuery))
			$strReturn .= "An error occured! ...\n";

		//elementtable-----------------------------------------------------------------------------------
		$strReturn .= "Installing table element...\n";

		$strQuery = "CREATE TABLE `"._dbprefix_."element` (
						`element_id` VARCHAR( 41 ) NOT NULL ,
						`element_name` VARCHAR( 180 ) ,
						`element_class_portal` VARCHAR( 120 )  ,
						`element_class_admin` VARCHAR( 120 ) ,
						`element_repeat` SMALLINT( 2 ) ,
						`element_cachetime` INT DEFAULT '-1' NOT NULL,
						PRIMARY KEY ( `element_id` )
						) ";

		if(!$this->objDB->createTable($strQuery))
			$strReturn .= "An error occured! ...\n";


		//pageelementtable-------------------------------------------------------------------------------
		$strReturn .= "Installing table page_element...\n";

		$strQuery = "CREATE TABLE `"._dbprefix_."page_element` (
						`page_element_id` VARCHAR( 41 ) NOT NULL ,
						`page_element_placeholder_placeholder` VARCHAR( 250 ) ,
						`page_element_placeholder_name` VARCHAR( 250 ) ,
						`page_element_placeholder_element` VARCHAR( 250 ) ,
						`page_element_placeholder_title` VARCHAR( 250 ) ,
						`page_element_placeholder_language` VARCHAR( 100 ) ,
						PRIMARY KEY ( `page_element_id` )
						) ";

		if(!$this->objDB->createTable($strQuery))
			$strReturn .= "An error occured! ...\n";


		//page_cache_table-------------------------------------------------------------------------------
		$strReturn .= "Installing table page_cache...\n";

		$strQuery = "CREATE TABLE `"._dbprefix_."page_cache` (
                        `page_cache_id` VARCHAR( 20 ) NOT NULL ,
                        `page_cache_name` VARCHAR( 255 ) ,
                        `page_cache_checksum` VARCHAR( 100 ) ,
                        `page_cache_createtime` INT,
                        `page_cache_releasetime` INT,
                        `page_cache_userid` VARCHAR( 20 ) ,
                        `page_cache_content` TEXT,
                        PRIMARY KEY ( `page_cache_id` )
                        ) ";

		if(!$this->objDB->createTable($strQuery))
			$strReturn .= "An error occured! ...\n";

		//Now we have to register module by module

		//the pages
		$strSystemID = $this->registerModule("pages", _pages_modul_id_, "class_modul_pages", "class_modul_pages.php", "class_modul_pages_admin", "class_modul_pages_admin.php", $this->arrModule["version"] , true);
		//The pages_content
		$strRightID = $this->registerModule("pages_content", _pages_inhalte_modul_id_, "", "", "class_modul_pages_content_admin", "class_modul_pages_content_admin.php", $this->arrModule["version"], false);
		//The folderview
		$strUserID = $this->registerModule("folderview", _pages_folderview_modul_id, "", "", "class_folderview", "class_folderview.php", $this->arrModule["version"] , false);

		$strReturn .= "Registering system-constants...\n";
		$this->registerConstant("_pages_templatewechsel_", "false", class_modul_system_setting::$int_TYPE_BOOL, _pages_modul_id_);
		$this->registerConstant("_pages_startseite_", "index", class_modul_system_setting::$int_TYPE_PAGE, _pages_modul_id_);
		$this->registerConstant("_pages_fehlerseite_", "error", class_modul_system_setting::$int_TYPE_PAGE, _pages_modul_id_);
		$this->registerConstant("_pages_defaulttemplate_", "", class_modul_system_setting::$int_TYPE_STRING, _pages_modul_id_);
		//2.1.1: overall cachetime
		$this->registerConstant("_pages_maxcachetime_", "9999", class_modul_system_setting::$int_TYPE_INT, _pages_modul_id_);
		$this->registerConstant("_pages_cacheenabled_", "true", class_modul_system_setting::$int_TYPE_BOOL, _pages_modul_id_);
		//2.1.1: possibility, to create new pages disabled
		$this->registerConstant("_pages_newdisabled_", "false", class_modul_system_setting::$int_TYPE_BOOL, _pages_modul_id_);
		//portaleditor
        $this->registerConstant("_pages_portaleditor_", "true", class_modul_system_setting::$int_TYPE_BOOL, _pages_modul_id_);

		return $strReturn;

	}

	public function postInstall() {
		$strReturn = "";

		//Table for paragraphes
		$strReturn .= "Installing paragraph table...\n";
		$strQuery = "CREATE TABLE IF NOT EXISTS `"._dbprefix_."element_absatz` (
					`content_id` VARCHAR( 20 ) NOT NULL ,
					`absatz_titel` VARCHAR( 255 ) ,
					`absatz_inhalt` TEXT,
					`absatz_link` VARCHAR( 255 ) ,
					`absatz_bild` VARCHAR( 255 ) ,
					PRIMARY KEY ( `content_id` )
					) ";

		if(!$this->objDB->createTable($strQuery))
			$strReturn .= "An error occured! ...\n";

		//Register the element
		$strReturn .= "Registering paragraph...\n";
		//check, if not already existing
		$strQuery = "SELECT COUNT(*) FROM "._dbprefix_."element WHERE element_name='paragraph'";
		$arrRow = $this->objDB->getRow($strQuery);
		if($arrRow["COUNT(*)"] == 0) {
			$strQuery = "INSERT INTO "._dbprefix_."element
							(element_id, element_name, element_class_portal, element_class_admin, element_repeat) VALUES
							('".$this->generateSystemid()."', 'paragraph', 'class_element_absatz.php', 'class_element_absatz.php', 1)";
			$this->objDB->_query($strQuery);
			$strReturn .= "Element registered...\n";
		}
		else {
			$strReturn .= "Element already installed!...\n";
		}

		$strReturn .= "Registering row...\n";
		//check, if not already existing
		$strQuery = "SELECT COUNT(*) FROM "._dbprefix_."element WHERE element_name='row'";
		$arrRow = $this->objDB->getRow($strQuery);
		if($arrRow["COUNT(*)"] == 0) {
			$strQuery = "INSERT INTO "._dbprefix_."element
							(element_id, element_name, element_class_portal, element_class_admin, element_repeat) VALUES
							('".$this->generateSystemid()."', 'row', 'class_element_zeile.php', 'class_element_zeile.php', 1)";
			$this->objDB->_query($strQuery);
			$strReturn .= "Element registered...\n";
		}
		else {
			$strReturn .= "Element already installed!...\n";
		}

		//Table for images
		$strReturn .= "Installing image table...\n";
		$strQuery = "CREATE TABLE IF NOT EXISTS `"._dbprefix_."element_bild` (
					  `content_id` varchar(20) NOT NULL default '',
					  `bild_titel` varchar(255) default NULL,
					  `bild_link` varchar(255) default NULL,
					  `bild_bild` varchar(255) default NULL,
					  PRIMARY KEY  (`content_id`)
					) ";

		if(!$this->objDB->createTable($strQuery))
			$strReturn .= "An error occured! ...\n";

		//Register the element
		$strReturn .= "Registering image...\n";
		//check, if not already existing
		$strQuery = "SELECT COUNT(*) FROM "._dbprefix_."element WHERE element_name='image'";
		$arrRow = $this->objDB->getRow($strQuery);
		if($arrRow["COUNT(*)"] == 0) {
			$strQuery = "INSERT INTO "._dbprefix_."element
							(element_id, element_name, element_class_portal, element_class_admin, element_repeat) VALUES
							('".$this->generateSystemid()."', 'image', 'class_element_bild.php', 'class_element_bild.php', 1)";
			$this->objDB->_query($strQuery);
			$strReturn .= "Element registered...\n";
		}
		else {
			$strReturn .= "Element already installed!...\n";
		}

		$strReturn .= "Installing universal element table...\n";
		$strQuery = "CREATE TABLE IF NOT EXISTS `"._dbprefix_."element_universal` (
                        `content_id` VARCHAR( 20 ) NOT NULL ,
                        `char1` VARCHAR( 254 ) NULL ,
                        `char2` VARCHAR( 254 ) NULL ,
                        `char3` VARCHAR( 254 ) NULL ,
                        `int1` INT NULL ,
                        `int2` INT NULL ,
                        `int3` INT NULL ,
                        `text` TEXT NULL ,
                        PRIMARY KEY ( `content_id` )
                        ) ";
		if(!$this->objDB->createTable($strQuery))
			$strReturn .= "An error occured! ...\n";

		return $strReturn;
	}



	public function update() {
	    $strReturn = "";
        //check the version we have and to what version to update
        $arrModul = $this->getModuleData($this->arrModule["name"], false);

        $strReturn .= "Version found:\n\t Module: ".$arrModul["module_name"].", Version: ".$arrModul["module_version"]."\n\n";

        $arrModul = $this->getModuleData($this->arrModule["name"], false);
        if($arrModul["module_version"] == "2.2.0.0") {
            $strReturn .= $this->update_2200_2202();
        }

        $arrModul = $this->getModuleData($this->arrModule["name"], false);
        if($arrModul["module_version"] == "2.2.0.2") {
            $strReturn .= $this->update_2202_300();
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

	private function update_2200_2202() {
	    $strReturn = "";
	    $strReturn .= "Updating 2.2.0.0 to 2.2.0.2...\n";

	    $strReturn .= "Updating page-element-table...\n";
	    $strQuery = "ALTER TABLE `"._dbprefix_."page_element` ADD `page_element_placeholder_language` VARCHAR( 100 ) NULL ";
	    if(!$this->objDB->_query($strQuery))
			$strReturn .= "An error occured! ...\n";

		$strReturn .= "Reorganizing pages tables...\n";

		$strReturn .= "Renaming pages to pages_properties...\n";
        $strQuery = "ALTER TABLE "._dbprefix_."page RENAME AS "._dbprefix_."page_properties";
        if(!$this->objDB->_query($strQuery))
			$strReturn .= "An error occured! ...\n";

		$strReturn .= "Creating new page-table...\n";
        $strQuery = "CREATE TABLE `"._dbprefix_."page` (
						`page_id` VARCHAR( 41 ) NOT NULL ,
						`page_name` VARCHAR( 254 ) ,
						PRIMARY KEY ( `page_id` )
						) ";
		if(!$this->objDB->createTable($strQuery))
			$strReturn .= "An error occured! ...\n";

		$strReturn .= "Resorting existing pages...\n";
        $strQuery = "INSERT INTO "._dbprefix_."page
                        SELECT page_id, page_name
                        FROM "._dbprefix_."page_properties";
        if(!$this->objDB->_query($strQuery))
			$strReturn .= "An error occured! ...\n";

        $strReturn .= "Reasigning page_properties column names...\n";
        $strQuery = "ALTER TABLE `"._dbprefix_."page_properties`
                        CHANGE `page_id` `pageproperties_id` VARCHAR( 20 ) ,
                        CHANGE `page_name` `pageproperties_name` VARCHAR( 254 ) ,
                        CHANGE `page_keywords` `pageproperties_keywords` VARCHAR( 254 ) ,
                        CHANGE `page_description` `pageproperties_description` VARCHAR( 254 ) ,
                        CHANGE `page_template` `pageproperties_template` VARCHAR( 120 ) ,
                        CHANGE `page_browsername` `pageproperties_browsername` VARCHAR( 255 ) ,
                        CHANGE `page_seostring` `pageproperties_seostring` VARCHAR( 255 )";
        if(!$this->objDB->_query($strQuery))
			$strReturn .= "An error occured! ...\n";

		$strReturn .= "Adding language column...\n";
        $strQuery = "ALTER TABLE `"._dbprefix_."page_properties`
                        ADD `pageproperties_language` VARCHAR( 100 ) NULL";
        if(!$this->objDB->_query($strQuery))
			$strReturn .= "An error occured! ...\n";

        $strReturn .= "Updating default language of pages...\n";
        $strQuery = "UPDATE `"._dbprefix_."page_properties`
                        SET pageproperties_language=''";
        if(!$this->objDB->_query($strQuery))
			$strReturn .= "An error occured! ...\n";

        $strReturn .= "Updating default language of page-elements...\n";
        $strQuery = "UPDATE `"._dbprefix_."page_element`
                        SET page_element_placeholder_language=''";
        if(!$this->objDB->_query($strQuery))
			$strReturn .= "An error occured! ...\n";

		$strReturn .= "Dropping column name...\n";
        $strQuery = "ALTER TABLE `"._dbprefix_."page_properties` DROP `pageproperties_name` ";
        if(!$this->objDB->_query($strQuery))
			$strReturn .= "An error occured! ...\n";

		$strReturn .= "Creating new index...\n";
        $strQuery = "ALTER TABLE `"._dbprefix_."page_properties`
                    DROP PRIMARY KEY,
                       ADD PRIMARY KEY(`pageproperties_id`, `pageproperties_language`)";
        if(!$this->objDB->_query($strQuery))
			$strReturn .= "An error occured! ...\n";


	    //Update the module-records to 2.2.0.2
        $strReturn .= "Updating module-versions...\n";
        $this->updateModuleVersion("pages", "2.2.0.2");
        $this->updateModuleVersion("pages_content", "2.2.0.2");
        $this->updateModuleVersion("folderview", "2.2.0.2");

        return $strReturn;
	}

	private function update_2202_300() {
	    $strReturn = "";
	    $strReturn .= "Updating 2.2.0.2 to 3.0.0...\n";

		//Update the module-records to 3.0.0
        $strReturn .= "Updating module-versions...\n";
        $this->updateModuleVersion("pages", "3.0.0");
        $this->updateModuleVersion("pages_content", "3.0.0");
        $this->updateModuleVersion("folderview", "3.0.0");

	    return $strReturn;
	}

	private function update_300_301() {
	    $strReturn = "";
	    $strReturn .= "Updating 3.0.0 to 3.0.1...\n";

        $strReturn .= "Updating module-versions...\n";
        $this->updateModuleVersion("pages", "3.0.1");
        $this->updateModuleVersion("pages_content", "3.0.1");
        $this->updateModuleVersion("folderview", "3.0.1");

	    return $strReturn;
	}

	private function update_301_302() {
	    $strReturn = "";
	    $strReturn .= "Updating 3.0.1 to 3.0.2...\n";

	    $strReturn .= "Creating universal element-table...\n";

	    $strQuery = "CREATE TABLE IF NOT EXISTS `"._dbprefix_."element_universal` (
                        `content_id` VARCHAR( 20 ) NOT NULL ,
                        `char1` VARCHAR( 254 ) NULL ,
                        `char2` VARCHAR( 254 ) NULL ,
                        `char3` VARCHAR( 254 ) NULL ,
                        `int1` INT NULL ,
                        `int2` INT NULL ,
                        `int3` INT NULL ,
                        `text` TEXT NULL ,
                        PRIMARY KEY ( `content_id` )
                        ) ";
		if(!$this->objDB->createTable($strQuery))
			$strReturn .= "An error occured! ...\n";

        $strReturn .= "Updating module-versions...\n";
        $this->updateModuleVersion("pages", "3.0.2");
        $this->updateModuleVersion("pages_content", "3.0.2");
        $this->updateModuleVersion("folderview", "3.0.2");

	    return $strReturn;
	}

}
?>