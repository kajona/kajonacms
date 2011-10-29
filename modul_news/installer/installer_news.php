<?php
/*"******************************************************************************************************
*   (c) 2004-2006 by MulchProductions, www.mulchprod.de                                                 *
*   (c) 2007-2011 by Kajona, www.kajona.de                                                              *
*       Published under the GNU LGPL v2.1, see /system/licence_lgpl.txt                                 *
*-------------------------------------------------------------------------------------------------------*
*	$Id$                                           *
********************************************************************************************************/

/**
 * Class providing an install for the news module
 *
 * @package modul_news
 */
class class_installer_news extends class_installer_base implements interface_installer {

	public function __construct() {
        $arrModule = array();
		$arrModule["version"] 		  = "3.4.1";
		$arrModule["name"] 			  = "news";
		$arrModule["name_lang"] 	  = "Module News";
		$arrModule["moduleId"] 		  = _news_modul_id_;
		parent::__construct($arrModule);
	}

	public function getNeededModules() {
	    return array("system", "pages");
	}

    public function getMinSystemVersion() {
	    return "3.4.1";
	}

	public function hasPostInstalls() {
	    $strQuery = "SELECT COUNT(*) FROM "._dbprefix_."element WHERE element_name='news'";
	    $arrRow = $this->objDB->getRow($strQuery);
        if($arrRow["COUNT(*)"] == 0)
            return true;

        return false;
	}

   public function install() {
		$strReturn = "";
		//Tabellen anlegen

		//news cat-------------------------------------------------------------------------------------
		$strReturn .= "Installing table news_category...\n";

		$arrFields = array();
		$arrFields["news_cat_id"] 		= array("char20", false);
		$arrFields["news_cat_title"] 	= array("char254", true);

		if(!$this->objDB->createTable("news_category", $arrFields, array("news_cat_id")))
			$strReturn .= "An error occured! ...\n";

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
			$strReturn .= "An error occured! ...\n";

		//news_member----------------------------------------------------------------------------------
		$strReturn .= "Installing table news_member...\n";

		$arrFields = array();
		$arrFields["newsmem_id"] 		= array("char20", false);
		$arrFields["newsmem_news"]	 	= array("char20", true);
		$arrFields["newsmem_category"]  = array("char20", true);

		if(!$this->objDB->createTable("news_member", $arrFields, array("newsmem_id")))
			$strReturn .= "An error occured! ...\n";

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
			$strReturn .= "An error occured! ...\n";


		//register the module
		$this->registerModule("news", _news_modul_id_, "class_modul_news_portal.php", "class_modul_news_admin.php", $this->arrModule["version"] , true, "class_modul_news_portal_xml.php");

		$strReturn .= "Registering system-constants...\n";

		$this->registerConstant("_news_search_resultpage_", "newsdetails", class_modul_system_setting::$int_TYPE_PAGE, _news_modul_id_);

		return $strReturn;

	}

