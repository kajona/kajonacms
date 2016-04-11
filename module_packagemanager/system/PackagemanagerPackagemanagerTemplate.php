<?php
/*"******************************************************************************************************
*   (c) 2007-2016 by Kajona, www.kajona.de                                                              *
*       Published under the GNU LGPL v2.1, see /system/licence_lgpl.txt                                 *
*-------------------------------------------------------------------------------------------------------*
*	$Id$                                  *
********************************************************************************************************/

namespace Kajona\Packagemanager\System;

use Kajona\System\System\Exception;
use Kajona\System\System\Filesystem;
use Kajona\System\System\Logger;
use Kajona\System\System\OrmBase;
use Kajona\System\System\OrmDeletedhandlingEnum;
use Kajona\System\System\PharModule;
use Kajona\System\System\PharModuleExtractor;
use Kajona\System\System\SystemSetting;


/**
 * Manager to handle template packages.
 * Capable of installing and updating packages.
 * Since template-packs are only file-system based, the installation is only a copy-procedure.
 *
 * @package module_packagemanager
 * @author sidler@mulchprod.de
 * @since 4.0
 */
class PackagemanagerPackagemanagerTemplate implements PackagemanagerPackagemanagerInterface
{

    /**
     * @var PackagemanagerMetadata
     */
    private $objMetadata;


    /**
     * Returns a list of installed packages, so a single metadata-entry
     * for each package.
     *
     * @return PackagemanagerMetadata[]
     */
    public function getInstalledPackages()
    {
        $arrReturn = array();

        //loop all packages found
        $objFilesystem = new Filesystem();
        $arrFolders = $objFilesystem->getCompleteList("/templates");

        foreach ($arrFolders["folders"] as $strOneFolder) {
            try {
                $objMetadata = new PackagemanagerMetadata();
                $objMetadata->autoInit("/templates/".$strOneFolder);
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

        if (\Kajona\System\System\PharModule::isPhar(_realpath_.$strSource)) {
            $objFilesystem = new Filesystem();
            $objFilesystem->chmod($this->getStrTargetPath(), 0777);
            Logger::getInstance(Logger::PACKAGEMANAGEMENT)->addLogRow("extracting ".$strSource." to ".$this->getStrTargetPath(), Logger::$levelInfo);

            $objPharModules = new PharModule($strSource);
            foreach($objPharModules->getContentMap() as $strFilename => $strPath) {
                $objFilesystem->folderCreate(dirname($this->getStrTargetPath().$strFilename), true);
                copy($strPath, _realpath_.$this->getStrTargetPath().$strFilename);
            }

            $objFilesystem->fileDelete($strSource);

        }
        elseif (is_dir(_realpath_.$strSource)) {

            $objFilesystem = new Filesystem();
            $objFilesystem->chmod($this->getStrTargetPath(), 0777);

            Logger::getInstance(Logger::PACKAGEMANAGEMENT)->addLogRow("moving ".$strSource." to ".$this->getStrTargetPath(), Logger::$levelInfo);

            $objFilesystem->folderCopyRecursive($strSource, $this->getStrTargetPath(), true);
            $objFilesystem->folderDeleteRecursive($strSource);


        } else {
            throw new Exception("current package ".$strSource." is not a folder and not a phar", Exception::$level_ERROR);
        }

        $this->objMetadata->setStrPath($this->getStrTargetPath());
        $objFilesystem->chmod($this->getStrTargetPath());

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
        return "";
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
     *
     * @return bool
     */
    public function isInstallable()
    {
        return false;
    }

    /**
     * Gets the version of the package currently installed.
     * If not installed, null should be returned instead.
     *
     * @return string|null
     */
    public function getVersionInstalled()
    {

        $strTarget = $this->getStrTargetPath();

        if (is_dir(_realpath_.$strTarget)) {
            $objManager = new PackagemanagerMetadata();
            $objManager->autoInit($strTarget);
            return $objManager->getStrVersion();
        }


        return null;
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
            $strTarget = uniStrtolower(createFilename($this->objMetadata->getStrTitle(), true));
        }

        return "/templates/".$strTarget;
    }

    /**
     * This method is called during the installation of a package.
     * Depending on the current manager, the default-template may be updated.
     *
     * @return bool
     */
    public function updateDefaultTemplate()
    {
        return true;
    }

    /**
     * Validates if the current package is removable or not.
     *
     * @return bool
     */
    public function isRemovable()
    {
        return SystemSetting::getConfigValue("_packagemanager_defaulttemplate_") != $this->getObjMetadata()->getStrTitle();
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

        OrmBase::setObjHandleLogicalDeletedGlobal(OrmDeletedhandlingEnum::INCLUDED);

        if (!$this->isRemovable()) {
            return false;
        }

        /** @var PackagemanagerTemplate[] $arrTemplates */
        $arrTemplates = PackagemanagerTemplate::getObjectList();

        foreach ($arrTemplates as $objOneTemplate) {
            if ($objOneTemplate->getStrName() == $this->getObjMetadata()->getStrTitle()) {
                return $objOneTemplate->deleteObjectFromDatabase();
            }
        }

        OrmBase::setObjHandleLogicalDeletedGlobal(OrmDeletedhandlingEnum::EXCLUDED);

        return false;
    }

}