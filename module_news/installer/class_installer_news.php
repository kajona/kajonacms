<?php
/*"******************************************************************************************************
*   (c) 2004-2006 by MulchProductions, www.mulchprod.de                                                 *
*   (c) 2007-2015 by Kajona, www.kajona.de                                                              *
*       Published under the GNU LGPL v2.1, see /system/licence_lgpl.txt                                 *
*-------------------------------------------------------------------------------------------------------*
*	$Id$                                           *
********************************************************************************************************/

/**
 * Class providing an install for the news module
 *
 * @package module_news
 * @moduleId _news_module_id_
 */
class class_installer_news extends class_installer_base implements interface_installer_removable {

    public function install() {
		$strReturn = "";
        $objManager = new class_orm_schemamanager();

		$strReturn .= "Installing table news_category...\n";
        $objManager->createTable("class_module_news_category");

		$strReturn .= "Installing table news...\n";
        $objManager->createTable("class_module_news_news");

		$strReturn .= "Installing table news_member...\n";

		$arrFields = array();
		$arrFields["newsmem_id"] 		= array("char20", false);
		$arrFields["newsmem_news"]	 	= array("char20", true);
		$arrFields["newsmem_category"]  = array("char20", true);

		if(!$this->objDB->createTable("news_member", $arrFields, array("newsmem_id")))
			$strReturn .= "An error occurred! ...\n";

		$strReturn .= "Installing table news_feed...\n";
        $objManager->createTable("class_module_news_feed");

		//register the module
		$this->registerModule(
            "news",
            _news_module_id_,
            "class_module_news_portal.php",
            "class_module_news_admin.php",
            $this->objMetadata->getStrVersion(),
            true,
            "class_module_news_portal_xml.php"
        );

        $strReturn .= "Installing news-element table...\n";
        $objManager->createTable("class_element_news_admin");


        //Register the element
        $strReturn .= "Registering news-element...\n";
        //check, if not already existing
        if(class_module_pages_element::getElement("news") == null) {
            $objElement = new class_module_pages_element();
            $objElement->setStrName("news");
            $objElement->setStrClassAdmin("class_element_news_admin.php");
            $objElement->setStrClassPortal("class_element_news_portal.php");
            $objElement->setIntCachetime(3600);
            $objElement->setIntRepeat(1);
            $objElement->setStrVersion($this->objMetadata->getStrVersion());
            $objElement->updateObjectToDb();
            $strReturn .= "Element registered...\n";
        }
        else {
            $strReturn .= "Element already installed!...\n";
        }

        $strReturn .= "Setting aspect assignments...\n";
        if(class_module_system_aspect::getAspectByName("content") != null) {
            $objModule = class_module_system_module::getModuleByName($this->objMetadata->getStrTitle());
            $objModule->setStrAspect(class_module_system_aspect::getAspectByName("content")->getSystemid());
            $objModule->updateObjectToDb();
        }

        $strReturn .= "Registering config settings...\n";
        $this->registerConstant("_news_news_datetime_", "false", class_module_system_setting::$int_TYPE_BOOL, _news_module_id_);

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
        $objElement = class_module_pages_element::getElement("news");
        if($objElement != null) {
            $strReturn .= "Deleting page-element 'news'...\n";
            $objElement->deleteObject();
        }
        else {
            $strReturn .= "Error finding page-element 'news', aborting.\n";
            return false;
        }

        /** @var class_module_news_feed $objOneObject */
        foreach(class_module_news_feed::getObjectList() as $objOneObject) {
            $strReturn .= "Deleting object '".$objOneObject->getStrDisplayName()."' ...\n";
            if(!$objOneObject->deleteObject()) {
                $strReturn .= "Error deleting object, aborting.\n";
                return false;
            }
        }

        /** @var class_module_news_category $objOneObject */
        foreach(class_module_news_category::getObjectList() as $objOneObject) {
            $strReturn .= "Deleting object '".$objOneObject->getStrDisplayName()."' ...\n";
            if(!$objOneObject->deleteObject()) {
                $strReturn .= "Error deleting object, aborting.\n";
                return false;
            }
        }

        /** @var class_module_news_news $objOneObject */
        foreach(class_module_news_news::getObjectList() as $objOneObject) {
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
        foreach(array("news_category", "news", "news_member", "news_feed", "element_news") as $strOneTable) {
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
            $strReturn .= $this->update_349_40();
        }

        $arrModule = class_module_system_module::getPlainModuleData($this->objMetadata->getStrTitle(), false);
        if($arrModule["module_version"] == "4.0") {
            $strReturn .= $this->update_40_41();
        }

        $arrModule = class_module_system_module::getPlainModuleData($this->objMetadata->getStrTitle(), false);
        if($arrModule["module_version"] == "4.1") {
            $strReturn .= "Updating 4.1 to 4.2...\n";
            $strReturn .= "Updating module-versions...\n";
            $this->updateModuleVersion("news", "4.2");
            $strReturn .= "Updating element-versions...\n";
            $this->updateElementVersion("news", "4.2");
        }

        $arrModule = class_module_system_module::getPlainModuleData($this->objMetadata->getStrTitle(), false);
        if($arrModule["module_version"] == "4.2") {
            $strReturn .= "Updating 4.2 to 4.3...\n";
            $strReturn .= "Updating module-versions...\n";
            $this->updateModuleVersion("news", "4.3");
            $strReturn .= "Updating element-versions...\n";
            $this->updateElementVersion("news", "4.3");
        }

        $arrModule = class_module_system_module::getPlainModuleData($this->objMetadata->getStrTitle(), false);
        if($arrModule["module_version"] == "4.3") {
            $strReturn .= "Updating 4.3 to 4.4...\n";
            $this->updateModuleVersion("news", "4.4");
            $this->updateElementVersion("news", "4.4");
        }

        $arrModule = class_module_system_module::getPlainModuleData($this->objMetadata->getStrTitle(), false);
        if($arrModule["module_version"] == "4.4") {
            $strReturn .= $this->update_44_45();
        }

        $arrModule = class_module_system_module::getPlainModuleData($this->objMetadata->getStrTitle(), false);
        if($arrModule["module_version"] == "4.5") {
            $strReturn .= $this->update_45_451();
        }

        $arrModule = class_module_system_module::getPlainModuleData($this->objMetadata->getStrTitle(), false);
        if($arrModule["module_version"] == "4.5.1") {
            $strReturn .= "Updating 4.5.1 to 4.6...\n";
            $this->updateModuleVersion("news", "4.6");
            $this->updateElementVersion("news", "4.6");
        }

        $arrModule = class_module_system_module::getPlainModuleData($this->objMetadata->getStrTitle(), false);
        if($arrModule["module_version"] == "4.6") {
            $strReturn .= "Updating to 4.7...\n";
            $this->updateModuleVersion("news", "4.7");
            $this->updateElementVersion("news", "4.7");
        }

        return $strReturn."\n\n";
	}


    private function update_342_349() {

        $strReturn = "Updating 3.4.2 to 3.4.9...\n";

        $strReturn .= "Adding classes for existing records...\n";
        $strReturn .= "FAQs\n";
        $arrRows = $this->objDB->getPArray("SELECT news_id FROM "._dbprefix_."news, "._dbprefix_."system WHERE system_id = news_id AND (system_class IS NULL OR system_class = '')", array());
        foreach($arrRows as $arrOneRow) {
            $strQuery = "UPDATE "._dbprefix_."system SET system_class = ? where system_id = ?";
            $this->objDB->_pQuery($strQuery, array( 'class_module_news_news', $arrOneRow["news_id"] ) );
        }

        $strReturn .= "Categories\n";
        $arrRows = $this->objDB->getPArray("SELECT news_cat_id FROM "._dbprefix_."news_category, "._dbprefix_."system WHERE system_id = news_cat_id AND (system_class IS NULL OR system_class = '')", array());
        foreach($arrRows as $arrOneRow) {
            $strQuery = "UPDATE "._dbprefix_."system SET system_class = ? where system_id = ?";
            $this->objDB->_pQuery($strQuery, array( 'class_module_news_category', $arrOneRow["news_cat_id"] ) );
        }

        $strReturn .= "Feeds\n";
        $arrRows = $this->objDB->getPArray("SELECT news_feed_id FROM "._dbprefix_."news_feed, "._dbprefix_."system WHERE system_id = news_feed_id AND (system_class IS NULL OR system_class = '')", array());
        foreach($arrRows as $arrOneRow) {
            $strQuery = "UPDATE "._dbprefix_."system SET system_class = ? where system_id = ?";
            $this->objDB->_pQuery($strQuery, array( 'class_module_news_feed', $arrOneRow["news_feed_id"] ) );
        }

        $strReturn .= "Removing old constants\n";
        $strQuery = "DELETE FROM "._dbprefix_."system_config WHERE system_config_name = ?";
        $this->objDB->_pQuery($strQuery, array("_news_search_resultpage_"));

        $strReturn .= "Setting aspect assignments...\n";
        if(class_module_system_aspect::getAspectByName("content") != null) {
            $objModule = class_module_system_module::getModuleByName($this->objMetadata->getStrTitle());
            $objModule->setStrAspect(class_module_system_aspect::getAspectByName("content")->getSystemid());
            $objModule->updateObjectToDb();
        }


        $strReturn .= "Updating module-versions...\n";
        $this->updateModuleVersion("news", "3.4.9");
        $strReturn .= "Updating element-versions...\n";
        $this->updateElementVersion("news", "3.4.9");
        return $strReturn;
    }


    private function update_349_40() {
        $strReturn = "Updating 3.4.9 to 4.0...\n";
        $strReturn .= "Updating module-versions...\n";
        $this->updateModuleVersion("news", "4.0");
        $strReturn .= "Updating element-versions...\n";
        $this->updateElementVersion("news", "4.0");
        return $strReturn;
    }


    private function update_40_41() {
        $strReturn = "Updating 4.0 to 4.1...\n";
        $strReturn .= "Updating module-versions...\n";
        $this->updateModuleVersion("news", "4.1");
        $strReturn .= "Updating element-versions...\n";
        $this->updateElementVersion("news", "4.1");
        return $strReturn;
    }

    private function update_44_45() {
        $strReturn = "Updating 4.4 to 4.5...\n";

        $strReturn .= "Updating news table...\n";

        $strQuery = "ALTER TABLE ".$this->objDB->encloseTableName(_dbprefix_."news")."
                            ADD ".$this->objDB->encloseColumnName("news_redirect_page")." ".$this->objDB->getDatatype("char254")." NULL";

        if(!$this->objDB->_pQuery($strQuery, array()))
            $strReturn .= "An error occurred! ...\n";


        $strQuery = "ALTER TABLE ".$this->objDB->encloseTableName(_dbprefix_."news")."
                            ADD ".$this->objDB->encloseColumnName("news_redirect_enabled")." ".$this->objDB->getDatatype("int")." NULL";

        if(!$this->objDB->_pQuery($strQuery, array()))
            $strReturn .= "An error occurred! ...\n";



        $strReturn .= "Updating module-versions...\n";
        $this->updateModuleVersion("news", "4.5");
        $strReturn .= "Updating element-versions...\n";
        $this->updateElementVersion("news", "4.5");
        return $strReturn;
    }

    private function update_45_451() {
        $strReturn = "Updating 4.5 to 4.5.1...\n";

        $strReturn .= "Registering config settings...\n";
        $this->registerConstant("_news_news_datetime_", "false", class_module_system_setting::$int_TYPE_BOOL, _news_module_id_);


        $strReturn .= "Updating module-versions...\n";
        $this->updateModuleVersion("news", "4.5.1");
        $strReturn .= "Updating element-versions...\n";
        $this->updateElementVersion("news", "4.5.1");
        return $strReturn;
    }
}
