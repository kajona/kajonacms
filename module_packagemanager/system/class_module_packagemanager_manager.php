<?php
/*"******************************************************************************************************
*   (c) 2007-2012 by Kajona, www.kajona.de                                                              *
*       Published under the GNU LGPL v2.1, see /system/licence_lgpl.txt                                 *
*-------------------------------------------------------------------------------------------------------*
*	$Id$                                  *
********************************************************************************************************/

/**
 * Central class to access the package-management subsystem.
 *
 * @author sidler@mulchprod.de
 * @since 4.0
 * @package module_packagemanager
 */
class class_module_packagemanager_manager {

    const STR_TYPE_MODULE = "MODULE";
    const STR_TYPE_ELEMENT = "ELEMENT";
    const STR_TYPE_TEMPLATE = "TEMPLATE";

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

        usort($arrReturn, function(class_module_packagemanager_metadata $objA, class_module_packagemanager_metadata $objB) {
            return strcmp($objA->getStrTitle(), $objB->getStrTitle());
        });

        $objPackageProvider = new class_module_packagemanager_packagemanager_template();
        $arrReturn = array_merge($objPackageProvider->getInstalledPackages(), $arrReturn);

        return $arrReturn;
    }

    /**
     * Searches the current local packages for a single, given package.
     * If not found, null is returned.
     *
     * @param $strName
     *
     * @return class_module_packagemanager_metadata|null
     */
    public function getPackage($strName) {
        $arrAvailable = $this->getAvailablePackages();
        foreach($arrAvailable as $objOnePackage) {
            if($objOnePackage->getStrTitle() == $strName)
                return $objOnePackage;
        }

        return null;
    }

    /**
     * Loads the matching packagemanager for a given path.
     *
     * @param $strPath
     *
     * @return interface_packagemanager_packagemanager|null
     */
    public function getPackageManagerForPath($strPath) {
        $objMetadata = new class_module_packagemanager_metadata();
        $objMetadata->autoInit($strPath);

        $objManager = null;

        if($objMetadata->getStrType() == self::STR_TYPE_MODULE) {
            $objManager = new class_module_packagemanager_packagemanager_module();
            $objManager->setObjMetadata($objMetadata);
        }

        if($objMetadata->getStrType() == self::STR_TYPE_TEMPLATE) {
            $objManager = new class_module_packagemanager_packagemanager_template();
            $objManager->setObjMetadata($objMetadata);
        }

        if($objMetadata->getStrType() == self::STR_TYPE_ELEMENT) {
            $objManager = new class_module_packagemanager_packagemanager_element();
            $objManager->setObjMetadata($objMetadata);
        }


        return $objManager;
    }

    /**
     * Extracts the zip-archive into a temp-folder.
     * The matching packagemanager is returned.
     *
     * @param $strPackagePath
     *
     * @return interface_packagemanager_packagemanager
     */
    public function extractPackage($strPackagePath) {
        $strTargetFolder = generateSystemid();

        class_logger::getInstance(class_logger::PACKAGEMANAGEMENT)->addLogRow("extracting package ".$strPackagePath." to "._projectpath_."/temp/".$strTargetFolder, class_logger::$levelInfo);

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
     *
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
     *
     * @return string|null
     *
     * @todo maybe load all external packages, this could reduce the number of external requests per source to one
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
     * Validates a packages' latest version and compares it to the version currently installed.
     *
     * @param interface_packagemanager_packagemanager $objPackage
     *
     * @return bool or null of the package could not be found
     */
    public function updateAvailable(interface_packagemanager_packagemanager $objPackage) {
        $strLatestVersion = $this->searchLatestVersion($objPackage);
        if($strLatestVersion !== null) {
            if($strLatestVersion != null && version_compare($strLatestVersion, $objPackage->getObjMetadata()->getStrVersion(), ">")) {
                class_logger::getInstance(class_logger::PACKAGEMANAGEMENT)->addLogRow(
                    "found update for package ".$objPackage->getObjMetadata()->getStrTitle().", installed: ".$objPackage->getObjMetadata()->getStrVersion()." available: ".$strLatestVersion, class_logger::$levelInfo
                );

                $this->sendUpdateAvailableMessage($objPackage, $strLatestVersion);

                return true;
            }
            else
                return false;
        }

        return null;
    }

    private function sendUpdateAvailableMessage(interface_packagemanager_packagemanager $objPackage, $strLatestVersion) {
        //check, if not already sent
        $strIdentifier = sha1(__CLASS__.$objPackage->getObjMetadata()->getStrTitle().$strLatestVersion);

        if(count(class_module_messaging_message::getMessagesByIdentifier($strIdentifier)) == 0) {

            $strMailtext = class_carrier::getInstance()->getObjLang()->getLang("update_notification_intro", "packagemanager")."\n";
            $strMailtext .= class_carrier::getInstance()->getObjLang()->getLang("update_notification_package", "packagemanager")." ".$objPackage->getObjMetadata()->getStrTitle()."\n";
            $strMailtext .= class_carrier::getInstance()->getObjLang()->getLang("update_notification_verinst", "packagemanager")." ".$objPackage->getObjMetadata()->getStrVersion()."\n";
            $strMailtext .= class_carrier::getInstance()->getObjLang()->getLang("update_notification_verav", "packagemanager")." ".$strLatestVersion."\n";

            $objMessageHandler = new class_module_messaging_messagehandler();
            $objMessageHandler->sendMessage($strMailtext, new class_module_user_group(_admins_group_id_), new class_messageprovider_packageupdate(), $strIdentifier);
        }
    }

    /**
     * Triggers the update of the passed package.
     * It is evaluated, if a new version is available.
     * The provider itself is called via initPackageUpdate, so it's to providers choice
     * to decide what action to take.
     *
     * @param interface_packagemanager_packagemanager $objPackage
     *
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

        class_resourceloader::getInstance()->flushCache();
        class_classloader::getInstance()->flushCache();
        class_reflection::flushCache();
    }
}