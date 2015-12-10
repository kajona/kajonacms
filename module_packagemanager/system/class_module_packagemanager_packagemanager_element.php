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
class class_module_packagemanager_packagemanager_element extends class_module_packagemanager_packagemanager_module implements interface_packagemanager_packagemanager {


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
            throw new class_exception("Current module not located at /core*.", class_exception::$level_ERROR);

        if(!$this->isInstallable())
            throw new class_exception("Current module isn't installable, not all requirements are given", class_exception::$level_ERROR);

        //search for an existing installer
        $objFilesystem = new class_filesystem();
        $arrInstaller = $objFilesystem->getFilelist($this->objMetadata->getStrPath()."/installer/", array(".php"));

        if($arrInstaller === false) {
            $strReturn .= "Updating default template pack...\n";
            $this->updateDefaultTemplate();
            class_cache::flushCache();
            return $strReturn;
        }

        //proceed with elements
        foreach($arrInstaller as $strOneInstaller) {

            $objInstance = class_classloader::getInstance()->getInstanceFromFilename(_realpath_.$this->objMetadata->getStrPath()."/installer/".$strOneInstaller, "class_elementinstaller_base");

            if($objInstance == false)
                continue;

            //skip samplecontent files
            class_logger::getInstance(class_logger::PACKAGEMANAGEMENT)->addLogRow("triggering updateOrInstall() on installer ".$strOneInstaller.", all requirements given", class_logger::$levelInfo);
            //trigger update or install
            $strReturn .= $objInstance->installOrUpdate();
        }

        $strReturn .= "Updating default template pack...\n";
        $this->updateDefaultTemplate();

        return $strReturn;
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
                if($objModule === null)
                    return false;

                if(version_compare($strMinVersion, $objModule->getStrVersion(), ">"))
                    return false;
            }
        }


        //compare versions of installed elements

        //version compare - depending on module or element
        $objElement = class_module_pages_element::getElement(uniStrReplace("element_", "", $this->objMetadata->getStrTitle()));
        if($objElement !== null) {
            if(version_compare($this->objMetadata->getStrVersion(), $objElement->getStrVersion(), ">"))
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
        if(class_module_system_module::getModuleByName("pages") === null)
            return null;

        $objElement = class_module_pages_element::getElement(uniStrReplace("element_", "", $this->objMetadata->getStrTitle()));
        if($objElement !== null)
            return $objElement->getStrVersion();
        else
            return null;

    }

}