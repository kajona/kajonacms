<?php
/*"******************************************************************************************************
*   (c) 2004-2006 by MulchProductions, www.mulchprod.de                                                 *
*   (c) 2007-2012 by Kajona, www.kajona.de                                                              *
*       Published under the GNU LGPL v2.1, see /system/licence_lgpl.txt                                 *
*-------------------------------------------------------------------------------------------------------*
*   $Id$                                         *
********************************************************************************************************/

/**
 * Installer of the pages-module
 *
 * @package module_pages
 */
class class_installer_pages extends class_installer_base implements interface_installer {

	public function __construct() {
        $this->setArrModuleEntry("version", "3.4.9");
        $this->setArrModuleEntry("moduleId", _pages_modul_id_);
        $this->setArrModuleEntry("name", "pages");
        $this->setArrModuleEntry("name_lang", "Module Pages");

		parent::__construct();
	}

	public function getNeededModules() {
	    return array("system", "templatemanager");
	}

    public function getMinSystemVersion() {
	    return "3.4.9";
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

		$arrFields = array();
		$arrFields["page_id"] 		= array("char20", false);
		$arrFields["page_name"] 	= array("char254", true);
		$arrFields["page_type"] 	= array("int", true, "0");

		if(!$this->objDB->createTable("page", $arrFields, array("page_id")))
			$strReturn .= "An error occured! ...\n";

		//Pages_properties ------------------------------------------------------------------------------
		$strReturn .= "Installing table page_folderproperties...\n";

		$arrFields = array();
		$arrFields["folderproperties_id"]           = array("char20", false);
		$arrFields["folderproperties_name"]         = array("char254", true);
		$arrFields["folderproperties_language"]     = array("char20", true);

		if(!$this->objDB->createTable("page_folderproperties", $arrFields, array("folderproperties_id", "folderproperties_language")))
			$strReturn .= "An error occured! ...\n";

        //folder_properties
        $strReturn .= "Installing table page_properties...\n";

		$arrFields = array();
		$arrFields["pageproperties_id"] 		= array("char20", false);
		$arrFields["pageproperties_browsername"]= array("char254", true);
		$arrFields["pageproperties_keywords"] 	= array("char254", true);
		$arrFields["pageproperties_description"]= array("char254", true);
		$arrFields["pageproperties_template"] 	= array("char254", true);
		$arrFields["pageproperties_seostring"] 	= array("char254", true);
		$arrFields["pageproperties_language"] 	= array("char20", true);
		$arrFields["pageproperties_alias"] 	    = array("char254", true);

		if(!$this->objDB->createTable("page_properties", $arrFields, array("pageproperties_id", "pageproperties_language"), array("pageproperties_language")))
			$strReturn .= "An error occured! ...\n";

		//elementtable-----------------------------------------------------------------------------------
		$strReturn .= "Installing table element...\n";

		$arrFields = array();
		$arrFields["element_id"] 			= array("char20", false);
		$arrFields["element_name"]			= array("char254", true);
		$arrFields["element_class_portal"] 	= array("char254", true);
		$arrFields["element_class_admin"]	= array("char254", true);
		$arrFields["element_repeat"] 		= array("int", true);
		$arrFields["element_cachetime"] 	= array("int", false, "-1");
		$arrFields["element_version"] 	    = array("char20", true);

		if(!$this->objDB->createTable("element", $arrFields, array("element_id")))
			$strReturn .= "An error occured! ...\n";


		//pageelementtable-------------------------------------------------------------------------------
		$strReturn .= "Installing table page_element...\n";

		$arrFields = array();
		$arrFields["page_element_id"] 					= array("char20", false);
		$arrFields["page_element_ph_placeholder"]       = array("char254", true);
		$arrFields["page_element_ph_name"]              = array("char254", true);
		$arrFields["page_element_ph_element"]           = array("char254", true);
		$arrFields["page_element_ph_title"]             = array("char254", true);
		$arrFields["page_element_ph_language"]          = array("char20", true);

		if(!$this->objDB->createTable("page_element", $arrFields, array("page_element_id")))
			$strReturn .= "An error occured! ...\n";


		//Now we have to register module by module

		//the pages
		$strSystemID = $this->registerModule("pages", _pages_modul_id_, "class_module_pages_portal.php", "class_module_pages_admin.php", $this->arrModule["version"] , true);
		//The pages_content
		$this->registerModule("pages_content", _pages_content_modul_id_, "", "class_module_pages_content_admin.php", $this->arrModule["version"], false);
		//The folderview
		$this->registerModule("folderview", _pages_folderview_modul_id_, "", "class_module_folderview_admin.php", $this->arrModule["version"] , false);

		$strReturn .= "Registering system-constants...\n";
		$this->registerConstant("_pages_templatechange_", "false", class_module_system_setting::$int_TYPE_BOOL, _pages_modul_id_);
		$this->registerConstant("_pages_indexpage_", "index", class_module_system_setting::$int_TYPE_PAGE, _pages_modul_id_);
		$this->registerConstant("_pages_errorpage_", "error", class_module_system_setting::$int_TYPE_PAGE, _pages_modul_id_);
		$this->registerConstant("_pages_defaulttemplate_", "", class_module_system_setting::$int_TYPE_STRING, _pages_modul_id_);
		//2.1.1: overall cachetime
		$this->registerConstant("_pages_cacheenabled_", "false", class_module_system_setting::$int_TYPE_BOOL, _pages_modul_id_); //FIXME: reenable
		//2.1.1: possibility, to create new pages disabled
		$this->registerConstant("_pages_newdisabled_", "false", class_module_system_setting::$int_TYPE_BOOL, _pages_modul_id_);
		//portaleditor
        $this->registerConstant("_pages_portaleditor_", "true", class_module_system_setting::$int_TYPE_BOOL, _pages_modul_id_);

        $strReturn .= "Shifting pages to first position...\n";
        $objCommon = new class_module_system_common($strSystemID);
        $objCommon->setAbsolutePosition(1);







        //Table for paragraphes
        $strReturn .= "Installing paragraph table...\n";

        $arrFields = array();
        $arrFields["content_id"]        = array("char20", false);
        $arrFields["paragraph_title"]	= array("char254", true);
        $arrFields["paragraph_content"] = array("text", true);
        $arrFields["paragraph_link"]	= array("char254", true);
        $arrFields["paragraph_image"]	= array("char254", true);
        $arrFields["paragraph_template"]= array("char254", true);

        if(!$this->objDB->createTable("element_paragraph", $arrFields, array("content_id")))
            $strReturn .= "An error occured! ...\n";

        //Register the element
        $strReturn .= "Registering paragraph...\n";
        //check, if not already existing
        $objElement = null;
        try {
            $objElement = class_module_pages_element::getElement("paragraph");
        }
        catch (class_exception $objEx)  {
        }
        if($objElement == null) {
            $objElement = new class_module_pages_element();
            $objElement->setStrName("paragraph");
            $objElement->setStrClassAdmin("class_element_paragraph_admin.php");
            $objElement->setStrClassPortal("class_element_paragraph_portal.php");
            $objElement->setIntCachetime(3600*24*30);
            $objElement->setIntRepeat(1);
            $objElement->setStrVersion($this->getVersion());
            $objElement->updateObjectToDb();
            $strReturn .= "Element registered...\n";
        }
        else {
            $strReturn .= "Element already installed!...\n";
        }

        $strReturn .= "Registering row...\n";
        //check, if not already existing
        $objElement = null;
        try {
            $objElement = class_module_pages_element::getElement("row");
        }
        catch (class_exception $objEx)  {
        }
        if($objElement == null) {
            $objElement = new class_module_pages_element();
            $objElement->setStrName("row");
            $objElement->setStrClassAdmin("class_element_row_admin.php");
            $objElement->setStrClassPortal("class_element_row_portal.php");
            $objElement->setIntCachetime(3600*24*30);
            $objElement->setIntRepeat(0);
            $objElement->setStrVersion($this->getVersion());
            $objElement->updateObjectToDb();
            $strReturn .= "Element registered...\n";
        }
        else {
            $strReturn .= "Element already installed!...\n";
        }

        //Table for images
        $strReturn .= "Installing image table...\n";

        $arrFields = array();
        $arrFields["content_id"] 	 = array("char20", false);
        $arrFields["image_title"]	 = array("char254", true);
        $arrFields["image_link"] 	 = array("char254", true);
        $arrFields["image_image"]	 = array("char254", true);
        $arrFields["image_x"]        = array("int", true);
        $arrFields["image_y"]        = array("int", true);
        $arrFields["image_template"] = array("char254", true);

        if(!$this->objDB->createTable("element_image", $arrFields, array("content_id")))
            $strReturn .= "An error occured! ...\n";

        //Register the element
        $strReturn .= "Registering image...\n";
        //check, if not already existing
        $objElement = null;
        try {
            $objElement = class_module_pages_element::getElement("image");
        }
        catch (class_exception $objEx)  {
        }
        if($objElement == null) {
            $objElement = new class_module_pages_element();
            $objElement->setStrName("image");
            $objElement->setStrClassAdmin("class_element_image_admin.php");
            $objElement->setStrClassPortal("class_element_image_portal.php");
            $objElement->setIntCachetime(3600*24*30);
            $objElement->setIntRepeat(1);
            $objElement->setStrVersion($this->getVersion());
            $objElement->updateObjectToDb();
            $strReturn .= "Element registered...\n";
        }
        else {
            $strReturn .= "Element already installed!...\n";
        }

        $strReturn .= "Installing universal element table...\n";

        $arrFields = array();
        $arrFields["content_id"]= array("char20", false);
        $arrFields["char1"]		= array("char254", true);
        $arrFields["char2"] 	= array("char254", true);
        $arrFields["char3"]		= array("char254", true);
        $arrFields["int1"]		= array("int", true);
        $arrFields["int2"]		= array("int", true);
        $arrFields["int3"]		= array("int", true);
        $arrFields["text"]		= array("text", true);

        if(!$this->objDB->createTable("element_universal", $arrFields, array("content_id")))
            $strReturn .= "An error occured! ...\n";

		return $strReturn;

	}


