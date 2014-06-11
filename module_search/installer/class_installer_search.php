<?php
/*"******************************************************************************************************
*   (c) 2004-2006 by MulchProductions, www.mulchprod.de                                                 *
*   (c) 2007-2014 by Kajona, www.kajona.de                                                              *
*       Published under the GNU LGPL v2.1, see /system/licence_lgpl.txt                                 *
*-------------------------------------------------------------------------------------------------------*
*	$Id$                                         *
********************************************************************************************************/


/**
 * Class providing the installer of the search-module
 *
 * @package module_search
 * @moduleId _search_module_id_
 */
class class_installer_search extends class_installer_base implements interface_installer_removable {

    public function install() {

        //Install Index Tables
        $strReturn = $this->install_index_tables();

        //Table for search
        $strReturn .= "Installing table search_search...\n";

        $arrFields = array();
        $arrFields["search_search_id"] 		= array("char20", false);
        $arrFields["search_search_query"] 	= array("char254", true);
        $arrFields["search_search_filter_modules"] 	= array("char254", true);

        $arrFields["search_search_private"] = array("int", true);

        if(!$this->objDB->createTable("search_search", $arrFields, array("search_search_id")))
            $strReturn .= "An error occurred! ...\n";

        //Table for search log entry
        $strReturn .= "Installing search-log table...\n";

        $arrFields = array();
		$arrFields["search_log_id"] 	  = array("char20", false);
		$arrFields["search_log_date"] 	  = array("int", true);
		$arrFields["search_log_query"] 	  = array("char254", true);
		$arrFields["search_log_language"] = array("char10", true);

		if(!$this->objDB->createTable("search_log", $arrFields, array("search_log_id")))
			$strReturn .= "An error occurred! ...\n";


        //Table for page-element
        $strReturn .= "Installing search-element table...\n";

        $arrFields = array();
        $arrFields["content_id"] 		= array("char20", false);
        $arrFields["search_template"] 	= array("char254", true);
        $arrFields["search_amount"] 	= array("int", true);
        $arrFields["search_page"] 		= array("char254", true);

        if(!$this->objDB->createTable("element_search", $arrFields, array("content_id")))
            $strReturn .= "An error occurred! ...\n";

		$strReturn .= "Registering module...\n";
		//register the module
		$this->registerModule("search", _search_module_id_, "class_module_search_portal.php", "class_module_search_admin.php", $this->objMetadata->getStrVersion() , true, "class_module_search_portal_xml.php");

        if(class_module_system_module::getModuleByName("pages") !== null && class_module_pages_element::getElement("search") == null) {
            $objElement = new class_module_pages_element();
            $objElement->setStrName("search");
            $objElement->setStrClassAdmin("class_element_search_admin.php");
            $objElement->setStrClassPortal("class_element_search_portal.php");
            $objElement->setIntCachetime(3600);
            $objElement->setIntRepeat(0);
            $objElement->setStrVersion($this->objMetadata->getStrVersion());
            $objElement->updateObjectToDb();
            $strReturn .= "Element registered...\n";
        }
        else {
            $strReturn .= "Element already installed!...\n";
        }

        $strReturn .= "Updating index...\n";
        class_module_system_module::flushCache();
        class_module_search_indexwriter::resetIndexAvailableCheck();
        $objWorker = new class_module_search_indexwriter();
        $objWorker->clearIndex();
        $objWorker->indexRebuild();


		return $strReturn;

	}

    /**
     * Validates whether the current module/element is removable or not.
     * This is the place to trigger special validations and consistency checks going
     * beyond the common metadata-dependencies.
     *
     * @return bool
     */
    public function isRemovable() {
        return true;
    }

    /**
     * Removes the elements / modules handled by the current installer.
     * Use the reference param to add a human readable logging.
     *
     * @param string &$strReturn
     *
     * @return bool
     */
    public function remove(&$strReturn) {
        //delete the page-element
        if(class_module_system_module::getModuleByName("pages") !== null && class_module_pages_element::getElement("search") != null) {
            $objElement = class_module_pages_element::getElement("search");
            if($objElement != null) {
                $strReturn .= "Deleting page-element 'search'...\n";
                $objElement->deleteObject();
            }
            else {
                $strReturn .= "Error finding page-element 'search', aborting.\n";
                return false;
            }
        }

        /** @var class_module_search_search $objOneObject */
        foreach(class_module_search_search::getObjectList() as $objOneObject) {
            $strReturn .= "Deleting object '".$objOneObject->getStrDisplayName()."' ...\n";
            if(!$objOneObject->deleteObject()) {
                $strReturn .= "Error deleting object, aborting.\n";
                return false;
            }
        }

        //delete the module-node
        $strReturn .= "Deleting the module-registration...\n";
        $objModule = class_module_system_module::getModuleByName($this->objMetadata->getStrTitle(), true);
        if(!$objModule->deleteObject()) {
            $strReturn .= "Error deleting module, aborting.\n";
            return false;
        }

        //delete the tables
        foreach(array("search_search", "search_log", "element_search", "search_ix_document", "search_ix_content") as $strOneTable) {
            $strReturn .= "Dropping table ".$strOneTable."...\n";
            if(!$this->objDB->_pQuery("DROP TABLE ".$this->objDB->encloseTableName(_dbprefix_.$strOneTable)."", array())) {
                $strReturn .= "Error deleting table, aborting.\n";
                return false;
            }

        }

        return true;
    }


