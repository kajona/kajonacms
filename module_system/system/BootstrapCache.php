<?php
/*"******************************************************************************************************
*   (c) 2015 by Kajona, www.kajona.de                                                              *
*       Published under the GNU LGPL v2.1, see /system/licence_lgpl.txt                                 *
********************************************************************************************************/


namespace Kajona\System\System;



/**
 * The bootstrap cache is used by various kernel-components of the system in order to
 * cache lookups and path-resolvings.
 * There's no usecase to access this cache by other components, so leave this cache "as is" and feel happy.
 *
 * @author sidler@mulchprod.de
 * @since 5.0
 */
class BootstrapCache
{

    const CACHE_TEMPLATES = "templates";
    const CACHE_FOLDERCONTENT = "foldercontent";
    const CACHE_PHARCONTENT = "pharcontent";
    const CACHE_LANG = "lang";
    const CACHE_MODULES = "modules";
    const CACHE_PHARMODULES = "pharmodules";
    const CACHE_PHARSUMS = "pharsums";
    const CACHE_CLASSES = "classes";
    const CACHE_REFLECTION = "reflection";
    const CACHE_OBJECTS = "objects";


    /**
     * @var BootstrapCache
     */
    private static $objInstance = null;

    private static $arrCaches = array();
    private static $arrCacheSavesRequired = array();


    private function getCacheNames()
    {
        return array(
            self::CACHE_TEMPLATES,
            self::CACHE_FOLDERCONTENT,
            self::CACHE_PHARCONTENT,
            self::CACHE_LANG,
            self::CACHE_MODULES,
            self::CACHE_PHARMODULES,
            self::CACHE_CLASSES,
            self::CACHE_PHARSUMS,
            self::CACHE_REFLECTION,
            self::CACHE_OBJECTS
        );
    }


    /**
     * BootstrapCache constructor.
     */
    private function __construct()
    {
        require_once __DIR__ . "/CacheManager.php";

        foreach($this->getCacheNames() as $strOneType) {
            self::$arrCaches[$strOneType] = CacheManager::getInstance()->getValue(__CLASS__.$strOneType, CacheManager::TYPE_FILESYSTEM);
        }
    }

    public function __destruct()
    {
        foreach($this->getCacheNames() as $strOneType) {
            if(isset(self::$arrCacheSavesRequired[$strOneType]) && Config::getInstance()->getConfig("bootstrapcache_".$strOneType) === true && isset(self::$arrCaches[$strOneType])) {
                CacheManager::getInstance()->addValue(__CLASS__.$strOneType, self::$arrCaches[$strOneType], 0, CacheManager::TYPE_FILESYSTEM);
            }
        }
    }

    public static function getInstance()
    {
        if(self::$objInstance == null) {
            self::$objInstance = new BootstrapCache();
        }

        return self::$objInstance;
    }

    public function updateCache($strCacheIdentifier, array $arrContent)
    {
        self::$arrCaches[$strCacheIdentifier] = $arrContent;
        self::$arrCacheSavesRequired[$strCacheIdentifier] = true;
    }

    public function addCacheRow($strCacheIdentifier, $strKey, $strValue)
    {
        self::$arrCaches[$strCacheIdentifier][$strKey] = $strValue;
        self::$arrCacheSavesRequired[$strCacheIdentifier] = true;
    }

    public function removeCacheRow($strCacheIdentifier, $strKey)
    {
        unset(self::$arrCaches[$strCacheIdentifier][$strKey]);
        self::$arrCacheSavesRequired[$strCacheIdentifier] = true;
    }

    /**
     * @param $strCacheIdentifier
     * @param $strKey
     *
     * @return bool|mixed
     */
    public function getCacheRow($strCacheIdentifier, $strKey)
    {
        if(isset(self::$arrCaches[$strCacheIdentifier][$strKey])) {
            return self::$arrCaches[$strCacheIdentifier][$strKey];
        }
        else {
            return false;
        }
    }

    /**
     * Returns the content of a cache.
     * @param $strCacheIdentifier
     *
     * @return mixed
     */
    public function getCacheContent($strCacheIdentifier)
    {
        if(isset(self::$arrCaches[$strCacheIdentifier])) {
            return self::$arrCaches[$strCacheIdentifier];
        }
        else {
            return false;
        }
    }


    public function flushCache()
    {
        CacheManager::getInstance()->flushCache();
        self::$arrCaches = array();
    }
}

