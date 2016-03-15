<?php
/*"******************************************************************************************************
*   (c) 2004-2006 by MulchProductions, www.mulchprod.de                                                 *
*   (c) 2007-2016 by Kajona, www.kajona.de                                                              *
*       Published under the GNU LGPL v2.1, see /system/licence_lgpl.txt                                 *
*-------------------------------------------------------------------------------------------------------*
*	$Id$                                         *
********************************************************************************************************/

namespace Kajona\Search\Installer;

use Kajona\Packagemanager\System\PackagemanagerManager;
use Kajona\Pages\System\PagesElement;
use Kajona\Search\System\SearchIndexwriter;
use Kajona\Search\System\SearchSearch;
use Kajona\System\System\Carrier;
use Kajona\System\System\Filesystem;
use Kajona\System\System\InstallerBase;
use Kajona\System\System\InstallerRemovableInterface;
use Kajona\System\System\OrmSchemamanager;
use Kajona\System\System\Resourceloader;
use Kajona\System\System\SystemModule;
use Kajona\System\System\SystemSetting;

/**
 * Class providing the installer of the search-module
 *
 * @package module_search
 * @moduleId _search_module_id_
 */
class InstallerSearch extends InstallerBase implements InstallerRemovableInterface {

    private $bitIndexRebuild = false;
    private $bitIndexTablesUpToDate = false;

