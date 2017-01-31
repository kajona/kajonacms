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
use Kajona\System\System\InstallerBase;
use Kajona\System\System\InstallerInterface;
use Kajona\System\System\InstallerRemovableInterface;
use Kajona\System\System\Logger;
use Kajona\System\System\StringUtil;
use Kajona\System\System\SystemSetting;
use Phar;
use RecursiveIteratorIterator;


/**
 * Implementation to handle module-packages. List all installed module-packages and starts the installation / update.
 *
 * @package module_packagemanager
 * @author sidler@mulchprod.de
 * @since 4.0
 */
class PackagemanagerPackagemanagerPharmodule extends PackagemanagerPackagemanagerModule
{


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
            $strTarget = StringUtil::toLowerCase($this->objMetadata->getStrType()."_".createFilename($this->objMetadata->getStrTitle(), true)).".phar";
        }

        $arrModules = array_flip(Classloader::getInstance()->getArrModules());

        if (isset($arrModules[$strTarget])) {
            return "/".$arrModules[$strTarget];
        }

        return "/core/".$strTarget;
    }


    /**
     * Copies the phar from the temp-folder
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

        if (!\Kajona\System\System\PharModule::isPhar(_realpath_.$strSource)) {
            throw new Exception("current package ".$strSource." is not a phar.", Exception::$level_ERROR);
        }

        Logger::getInstance(Logger::PACKAGEMANAGEMENT)->addLogRow("moving ".$strSource." to ".$this->getStrTargetPath(), Logger::$levelInfo);

        $objFilesystem = new Filesystem();
        //set a chmod before copying the files - at least try to
        $objFilesystem->chmod($this->getStrTargetPath(), 0777);

        $objFilesystem->fileCopy($strSource, $this->getStrTargetPath(), true);
        $this->objMetadata->setStrPath($this->getStrTargetPath());

        //reset chmod after copying the files
        $objFilesystem->chmod($this->getStrTargetPath());
        $objFilesystem->fileDelete($strSource);

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

        if (StringUtil::indexOf($this->getObjMetadata()->getStrPath(), "core") === false) {
            throw new Exception("Current module not located in a core directory.", Exception::$level_ERROR);
        }

        if (!$this->isInstallable()) {
            throw new Exception("Current module isn't installable, not all requirements are given", Exception::$level_ERROR);
        }

        //search for an existing installer
        $arrInstaller = $this->getInstaller($this->getObjMetadata());

        //start with modules
        foreach ($arrInstaller as $objInstance) {

            if (!$objInstance instanceof InstallerBase) {
                continue;
            }

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
            $bitReturn = $objFilesystem->fileDelete($this->getObjMetadata()->getStrPath());

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

        $objPhar = new Phar(_realpath_.$objMetadata->getStrPath());
        $arrReturn = array();
        foreach (new RecursiveIteratorIterator($objPhar) as $objFile) {
            if (strpos($objFile->getPathname(), "/installer/") !== false && StringUtil::substring($objFile->getPathname(), -4) === ".php") {
                /** @var $objInstaller InstallerInterface */
                $objInstaller = Classloader::getInstance()->getInstanceFromFilename($objFile->getPathname());
                $arrReturn[] = $objInstaller;
            }
        }

        return $arrReturn;
    }

}