<?php
/*"******************************************************************************************************
*   (c) 2007-2015 by Kajona, www.kajona.de                                                              *
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


    public static $arrLatestVersion = null;

    /**
     * Queries the local filesystem in order to find all packages available.
     * This may include packages of all providers.
     * Optionally you may reduce the list of packages using a simple filter-string
     *
     * @param string $strFilterText
     *
     * @return class_module_packagemanager_metadata[]
     */
    public function getAvailablePackages($strFilterText = "") {
        $arrReturn = array();

        $objModuleProvider = new class_module_packagemanager_packagemanager_module();
        $arrReturn = array_merge($arrReturn, $objModuleProvider->getInstalledPackages());

        $objPackageProvider = new class_module_packagemanager_packagemanager_template();
        $arrReturn = array_merge($objPackageProvider->getInstalledPackages(), $arrReturn);

        if($strFilterText != "") {
            $arrReturn = array_filter($arrReturn, function($objOneMetadata) use ($strFilterText) {return uniStrpos($objOneMetadata->getStrTitle(), $strFilterText) !== false; });
        }

        return $arrReturn;
    }

    /**
     * Sorts the array of packages ordered by the installation state, the type and the title
     *
     * @param class_module_packagemanager_metadata[] $arrPackages
     * @param bool $bitByNameOnly
     *
     * @return class_module_packagemanager_metadata[]
     */
    public function sortPackages(array $arrPackages, $bitByNameOnly = false) {
        $objManager = new class_module_packagemanager_manager();
        usort($arrPackages, function(class_module_packagemanager_metadata $objA, class_module_packagemanager_metadata $objB) use ($bitByNameOnly, $objManager) {

            $objHandlerA = $objManager->getPackageManagerForPath($objA->getStrPath());
            $objHandlerB = $objManager->getPackageManagerForPath($objB->getStrPath());

            if($bitByNameOnly) {
                return strcmp($objA->getStrTitle(), $objB->getStrTitle());
            }

            if($objA->getStrType() == class_module_packagemanager_manager::STR_TYPE_TEMPLATE && $objB->getStrType() != class_module_packagemanager_manager::STR_TYPE_TEMPLATE)
                return -1;
            else if($objA->getStrType() != class_module_packagemanager_manager::STR_TYPE_TEMPLATE && $objB->getStrType() == class_module_packagemanager_manager::STR_TYPE_TEMPLATE)
                return 1;

            if($objHandlerA->isInstallable() && $objHandlerB->isInstallable()) {
                return strcmp($objA->getStrTitle(), $objB->getStrTitle());
            }

            if($objHandlerA->isInstallable() && !$objHandlerB->isInstallable()) {
                return -1;
            }

            if(!$objHandlerA->isInstallable() && $objHandlerB->isInstallable()) {
                return 1;
            }

            return strcmp($objA->getStrTitle(), $objB->getStrTitle());
        });

        return $arrPackages;
    }

    /**
     * Searches the current local packages for a single, given package.
     * If not found, null is returned.
     *
     * @param string $strName
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
     * @param string $strPath
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
     * @param string $strPackagePath
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
     * @param string $strPath
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
     * Scans all packages available and tries to load the latest version available.
     * All packages found are returned in a list like
     * array(packagename => version)
     * In addition, the update-available messages are triggered internally.
     *
     * @return array
     */
    public function scanForUpdates() {

        $objManager = new class_module_packagemanager_manager();
        $arrVersions = $objManager->getArrLatestVersion();

        foreach($arrVersions as $strOneModule => $strOneVersion) {
            $objMetadata = $objManager->getPackage($strOneModule);
            if($objMetadata != null) {
                $objManager->updateAvailable($objManager->getPackageManagerForPath($objMetadata->getStrPath()), $strOneVersion);
            }
        }

        return $arrVersions;
    }


    /**
     * Internal helper, searches for all packages currently installed if a new version is available.
     * Therefore every source is queries only once.
     *
     * @return array array( array("title" => "version") )
     */
    private function getArrLatestVersion() {
        $arrPackages = $this->getAvailablePackages();

        $arrQueries = array();
        foreach($arrPackages as $objOneMetadata) {
            $arrQueries[$objOneMetadata->getStrTitle()] = $objOneMetadata;
        }

        $arrResult = array();
        $arrProvider = $this->getContentproviders();

        foreach($arrProvider as $objOneProvider) {
            $arrRemoteVersions = $objOneProvider->searchPackage(implode(",", array_keys($arrQueries)));
            if(!is_array($arrRemoteVersions))
                continue;

            foreach($arrRemoteVersions as $arrOneRemotePackage) {
                $arrResult[$arrOneRemotePackage["title"]] = $arrOneRemotePackage["version"];
                unset($arrQueries[$arrOneRemotePackage["title"]]);
            }

        }

        return $arrResult;
    }

    /**
     * Does an inverse-search for the package-requirements. This means that not the packages required to install the
     * passed package are returned, but the packages depending on the passed package.
     * Useful for consistency checks, e.g. before deleting a package.
     *
     * @param class_module_packagemanager_metadata $objMetadata
     *
     * @return string[]
     */
    public function getArrRequiredBy(class_module_packagemanager_metadata $objMetadata) {
        $arrReturn = array();
        foreach($this->getAvailablePackages() as $objOnePackage) {
            foreach($objOnePackage->getArrRequiredModules() as $strModule => $strVersion) {
                if($strModule == $objMetadata->getStrTitle())
                    $arrReturn[] = $objOnePackage->getStrTitle();
            }
        }

        return $arrReturn;
    }


    /**
     * Validates a packages' latest version and compares it to the version currently installed.
     * Optionally, a version to compare may be passed.
     *
     * @param interface_packagemanager_packagemanager $objPackage
     * @param string $strVersionToCompare
     *
     * @return bool or null of the package could not be found
     */
    public function updateAvailable(interface_packagemanager_packagemanager $objPackage, $strVersionToCompare = "") {

        if($strVersionToCompare === "") {
            $arrRemotePackages = $this->getArrLatestVersion();
            if(isset($arrRemotePackages[$objPackage->getObjMetadata()->getStrTitle()]))
                $strLatestVersion = $arrRemotePackages[$objPackage->getObjMetadata()->getStrTitle()];
            else
                $strLatestVersion = null;
        }
        else
            $strLatestVersion = $strVersionToCompare;

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

    /**
     * @param interface_packagemanager_packagemanager $objPackage
     * @param string $strLatestVersion
     * @return void
     */
    private function sendUpdateAvailableMessage(interface_packagemanager_packagemanager $objPackage, $strLatestVersion) {
        //check, if not already sent
        $strIdentifier = sha1(__CLASS__.$objPackage->getObjMetadata()->getStrTitle().$strLatestVersion);

        if(count(class_module_messaging_message::getMessagesByIdentifier($strIdentifier)) == 0) {

            $strMailtext = class_carrier::getInstance()->getObjLang()->getLang("update_notification_package", "packagemanager")." ".$objPackage->getObjMetadata()->getStrTitle()."\n";
            $strMailtext .= class_carrier::getInstance()->getObjLang()->getLang("update_notification_verinst", "packagemanager")." ".$objPackage->getObjMetadata()->getStrVersion()."\n";
            $strMailtext .= class_carrier::getInstance()->getObjLang()->getLang("update_notification_verav", "packagemanager")." ".$strLatestVersion."\n";

            $objMessageHandler = new class_module_messaging_messagehandler();
            $objMessage = new class_module_messaging_message();
            $objMessage->setStrTitle(class_carrier::getInstance()->getObjLang()->getLang("update_notification_intro", "packagemanager"));
            $objMessage->setStrBody($strMailtext);
            $objMessage->setObjMessageProvider(new class_messageprovider_packageupdate());
            $objMessage->setStrInternalIdentifier($strIdentifier);
            $objMessageHandler->sendMessageObject($objMessage, new class_module_user_group(class_module_system_setting::getConfigValue("_admins_group_id_")));
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
     * @throws class_exception
     * @return mixed
     */
    public function updatePackage(interface_packagemanager_packagemanager $objPackage) {
        $arrProvider = $this->getContentproviders();

        foreach($arrProvider as $objOneProvider) {
            $arrModule = $objOneProvider->searchPackage($objPackage->getObjMetadata()->getStrTitle());

            if(count($arrModule) == 1)
                $arrModule = $arrModule[0];


            if($arrModule != null && isset($arrModule["title"]) && $arrModule["title"] == $objPackage->getObjMetadata()->getStrTitle()) {
                $objOneProvider->initPackageUpdate($arrModule["title"]);
                break;
            }

        }

        return "Error loading metainformation for package ".$objPackage->getObjMetadata()->getStrTitle();
    }




    /**
     * @param class_module_packagemanager_metadata $objMetadata
     *
     * @throws class_exception
     * @return string
     */
    public function removePackage(class_module_packagemanager_metadata $objMetadata) {

        $strLog = "";

        class_orm_base::setObjHandleLogicalDeletedGlobal(class_orm_deletedhandling_enum::INCLUDED);
        $objHandler = $this->getPackageManagerForPath($objMetadata->getStrPath());
        if($objHandler->isRemovable())
            $objHandler->remove($strLog);

        class_orm_base::setObjHandleLogicalDeletedGlobal(class_orm_deletedhandling_enum::EXCLUDED);

        class_resourceloader::getInstance()->flushCache();

        return $strLog;
    }



}