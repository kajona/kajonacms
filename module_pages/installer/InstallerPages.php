<?php
/*"******************************************************************************************************
*   (c) 2004-2006 by MulchProductions, www.mulchprod.de                                                 *
*   (c) 2007-2015 by Kajona, www.kajona.de                                                              *
*       Published under the GNU LGPL v2.1, see /system/licence_lgpl.txt                                 *
*-------------------------------------------------------------------------------------------------------*
*   $Id$                                         *
********************************************************************************************************/

namespace Kajona\Pages\Installer;

use class_filesystem;
use class_installer_base;
use class_module_packagemanager_template;
use class_module_system_aspect;
use class_module_system_module;
use class_module_system_setting;
use class_orm_schemamanager;
use interface_installer;
use Kajona\Pages\System\PagesElement;

/**
 * Installer of the pages-module
 *
 * @package module_pages
 * @moduleId _pages_modul_id_
 */
class InstallerPages extends class_installer_base implements interface_installer {

	public function install() {

		$strReturn = "Installing ".$this->objMetadata->getStrTitle()."...\n";
        $objManager = new class_orm_schemamanager();

		$strReturn .= "Installing table pages...\n";
        $objManager->createTable("class_module_pages_page");

		$strReturn .= "Installing table page_folder...\n";
        $objManager->createTable("class_module_pages_folder");

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
			$strReturn .= "An error occurred! ...\n";

		$strReturn .= "Installing table element...\n";
        $objManager->createTable("class_module_pages_element");

		$strReturn .= "Installing table page_element...\n";
        $objManager->createTable("class_module_pages_pageelement");


		//Now we have to register module by module

		//the pages
		$this->registerModule("pages", _pages_modul_id_, "class_module_pages_portal.php", "class_module_pages_admin.php", $this->objMetadata->getStrVersion(), true);
		//The pages_content
		$this->registerModule("pages_content", _pages_content_modul_id_, "", "class_module_pages_content_admin.php", $this->objMetadata->getStrVersion(), false);


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

        $strReturn .= "Shifting pages to third position...\n";
        $objModule = class_module_system_module::getModuleByName("pages");
        $objModule->setAbsolutePosition(3);



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
            $strReturn .= "An error occurred! ...\n";

        //Table for paragraphes
        $strReturn .= "Installing paragraph table...\n";
        $objManager->createTable("class_element_paragraph_admin");

        //Table for page-element
        $strReturn .= "Installing gallery-element table...\n";
        $objManager->createTable("class_element_gallery_admin");

        //Table for page-element
        $strReturn .= "Installing downloads-element table...\n";
        $objManager->createTable("class_element_downloads_admin");

        //Table for images
        $strReturn .= "Installing image table...\n";
        $objManager->createTable("class_element_image_admin");


        $arrElements = array(
            "row" => array("ElementRowAdmin.php", "ElementRowPortal.php"),
            "paragraph" => array("ElementParagraphAdmin.php", "ElementParagraphPortal.php"),
            "image" => array("ElementImageAdmin.php", "ElementImagePortal.php"),
            "downloads" => array("class_element_downloads_admin.php", "class_element_downloads_portal.php"),
            "gallery" => array("class_element_gallery_admin.php", "class_element_gallery_portal.php"),
            "galleryRandom" => array("class_element_galleryRandom_admin.php", "class_element_gallery_portal.php"),
            "blocks" => array("ElementBlocksAdmin.php", "ElementBlocksPortal.php"),
            "block" => array("ElementBlockAdmin.php", "ElementBlockPortal.php"),
            "date" => array("ElementDateAdmin.php", "ElementDatePortal.php"),
            "plaintext" => array("ElementPlaintextAdmin.php", "ElementPlaintextPortal.php"),
            "richtext" => array("ElementRichtextAdmin.php", "ElementRichtextPortal.php"),
        );

        foreach($arrElements as $strOneElement => $arrConfig) {

            //Register the element
            $strReturn .= "Registering element ".$strOneElement."...\n";
            //check, if not already existing
            $objElement = PagesElement::getElement($strOneElement);
            if ($objElement == null) {
                $objElement = new PagesElement();
                $objElement->setStrName($strOneElement);
                $objElement->setStrClassAdmin($arrConfig[0]);
                $objElement->setStrClassPortal($arrConfig[1]);
                $objElement->setIntCachetime(3600);
                $objElement->setIntRepeat(1);
                $objElement->setStrVersion($this->objMetadata->getStrVersion());
                $objElement->updateObjectToDb();
                $strReturn .= "Element registered...\n";
            }
            else {
                $strReturn .= "Element already installed!...\n";
            }

        }




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
	}


