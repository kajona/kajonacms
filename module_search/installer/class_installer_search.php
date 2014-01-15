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
class class_installer_search extends class_installer_base implements interface_installer {

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
            $strReturn .= "An error occured! ...\n";

        //Table for search log entry
        $strReturn .= "Installing search-log table...\n";

        $arrFields = array();
		$arrFields["search_log_id"] 	  = array("char20", false);
		$arrFields["search_log_date"] 	  = array("int", true);
		$arrFields["search_log_query"] 	  = array("char254", true);
		$arrFields["search_log_language"] = array("char10", true);

		if(!$this->objDB->createTable("search_log", $arrFields, array("search_log_id")))
			$strReturn .= "An error occured! ...\n";


        //Table for page-element
        $strReturn .= "Installing search-element table...\n";

        $arrFields = array();
        $arrFields["content_id"] 		= array("char20", false);
        $arrFields["search_template"] 	= array("char254", true);
        $arrFields["search_amount"] 	= array("int", true);
        $arrFields["search_page"] 		= array("char254", true);

        if(!$this->objDB->createTable("element_search", $arrFields, array("content_id")))
            $strReturn .= "An error occured! ...\n";

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


		return $strReturn;

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
            $strReturn .= "Updating 4.3 to 4.4...\n";
            // Install Index
            $strReturn .= "Adding index tables...\n";
            $this->install_index_tables();

            $strReturn .= "Updating index...\n";
            $objWorker = new class_module_search_indexwriter();
            $objWorker->clearIndex();
            $objWorker->indexRebuild();

            $strReturn .= "Updating module-versions...\n";
            $this->updateModuleVersion("search", "4.4");
            $this->updateElementVersion("search", "4.4");

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
            $strReturn .= "An error occured! ...\n";

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

    private function install_index_tables() {

        //Tables for search documents
        $strReturn = "Installing table search_index_document...\n";

        $arrFields = array();
        $arrFields["search_index_document_id"] 		= array("char20", false);
        $arrFields["search_index_system_id"] 	= array("char20", true);

        if(!$this->objDB->createTable("search_index_document", $arrFields, array("search_index_document_id")))
            $strReturn .= "An error occured! ...\n";

        $strReturn .= "Installing table search_index_content...\n";

        $arrFields = array();
        $arrFields["search_index_content_id"] 		= array("char20", false);
        $arrFields["search_index_content_field_name"] 		= array("char20", false);
        $arrFields["search_index_content_content"] 	= array("char256", true);
        $arrFields["search_index_content_score"] 	= array("int", true);
        $arrFields["search_index_content_document_id"] 	= array("char20", true);

        if(!$this->objDB->createTable("search_index_content", $arrFields, array("search_index_content_id"), array("search_index_content_field_name", "search_index_content_content", "search_index_content_document_id")))
           $strReturn .= "An error occured! ...\n";

        return $strReturn;
    }

}
