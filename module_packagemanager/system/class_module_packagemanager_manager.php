<?php
/*"******************************************************************************************************
*   (c) 2007-2012 by Kajona, www.kajona.de                                                              *
*       Published under the GNU LGPL v2.1, see /system/licence_lgpl.txt                                 *
*-------------------------------------------------------------------------------------------------------*
*	$Id: class_module_tags_admin.php 4485 2012-02-07 12:48:04Z sidler $                                  *
********************************************************************************************************/

/**
 * Central class to access the package-management subsystem.
 *
 * @author sidler@mulchprod.de
 * @since 4.0
 * @package module_packagemanager
 */
class class_module_packagemanager_manager {

    public static $STR_TYPE_MODULE = "MODULE";
    public static $STR_TYPE_TEMPLATE = "TEMPLATE";

    /**
     * Queries the local filesystem in order to find all packages available.
     * This may include packages of all providers.
     *
     * @return class_module_packagemanager_metadata[]
     */
    public function getAvailablePackages() {
        $arrReturn = array();

        $objModuleProvider = new class_module_packagemanager_packagemanager_module();
        $arrReturn = array_merge($arrReturn, $objModuleProvider->getInstalledPackages());

        $objPackageProvider = new class_module_packagemanager_packagemanager_template();
        $arrReturn = array_merge($arrReturn, $objPackageProvider->getInstalledPackages());

        return $arrReturn;
    }

    /**
     * Loads the matching packagemanager for a given path.
     *
     * @param $strPath
     * @return interface_packagemanager_packagemanager|null
     */
    public function getPackageManagerForPath($strPath) {
        $objMetadata = new class_module_packagemanager_metadata();
        $objMetadata->autoInit($strPath);

        $objManager = null;

        if($objMetadata->getStrType() == self::$STR_TYPE_MODULE) {
            $objManager = new class_module_packagemanager_packagemanager_module();
            $objManager->setObjMetadata($objMetadata);
        }

        if($objMetadata->getStrType() == self::$STR_TYPE_TEMPLATE) {
            $objManager = new class_module_packagemanager_packagemanager_template();
            $objManager->setObjMetadata($objMetadata);
        }


        return $objManager;
    }

    /**
     * Extracts the zip-archive into a temp-folder.
     * The matching packagemanager is returned.
     *
     * @param $strPackagePath
     * @return interface_packagemanager_packagemanager
     */
    public function extractPackage($strPackagePath) {
        $strTargetFolder = generateSystemid();

        class_logger::getInstance("extracting package ".$strPackagePath." to "._projectpath_."/temp/".$strTargetFolder, class_logger::$levelInfo);

        $objZip = new class_zip();
        $objZip->extractArchive($strPackagePath, _projectpath_."/temp/".$strTargetFolder);

        return $this->getPackageManagerForPath(_projectpath_."/temp/".$strTargetFolder);
    }

    /**
     * Returns all content-providers as configured in the /config/packagemanager.php file.
     *
     * @return interface_packagemanager_contentprovider[]
     */
    public function getContentproviders() {
        $objConfig = class_config::getInstance("packagemanager.php");

        $strProvider = $objConfig->getConfig("contentproviders");

        $arrProviders = explode(",", $strProvider);
        $arrReturn = array();
        foreach($arrProviders as $strOneProvider) {
            $strOneProvider = trim($strOneProvider);
            if($strOneProvider != "") {
                $arrReturn[] = new $strOneProvider();
            }
        }

        return $arrReturn;
    }

    /**
     * Validates, if a given path represents a valid package
     *
     * @param $strPath
     * @return bool
     */
    public function validatePackage($strPath) {
        try {
            $objMetadata = new class_module_packagemanager_metadata();
            $objMetadata->autoInit($strPath);
            return true;
        }
        catch(class_exception $objEx) {

        }

        return false;

    }

    /**
     * Searches the latest version of a given package.
     * If found, the version itself is returned.
     * Therefore, all providers are called in order of appearance, the
     * first match is returned.
     *
     * @param interface_packagemanager_packagemanager $objPackage
     * @return string|null
     */
    public function searchLatestVersion(interface_packagemanager_packagemanager $objPackage) {
        $arrProvider = $this->getContentproviders();

        foreach($arrProvider as $objOneProvider) {
            $arrModule = $objOneProvider->searchPackage($objPackage->getObjMetadata()->getStrTitle());

            if($arrModule != null && $arrModule["title"] == $objPackage->getObjMetadata()->getStrTitle()) {
                return $arrModule["version"];
            }

        }

        return null;
    }

    /**
     * Triggers the update of the passed package.
     * It is evaluated, if a new version is available.
     * The provider itself is called via initPackageUpdate, so it's to providers choice
     * to decide what action to take.
     *
     * @param interface_packagemanager_packagemanager $objPackage
     * @return mixed
     */
    public function updatePackage(interface_packagemanager_packagemanager $objPackage) {
        $arrProvider = $this->getContentproviders();

        foreach($arrProvider as $objOneProvider) {
            $arrModule = $objOneProvider->searchPackage($objPackage->getObjMetadata()->getStrTitle());

            if($arrModule != null && $arrModule["title"] == $objPackage->getObjMetadata()->getStrTitle()) {

                $objOneProvider->initPackageUpdate($arrModule["title"]);
            }

        }
    }
}