<?php
/*"******************************************************************************************************
*   (c) 2007-2016 by Kajona, www.kajona.de                                                              *
*       Published under the GNU LGPL v2.1, see /system/licence_lgpl.txt                                 *
********************************************************************************************************/


namespace Kajona\Packageserver\Installer;

use Kajona\Mediamanager\System\MediamanagerRepo;
use Kajona\System\System\Carrier;
use Kajona\System\System\Filesystem;
use Kajona\System\System\InstallerBase;
use Kajona\System\System\InstallerRemovableInterface;
use Kajona\System\System\Rights;
use Kajona\System\System\SystemAspect;
use Kajona\System\System\SystemModule;
use Kajona\System\System\SystemSetting;

/**
 * Installer of the packageserver module
 *
 * @moduleId _packageserver_module_id_
 */
class InstallerPackageserver extends InstallerBase implements InstallerRemovableInterface
{

    public function install()
    {

        $strReturn = "";

        $strReturn .= "Installing table packageserver_log...\n";

        $arrFields = array();
        $arrFields["log_id"] = array("char20", false);
        $arrFields["log_query"] = array("text", true);
        $arrFields["log_ip"] = array("char254", true);
        $arrFields["log_hostname"] = array("char254", true);
        $arrFields["log_date"] = array("long", true);

        if (!$this->objDB->createTable("packageserver_log", $arrFields, array("log_id"), array("log_date"), false)) {
            $strReturn .= "An error occurred! ...\n";
        }


        //register the module
        $this->registerModule("packageserver", _packageserver_module_id_, "PackageserverPortal.php", "PackageserverAdmin.php", $this->objMetadata->getStrVersion(), true);


        $strReturn .= "creating package-upload-repository...\n";
        $objFilesytem = new Filesystem();
        $objFilesytem->folderCreate("/files/packagesv4");
        $objV4Repo = new MediamanagerRepo();
        $objV4Repo->setStrPath("/files/packagesv4");
        $objV4Repo->setStrViewFilter(".zip");
        $objV4Repo->setStrUploadFilter(".zip");
        $objV4Repo->setStrTitle("Packageserver v4 packages");
        $objV4Repo->updateObjectToDb();


        $objFilesytem = new Filesystem();
        $objFilesytem->folderCreate("/files/packagesv5");
        $objV5Repo = new MediamanagerRepo();
        $objV5Repo->setStrPath("/files/packagesv5");
        $objV5Repo->setStrViewFilter(".phar");
        $objV5Repo->setStrUploadFilter(".phar");
        $objV5Repo->setStrTitle("Packageserver v5 packages");
        $objV5Repo->updateObjectToDb();

        Carrier::getInstance()->getObjRights()->addGroupToRight(SystemSetting::getConfigValue("_guests_group_id_"), $objV4Repo->getSystemid(), Rights::$STR_RIGHT_RIGHT2);


        $strReturn .= "Registering system-constants...\n";
        $this->registerConstant("_packageserver_repo_v4_id_", $objV4Repo->getSystemid(), SystemSetting::$int_TYPE_STRING, _packageserver_module_id_);
        $this->registerConstant("_packageserver_repo_v5_id_", $objV5Repo->getSystemid(), SystemSetting::$int_TYPE_STRING, _packageserver_module_id_);


        $strReturn .= "Setting aspect assignments...\n";
        if (SystemAspect::getAspectByName("content") != null) {
            $objModule = SystemModule::getModuleByName($this->objMetadata->getStrTitle());
            $objModule->setStrAspect(SystemAspect::getAspectByName("content")->getSystemid());
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
    public function isRemovable()
    {
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
    public function remove(&$strReturn)
    {

        $strReturn .= "Deleting config-entries..\n";
        SystemSetting::getConfigByName("_packageserver_repo_v4_id_")->deleteObjectFromDatabase();
        SystemSetting::getConfigByName("_packageserver_repo_v5_id_")->deleteObjectFromDatabase();

        //delete the module-node
        $strReturn .= "Deleting the module-registration...\n";
        $objModule = SystemModule::getModuleByName($this->objMetadata->getStrTitle(), true);
        if (!$objModule->deleteObjectFromDatabase()) {
            $strReturn .= "Error deleting module, aborting.\n";
            return false;
        }

        //delete the tables
        foreach (array("packageserver_log") as $strOneTable) {
            $strReturn .= "Dropping table ".$strOneTable."...\n";
            if (!$this->objDB->_pQuery("DROP TABLE ".$this->objDB->encloseTableName(_dbprefix_.$strOneTable), array())) {
                $strReturn .= "Error deleting table, aborting.\n";
                return false;
            }

        }

        return true;
    }


    public function update()
    {
        $strReturn = "";
        //check installed version and to which version we can update
        $arrModule = SystemModule::getPlainModuleData($this->objMetadata->getStrTitle(), false);

        $strReturn .= "Version found:\n\t Module: ".$arrModule["module_name"].", Version: ".$arrModule["module_version"]."\n\n";


        $arrModule = SystemModule::getPlainModuleData($this->objMetadata->getStrTitle(), false);
        if($arrModule["module_version"] == "4.7") {
            $strReturn .= $this->update_47_50();
        }
        
        $arrModule = SystemModule::getPlainModuleData($this->objMetadata->getStrTitle(), false);
        if($arrModule["module_version"] == "5.0") {
            $strReturn .= "Updating module to 5.1...\n";
            $this->updateModuleVersion($this->objMetadata->getStrTitle(), "5.1");
        }


        return $strReturn."\n\n";
    }

    private function update_47_50()
    {
        $strReturn = "Updating 4.7 to 5.0...\n";

        $strReturn .= "Updating system-constants...\n";
        //register v5 repo id
        $this->registerConstant("_packageserver_repo_v5_id_", "", SystemSetting::$int_TYPE_STRING, _packageserver_module_id_);

        //rename v4 repo id
        $objSetting = SystemSetting::getConfigByName("_packageserver_repo_id_");
        $objSetting->setStrName("_packageserver_repo_v4_id_");
        $objSetting->updateObjectToDb();


        $strReturn .= "Updating module-versions...\n";
        $this->updateModuleVersion($this->objMetadata->getStrTitle(), "5.0");
        return $strReturn;

    }

}
