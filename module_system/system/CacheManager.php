<?php
/*"******************************************************************************************************
*   (c) 2015-2016 by Kajona, www.kajona.de                                                         *
*       Published under the GNU LGPL v2.1, see /system/licence_lgpl.txt                                 *
********************************************************************************************************/

namespace Kajona\System\System;

use Doctrine\Common\Cache\ApcCache;
use Doctrine\Common\Cache\ApcuCache;
use Doctrine\Common\Cache\ArrayCache;
use Doctrine\Common\Cache\CacheProvider;
use Doctrine\Common\Cache\ChainCache;
use Doctrine\Common\Cache\ClearableCache;
use Doctrine\Common\Cache\FilesystemCache;
use Doctrine\Common\Cache\FlushableCache;
use Doctrine\Common\Cache\PhpFileCache;

/**
 * Cache manager which can store and retrieve values from different cache systems. The API is compatible to the
 * ApcCache but it is possible to specifiy different cache systems.
 *
 * <code>
 * $strData = CacheManager::getInstance()->getValue('[key]');
 *
 * if ($strData !== false) {
 *      return $strData;
 * } else {
 *      $strData = complexTask();
 *
 *      CacheManager::getInstance()->addValue('[key]', $strData);
 * }
 * </code>
 *
 * It is also possible to specify specific cache types. I.e. if you want to store your data per APC and also on the
 * filesystem. In this case the system checks first whether an entry is available in APC and then the database
 * <code>
 * $objCache = CacheManager::getInstance()->getCache(CacheManager::TYPE_APC | CacheManager::TYPE_DATABASE);
 * </code>
 *
 * @author christoph.kappestein@gmail.com
 * @since 5.0
 */
class CacheManager
{
    /**
     * Stores the data in an array
     *
     * @var integer
     */
    const TYPE_ARRAY = 1;

    /**
     * Uses the APC functions if available to store the data. If not available an array type is used
     *
     * @var integer
     */
    const TYPE_APC = 2;

    /**
     * Uses the Cache to store the data in a database table
     *
     * @var integer
     */
    const TYPE_DATABASE = 4;

    /**
     * Stores the data in the temp folder
     *
     * @var integer
     */
    const TYPE_FILESYSTEM = 8;

    /**
     * Stores the data in a PHP file where the data is exported through var_export. Might be faster then the filesystem
     * type. Note for PHP >= 5.5 the internal opcode cache is also used
     *
     * @var integer
     */
    const TYPE_PHPFILE = 16;

    /**
     * Namespace of the global cache. This cache is flushed more often in case changes happen to the db
     *
     * @var string
     */
    const NS_GLOBAL = 'global';

    /**
     * Namespace of the bootstrap cache. The bootstrap cache contains cache values which are rarely removed it contains
     * i.e. the class map or annotations values
     *
     * @var string
     */
    const NS_BOOTSTRAP = 'bootstrap';

    /**
     * @var array
     */
    protected $arrSystems = array();

    /**
     * @var string
     */
    protected $strSystemKey;

    /**
     * @var CacheManager
     */
    private static $objInstance = null;

    public function __construct()
    {
        $this->strSystemKey = md5(__FILE__);
    }

    /**
     * @param string $strKey
     * @param int $intType
     * @return mixed The cached data or FALSE, if no cache entry exists for the given id.
     */
    public function getValue($strKey, $intType = null, $strNamespace = self::NS_GLOBAL)
    {
        return $this->getCache($intType, $strNamespace)->fetch($strKey);
    }

    /**
     * @param string $strKey
     * @param mixed $objValue
     * @param int $intTtl The lifetime in number of seconds for this cache entry.
     *                         If zero (the default), the entry never expires (although it may be deleted from the cache
     *                         to make place for other entries).
     * @param int $intType
     * @return bool
     */
    public function addValue($strKey, $objValue, $intTtl = 180, $intType = null, $strNamespace = self::NS_GLOBAL)
    {
        return $this->getCache($intType, $strNamespace)->save($strKey, $objValue, $intTtl);
    }

    /**
     * @param string $strKey
     * @param int $intType
     * @return bool
     */
    public function removeValue($strKey, $intType = null, $strNamespace = self::NS_GLOBAL)
    {
        return $this->getCache($intType, $strNamespace)->delete($strKey);
    }

