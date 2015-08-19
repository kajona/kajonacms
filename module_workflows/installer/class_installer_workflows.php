<?php
/*"******************************************************************************************************
*   (c) 2004-2006 by MulchProductions, www.mulchprod.de                                                 *
*   (c) 2007-2015 by Kajona, www.kajona.de                                                              *
*       Published under the GNU LGPL v2.1, see /system/licence_lgpl.txt                                 *
*-------------------------------------------------------------------------------------------------------*
*	$Id$					    *
********************************************************************************************************/

/**
 * Class providing an installer for the workflows module
 *
 * @package module_workflows
 * @moduleId _workflows_module_id_
 */
class class_installer_workflows extends class_installer_base implements interface_installer_removable {

    private $bitUpdatingFrom3421 = false;

    public function install() {
		$strReturn = "";
        $objManager = new class_orm_schemamanager();
		//workflows workflow ---------------------------------------------------------------------
		$strReturn .= "Installing table workflows...\n";
        $objManager->createTable("class_module_workflows_workflow");

        $strReturn .= "Installing table workflows_handler...\n";
        $objManager->createTable("class_module_workflows_handler");

		//register the module
		$this->registerModule(
            "workflows",
            _workflows_module_id_,
            "class_module_workflows_portal.php",
            "class_module_workflows_admin.php",
            $this->objMetadata->getStrVersion(),
            true
        );

        $strReturn .= "synchronizing list...\n";
        class_module_workflows_handler::synchronizeHandlerList();

        $strReturn .= "Generating and adding trigger-authkey...\n";
        $this->registerConstant("_workflows_trigger_authkey_", generateSystemid(), class_module_system_setting::$int_TYPE_STRING, _workflows_module_id_);

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

        $strReturn .= "Removing system settings...\n";
        if(class_module_system_setting::getConfigByName("_workflows_trigger_authkey_") != null)
            class_module_system_setting::getConfigByName("_workflows_trigger_authkey_")->deleteObjectFromDatabase();

        /** @var class_module_workflows_workflow $objOneObject */
        foreach(class_module_workflows_workflow::getObjectList() as $objOneObject) {
            $strReturn .= "Deleting object '".$objOneObject->getStrDisplayName()."' ...\n";
            if(!$objOneObject->deleteObjectFromDatabase()) {
                $strReturn .= "Error deleting object, aborting.\n";
                return false;
            }
        }

        /** @var class_module_workflows_handler $objOneObject */
        foreach(class_module_workflows_handler::getObjectList() as $objOneObject) {
            $strReturn .= "Deleting object '".$objOneObject->getStrDisplayName()."' ...\n";
            if(!$objOneObject->deleteObjectFromDatabase()) {
                $strReturn .= "Error deleting object, aborting.\n";
                return false;
            }
        }

        //delete the module-node
        $strReturn .= "Deleting the module-registration...\n";
        $objModule = class_module_system_module::getModuleByName($this->objMetadata->getStrTitle(), true);
        if(!$objModule->deleteObjectFromDatabase()) {
            $strReturn .= "Error deleting module, aborting.\n";
            return false;
        }

        //delete the tables
        foreach(array("workflows_handler", "workflows") as $strOneTable) {
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
        if($arrModule["module_version"] == "4.0") {
            $strReturn .= $this->update_40_401();
        }

        $arrModule = class_module_system_module::getPlainModuleData($this->objMetadata->getStrTitle(), false);
        if($arrModule["module_version"] == "4.0.1") {
            $strReturn .= "Updating 4.0.1 to 4.1...\n";
            $strReturn .= "Updating module-versions...\n";
            $this->updateModuleVersion($this->objMetadata->getStrTitle(), "4.1");
        }

        $arrModule = class_module_system_module::getPlainModuleData($this->objMetadata->getStrTitle(), false);
        if($arrModule["module_version"] == "4.1") {
            $strReturn .= "Updating 4.1 to 4.2...\n";
            $strReturn .= "Updating module-versions...\n";
            $this->updateModuleVersion($this->objMetadata->getStrTitle(), "4.2");
        }

        $arrModule = class_module_system_module::getPlainModuleData($this->objMetadata->getStrTitle(), false);
        if($arrModule["module_version"] == "4.2") {
            $strReturn .= "Updating 4.2 to 4.3...\n";
            $strReturn .= "Updating module-versions...\n";
            $this->updateModuleVersion($this->objMetadata->getStrTitle(), "4.3");
        }

        $arrModule = class_module_system_module::getPlainModuleData($this->objMetadata->getStrTitle(), false);
        if($arrModule["module_version"] == "4.3") {
            $strReturn .= $this->update_43_431();
        }

        $arrModule = class_module_system_module::getPlainModuleData($this->objMetadata->getStrTitle(), false);
        if($arrModule["module_version"] == "4.3.1") {
            $strReturn .= "Updating 4.3 to 4.4...\n";
            $this->updateModuleVersion($this->objMetadata->getStrTitle(), "4.4");
        }

        $arrModule = class_module_system_module::getPlainModuleData($this->objMetadata->getStrTitle(), false);
        if($arrModule["module_version"] == "4.4") {
            $strReturn .= "Updating 4.4 to 4.5...\n";
            $this->updateModuleVersion($this->objMetadata->getStrTitle(), "4.5");
        }

        $arrModule = class_module_system_module::getPlainModuleData($this->objMetadata->getStrTitle(), false);
        if($arrModule["module_version"] == "4.5") {
            $strReturn .= $this->update_45_451();
        }

        $arrModule = class_module_system_module::getPlainModuleData($this->objMetadata->getStrTitle(), false);
        if($arrModule["module_version"] == "4.5.1") {
            $strReturn .= "Updating 4.5.1 to 4.6...\n";
            $this->updateModuleVersion($this->objMetadata->getStrTitle(), "4.6");
        }

        $arrModule = class_module_system_module::getPlainModuleData($this->objMetadata->getStrTitle(), false);
        if($arrModule["module_version"] == "4.6") {
            $strReturn .= "Updating to 4.7...\n";
            $this->updateModuleVersion($this->objMetadata->getStrTitle(), "4.7");
        }

        $arrModule = class_module_system_module::getPlainModuleData($this->objMetadata->getStrTitle(), false);
        if($arrModule["module_version"] == "4.7") {
            $strReturn .= $this->update_47_475();
        }

        $arrModule = class_module_system_module::getPlainModuleData($this->objMetadata->getStrTitle(), false);
        if($arrModule["module_version"] == "4.7.5") {
            $strReturn .= $this->update_475_476();
        }

        return $strReturn."\n\n";
	}


    private function update_40_401() {
        $strReturn = "Updating 4.0 to 4.0.1...\n";

        if(!$this->bitUpdatingFrom3421) {
            $strReturn .= "Altering workflows table...\n";

            $strQuery = "ALTER TABLE ".$this->objDB->encloseTableName(_dbprefix_."workflows")."
                              ADD ".$this->objDB->encloseColumnName("workflows_text2")." ".$this->objDB->getDatatype("text")." NULL DEFAULT NULL ";
            if(!$this->objDB->_query($strQuery))
                $strReturn .= "An error occurred! ...\n";
        }


        $strReturn .= "Updating module-versions...\n";
        $this->updateModuleVersion($this->objMetadata->getStrTitle(), "4.0.1");
        return $strReturn;
    }

    private function update_43_431() {
        $strReturn = "Updating 4.3 to 4.3.1...\n";

        $strReturn .= "Adding trigger-authkey...\n";
        $this->registerConstant("_workflows_trigger_authkey_", generateSystemid(), class_module_system_setting::$int_TYPE_STRING, _workflows_module_id_);

        $strReturn .= "Updating workflows module definition...\n";
        $objModule = class_module_system_module::getModuleByName("workflows");
        $objModule->setStrNamePortal("class_module_workflows_portal.php");
        $objModule->updateObjectToDb();


        $strReturn .= "Adding indices to tables..\n";
        $this->objDB->_pQuery("ALTER TABLE ".$this->objDB->encloseTableName(_dbprefix_."workflows")." ADD INDEX ( ".$this->objDB->encloseColumnName("workflows_state")." ) ", array());
        $this->objDB->_pQuery("ALTER TABLE ".$this->objDB->encloseTableName(_dbprefix_."workflows")." ADD INDEX ( ".$this->objDB->encloseColumnName("workflows_systemid")." ) ", array());


        $strReturn .= "Updating module-versions...\n";
        $this->updateModuleVersion($this->objMetadata->getStrTitle(), "4.3.1");
        return $strReturn;
    }

    private function update_45_451() {
        $strReturn = "Updating 4.5 to 4.5.1...\n";

        $strReturn .= "Altering workflows table...\n";

        $strQuery = "ALTER TABLE ".$this->objDB->encloseTableName(_dbprefix_."workflows")."
                          ADD ".$this->objDB->encloseColumnName("workflows_text3")." ".$this->objDB->getDatatype("text")." NULL DEFAULT NULL ";
        if(!$this->objDB->_query($strQuery))
            $strReturn .= "An error occurred! ...\n";


        $strReturn .= "Updating module-versions...\n";
        $this->updateModuleVersion($this->objMetadata->getStrTitle(), "4.5.1");
        return $strReturn;
    }

    private function update_47_475() {
        $strReturn = "Updating 4.7 to 4.7.5...\n";

        $strReturn .= "Removing messagesummary login-listeners...\n";

        $objFilesystem = new class_filesystem();
        if(is_file(_realpath_."/core/module_workflows/system/class_module_messagesummary_firstloginlistener.php")) {
            $objFilesystem->fileDelete("/core/module_workflows/system/class_module_messagesummary_firstloginlistener.php");
        }

        if(is_file(_realpath_."/project/system/class_module_messagesummary_firstloginlistener.php")) {
            $objFilesystem->fileDelete("/project/system/class_module_messagesummary_firstloginlistener.php");
        }

        $strReturn .= "Updating module-versions...\n";
        $this->updateModuleVersion($this->objMetadata->getStrTitle(), "4.7.5");
        return $strReturn;
    }


    private function update_475_476() {
        $strReturn = "Updating database indexes\n";

        $this->objDB->_pQuery("ALTER TABLE ".$this->objDB->encloseTableName(_dbprefix_."workflows")." ADD INDEX ( ".$this->objDB->encloseColumnName("workflows_class")." ) ", array());
        $this->objDB->_pQuery("ALTER TABLE ".$this->objDB->encloseTableName(_dbprefix_."workflows")." ADD INDEX ( ".$this->objDB->encloseColumnName("workflows_responsible")." ) ", array());


        $strReturn .= "Updating module-versions...\n";
        $this->objDB->flushQueryCache();
        $this->updateModuleVersion($this->objMetadata->getStrTitle(), "5.3.2");

        return $strReturn;
    }

}
