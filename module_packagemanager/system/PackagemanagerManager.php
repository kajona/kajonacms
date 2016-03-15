<?php
/*"******************************************************************************************************
*   (c) 2007-2016 by Kajona, www.kajona.de                                                              *
*       Published under the GNU LGPL v2.1, see /system/licence_lgpl.txt                                 *
*-------------------------------------------------------------------------------------------------------*
*	$Id$                                  *
********************************************************************************************************/

namespace Kajona\Packagemanager\System;

use Kajona\Packagemanager\System\Messageproviders\MessageproviderPackageupdate;
use Kajona\System\System\CacheManager;
use Kajona\System\System\Carrier;
use Kajona\System\System\Classloader;
use Kajona\System\System\Config;
use Kajona\System\System\Exception;
use Kajona\System\System\Logger;
use Kajona\System\System\MessagingMessage;
use Kajona\System\System\MessagingMessagehandler;
use Kajona\System\System\OrmBase;
use Kajona\System\System\OrmDeletedhandlingEnum;
use Kajona\System\System\Reflection;
use Kajona\System\System\Resourceloader;
use Kajona\System\System\SystemSetting;
use Kajona\System\System\UserGroup;
use Kajona\System\System\Zip;


/**
 * Central class to access the package-management subsystem.
 *
 * @author sidler@mulchprod.de
 * @since 4.0
 * @package module_packagemanager
 */
class PackagemanagerManager
{

    const STR_TYPE_MODULE = "MODULE";
    const STR_TYPE_TEMPLATE = "TEMPLATE";
    /** @deprecated */
    const STR_TYPE_ELEMENT = "ELEMENT";


    public static $arrLatestVersion = null;

    /**
     * Queries the local filesystem in order to find all packages available.
     * This may include packages of all providers.
     * Optionally you may reduce the list of packages using a simple filter-string
     *
     * @param string $strFilterText
     *
     * @return PackagemanagerMetadata[]
     */
    public function getAvailablePackages($strFilterText = "")
    {
        $arrReturn = array();

        $objModuleProvider = new PackagemanagerPackagemanagerModule();
        $arrReturn = array_merge($arrReturn, $objModuleProvider->getInstalledPackages());

        $objPackageProvider = new PackagemanagerPackagemanagerTemplate();
        $arrReturn = array_merge($objPackageProvider->getInstalledPackages(), $arrReturn);

        if ($strFilterText != "") {
            $arrReturn = array_filter($arrReturn, function ($objOneMetadata) use ($strFilterText) {
                return uniStrpos($objOneMetadata->getStrTitle(), $strFilterText) !== false;
            });
        }

        return $arrReturn;
    }