    public function update() {
	    $strReturn = "";
        //check installed version and to which version we can update
        $arrModule = class_module_system_module::getPlainModuleData($this->objMetadata->getStrTitle(), false);

        $strReturn .= "Version found:\n\t Module: ".$arrModule["module_name"].", Version: ".$arrModule["module_version"]."\n\n";

        $arrModule = class_module_system_module::getPlainModuleData($this->objMetadata->getStrTitle(), false);
        if($arrModule["module_version"] == "3.4.2") {
            $strReturn .= $this->update_342_349();
        }

        $arrModule = class_module_system_module::getPlainModuleData($this->objMetadata->getStrTitle(), false);
        if($arrModule["module_version"] == "3.4.9") {
            $strReturn .= $this->update_342_3491();
        }

        $arrModule = class_module_system_module::getPlainModuleData($this->objMetadata->getStrTitle(), false);
        if($arrModule["module_version"] == "3.4.9.1") {
            $strReturn .= $this->update_3491_40();
        }

        $arrModule = class_module_system_module::getPlainModuleData($this->objMetadata->getStrTitle(), false);
        if($arrModule["module_version"] == "4.0") {
            $strReturn .= "Updating 4.0 to 4.1...\n";
            $strReturn .= "Updating module-versions...\n";
            $this->updateModuleVersion("search", "4.1");
            $this->updateElementVersion("search", "4.1");
        }

        $arrModule = class_module_system_module::getPlainModuleData($this->objMetadata->getStrTitle(), false);
        if($arrModule["module_version"] == "4.1") {
            $strReturn .= "Updating 4.1 to 4.2...\n";
            $strReturn .= "Updating module-versions...\n";
            $this->updateModuleVersion("search", "4.2");
            $this->updateElementVersion("search", "4.2");
        }

        $arrModule = class_module_system_module::getPlainModuleData($this->objMetadata->getStrTitle(), false);
        if($arrModule["module_version"] == "4.2") {
            $strReturn .= "Updating 4.2 to 4.3...\n";
            $strReturn .= "Updating module-versions...\n";
            $this->updateModuleVersion("search", "4.3");
            $this->updateElementVersion("search", "4.3");

       }

        $arrModule = class_module_system_module::getPlainModuleData($this->objMetadata->getStrTitle(), false);
        if($arrModule["module_version"] == "4.3") {
            $strReturn .= $this->update_43_44();
        }

        if($arrModule["module_version"] == "4.4") {
            $strReturn .= "Updating 4.4 to 4.4.1...\n";
            $strReturn .= "Updating module-versions...\n";
            $this->updateModuleVersion("search", "4.4.1");
            $this->updateElementVersion("search", "4.4.1");

        }

        $arrModule = class_module_system_module::getPlainModuleData($this->objMetadata->getStrTitle(), false);
        if($arrModule["module_version"] == "4.4.1") {
            $strReturn .= $this->update_441_45();
        }


        return $strReturn."\n\n";
	}


    private function update_342_349() {
        $strReturn = "Updating 3.4.2 to 3.4.9...\n";

        $strReturn .= "Registering search admin class...\n";
        $objModule = class_module_system_module::getModuleByName("search");
        $objModule->setStrNameAdmin("class_module_search_admin.php");
        $objModule->updateObjectToDb();

        $strReturn .= "Updating module-versions...\n";
        $this->updateModuleVersion("search", "3.4.9");
        $strReturn .= "Updating element-versions...\n";
        $this->updateElementVersion("search", "3.4.9");
        return $strReturn;
    }

    private function update_342_3491() {
        $strReturn = "Updating 3.4.9 to 3.4.9.1...\n";

        $strReturn .= "Make module visible in navigation...\n";
        $objModule = class_module_system_module::getModuleByName("search");
        $objModule->setIntNavigation(1);
        $objModule->updateObjectToDb();

        //Table for search
        $strReturn .= "Installing table search_search...\n";

        $arrFields = array();
        $arrFields["search_search_id"] 		= array("char20", false);
        $arrFields["search_search_query"] 	= array("char254", true);
        $arrFields["search_search_filter_modules"] 	= array("char254", true);
        $arrFields["search_search_private"] = array("int", true);

        if(!$this->objDB->createTable("search_search", $arrFields, array("search_search_id")))
            $strReturn .= "An error occurred! ...\n";

        $strReturn .= "Updating module-versions...\n";
        $this->updateModuleVersion("search", "3.4.9.1");
        $this->updateElementVersion("search", "3.4.9.1");
        return $strReturn;
    }

