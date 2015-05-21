<?php
/*"******************************************************************************************************
*   (c) 2007-2015 by Kajona, www.kajona.de                                                              *
*       Published under the GNU LGPL v2.1, see /system/licence_lgpl.txt                                 *
********************************************************************************************************/

/**
 * Installer of the packageserver module
 *
 * @package module_packageserver
 * @moduleId _packageserver_module_id_
 */
class class_installer_packageserver extends class_installer_base implements interface_installer_removable {

    public function install() {

        $strReturn = "";

        $strReturn .= "Installing table packageserver_log...\n";

        $arrFields = array();
        $arrFields["log_id"] = array("char20", false);
        $arrFields["log_query"] = array("text", true);
        $arrFields["log_ip"] = array("char254", true);
        $arrFields["log_hostname"] = array("char254", true);
        $arrFields["log_date"] = array("long", true);

        if(!$this->objDB->createTable("packageserver_log", $arrFields, array("log_id"), array("log_date"), false))
            $strReturn .= "An error occurred! ...\n";


        //register the module
        $this->registerModule("packageserver", _packageserver_module_id_, "class_module_packageserver_portal.php", "class_module_packageserver_admin.php", $this->objMetadata->getStrVersion(), true);


        $strReturn .= "creating package-upload-repository...\n";
        $objFilesytem = new class_filesystem();
        $objFilesytem->folderCreate("/files/packages");
        $objRepo = new class_module_mediamanager_repo();
        $objRepo->setStrPath("/files/packages");
        $objRepo->setStrViewFilter(".zip");
        $objRepo->setStrUploadFilter(".zip");
        $objRepo->setStrTitle("Packageserver packages");
        $objRepo->updateObjectToDb();

        class_carrier::getInstance()->getObjRights()->addGroupToRight(class_module_system_setting::getConfigValue("_guests_group_id_"), $objRepo->getSystemid(), class_rights::$STR_RIGHT_RIGHT2);


        $strReturn .= "Registering system-constants...\n";
        $this->registerConstant("_packageserver_repo_id_", "", class_module_system_setting::$int_TYPE_STRING, _packageserver_module_id_);


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

        $strReturn .= "Deleting config-entries..\n";
        class_module_system_setting::getConfigByName("_packageserver_repo_id_")->deleteObject();

        //delete the module-node
        $strReturn .= "Deleting the module-registration...\n";
        $objModule = class_module_system_module::getModuleByName($this->objMetadata->getStrTitle(), true);
        if(!$objModule->deleteObject()) {
            $strReturn .= "Error deleting module, aborting.\n";
            return false;
        }

        //delete the tables
        foreach(array("packageserver_log") as $strOneTable) {
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
        if($arrModule["module_version"] == "3.4.9") {
            $strReturn .= "Updating 3.4.9 to 4.0...\n";
            $this->updateModuleVersion($this->objMetadata->getStrTitle(), "4.0");
        }

        $arrModule = class_module_system_module::getPlainModuleData($this->objMetadata->getStrTitle(), false);
        if($arrModule["module_version"] == "4.0") {
            $strReturn .= "Updating 4.0 to 4.1...\n";
            $this->updateModuleVersion($this->objMetadata->getStrTitle(), "4.1");
        }

        $arrModule = class_module_system_module::getPlainModuleData($this->objMetadata->getStrTitle(), false);
        if($arrModule["module_version"] == "4.1") {
            $strReturn .= "Updating 4.1 to 4.2...\n";
            $this->updateModuleVersion($this->objMetadata->getStrTitle(), "4.2");
        }

        $arrModule = class_module_system_module::getPlainModuleData($this->objMetadata->getStrTitle(), false);
        if($arrModule["module_version"] == "4.2") {
            $strReturn .= "Updating 4.2 to 4.3...\n";
            $this->updateModuleVersion($this->objMetadata->getStrTitle(), "4.3");
        }

        $arrModule = class_module_system_module::getPlainModuleData($this->objMetadata->getStrTitle(), false);
        if($arrModule["module_version"] == "4.3") {
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
            $strReturn .= "Updating 4.5 to 4.6...\n";
            $this->updateModuleVersion($this->objMetadata->getStrTitle(), "4.6");
        }

        $arrModule = class_module_system_module::getPlainModuleData($this->objMetadata->getStrTitle(), false);
        if($arrModule["module_version"] == "4.6") {
            $strReturn .= "Updating to 4.7...\n";
            $this->updateModuleVersion($this->objMetadata->getStrTitle(), "4.7");
        }

        return $strReturn."\n\n";
    }

}
