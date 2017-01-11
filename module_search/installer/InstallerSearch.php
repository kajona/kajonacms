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
use Kajona\Search\Admin\Elements\ElementSearchAdmin;
use Kajona\Search\System\SearchIndexwriter;
use Kajona\Search\System\SearchSearch;
use Kajona\System\System\Carrier;
use Kajona\System\System\DbDatatypes;
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
        $objManager->createTable(SearchSearch::class
        );

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
            $objManager->createTable(ElementSearchAdmin::class);

		$strReturn .= "Registering module...\n";
		//register the module
		$this->registerModule("search", _search_module_id_, "SearchPortal.php", "SearchAdmin.php", $this->objMetadata->getStrVersion());

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
        foreach(SearchSearch::getObjectListFiltered() as $objOneObject) {
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
            if(!$this->objDB->_pQuery("DROP TABLE ".$this->objDB->encloseTableName(_dbprefix_.$strOneTable), array())) {
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
        if($arrModule["module_version"] == "4.6") {
            $strReturn .= $this->update_46_461();
        }

        $arrModule = SystemModule::getPlainModuleData($this->objMetadata->getStrTitle(), false);
        if($arrModule["module_version"] == "4.6.1") {
            $strReturn .= "Updating to 4.7...\n";
            $this->updateModuleVersion("search", "4.7");
            $this->updateElementVersion("search", "4.7");
        }

        $arrModule = SystemModule::getPlainModuleData($this->objMetadata->getStrTitle(), false);
        if($arrModule["module_version"] == "4.7") {
            $strReturn .= "Updating to 5.0...\n";
            $this->updateModuleVersion("search", "5.0");
            $this->updateElementVersion("search", "5.0");
        }

        $arrModule = SystemModule::getPlainModuleData($this->objMetadata->getStrTitle(), false);
        if($arrModule["module_version"] == "5.0") {
            $strReturn .= $this->update_50_51();
        }

        $arrModule = SystemModule::getPlainModuleData($this->objMetadata->getStrTitle(), false);
        if($arrModule["module_version"] == "5.1") {
            $strReturn .= "Updating to 6.2...\n";
            $this->updateModuleVersion("search", "6.2");
            $this->updateElementVersion("search", "6.2");
        }

        if($this->bitIndexRebuild) {
            $strReturn .= "Rebuilding search index...\n";
            $this->updateIndex();
        }


        return $strReturn."\n\n";
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
    private function update_50_51() {
        $strReturn = "Updating to 5.1...\n";
        $strReturn .= "Updating element table...";


        if(!$this->objDB->addColumn("element_search", "search_query_append", DbDatatypes::STR_TYPE_CHAR254))
            $strReturn .= "An error occurred! ...\n";

        $strReturn .= "Updating module-versions...\n";
        $this->updateModuleVersion("search", "5.1");
        $this->updateElementVersion("search", "5.1");

        return $strReturn;

    }

}
