<?php
/*"******************************************************************************************************
*   (c) 2004-2006 by MulchProductions, www.mulchprod.de                                                 *
*   (c) 2007-2012 by Kajona, www.kajona.de                                                              *
*       Published under the GNU LGPL v2.1, see /system/licence_lgpl.txt                                 *
*-------------------------------------------------------------------------------------------------------*
*	$Id: installer_workflows.php 4148 2011-10-27 19:47:06Z sidler $					    *
********************************************************************************************************/

/**
 * Class providing an installer for the workflows module
 *
 * @package module_workflows
 */
class class_installer_workflows extends class_installer_base implements interface_installer {

	public function __construct() {
        $this->objMetadata = new class_module_packagemanager_metadata();
        $this->objMetadata->autoInit(uniStrReplace(array(DIRECTORY_SEPARATOR."installer", _realpath_), array("", ""), __DIR__));
        $this->setArrModuleEntry("moduleId", _workflows_module_id_);
        parent::__construct();
	}

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

		if(!$this->objDB->createTable("workflows", $arrFields, array("workflows_id")))
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
            "",
            "class_module_workflows_admin.php",
            $this->objMetadata->getStrVersion(),
            true
        );

        $strReturn .= "synchronizing list...\n";
        class_module_workflows_handler::synchronizeHandlerList();


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

        $strReturn .= "Adding classes for existing records...\n";
        $strReturn .= "Workflows\n";
        $arrRows = $this->objDB->getPArray("SELECT workflows_id FROM "._dbprefix_."workflows, "._dbprefix_."system WHERE system_id = workflows_id AND (system_class IS NULL OR system_class = '')", array());
        foreach($arrRows as $arrOneRow) {
            $strQuery = "UPDATE "._dbprefix_."system SET system_class = ? where system_id = ?";
            $this->objDB->_pQuery($strQuery, array( 'class_module_workflows_workflow', $arrOneRow["system_id"] ) );
        }

        $strReturn .= "Handler\n";
        $arrRows = $this->objDB->getPArray("SELECT workflows_handler_id FROM "._dbprefix_."workflows_handler, "._dbprefix_."system WHERE system_id = workflows_handler_id AND (system_class IS NULL OR system_class = '')", array());
        foreach($arrRows as $arrOneRow) {
            $strQuery = "UPDATE "._dbprefix_."system SET system_class = ? where system_id = ?";
            $this->objDB->_pQuery($strQuery, array( 'class_module_workflows_handler', $arrOneRow["system_id"] ) );
        }


        $strReturn .= "Updating module-versions...\n";
        $this->updateModuleVersion($this->arrModule["name"], "3.4.9");
        return $strReturn;
    }
}
