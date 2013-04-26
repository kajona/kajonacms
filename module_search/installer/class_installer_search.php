<?php
/*"******************************************************************************************************
*   (c) 2004-2006 by MulchProductions, www.mulchprod.de                                                 *
*   (c) 2007-2013 by Kajona, www.kajona.de                                                              *
*       Published under the GNU LGPL v2.1, see /system/licence_lgpl.txt                                 *
*-------------------------------------------------------------------------------------------------------*
*	$Id$                                         *
********************************************************************************************************/


/**
 * Class providing the installer of the search-module
 *
 * @package module_search
 */
class class_installer_search extends class_installer_base implements interface_installer {
	/**
	 * Constructor
	 *
	 */
    public function __construct() {
        $this->objMetadata = new class_module_packagemanager_metadata();
        $this->objMetadata->autoInit(uniStrReplace(array(DIRECTORY_SEPARATOR."installer", _realpath_), array("", ""), __DIR__));
        $this->setArrModuleEntry("moduleId", _search_module_id_);

        parent::__construct();
	}


    public function install() {

        //Table for search
        $strReturn = "Installing table search_search...\n";

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

        if(class_module_pages_element::getElement("search") == null) {
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
        $arrModul = class_module_system_module::getPlainModuleData($this->objMetadata->getStrTitle(), false);

        $strReturn .= "Version found:\n\t Module: ".$arrModul["module_name"].", Version: ".$arrModul["module_version"]."\n\n";

        $arrModul = class_module_system_module::getPlainModuleData($this->objMetadata->getStrTitle(), false);
        if($arrModul["module_version"] == "3.4.2") {
            $strReturn .= $this->update_342_349();
        }

        $arrModul = class_module_system_module::getPlainModuleData($this->objMetadata->getStrTitle(), false);
        if($arrModul["module_version"] == "3.4.9") {
            $strReturn .= $this->update_342_3491();
        }

        $arrModul = class_module_system_module::getPlainModuleData($this->objMetadata->getStrTitle(), false);
        if($arrModul["module_version"] == "3.4.9.1") {
            $strReturn .= $this->update_3491_40();
        }

        $arrModul = class_module_system_module::getPlainModuleData($this->objMetadata->getStrTitle(), false);
        if($arrModul["module_version"] == "4.0") {
            $strReturn .= $this->update_40_41();
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

    private function update_40_41() {
        $strReturn = "Updating 4.0 to 4.1...\n";
        $strReturn .= "Updating module-versions...\n";
        $this->updateModuleVersion("search", "4.1");
        $this->updateElementVersion("search", "4.1");
        return $strReturn;
    }
}
