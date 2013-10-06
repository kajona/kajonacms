<?php
/*"******************************************************************************************************
*   (c) 2007-2013 by Kajona, www.kajona.de                                                              *
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


        $strReturn .= "Installing table templatepacks...\n";

        $arrFields = array();
        $arrFields["templatepack_id"] 		    = array("char20", false);
        $arrFields["templatepack_name"] 	    = array("char254", true);

        if(!$this->objDB->createTable("templatepacks", $arrFields, array("templatepack_id")))
            $strReturn .= "An error occured! ...\n";

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
                if($objOnePack->getStrName() == "default")
                    $objOnePack->setIntRecordStatus(1);
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
        $arrModul = class_module_system_module::getPlainModuleData($this->objMetadata->getStrTitle(), false);

        $strReturn .= "Version found:\n\t Module: ".$arrModul["module_name"].", Version: ".$arrModul["module_version"]."\n\n";

        $arrModul = class_module_system_module::getPlainModuleData($this->objMetadata->getStrTitle(), false);
        if($arrModul["module_version"] == "3.4.9") {
            $strReturn .= "Updating 3.4.9 to 4.0...\n";
            $strReturn .= "Updating module-versions...\n";
            $this->updateModuleVersion($this->objMetadata->getStrTitle(), "4.0");
        }

        $arrModul = class_module_system_module::getPlainModuleData($this->objMetadata->getStrTitle(), false);
        if($arrModul["module_version"] == "4.0") {
            $strReturn .= "Updating 4.0 to 4.1...\n";
            $strReturn .= "Updating module-versions...\n";
            $this->updateModuleVersion($this->objMetadata->getStrTitle(), "4.1");
        }

        $arrModul = class_module_system_module::getPlainModuleData($this->objMetadata->getStrTitle(), false);
        if($arrModul["module_version"] == "4.1") {
            $strReturn .= "Updating 4.1 to 4.1.1...\n";
            $strReturn .= "Updating module-versions...\n";
            $this->updateModuleVersion($this->objMetadata->getStrTitle(), "4.1.1");
        }

        $arrModul = class_module_system_module::getPlainModuleData($this->objMetadata->getStrTitle(), false);
        if($arrModul["module_version"] == "4.1.1") {
            $strReturn .= "Updating 4.1.1 to 4.2...\n";
            $strReturn .= "Updating module-versions...\n";
            $this->updateModuleVersion($this->objMetadata->getStrTitle(), "4.2");
        }

        $arrModul = class_module_system_module::getPlainModuleData($this->objMetadata->getStrTitle(), false);
        if($arrModul["module_version"] == "4.2") {
            $strReturn .= "Updating 4.2 to 4.3...\n";
            $strReturn .= "Updating module-versions...\n";
            $this->updateModuleVersion($this->objMetadata->getStrTitle(), "4.3");
        }

        return $strReturn."\n\n";
    }


}
