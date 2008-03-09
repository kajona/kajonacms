<?php
/*"******************************************************************************************************
*   (c) 2004-2006 by MulchProductions, www.mulchprod.de                                                 *
*   (c) 2007-2008 by Kajona, www.kajona.de                                                              *
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
		$arrModule["version"] 		= "3.1.0";
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
	    return "3.0.9";
	}

    public function install() {
       $strReturn = "";

		$strReturn = "Installing ".$this->arrModule["name_lang"]."...\n";
		//Tabellen anlegen

		//gallery ---------------------------------------------------------------------------------------
		$strReturn .= "Installing table gallery_gallery...\n";
		
		$arrFields = array();
		$arrFields["gallery_id"] 	= array("char20", false);
		$arrFields["gallery_path"] 	= array("char254", true);
		$arrFields["gallery_title"] = array("char254", true);

		if(!$this->objDB->createTable("gallery_gallery", $arrFields, array("gallery_id")))
			$strReturn .= "An error occured! ...\n";

		//gallery_pic -----------------------------------------------------------------------------------
		$strReturn .= "Installing table gallery_pic...\n";
		
		$arrFields = array();
		$arrFields["pic_id"] 			= array("char20", false);
		$arrFields["pic_name"] 			= array("char254", true);
		$arrFields["pic_filename"] 		= array("char254", true);
		$arrFields["pic_description"] 	= array("text", true);
		$arrFields["pic_subtitle"] 		= array("char254", true);
		$arrFields["pic_size"] 			= array("int", true);
		$arrFields["pic_hits"] 			= array("int", true);
		$arrFields["pic_type"] 			= array("int", true);

		if(!$this->objDB->createTable("gallery_pic", $arrFields, array("pic_id")))
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
		
		$arrFields = array();
		$arrFields["content_id"] 			= array("char20", false);
		$arrFields["gallery_id"] 			= array("char20", true);
		$arrFields["gallery_mode"] 			= array("int", true);
		$arrFields["gallery_template"] 		= array("char254", true);
		$arrFields["gallery_maxh_p"] 		= array("int", true);
		$arrFields["gallery_maxh_d"] 		= array("int", true);
		$arrFields["gallery_maxw_p"] 		= array("int", true);
		$arrFields["gallery_maxw_d"] 		= array("int", true);
		$arrFields["gallery_maxh_m"] 		= array("int", true);
		$arrFields["gallery_maxw_m"] 		= array("int", true);
		$arrFields["gallery_nrow"] 			= array("int", true);
		$arrFields["gallery_imagesperpage"] = array("int", true);
		$arrFields["gallery_text"] 			= array("char254", true);
		$arrFields["gallery_text_x"] 		= array("int", true);
		$arrFields["gallery_text_y"] 		= array("int", true);
		
		if(!$this->objDB->createTable("element_gallery", $arrFields, array("content_id")))
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
                            ADD `gallery_imagesperpage` INT NULL ,
                            ADD `gallery_maxw_m` INT NULL;";
            if(!$this->objDB->_query($strQuery))
                $strReturn .= "An error occured!!!\n";
        }

        $strReturn .= "Updating module-versions...\n";
        $this->updateModuleVersion("gallery", "3.0.2");

        return $strReturn;
	}
	
    private function update_302_309() {
        //Run the updates
        $strReturn = "";
        $strReturn .= "Updating 3.0.2 to 3.0.9...\n";
        $strReturn .= "Altering gallery-element-table...\n";
        $strQuery = "ALTER TABLE `"._dbprefix_."element_gallery` 
                        ADD `gallery_imagesperpage` INT NULL;";
        
        if(!$this->objDB->_query($strQuery))
                $strReturn .= "An error occured!!!\n";    
        
        $strReturn .= "Updating module-versions...\n";
        $this->updateModuleVersion("gallery", "3.0.9");

        return $strReturn;
    }
    
	private function update_309_3095() {
        //Run the updates
        $strReturn = "";
        $strReturn .= "Updating 3.0.9 to 3.0.95...\n";
        
        $strReturn .= "Updating module-versions...\n";
        $this->updateModuleVersion("gallery", "3.0.95");

        return $strReturn;
    }

    private function update_3095_310() {
        $strReturn = "Updating 3.0.95 to 3.1.0...\n";
        
        $strReturn .= "Updating module-versions...\n";
        $this->updateModuleVersion("gallery", "3.1.0");

        return $strReturn;
    }
    
}
?>