    /**
     * Sorts the array of packages ordered by the installation state, the type and the title
     *
     * @param PackagemanagerMetadata[] $arrPackages
     * @param bool $bitByNameOnly
     *
     * @return PackagemanagerMetadata[]
     */
    public function sortPackages(array $arrPackages, $bitByNameOnly = false)
    {
        $objManager = new PackagemanagerManager();
        usort($arrPackages, function (PackagemanagerMetadata $objA, PackagemanagerMetadata $objB) use ($bitByNameOnly, $objManager) {

            $objHandlerA = $objManager->getPackageManagerForPath($objA->getStrPath());
            $objHandlerB = $objManager->getPackageManagerForPath($objB->getStrPath());

            if ($bitByNameOnly) {
                return strcmp($objA->getStrTitle(), $objB->getStrTitle());
            }

            if ($objA->getStrType() == PackagemanagerManager::STR_TYPE_TEMPLATE && $objB->getStrType() != PackagemanagerManager::STR_TYPE_TEMPLATE) {
                return -1;
            }
            elseif ($objA->getStrType() != PackagemanagerManager::STR_TYPE_TEMPLATE && $objB->getStrType() == PackagemanagerManager::STR_TYPE_TEMPLATE) {
                return 1;
            }

            if ($objHandlerA->isInstallable() && $objHandlerB->isInstallable()) {
                return strcmp($objA->getStrTitle(), $objB->getStrTitle());
            }

            if ($objHandlerA->isInstallable() && !$objHandlerB->isInstallable()) {
                return -1;
            }

            if (!$objHandlerA->isInstallable() && $objHandlerB->isInstallable()) {
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
     * @return PackagemanagerMetadata|null
     */
    public function getPackage($strName)
    {
        $arrAvailable = $this->getAvailablePackages();
        foreach ($arrAvailable as $objOnePackage) {
            if ($objOnePackage->getStrTitle() == $strName) {
                return $objOnePackage;
            }
        }

        return null;
    }

    /**
     * Loads the matching packagemanager for a given path.
     *
     * @param string $strPath
     *
     * @return PackagemanagerPackagemanagerInterface|null
     */
    public function getPackageManagerForPath($strPath)
    {
        $objMetadata = new PackagemanagerMetadata();
        $objMetadata->autoInit($strPath);

        $objManager = null;

        if ($objMetadata->getStrType() == self::STR_TYPE_MODULE || $objMetadata->getStrType() == self::STR_TYPE_ELEMENT) {
            if ($objMetadata->getBitIsPhar()) {
                $objManager = new PackagemanagerPackagemanagerPharmodule();
            }
            else {
                $objManager = new PackagemanagerPackagemanagerModule();
            }

            $objManager->setObjMetadata($objMetadata);
        }

        if ($objMetadata->getStrType() == self::STR_TYPE_TEMPLATE) {
            $objManager = new PackagemanagerPackagemanagerTemplate();
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
     * @deprecated
     *
     * @return PackagemanagerPackagemanagerInterface
     */
    public function extractPackage($strPackagePath)
    {
        $strTargetFolder = generateSystemid();

        Logger::getInstance(Logger::PACKAGEMANAGEMENT)->addLogRow("extracting package ".$strPackagePath." to "._projectpath_."/temp/".$strTargetFolder, Logger::$levelInfo);

        $objZip = new Zip();
        $objZip->extractArchive($strPackagePath, _projectpath_."/temp/".$strTargetFolder);

        return $this->getPackageManagerForPath(_projectpath_."/temp/".$strTargetFolder);
    }

    /**
     * Returns all content-providers as configured in the /config/packagemanager.php file.
     *
     * @return PackagemanagerContentproviderInterface[]
     */
    public function getContentproviders()
    {
        $objConfig = Config::getInstance("module_packagemanager");

        $strProvider = $objConfig->getConfig("contentproviders");

        $arrProviders = explode(",", $strProvider);
        $arrReturn = array();
        foreach ($arrProviders as $strOneProvider) {
            $strOneProvider = trim($strOneProvider);
            if ($strOneProvider != "") {
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
    public function validatePackage($strPath)
    {
        try {
            $objMetadata = new PackagemanagerMetadata();
            $objMetadata->autoInit($strPath);
            return true;
        }
        catch (Exception $objEx) {

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
    public function scanForUpdates()
    {

        $objManager = new PackagemanagerManager();
        $arrVersions = $objManager->getArrLatestVersion();

        foreach ($arrVersions as $strOneModule => $strOneVersion) {
            $objMetadata = $objManager->getPackage($strOneModule);
            if ($objMetadata != null) {
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
    private function getArrLatestVersion()
    {
        $arrPackages = $this->getAvailablePackages();

        $arrQueries = array();
        foreach ($arrPackages as $objOneMetadata) {
            $arrQueries[$objOneMetadata->getStrTitle()] = $objOneMetadata;
        }

        $arrResult = array();
        $arrProvider = $this->getContentproviders();

        foreach ($arrProvider as $objOneProvider) {
            $arrRemoteVersions = $objOneProvider->searchPackage(implode(",", array_keys($arrQueries)));
            if (!is_array($arrRemoteVersions)) {
                continue;
            }

            foreach ($arrRemoteVersions as $arrOneRemotePackage) {
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
     * @param PackagemanagerMetadata $objMetadata
     *
     * @return string[]
     */
    public function getArrRequiredBy(PackagemanagerMetadata $objMetadata)
    {
        $arrReturn = array();
        foreach ($this->getAvailablePackages() as $objOnePackage) {
            foreach ($objOnePackage->getArrRequiredModules() as $strModule => $strVersion) {
                if ($strModule == $objMetadata->getStrTitle()) {
                    $arrReturn[] = $objOnePackage->getStrTitle();
                }
            }
        }

        return $arrReturn;
    }


    /**
     * Validates a packages' latest version and compares it to the version currently installed.
     * Optionally, a version to compare may be passed.
     *
     * @param PackagemanagerPackagemanagerInterface $objPackage
     * @param string $strVersionToCompare
     *
     * @return bool or null of the package could not be found
     */
    public function updateAvailable(PackagemanagerPackagemanagerInterface $objPackage, $strVersionToCompare = "")
    {

        if ($strVersionToCompare === "") {
            $arrRemotePackages = $this->getArrLatestVersion();
            if (isset($arrRemotePackages[$objPackage->getObjMetadata()->getStrTitle()])) {
                $strLatestVersion = $arrRemotePackages[$objPackage->getObjMetadata()->getStrTitle()];
            }
            else {
                $strLatestVersion = null;
            }
        }
        else {
            $strLatestVersion = $strVersionToCompare;
        }

        if ($strLatestVersion !== null) {
            if ($strLatestVersion != null && version_compare($strLatestVersion, $objPackage->getObjMetadata()->getStrVersion(), ">")) {
                Logger::getInstance(Logger::PACKAGEMANAGEMENT)->addLogRow(
                    "found update for package ".$objPackage->getObjMetadata()->getStrTitle().", installed: ".$objPackage->getObjMetadata()->getStrVersion()." available: ".$strLatestVersion, Logger::$levelInfo
                );

                $this->sendUpdateAvailableMessage($objPackage, $strLatestVersion);

                return true;
            }
            else {
                return false;
            }
        }

        return null;
    }

    /**
     * @param PackagemanagerPackagemanagerInterface $objPackage
     * @param string $strLatestVersion
     *
     * @return void
     */
    private function sendUpdateAvailableMessage(PackagemanagerPackagemanagerInterface $objPackage, $strLatestVersion)
    {
        //check, if not already sent
        $strIdentifier = sha1(__CLASS__.$objPackage->getObjMetadata()->getStrTitle().$strLatestVersion);

        if (count(MessagingMessage::getMessagesByIdentifier($strIdentifier)) == 0) {

            $strMailtext = Carrier::getInstance()->getObjLang()->getLang("update_notification_package", "packagemanager")." ".$objPackage->getObjMetadata()->getStrTitle()."\n";
            $strMailtext .= Carrier::getInstance()->getObjLang()->getLang("update_notification_verinst", "packagemanager")." ".$objPackage->getObjMetadata()->getStrVersion()."\n";
            $strMailtext .= Carrier::getInstance()->getObjLang()->getLang("update_notification_verav", "packagemanager")." ".$strLatestVersion."\n";

            $objMessageHandler = new MessagingMessagehandler();
            $objMessage = new MessagingMessage();
            $objMessage->setStrTitle(Carrier::getInstance()->getObjLang()->getLang("update_notification_intro", "packagemanager"));
            $objMessage->setStrBody($strMailtext);
            $objMessage->setObjMessageProvider(new MessageproviderPackageupdate());
            $objMessage->setStrInternalIdentifier($strIdentifier);
            $objMessageHandler->sendMessageObject($objMessage, new UserGroup(SystemSetting::getConfigValue("_admins_group_id_")));
        }
    }

    /**
     * Triggers the update of the passed package.
     * It is evaluated, if a new version is available.
     * The provider itself is called via initPackageUpdate, so it's to providers choice
     * to decide what action to take.
     *
     * @param PackagemanagerPackagemanagerInterface $objPackage
     *
     * @throws Exception
     * @return mixed
     */
    public function updatePackage(PackagemanagerPackagemanagerInterface $objPackage)
    {
        $arrProvider = $this->getContentproviders();

        foreach ($arrProvider as $objOneProvider) {
            $arrModule = $objOneProvider->searchPackage($objPackage->getObjMetadata()->getStrTitle());

            if (count($arrModule) == 1) {
                $arrModule = $arrModule[0];
            }


            if ($arrModule != null && isset($arrModule["title"]) && $arrModule["title"] == $objPackage->getObjMetadata()->getStrTitle()) {
                $objOneProvider->initPackageUpdate($arrModule["title"]);
                break;
            }

        }

        return "Error loading metainformation for package ".$objPackage->getObjMetadata()->getStrTitle();
    }


    /**
     * @param PackagemanagerMetadata $objMetadata
     *
     * @throws Exception
     * @return string
     */
    public function removePackage(PackagemanagerMetadata $objMetadata)
    {

        $strLog = "";

        OrmBase::setObjHandleLogicalDeletedGlobal(OrmDeletedhandlingEnum::INCLUDED);
        $objHandler = $this->getPackageManagerForPath($objMetadata->getStrPath());
        if ($objHandler->isRemovable()) {
            $objHandler->remove($strLog);
        }

        OrmBase::setObjHandleLogicalDeletedGlobal(OrmDeletedhandlingEnum::EXCLUDED);

        Classloader::getInstance()->flushCache();
        Reflection::flushCache();
        Resourceloader::getInstance()->flushCache();
        CacheManager::getInstance()->flushCache(null, CacheManager::NS_BOOTSTRAP);

        return $strLog;
    }


}