    /**
     * Flushes the complete cache for the given namespace. Note in most cases that does not delete the actual data
     * instead an internal version number is increased which is always appended to the cache key
     *
     * @param integer $intType
     * @param string $strNamespace
     */
    public function flushCache($intType = null, $strNamespace = self::NS_GLOBAL)
    {
        $objCache = $this->getCache($intType, $strNamespace);
        if ($objCache instanceof ClearableCache) {
            $objCache->deleteAll();
        }
    }

    /**
     * Removes the complete cache for a specific type. This actually deletes the data thus the method should be used
     * carefully
     *
     * @param integer $intType
     * @param string $strNamespace
     */
    public function flushAll($intType = null)
    {
        $arrTypes = self::getAvailableDriver();

        foreach ($arrTypes as $intKey => $strLabel) {
            if ($intType !== null && $intType != $intKey) {
                continue;
            }

            $objCache = $this->getCache($intKey, null);
            if ($objCache instanceof FlushableCache) {
                $objCache->flushAll();
            }
        }

        $this->arrSystems = array();
    }

    /**
     * Returns stats informations for a specific type
     *
     * @param integer $intType
     * @return array|null
     */
    public function getStats($intType)
    {
        return $this->getCache($intType, null)->getStats();
    }

    /**
     * Returns a specific cache system
     *
     * @param integer $intType
     * @return \Doctrine\Common\Cache\Cache
     */
    protected function getCache($intType, $strNamespace)
    {
        if (empty($intType)) {
            $intType = self::TYPE_APC | self::TYPE_FILESYSTEM;
        }

        $strKey = $intType . '-' . $strNamespace;
        if (isset($this->arrSystems[$strKey])) {
            return $this->arrSystems[$strKey];
        } else {
            return $this->arrSystems[$strKey] = $this->buildDriver($intType, $strNamespace);
        }
    }

    protected function buildDriver($intType, $strNamespace)
    {
        require_once __DIR__ . '/../vendor/autoload.php';
        $arrDriver = array();

        if ($intType & self::TYPE_ARRAY) {
            $arrDriver[] = new ArrayCache();
        }

        if ($intType & self::TYPE_APC) {
            if (function_exists("apcu_cache_info") && @apcu_cache_info() !== false) {
                $arrDriver[] = new ApcuCache();
            }
            elseif (function_exists("apc_cache_info") && @apc_cache_info() !== false) {
                $arrDriver[] = new ApcCache();
            }
            elseif (!($intType & self::TYPE_ARRAY)) {
                // in case we have no APC use a simple array cache but only if we have not already added a array cache
                $arrDriver[] = new ArrayCache();
            }
        }

        if ($intType & self::TYPE_DATABASE) {
            $arrDriver[] = new CacheDatabase();
        }

        if ($intType & self::TYPE_FILESYSTEM) {
            try {
                $arrDriver[] = new FilesystemCache(_realpath_."/project/temp/cache", ".cache");
            }
            catch(\InvalidArgumentException $objEx) {
                $arrDriver[] = new ArrayCache();
            }
        }

        if ($intType & self::TYPE_PHPFILE) {
            try {
                $arrDriver[] = new PhpFileCache(_realpath_."/project/temp/cache", ".cache.php");
            }
            catch(\InvalidArgumentException $objEx) {
                $arrDriver[] = new ArrayCache();
            }
        }

        $objCache = null;
        if (count($arrDriver) == 1) {
            $objCache = current($arrDriver);
        } elseif (count($arrDriver) > 1) {
            $objCache = new ChainCache($arrDriver);
        } else {
            throw new \Exception("Invalid cache type");
        }

        if ($objCache instanceof CacheProvider) {
            $objCache->setNamespace($this->strSystemKey . $strNamespace);
        }

        return $objCache;
    }

    /**
     * @deprecated if possible use the cache manager instance from the DI container "@inject system_cache_manager"
     * @return CacheManager
     */
    public static function getInstance()
    {
        if(self::$objInstance == null) {
            self::$objInstance = new self();
        }

        return self::$objInstance;
    }

    /**
     * Returns all available drivers which can be deleted through the system task
     *
     * @return array
     */
    public static function getAvailableDriver()
    {
        return array(
            self::TYPE_APC => "APC",
            self::TYPE_DATABASE => "Database",
            self::TYPE_FILESYSTEM => "Filesystem",
            self::TYPE_PHPFILE => "PHP-File",
        );
    }
}

