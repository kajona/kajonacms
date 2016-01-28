<?php
/*"******************************************************************************************************
*   (c) 2015 by Kajona, www.kajona.de                                                              *
*       Published under the GNU LGPL v2.1, see /system/licence_lgpl.txt                                 *
********************************************************************************************************/

namespace Kajona\System\System;

use Doctrine\Common\Cache\ApcCache;
use Doctrine\Common\Cache\ApcuCache;
use Doctrine\Common\Cache\ArrayCache;
use Doctrine\Common\Cache\CacheProvider;
use Doctrine\Common\Cache\ChainCache;
use Doctrine\Common\Cache\FilesystemCache;
use Doctrine\Common\Cache\FlushableCache;
use Doctrine\Common\Cache\PhpFileCache;

/**
 * Cache manager which can store and retrieve values from different cache systems. The API is compatible to the
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
     * Uses the class_cache to store the data in a database table
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
     * @return mixed
     */
    public function getValue($strKey, $intType = null)
    {
        return $this->getCache($intType)->fetch($strKey);
    }

    /**
     * @param string $strKey
     * @param mixed $objValue
     * @param int $intTtl
     * @param int $intType
     * @return bool
     */
    public function addValue($strKey, $objValue, $intTtl = 180, $intType = null)
    {
        return $this->getCache($intType)->save($strKey, $objValue, $intTtl);
    }

    /**
     * @param string $strKey
     * @param int $intType
     * @return bool
     */
    public function removeValue($strKey, $intType = null)
    {
        return $this->getCache($intType)->delete($strKey);
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
     * Returns a specific cache system
     *
     * @param integer $intType
     * @return \Doctrine\Common\Cache\Cache
     */
    protected function getCache($intType = null)
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

    protected function buildDriver($intType)
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
            $arrDriver[] = new FilesystemCache(_realpath_ . "/project/temp/cache", ".cache");
        }

        if ($intType & self::TYPE_PHPFILE) {
            $arrDriver[] = new PhpFileCache(_realpath_ . "/project/temp/cache", ".cache.php");
        }

        $objCache = null;
        if (count($arrDriver) == 1) {
            $objCache = current($arrDriver);
        } elseif (count($arrDriver) > 1) {
            $objCache = new ChainCache($arrDriver);
        } else {
            throw new \class_exception("Invalid cache type", \class_exception::$level_ERROR);
        }

        if ($objCache instanceof CacheProvider) {
            $objCache->setNamespace($this->strSystemKey);
        }

        return $objCache;
    }

    /**
     * @deprecated if possible use the cache manager instance from the DI container "@Inject cache_manager"
     * @return CacheManager
     */
    public static function getInstance()
    {
        if(self::$objInstance == null) {
            self::$objInstance = new self();
        }

        return self::$objInstance;
    }
}

