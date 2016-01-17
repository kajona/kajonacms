<?php
/*"******************************************************************************************************
*   (c) 2007-2015 by Kajona, www.kajona.de                                                              *
*       Published under the GNU LGPL v2.1, see /system/licence_lgpl.txt                                 *
*-------------------------------------------------------------------------------------------------------*
*	$Id$                                  *
********************************************************************************************************/


/**
 * Implementation to handle module-packages. List all installed module-packages and starts the installation / update.
 *
 * @package module_packagemanager
 * @author sidler@mulchprod.de
 * @since 4.0
 */
class class_module_packagemanager_packagemanager_pharmodule extends class_module_packagemanager_packagemanager_module {




    /**
     * Copies the phar from the temp-folder
     * to the target-folder.
     * In most cases, this is either located at /core or at /templates.
     * The original should be deleted afterwards.
     *
     * @throws class_exception
     * @return void
     */
    public function move2Filesystem() {
        $strSource = $this->objMetadata->getStrPath();

        if(!\Kajona\System\System\PharModule::isPhar(_realpath_.$strSource))
            throw new class_exception("current package ".$strSource." is not a phar.", class_exception::$level_ERROR);

        class_logger::getInstance(class_logger::PACKAGEMANAGEMENT)->addLogRow("moving ".$strSource." to ".$this->getStrTargetPath(), class_logger::$levelInfo);

        $objFilesystem = new class_filesystem();
        //set a chmod before copying the files - at least try to
        $objFilesystem->chmod($this->getStrTargetPath(), 0777);

        $objFilesystem->fileCopy($strSource, $this->getStrTargetPath(), true);
        $this->objMetadata->setStrPath($this->getStrTargetPath());

        //reset chmod after copying the files
        $objFilesystem->chmod($this->getStrTargetPath());
        $objFilesystem->fileDelete($strSource);

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
        $strReturn = "";

        if(uniStrpos($this->getObjMetadata()->getStrPath(), "core") === false)
            throw new class_exception("Current module not located in a core directory.", class_exception::$level_ERROR);

        if(!$this->isInstallable())
            throw new class_exception("Current module isn't installable, not all requirements are given", class_exception::$level_ERROR);

        //search for an existing installer
        $objPhar = new Phar(_realpath_.$this->objMetadata->getStrPath());
        $arrInstaller = array();
        foreach (new RecursiveIteratorIterator($objPhar) as $objFile) {
            if (strpos($objFile->getPathname(), "/installer/") !== false) {
                $arrInstaller[] = $objFile->getPathname();
            }
        }


        $arrInstaller = $this->getInstaller($this->getObjMetadata());

        //start with modules
        foreach($arrInstaller as $objInstance) {

            if(!$objInstance instanceof class_installer_base) {
                continue;
            }

            //skip element installers at first run
            class_logger::getInstance(class_logger::PACKAGEMANAGEMENT)->addLogRow("triggering updateOrInstall() on installer ".get_class($objInstance).", all requirements given", class_logger::$levelInfo);
            //trigger update or install
            $strReturn .= $objInstance->installOrUpdate();
        }

        class_cache::flushCache();

        return $strReturn;
    }


    /**
     * @return bool
     */
    public function updateDefaultTemplate() {
        //TODO
        $objFilesystem = new class_filesystem();
        class_logger::getInstance(class_logger::PACKAGEMANAGEMENT)->addLogRow("updating default template from /".$this->objMetadata->getStrPath(), class_logger::$levelInfo);
        if(is_dir(_realpath_."/".$this->objMetadata->getStrPath()."/templates/default/js"))
            $objFilesystem->folderCopyRecursive($this->objMetadata->getStrPath()."/templates/default/js", "/templates/default/js", true);

        if(is_dir(_realpath_."/".$this->objMetadata->getStrPath()."/templates/default/css"))
            $objFilesystem->folderCopyRecursive($this->objMetadata->getStrPath()."/templates/default/css", "/templates/default/css", true);

        if(is_dir(_realpath_."/".$this->objMetadata->getStrPath()."/templates/default/pics"))
            $objFilesystem->folderCopyRecursive($this->objMetadata->getStrPath()."/templates/default/pics", "/templates/default/pics", true);

        return true;
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

        $bitReturn = true;

        //if we reach up until here, each installer should be an instance of interface_installer_removable
        foreach($this->getInstaller($this->getObjMetadata()) as $objOneInstaller) {
            if($objOneInstaller instanceof interface_installer_removable) {
                $bitReturn = $bitReturn && $objOneInstaller->remove($strLog);
            }
        }

        //finally: delete the the module on file-system level
        if($bitReturn) {
            $strLog .= "Deleting file-system parts...\n";
            $objFilesystem = new class_filesystem();
            $bitReturn = $objFilesystem->fileDelete($this->getObjMetadata()->getStrPath());

            if(!$bitReturn) {
                $strLog .= "Error deleting file-system parts!. Please remove manually: ".$this->getObjMetadata()->getStrPath()."";
            }
        }

        $strLog.= "\n\nRemoval finished ".($bitReturn ? "successfully" : " with errors")."\n";

        return $bitReturn;
    }


    /**
     * Internal helper, fetches all installers located within the passed package
     *
     * @param class_module_packagemanager_metadata $objMetadata
     *
     * @return interface_installer[]
     */
    protected function getInstaller(class_module_packagemanager_metadata $objMetadata) {

        $objPhar = new Phar(_realpath_.$objMetadata->getStrPath());
        $arrReturn = array();
        foreach (new RecursiveIteratorIterator($objPhar) as $objFile) {
            if (strpos($objFile->getPathname(), "/installer/") !== false && uniSubstr($objFile->getPathname(), -4) === ".php") {
                $strName = uniSubstr($objFile->getPathname(), 0, -4);
                /** @var $objInstaller interface_installer */
                $objInstaller = new $strName();
                $arrReturn[] = $objInstaller;
            }
        }

        return $arrReturn;
    }

}