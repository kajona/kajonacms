<?php
/*"******************************************************************************************************
*   (c) 2004-2006 by MulchProductions, www.mulchprod.de                                                 *
*   (c) 2007-2015 by Kajona, www.kajona.de                                                              *
*       Published under the GNU LGPL v2.1, see /system/licence_lgpl.txt                                 *
*-------------------------------------------------------------------------------------------------------*
*	$Id$                                         *
********************************************************************************************************/

namespace Kajona\Stats\Installer;

use Kajona\System\System\Filesystem;
use Kajona\System\System\Gzip;
use Kajona\System\System\InstallerBase;
use Kajona\System\System\InstallerRemovableInterface;
use Kajona\System\System\Resourceloader;
use Kajona\System\System\SystemAspect;
use Kajona\System\System\SystemModule;
use Kajona\System\System\SystemSetting;


/**
 * Installer handling the installation of the stats module
 *
 * @package module_stats
 * @moduleId _stats_modul_id_
 */
class InstallerStats extends InstallerBase implements InstallerRemovableInterface
{

    public function install()
    {
        $strReturn = "";

        //Stats table -----------------------------------------------------------------------------------
        $strReturn .= "Installing table stats...\n";

        $arrFields = array();
        $arrFields["stats_id"] = array("char20", false);
        $arrFields["stats_ip"] = array("char20", true);
        $arrFields["stats_hostname"] = array("char254", true);
        $arrFields["stats_date"] = array("int", true);
        $arrFields["stats_page"] = array("char254", true);
        $arrFields["stats_language"] = array("char10", true);
        $arrFields["stats_referer"] = array("char254", true);
        $arrFields["stats_browser"] = array("char254", true);
        $arrFields["stats_session"] = array("char100", true);

        if (!$this->objDB->createTable("stats_data", $arrFields, array("stats_id"), array("stats_date", "stats_hostname", "stats_page", "stats_referer", "stats_browser"), false)) {
            $strReturn .= "An error occurred! ...\n";
        }

        $strReturn .= "Installing table ip2country...\n";

        $arrFields = array();
        $arrFields["ip2c_ip"] = array("char20", false);
        $arrFields["ip2c_name"] = array("char100", false);

        if (!$this->objDB->createTable("stats_ip2country", $arrFields, array("ip2c_ip"), array(), false)) {
            $strReturn .= "An error occurred! ...\n";
        }


        //register module
        $this->registerModule(
            "stats",
            _stats_modul_id_,
            "",
            "StatsAdmin.php",
            $this->objMetadata->getStrVersion(),
            true,
            "",
            "StatsAdminXml.php"
        );

        $strReturn .= "Registering system-constants...\n";
        //Number of rows in the login-log
        $this->registerConstant("_stats_duration_online_", "300", SystemSetting::$int_TYPE_INT, _stats_modul_id_);
        $this->registerConstant("_stats_exclusionlist_", _webpath_, SystemSetting::$int_TYPE_STRING, _stats_modul_id_);


        $strReturn .= "Setting aspect assignments...\n";
        if (SystemAspect::getAspectByName("management") != null) {
            $objModule = SystemModule::getModuleByName($this->objMetadata->getStrTitle());
            $objModule->setStrAspect(SystemAspect::getAspectByName("management")->getSystemid());
            $objModule->updateObjectToDb();
        }

        $strReturn .= $this->extractBrowscap();

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

        $strReturn .= "Deleting settings...\n";
        foreach (array("_stats_duration_online_", "_stats_exclusionlist_") as $strOneSetting) {
            if (SystemSetting::getConfigByName($strOneSetting) !== null) {
                SystemSetting::getConfigByName($strOneSetting)->deleteObjectFromDatabase();
            }
        }

        //delete the module-node
        $strReturn .= "Deleting the module-registration...\n";
        $objModule = SystemModule::getModuleByName($this->objMetadata->getStrTitle(), true);
        if (!$objModule->deleteObjectFromDatabase()) {
            $strReturn .= "Error deleting module, aborting.\n";
            return false;
        }

        //delete the tables
        foreach (array("stats_data", "stats_ip2country") as $strOneTable) {
            $strReturn .= "Dropping table ".$strOneTable."...\n";
            if (!$this->objDB->_pQuery("DROP TABLE ".$this->objDB->encloseTableName(_dbprefix_.$strOneTable)."", array())) {
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

        $arrModul = SystemModule::getPlainModuleData($this->objMetadata->getStrTitle(), false);
        if ($arrModul["module_version"] == "4.6") {
            $strReturn .= "Updating to 4.7...\n";
            $this->updateModuleVersion("stats", "4.7");
        }

        $arrModule = SystemModule::getPlainModuleData($this->objMetadata->getStrTitle(), false);
        if ($arrModule["module_version"] == "4.7" || $arrModule["module_version"] == "4.7.1") {
            $strReturn .= $this->update_47_475();
        }

        $strReturn .= $this->extractBrowscap();
        return $strReturn."\n\n";
    }


    private function extractBrowscap()
    {
        $strReturn = "";
        if (!is_file(_realpath_._projectpath_."/temp/browscap.cache.php")) {
            $objZip = new Gzip();
            $objFile = new Filesystem();
            $objFile->fileCopy(Resourceloader::getInstance()->getAbsolutePathForModule("module_stats")."/installer/browscap.cache.php.gz", _projectpath_."/temp/browscap.cache.php.gz");
            if (is_file(_realpath_._projectpath_."/temp/browscap.cache.php.gz")) {
                $objZip->decompressFile(_projectpath_."/temp/browscap.cache.php.gz");
                $objFile->fileDelete(_projectpath_."/temp/browscap.cache.php.gz");

                touch(_realpath_._projectpath_."/temp/browscap.ini");
            }
            else {
                $strReturn .= "Failed to copy the browscap file to the project folder\n";
            }
        }
        else {
            $strReturn .= "Browscap cache file already existing\n";
        }

        return $strReturn;
    }


    private function update_47_475()
    {
        $strReturn = "Updating 4.7 to 4.7.5...\n";

        $strReturn .= "Removing setting _stats_nrofrecords_\n";
        SystemSetting::getConfigByName("_stats_nrofrecords_")->deleteObjectFromDatabase();

        $strReturn .= "Updating module-versions...\n";
        $this->updateModuleVersion("stats", "4.6.1");
        return $strReturn;
    }


}
