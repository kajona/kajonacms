<?php
/*"******************************************************************************************************
*   (c) 2007-2015 by Kajona, www.kajona.de                                                              *
*       Published under the GNU LGPL v2.1, see /system/licence_lgpl.txt                                 *
*-------------------------------------------------------------------------------------------------------*
*	$Id$                                  *
********************************************************************************************************/

/**
 * Manager to handle template packages.
 * Capable of installing and updating packages.
 * Since template-packs are only file-system based, the installation is only a copy-procedure.
 *
 * @package module_packagemanager
 * @author sidler@mulchprod.de
 * @since 4.0
 */
class class_module_packagemanager_packagemanager_template implements interface_packagemanager_packagemanager {

    /**
     * @var class_module_packagemanager_metadata
     */
    private $objMetadata;


    /**
     * Returns a list of installed packages, so a single metadata-entry
     * for each package.
     *
     * @return class_module_packagemanager_metadata[]
     */
    public function getInstalledPackages() {
        $arrReturn = array();

        //loop all packages found
        $objFilesystem = new class_filesystem();
        $arrFolders = $objFilesystem->getCompleteList("/templates");

        foreach($arrFolders["folders"] as $strOneFolder) {
            try {
                $objMetadata = new class_module_packagemanager_metadata();
                $objMetadata->autoInit("/templates/".$strOneFolder);
                $arrReturn[] = $objMetadata;
            }
            catch(class_exception $objEx) {

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
     * @throws class_exception
     * @return void
     */
    public function move2Filesystem() {
        $strSource = $this->objMetadata->getStrPath();

        if(!is_dir(_realpath_.$strSource))
            throw new class_exception("current package ".$strSource." is not a folder.", class_exception::$level_ERROR);

        $objFilesystem = new class_filesystem();
        $objFilesystem->chmod($this->getStrTargetPath(), 0777);

        class_logger::getInstance(class_logger::PACKAGEMANAGEMENT)->addLogRow("moving ".$strSource." to ".$this->getStrTargetPath(), class_logger::$levelInfo);

        $objFilesystem->folderCopyRecursive($strSource, $this->getStrTargetPath(), true);
        $this->objMetadata->setStrPath($this->getStrTargetPath());

        $objFilesystem->chmod($this->getStrTargetPath());

        $objFilesystem->folderDeleteRecursive($strSource);

        //shift the cache buster
        $objSetting = class_module_system_setting::getConfigByName("_system_browser_cachebuster_");
        if($objSetting != null) {
            $objSetting->setStrValue((int)$objSetting->getStrValue()+1);
            $objSetting->updateObjectToDb();
        }
    }

    /**
     * Invokes the installer, if given.
     * The installer itself is capable of detecting whether an update or a plain installation is required.
     *
     * @throws class_exception
     * @return string
     */
    public function installOrUpdate() {
        return "";
    }


    /**
     * @param class_module_packagemanager_metadata $objMetadata
     * @return void
     */
    public function setObjMetadata($objMetadata) {
        $this->objMetadata = $objMetadata;
    }

    /**
     * @return class_module_packagemanager_metadata
     */
    public function getObjMetadata() {
        return $this->objMetadata;
    }

    /**
     * Validates, whether the current package is installable or not.
     *
     * @return bool
     */
    public function isInstallable() {
        return false;
    }

    /**
     * Gets the version of the package currently installed.
     * If not installed, null should be returned instead.
     *
     * @return string|null
     */
    public function getVersionInstalled() {

        $strTarget = $this->getStrTargetPath();

        if(is_dir(_realpath_.$strTarget)) {
            $objManager = new class_module_packagemanager_metadata();
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
    public function getStrTargetPath() {
        $strTarget = $this->objMetadata->getStrTarget();
        if($strTarget == "")
            $strTarget = uniStrtolower(createFilename($this->objMetadata->getStrTitle(), true));

        return "/templates/".$strTarget;
    }

    /**
     * This method is called during the installation of a package.
     * Depending on the current manager, the default-template may be updated.
     *
     * @return bool
     */
    public function updateDefaultTemplate() {
        return true;
    }

    /**
     * Validates if the current package is removable or not.
     *
     * @return bool
     */
    public function isRemovable() {
        return class_module_system_setting::getConfigValue("_packagemanager_defaulttemplate_") != $this->getObjMetadata()->getStrTitle();
    }

    /**
     * Removes the current package, if possible, from the system
     *
     * @param string &$strLog
     *
     * @return bool
     */
    public function remove(&$strLog) {

        if(!$this->isRemovable()) {
            return false;
        }

        /** @var class_module_packagemanager_template[] $arrTemplates */
        $arrTemplates = class_module_packagemanager_template::getObjectList();

        foreach($arrTemplates as $objOneTemplate) {
            if($objOneTemplate->getStrName() == $this->getObjMetadata()->getStrTitle()) {
                return $objOneTemplate->deleteObject();
            }
        }

        return false;
    }

}