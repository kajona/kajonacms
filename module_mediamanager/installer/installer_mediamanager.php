<?php
/*"******************************************************************************************************
*   (c) 2007-2012 by Kajona, www.kajona.de                                                              *
*       Published under the GNU LGPL v2.1, see /system/licence_lgpl.txt                                 *
*-------------------------------------------------------------------------------------------------------*
*	$Id: class_carrier.php 4059 2011-08-09 14:52:41Z sidler $                                            *
********************************************************************************************************/

/**
 * Installer to install the mediamanager-module
 *
 * @package module_mediamanager
 */
class class_installer_mediamanager extends class_installer_base implements interface_installer {

	public function __construct() {
		$this->setArrModuleEntry("version", "3.4.9");
		$this->setArrModuleEntry("name", "mediamanager");
		$this->setArrModuleEntry("name_lang", "Module Mediamanager");
		$this->setArrModuleEntry("moduleId", _mediamanager_module_id_);

		parent::__construct();
	}

	public function getNeededModules() {
	    return array("system", "pages");
	}

	public function hasPostInstalls() {

        $objElement = class_module_pages_element::getElement("gallery");
        if($objElement === null)
            return true;

        $objElement = class_module_pages_element::getElement("galleryRandom");
        if($objElement === null)
            return true;


        return false;
	}

    public function getMinSystemVersion() {
	    return "3.4.9";
	}

    public function install() {

        if(count($this->objDB->getTables()) > 0) {
            $arrModul = $this->getModuleData($this->arrModule["name"]);
            if(count($arrModul) > 0)
                return "<strong>Module already installed!!!</strong><br /><br />";
        }

		$strReturn = "Installing ".$this->arrModule["name_lang"]."...\n";
		//Tabellen anlegen

		//gallery ---------------------------------------------------------------------------------------
		$strReturn .= "Installing table mediamanager_repo...\n";

		$arrFields = array();
		$arrFields["repo_id"] 	            = array("char20", false);
		$arrFields["repo_path"]             = array("char254", true);
		$arrFields["repo_title"]            = array("char254", true);
		$arrFields["repo_upload_filter"]    = array("char254", true);
		$arrFields["repo_view_filter"]      = array("char254", true);

		if(!$this->objDB->createTable("mediamanager_repo", $arrFields, array("repo_id")))
			$strReturn .= "An error occured! ...\n";

		//gallery_pic -----------------------------------------------------------------------------------
		$strReturn .= "Installing table mediamanager_file...\n";

		$arrFields = array();
		$arrFields["file_id"] 			    = array("char20", false);
		$arrFields["file_name"] 			= array("char254", true);
		$arrFields["file_filename"] 		= array("char254", true);
		$arrFields["file_description"] 	    = array("text", true);
		$arrFields["file_subtitle"] 		= array("char254", true);
		$arrFields["file_hits"] 			= array("int", true);
		$arrFields["file_type"] 			= array("int", true);

		if(!$this->objDB->createTable("mediamanager_file", $arrFields, array("file_id")))
			$strReturn .= "An error occured! ...\n";


		//register the module
		$this->registerModule(
            "mediamanager",
            _mediamanager_module_id_,
            "class_module_mediamanager_portal.php",
            "class_module_mediamanager_admin.php",
            $this->arrModule["version"],
            true, "",
            "class_module_mediamanager_admin_xml.php");

		$strReturn .= "Registering system-constants...\n";

        //FIXME: remove
//        if(class_module_system_setting::getConfigByName("_gallery_imagetypes_") === null)
//		    $this->registerConstant("_gallery_imagetypes_", ".jpg,.gif,.png", class_module_system_setting::$int_TYPE_STRING, _mediamanager_module_id_);

        if(class_module_system_setting::getConfigByName("_gallery_search_resultpage_") === null)
		    $this->registerConstant("_gallery_search_resultpage_", "gallery", class_module_system_setting::$int_TYPE_PAGE, _mediamanager_module_id_);


        $this->registerConstant("_mediamanager_default_imagesrepoid_", "", class_module_system_setting::$int_TYPE_STRING, _mediamanager_module_id_);
        $this->registerConstant("_mediamanager_default_filesrepoid_", "", class_module_system_setting::$int_TYPE_STRING, _mediamanager_module_id_);





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
		$arrFields["gallery_overlay"]    	= array("char254", true);
		$arrFields["gallery_text_x"] 		= array("int", true);
		$arrFields["gallery_text_y"] 		= array("int", true);

		if(!$this->objDB->createTable("element_gallery", $arrFields, array("content_id")))
			$strReturn .= "An error occured! ...\n";

		//Register the element
		$strReturn .= "Registering gallery-element...\n";
		//check, if not already existing
        $objElement = null;
		try {
		    $objElement = class_module_pages_element::getElement("gallery");
		}
		catch (class_exception $objEx)  {
		}
		if($objElement == null) {
		    $objElement = new class_module_pages_element();
		    $objElement->setStrName("gallery");
		    $objElement->setStrClassAdmin("class_element_gallery.php");
		    $objElement->setStrClassPortal("class_element_gallery.php");
		    $objElement->setIntCachetime(3600);
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
		    $objElement = class_module_pages_element::getElement("galleryRandom");
		}
		catch (class_exception $objEx)  {
		}
		if($objElement == null) {
		    $objElement = new class_module_pages_element();
		    $objElement->setStrName("galleryRandom");
		    $objElement->setStrClassAdmin("class_element_galleryRandom.php");
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



        return $strReturn."\n\n";
	}



}
