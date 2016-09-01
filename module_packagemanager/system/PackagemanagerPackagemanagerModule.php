<?php
/*"******************************************************************************************************
*   (c) 2007-2016 by Kajona, www.kajona.de                                                              *
*       Published under the GNU LGPL v2.1, see /system/licence_lgpl.txt                                 *
*-------------------------------------------------------------------------------------------------------*
*	$Id$                                  *
********************************************************************************************************/

namespace Kajona\Packagemanager\System;

use Kajona\System\System\CacheManager;
use Kajona\System\System\Carrier;
use Kajona\System\System\Classloader;
use Kajona\System\System\CoreEventdispatcher;
use Kajona\System\System\Exception;
use Kajona\System\System\Filesystem;
use Kajona\System\System\InstallerInterface;
use Kajona\System\System\InstallerRemovableInterface;
use Kajona\System\System\Logger;
use Kajona\System\System\SystemModule;
use Kajona\System\System\SystemSetting;


/**
 * Implementation to handle module-packages. List all installed module-packages and starts the installation / update.
 *
 * @package module_packagemanager
 * @author sidler@mulchprod.de
 * @since 4.0
 */
class PackagemanagerPackagemanagerModule implements PackagemanagerPackagemanagerInterface
{

    /**
     * @var PackagemanagerMetadata
     */
    protected $objMetadata;


    /**
     * Returns a list of installed packages, so a single metadata-entry
     * for each package.
     *
     * @return PackagemanagerMetadata[]
     */
    public function getInstalledPackages()
    {
        $arrReturn = array();

        //loop all modules
        $arrModules = Classloader::getInstance()->getArrModules();

        foreach ($arrModules as $strPath => $strOneModule) {
            try {
                $objMetadata = new PackagemanagerMetadata();
                $objMetadata->autoInit("/".$strPath);
                $arrReturn[] = $objMetadata;
            }
            catch (Exception $objEx) {

            }
        }

        return $arrReturn;
    }


    /**
     * Copies the extracted(!) package from the temp-folder
     * to the target-folder.
     * In most cases, this is either located at /core or at /templates.
     * The original should be deleted afterwards.
     *
     * @throws Exception
     * @return void
     */
    public function move2Filesystem()
    {
        $strSource = $this->objMetadata->getStrPath();

        if (!is_dir(_realpath_.$strSource)) {
            throw new Exception("current package ".$strSource." is not a folder.", Exception::$level_ERROR);
        }

        Logger::getInstance(Logger::PACKAGEMANAGEMENT)->addLogRow("moving ".$strSource." to ".$this->getStrTargetPath(), Logger::$levelInfo);

        $objFilesystem = new Filesystem();
        //set a chmod before copying the files - at least try to
        $objFilesystem->chmod($this->getStrTargetPath(), 0777);

        $objFilesystem->folderCopyRecursive($strSource, $this->getStrTargetPath(), true);
        $this->objMetadata->setStrPath($this->getStrTargetPath());

        //reset chmod after copying the files
        $objFilesystem->chmod($this->getStrTargetPath());

        $objFilesystem->folderDeleteRecursive($strSource);

        //shift the cache buster
        $objSetting = SystemSetting::getConfigByName("_system_browser_cachebuster_");
        if ($objSetting != null) {
            $objSetting->setStrValue((int)$objSetting->getStrValue() + 1);
            $objSetting->updateObjectToDb();
        }
    }


    /**
     * Invokes the installer, if given.
     * The installer itself is capable of detecting whether an update or a plain installation is required.
     *
     * @throws Exception
     * @return string
     */
    public function installOrUpdate()
    {
        $strReturn = "";

        if(!$this->getObjMetadata()->getBitProvidesInstaller()) {
            CoreEventdispatcher::getInstance()->notifyGenericListeners(PackagemanagerEventidentifier::EVENT_PACKAGEMANAGER_PACKAGEUPDATED, array($this));
            return "";
        }

        if (uniStrpos($this->getObjMetadata()->getStrPath(), "core") === false) {
            throw new Exception("Current module not located in a core directory.", Exception::$level_ERROR);
        }

        if (!$this->isInstallable()) {
            throw new Exception("Current module isn't installable, not all requirements are given", Exception::$level_ERROR);
        }

        //search for an existing installer
        $arrInstaller = $this->getInstaller($this->getObjMetadata());

        //start with modules
        foreach ($arrInstaller as $objInstance) {

            if (!$objInstance instanceof \Kajona\System\System\InstallerBase) {
                continue;
            }

            //skip element installers at first run
            Logger::getInstance(Logger::PACKAGEMANAGEMENT)->addLogRow("triggering updateOrInstall() on installer ".get_class($objInstance).", all requirements given", Logger::$levelInfo);
            //trigger update or install
            $strReturn .= $objInstance->installOrUpdate();
        }

        /** @var CacheManager $objCache */
        $objCache = Carrier::getInstance()->getContainer()->offsetGet(\Kajona\System\System\ServiceProvider::STR_CACHE_MANAGER);
        $objCache->flushCache();
        CoreEventdispatcher::getInstance()->notifyGenericListeners(PackagemanagerEventidentifier::EVENT_PACKAGEMANAGER_PACKAGEUPDATED, array($this));

        return $strReturn;
    }

    /**
     * @param PackagemanagerMetadata $objMetadata
     *
     * @return void
     */
    public function setObjMetadata($objMetadata)
    {
        $this->objMetadata = $objMetadata;
    }

    /**
     * @return PackagemanagerMetadata
     */
    public function getObjMetadata()
    {
        return $this->objMetadata;
    }

