<?php
/*"******************************************************************************************************
*   (c) 2004-2006 by MulchProductions, www.mulchprod.de                                                 *
*   (c) 2007-2014 by Kajona, www.kajona.de                                                              *
*       Published under the GNU LGPL v2.1, see /system/licence_lgpl.txt                                 *
*-------------------------------------------------------------------------------------------------------*
*   $Id$                                     *
********************************************************************************************************/

/**
 * Installer of the navigation
 *
 * @package module_navigation
 * @moduleId _navigation_modul_id_
 */
class class_installer_navigation extends class_installer_base implements interface_installer {

    public function install() {

		$strReturn = "Installing ".$this->objMetadata->getStrTitle()."...\n";
		//Tabellen anlegen

		//navigation-------------------------------------------------------------------------------------
		$strReturn .= "Installing table navigation...\n";

		$arrFields = array();
		$arrFields["navigation_id"] 		= array("char20", false);
		$arrFields["navigation_name"] 		= array("char254", true);
		$arrFields["navigation_page_e"] 	= array("char254", true);
		$arrFields["navigation_page_i"] 	= array("char254", true);
		$arrFields["navigation_folder_i"] 	= array("char20", true);
		$arrFields["navigation_target"] 	= array("char254", true);
		$arrFields["navigation_image"] 		= array("char254", true);

		if(!$this->objDB->createTable("navigation", $arrFields, array("navigation_id")))
			$strReturn .= "An error occurred! ...\n";


		//register the module
		$this->registerModule("navigation", _navigation_modul_id_, "class_module_navigation_portal.php", "class_module_navigation_admin.php", $this->objMetadata->getStrVersion() , true);



        $strReturn .= "Installing navigation-element table...\n";

        $arrFields = array();
        $arrFields["content_id"] 			= array("char20", false);
        $arrFields["navigation_id"] 		= array("char20", true);
        $arrFields["navigation_template"] 	= array("char254", true);
        $arrFields["navigation_mode"] 		= array("char254", true);
        $arrFields["navigation_foreign"] 	= array("int", true);

        if(!$this->objDB->createTable("element_navigation", $arrFields, array("content_id")))
            $strReturn .= "An error occurred! ...\n";

        //Register the element
        $strReturn .= "Registering navigation-element...\n";
        //check, if not already existing
        $objElement = null;
        try {
            $objElement = class_module_pages_element::getElement("navigation");
        }
        catch (class_exception $objEx)  {
        }
        if($objElement == null) {
            $objElement = new class_module_pages_element();
            $objElement->setStrName("navigation");
            $objElement->setStrClassAdmin("class_element_navigation_admin.php");
            $objElement->setStrClassPortal("class_element_navigation_portal.php");
            $objElement->setIntCachetime(3600);
            $objElement->setIntRepeat(0);
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
            $strReturn .= $this->update_349_3491();
        }

        $arrModul = class_module_system_module::getPlainModuleData($this->objMetadata->getStrTitle(), false);
        if($arrModul["module_version"] == "3.4.9.1") {
            $strReturn .= $this->update_3491_3492();
        }

        $arrModul = class_module_system_module::getPlainModuleData($this->objMetadata->getStrTitle(), false);
        if($arrModul["module_version"] == "3.4.9.2") {
            $strReturn .= $this->update_3492_40();
        }

        $arrModul = class_module_system_module::getPlainModuleData($this->objMetadata->getStrTitle(), false);
        if($arrModul["module_version"] == "4.0") {
            $strReturn .= $this->update_40_41();
        }

        $arrModul = class_module_system_module::getPlainModuleData($this->objMetadata->getStrTitle(), false);
        if($arrModul["module_version"] == "4.1") {
            $strReturn = "Updating 4.1 to 4.2...\n";
            $strReturn .= "Updating module-versions...\n";
            $this->updateModuleVersion("navigation", "4.2");
            $strReturn .= "Updating element-versions...\n";
            $this->updateElementVersion("navigation", "4.2");
        }

        $arrModul = class_module_system_module::getPlainModuleData($this->objMetadata->getStrTitle(), false);
        if($arrModul["module_version"] == "4.2") {
            $strReturn = "Updating 4.2 to 4.3...\n";
            $strReturn .= "Updating module-versions...\n";
            $this->updateModuleVersion("navigation", "4.3");
            $strReturn .= "Updating element-versions...\n";
            $this->updateElementVersion("navigation", "4.3");
        }

        $arrModul = class_module_system_module::getPlainModuleData($this->objMetadata->getStrTitle(), false);
        if($arrModul["module_version"] == "4.3") {
            $strReturn = "Updating 4.3 to 4.4...\n";
            $this->updateModuleVersion("navigation", "4.4");
            $this->updateElementVersion("navigation", "4.4");
        }

        return $strReturn."\n\n";
	}


