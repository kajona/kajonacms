<?php
/*"******************************************************************************************************
*   (c) 2004-2006 by MulchProductions, www.mulchprod.de                                                 *
*   (c) 2007-2011 by Kajona, www.kajona.de                                                              *
*       Published under the GNU LGPL v2.1, see /system/licence_lgpl.txt                                 *
*-------------------------------------------------------------------------------------------------------*
*   $Id$                                         *
********************************************************************************************************/

/**
 * Installer of the pages-module
 *
 * @package modul_pages
 */
class class_installer_pages extends class_installer_base implements interface_installer {

	public function __construct() {
        $arrModule = array();
		$arrModule["version"] 		= "3.3.1.1";
		$arrModule["name"] 			= "pages";
		$arrModule["name2"] 		= "pages_content";
		$arrModule["name3"] 		= "folderview";
		$arrModule["name_lang"] 	= "Module Pages";
		$arrModule["moduleId"] 		= _pages_modul_id_;
		parent::__construct($arrModule);
	}

	public function getNeededModules() {
	    return array("system");
	}

    public function getMinSystemVersion() {
	    return "3.2.1";
	}

	public function hasPostInstalls() {
		//check, if elements not already existing
		$strQuery = "SELECT COUNT(*) FROM "._dbprefix_."element WHERE element_name='paragraph'";
		$arrRow = $this->objDB->getRow($strQuery);
		if($arrRow["COUNT(*)"] == 0)
		    return true;

		$strQuery = "SELECT COUNT(*) FROM "._dbprefix_."element WHERE element_name='row'";
		$arrRow = $this->objDB->getRow($strQuery);
		if($arrRow["COUNT(*)"] == 0)
		    return true;

		$strQuery = "SELECT COUNT(*) FROM "._dbprefix_."element WHERE element_name='image'";
		$arrRow = $this->objDB->getRow($strQuery);
		if($arrRow["COUNT(*)"] == 0)
		    return true;

		return false;
	}

	public function install() {
		//Nur installieren, wenn noch nicht vorhanden
		if(count($this->objDB->getTables()) > 0) {
			$arrModul = $this->getModuleData($this->arrModule["name"]);
			if(count($arrModul) > 0)
				return "<strong>Module already installed!!!</strong><br /><br />";
		}

		$strReturn = "Installing ".$this->arrModule["name_lang"]."...\n";
		//Tabellen anlegen

		//Pages -----------------------------------------------------------------------------------------
		$strReturn .= "Installing table pages...\n";

		$arrFields = array();
		$arrFields["page_id"] 		= array("char20", false);
		$arrFields["page_name"] 	= array("char254", true);

		if(!$this->objDB->createTable("page", $arrFields, array("page_id")))
			$strReturn .= "An error occured! ...\n";

		//Pages_properties ------------------------------------------------------------------------------
		$strReturn .= "Installing table pages_properties...\n";

		$arrFields = array();
		$arrFields["pageproperties_id"] 		= array("char20", false);
		$arrFields["pageproperties_browsername"]= array("char254", true);
		$arrFields["pageproperties_keywords"] 	= array("char254", true);
		$arrFields["pageproperties_description"]= array("char254", true);
		$arrFields["pageproperties_template"] 	= array("char254", true);
		$arrFields["pageproperties_seostring"] 	= array("char254", true);
		$arrFields["pageproperties_language"] 	= array("char20", true);

		if(!$this->objDB->createTable("page_properties", $arrFields, array("pageproperties_id", "pageproperties_language")))
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

		if(!$this->objDB->createTable("element", $arrFields, array("element_id")))
			$strReturn .= "An error occured! ...\n";


		//pageelementtable-------------------------------------------------------------------------------
		$strReturn .= "Installing table page_element...\n";

		$arrFields = array();
		$arrFields["page_element_id"] 					= array("char20", false);
		$arrFields["page_element_placeholder_placeholder"]=array("char254", true);
		$arrFields["page_element_placeholder_name"] 	= array("char254", true);
		$arrFields["page_element_placeholder_element"]	= array("char254", true);
		$arrFields["page_element_placeholder_title"] 	= array("char254", true);
		$arrFields["page_element_placeholder_language"] = array("char20", true);

		if(!$this->objDB->createTable("page_element", $arrFields, array("page_element_id")))
			$strReturn .= "An error occured! ...\n";


		//Now we have to register module by module

		//the pages
		$strSystemID = $this->registerModule("pages", _pages_modul_id_, "class_modul_pages_portal.php", "class_modul_pages_admin.php", $this->arrModule["version"] , true, "", "class_modul_pages_admin_xml.php");
		//The pages_content
		$strRightID = $this->registerModule("pages_content", _pages_content_modul_id_, "", "class_modul_pages_content_admin.php", $this->arrModule["version"], false);
		//The folderview
		$strUserID = $this->registerModule("folderview", _pages_folderview_modul_id_, "", "class_modul_folderview_admin.php", $this->arrModule["version"] , false);

		$strReturn .= "Registering system-constants...\n";
		$this->registerConstant("_pages_templatechange_", "false", class_modul_system_setting::$int_TYPE_BOOL, _pages_modul_id_);
		$this->registerConstant("_pages_indexpage_", "index", class_modul_system_setting::$int_TYPE_PAGE, _pages_modul_id_);
		$this->registerConstant("_pages_errorpage_", "error", class_modul_system_setting::$int_TYPE_PAGE, _pages_modul_id_);
		$this->registerConstant("_pages_defaulttemplate_", "", class_modul_system_setting::$int_TYPE_STRING, _pages_modul_id_);
		//2.1.1: overall cachetime
		$this->registerConstant("_pages_cacheenabled_", "true", class_modul_system_setting::$int_TYPE_BOOL, _pages_modul_id_);
		//2.1.1: possibility, to create new pages disabled
		$this->registerConstant("_pages_newdisabled_", "false", class_modul_system_setting::$int_TYPE_BOOL, _pages_modul_id_);
		//portaleditor
        $this->registerConstant("_pages_portaleditor_", "true", class_modul_system_setting::$int_TYPE_BOOL, _pages_modul_id_);

        $strReturn .= "Shifting pages to first position...\n";
        $objCommon = new class_modul_system_common();
        $objCommon->setAbsolutePosition($strSystemID, 1);


		return $strReturn;

	}