	protected function updateModuleVersion($strModuleName, $strVersion) {
		parent::updateModuleVersion("pages", $strVersion);
        parent::updateModuleVersion("pages_content", $strVersion);
        parent::updateModuleVersion("folderview", $strVersion);
	}


	public function update() {
	    $strReturn = "";
        //check installed version and to which version we can update
        $arrModul = $this->getModuleData($this->arrModule["name"], false);

        $strReturn .= "Version found:\n\t Module: ".$arrModul["module_name"].", Version: ".$arrModul["module_version"]."\n\n";

        $arrModul = $this->getModuleData($this->arrModule["name"], false);
        if($arrModul["module_version"] == "3.4.0") {
            $strReturn .= $this->update_340_3401();
        }

        $arrModul = $this->getModuleData($this->arrModule["name"], false);
        if($arrModul["module_version"] == "3.4.0.1") {
            $strReturn .= $this->update_3401_3402();
        }

        $arrModul = $this->getModuleData($this->arrModule["name"], false);
        if($arrModul["module_version"] == "3.4.0.2") {
            $strReturn .= $this->update_3402_341();
        }

        $arrModul = $this->getModuleData($this->arrModule["name"], false);
        if($arrModul["module_version"] == "3.4.1") {
            $strReturn .= $this->update_341_349();
        }

        return $strReturn."\n\n";
	}



