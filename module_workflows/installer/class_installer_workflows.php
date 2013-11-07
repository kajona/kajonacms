<?php
/*"******************************************************************************************************
*   (c) 2004-2006 by MulchProductions, www.mulchprod.de                                                 *
*   (c) 2007-2013 by Kajona, www.kajona.de                                                              *
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
class class_installer_workflows extends class_installer_base implements interface_installer {

    private $bitUpdatingFrom3421 = false;

    public function install() {
		$strReturn = "";

		//workflows workflow ---------------------------------------------------------------------
		$strReturn .= "Installing table workflows...\n";

		$arrFields = array();
		$arrFields["workflows_id"]             = array("char20", false);
		$arrFields["workflows_state"]          = array("int", true);
        $arrFields["workflows_runs"]           = array("int", true);
		$arrFields["workflows_class"]          = array("char254", true);
		$arrFields["workflows_systemid"]       = array("char20", true);
		$arrFields["workflows_responsible"]    = array("char254", true);
		$arrFields["workflows_int1"]           = array("int1", true);
		$arrFields["workflows_int2"]           = array("int2", true);
		$arrFields["workflows_char1"]          = array("char254", true);
		$arrFields["workflows_char2"]          = array("char254", true);
		$arrFields["workflows_date1"]          = array("long", true);
		$arrFields["workflows_date2"]          = array("long", true);
		$arrFields["workflows_text"]           = array("text", true);
		$arrFields["workflows_text2"]          = array("text", true);

		if(!$this->objDB->createTable("workflows", $arrFields, array("workflows_id"), array("workflows_state", "workflows_systemid")))
			$strReturn .= "An error occured! ...\n";

        $strReturn .= "Installing table workflows_handler...\n";

		$arrFields = array();
		$arrFields["workflows_handler_id"]     = array("char20", false);
		$arrFields["workflows_handler_class"]  = array("char254", true);
        $arrFields["workflows_handler_val1"]   = array("char254", true);
        $arrFields["workflows_handler_val2"]   = array("char254", true);
        $arrFields["workflows_handler_val3"]   = array("text", true);

		if(!$this->objDB->createTable("workflows_handler", $arrFields, array("workflows_handler_id")))
			$strReturn .= "An error occured! ...\n";

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

	public function update() {
	    $strReturn = "";
        //check installed version and to which version we can update
        $arrModul = class_module_system_module::getPlainModuleData($this->objMetadata->getStrTitle(), false);
        $strReturn .= "Version found:\n\t Module: ".$arrModul["module_name"].", Version: ".$arrModul["module_version"]."\n\n";

        $arrModul = class_module_system_module::getPlainModuleData($this->objMetadata->getStrTitle(), false);
        if($arrModul["module_version"] == "3.4.2" || $arrModul["module_version"] == "3.4.2.1") {

            if($arrModul["module_version"] == "3.4.2.1")
                $this->bitUpdatingFrom3421 = true;

            $strReturn .= $this->update_342_349();
        }

        $arrModul = class_module_system_module::getPlainModuleData($this->objMetadata->getStrTitle(), false);
        if($arrModul["module_version"] == "3.4.9") {
            $strReturn .= $this->update_349_40();
        }

        $arrModul = class_module_system_module::getPlainModuleData($this->objMetadata->getStrTitle(), false);
        if($arrModul["module_version"] == "4.0") {
            $strReturn .= $this->update_40_401();
        }

        $arrModul = class_module_system_module::getPlainModuleData($this->objMetadata->getStrTitle(), false);
        if($arrModul["module_version"] == "4.0.1") {
            $strReturn .= "Updating 4.0.1 to 4.1...\n";
            $strReturn .= "Updating module-versions...\n";
            $this->updateModuleVersion($this->objMetadata->getStrTitle(), "4.1");
        }

        $arrModul = class_module_system_module::getPlainModuleData($this->objMetadata->getStrTitle(), false);
        if($arrModul["module_version"] == "4.1") {
            $strReturn .= "Updating 4.1 to 4.2...\n";
            $strReturn .= "Updating module-versions...\n";
            $this->updateModuleVersion($this->objMetadata->getStrTitle(), "4.2");
        }


        $arrModul = class_module_system_module::getPlainModuleData($this->objMetadata->getStrTitle(), false);
        if($arrModul["module_version"] == "4.2") {
            $strReturn .= "Updating 4.2 to 4.3...\n";
            $strReturn .= "Updating module-versions...\n";
            $this->updateModuleVersion($this->objMetadata->getStrTitle(), "4.3");
        }

        $arrModul = class_module_system_module::getPlainModuleData($this->objMetadata->getStrTitle(), false);
        if($arrModul["module_version"] == "4.3") {
            $strReturn .= $this->update_43_431();
        }

        return $strReturn."\n\n";
	}

    private function update_342_349() {
        $strReturn = "Updating 3.4.2 to 3.4.9...\n";

        $strReturn .= "Adding classes for existing records...\n";
        $strReturn .= "Workflows\n";
        $arrRows = $this->objDB->getPArray("SELECT workflows_id FROM "._dbprefix_."workflows, "._dbprefix_."system WHERE system_id = workflows_id AND (system_class IS NULL OR system_class = '')", array());
        foreach($arrRows as $arrOneRow) {
            $strQuery = "UPDATE "._dbprefix_."system SET system_class = ? where system_id = ?";
            $this->objDB->_pQuery($strQuery, array( 'class_module_workflows_workflow', $arrOneRow["workflows_id"] ) );
        }

        $strReturn .= "Handler\n";
        $arrRows = $this->objDB->getPArray("SELECT workflows_handler_id FROM "._dbprefix_."workflows_handler, "._dbprefix_."system WHERE system_id = workflows_handler_id AND (system_class IS NULL OR system_class = '')", array());
        foreach($arrRows as $arrOneRow) {
            $strQuery = "UPDATE "._dbprefix_."system SET system_class = ? where system_id = ?";
            $this->objDB->_pQuery($strQuery, array( 'class_module_workflows_handler', $arrOneRow["workflows_handler_id"] ) );
        }


        $strReturn .= "Updating module-versions...\n";
        $this->updateModuleVersion($this->objMetadata->getStrTitle(), "3.4.9");
        return $strReturn;
    }

    private function update_349_40() {
        $strReturn = "Updating 3.4.9 to 4.0...\n";
        $strReturn .= "Updating module-versions...\n";
        $this->updateModuleVersion($this->objMetadata->getStrTitle(), "4.0");
        return $strReturn;
    }

    private function update_40_401() {
        $strReturn = "Updating 4.0 to 4.0.1...\n";

        if(!$this->bitUpdatingFrom3421) {
            $strReturn .= "Altering workflows table...\n";

            $strQuery = "ALTER TABLE ".$this->objDB->encloseTableName(_dbprefix_."workflows")."
                              ADD ".$this->objDB->encloseColumnName("workflows_text2")." ".$this->objDB->getDatatype("text")." NULL DEFAULT NULL ";
            if(!$this->objDB->_query($strQuery))
                $strReturn .= "An error occured! ...\n";
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


}