    public function install() {

        $objManager = new OrmSchemamanager();
        //Install Index Tables
        $strReturn = $this->installIndexTables();

        //Table for search
        $strReturn .= "Installing table search_search...\n";
        $objManager->createTable("Kajona\\Search\\System\\SearchSearch");

        //Table for search log entry
        $strReturn .= "Installing search-log table...\n";

        $arrFields = array();
		$arrFields["search_log_id"] 	  = array("char20", false);
		$arrFields["search_log_date"] 	  = array("int", true);
		$arrFields["search_log_query"] 	  = array("char254", true);
		$arrFields["search_log_language"] = array("char10", true);

		if(!$this->objDB->createTable("search_log", $arrFields, array("search_log_id")))
			$strReturn .= "An error occurred! ...\n";

        //Table for the index queue
        $strReturn .= "Installing search-queue table...\n";

        $arrFields = array();
		$arrFields["search_queue_id"] 	    = array("char20", false);
		$arrFields["search_queue_systemid"] = array("char20", true);
		$arrFields["search_queue_action"] 	= array("char20", true);

		if(!$this->objDB->createTable("search_queue", $arrFields, array("search_queue_id")))
			$strReturn .= "An error occurred! ...\n";


        //Table for page-element
        $strReturn .= "Installing search-element table...\n";
        $objPackageManager = new PackagemanagerManager();
        if($objPackageManager->getPackage("pages") !== null)
            $objManager->createTable("Kajona\\Search\\Admin\\Elements\\ElementSearchAdmin");

		$strReturn .= "Registering module...\n";
		//register the module
		$this->registerModule("search", _search_module_id_, "SearchPortal.php", "SearchAdmin.php", $this->objMetadata->getStrVersion() , true, "SearchPortalXml.php");

        $strReturn .= "Registering config-values...\n";
        $this->registerConstant("_search_deferred_indexer_", "false", SystemSetting::$int_TYPE_BOOL, _search_module_id_);


        if(SystemModule::getModuleByName("pages") !== null && PagesElement::getElement("search") == null) {
            $objElement = new PagesElement();
            $objElement->setStrName("search");
            $objElement->setStrClassAdmin("ElementSearchAdmin.php");
            $objElement->setStrClassPortal("ElementSearchPortal.php");
            $objElement->setIntCachetime(3600);
            $objElement->setIntRepeat(0);
            $objElement->setStrVersion($this->objMetadata->getStrVersion());
            $objElement->updateObjectToDb();
            $strReturn .= "Element registered...\n";
        }
        else {
            $strReturn .= "Element already installed!...\n";
        }

        $strReturn .= "Rebuilding search index...\n";
        $this->updateIndex();


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
        if(SystemModule::getModuleByName("pages") !== null && PagesElement::getElement("search") != null) {
            $objElement = PagesElement::getElement("search");
            if($objElement != null) {
                $strReturn .= "Deleting page-element 'search'...\n";
                $objElement->deleteObjectFromDatabase();
            }
            else {
                $strReturn .= "Error finding page-element 'search', aborting.\n";
                return false;
            }
        }

        /** @var SearchSearch $objOneObject */
        foreach(SearchSearch::getObjectList() as $objOneObject) {
            $strReturn .= "Deleting object '".$objOneObject->getStrDisplayName()."' ...\n";
            if(!$objOneObject->deleteObjectFromDatabase()) {
                $strReturn .= "Error deleting object, aborting.\n";
                return false;
            }
        }

        //delete the module-node
        $strReturn .= "Deleting the module-registration...\n";
        $objModule = SystemModule::getModuleByName($this->objMetadata->getStrTitle(), true);
        if(!$objModule->deleteObjectFromDatabase()) {
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
        $arrModule = SystemModule::getPlainModuleData($this->objMetadata->getStrTitle(), false);

        $strReturn .= "Version found:\n\t Module: ".$arrModule["module_name"].", Version: ".$arrModule["module_version"]."\n\n";

        $arrModule = SystemModule::getPlainModuleData($this->objMetadata->getStrTitle(), false);
        if($arrModule["module_version"] == "4.0") {
            $strReturn .= "Updating 4.0 to 4.1...\n";
            $strReturn .= "Updating module-versions...\n";
            $this->updateModuleVersion("search", "4.1");
            $this->updateElementVersion("search", "4.1");
        }

        $arrModule = SystemModule::getPlainModuleData($this->objMetadata->getStrTitle(), false);
        if($arrModule["module_version"] == "4.1") {
            $strReturn .= "Updating 4.1 to 4.2...\n";
            $strReturn .= "Updating module-versions...\n";
            $this->updateModuleVersion("search", "4.2");
            $this->updateElementVersion("search", "4.2");
        }

        $arrModule = SystemModule::getPlainModuleData($this->objMetadata->getStrTitle(), false);
        if($arrModule["module_version"] == "4.2") {
            $strReturn .= "Updating 4.2 to 4.3...\n";
            $strReturn .= "Updating module-versions...\n";
            $this->updateModuleVersion("search", "4.3");
            $this->updateElementVersion("search", "4.3");
        }

        $arrModule = SystemModule::getPlainModuleData($this->objMetadata->getStrTitle(), false);
        if($arrModule["module_version"] == "4.3") {
            $strReturn .= $this->update_43_44();
        }

        $arrModule = SystemModule::getPlainModuleData($this->objMetadata->getStrTitle(), false);
        if($arrModule["module_version"] == "4.4" || $arrModule["module_version"] == "4.4.1") {
            $strReturn .= $this->update_441_45();
        }

        $arrModule = SystemModule::getPlainModuleData($this->objMetadata->getStrTitle(), false);
        if($arrModule["module_version"] == "4.5") {
            $strReturn .= $this->update_45_451();
        }

        $arrModule = SystemModule::getPlainModuleData($this->objMetadata->getStrTitle(), false);
        if($arrModule["module_version"] == "4.5.1") {
            $strReturn .= $this->update_451_452();
        }

        $arrModule = SystemModule::getPlainModuleData($this->objMetadata->getStrTitle(), false);
        if($arrModule["module_version"] == "4.5.2") {
            $strReturn .= "Updating 4.5.2 to 4.6...\n";
            $strReturn .= "Updating module-versions...\n";
            $this->updateModuleVersion("search", "4.6");
            $this->updateElementVersion("search", "4.6");
        }

        $arrModule = SystemModule::getPlainModuleData($this->objMetadata->getStrTitle(), false);
        if($arrModule["module_version"] == "4.6") {
            $strReturn .= $this->update_46_461();
        }

        $arrModule = SystemModule::getPlainModuleData($this->objMetadata->getStrTitle(), false);
        if($arrModule["module_version"] == "4.6.1") {
            $strReturn .= "Updating to 4.7...\n";
            $this->updateModuleVersion("search", "4.7");
            $this->updateElementVersion("search", "4.7");
        }

        if($this->bitIndexRebuild) {
            $strReturn .= "Rebuilding search index...\n";
            $this->updateIndex();
        }


        return $strReturn."\n\n";
	}


    private function update_43_44() {
        $strReturn = "Updating 4.3 to 4.4...\n";
        // Install Index
        $strReturn .= "Adding index tables...\n";
        $this->installIndexTables();

        $strReturn .= "Updating module-versions...\n";
        $this->updateModuleVersion("search", "4.4");
        $this->updateElementVersion("search", "4.4");

        $this->bitIndexRebuild = true;


        return $strReturn;
    }

    private function update_441_45() {
        $strReturn = "Updating 4.4[.1] to 4.5...\n";
        // Install Index
        if(!$this->bitIndexTablesUpToDate) {
            $strReturn .= "Updating index tables...\n";
            $strQuery = "ALTER TABLE ".$this->objDB->encloseTableName(_dbprefix_."search_ix_document")."
                                ADD ".$this->objDB->encloseColumnName("search_ix_content_lang")." ".$this->objDB->getDatatype("char20")." NULL";

            if(!$this->objDB->_pQuery($strQuery, array()))
                $strReturn .= "An error occurred! ...\n";

            $strQuery = "ALTER TABLE ".$this->objDB->encloseTableName(_dbprefix_."search_ix_document")."
                                ADD ".$this->objDB->encloseColumnName("search_ix_portal_object")." ".$this->objDB->getDatatype("int")." NULL";

            if(!$this->objDB->_pQuery($strQuery, array()))
                $strReturn .= "An error occurred! ...\n";

            $this->objDB->_pQuery("CREATE INDEX ix_search_ix_content_lang ON ".$this->objDB->encloseTableName(_dbprefix_."search_ix_document")."  ( ".$this->objDB->encloseColumnName("search_ix_content_lang")." ) ", array());
            $this->objDB->_pQuery("CREATE INDEX ix_search_ix_portal_object ON ".$this->objDB->encloseTableName(_dbprefix_."search_ix_document")."  ( ".$this->objDB->encloseColumnName("search_ix_portal_object")." ) ", array());
        }

        $strReturn .= "Removing old searchplugins...\n";
        $objFilesystem = new Filesystem();
        foreach(Resourceloader::getInstance()->getFolderContent("/admin/searchplugins/") as $strPath => $strFilename) {
            $strReturn .= "Deleting ".$strPath."\n";
            $objFilesystem->fileDelete($strPath);
        }
        foreach(Resourceloader::getInstance()->getFolderContent("/portal/searchplugins/") as $strPath => $strFilename) {
            $strReturn .= "Deleting ".$strPath."\n";
            $objFilesystem->fileDelete($strPath);
        }


        $strReturn .= "Updating module-versions...\n";
        $this->updateModuleVersion("search", "4.5");
        $this->updateElementVersion("search", "4.5");

        $strReturn .= "Updating index...\n";

        if(@ini_get("max_execution_time") < 3600 && @ini_get("max_execution_time") > 0)
            @ini_set("max_execution_time", "3600");


        $this->bitIndexRebuild = true;

        $strReturn .= "Please make sure to update your searchindex manually as soon as all other packages have been updated.\n";
        $strReturn .= "An index-rebuild can be started using module system, action systemtasks, task 'Rebuild search index'.";


        return $strReturn;
    }

    private function update_45_451() {
        $strReturn = "Updating 4.5 to 4.5.1...\n";

        //Table for the index queue
        $strReturn .= "Installing search-queue table...\n";

        $arrFields = array();
        $arrFields["search_queue_id"] 	    = array("char20", false);
        $arrFields["search_queue_systemid"] 	= array("char20", true);
        $arrFields["search_queue_action"] 	= array("char20", true);

        if(!$this->objDB->createTable("search_queue", $arrFields, array("search_queue_id")))
            $strReturn .= "An error occurred! ...\n";

        $strReturn .= "Updating module-versions...\n";
        $this->updateModuleVersion("search", "4.5.1");
        $this->updateElementVersion("search", "4.5.1");

        return $strReturn;
    }

    private function updateIndex() {
        Carrier::getInstance()->flushCache(Carrier::INT_CACHE_TYPE_DBQUERIES | Carrier::INT_CACHE_TYPE_MODULES);

        SearchIndexwriter::resetIndexAvailableCheck();
        $objWorker = new SearchIndexwriter();
        $objWorker->indexRebuild();
    }

    private function installIndexTables() {
        $this->bitIndexTablesUpToDate = true;
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

    private function update_451_452() {
        $strReturn = "";

        $objPackageManager = new PackagemanagerManager();
        if($objPackageManager->getPackage("pages") !== null) {
            $strReturn .= "Updating search_element tables...\n";
            $strQuery = "ALTER TABLE ".$this->objDB->encloseTableName(_dbprefix_."element_search")."
                                ADD ".$this->objDB->encloseColumnName("search_query_id")." ".$this->objDB->getDatatype("char20")." NULL";

            if(!$this->objDB->_pQuery($strQuery, array())) {
                $strReturn .= "An error occurred! ...\n";
            }

        }

        $strReturn .= "Updating module-versions...\n";
        $this->updateModuleVersion("search", "4.5.2");
        $this->updateElementVersion("search", "4.5.2");

        return $strReturn;

    }

    private function update_46_461() {
        $strReturn = "Adding index queue functionality...\n";


        //Table for the index queue
        $strReturn .= "Installing search-queue table...\n";

        $arrFields = array();
        $arrFields["search_queue_id"] 	    = array("char20", false);
        $arrFields["search_queue_systemid"] = array("char20", true);
        $arrFields["search_queue_action"] 	= array("char20", true);

        if(!$this->objDB->createTable("search_queue", $arrFields, array("search_queue_id")))
            $strReturn .= "An error occurred! ...\n";

        $strReturn .= "Registering config-values...\n";
        $this->registerConstant("_search_deferred_indexer_", "false", SystemSetting::$int_TYPE_BOOL, _search_module_id_);

        $strReturn .= "Updating module-versions...\n";
        $this->updateModuleVersion("search", "4.6.1");
        $this->updateElementVersion("search", "4.6.1");

        return $strReturn;

    }

}
