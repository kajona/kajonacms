<?php
/*"******************************************************************************************************
*   (c) 2015 by Kajona, www.kajona.de                                                              *
*       Published under the GNU LGPL v2.1, see /system/licence_lgpl.txt                                 *
********************************************************************************************************/


namespace Kajona\System\System;

use class_apc_cache;
use class_filesystem;

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

    const CACHE_TEMPLATES = "templates.cache";
    const CACHE_FOLDERCONTENT = "foldercontent.cache";
    const CACHE_PHARCONTENT = "pharcontent.cache";
    const CACHE_LANG = "lang.cache";
    const CACHE_MODULES = "modules.cache";
    const CACHE_PHARMODULES = "pharmodules.cache";
    const CACHE_PHARSUMS = "pharsums.cache";
    const CACHE_CLASSES = "classes.cache";
    const CACHE_REFLECTION = "reflection.cache";


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
            self::CACHE_REFLECTION
        );
    }


    /**
     * BootstrapCache constructor.
     */
    private function __construct()
    {

        include_once(__DIR__."/class_apc_cache.php");

        foreach($this->getCacheNames() as $strOneFile) {
            self::$arrCaches[$strOneFile] = class_apc_cache::getInstance()->getValue(__CLASS__.$strOneFile);

            if(self::$arrCaches[$strOneFile] === false) {
                if(is_file(_realpath_."/project/temp/".$strOneFile)) {
                    self::$arrCaches[$strOneFile] = unserialize(file_get_contents(_realpath_."/project/temp/".$strOneFile));
                }
                else {
                }

            }
        }

    }

    public function __destruct()
    {
        foreach($this->getCacheNames() as $strOneFile) {
            if(isset(self::$arrCacheSavesRequired[$strOneFile])) {
                class_apc_cache::getInstance()->addValue(__CLASS__.$strOneFile, self::$arrCaches[$strOneFile]);
                file_put_contents(_realpath_."/project/temp/".$strOneFile, serialize(self::$arrCaches[$strOneFile]));
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
        $objFilesystem = new class_filesystem();
        foreach($this->getCacheNames() as $strOneFile) {
            $objFilesystem->fileDelete("/project/temp/".$strOneFile);
        }

        self::$arrCaches = array();
        class_apc_cache::getInstance()->flushCache();
    }
}