    private function update_340_3401() {
        $strReturn = "Updating 3.4.0 to 3.4.0.1...\n";

        $strReturn .= "Updating page_element-table...\n";
        $strQuery = "ALTER TABLE ".$this->objDB->encloseTableName(_dbprefix_."page_element")."
                    CHANGE ".$this->objDB->encloseColumnName("page_element_placeholder_placeholder")." ".$this->objDB->encloseColumnName("page_element_ph_placeholder")." ".$this->objDB->getDatatype("char254")." NOT NULL,
                    CHANGE ".$this->objDB->encloseColumnName("page_element_placeholder_name")." ".$this->objDB->encloseColumnName("page_element_ph_name")." ".$this->objDB->getDatatype("char254")." NULL,
                    CHANGE ".$this->objDB->encloseColumnName("page_element_placeholder_element")." ".$this->objDB->encloseColumnName("page_element_ph_element")." ".$this->objDB->getDatatype("char254")." NULL,
                    CHANGE ".$this->objDB->encloseColumnName("page_element_placeholder_title")." ".$this->objDB->encloseColumnName("page_element_ph_title")." ".$this->objDB->getDatatype("char254")." NULL,
                    CHANGE ".$this->objDB->encloseColumnName("page_element_placeholder_language")." ".$this->objDB->encloseColumnName("page_element_ph_language")." ".$this->objDB->getDatatype("char20")." NULL";
        if(!$this->objDB->_query($strQuery))
            $strReturn .= "An error occured! ...\n";

        $strReturn .= "Updating module-versions...\n";
        $this->updateModuleVersion("", "3.4.0.1");
        $strReturn .= "Updating element-version...\n";
        $this->updateElementVersion("row", "3.4.0.1");
        $this->updateElementVersion("paragraph", "3.4.0.1");
        $this->updateElementVersion("image", "3.4.0.1");
        return $strReturn;
    }

