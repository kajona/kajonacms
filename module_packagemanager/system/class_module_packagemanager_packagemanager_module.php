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
class class_module_packagemanager_packagemanager_module implements interface_packagemanager_packagemanager {

    /**
     * @var class_module_packagemanager_metadata
     */
    protected $objMetadata;


    /**
     * Returns a list of installed packages, so a single metadata-entry
     * for each package.
     *
     * @return class_module_packagemanager_metadata[]
     */
    public function getInstalledPackages() {
        $arrReturn = array();

        //loop all modules
        $arrModules = class_resourceloader::getInstance()->getArrModules();

        foreach($arrModules as $strPath => $strOneModule) {
            try {
                $objMetadata = new class_module_packagemanager_metadata();
                $objMetadata->autoInit("/".$strPath);
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

        class_logger::getInstance(class_logger::PACKAGEMANAGEMENT)->addLogRow("moving ".$strSource." to ".$this->getStrTargetPath(), class_logger::$levelInfo);

        $objFilesystem = new class_filesystem();
        //set a chmod before copying the files - at least try to
        $objFilesystem->chmod($this->getStrTargetPath(), 0777);

        $objFilesystem->folderCopyRecursive($strSource, $this->getStrTargetPath(), true);
        $this->objMetadata->setStrPath($this->getStrTargetPath());

        //reset chmod after copying the files
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
        $strReturn = "";

        if(uniStrpos($this->getObjMetadata()->getStrPath(), "core") === false)
            throw new class_exception("Current module not located in a core directory.", class_exception::$level_ERROR);

        if(!$this->isInstallable())
            throw new class_exception("Current module isn't installable, not all requirements are given", class_exception::$level_ERROR);

        //search for an existing installer
        if (substr($this->objMetadata->getStrPath(), -5) == ".phar") {
            $objPhar = new Phar(_realpath_.$this->objMetadata->getStrPath());
            $arrInstaller = array();
            foreach (new RecursiveIteratorIterator($objPhar) as $objFile) {
                if (strpos($objFile->getPathname(), "/installer/") !== false) {
                    $arrInstaller[] = $objFile->getPathname();
                }
            }
        } else {
            $objFilesystem = new class_filesystem();
            $arrInstaller = $objFilesystem->getFilelist($this->objMetadata->getStrPath()."/installer/", array(".php"));
        }


        if($arrInstaller === false)
            return "";

        //start with modules
        foreach($arrInstaller as $strOneInstaller) {

            if (substr($strOneInstaller, 0, 7) == "phar://") {
                $strFile = $strOneInstaller;
            } else {
                $strFile = $this->objMetadata->getStrPath()."/installer/".$strOneInstaller;
            }

            $objInstance = class_classloader::getInstance()->getInstanceFromFilename($strFile, "class_installer_base");

            if($objInstance == false)
                continue;

            //skip element installers at first run
            class_logger::getInstance(class_logger::PACKAGEMANAGEMENT)->addLogRow("triggering updateOrInstall() on installer ".$strOneInstaller.", all requirements given", class_logger::$levelInfo);
            //trigger update or install
            $strReturn .= $objInstance->installOrUpdate();
        }

        //proceed with elements
        foreach($arrInstaller as $strOneInstaller) {

            if (substr($strOneInstaller, 0, 7) == "phar://") {
                $strFile = $strOneInstaller;
            } else {
                $strFile = $this->objMetadata->getStrPath()."/installer/".$strOneInstaller;
            }

            $objInstance = class_classloader::getInstance()->getInstanceFromFilename($strFile, "class_elementinstaller_base");

            if($objInstance == false)
                continue;

            //skip samplecontent files
            class_logger::getInstance(class_logger::PACKAGEMANAGEMENT)->addLogRow("triggering updateOrInstall() on installer ".$strOneInstaller.", all requirements given", class_logger::$levelInfo);
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
     * In nearly all cases
     *
     * @return bool
     */
    public function isInstallable() {

        if(!$this->getObjMetadata()->getBitProvidesInstaller())
            return false;

        //check if required modules are given in matching versions
        $arrRequiredModules = $this->objMetadata->getArrRequiredModules();
        foreach($arrRequiredModules as $strOneModule => $strMinVersion) {

            if(trim($strOneModule) != "") {
                $objModule = class_module_system_module::getModuleByName(trim($strOneModule));
                if($objModule === null) {

                    $arrModules = class_resourceloader::getInstance()->getArrModules();
                    $objMetadata = null;
                    foreach($arrModules as $strPath => $strOneFolder) {
                        if(uniStrpos($strOneFolder, $strOneModule) !== false) {
                            $objMetadata = new class_module_packagemanager_metadata();
                            $objMetadata->autoInit("/".$strPath);

                            //but: if the package provides an installer and was not resolved by the previous calls,
                            //we shouldn't include it here
                            if($objMetadata->getBitProvidesInstaller())
                                $objMetadata = null;
                        }

                    }

                    //no package found
                    if($objMetadata === null) {
                        return false;
                    }

                    //package found, but wrong version
                    if(version_compare($strMinVersion, $objMetadata->getStrVersion(), ">"))
                        return false;

                }
                //module found, but wrong version
                else if(version_compare($strMinVersion, $objModule->getStrVersion(), ">")) {
                    return false;
                }
            }
        }


        //compare versions of installed elements
        $objModule = class_module_system_module::getModuleByName($this->getObjMetadata()->getStrTitle());
        if($objModule !== null) {
            if(version_compare($this->objMetadata->getStrVersion(), $objModule->getStrVersion(), ">"))
                return true;
            else
                return false;
        }
        else
            return true;


    }

    /**
     * Gets the version of the package currently installed.
     * If not installed, null should be returned instead.
     *
     * @return string|null
     */
    public function getVersionInstalled() {
        //version compare - depending on module or element
        $objModule = class_module_system_module::getModuleByName($this->getObjMetadata()->getStrTitle());
        if($objModule !== null)
            return $objModule->getStrVersion();
        else
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
        if($strTarget == "") {
            $strTarget = uniStrtolower($this->objMetadata->getStrType()."_".createFilename($this->objMetadata->getStrTitle(), true));
        }

        $arrModules = array_flip(class_resourceloader::getInstance()->getArrModules());

        if(isset($arrModules[$strTarget]))
            return "/".$arrModules[$strTarget];

        return "/core/".$strTarget;
    }

    /**
     * Validates if the current package is removable or not.
     *
     * @return bool
     */
    public function isRemovable() {
        $objManager = new class_module_packagemanager_manager();

        if(count($objManager->getArrRequiredBy($this->getObjMetadata())) > 0)
            return false;

        if(!$this->getObjMetadata()->getBitProvidesInstaller())
            return true;

        //scan installers in order to query them on their removable status
        $bitIsRemovable = true;
        foreach($this->getInstaller($this->getObjMetadata()) as $objOneInstaller) {
            if(!$objOneInstaller instanceof interface_installer_removable) {
                $bitIsRemovable = false;
                break;
            }

            if(!$objOneInstaller->isRemovable()) {
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
            $bitReturn = $objFilesystem->folderDeleteRecursive($this->getObjMetadata()->getStrPath());

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
    private function getInstaller(class_module_packagemanager_metadata $objMetadata) {
        $objFilesystem = new class_filesystem();
        $arrInstaller = $objFilesystem->getFilelist($objMetadata->getStrPath()."/installer/", array(".php"));

        if($arrInstaller === false)
            return array();

        $arrReturn = array();
        //start with modules
        foreach($arrInstaller as $strOneInstaller) {
            //skip samplecontent files
            if(uniStrpos($strOneInstaller, "class_") === false || uniStrpos($strOneInstaller, "installer") === false || uniStrpos($strOneInstaller, "_sc_") !== false)
                continue;

            $strName = uniSubstr($strOneInstaller, 0, -4);
            /** @var $objInstaller interface_installer */
            $objInstaller = new $strName();
            $arrReturn[] = $objInstaller;
        }

        return $arrReturn;
    }

}