	public function postInstall() {
		$strReturn = "";

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
		    $objElement = class_modul_pages_element::getElement("paragraph");
		}
		catch (class_exception $objEx)  {
		}
		if($objElement == null) {
		    $objElement = new class_modul_pages_element();
		    $objElement->setStrName("paragraph");
		    $objElement->setStrClassAdmin("class_element_paragraph.php");
		    $objElement->setStrClassPortal("class_element_paragraph.php");
		    $objElement->setIntCachetime(3600*24*30);
		    $objElement->setIntRepeat(1);
            $objElement->setStrVersion($this->getVersion());
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
		    $objElement = class_modul_pages_element::getElement("row");
		}
		catch (class_exception $objEx)  {
		}
		if($objElement == null) {
		    $objElement = new class_modul_pages_element();
		    $objElement->setStrName("row");
		    $objElement->setStrClassAdmin("class_element_row.php");
		    $objElement->setStrClassPortal("class_element_row.php");
		    $objElement->setIntCachetime(3600*24*30);
		    $objElement->setIntRepeat(0);
            $objElement->setStrVersion($this->getVersion());
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
		    $objElement = class_modul_pages_element::getElement("image");
		}
		catch (class_exception $objEx)  {
		}
		if($objElement == null) {
		    $objElement = new class_modul_pages_element();
		    $objElement->setStrName("image");
		    $objElement->setStrClassAdmin("class_element_image.php");
		    $objElement->setStrClassPortal("class_element_image.php");
		    $objElement->setIntCachetime(3600*24*30);
		    $objElement->setIntRepeat(1);
            $objElement->setStrVersion($this->getVersion());
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
        $arrModul = $this->getModuleData($this->arrModule["name"], false);

        $strReturn .= "Version found:\n\t Module: ".$arrModul["module_name"].", Version: ".$arrModul["module_version"]."\n\n";

	    $arrModul = $this->getModuleData($this->arrModule["name"], false);
        if($arrModul["module_version"] == "3.1.0") {
            $strReturn .= $this->update_310_311();
        }

        $arrModul = $this->getModuleData($this->arrModule["name"], false);
        if($arrModul["module_version"] == "3.1.1") {
            $strReturn .= $this->update_311_319();
        }

        $arrModul = $this->getModuleData($this->arrModule["name"], false);
        if($arrModul["module_version"] == "3.1.9") {
            $strReturn .= $this->update_319_3195();
        }

        $arrModul = $this->getModuleData($this->arrModule["name"], false);
        if($arrModul["module_version"] == "3.1.95") {
            $strReturn .= $this->update_3195_320();
        }

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
            $strReturn .= $this->update_3292_3293();
        }

