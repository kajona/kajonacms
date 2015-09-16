<?php
/*"******************************************************************************************************
*   (c) 2007-2015 by Kajona, www.kajona.de                                                              *
*       Published under the GNU LGPL v2.1, see /system/licence_lgpl.txt                                 *
*-------------------------------------------------------------------------------------------------------*
*	$Id$                                          *
********************************************************************************************************/

/**
 * Class providing an install for the packagemanager module
 *
 * @package module_packagemanager
 * @moduleId _packagemanager_module_id_
 */
class class_installer_packagemanager extends class_installer_base implements interface_installer {

    public function install() {
		$strReturn = "";
        $objManager = new class_orm_schemamanager();

        $strReturn .= "Installing table templatepacks...\n";
        $objManager->createTable("class_module_packagemanager_template");

		//register the module
		$this->registerModule(
            "packagemanager",
            _packagemanager_module_id_,
            "",
            "class_module_packagemanager_admin.php",
            $this->objMetadata->getStrVersion(),
            true
        );

		$strReturn .= "Registering system-constants...\n";
        $this->registerConstant("_packagemanager_defaulttemplate_", "default", class_module_system_setting::$int_TYPE_STRING, _packagemanager_module_id_);

        $strReturn .= "Initial templatepack sync...\n";
        class_module_packagemanager_template::syncTemplatepacks();

        $arrPacks = class_module_packagemanager_template::getObjectList();
        if(count($arrPacks) > 0) {
            //search the default package
            foreach($arrPacks as $objOnePack) {
                if($objOnePack->getStrName() == "default") {
                    $objOnePack->setIntRecordStatus(1);
                    $objOnePack->updateObjectToDb();
                }
            }
        }

        $strReturn .= "Setting aspect assignments...\n";
        if(class_module_system_aspect::getAspectByName("management") != null) {
            $objModule = class_module_system_module::getModuleByName($this->objMetadata->getStrTitle());
            $objModule->setStrAspect(class_module_system_aspect::getAspectByName("management")->getSystemid());
            $objModule->updateObjectToDb();
        }

		return $strReturn;

	}


    public function update() {
        $strReturn = "";
        //check installed version and to which version we can update
        $arrModule = class_module_system_module::getPlainModuleData($this->objMetadata->getStrTitle(), false);

        $strReturn .= "Version found:\n\t Module: ".$arrModule["module_name"].", Version: ".$arrModule["module_version"]."\n\n";


        $arrModule = class_module_system_module::getPlainModuleData($this->objMetadata->getStrTitle(), false);
        if($arrModule["module_version"] == "4.0") {
            $strReturn .= "Updating 4.0 to 4.1...\n";
            $this->updateModuleVersion($this->objMetadata->getStrTitle(), "4.1");
        }

        $arrModule = class_module_system_module::getPlainModuleData($this->objMetadata->getStrTitle(), false);
        if($arrModule["module_version"] == "4.1") {
            $strReturn .= "Updating 4.1 to 4.1.1...\n";
            $this->updateModuleVersion($this->objMetadata->getStrTitle(), "4.1.1");
        }

        $arrModule = class_module_system_module::getPlainModuleData($this->objMetadata->getStrTitle(), false);
        if($arrModule["module_version"] == "4.1.1") {
            $strReturn .= "Updating 4.1.1 to 4.2...\n";
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
            $strReturn .= "Updating 4.4 to 4.4.1...\n";
            $this->updateModuleVersion($this->objMetadata->getStrTitle(), "4.4.1");
        }

        $arrModule = class_module_system_module::getPlainModuleData($this->objMetadata->getStrTitle(), false);
        if($arrModule["module_version"] == "4.4.1") {
            $strReturn .= "Updating 4.4.1 to 4.5...\n";
            $this->updateModuleVersion($this->objMetadata->getStrTitle(), "4.5");
        }

        $arrModule = class_module_system_module::getPlainModuleData($this->objMetadata->getStrTitle(), false);
        if($arrModule["module_version"] == "4.5") {
            $strReturn .= "Updating 4.5 to 4.6...\n";
            $this->updateModuleVersion($this->objMetadata->getStrTitle(), "4.6");
        }

        $arrModule = class_module_system_module::getPlainModuleData($this->objMetadata->getStrTitle(), false);
        if($arrModule["module_version"] == "4.6") {
            $strReturn .= "Updating 4.6 to 4.7...\n";
            $this->updateModuleVersion($this->objMetadata->getStrTitle(), "4.7");
        }

        return $strReturn."\n\n";
    }


}
