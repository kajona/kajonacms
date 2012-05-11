<?php
/*"******************************************************************************************************
*   (c) 2007-2012 by Kajona, www.kajona.de                                                              *
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
     */
    public function move2Filesystem() {
        $strSource = $this->objMetadata->getStrPath();
        $strTarget = $this->objMetadata->getStrTarget();

        if(!is_dir(_realpath_.$strSource))
            throw new class_exception("current package ".$strSource." is not a folder.", class_exception::$level_ERROR);

        class_logger::getInstance("moving ".$strSource." to /templates/".$strTarget, class_logger::$levelInfo);


        $objFilesystem = new class_filesystem();
        $objFilesystem->folderCopyRecursive($strSource, "/templates/".$strTarget, true);
        $this->objMetadata->setStrPath("/templates/".$strTarget);

        $objFilesystem->folderDeleteRecursive($strSource);
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


    public function setObjMetadata($objMetadata) {
        $this->objMetadata = $objMetadata;
    }

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
        return null;
    }
}