    private function update_3401_3402() {
        $strReturn = "Updating 3.4.0.1 to 3.4.0.2...\n";

        $strReturn .= "Deleting process-xml class...\n";
        $objFilesystem = new class_filesystem();
        if(!$objFilesystem->fileDelete("/admin/class_modul_pages_admin_xml.php"))
            $strReturn .= "Deletion of /admin/class_modul_pages_admin_xml.php failed!\n";

        $objModule = class_module_system_module::getModuleByName($this->arrModule["name"]);
        $objModule->setStrXmlNameAdmin("");
        $objModule->updateObjectToDb();

        $strReturn .= "Updating module-versions...\n";
        $this->updateModuleVersion("", "3.4.0.2");
        $strReturn .= "Updating element-version...\n";
        $this->updateElementVersion("row", "3.4.0.2");
        $this->updateElementVersion("paragraph", "3.4.0.2");
        $this->updateElementVersion("image", "3.4.0.2");
        return $strReturn;
    }

    private function update_3402_341() {
        $strReturn = "Updating 3.4.0.2 to 3.4.1...\n";
        $strReturn .= "Updating module-versions...\n";
        $this->updateModuleVersion("", "3.4.1");
        $strReturn .= "Updating element-version...\n";
        $this->updateElementVersion("row", "3.4.1");
        $this->updateElementVersion("paragraph", "3.4.1");
        $this->updateElementVersion("image", "3.4.1");
        return $strReturn;
    }

    private function update_341_349() {
        $strReturn = "Updating 3.4.1 to 3.4.9...\n";

        $strReturn .= "Setting new element-classes...\n";
        $arrElements = class_module_pages_element::getAllElements();
        /** @var class_module_pages_element $objOneElement */
        foreach($arrElements as $objOneElement) {
            $objOneElement->setStrClassAdmin(uniStrReplace(".php", "_admin.php", $objOneElement->getStrClassAdmin()));
            $objOneElement->setStrClassPortal(uniStrReplace(".php", "_portal.php", $objOneElement->getStrClassPortal()));
            $objOneElement->updateObjectToDb();
        }

        $strReturn .= "Pages & Folders\n";
        $arrRows = $this->objDB->getPArray("SELECT system_id, system_module_nr FROM "._dbprefix_."page, "._dbprefix_."system WHERE system_id = page_id AND (system_class IS NULL OR system_class = '')", array());
        foreach($arrRows as $arrOneRow) {
            $strQuery = "UPDATE "._dbprefix_."system SET system_class = ? where system_id = ?";
            if($arrOneRow["system_module_nr"] == _pages_folder_id_)
                $this->objDB->_pQuery($strQuery, array( 'class_module_pages_folder', $arrOneRow["system_id"] ) );
            else
                $this->objDB->_pQuery($strQuery, array( 'class_module_pages_page', $arrOneRow["system_id"] ) );
        }

        $strReturn .= "Elements\n";
        $arrRows = $this->objDB->getPArray("SELECT system_id FROM "._dbprefix_."page_element, "._dbprefix_."system WHERE system_id = page_element_id AND (system_class IS NULL OR system_class = '')", array());
        foreach($arrRows as $arrOneRow) {
            $strQuery = "UPDATE "._dbprefix_."system SET system_class = ? where system_id = ?";
            $this->objDB->_pQuery($strQuery, array( 'class_module_pages_pageelement', $arrOneRow["system_id"] ) );
        }


        $strReturn .= "Updating module-versions...\n";
        $this->updateModuleVersion("", "3.4.9");
        $strReturn .= "Updating element-version...\n";
        $this->updateElementVersion("row", "3.4.9");
        $this->updateElementVersion("paragraph", "3.4.9");
        $this->updateElementVersion("image", "3.4.9");
        return $strReturn;
    }
}