    private function update_342_349() {
        $strReturn = "Updating 3.4.2 to 3.4.9...\n";

        $strReturn .= "Adding classes for existing records...\n";
        $strReturn .= "Trees\n";
        $arrRows = $this->objDB->getPArray(
            "SELECT system_id FROM "._dbprefix_."navigation, "._dbprefix_."system WHERE system_id = navigation_id AND system_prev_id = ? AND (system_class IS NULL OR system_class = '')",
            array(class_module_system_module::getModuleIdByNr(_navigation_modul_id_))
        );
        foreach($arrRows as $arrOneRow) {
            $strQuery = "UPDATE "._dbprefix_."system SET system_class = ? where system_id = ?";
            $this->objDB->_pQuery($strQuery, array( 'class_module_navigation_tree', $arrOneRow["system_id"] ) );
        }

        $strReturn .= "Navigation Points\n";
        $arrRows = $this->objDB->getPArray("SELECT system_id FROM "._dbprefix_."navigation, "._dbprefix_."system WHERE system_id = navigation_id AND (system_class IS NULL OR system_class = '')", array());
        foreach($arrRows as $arrOneRow) {
            $strQuery = "UPDATE "._dbprefix_."system SET system_class = ? where system_id = ?";
            $this->objDB->_pQuery($strQuery, array( 'class_module_navigation_point', $arrOneRow["system_id"] ) );
        }

        $strReturn .= "Setting aspect assignments...\n";
        if(class_module_system_aspect::getAspectByName("content") != null) {
            $objModule = class_module_system_module::getModuleByName($this->objMetadata->getStrTitle());
            $objModule->setStrAspect(class_module_system_aspect::getAspectByName("content")->getSystemid());
            $objModule->updateObjectToDb();
        }

        $strReturn .= "Updating module-versions...\n";
        $this->updateModuleVersion("navigation", "3.4.9");
        $strReturn .= "Updating element-versions...\n";
        $this->updateElementVersion("navigation", "3.4.9");

        return $strReturn;
    }

    private function update_349_3491() {
        $strReturn = "Updating 3.4.9 to 3.4.9.1...\n";

        $strReturn .= "Adding foreign-column to navigation element...\n";

        $strQuery = "ALTER TABLE ".$this->objDB->encloseTableName(_dbprefix_."element_navigation")."
                    ADD ".$this->objDB->encloseColumnName("navigation_foreign")." ".$this->objDB->getDatatype("int")." ";

        if(!$this->objDB->_query($strQuery))
            $strReturn .= "An error occurred! ...\n";

        $strReturn .= "Updating module-versions...\n";
        $this->updateModuleVersion("navigation", "3.4.9.1");
        $strReturn .= "Updating element-versions...\n";
        $this->updateElementVersion("navigation", "3.4.9.1");

        return $strReturn;
    }

    private function update_3491_3492() {
        $strReturn = "Updating 3.4.9.1 to 3.4.9.2...\n";

        $strReturn .= "Removing mode column from navigation element...\n";

        $strQuery = "ALTER TABLE ".$this->objDB->encloseTableName(_dbprefix_."element_navigation")."
                    DROP ".$this->objDB->encloseColumnName("navigation_mode")."";

        if(!$this->objDB->_query($strQuery))
            $strReturn .= "An error occurred! ...\n";

        $strReturn .= "Updating module-versions...\n";
        $this->updateModuleVersion("navigation", "3.4.9.2");
        $strReturn .= "Updating element-versions...\n";
        $this->updateElementVersion("navigation", "3.4.9.2");

        return $strReturn;
    }

    private function update_3492_40() {
        $strReturn = "Updating 3.4.9.2 to 4.0...\n";
        $strReturn .= "Updating module-versions...\n";
        $this->updateModuleVersion("navigation", "4.0");
        $strReturn .= "Updating element-versions...\n";
        $this->updateElementVersion("navigation", "4.0");
        return $strReturn;
    }

    private function update_40_41() {
        $strReturn = "Updating 4.0 to 4.1...\n";
        $strReturn .= "Updating module-versions...\n";
        $this->updateModuleVersion("navigation", "4.1");
        $strReturn .= "Updating element-versions...\n";
        $this->updateElementVersion("navigation", "4.1");
        return $strReturn;
    }
}
