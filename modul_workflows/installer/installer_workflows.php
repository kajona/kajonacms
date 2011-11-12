<?php
/*"******************************************************************************************************
*   (c) 2004-2006 by MulchProductions, www.mulchprod.de                                                 *
*   (c) 2007-2011 by Kajona, www.kajona.de                                                              *
*       Published under the GNU LGPL v2.1, see /system/licence_lgpl.txt                                 *
*-------------------------------------------------------------------------------------------------------*
*	$Id$					    *
********************************************************************************************************/

/**
 * Class providing an installer for the workflows module
 *
 * @package modul_workflows
 */
class class_installer_workflows extends class_installer_base implements interface_installer {

	public function __construct() {
        $arrModule = array();
		$arrModule["version"] 		  = "3.4.1";
		$arrModule["name"] 			  = "workflows";
		$arrModule["name_lang"] 	  = "Modul workflows";
		$arrModule["moduleId"] 		  = _workflows_modul_id_;
		parent::__construct($arrModule);
	}

	public function getNeededModules() {
	    return array("system");
	}

    public function getMinSystemVersion() {
	    return "3.3.1.8";
	}

	public function hasPostInstalls() {
        return false;
	}

   public function install() {
		$strReturn = "";
		//Tabellen anlegen

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
		$this->registerModule("workflows", _workflows_modul_id_, "", "class_modul_workflows_admin.php", $this->arrModule["version"], true);

        $strReturn .= "synchronizing list...\n";
        class_modul_workflows_handler::synchronizeHandlerList();


		return $strReturn;

	}

	public function postInstall() {
		return "";
	}

	public function update() {
	    $strReturn = "";
        //check installed version and to which version we can update
        $arrModul = $this->getModuleData($this->arrModule["name"], false);
        $strReturn .= "Version found:\n\t Module: ".$arrModul["module_name"].", Version: ".$arrModul["module_version"]."\n\n";

        $arrModul = $this->getModuleData($this->arrModule["name"], false);
        if($arrModul["module_version"] == "3.3.1") {
            $strReturn .= $this->update_331_3311();
        }

        $arrModul = $this->getModuleData($this->arrModule["name"], false);
        if($arrModul["module_version"] == "3.3.1.1") {
            $strReturn .= $this->update_3311_3312();
        }

        $arrModul = $this->getModuleData($this->arrModule["name"], false);
        if($arrModul["module_version"] == "3.3.1.2") {
            $strReturn .= $this->update_3312_3318();
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

    private function update_331_3311() {
        $strReturn = "Updating 3.3.1 to 3.3.1.1.\n";

        $strReturn .= "Altering workflows table...\n";
        $strQuery = "ALTER TABLE ".$this->objDB->encloseTableName(_dbprefix_."workflows")."
                          CHANGE ".$this->objDB->encloseColumnName("workflows_responsible")." ".$this->objDB->encloseColumnName("workflows_responsible")." ".$this->objDB->getDatatype("char254")." NULL DEFAULT NULL ";
        if(!$this->objDB->_query($strQuery))
             $strReturn .= "An error occured! ...\n";

        $strReturn .= "Updating module-versions...\n";
        $this->updateModuleVersion("workflows", "3.3.1.1");
        return $strReturn;
    }

    private function update_3311_3312() {
        $strReturn = "Updating 3.3.1.1 to 3.3.1.2.\n";

        $strReturn .= "Adding workflows_handler table...\n";
        $arrFields = array();
		$arrFields["workflows_handler_id"]     = array("char20", false);
		$arrFields["workflows_handler_class"]  = array("char254", true);
        $arrFields["workflows_handler_val1"]   = array("char254", true);
        $arrFields["workflows_handler_val2"]   = array("char254", true);
        $arrFields["workflows_handler_val3"]   = array("text", true);

		if(!$this->objDB->createTable("workflows_handler", $arrFields, array("workflows_handler_id")))
			$strReturn .= "An error occured! ...\n";

        $strReturn .= "synchronizing list...\n";
        class_modul_workflows_handler::synchronizeHandlerList();

        $strReturn .= "Updating module-versions...\n";
        $this->updateModuleVersion("workflows", "3.3.1.2");
        return $strReturn;
    }

    private function update_3312_3318() {
        $strReturn = "Updating 3.3.1.2 to 3.3.1.8...\n";
        $strReturn .= "Updating module-versions...\n";
        $this->updateModuleVersion($this->arrModule["name"], "3.3.1.8");
        return $strReturn;
    }

    private function update_3318_340() {
        $strReturn = "Updating 3.3.1.8 to 3.4.0...\n";
        $strReturn .= "Updating module-versions...\n";
        $this->updateModuleVersion($this->arrModule["name"], "3.4.0");
        return $strReturn;
    }

    private function update_340_341() {
        $strReturn = "Updating 3.4.0 to 3.4.1...\n";
        $strReturn .= "Updating module-versions...\n";
        $this->updateModuleVersion($this->arrModule["name"], "3.4.1");
        return $strReturn;
    }
}
?>