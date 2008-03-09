<?php
/*"******************************************************************************************************
*   (c) 2004-2006 by MulchProductions, www.mulchprod.de                                                 *
*   (c) 2007-2008 by Kajona, www.kajona.de                                                              *
*       Published under the GNU LGPL v2.1, see /system/licence_lgpl.txt                                 *
*-------------------------------------------------------------------------------------------------------*
* 																										*
* 	installer_news.php																					*
* 	Installer of the news module																		*
*																										*
*-------------------------------------------------------------------------------------------------------*
*	$Id$                                           *
********************************************************************************************************/

require_once(_systempath_."/class_installer_base.php");
require_once(_systempath_."/interface_installer.php");

/**
 * Class providing an install for the news module
 *
 * @package modul_news
 */
class class_installer_news extends class_installer_base implements interface_installer {

	public function __construct() {
		$arrModule["version"] 		  = "3.1.0";
		$arrModule["name"] 			  = "news";
		$arrModule["class_admin"]  	  = "class_modul_news_admin";
		$arrModule["file_admin"] 	  = "class_modul_news_admin.php";
		$arrModule["class_portal"] 	  = "class_modul_news_portal";
		$arrModule["file_portal"] 	  = "class_modul_news_portal.php";
		$arrModule["name_lang"] 	  = "Module News";
		$arrModule["moduleId"] 		  = _news_modul_id_;

		$arrModule["tabellen"][]      = _dbprefix_."news";
		$arrModule["tabellen"][]      = _dbprefix_."news_category";
		$arrModule["tabellen"][]      = _dbprefix_."news_member";
		$arrModule["tabellen"][]      = _dbprefix_."element_news";
		parent::__construct($arrModule);
	}

	public function getNeededModules() {
	    return array("system", "pages");
	}
	
    public function getMinSystemVersion() {
	    return "3.0.9";
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
		
		if(!$this->objDB->createTable("news_feed", $arrFields, array("news_feed_id")))
			$strReturn .= "An error occured! ...\n";


		//register the module
		$strSystemID = $this->registerModule("news", _news_modul_id_, "class_modul_news_portal.php", "class_modul_news_admin.php", $this->arrModule["version"] , true, "class_modul_news_portal_xml.php");


		$strReturn .= "Registering system-constants...\n";

		$this->registerConstant("_news_suche_seite_", "newsdetails", class_modul_system_setting::$int_TYPE_PAGE, _news_modul_id_);

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
		$arrFields["news_detailspage"] 	= array("char254", true);
		$arrFields["news_template"] 	= array("char254", true);
		
		if(!$this->objDB->createTable("element_news", $arrFields, array("content_id")))
			$strReturn .= "An error occured! ...\n";

		//Register the element
		$strReturn .= "Registering news-element...\n";
		//check, if not already existing
		$strQuery = "SELECT COUNT(*) FROM "._dbprefix_."element WHERE element_name='news'";
		$arrRow = $this->objDB->getRow($strQuery);
		if($arrRow["COUNT(*)"] == 0) {
			$strQuery = "INSERT INTO "._dbprefix_."element
							(element_id, element_name, element_class_portal, element_class_admin, element_repeat) VALUES
							('".$this->generateSystemid()."', 'news', 'class_element_news.php', 'class_element_news.php', 1)";
			$this->objDB->_query($strQuery);
			$strReturn .= "Element registered...\n";
		}
		else {
			$strReturn .= "Element already installed!...\n";
			}
		return $strReturn;
	}


