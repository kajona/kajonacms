<?php
/*"******************************************************************************************************
*   (c) 2004-2006 by MulchProductions, www.mulchprod.de                                                 *
*   (c) 2007 by Kajona, www.kajona.de                                                                   *
*       Published under the GNU LGPL v2.1, see /system/licence_lgpl.txt                                 *
*-------------------------------------------------------------------------------------------------------*
* 																										*
* 	installer_gallery.php    																			*
* 	Installer of the gallery																			*
*																										*
*-------------------------------------------------------------------------------------------------------*
*	$Id$	                                    *
********************************************************************************************************/

require_once(_systempath_."/class_installer_base.php");
require_once(_systempath_."/interface_installer.php");

/**
 * Installer to install the gallery-module
 *
 * @package modul_gallery
 */
class class_installer_gallery extends class_installer_base implements interface_installer {

	public function __construct() {
		$arrModule["version"] 		= "3.0.2";
		$arrModule["name"] 			= "gallery";
		$arrModule["class_admin"] 	= "class_modul_gallery_admin";
		$arrModule["file_admin"] 	= "class_modul_gallery_admin.php";
		$arrModule["class_portal"] 	= "class_modul_gallery_portal";
		$arrModule["file_portal"] 	= "class_modul_gallery_portal.php";
		$arrModule["name_lang"] 	= "Module Gallery";
		$arrModule["moduleId"] 		= _bildergalerie_modul_id_;
		$arrModule["tabellen"][]    = _dbprefix_."gallery_gallery";
		$arrModule["tabellen2"][]   = _dbprefix_."gallery_pic";
		parent::__construct($arrModule);
	}

	public function getNeededModules() {
	    return array("system", "pages");
	}

	public function hasPostInstalls() {
		//check, if not already existing
		$strQuery = "SELECT COUNT(*) FROM "._dbprefix_."element WHERE element_name='gallery'";
		$arrRow = $this->objDB->getRow($strQuery);
		if($arrRow["COUNT(*)"] == 0)
		    return true;
		$strQuery = "SELECT COUNT(*) FROM "._dbprefix_."element WHERE element_name='galleryRandom'";
		$arrRow = $this->objDB->getRow($strQuery);
		if($arrRow["COUNT(*)"] == 0)
		    return true;

        return false;
	}
	
    public function getMinSystemVersion() {
	    return "3.0.2";
	}

    public function install() {
       $strReturn = "";

		$strReturn = "Installing ".$this->arrModule["name_lang"]."...\n";
		//Tabellen anlegen

		//gallery ---------------------------------------------------------------------------------------
		$strReturn .= "Installing table gallery_gallery...\n";

		$strQuery = "CREATE TABLE IF NOT EXISTS `"._dbprefix_."gallery_gallery` (
                        `gallery_id` VARCHAR( 20 ) NOT NULL ,
                        `gallery_path` VARCHAR( 255 ) ,
                        `gallery_title` VARCHAR( 255 ) ,
                        PRIMARY KEY ( `gallery_id` )
                        ) ";

		if(!$this->objDB->createTable($strQuery))
			$strReturn .= "An error occured! ...\n";

		//gallery_pic -----------------------------------------------------------------------------------
		$strReturn .= "Installing table gallery_pic...\n";