	public function update() {
	    $strReturn = "";
        //check installed version and to which version we can update
        $arrModule = class_module_system_module::getPlainModuleData($this->objMetadata->getStrTitle(), false);
        $strReturn .= "Version found:\n\t Module: ".$arrModule["module_name"].", Version: ".$arrModule["module_version"]."\n\n";


        $arrModule = class_module_system_module::getPlainModuleData($this->objMetadata->getStrTitle(), false);
        if($arrModule["module_version"] == "4.0") {
            $strReturn .= $this->update_40_401();
        }

        $arrModule = class_module_system_module::getPlainModuleData($this->objMetadata->getStrTitle(), false);
        if($arrModule["module_version"] == "4.0.1") {
            $strReturn .= $this->update_401_41();
        }

        $arrModule = class_module_system_module::getPlainModuleData($this->objMetadata->getStrTitle(), false);
        if($arrModule["module_version"] == "4.1") {
            $strReturn .= $this->update_41_42();
        }

        $arrModule = class_module_system_module::getPlainModuleData($this->objMetadata->getStrTitle(), false);
        if($arrModule["module_version"] == "4.2") {
            $strReturn .= $this->update_42_43();
        }

        $arrModule = class_module_system_module::getPlainModuleData($this->objMetadata->getStrTitle(), false);
        if($arrModule["module_version"] == "4.3") {
            $strReturn = "Updating 4.3 to 4.4...\n";
            $this->updateModuleVersion("", "4.4");
            $this->updateElementVersion("row", "4.4");
            $this->updateElementVersion("paragraph", "4.4");
            $this->updateElementVersion("image", "4.4");
        }

        $arrModule = class_module_system_module::getPlainModuleData($this->objMetadata->getStrTitle(), false);
        if($arrModule["module_version"] == "4.4") {
            $strReturn = "Updating 4.4 to 4.5...\n";
            $this->updateModuleVersion("", "4.5");
            $this->updateElementVersion("row", "4.5");
            $this->updateElementVersion("paragraph", "4.5");
            $this->updateElementVersion("image", "4.5");
        }

        $arrModule = class_module_system_module::getPlainModuleData($this->objMetadata->getStrTitle(), false);
        if($arrModule["module_version"] == "4.5") {
            $strReturn = "Updating 4.5 to 4.6...\n";
            $this->updateModuleVersion("", "4.6");
            $this->updateElementVersion("row", "4.6");
            $this->updateElementVersion("paragraph", "4.6");
            $this->updateElementVersion("image", "4.6");
        }

        $arrModule = class_module_system_module::getPlainModuleData($this->objMetadata->getStrTitle(), false);
        if($arrModule["module_version"] == "4.6") {
            $strReturn = "Updating 4.6 to 4.6.1...\n";
            $this->updateModuleVersion("", "4.6.1");
            $this->updateElementVersion("row", "4.6.1");
            $this->updateElementVersion("paragraph", "4.6.1");
            $this->updateElementVersion("image", "4.6.1");
        }

        $arrModule = class_module_system_module::getPlainModuleData($this->objMetadata->getStrTitle(), false);
        if($arrModule["module_version"] == "4.6.1") {
            $strReturn = "Updating 4.6.1 to 4.6.2...\n";
            $this->updateModuleVersion("", "4.6.2");
            $this->updateElementVersion("row", "4.6.2");
            $this->updateElementVersion("paragraph", "4.6.2");
            $this->updateElementVersion("image", "4.6.2");
        }

        $arrModule = class_module_system_module::getPlainModuleData($this->objMetadata->getStrTitle(), false);
        if($arrModule["module_version"] == "4.6.2") {
            $strReturn = "Updating to 4.7...\n";
            $this->updateModuleVersion("", "4.7");
            $this->updateElementVersion("row", "4.7");
            $this->updateElementVersion("paragraph", "4.7");
            $this->updateElementVersion("image", "4.7");
        }

        $arrModule = class_module_system_module::getPlainModuleData($this->objMetadata->getStrTitle(), false);
        if($arrModule["module_version"] == "4.7") {
            $strReturn = "Updating to 4.7.1...\n";
            $this->updateModuleVersion("", "4.7.1");
            $this->updateElementVersion("row", "4.7.1");
            $this->updateElementVersion("paragraph", "4.7.1");
            $this->updateElementVersion("image", "4.7.1");
        }

        $arrModule = class_module_system_module::getPlainModuleData($this->objMetadata->getStrTitle(), false);
        if($arrModule["module_version"] == "4.7.1") {
            $strReturn = "Updating to 4.7.2...\n";
            $this->updateModuleVersion("", "4.7.2");
            $this->updateElementVersion("row", "4.7.2");
            $this->updateElementVersion("paragraph", "4.7.2");
            $this->updateElementVersion("image", "4.7.2");
            $this->updateElementVersion("gallery", "4.7.2");
            $this->updateElementVersion("galleryRandom", "4.7.2");
            $this->updateElementVersion("downloads", "4.7.2");
        }

        return $strReturn."\n\n";
	}


