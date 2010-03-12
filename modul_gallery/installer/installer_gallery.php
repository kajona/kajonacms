<?php
/*"******************************************************************************************************
*   (c) 2004-2006 by MulchProductions, www.mulchprod.de                                                 *
*   (c) 2007-2010 by Kajona, www.kajona.de                                                              *
*       Published under the GNU LGPL v2.1, see /system/licence_lgpl.txt                                 *
*-------------------------------------------------------------------------------------------------------*
*	$Id$	                                    *
********************************************************************************************************/

/**
 * Installer to install the gallery-module
 *
 * @package modul_gallery
 */
class class_installer_gallery extends class_installer_base implements interface_installer {

	public function __construct() {
        $arrModule = array();
		$arrModule["version"] 		= "3.2.92";
		$arrModule["name"] 			= "gallery";
		$arrModule["class_admin"] 	= "class_modul_gallery_admin";
		$arrModule["file_admin"] 	= "class_modul_gallery_admin.php";
		$arrModule["class_portal"] 	= "class_modul_gallery_portal";
		$arrModule["file_portal"] 	= "class_modul_gallery_portal.php";
		$arrModule["name_lang"] 	= "Module Gallery";
		$arrModule["moduleId"] 		= _gallery_modul_id_;
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
	    return "3.2.1";
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
		$strSystemID = $this->registerModule("gallery", _gallery_modul_id_, "class_modul_gallery_portal.php", "class_modul_gallery_admin.php", $this->arrModule["version"] , true, "", "class_modul_gallery_admin_xml.php");

		$strReturn .= "Registering system-constants...\n";
		$this->registerConstant("_gallery_imagetypes_", ".jpg,.gif,.png", class_modul_system_setting::$int_TYPE_STRING, _gallery_modul_id_);

		$this->registerConstant("_gallery_search_resultpage_", "gallery", class_modul_system_setting::$int_TYPE_PAGE, _gallery_modul_id_);

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
		$arrFields["gallery_imagesperpage"] = array("int", true);
		$arrFields["gallery_text"] 			= array("char254", true);
		$arrFields["gallery_text_x"] 		= array("int", true);
		$arrFields["gallery_text_y"] 		= array("int", true);

		if(!$this->objDB->createTable("element_gallery", $arrFields, array("content_id")))
			$strReturn .= "An error occured! ...\n";

		//Register the element
		$strReturn .= "Registering gallery-element...\n";
		//check, if not already existing
        $objElement = null;
		try {
		    $objElement = class_modul_pages_element::getElement("gallery");
		}
		catch (class_exception $objEx)  {
		}
		if($objElement == null) {
		    $objElement = new class_modul_pages_element();
		    $objElement->setStrName("gallery");
		    $objElement->setStrClassAdmin("class_element_gallery.php");
		    $objElement->setStrClassPortal("class_element_gallery.php");
		    $objElement->setIntCachetime(-1);
		    $objElement->setIntRepeat(1);
            $objElement->setStrVersion($this->getVersion());
			$objElement->updateObjectToDb();
			$strReturn .= "Element registered...\n";
		}
		else {
			$strReturn .= "Element already installed!...\n";
		}


		$strReturn .= "Registering galleryRandom-element...\n";
		//check, if not already existing
        $objElement = null;
		try {
		    $objElement = class_modul_pages_element::getElement("galleryRandom");
		}
		catch (class_exception $objEx)  {
		}
		if($objElement == null) {
		    $objElement = new class_modul_pages_element();
		    $objElement->setStrName("galleryRandom");
		    $objElement->setStrClassAdmin("class_element_gallery.php");
		    $objElement->setStrClassPortal("class_element_gallery.php");
		    $objElement->setIntCachetime(-1);
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

        return $strReturn."\n\n";
	}

    private function update_310_311() {
        $strReturn = "Updating 3.1.0 to 3.1.1...\n";
        $strReturn .= "Updating module-versions...\n";
        $this->updateModuleVersion("gallery", "3.1.1");
        return $strReturn;
    }

    private function update_311_319() {
        $strReturn = "Updating 3.1.1 to 3.1.9...\n";

        $strReturn .= "Creating filemanager-repos for existing galleries...\n";
        $arrGalleries = class_modul_gallery_gallery::getGalleries();
        foreach($arrGalleries as $objOneGallery) {
            $strReturn .= "Investigating gallery ".$objOneGallery->getStrTitle()."\n";
            $objRepo = new class_modul_filemanager_repo();
            $objRepo->setStrPath($objOneGallery->getStrPath());
            $objRepo->setStrForeignId($objOneGallery->getSystemid());
            $objRepo->setStrName("Internal Repo for Gallery ".$objOneGallery->getSystemid());
            $objRepo->setStrViewFilter(class_modul_gallery_gallery::$strFilemanagerViewFilter);
            $objRepo->setStrUploadFilter(class_modul_gallery_gallery::$strFilemanagerUploadFilter);
            $objRepo->updateObjectToDb();

            $strReturn .= "Repo created with id ".$objRepo->getSystemid()."\n";
        }

        $strReturn .= "Registering xml-classes...\n";
        $objModule = class_modul_system_module::getModuleByName("gallery", true);
        $objModule->setStrXmlNameAdmin("class_modul_gallery_admin_xml.php");
        if(!$objModule->updateObjectToDb())
            $strReturn .= "An error occured!\n";

        $strReturn .= "Updating system-constants...\n";
        $objConstant = class_modul_system_setting::getConfigByName("_bildergalerie_bildtypen_");
        $objConstant->renameConstant("_gallery_imagetypes_");

        $objConstant = class_modul_system_setting::getConfigByName("_bildergalerie_suche_seite_");
        $objConstant->renameConstant("_gallery_search_resultpage_");

        $strReturn .= "Updating module-versions...\n";
        $this->updateModuleVersion("gallery", "3.1.9");

        return $strReturn;
    }

    private function update_319_3195() {
        $strReturn = "Updating 3.1.9 to 3.1.95...\n";
        $strReturn .= "Updating module-versions...\n";
        $this->updateModuleVersion("gallery", "3.1.95");
        return $strReturn;
    }

    private function update_3195_320() {
        $strReturn = "Updating 3.1.95 to 3.2.0...\n";
        $strReturn .= "Updating module-versions...\n";
        $this->updateModuleVersion("gallery", "3.2.0");
        return $strReturn;
    }

    private function update_320_3209() {
        $strReturn = "Updating 3.2.0 to 3.2.0.9...\n";
        $strReturn .= "Updating module-versions...\n";
        $this->updateModuleVersion("gallery", "3.2.0.9");
        return $strReturn;
    }

    private function update_3209_321() {
        $strReturn = "Updating 3.2.0.9 to 3.2.1...\n";
        $strReturn .= "Updating module-versions...\n";
        $this->updateModuleVersion("gallery", "3.2.1");
        $strReturn .= "Updating element-versions...\n";
        $this->updateElementVersion("gallery", "3.2.1");
        $this->updateElementVersion("galleryRandom", "3.2.1");
        return $strReturn;
    }

    private function update_321_3291() {
        $strReturn = "Updating 3.2.1 to 3.2.91...\n";

        $strReturn .= "Reorganizing galleries..\n";

        $strQuery = "SELECT module_id
                       FROM "._dbprefix_."system_module
                      WHERE module_nr = "._gallery_modul_id_."";
        $arrEntries = $this->objDB->getRow($strQuery);
        $strModuleId = $arrEntries["module_id"];

        $strQuery = "SELECT gallery_id
                       FROM "._dbprefix_."gallery_gallery";
        $arrEntries = $this->objDB->getArray($strQuery);

        foreach($arrEntries as $arrSingleRow) {
            $strReturn .= " ...updating gallery ".$arrSingleRow["gallery_id"]."";
            $strQuery = "UPDATE "._dbprefix_."system
                            SET system_prev_id = '".dbsafeString($strModuleId)."'
                          WHERE system_id = '".dbsafeString($arrSingleRow["gallery_id"])."'";
            if($this->objDB->_query($strQuery))
                $strReturn .= " ...ok\n";
            else
                $strReturn .= " ...failed!!!\n";
        }

        $strReturn .= "Updating module-versions...\n";
        $this->updateModuleVersion("gallery", "3.2.91");
        $strReturn .= "Updating element-versions...\n";
        $this->updateElementVersion("gallery", "3.2.91");
        $this->updateElementVersion("galleryRandom", "3.2.91");
        return $strReturn;
    }

    private function update_3291_3292() {
        $strReturn = "Updating 3.2.91 to 3.2.92...\n";


        if(in_array(_dbprefix_."element_gallery", $this->objDB->getTables())) {
            $strReturn .= "Updating gallery-element table...\n";

            $strQuery = "ALTER TABLE ".$this->objDB->encloseTableName(_dbprefix_."element_gallery")."
                                DROP ".$this->objDB->encloseColumnName("gallery_nrow").";";
            if(!$this->objDB->_query($strQuery))
                $strReturn .= "An error occured!!!\n";
        }


        $strReturn .= "Updating module-versions...\n";
        $this->updateModuleVersion("gallery", "3.2.92");
        $strReturn .= "Updating element-versions...\n";
        $this->updateElementVersion("gallery", "3.2.92");
        $this->updateElementVersion("galleryRandom", "3.2.92");
        return $strReturn;
    }

}
?>