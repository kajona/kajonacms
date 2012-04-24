<?php
/*"******************************************************************************************************
*   (c) 2007-2012 by Kajona, www.kajona.de                                                              *
*       Published under the GNU LGPL v2.1, see /system/licence_lgpl.txt                                 *
*-------------------------------------------------------------------------------------------------------*
*	$Id: class_module_tags_admin.php 4485 2012-02-07 12:48:04Z sidler $                                  *
********************************************************************************************************/

class class_module_packagemanager_packagemanager_module implements interface_packagemanager_packagemanager {

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

        //loop all modules
        $arrModules = class_resourceloader::getInstance()->getArrModules();

        foreach($arrModules as $strOneModule) {
            try {
                $objMetadata = new class_module_packagemanager_metadata();
                $objMetadata->autoInit("/core/".$strOneModule);
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
     */
    public function move2Filesystem() {
        $strSource = $this->objMetadata->getStrPath();
        $strTarget = $this->objMetadata->getStrTarget();


        class_logger::getInstance("moving ".$strSource." to /core/".$strTarget, class_logger::$levelInfo);


        $objFilesystem = new class_filesystem();
        $objFilesystem->folderCopyRecursive($strSource, "/core/".$strTarget);
        $this->objMetadata->setStrPath("/core/".$strTarget);

        $objFilesystem->folderDeleteRecursive($strSource);
    }

    /**
     * Invokes the installer, if given.
     * The installer itself is capable of detecting whether an update or a plain installation is required.
     *
     * @return string
     */
    public function installOrUpdate() {
        $strReturn = "";

        if(uniStrpos($this->getObjMetadata()->getStrPath(), "core") === false)
            throw new class_exception("Current module not located at /core.", class_exception::$level_ERROR);

        if(!$this->isInstallable())
            throw new class_exception("Current module isn't installable, not all requirements are given", class_exception::$level_ERROR);

        //search for an existing installer
        $objFilesystem = new class_filesystem();
        $arrInstaller = $objFilesystem->getFilelist($this->objMetadata->getStrPath()."/installer/", array(".php"));

        if($arrInstaller === false)
            return "";

        //start with modules
        foreach($arrInstaller as $strOneInstaller) {

            if(uniStrpos($strOneInstaller, "class_") === false)
                continue;

            //skip samplecontent files
            if(uniStrpos($strOneInstaller, "element") === false) {
                class_logger::getInstance("triggering updateOrInstall() on installer ".$strOneInstaller.", all requirements given", class_logger::$levelInfo);
                //trigger update or install
                $strName = uniSubstr($strOneInstaller, 0, -4);
                /** @var $objInstaller interface_installer */
                $objInstaller = new $strName();
                $strReturn .= $objInstaller->installOrUpdate();
            }
        }

        //proceed with elements
        foreach($arrInstaller as $strOneInstaller) {
            //skip samplecontent files
            if(uniStrpos($strOneInstaller, "element") !== false) {
                class_logger::getInstance("triggering updateOrInstall() on installer ".$strOneInstaller.", all requirements given", class_logger::$levelInfo);
                //trigger update or install
                $strName = uniSubstr($strOneInstaller, 0, -4);
                /** @var $objInstaller interface_installer */
                $objInstaller = new $strName();
                $strReturn .= $objInstaller->installOrUpdate();
            }
        }

        return $strReturn;
    }


    public function setObjMetadata($objMetadata) {
        $this->objMetadata = $objMetadata;
    }

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

        //check if required modules are given
        $arrRequiredModules = explode(",", $this->objMetadata->getStrRequiredModules());
        foreach($arrRequiredModules as $strOneModule) {
            if(trim($strOneModule) != "") {
                $objModule = class_module_system_module::getModuleByName(trim($strOneModule));
                if($objModule === null)
                    return false;
            }
        }

        //validate min system-version
        $strVersion = $this->objMetadata->getStrMinVersion();
        if($strVersion != "") {
            $objSystem = class_module_system_module::getModuleByName("system");
            if($objSystem == null || version_compare($strVersion, $objSystem->getStrVersion(), ">")) {
                return false;
            }
        }

        //compare versions

        //version compare - depending on module or element
        if(uniStrpos($this->objMetadata->getStrTarget(), "element_") !== false) {
            $objElement = class_module_pages_element::getElement($this->objMetadata->getStrTitle());
            if($objElement !== null) {
                if(version_compare($this->objMetadata->getStrVersion(), $objElement->getStrVersion(), ">"))
                    return true;
                else
                    return false;
            }
            else
                return true;
        }
        else {
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

    }

    /**
     * Gets the version of the package currently installed.
     * If not installed, null should be returned instead.
     *
     * @return string|null
     */
    public function getVersionInstalled() {
        //version compare - depending on module or element
        if(uniStrpos($this->objMetadata->getStrTarget(), "element_") !== false) {
            $objElement = class_module_pages_element::getElement($this->objMetadata->getStrTitle());
            if($objElement !== null)
                return $objElement->getStrVersion();
            else
                return null;
        }
        else {
            $objModule = class_module_system_module::getModuleByName($this->getObjMetadata()->getStrTitle());
            if($objModule !== null)
                return $objModule->getStrVersion();
            else
                return null;
        }
    }
}