        $arrModul = $this->getModuleData($this->arrModule["name"], false);
        if($arrModul["module_version"] == "3.2.93") {
            $strReturn .= $this->update_3293_330();
        }

        $arrModul = $this->getModuleData($this->arrModule["name"], false);
        if($arrModul["module_version"] == "3.3.0") {
            $strReturn .= $this->update_330_3301();
        }

        $arrModul = $this->getModuleData($this->arrModule["name"], false);
        if($arrModul["module_version"] == "3.3.0.1") {
            $strReturn .= $this->update_3301_3302();
        }

        $arrModul = $this->getModuleData($this->arrModule["name"], false);
        if($arrModul["module_version"] == "3.3.0.2") {
            $strReturn .= $this->update_3302_331();
        }

        $arrModul = $this->getModuleData($this->arrModule["name"], false);
        if($arrModul["module_version"] == "3.3.1") {
            $strReturn .= $this->update_331_3311();
        }

        return $strReturn."\n\n";
	}

    private function update_310_311() {
        $strReturn = "";

        $strReturn .= "Updating 3.1.0 to 3.1.1...\n";

        $strReturn .= "Updating module-versions...\n";
        $this->updateModuleVersion("", "3.1.1");

        return $strReturn;
    }

    private function update_311_319() {
        $strReturn = "";

        $strReturn .= "Updating 3.1.1 to 3.1.9...\n";
        $strReturn .= "Registering module settings...\n";
        $objModule = class_modul_system_module::getModuleByName("pages", true);
        $objModule->setStrNamePortal("class_modul_pages_portal.php");
        if(!$objModule->updateObjectToDb())
            $strReturn .= "An error occured!\n";

        $objModule = class_modul_system_module::getModuleByName("folderview", true);
        $objModule->setStrNameAdmin("class_modul_folderview_admin.php");
        if(!$objModule->updateObjectToDb())
            $strReturn .= "An error occured!\n";

        $strReturn .= "Updating system-constants...\n";
        $objConstant = class_modul_system_setting::getConfigByName("_pages_fehlerseite_");
        $objConstant->renameConstant("_pages_errorpage_");

        $objConstant = class_modul_system_setting::getConfigByName("_pages_startseite_");
        $objConstant->renameConstant("_pages_indexpage_");

        $objConstant = class_modul_system_setting::getConfigByName("_pages_templatewechsel_");
        $objConstant->renameConstant("_pages_templatechange_");


        $strReturn .= "Updating module-versions...\n";
        $this->updateModuleVersion("", "3.1.9");

        return $strReturn;
    }

    private function update_319_3195() {
        $strReturn = "Updating 3.1.9 to 3.1.95...\n";

        $strReturn .= "Searching for portallogin-element to alter...\n";
        $strQuery = "SELECT COUNT(*) FROM "._dbprefix_."element WHERE element_name='portallogin'";
        $arrRow = $this->objDB->getRow($strQuery);
        if($arrRow["COUNT(*)"] != 0) {
        	$strSql = "ALTER TABLE ".$this->objDB->encloseTableName(_dbprefix_."element_portallogin")."
        	                   ADD ".$this->objDB->encloseColumnName("portallogin_profile")." VARCHAR (254) NULL ";

        	if(!$this->objDB->_query($strSql))
        	   $strReturn .= "An error occured!\n";
        }


        $strReturn .= "Updating module-versions...\n";
        $this->updateModuleVersion("", "3.1.95");

        return $strReturn;
    }

    private function update_3195_320() {
        $strReturn = "Updating 3.1.95 to 3.2.0...\n";
        $strReturn .= "Updating module-versions...\n";
        $this->updateModuleVersion("", "3.2.0");
        return $strReturn;
    }

    private function update_320_3209() {
        $strReturn = "Updating 3.2.0 to 3.2.0.9...\n";

        $strReturn .= "Updating module-versions...\n";
        $this->updateModuleVersion("", "3.2.0.9");
        return $strReturn;
    }

    private function update_3209_321() {
        $strReturn = "Updating 3.2.0.9 to 3.2.1...\n";

        $strReturn .= "Updating module-versions...\n";
        $this->updateModuleVersion("", "3.2.1");

        $strReturn .= "Updating element-version...\n";
        $this->updateElementVersion("row", "3.2.1");
        $this->updateElementVersion("paragraph", "3.2.1");
        $this->updateElementVersion("image", "3.2.1");
        return $strReturn;
    }


    private function update_321_3291() {
        $strReturn = "Updating 3.2.1 to 3.2.91...\n";


        $strReturn .= "Reorganizing pages...\n";

        $strQuery = "SELECT module_id
                       FROM "._dbprefix_."system_module
                      WHERE module_nr = "._pages_modul_id_."";
        $arrEntries = $this->objDB->getRow($strQuery);
        $strModuleId = $arrEntries["module_id"];

        $strQuery = "SELECT page_id
                       FROM "._dbprefix_."page,
                            "._dbprefix_."system
                       WHERE page_id=system_id
                         AND system_prev_id = '0'";
        $arrEntries = $this->objDB->getArray($strQuery);

        foreach($arrEntries as $arrSingleRow) {
            $strReturn .= " ...updating page ".$arrSingleRow["page_id"]."";
            $strQuery = "UPDATE "._dbprefix_."system
                            SET system_prev_id = '".dbsafeString($strModuleId)."'
                          WHERE system_id = '".dbsafeString($arrSingleRow["page_id"])."'";
            if($this->objDB->_query($strQuery))
                $strReturn .= " ...ok\n";
            else
                $strReturn .= " ...failed!!!\n";
        }



        $strReturn .= "Reorganizing folders...\n";

        $strQuery = "SELECT system_id
                       FROM "._dbprefix_."system
                      WHERE system_module_nr = "._pages_folder_id_."
                        AND system_prev_id = '0'";
        $arrEntries = $this->objDB->getArray($strQuery);


        foreach($arrEntries as $arrSingleRow) {
            $strReturn .= " ...updating folder ".$arrSingleRow["system_id"]."";
            $strQuery = "UPDATE "._dbprefix_."system
                            SET system_prev_id = '".dbsafeString($strModuleId)."'
                          WHERE system_id = '".dbsafeString($arrSingleRow["system_id"])."'";

            if($this->objDB->_query($strQuery))
                $strReturn .= " ...ok\n";
            else
                $strReturn .= " ...failed!!!\n";
        }


        $strReturn .= "Updating module-versions...\n";
        $this->updateModuleVersion("", "3.2.91");

        $strReturn .= "Updating element-version...\n";
        $this->updateElementVersion("row", "3.2.91");
        $this->updateElementVersion("paragraph", "3.2.91");
        $this->updateElementVersion("image", "3.2.91");
        return $strReturn;
    }


    private function update_3291_3292() {
        $strReturn = "Updating 3.2.91 to 3.2.92...\n";


        $strReturn .= "Transforming old element absatz to element paragraph...\n";

        $arrTables = $this->objDB->getTables();
        if(in_array(_dbprefix_."element_absatz", $arrTables) && !in_array(_dbprefix_."element_paragraph", $arrTables)) {
            $strReturn .= "Renaming table to element_paragraph\n";
            $strQuery = "RENAME TABLE ".$this->objDB->encloseTableName(_dbprefix_."element_absatz")." TO ".$this->objDB->encloseTableName(_dbprefix_."element_paragraph")."";
            $this->objDB->_query($strQuery);

            $strReturn .= "Adding row paragraph_template...\n";
            $strQuery = "ALTER TABLE ".$this->objDB->encloseTableName(_dbprefix_."element_paragraph")."
                               CHANGE ".$this->objDB->encloseColumnName("absatz_titel")." ".$this->objDB->encloseColumnName("paragraph_title")." ".$this->objDB->getDatatype("char254")." NULL,
                               CHANGE ".$this->objDB->encloseColumnName("absatz_inhalt")." ".$this->objDB->encloseColumnName("paragraph_content")." ".$this->objDB->getDatatype("text")." NULL,
                               CHANGE ".$this->objDB->encloseColumnName("absatz_link")." ".$this->objDB->encloseColumnName("paragraph_link")." ".$this->objDB->getDatatype("char254")." NULL,
                               CHANGE ".$this->objDB->encloseColumnName("absatz_bild")."  ".$this->objDB->encloseColumnName("paragraph_image")."".$this->objDB->getDatatype("char254")." NULL,
        	                   ADD ".$this->objDB->encloseColumnName("paragraph_template")." ".$this->objDB->getDatatype("char254")." NULL ";

            $this->objDB->_query($strQuery);

            $strReturn .= "Setting new classes to existing elements...\n";
            $strQuery = "UPDATE ".$this->objDB->encloseTableName(_dbprefix_."element")."
                               SET ".$this->objDB->encloseColumnName("element_class_admin")." = 'class_element_paragraph.php'
                               WHERE ".$this->objDB->encloseColumnName("element_class_admin")." = 'class_element_absatz.php'";
            $this->objDB->_query($strQuery);
            $strQuery = "UPDATE ".$this->objDB->encloseTableName(_dbprefix_."element")."
                               SET ".$this->objDB->encloseColumnName("element_class_portal")." = 'class_element_paragraph.php'
                               WHERE ".$this->objDB->encloseColumnName("element_class_portal")." = 'class_element_absatz.php'";
            $this->objDB->_query($strQuery);
        }

        $strReturn .= "Updating module-versions...\n";
        $this->updateModuleVersion("3.2.92");

        $strReturn .= "Updating element-version...\n";
        $this->updateElementVersion("row", "3.2.92");
        $this->updateElementVersion("paragraph", "3.2.92");
        $this->updateElementVersion("image", "3.2.92");
        return $strReturn;
    }

    private function update_3292_3293() {
        $strReturn = "Updating 3.2.92 to 3.2.93...\n";


        $strReturn .= "Transforming old element bild to element image...\n";

        $arrTables = $this->objDB->getTables();
        if(in_array(_dbprefix_."element_bild", $arrTables) && !in_array(_dbprefix_."element_image", $arrTables)) {
            $strReturn .= "Renaming table to element_image\n";
            $strQuery = "RENAME TABLE ".$this->objDB->encloseTableName(_dbprefix_."element_bild")." TO ".$this->objDB->encloseTableName(_dbprefix_."element_image")."";
            $this->objDB->_query($strQuery);

            $strReturn .= "Adding row paragraph_template...\n";
            $strQuery = "ALTER TABLE ".$this->objDB->encloseTableName(_dbprefix_."element_image")."
                               CHANGE ".$this->objDB->encloseColumnName("bild_titel")." ".$this->objDB->encloseColumnName("image_title")." ".$this->objDB->getDatatype("char254")." NULL,
                               CHANGE ".$this->objDB->encloseColumnName("bild_link")." ".$this->objDB->encloseColumnName("image_link")." ".$this->objDB->getDatatype("char254")." NULL,
                               CHANGE ".$this->objDB->encloseColumnName("bild_bild")."  ".$this->objDB->encloseColumnName("image_image")."".$this->objDB->getDatatype("char254")." NULL,
                               CHANGE ".$this->objDB->encloseColumnName("bild_x")."  ".$this->objDB->encloseColumnName("image_x")."".$this->objDB->getDatatype("char254")." NULL,
                               CHANGE ".$this->objDB->encloseColumnName("bild_y")."  ".$this->objDB->encloseColumnName("image_y")."".$this->objDB->getDatatype("char254")." NULL,
        	                   ADD ".$this->objDB->encloseColumnName("image_template")." ".$this->objDB->getDatatype("char254")." NULL ";

            $this->objDB->_query($strQuery);

            $strReturn .= "Setting new classes to existing image elements...\n";
            $strQuery = "UPDATE ".$this->objDB->encloseTableName(_dbprefix_."element")."
                               SET ".$this->objDB->encloseColumnName("element_class_admin")." = 'class_element_image.php'
                               WHERE ".$this->objDB->encloseColumnName("element_class_admin")." = 'class_element_bild.php'";
            $this->objDB->_query($strQuery);
            $strQuery = "UPDATE ".$this->objDB->encloseTableName(_dbprefix_."element")."
                               SET ".$this->objDB->encloseColumnName("element_class_portal")." = 'class_element_image.php'
                               WHERE ".$this->objDB->encloseColumnName("element_class_portal")." = 'class_element_bild.php'";
            $this->objDB->_query($strQuery);
        }


        $strReturn .= "Setting new classes to existing row elements...\n";
        $strQuery = "UPDATE ".$this->objDB->encloseTableName(_dbprefix_."element")."
                           SET ".$this->objDB->encloseColumnName("element_class_admin")." = 'class_element_row.php'
                           WHERE ".$this->objDB->encloseColumnName("element_class_admin")." = 'class_element_zeile.php'";
        $this->objDB->_query($strQuery);
        $strQuery = "UPDATE ".$this->objDB->encloseTableName(_dbprefix_."element")."
                           SET ".$this->objDB->encloseColumnName("element_class_portal")." = 'class_element_row.php'
                           WHERE ".$this->objDB->encloseColumnName("element_class_portal")." = 'class_element_zeile.php'";
        $this->objDB->_query($strQuery);

        $strReturn .= "Updating module-versions...\n";
        $this->updateModuleVersion("", "3.2.93");

        $strReturn .= "Updating element-version...\n";
        $this->updateElementVersion("row", "3.2.93");
        $this->updateElementVersion("paragraph", "3.2.93");
        $this->updateElementVersion("image", "3.2.93");
        return $strReturn;
    }

    private function update_3293_330() {
        $strReturn = "Updating 3.2.93 to 3.3.0...\n";

        $strReturn .= "Updating module-versions...\n";
        $this->updateModuleVersion("", "3.3.0");

        $strReturn .= "Updating element-version...\n";
        $this->updateElementVersion("row", "3.3.0");
        $this->updateElementVersion("paragraph", "3.3.0");
        $this->updateElementVersion("image", "3.3.0");
        return $strReturn;
    }

    private function update_330_3301() {
        $strReturn = "Updating 3.3.0 to 3.3.0.1...\n";

        $strReturn .= "Dropping table page_cache...\n";
        $strQuery = "DROP TABLE "._dbprefix_."page_cache";
        if(!$this->objDB->_query($strQuery))
			$strReturn .= "An error occured! ...\n";

        $strReturn .= "Updating module-versions...\n";
        $this->updateModuleVersion("", "3.3.0.1");

        $strReturn .= "Updating element-version...\n";
        $this->updateElementVersion("row", "3.3.0.1");
        $this->updateElementVersion("paragraph", "3.3.0.1");
        $this->updateElementVersion("image", "3.3.0.1");
        return $strReturn;
    }

    private function update_3301_3302() {
        $strReturn = "Updating 3.3.0.1 to 3.3.0.2...\n";

        $strReturn .= "Setting cache-timeouts for paragraphs, rows and images...\n";
        $strQuery = "UPDATE "._dbprefix_."element
                        SET element_cachetime=".(3600*24*30)."
                      WHERE element_class_admin = 'class_element_paragraph.php'
                         OR element_class_admin = 'class_element_row.php'
                         OR element_class_admin = 'class_element_imagelightbox.php'
                         OR element_class_admin = 'class_element_image.php'";
        if(!$this->objDB->_query($strQuery))
			$strReturn .= "An error occured! ...\n";

        $strReturn .= "Updating module-versions...\n";
        $this->updateModuleVersion("", "3.3.0.2");

        $strReturn .= "Updating element-version...\n";
        $this->updateElementVersion("row", "3.3.0.2");
        $this->updateElementVersion("paragraph", "3.3.0.2");
        $this->updateElementVersion("image", "3.3.0.2");
        return $strReturn;
    }

    private function update_3302_331() {
        $strReturn = "Updating 3.3.0.2 to 3.3.1...\n";

        $strReturn .= "Deleting old systemtasks...\n";
        $objFilesystem = new class_filesystem();
        $objFilesystem->fileDelete("/admin/systemtasks/class_systemtask_flushpagescache.php");

        $strReturn .= "Updating module-versions...\n";
        $this->updateModuleVersion("", "3.3.1");

        $strReturn .= "Updating element-version...\n";
        $this->updateElementVersion("row", "3.3.1");
        $this->updateElementVersion("paragraph", "3.3.1");
        $this->updateElementVersion("image", "3.3.1");
        return $strReturn;
    }

    private function update_331_3311() {
        $strReturn = "Updating 3.3.1 to 3.3.1.1...\n";

        $strReturn .= "Removing unused constant _pages_maxcachetime_...\n";
        $objConstant = class_modul_system_setting::getConfigByName("_pages_maxcachetime_");
        $strQuery = "DELETE FROM "._dbprefix_."system_config WHERE system_config_id='".$objConstant->getSystemid()."'";
        if(!$this->objDB->_query($strQuery))
            $strReturn .= "An error occured! ...\n";


        $strReturn .= "Updating module-versions...\n";
        $this->updateModuleVersion("", "3.3.1.1");

        $strReturn .= "Updating element-version...\n";
        $this->updateElementVersion("row", "3.3.1.1");
        $this->updateElementVersion("paragraph", "3.3.1.1");
        $this->updateElementVersion("image", "3.3.1.1");
        return $strReturn;
    }

}
?>