		$strQuery = "CREATE TABLE IF NOT EXISTS `"._dbprefix_."gallery_pic` (
                        `pic_id` VARCHAR( 20 ) NOT NULL ,
                        `pic_name` VARCHAR( 255 ) ,
                        `pic_filename` VARCHAR( 255 ) ,
                        `pic_description` TEXT ,
                        `pic_subtitle` VARCHAR( 255 ) ,
                        `pic_size` INT,
                        `pic_hits` INT,
                        `pic_type` INT( 2 ) ,
                        PRIMARY KEY ( `pic_id` )
                        ) ";

		if(!$this->objDB->createTable($strQuery))
			$strReturn .= "An error occured! ...\n";


		//register the module
		$strSystemID = $this->registerModule("gallery", _bildergalerie_modul_id_, "class_modul_gallery_portal.php", "class_modul_gallery_admin.php", $this->arrModule["version"] , true);

		$strReturn .= "Registering system-constants...\n";
		$this->registerConstant("_bildergalerie_bildtypen_", ".jpg,.gif,.png", class_modul_system_setting::$int_TYPE_STRING, _bildergalerie_modul_id_);

		$this->registerConstant("_bildergalerie_suche_seite_", "gallery", class_modul_system_setting::$int_TYPE_PAGE, _bildergalerie_modul_id_);

		return $strReturn;

	}

	public function postInstall() {
		$strReturn = "";

		//Table for page-element
		$strReturn .= "Installing gallery-element table...\n";
		$strQuery = "CREATE TABLE IF NOT EXISTS `"._dbprefix_."element_gallery` (
                        `content_id` VARCHAR( 20 ) NOT NULL ,
                        `gallery_id` VARCHAR( 20 ) ,
                        `gallery_mode` INT ,
                        `gallery_template` VARCHAR( 255 ) ,
                        `gallery_maxh_p` INT,
                        `gallery_maxh_d` INT,
                        `gallery_maxw_p` INT,
                        `gallery_maxw_d` INT,
                        `gallery_maxh_m` INT,
                        `gallery_maxw_m` INT,
                        `gallery_nrow` INT,
                        `gallery_text` VARCHAR( 255 ) ,
                        `gallery_text_x` INT,
                        `gallery_text_y` INT,
                        PRIMARY KEY ( `content_id` )
                        ) ";

		if(!$this->objDB->createTable($strQuery))
			$strReturn .= "An error occured! ...\n";

		//Register the element
		$strReturn .= "Registering gallery-element...\n";
		//check, if not already existing
		$strQuery = "SELECT COUNT(*) FROM "._dbprefix_."element WHERE element_name='gallery'";
		$arrRow = $this->objDB->getRow($strQuery);
		if($arrRow["COUNT(*)"] == 0) {
			$strQuery = "INSERT INTO "._dbprefix_."element
							(element_id, element_name, element_class_portal, element_class_admin, element_repeat) VALUES
							('".$this->generateSystemid()."', 'gallery', 'class_element_gallery.php', 'class_element_gallery.php', 1)";
			$this->objDB->_query($strQuery);
			$strReturn .= "Element registered...\n";
		}
		else {
			$strReturn .= "Element already installed!...\n";
		}
		$strReturn .= "Registering galleryRandom-element...\n";
		//check, if not already existing
		$strQuery = "SELECT COUNT(*) FROM "._dbprefix_."element WHERE element_name='galleryRandom'";
		$arrRow = $this->objDB->getRow($strQuery);
		if($arrRow["COUNT(*)"] == 0) {
			$strQuery = "INSERT INTO "._dbprefix_."element
							(element_id, element_name, element_class_portal, element_class_admin, element_repeat) VALUES
							('".$this->generateSystemid()."', 'galleryRandom', 'class_element_gallery.php', 'class_element_galleryRandom.php', 1)";
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

        $strReturn .= "Updating module-versions...\n";
        $this->updateModuleVersion("gallery", "3.0.0");

        return $strReturn;
	}

	private function update_300_301() {
	    //Run the updates
	    $strReturn = "";
        $strReturn .= "Updating 3.0.0 to 3.0.1...\n";

        $strReturn .= "Altering pic-table...\n";
        $strQuery = "ALTER TABLE `"._dbprefix_."gallery_pic` ADD `pic_subtitle` VARCHAR( 255 ) NULL ";
        if(!$this->objDB->_query($strQuery))
            $strReturn .= "An error occured!!!\n";

        $strQuery = "ALTER TABLE `"._dbprefix_."gallery_pic` CHANGE `pic_description` `pic_description` TEXT  NULL ";
        if(!$this->objDB->_query($strQuery))
            $strReturn .= "An error occured!!!\n";

        $strReturn .= "Updating module-versions...\n";
        $this->updateModuleVersion("gallery", "3.0.1");

        return $strReturn;
	}
	
	private function update_301_302() {
	    //Run the updates
	    $strReturn = "";
        $strReturn .= "Updating 3.0.1 to 3.0.2...\n";
        
        $strReturn .= "Scanning tables...\n";
        $arrTables = $this->objDB->getTables();
        
        if(in_array(_dbprefix_."element_gallery", $arrTables)) {
            $strReturn .= "Altering gallery-element-table...\n";
            $strQuery = "ALTER TABLE `"._dbprefix_."element_gallery` 
                            ADD `gallery_maxh_m` INT  ,
                            ADD `gallery_maxw_m` INT ;";
            if(!$this->objDB->_query($strQuery))
                $strReturn .= "An error occured!!!\n";
        }

        $strReturn .= "Updating module-versions...\n";
        $this->updateModuleVersion("gallery", "3.0.2");

        return $strReturn;
	}

}
?>