	public function postInstall() {
		$strReturn = "";

		//Table for page-element
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
			$strReturn .= "An error occured! ...\n";

		//Register the element
		$strReturn .= "Registering news-element...\n";
		//check, if not already existing
        $objElement = null;
		try {
		    $objElement = class_modul_pages_element::getElement("news");
		}
		catch (class_exception $objEx)  {
		}
		if($objElement == null) {
		    $objElement = new class_modul_pages_element();
		    $objElement->setStrName("news");
		    $objElement->setStrClassAdmin("class_element_news.php");
		    $objElement->setStrClassPortal("class_element_news.php");
		    $objElement->setIntCachetime(3600);
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

        $arrModul = $this->getModuleData($this->arrModule["name"], false);
        if($arrModul["module_version"] == "3.2.0") {
            $strReturn .= $this->update_320_3209();
        }

        $arrModul = $this->getModuleData($this->arrModule["name"], false);
        if($arrModul["module_version"] == "3.2.0.9") {
            $strReturn .= $this->update_3209_321();
        }

        $arrModul = $this->getModuleData($this->arrModule["name"], false);
        if($arrModul["module_version"] == "3.2.1") {
            $strReturn .= $this->update_321_3291();
        }

        $arrModul = $this->getModuleData($this->arrModule["name"], false);
        if($arrModul["module_version"] == "3.2.91") {
            $strReturn .= $this->update_3291_3292();
        }

        $arrModul = $this->getModuleData($this->arrModule["name"], false);
        if($arrModul["module_version"] == "3.2.92") {
            $strReturn .= $this->update_3292_330();
        }

        $arrModul = $this->getModuleData($this->arrModule["name"], false);
        if($arrModul["module_version"] == "3.3.0") {
            $strReturn .= $this->update_330_3301();
        }

        $arrModul = $this->getModuleData($this->arrModule["name"], false);
        if($arrModul["module_version"] == "3.3.0.1") {
            $strReturn .= $this->update_3301_331();
        }

        $arrModul = $this->getModuleData($this->arrModule["name"], false);
        if($arrModul["module_version"] == "3.3.1") {
            $strReturn .= $this->update_331_3318();
        }

        $arrModul = $this->getModuleData($this->arrModule["name"], false);
        if($arrModul["module_version"] == "3.3.1.8") {
            $strReturn .= $this->update_3318_340();
        }

        $arrModul = $this->getModuleData($this->arrModule["name"], false);
        if($arrModul["module_version"] == "3.4.0") {
            $strReturn .= $this->update_340_341();
        }

        return $strReturn."\n\n";
	}

    private function update_320_3209() {
        $strReturn = "Updating 3.2.0 to 3.2.0.9...\n";
        $strReturn .= "Updating module-versions...\n";
        $this->updateModuleVersion("news", "3.2.0.9");
        return $strReturn;
    }

    private function update_3209_321() {
        $strReturn = "Updating 3.2.0.9 to 3.2.1...\n";
        $strReturn .= "Updating module-versions...\n";
        $this->updateModuleVersion("news", "3.2.1");
        $strReturn .= "Updating element-versions...\n";
        $this->updateElementVersion("news", "3.2.1");
        return $strReturn;
    }


    private function update_321_3291() {
        $strReturn = "Updating 3.2.1 to 3.2.91...\n";


        $strReturn .= "Reorganizing news...\n";

        $strQuery = "SELECT module_id
                       FROM "._dbprefix_."system_module
                      WHERE module_nr = "._news_modul_id_."";
        $arrEntries = $this->objDB->getRow($strQuery);
        $strModuleId = $arrEntries["module_id"];

        $strQuery = "SELECT news_id
                       FROM "._dbprefix_."news";
        $arrEntries = $this->objDB->getArray($strQuery);

        foreach($arrEntries as $arrSingleRow) {
            $strReturn .= " ...updating news ".$arrSingleRow["news_id"]."";
            $strQuery = "UPDATE "._dbprefix_."system
                            SET system_prev_id = '".dbsafeString($strModuleId)."'
                          WHERE system_id = '".dbsafeString($arrSingleRow["news_id"])."'";
            if($this->objDB->_query($strQuery))
                $strReturn .= " ...ok\n";
            else
                $strReturn .= " ...failed!!!\n";
        }


        $strReturn .= "Reorganizing news-cat...\n";

        $strQuery = "SELECT news_cat_id
                       FROM "._dbprefix_."news_category";
        $arrEntries = $this->objDB->getArray($strQuery);

        foreach($arrEntries as $arrSingleRow) {
            $strReturn .= " ...updating news-cats ".$arrSingleRow["news_cat_id"]."";
            $strQuery = "UPDATE "._dbprefix_."system
                            SET system_prev_id = '".dbsafeString($strModuleId)."'
                          WHERE system_id = '".dbsafeString($arrSingleRow["news_cat_id"])."'";
            if($this->objDB->_query($strQuery))
                $strReturn .= " ...ok\n";
            else
                $strReturn .= " ...failed!!!\n";
        }


        $strReturn .= "Updating module-versions...\n";
        $this->updateModuleVersion("news", "3.2.91");
        $strReturn .= "Updating element-versions...\n";
        $this->updateElementVersion("news", "3.2.91");
        return $strReturn;
    }


    private function update_3291_3292() {
        $strReturn = "Updating 3.2.91 to 3.2.92...\n";

        $strReturn .= "Altering news-feed-table...\n";
        $strQuery = "ALTER TABLE ".$this->objDB->encloseTableName(_dbprefix_."news_feed")."
                        ADD ".$this->objDB->encloseColumnName("news_feed_amount")." INT NULL;";

        if(!$this->objDB->_query($strQuery))
            $strReturn .= "An error occured!!!\n";

        $strReturn .= "Updating module-versions...\n";
        $this->updateModuleVersion("news", "3.2.92");
        $strReturn .= "Updating element-versions...\n";
        $this->updateElementVersion("news", "3.2.92");
        return $strReturn;
    }

    private function update_3292_330() {
        $strReturn = "Updating 3.2.92 to 3.3.0...\n";
        $strReturn .= "Updating module-versions...\n";
        $this->updateModuleVersion("news", "3.3.0");
        $strReturn .= "Updating element-versions...\n";
        $this->updateElementVersion("news", "3.3.0");
        return $strReturn;
    }

    private function update_330_3301() {
        $strReturn = "Updating 3.3.0 to 3.3.0.1...\n";

        $strReturn .= "Setting cache-timeouts for news-element...\n";
        $strQuery = "UPDATE "._dbprefix_."element
                        SET element_cachetime=3600
                      WHERE element_class_admin = 'class_element_news.php'";
        if(!$this->objDB->_query($strQuery))
            $strReturn .= "An error occured! ...\n";

        $strReturn .= "Updating module-versions...\n";
        $this->updateModuleVersion("news", "3.3.0.1");
        $strReturn .= "Updating element-versions...\n";
        $this->updateElementVersion("news", "3.3.0.1");
        return $strReturn;
    }


    private function update_3301_331() {
        $strReturn = "Updating 3.3.0.1 to 3.3.1...\n";

        $strReturn .= "Updating module-versions...\n";
        $this->updateModuleVersion("news", "3.3.1");
        $strReturn .= "Updating element-versions...\n";
        $this->updateElementVersion("news", "3.3.1");
        return $strReturn;
    }

    private function update_331_3318() {
        $strReturn = "Updating 3.3.1 to 3.3.1.8...\n";
        $strReturn .= "Updating module-versions...\n";
        $this->updateModuleVersion("news", "3.3.1.8");
        $strReturn .= "Updating element-versions...\n";
        $this->updateElementVersion("news", "3.3.1.8");
        return $strReturn;
    }

    private function update_3318_340() {
        $strReturn = "Updating 3.3.1.8 to 3.4.0...\n";
        $strReturn .= "Updating module-versions...\n";
        $this->updateModuleVersion("news", "3.4.0");
        $strReturn .= "Updating element-versions...\n";
        $this->updateElementVersion("news", "3.4.0");
        return $strReturn;
    }

    private function update_340_341() {
        $strReturn = "Updating 3.4.0 to 3.4.1...\n";
        $strReturn .= "Updating module-versions...\n";
        $this->updateModuleVersion("news", "3.4.1");
        $strReturn .= "Updating element-versions...\n";
        $this->updateElementVersion("news", "3.4.1");
        return $strReturn;
    }
}
?>