	public function update() {
	    $strReturn = "";
        //check the version we have and to what version to update
        $arrModul = $this->getModuleData($this->arrModule["name"], false);

        $strReturn .= "Version found:\n\t Module: ".$arrModul["module_name"].", Version: ".$arrModul["module_version"]."\n\n";

        $arrModul = $this->getModuleData($this->arrModule["name"], false);
        if($arrModul["module_version"] == "2.2.0.0") {
            $strReturn .= $this->update_2200_221();
        }

        $arrModul = $this->getModuleData($this->arrModule["name"], false);
        if($arrModul["module_version"] == "2.2.1") {
            $strReturn .= $this->update_221_300();
        }

        $arrModul = $this->getModuleData($this->arrModule["name"], false);
        if($arrModul["module_version"] == "3.0.0") {
            $strReturn .= $this->update_300_301();
        }

        $arrModul = $this->getModuleData($this->arrModule["name"], false);
        if($arrModul["module_version"] == "3.0.1") {
            $strReturn .= $this->update_301_302();
        }
        
	    $arrModul = $this->getModuleData($this->arrModule["name"], false);
        if($arrModul["module_version"] == "3.0.2") {
            $strReturn .= $this->update_302_309();
        }
        
		$arrModul = $this->getModuleData($this->arrModule["name"], false);
        if($arrModul["module_version"] == "3.0.9") {
            $strReturn .= $this->update_309_3095();
        }
        
	   $arrModul = $this->getModuleData($this->arrModule["name"], false);
        if($arrModul["module_version"] == "3.0.95") {
            $strReturn .= $this->update_3095_310();
        }

        return $strReturn."\n\n";
	}

	private function update_2200_221() {
	    //Run the updates
	    $strReturn = "";
        $strReturn .= "Updating 2.2.0.0 to 2.2.1...\n";

        $strReturn .= "Adding xml-classes to module...\n";
		$strQuery = "UPDATE "._dbprefix_."system_module
		            SET module_xmlfilenameportal = 'class_modul_news_portal_xml.php',
		                module_xmlfilenameadmin = ''
		            WHERE module_name = 'news'";

		if(!$this->objDB->_query($strQuery))
			$strReturn .= "An error occured! ...\n";

        $strReturn .= "Updating module-versions...\n";
        $this->updateModuleVersion("news", "2.2.1");

        return $strReturn;
	}

	private function update_221_300() {
	    //Run the updates
	    $strReturn = "";
        $strReturn .= "Updating 2.2.1 to 3.0.0...\n";

        $strReturn .= "Adding internal-feed title field to db-table...\n";

        $strQuery = "ALTER TABLE `"._dbprefix_."news_feed` ADD `news_feed_urltitle` VARCHAR( 255 ) ";

        if(!$this->objDB->_query($strQuery))
			$strReturn .= "An error occured! ...\n";

        $strReturn .= "Updating module-versions...\n";
        $this->updateModuleVersion("news", "3.0.0");

        return $strReturn;
	}

	private function update_300_301() {
	    //Run the updates
	    $strReturn = "";
        $strReturn .= "Updating 3.0.0 to 3.0.1...\n";

        $strReturn .= "Updating module-versions...\n";
        $this->updateModuleVersion("news", "3.0.1");

        return $strReturn;
	}

	private function update_301_302() {
	    //Run the updates
	    $strReturn = "";
        $strReturn .= "Updating 3.0.1 to 3.0.2...\n";

        $strReturn .= "Updating module-versions...\n";
        $this->updateModuleVersion("news", "3.0.2");

        return $strReturn;
	}
	
    private function update_302_309() {
        //Run the updates
        $strReturn = "";
        $strReturn .= "Updating 3.0.2 to 3.0.9...\n";

        $strReturn .= "Updating module-versions...\n";
        $this->updateModuleVersion("news", "3.0.9");

        return $strReturn;
    }
    
	private function update_309_3095() {
        //Run the updates
        $strReturn = "";
        $strReturn .= "Updating 3.0.9 to 3.0.95...\n";

        $strReturn .= "Updating module-versions...\n";
        $this->updateModuleVersion("news", "3.0.95");

        return $strReturn;
    }
    
    private function update_3095_310() {
        $strReturn = "Updating 3.0.95 to 3.1.0...\n";

        $strReturn .= "Updating module-versions...\n";
        $this->updateModuleVersion("news", "3.1.0");

        return $strReturn;
    }
}
?>