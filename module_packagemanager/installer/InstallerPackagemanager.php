<?php
/*"******************************************************************************************************
*   (c) 2007-2016 by Kajona, www.kajona.de                                                              *
*       Published under the GNU LGPL v2.1, see /system/licence_lgpl.txt                                 *
*-------------------------------------------------------------------------------------------------------*
*	$Id$                                          *
********************************************************************************************************/


namespace Kajona\Packagemanager\Installer;

use Kajona\Packagemanager\System\PackagemanagerTemplate;
use Kajona\System\System\InstallerBase;
use Kajona\System\System\InstallerInterface;
use Kajona\System\System\OrmSchemamanager;
use Kajona\System\System\SystemAspect;
use Kajona\System\System\SystemModule;
use Kajona\System\System\SystemSetting;

/**
 * Class providing an install for the packagemanager module
 *
 * @package module_packagemanager
 * @moduleId _packagemanager_module_id_
 */
class InstallerPackagemanager extends InstallerBase implements InstallerInterface {

    public function install() {
		$strReturn = "";
        $objManager = new OrmSchemamanager();

        $strReturn .= "Installing table templatepacks...\n";
        $objManager->createTable("Kajona\\Packagemanager\\System\\PackagemanagerTemplate");

		//register the module
		$this->registerModule(
            "packagemanager",
            _packagemanager_module_id_,
            "",
            "PackagemanagerAdmin.php",
            $this->objMetadata->getStrVersion(),
            true
        );

		$strReturn .= "Registering system-constants...\n";
        $this->registerConstant("_packagemanager_defaulttemplate_", "default", SystemSetting::$int_TYPE_STRING, _packagemanager_module_id_);

        $strReturn .= "Initial templatepack sync...\n";
        PackagemanagerTemplate::syncTemplatepacks();

        $arrPacks = PackagemanagerTemplate::getObjectListFiltered();
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
        if(SystemAspect::getAspectByName("management") != null) {
            $objModule = SystemModule::getModuleByName($this->objMetadata->getStrTitle());
            $objModule->setStrAspect(SystemAspect::getAspectByName("management")->getSystemid());
            $objModule->updateObjectToDb();
        }

		return $strReturn;

	}


    public function update() {
        $strReturn = "";
        //check installed version and to which version we can update
        $arrModule = SystemModule::getPlainModuleData($this->objMetadata->getStrTitle(), false);

        $strReturn .= "Version found:\n\t Module: ".$arrModule["module_name"].", Version: ".$arrModule["module_version"]."\n\n";

        $arrModule = SystemModule::getPlainModuleData($this->objMetadata->getStrTitle(), false);
        if($arrModule["module_version"] == "4.6") {
            $strReturn .= "Updating 4.6 to 4.7...\n";
            $this->updateModuleVersion($this->objMetadata->getStrTitle(), "4.7");
        }
        
        $arrModule = SystemModule::getPlainModuleData($this->objMetadata->getStrTitle(), false);
        if($arrModule["module_version"] == "4.7") {
            $strReturn .= "Updating to 5.0...\n";
            $this->updateModuleVersion($this->objMetadata->getStrTitle(), "5.0");
        }
        
        $arrModule = SystemModule::getPlainModuleData($this->objMetadata->getStrTitle(), false);
        if($arrModule["module_version"] == "5.0") {
            $strReturn .= "Updating to 5.0.1...\n";
            $this->updateModuleVersion($this->objMetadata->getStrTitle(), "5.0.1");
        }
        
        $arrModule = SystemModule::getPlainModuleData($this->objMetadata->getStrTitle(), false);
        if($arrModule["module_version"] == "5.0.1" || $arrModule["module_version"] == "5.0.2" || $arrModule["module_version"] == "5.0.3" || $arrModule["module_version"] == "5.0.4") {
            $strReturn .= "Updating to 5.1...\n";
            $this->updateModuleVersion($this->objMetadata->getStrTitle(), "5.1");
        }

        $arrModule = SystemModule::getPlainModuleData($this->objMetadata->getStrTitle(), false);
        if($arrModule["module_version"] == "5.1") {
            $strReturn .= "Updating to 6.2...\n";
            $this->updateModuleVersion($this->objMetadata->getStrTitle(), "6.2");
        }

        return $strReturn."\n\n";
    }


}