    private function update_3491_40() {
        $strReturn = "Updating 3.4.9.1 to 4.0...\n";
        $strReturn .= "Updating module-versions...\n";
        $this->updateModuleVersion("search", "4.0");
        $this->updateElementVersion("search", "4.0");
        return $strReturn;
    }

    private function update_43_44() {
        $strReturn = "Updating 4.3 to 4.4...\n";
        // Install Index
        $strReturn .= "Adding index tables...\n";
        $this->install_index_tables();

        $strReturn .= "Updating module-versions...\n";
        $this->updateModuleVersion("search", "4.4");
        $this->updateElementVersion("search", "4.4");

        $strReturn .= "Updating index...\n";
        class_module_system_module::flushCache();
        class_module_search_indexwriter::resetIndexAvailableCheck();
        $objWorker = new class_module_search_indexwriter();
        $objWorker->clearIndex();
        $objWorker->indexRebuild();


        return $strReturn;
    }

    private function update_441_45() {
        $strReturn = "Updating 4.4.1 to 4.5...\n";
        // Install Index
        $strReturn .= "Updating index tables...\n";
        $strQuery = "ALTER TABLE ".$this->objDB->encloseTableName(_dbprefix_."search_ix_document")."
                            ADD ".$this->objDB->encloseColumnName("search_ix_content_lang")." ".$this->objDB->getDatatype("char20")." NULL,
                            ADD ".$this->objDB->encloseColumnName("search_ix_portal_object")." ".$this->objDB->getDatatype("int")." NULL";

        if(!$this->objDB->_pQuery($strQuery, array()))
            $strReturn .= "An error occurred! ...\n";

        $this->objDB->_pQuery("ALTER TABLE ".$this->objDB->encloseTableName(_dbprefix_."search_ix_document")." ADD INDEX ( ".$this->objDB->encloseColumnName("search_ix_content_lang")." ) ", array());
        $this->objDB->_pQuery("ALTER TABLE ".$this->objDB->encloseTableName(_dbprefix_."search_ix_document")." ADD INDEX ( ".$this->objDB->encloseColumnName("search_ix_portal_object")." ) ", array());


        $strReturn .= "Removing old searchplugins...\n";
        $objFilesystem = new class_filesystem();
        foreach(class_resourceloader::getInstance()->getFolderContent("/admin/searchplugins/") as $strPath => $strFilename) {
            $strReturn .= "Deleting ".$strPath."\n";
            $objFilesystem->fileDelete($strPath);
        }
        foreach(class_resourceloader::getInstance()->getFolderContent("/portal/searchplugins/") as $strPath => $strFilename) {
            $strReturn .= "Deleting ".$strPath."\n";
            $objFilesystem->fileDelete($strPath);
        }


        $strReturn .= "Updating module-versions...\n";
        $this->updateModuleVersion("search", "4.5");
        $this->updateElementVersion("search", "4.5");

        $strReturn .= "Updating index...\n";
        class_module_system_module::flushCache();
        class_module_search_indexwriter::resetIndexAvailableCheck();
        $objWorker = new class_module_search_indexwriter();
        $objWorker->indexRebuild();


        return $strReturn;
    }

    private function install_index_tables() {

        //Tables for search documents
        $strReturn = "Installing table search_ix_document...\n";

        $arrFields = array();
        $arrFields["search_ix_document_id"] 		= array("char20", false);
        $arrFields["search_ix_system_id"] 	        = array("char20", true);
        $arrFields["search_ix_content_lang"] 	    = array("char20", true);
        $arrFields["search_ix_portal_object"] 	    = array("int", true);

        if(!$this->objDB->createTable("search_ix_document", $arrFields, array("search_ix_document_id"), array("search_ix_system_id", "search_ix_content_lang", "search_ix_portal_object"), false))
            $strReturn .= "An error occurred! ...\n";

        $strReturn .= "Installing table search_ix_content...\n";

        $arrFields = array();
        $arrFields["search_ix_content_id"] 		    = array("char20", false);
        $arrFields["search_ix_content_field_name"] 	= array("char254", false);
        $arrFields["search_ix_content_content"] 	= array("char254", true);
        $arrFields["search_ix_content_score"] 	    = array("int", true);
        $arrFields["search_ix_content_document_id"] = array("char20", true);

        if(!$this->objDB->createTable("search_ix_content", $arrFields, array("search_ix_content_id"), array("search_ix_content_field_name", "search_ix_content_content", "search_ix_content_document_id"), false))
           $strReturn .= "An error occurred! ...\n";

        return $strReturn;
    }

}
