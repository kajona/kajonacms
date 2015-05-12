<?php
/*"******************************************************************************************************
*   (c) 2004-2006 by MulchProductions, www.mulchprod.de                                                 *
*   (c) 2007-2015 by Kajona, www.kajona.de                                                              *
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
class class_installer_navigation extends class_installer_base implements interface_installer_removable {

    public function install() {

		$strReturn = "Installing ".$this->objMetadata->getStrTitle()."...\n";
        $objManager = new class_orm_schemamanager();

		$strReturn .= "Installing table navigation...\n";
        $objManager->createTable("class_module_navigation_point");

		//register the module
		$this->registerModule("navigation", _navigation_modul_id_, "class_module_navigation_portal.php", "class_module_navigation_admin.php", $this->objMetadata->getStrVersion() , true);

        $strReturn .= "Installing navigation-element table...\n";
        $objManager->createTable("class_element_navigation_admin");

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
        $objElement = class_module_pages_element::getElement("navigation");
        if($objElement != null) {
            $strReturn .= "Deleting page-element 'navigation'...\n";
            $objElement->deleteObject();
        }
        else {
            $strReturn .= "Error finding page-element 'navigation', aborting.\n";
            return false;
        }

        /** @var class_module_navigation_tree $objOneObject */
        foreach(class_module_navigation_tree::getObjectList() as $objOneObject) {
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
        foreach(array("navigation", "element_navigation") as $strOneTable) {
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
            $strReturn .= $this->update_349_3491();
        }

        $arrModule = class_module_system_module::getPlainModuleData($this->objMetadata->getStrTitle(), false);
        if($arrModule["module_version"] == "3.4.9.1") {
            $strReturn .= $this->update_3491_3492();
        }

        $arrModule = class_module_system_module::getPlainModuleData($this->objMetadata->getStrTitle(), false);
        if($arrModule["module_version"] == "3.4.9.2") {
            $strReturn .= $this->update_3492_40();
        }

        $arrModule = class_module_system_module::getPlainModuleData($this->objMetadata->getStrTitle(), false);
        if($arrModule["module_version"] == "4.0") {
            $strReturn .= $this->update_40_41();
        }

        $arrModule = class_module_system_module::getPlainModuleData($this->objMetadata->getStrTitle(), false);
        if($arrModule["module_version"] == "4.1") {
            $strReturn = "Updating 4.1 to 4.2...\n";
            $strReturn .= "Updating module-versions...\n";
            $this->updateModuleVersion("navigation", "4.2");
            $strReturn .= "Updating element-versions...\n";
            $this->updateElementVersion("navigation", "4.2");
        }

        $arrModule = class_module_system_module::getPlainModuleData($this->objMetadata->getStrTitle(), false);
        if($arrModule["module_version"] == "4.2") {
            $strReturn = "Updating 4.2 to 4.3...\n";
            $strReturn .= "Updating module-versions...\n";
            $this->updateModuleVersion("navigation", "4.3");
            $strReturn .= "Updating element-versions...\n";
            $this->updateElementVersion("navigation", "4.3");
        }

        $arrModule = class_module_system_module::getPlainModuleData($this->objMetadata->getStrTitle(), false);
        if($arrModule["module_version"] == "4.3") {
            $strReturn = "Updating 4.3 to 4.4...\n";
            $this->updateModuleVersion("navigation", "4.4");
            $this->updateElementVersion("navigation", "4.4");
        }

        $arrModule = class_module_system_module::getPlainModuleData($this->objMetadata->getStrTitle(), false);
        if($arrModule["module_version"] == "4.4") {
            $strReturn = "Updating 4.4 to 4.5...\n";
            $this->updateModuleVersion("navigation", "4.5");
            $this->updateElementVersion("navigation", "4.5");
        }

        $arrModule = class_module_system_module::getPlainModuleData($this->objMetadata->getStrTitle(), false);
        if($arrModule["module_version"] == "4.5") {
            $strReturn = "Updating 4.5 to 4.6...\n";
            $this->updateModuleVersion("navigation", "4.6");
            $this->updateElementVersion("navigation", "4.6");
        }

        $arrModule = class_module_system_module::getPlainModuleData($this->objMetadata->getStrTitle(), false);
        if($arrModule["module_version"] == "4.6") {
            $strReturn = "Updating to 4.7...\n";
            $this->updateModuleVersion("navigation", "4.7");
            $this->updateElementVersion("navigation", "4.7");
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
