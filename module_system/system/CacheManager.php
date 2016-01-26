<?php
/*"******************************************************************************************************
*   (c) 2015 by Kajona, www.kajona.de                                                              *
*       Published under the GNU LGPL v2.1, see /system/licence_lgpl.txt                                 *
********************************************************************************************************/

namespace Kajona\System\System;

use Doctrine\Common\Cache\ApcCache;
use Doctrine\Common\Cache\ArrayCache;
use Doctrine\Common\Cache\ChainCache;
use Doctrine\Common\Cache\FilesystemCache;
use Doctrine\Common\Cache\FlushableCache;
use Doctrine\Common\Cache\PhpFileCache;

/**
 * Cache manager which can store and retriefe values from different cache systems. The API is compatible to the
 * class_apc_cache but it is possible to specifiy different cache systems.
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
 * It is also possible to specify specific cache types. I.e. if you want to store your data per APC and on the
 * filesystem. The cache types are sorted after speed that means the fastest cache is always applied first
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
     * @var CacheManager
     */
    private static $objInstance = null;

    /**
     * Stores the data in an array
     *
     * @var integer
     */
    const TYPE_ARRAY = 1;

    /**
     * Uses the APC functions if available to store the data
     *
     * @var integer
     */
    const TYPE_APC = 2;

    /**
     * Stores the data in the _cache table
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
     * Stores the data in a PHP file where the data was exported through var_export. Might be faster then the
     * filesystem type which serializes the data. For PHP >= 5.5 the internal opcode cache is also used
     *
     * @var integer
     */
    const TYPE_PHPFILE = 16;

    /**
     * @var array
     */
    protected $arrSystems = array();

    /**
     * Returns a specific cache system
     *
     * @param integer $intType
     * @return \Doctrine\Common\Cache\Cache
     */
    public function getCache($intType = null)
    {
        if ($intType === null) {
            $intType = self::TYPE_APC | self::TYPE_FILESYSTEM;
        }

        if (isset($this->arrSystems[$intType])) {
            return $this->arrSystems[$intType];
        } else {
            return $this->arrSystems[$intType] = $this->buildDriver($intType);
        }
    }

    /**
     * @param $strKey
     * @param integer $intType
     * @return mixed
     */
    public function getValue($strKey, $intType = null)
    {
        return $this->getCache($intType)->fetch($strKey);
    }

    /**
     * Flushes the complete cache if its supported
     *
     * @param integer $intType
     */
    public function flushCache($intType = null)
    {
        $objCache = $this->getCache($intType);
        if ($objCache instanceof FlushableCache) {
            $objCache->flushAll();
        }
    }

    /**
     * @param $strKey
     * @param $objValue
     * @param int $intTtl
     * @param integer $intType
     * @return bool
     */
    public function addValue($strKey, $objValue, $intTtl = 180, $intType = null)
    {
        return $this->getCache($intType)->save($strKey, $objValue, $intTtl);
    }

    protected function buildDriver($intType)
    {
        require_once _realpath_ . '/core/module_system/vendor/autoload.php';
        $arrDriver = array();

        if ($intType & self::TYPE_ARRAY) {
            $arrDriver[] = new ArrayCache();
        }

        if ($intType & self::TYPE_APC) {
            if (function_exists("apc_cache_info") && @apc_cache_info() !== false) {
                $arrDriver[] = new ApcCache();
            } elseif (!($intType & self::TYPE_ARRAY)) {
                // in case we have no APC use a simple array cache but only if we have not already added a array cache
                $arrDriver[] = new ArrayCache();
            }
        }

        if ($intType & self::TYPE_DATABASE) {
            $arrDriver[] = new CacheDatabase();
        }

        if ($intType & self::TYPE_FILESYSTEM) {
            $arrDriver[] = new FilesystemCache(_realpath_ . "/project/temp/cache", ".cache");
        }

        if ($intType & self::TYPE_PHPFILE) {
            $arrDriver[] = new PhpFileCache(_realpath_ . "/project/temp/cache", ".cache.php");
        }

        if (count($arrDriver) == 1) {
            return current($arrDriver);
        } elseif (count($arrDriver) > 1) {
            return new ChainCache($arrDriver);
        } else {
            throw new \class_exception("Invalid cache type", \class_exception::$level_ERROR);
        }
    }

    public static function getInstance()
    {
        if(self::$objInstance == null) {
            self::$objInstance = new self();
        }

        return self::$objInstance;
    }
}