    /**
     * Validates, whether the current package is installable or not.
     * In nearly all cases
     *
     * @return bool
     */
    public function isInstallable()
    {

        if (!$this->getObjMetadata()->getBitProvidesInstaller()) {
            return false;
        }

        //check if required modules are given in matching versions
        $arrRequiredModules = $this->objMetadata->getArrRequiredModules();
        foreach ($arrRequiredModules as $strOneModule => $strMinVersion) {

            if (trim($strOneModule) != "") {
                $objModule = SystemModule::getModuleByName(trim($strOneModule));
                if ($objModule === null) {

                    $arrModules = Classloader::getInstance()->getArrModules();
                    $objMetadata = null;
                    foreach ($arrModules as $strPath => $strOneFolder) {
                        if (uniStrpos($strOneFolder, $strOneModule) !== false) {
                            $objMetadata = new PackagemanagerMetadata();
                            $objMetadata->autoInit("/".$strPath);

                            //but: if the package provides an installer and was not resolved by the previous calls,
                            //we shouldn't include it here
                            if ($objMetadata->getBitProvidesInstaller()) {
                                $objMetadata = null;
                            }
                        }

                    }

                    //no package found
                    if ($objMetadata === null) {
                        return false;
                    }

                    //package found, but wrong version
                    if (version_compare($strMinVersion, $objMetadata->getStrVersion(), ">")) {
                        return false;
                    }

                }
                //module found, but wrong version
                elseif (version_compare($strMinVersion, $objModule->getStrVersion(), ">")) {
                    return false;
                }
            }
        }


        //compare versions of installed elements
        $objModule = SystemModule::getModuleByName($this->getObjMetadata()->getStrTitle());
        if ($objModule !== null) {
            if (version_compare($this->objMetadata->getStrVersion(), $objModule->getStrVersion(), ">")) {
                return true;
            }
            else {
                return false;
            }
        }
        else {
            return true;
        }

    }

    /**
     * Gets the version of the package currently installed.
     * If not installed, null should be returned instead.
     *
     * @return string|null
     */
    public function getVersionInstalled()
    {
        //version compare - depending on module or element
        $objModule = SystemModule::getModuleByName($this->getObjMetadata()->getStrTitle());
        if ($objModule !== null) {
            return $objModule->getStrVersion();
        }
        else {
            return null;
        }

    }

    /**
     * Queries the packagemanager for the resolved target path, so the folder to package will be located at
     * after installation (or is already located at since it's already installed.
     *
     * @return mixed
     */
    public function getStrTargetPath()
    {

        $strTarget = $this->objMetadata->getStrTarget();
        if ($strTarget == "") {
            $strTarget = uniStrtolower($this->objMetadata->getStrType()."_".createFilename($this->objMetadata->getStrTitle(), true))."";
        }

        $arrModules = array_flip(Classloader::getInstance()->getArrModules());

        if (isset($arrModules[$strTarget])) {
            return "/".$arrModules[$strTarget];
        }

        return "/core/".$strTarget;
    }

    /**
     * Validates if the current package is removable or not.
     *
     * @return bool
     */
    public function isRemovable()
    {
        $objManager = new PackagemanagerManager();

        if (count($objManager->getArrRequiredBy($this->getObjMetadata())) > 0) {
            return false;
        }

        if (!$this->getObjMetadata()->getBitProvidesInstaller()) {
            return true;
        }

        //scan installers in order to query them on their removable status
        $bitIsRemovable = true;
        foreach ($this->getInstaller($this->getObjMetadata()) as $objOneInstaller) {
            if (!$objOneInstaller instanceof InstallerRemovableInterface) {
                $bitIsRemovable = false;
                break;
            }

            if (!$objOneInstaller->isRemovable()) {
                $bitIsRemovable = false;
                break;
            }
        }

        return $bitIsRemovable;
    }

    /**
     * Removes the current package, if possible, from the system
     *
     * @param string &$strLog
     *
     * @return bool
     */
    public function remove(&$strLog)
    {

        if (!$this->isRemovable()) {
            return false;
        }

        $bitReturn = true;

        //if we reach up until here, each installer should be an instance of InstallerRemovableInterface
        foreach ($this->getInstaller($this->getObjMetadata()) as $objOneInstaller) {
            if ($objOneInstaller instanceof InstallerRemovableInterface) {
                $bitReturn = $bitReturn && $objOneInstaller->remove($strLog);
            }
        }

        //finally: delete the the module on file-system level
        if ($bitReturn) {
            $strLog .= "Deleting file-system parts...\n";
            $objFilesystem = new Filesystem();
            $bitReturn = $objFilesystem->folderDeleteRecursive($this->getObjMetadata()->getStrPath());

            if (!$bitReturn) {
                $strLog .= "Error deleting file-system parts!. Please remove manually: ".$this->getObjMetadata()->getStrPath()."";
            }
        }

        $strLog .= "\n\nRemoval finished ".($bitReturn ? "successfully" : " with errors")."\n";

        return $bitReturn;
    }


    /**
     * Internal helper, fetches all installers located within the passed package
     *
     * @param PackagemanagerMetadata $objMetadata
     *
     * @return InstallerInterface[]
     */
    protected function getInstaller(PackagemanagerMetadata $objMetadata)
    {

        $objFilesystem = new Filesystem();
        $arrInstaller = $objFilesystem->getFilelist($objMetadata->getStrPath()."/installer/", array(".php"));

        $arrReturn = array();
        //start with modules
        foreach ($arrInstaller as $strOneInstaller) {

            /** @var $objInstaller InstallerInterface */
            $objInstaller = Classloader::getInstance()->getInstanceFromFilename(_realpath_.$objMetadata->getStrPath()."/installer/".$strOneInstaller);
            $arrReturn[] = $objInstaller;
        }

        return $arrReturn;
    }

}