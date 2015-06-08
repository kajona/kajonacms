<?php
/*"******************************************************************************************************
*   (c) 2004-2006 by MulchProductions, www.mulchprod.de                                                 *
*   (c) 2007-2015 by Kajona, www.kajona.de                                                              *
*       Published under the GNU LGPL v2.1, see /system/licence_lgpl.txt                                 *
*-------------------------------------------------------------------------------------------------------*
*	$Id$                                            *
********************************************************************************************************/

/**
 * Class providing an installer for the monita module
 *
 * @package module_ldap
 * @author sidler@mulchprod.de
 * @moduleId _ldap_module_id_
 */
class class_installer_ldap extends class_installer_base implements interface_installer_removable {

    public function install() {
		$strReturn = "";

        $strReturn .= "Installing table group_ldap...\n";
		$arrFields = array();
        $arrFields["group_ldap_id"]                                     = array("char20", false);
		$arrFields["group_ldap_dn"]                                     = array("text", true);

		if(!$this->objDB->createTable("user_group_ldap", $arrFields, array("group_ldap_id")))
			$strReturn .= "An error occurred! ...\n";
        
        $strReturn .= "Installing table user_ldap...\n";
		$arrFields = array();
        $arrFields["user_ldap_id"]                                     = array("char20", false);
		$arrFields["user_ldap_email"]                                  = array("char254", true);
		$arrFields["user_ldap_familyname"]                             = array("char254", true);
		$arrFields["user_ldap_givenname"]                              = array("char254", true);
		$arrFields["user_ldap_dn"]                                     = array("text", true);

		if(!$this->objDB->createTable("user_ldap", $arrFields, array("user_ldap_id")))
			$strReturn .= "An error occurred! ...\n";


		//register the module
		$this->registerModule("ldap", _ldap_module_id_, "", "", $this->objMetadata->getStrVersion(), false);
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
        return uniStrpos(class_config::getInstance()->getConfig("loginproviders"), "ldap") === false;
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

        //remove the workflow
        if(class_module_system_module::getModuleByName("workflows") !== null) {
            foreach(class_module_workflows_workflow::getWorkflowsForClass("class_workflow_ldap_sync") as $objOneWorkflow) {
                if(!$objOneWorkflow->deleteObjectFromDatabase()) {
                    $strReturn .= "Error deleting workflow, aborting.\n";
                    return false;
                }
            }

            $objHandler = class_module_workflows_handler::getHandlerByClass("class_workflow_ldap_sync");
            if(!$objHandler->deleteObjectFromDatabase()) {
                $strReturn .= "Error deleting workflow handler, aborting.\n";
                return false;
            }
        }

        //fetch associated users
        foreach($this->objDB->getPArray("SELECT * FROM "._dbprefix_."user_ldap", array()) as $arrOneRow) {
            $objOneUser = new class_module_user_user($arrOneRow["user_ldap_id"]);
            echo "Deleting ldap user ".$objOneUser->getStrDisplayName()."...\n";
            $objOneUser->deleteObjectFromDatabase();
        }

        //fetch associated groups
        foreach($this->objDB->getPArray("SELECT * FROM "._dbprefix_."user_group_ldap", array()) as $arrOneRow) {
            $objOneUser = new class_module_user_group($arrOneRow["group_ldap_id"]);
            echo "Deleting ldap group ".$objOneUser->getStrDisplayName()."...\n";
            $objOneUser->deleteObjectFromDatabase();
        }

        //delete the module-node
        $strReturn .= "Deleting the module-registration...\n";
        $objModule = class_module_system_module::getModuleByName($this->objMetadata->getStrTitle(), true);
        if(!$objModule->deleteObjectFromDatabase()) {
            $strReturn .= "Error deleting module, aborting.\n";
            return false;
        }

        //delete the tables
        foreach(array("user_group_ldap", "user_ldap") as $strOneTable) {
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
            $strReturn .= "Updating 3.4.2 to 3.4.9...\n";
            $strReturn .= "Updating module-versions...\n";
            $this->updateModuleVersion("ldap", "3.4.9");
        }

        $arrModule = class_module_system_module::getPlainModuleData($this->objMetadata->getStrTitle(), false);
        if($arrModule["module_version"] == "3.4.9") {
            $strReturn .= "Updating 3.4.9 to 4.0...\n";
            $strReturn .= "Updating module-versions...\n";
            $this->updateModuleVersion("ldap", "4.0");
        }

        $arrModule = class_module_system_module::getPlainModuleData($this->objMetadata->getStrTitle(), false);
        if($arrModule["module_version"] == "4.0") {
            $strReturn .= "Updating 4.0 to 4.1...\n";
            $strReturn .= "Updating module-versions...\n";
            $this->updateModuleVersion("ldap", "4.1");
        }

        $arrModule = class_module_system_module::getPlainModuleData($this->objMetadata->getStrTitle(), false);
        if($arrModule["module_version"] == "4.1") {
            $strReturn .= "Updating 4.1 to 4.2...\n";
            $strReturn .= "Updating module-versions...\n";
            $this->updateModuleVersion("ldap", "4.2");
        }

        $arrModule = class_module_system_module::getPlainModuleData($this->objMetadata->getStrTitle(), false);
        if($arrModule["module_version"] == "4.2") {
            $strReturn .= "Updating 4.2 to 4.3...\n";
            $strReturn .= "Updating module-versions...\n";
            $this->updateModuleVersion("ldap", "4.3");
        }

        $arrModule = class_module_system_module::getPlainModuleData($this->objMetadata->getStrTitle(), false);
        if($arrModule["module_version"] == "4.3") {
            $strReturn .= "Updating 4.3 to 4.4...\n";
            $this->updateModuleVersion("ldap", "4.4");
        }

        $arrModule = class_module_system_module::getPlainModuleData($this->objMetadata->getStrTitle(), false);
        if($arrModule["module_version"] == "4.4") {
            $strReturn .= "Updating 4.4 to 4.5...\n";
            $this->updateModuleVersion("ldap", "4.5");
        }

        $arrModule = class_module_system_module::getPlainModuleData($this->objMetadata->getStrTitle(), false);
        if($arrModule["module_version"] == "4.5") {
            $strReturn .= "Updating 4.5 to 4.6...\n";
            $this->updateModuleVersion("ldap", "4.6");
        }

        $arrModule = class_module_system_module::getPlainModuleData($this->objMetadata->getStrTitle(), false);
        if($arrModule["module_version"] == "4.6") {
            $strReturn .= "Updating to 4.7...\n";
            $this->updateModuleVersion("ldap", "4.7");
        }

        return $strReturn."\n\n";
	}

}
