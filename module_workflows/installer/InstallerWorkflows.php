<?php
/*"******************************************************************************************************
*   (c) 2004-2006 by MulchProductions, www.mulchprod.de                                                 *
*   (c) 2007-2015 by Kajona, www.kajona.de                                                              *
*       Published under the GNU LGPL v2.1, see /system/licence_lgpl.txt                                 *
*-------------------------------------------------------------------------------------------------------*
*	$Id$					    *
********************************************************************************************************/

namespace Kajona\Workflows\Installer;

use Kajona\System\System\Filesystem;
use Kajona\System\System\InstallerBase;
use Kajona\System\System\InstallerRemovableInterface;
use Kajona\System\System\OrmSchemamanager;
use Kajona\System\System\SystemModule;
use Kajona\System\System\SystemSetting;
use Kajona\Workflows\System\WorkflowsHandler;
use Kajona\Workflows\System\WorkflowsWorkflow;


/**
 * Class providing an installer for the workflows module
 *
 * @package module_workflows
 * @moduleId _workflows_module_id_
 */
class InstallerWorkflows extends InstallerBase implements InstallerRemovableInterface {


    public function install() {
		$strReturn = "";
        $objManager = new OrmSchemamanager();
		//workflows workflow ---------------------------------------------------------------------
		$strReturn .= "Installing table workflows...\n";
        $objManager->createTable("Kajona\\Workflows\\System\\WorkflowsWorkflow");

        $strReturn .= "Installing table workflows_handler...\n";
        $objManager->createTable("Kajona\\Workflows\\System\\WorkflowsHandler");

		//register the module
		$this->registerModule(
            "workflows",
            _workflows_module_id_,
            "WorkflowsPortal.php",
            "WorkflowsAdmin.php",
            $this->objMetadata->getStrVersion(),
            true
        );

        $strReturn .= "synchronizing list...\n";
        WorkflowsHandler::synchronizeHandlerList();

        $strReturn .= "Generating and adding trigger-authkey...\n";
        $this->registerConstant("_workflows_trigger_authkey_", generateSystemid(), SystemSetting::$int_TYPE_STRING, _workflows_module_id_);

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
        if(SystemSetting::getConfigByName("_workflows_trigger_authkey_") != null)
            SystemSetting::getConfigByName("_workflows_trigger_authkey_")->deleteObjectFromDatabase();

        /** @var WorkflowsWorkflow $objOneObject */
        foreach(WorkflowsWorkflow::getObjectList() as $objOneObject) {
            $strReturn .= "Deleting object '".$objOneObject->getStrDisplayName()."' ...\n";
            if(!$objOneObject->deleteObjectFromDatabase()) {
                $strReturn .= "Error deleting object, aborting.\n";
                return false;
            }
        }

        /** @var WorkflowsHandler $objOneObject */
        foreach(WorkflowsHandler::getObjectList() as $objOneObject) {
            $strReturn .= "Deleting object '".$objOneObject->getStrDisplayName()."' ...\n";
            if(!$objOneObject->deleteObjectFromDatabase()) {
                $strReturn .= "Error deleting object, aborting.\n";
                return false;
            }
        }

        //delete the module-node
        $strReturn .= "Deleting the module-registration...\n";
        $objModule = SystemModule::getModuleByName($this->objMetadata->getStrTitle(), true);
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
        $arrModule = SystemModule::getPlainModuleData($this->objMetadata->getStrTitle(), false);
        $strReturn .= "Version found:\n\t Module: ".$arrModule["module_name"].", Version: ".$arrModule["module_version"]."\n\n";



        $arrModule = SystemModule::getPlainModuleData($this->objMetadata->getStrTitle(), false);
        if($arrModule["module_version"] == "4.6") {
            $strReturn .= "Updating to 4.7...\n";
            $this->updateModuleVersion($this->objMetadata->getStrTitle(), "4.7");
        }

        $arrModule = SystemModule::getPlainModuleData($this->objMetadata->getStrTitle(), false);
        if($arrModule["module_version"] == "4.7" || $arrModule["module_version"] == "4.7.1") {
            $strReturn .= $this->update_47_475();
        }

        $arrModule = SystemModule::getPlainModuleData($this->objMetadata->getStrTitle(), false);
        if($arrModule["module_version"] == "4.7.5") {
            $strReturn .= $this->update_475_476();
        }

        return $strReturn."\n\n";
	}


    private function update_47_475() {
        $strReturn = "Updating 4.7 to 4.7.5...\n";

        $strReturn .= "Removing messagesummary login-listeners...\n";

        $objFilesystem = new Filesystem();
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
        $this->updateModuleVersion($this->objMetadata->getStrTitle(), "4.7.6");

        return $strReturn;
    }

}
