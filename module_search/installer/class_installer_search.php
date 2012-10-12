<?php
/*"******************************************************************************************************
*   (c) 2004-2006 by MulchProductions, www.mulchprod.de                                                 *
*   (c) 2007-2012 by Kajona, www.kajona.de                                                              *
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
		$strReturn = "Installing search-log table...\n";

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
		$this->registerModule("search", _search_module_id_, "class_module_search_portal.php", "class_module_search_admin.php", $this->objMetadata->getStrVersion() , false, "class_module_search_portal_xml.php");


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
}
