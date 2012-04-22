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
    public function getInstalledPackages() {
        $arrReturn = array();

        $objModuleProvider = new class_module_packagemanager_packagemanager_module();
        $arrReturn = array_merge($arrReturn, $objModuleProvider->getInstalledPackages());

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

        $objZip = new class_zip();
        $objZip->extractArchive($strPackagePath, _projectpath_."/temp/".$strTargetFolder);

        return $this->getPackageManagerForPath(_projectpath_."/temp/".$strTargetFolder);
    }
}