<?php
/*"******************************************************************************************************
*   (c) 2004-2006 by MulchProductions, www.mulchprod.de                                                 *
*   (c) 2007-2013 by Kajona, www.kajona.de                                                              *
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
        $this->objMetadata = new class_module_packagemanager_metadata();
        $this->objMetadata->autoInit(uniStrReplace(array(DIRECTORY_SEPARATOR."installer", _realpath_), array("", ""), __DIR__));

        $this->setArrModuleEntry("moduleId", _pages_modul_id_);
		parent::__construct();
	}

	public function install() {

		$strReturn = "Installing ".$this->objMetadata->getStrTitle()."...\n";
		//Tabellen anlegen

		//Pages -----------------------------------------------------------------------------------------
		$strReturn .= "Installing table pages...\n";

		$arrFields = array();
		$arrFields["page_id"] 		= array("char20", false);
		$arrFields["page_name"] 	= array("char254", true);
		$arrFields["page_type"] 	= array("int", true, "0");

		if(!$this->objDB->createTable("page", $arrFields, array("page_id")))
			$strReturn .= "An error occured! ...\n";

		//folder ----------------------------------------------------------------------------------------
		$strReturn .= "Installing table page_folder...\n";

		$arrFields = array();
		$arrFields["folder_id"]           = array("char20", false);
		$arrFields["folder_name"]         = array("char254", true);

		if(!$this->objDB->createTable("page_folder", $arrFields, array("folder_id")))
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
        $arrFields["pageproperties_path"] 	    = array("char254", true);
        $arrFields["pageproperties_target"] 	= array("char254", true);

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
		$arrFields["element_config1"] 	    = array("char254", true);
		$arrFields["element_config2"] 	    = array("char254", true);
		$arrFields["element_config3"] 	    = array("text", true);

		if(!$this->objDB->createTable("element", $arrFields, array("element_id"), array("element_name")))
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

		if(!$this->objDB->createTable("page_element", $arrFields, array("page_element_id"), array("page_element_ph_placeholder", "page_element_ph_language", "page_element_ph_element")))
			$strReturn .= "An error occured! ...\n";


		//Now we have to register module by module

		//the pages
		$this->registerModule("pages", _pages_modul_id_, "class_module_pages_portal.php", "class_module_pages_admin.php", $this->objMetadata->getStrVersion(), true);
		//The pages_content
		$this->registerModule("pages_content", _pages_content_modul_id_, "", "class_module_pages_content_admin.php", $this->objMetadata->getStrVersion(), false);
		//The folderview
		$this->registerModule("folderview", _pages_folderview_modul_id_, "", "class_module_folderview_admin.php", $this->objMetadata->getStrVersion() , false);

		$strReturn .= "Registering system-constants...\n";
		$this->registerConstant("_pages_templatechange_", "false", class_module_system_setting::$int_TYPE_BOOL, _pages_modul_id_);
		$this->registerConstant("_pages_indexpage_", "index", class_module_system_setting::$int_TYPE_PAGE, _pages_modul_id_);
		$this->registerConstant("_pages_errorpage_", "error", class_module_system_setting::$int_TYPE_PAGE, _pages_modul_id_);
		$this->registerConstant("_pages_defaulttemplate_", "standard.tpl", class_module_system_setting::$int_TYPE_STRING, _pages_modul_id_);
		//2.1.1: overall cachetime
		$this->registerConstant("_pages_cacheenabled_", "true", class_module_system_setting::$int_TYPE_BOOL, _pages_modul_id_);
		//2.1.1: possibility, to create new pages disabled
		$this->registerConstant("_pages_newdisabled_", "false", class_module_system_setting::$int_TYPE_BOOL, _pages_modul_id_);
		//portaleditor
        $this->registerConstant("_pages_portaleditor_", "true", class_module_system_setting::$int_TYPE_BOOL, _pages_modul_id_);

        $strReturn .= "Shifting pages to first position...\n";
        $objModule = class_module_system_module::getModuleByName("pages");
        $objModule->setAbsolutePosition(1);







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
            $objElement->setStrVersion($this->objMetadata->getStrVersion());
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
            $objElement->setStrVersion($this->objMetadata->getStrVersion());
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
            $objElement->setStrVersion($this->objMetadata->getStrVersion());
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


        $strReturn .= "Setting aspect assignments...\n";
        if(class_module_system_aspect::getAspectByName("content") != null) {
            $objModule = class_module_system_module::getModuleByName($this->objMetadata->getStrTitle());
            $objModule->setStrAspect(class_module_system_aspect::getAspectByName("content")->getSystemid());
            $objModule->updateObjectToDb();
        }

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
        $arrModul = class_module_system_module::getPlainModuleData($this->objMetadata->getStrTitle(), false);
        $strReturn .= "Version found:\n\t Module: ".$arrModul["module_name"].", Version: ".$arrModul["module_version"]."\n\n";

        $arrModul = class_module_system_module::getPlainModuleData($this->objMetadata->getStrTitle(), false);
        if($arrModul["module_version"] == "3.4.2") {
            $strReturn .= $this->update_342_349();
        }

        $arrModul = class_module_system_module::getPlainModuleData($this->objMetadata->getStrTitle(), false);
        if($arrModul["module_version"] == "3.4.9") {
            $strReturn .= $this->update_349_3491();
        }
        
        $arrModul = class_module_system_module::getPlainModuleData($this->objMetadata->getStrTitle(), false);
        if($arrModul["module_version"] == "3.4.9.1") {
            $strReturn .= $this->update_3491_3492();
        }

        $arrModul = class_module_system_module::getPlainModuleData($this->objMetadata->getStrTitle(), false);
        if($arrModul["module_version"] == "3.4.9.2") {
            $strReturn .= $this->update_3492_3493();
        }

        $arrModul = class_module_system_module::getPlainModuleData($this->objMetadata->getStrTitle(), false);
        if($arrModul["module_version"] == "3.4.9.3") {
            $strReturn .= $this->update_3493_40();
        }

        $arrModul = class_module_system_module::getPlainModuleData($this->objMetadata->getStrTitle(), false);
        if($arrModul["module_version"] == "4.0") {
            $strReturn .= $this->update_40_401();
        }

        $arrModul = class_module_system_module::getPlainModuleData($this->objMetadata->getStrTitle(), false);
        if($arrModul["module_version"] == "4.0.1") {
            $strReturn .= $this->update_401_41();
        }

        return $strReturn."\n\n";
	}



    private function update_342_349() {
        $strReturn = "Updating 3.4.2 to 3.4.9...\n";

        $strReturn .= "Altering element-table...\n";

        $strQuery = "ALTER TABLE ".$this->objDB->encloseTableName(_dbprefix_."element")."
                    ADD ".$this->objDB->encloseColumnName("element_config1")." ".$this->objDB->getDatatype("char254")." NULL,
                    ADD ".$this->objDB->encloseColumnName("element_config2")." ".$this->objDB->getDatatype("char254")." NULL,
                    ADD ".$this->objDB->encloseColumnName("element_config3")." ".$this->objDB->getDatatype("text")." NULL";
        if(!$this->objDB->_query($strQuery))
            $strReturn .= "An error occured! ...\n";

        $strReturn .= "Setting new element-classes...\n";
        $strQuery = "SELECT * FROM "._dbprefix_."element";
        $arrElements = $this->objDB->getPArray($strQuery, array());
        foreach($arrElements as $arrOneRow) {
            $strQuery = "UPDATE "._dbprefix_."element SET element_class_portal = ?, element_class_admin = ? WHERE element_id = ?";
            $this->objDB->_pQuery(
                $strQuery,
                array(
                    uniStrReplace(".php", "_portal.php", $arrOneRow["element_class_portal"]),
                    uniStrReplace(".php", "_admin.php", $arrOneRow["element_class_admin"]),
                    $arrOneRow["element_id"]
                )
            );
        }

        $arrElementData = $this->objDB->getPArray("SELECT * FROM "._dbprefix_."element", array());
        foreach($arrElementData as $arrOneRow) {

            $strReturn .= "Updating element classes for element ".$arrOneRow["element_name"]."\n";

            $strQuery = "UPDATE "._dbprefix_."element
                            SET element_class_portal = ?,
                                element_class_admin = ?
                          WHERE element_id = ?";
            $this->objDB->_pQuery(
                $strQuery,
                array(
                    uniStrReplace(".php", "_portal.php", $arrOneRow["element_class_portal"]),
                    uniStrReplace(".php", "_admin.php", $arrOneRow["element_class_admin"]),
                    $arrOneRow["element_id"]
                )
            );

        }


        $strReturn .= "Pages & Folders\n";
        $arrRows = $this->objDB->getPArray("SELECT system_id, system_module_nr FROM "._dbprefix_."page, "._dbprefix_."system WHERE system_id = page_id AND (system_class IS NULL OR system_class = '')", array());
        foreach($arrRows as $arrOneRow) {
            $strQuery = "UPDATE "._dbprefix_."system SET system_class = ? where system_id = ?";
            $this->objDB->_pQuery($strQuery, array( 'class_module_pages_page', $arrOneRow["system_id"] ) );
        }

        $strReturn .= "Pages & Folders\n";
        $arrRows = $this->objDB->getPArray("SELECT system_id FROM "._dbprefix_."system WHERE system_module_nr = ? AND system_prev_id != '0' AND (system_class IS NULL OR system_class = '')", array(_pages_folder_id_));
        foreach($arrRows as $arrOneRow) {
            $strQuery = "UPDATE "._dbprefix_."system SET system_class = ? where system_id = ?";
            $this->objDB->_pQuery($strQuery, array( 'class_module_pages_folder', $arrOneRow["system_id"] ) );
        }

        $strReturn .= "Elements\n";
        $arrRows = $this->objDB->getPArray("SELECT system_id FROM "._dbprefix_."page_element, "._dbprefix_."system WHERE system_id = page_element_id AND (system_class IS NULL OR system_class = '')", array());
        foreach($arrRows as $arrOneRow) {
            $strQuery = "UPDATE "._dbprefix_."system SET system_class = ? where system_id = ?";
            $this->objDB->_pQuery($strQuery, array( 'class_module_pages_pageelement', $arrOneRow["system_id"] ) );
        }


        $strReturn .= "Updating module-versions...\n";
        $this->updateModuleVersion("", "3.4.9");
        return $strReturn;
    }

    private function update_349_3491() {
        $strReturn = "Updating 3.4.9 to 3.4.9.1...\n";

        $this->objDB->flushQueryCache();
        $strReturn .= "Migrating elements to real records...\n";
        $strQuery = "SELECT * FROM "._dbprefix_."element ";
        $arrRows = $this->objDB->getPArray($strQuery, array());

        foreach($arrRows as $arrOneRow) {
            $objElement = new class_module_pages_element();
            $objElement->setStrName($arrOneRow["element_name"]);
            $objElement->setIntRepeat($arrOneRow["element_repeat"]);
            $objElement->setStrVersion($arrOneRow["element_version"]);
            $objElement->setIntCachetime($arrOneRow["element_cachetime"]);
            $objElement->setStrClassAdmin($arrOneRow["element_class_admin"]);
            $objElement->setStrClassPortal($arrOneRow["element_class_portal"]);

            $objElement->updateObjectToDb();

            $strQuery = "DELETE FROM "._dbprefix_."element WHERE element_id = ?";
            $this->objDB->_pQuery($strQuery, array($arrOneRow["element_id"]));
        }

        $strReturn .= "Setting aspect assignments...\n";
        if(class_module_system_aspect::getAspectByName("content") != null) {
            $objModule = class_module_system_module::getModuleByName($this->objMetadata->getStrTitle());
            $objModule->setStrAspect(class_module_system_aspect::getAspectByName("content")->getSystemid());
            $objModule->updateObjectToDb();
        }

        $strReturn .= "Updating module-versions...\n";
        $this->updateModuleVersion("", "3.4.9.1");
        $strReturn .= "Updating element-version...\n";
        $this->updateElementVersion("row", "3.4.9.1");
        $this->updateElementVersion("paragraph", "3.4.9.1");
        $this->updateElementVersion("image", "3.4.9.1");
        return $strReturn;
    }
    
    private function update_3491_3492() {
        $strReturn = "Updating 3.4.9.1 to 3.4.9.2...\n";
        
        $strReturn .= "Altering page_properties-table...\n";
        $strQuery = "ALTER TABLE ".$this->objDB->encloseTableName(_dbprefix_."page_properties")."
                    ADD ".$this->objDB->encloseColumnName("pageproperties_path")." ".$this->objDB->getDatatype("char254")." NULL";
        if(!$this->objDB->_query($strQuery))
            $strReturn .= "An error occured! ...\n";
        
        $strReturn .= "Updating module-versions...\n";
        $this->updateModuleVersion("", "3.4.9.2");

        $strReturn .= "Updating element-version...\n";
        $this->updateElementVersion("row", "3.4.9.2");
        $this->updateElementVersion("paragraph", "3.4.9.2");
        $this->updateElementVersion("image", "3.4.9.2");
        
        return $strReturn;
    }

    private function update_3492_3493() {
        $strReturn = "Updating 3.4.9.2 to 3.4.9.3...\n";

        $strReturn .= "Adding index to table element\n";
        $this->objDB->_pQuery("ALTER TABLE ".$this->objDB->encloseTableName(_dbprefix_."element")." ADD INDEX ( ".$this->objDB->encloseColumnName("element_name")." ) ", array());

        $strReturn .= "Adding index to table page_element\n";
        $this->objDB->_pQuery("ALTER TABLE ".$this->objDB->encloseTableName(_dbprefix_."page_element")." ADD INDEX ( ".$this->objDB->encloseColumnName("page_element_ph_placeholder")." ) ", array());
        $this->objDB->_pQuery("ALTER TABLE ".$this->objDB->encloseTableName(_dbprefix_."page_element")." ADD INDEX ( ".$this->objDB->encloseColumnName("page_element_ph_language")." ) ", array());
        $this->objDB->_pQuery("ALTER TABLE ".$this->objDB->encloseTableName(_dbprefix_."page_element")." ADD INDEX ( ".$this->objDB->encloseColumnName("page_element_ph_element")." ) ", array());

        $strReturn .= "Updating module-versions...\n";
        $this->updateModuleVersion("", "3.4.9.3");

        $strReturn .= "Updating element-version...\n";
        $this->updateElementVersion("row", "3.4.9.3");
        $this->updateElementVersion("paragraph", "3.4.9.3");
        $this->updateElementVersion("image", "3.4.9.3");

        return $strReturn;
    }

    private function update_3493_40() {
        $strReturn = "Updating 3.4.9.3 to 4.0...\n";


        $strReturn .= "Updating module-versions...\n";
        $this->updateModuleVersion("", "4.0");

        $strReturn .= "Updating element-version...\n";
        $this->updateElementVersion("row", "4.0");
        $this->updateElementVersion("paragraph", "4.0");
        $this->updateElementVersion("image", "4.0");

        return $strReturn;
    }

    private function update_40_401() {
        $strReturn = "Updating 4.0 to 4.0.1...\n";

        $strReturn .= "Removing i18n support for folder-names...\n";

        $strReturn .= "Installing table page_folder...\n";
        $arrFields = array();
        $arrFields["folder_id"]           = array("char20", false);
        $arrFields["folder_name"]         = array("char254", true);

        if(!$this->objDB->createTable("page_folder", $arrFields, array("folder_id")))
            $strReturn .= "An error occured! ...\n";

        $arrInserted = array();
        $strQuery = "SELECT * FROM "._dbprefix_."page_folderproperties";
        $arrRows = $this->objDB->getPArray($strQuery, array());
        foreach($arrRows as $arrOneRow) {
            if(!in_array($arrOneRow["folderproperties_id"], $arrInserted)) {
                $strQuery = "INSERT INTO "._dbprefix_."page_folder (folder_id, folder_name) VALUES (?, ?)";
                $this->objDB->_pQuery($strQuery, array($arrOneRow["folderproperties_id"], $arrOneRow["folderproperties_name"]));
                $arrInserted[] = $arrOneRow["folderproperties_id"];
            }
        }

        $strQuery = "DROP TABLE "._dbprefix_."page_folderproperties";
        $this->objDB->_pQuery($strQuery, array());



        $strReturn .= "Updating module-versions...\n";
        $this->updateModuleVersion("", "4.0.1");

        $strReturn .= "Updating element-version...\n";
        $this->updateElementVersion("row", "4.0.1");
        $this->updateElementVersion("paragraph", "4.0.1");
        $this->updateElementVersion("image", "4.0.1");

        return $strReturn;
    }

    private function update_401_41() {
        $strReturn = "Updating 4.0.1 to 4.1...\n";

        $strReturn .= "Altering page_properties-table...\n";
        $strQuery = "ALTER TABLE ".$this->objDB->encloseTableName(_dbprefix_."page_properties")."
                             ADD ".$this->objDB->encloseColumnName("pageproperties_target")." ".$this->objDB->getDatatype("char254")." NULL";
        if(!$this->objDB->_query($strQuery))
            $strReturn .= "An error occured! ...\n";

        $strReturn .= "Deleting legacy js-scripts...\n";
        $objFilesystem = new class_filesystem();
        $objFilesystem->folderDeleteRecursive("/core/module_pages/admin/scripts/halloeditor");
        $objFilesystem->folderDeleteRecursive("/core/module_pages/admin/scripts/rangy");

        $strReturn .= "Updating module-versions...\n";
        $this->updateModuleVersion("", "4.1");

        $strReturn .= "Updating element-version...\n";
        $this->updateElementVersion("row", "4.1");
        $this->updateElementVersion("paragraph", "4.1");
        $this->updateElementVersion("image", "4.1");

        return $strReturn;
    }

}
