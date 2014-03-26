<?php
/*"******************************************************************************************************
*   (c) 2004-2006 by MulchProductions, www.mulchprod.de                                                 *
*   (c) 2007-2014 by Kajona, www.kajona.de                                                              *
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
class class_installer_news extends class_installer_base implements interface_installer {

    public function install() {
		$strReturn = "";
		//Tabellen anlegen

		//news cat-------------------------------------------------------------------------------------
		$strReturn .= "Installing table news_category...\n";

		$arrFields = array();
		$arrFields["news_cat_id"] 		= array("char20", false);
		$arrFields["news_cat_title"] 	= array("char254", true);

		if(!$this->objDB->createTable("news_category", $arrFields, array("news_cat_id")))
			$strReturn .= "An error occurred! ...\n";

		//news----------------------------------------------------------------------------------
		$strReturn .= "Installing table news...\n";

		$arrFields = array();
		$arrFields["news_id"] 		= array("char20", false);
		$arrFields["news_title"] 	= array("char254", true);
		$arrFields["news_hits"] 	= array("int", true, "0");
		$arrFields["news_intro"] 	= array("text", true);
		$arrFields["news_text"] 	= array("text", true);
		$arrFields["news_image"] 	= array("char254", true);

		if(!$this->objDB->createTable("news", $arrFields, array("news_id")))
			$strReturn .= "An error occurred! ...\n";

		//news_member----------------------------------------------------------------------------------
		$strReturn .= "Installing table news_member...\n";

		$arrFields = array();
		$arrFields["newsmem_id"] 		= array("char20", false);
		$arrFields["newsmem_news"]	 	= array("char20", true);
		$arrFields["newsmem_category"]  = array("char20", true);

		if(!$this->objDB->createTable("news_member", $arrFields, array("newsmem_id")))
			$strReturn .= "An error occurred! ...\n";

		//news_feed--------------------------------------------------------------------------------------
		$strReturn .= "Installing table news_feed...\n";

		$arrFields = array();
		$arrFields["news_feed_id"] 		= array("char20", false);
		$arrFields["news_feed_title"] 	= array("char254", true);
		$arrFields["news_feed_urltitle"]= array("char254", true);
		$arrFields["news_feed_link"] 	= array("char254", true);
		$arrFields["news_feed_desc"] 	= array("char254", true);
		$arrFields["news_feed_page"] 	= array("char254", true);
		$arrFields["news_feed_cat"] 	= array("char20", true);
		$arrFields["news_feed_hits"] 	= array("int", true);
		$arrFields["news_feed_amount"] 	= array("int", true);

		if(!$this->objDB->createTable("news_feed", $arrFields, array("news_feed_id")))
			$strReturn .= "An error occurred! ...\n";


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

        $arrFields = array();
        $arrFields["content_id"] 		= array("char20", false);
        $arrFields["news_category"] 	= array("char20", true);
        $arrFields["news_view"] 		= array("int", true);
        $arrFields["news_mode"] 		= array("int", true);
        $arrFields["news_order"] 		= array("int", true);
        $arrFields["news_amount"] 		= array("int", true);
        $arrFields["news_detailspage"] 	= array("char254", true);
        $arrFields["news_template"] 	= array("char254", true);

        if(!$this->objDB->createTable("element_news", $arrFields, array("content_id")))
            $strReturn .= "An error occurred! ...\n";

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

		return $strReturn;

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
            $strReturn .= $this->update_349_40();
        }

        $arrModul = class_module_system_module::getPlainModuleData($this->objMetadata->getStrTitle(), false);
        if($arrModul["module_version"] == "4.0") {
            $strReturn .= $this->update_40_41();
        }

        $arrModul = class_module_system_module::getPlainModuleData($this->objMetadata->getStrTitle(), false);
        if($arrModul["module_version"] == "4.1") {
            $strReturn .= "Updating 4.1 to 4.2...\n";
            $strReturn .= "Updating module-versions...\n";
            $this->updateModuleVersion("news", "4.2");
            $strReturn .= "Updating element-versions...\n";
            $this->updateElementVersion("news", "4.2");
        }

        $arrModul = class_module_system_module::getPlainModuleData($this->objMetadata->getStrTitle(), false);
        if($arrModul["module_version"] == "4.2") {
            $strReturn .= "Updating 4.2 to 4.3...\n";
            $strReturn .= "Updating module-versions...\n";
            $this->updateModuleVersion("news", "4.3");
            $strReturn .= "Updating element-versions...\n";
            $this->updateElementVersion("news", "4.3");
        }

        $arrModul = class_module_system_module::getPlainModuleData($this->objMetadata->getStrTitle(), false);
        if($arrModul["module_version"] == "4.3") {
            $strReturn .= "Updating 4.3 to 4.4...\n";
            $this->updateModuleVersion("news", "4.4");
            $this->updateElementVersion("news", "4.4");
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
}