    private function update_40_401() {
        $strReturn = "Updating 4.0 to 4.0.1...\n";

        $strReturn .= "Removing i18n support for folder-names...\n";

        $strReturn .= "Installing table page_folder...\n";
        $arrFields = array();
        $arrFields["folder_id"]           = array("char20", false);
        $arrFields["folder_name"]         = array("char254", true);

        if(!$this->objDB->createTable("page_folder", $arrFields, array("folder_id")))
            $strReturn .= "An error occurred! ...\n";

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
            $strReturn .= "An error occurred! ...\n";

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

    private function update_41_42() {
        $strReturn = "Updating 4.1 to 4.2...\n";
        $strReturn .= "Updating module-versions...\n";
        $this->updateModuleVersion("", "4.2");

        $strReturn .= "Updating element-version...\n";
        $this->updateElementVersion("row", "4.2");
        $this->updateElementVersion("paragraph", "4.2");
        $this->updateElementVersion("image", "4.2");

        return $strReturn;
    }


    private function update_42_43() {
        $strReturn = "Updating 4.2 to 4.3...\n";

        $strReturn .= "Changing placeholder column data-type...\n";

        $strReturn .= "Creating temp-table...\n";
        $strReturn .= "Installing table page_element...\n";

        $arrFields = array();
        $arrFields["page_element_id"] 					= array("char20", false);
        $arrFields["page_element_ph_placeholder"]       = array("text", true);
        $arrFields["page_element_ph_name"]              = array("char254", true);
        $arrFields["page_element_ph_element"]           = array("char254", true);
        $arrFields["page_element_ph_title"]             = array("char254", true);
        $arrFields["page_element_ph_language"]          = array("char20", true);

        if(!$this->objDB->createTable("page_element_temp", $arrFields, array("page_element_id"), array("page_element_ph_language", "page_element_ph_element")))
            $strReturn .= "An error occurred! ...\n";


        $strReturn .= "Copying table content...\n";
        $strQuery = "INSERT INTO "._dbprefix_."page_element_temp
                        (page_element_id, page_element_ph_placeholder, page_element_ph_name, page_element_ph_element, page_element_ph_title, page_element_ph_language)
                       SELECT page_element_id, page_element_ph_placeholder, page_element_ph_name, page_element_ph_element, page_element_ph_title, page_element_ph_language FROM "._dbprefix_."page_element";

        if(!$this->objDB->_query($strQuery))
            $strReturn .= "An error occurred! ...\n";


        $strReturn .= "Dropping old table...\n";
        $strQuery = "DROP TABLE ".$this->objDB->encloseTableName(_dbprefix_."page_element")."";

        if(!$this->objDB->_query($strQuery))
            $strReturn .= "An error occurred! ...\n";

        $strReturn .= "Renaming new table...\n";
        $strQuery = "ALTER TABLE ".$this->objDB->encloseTableName(_dbprefix_."page_element_temp")." RENAME TO ".$this->objDB->encloseTableName(_dbprefix_."page_element")."";
        if(!$this->objDB->_query($strQuery))
            $strReturn .= "An error occurred! ...\n";




        $strReturn .= "Copying default-template, if in use. Placeholders changed in 4.3\n";
        if(class_module_system_setting::getConfigValue("_packagemanager_defaulttemplate_") == "default") {

            $objFS = new class_filesystem();
            $objFS->folderCreate("/templates/kajona42/tpl/module_pages", true);
            $objFS->fileCopy("/core/module_pages/installer/standard.tpl.42", "/templates/kajona42/tpl/module_pages/standard.tpl");

            class_module_packagemanager_template::syncTemplatepacks();

            /** @var $objOnePack class_module_packagemanager_template */
            foreach(class_module_packagemanager_template::getObjectList() as $objOnePack) {
                if($objOnePack->getStrName() == "kajona42") {
                    $objOnePack->setIntRecordStatus(1);
                    $objOnePack->updateObjectToDb();
                }
            }

            $objSetting = class_module_system_setting::getConfigByName("_packagemanager_defaulttemplate_");
            $objSetting->setStrValue("kajona42");
            $objSetting->updateObjectToDb();

        }




        $strReturn .= "Updating module-versions...\n";
        $this->updateModuleVersion("", "4.3");

        $strReturn .= "Updating element-version...\n";
        $this->updateElementVersion("row", "4.3");
        $this->updateElementVersion("paragraph", "4.3");
        $this->updateElementVersion("image", "4.3");

        return $strReturn;
    }

}
