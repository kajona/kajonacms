<?php
/*"******************************************************************************************************
*   (c) 2004-2006 by MulchProductions, www.mulchprod.de                                                 *
*   (c) 2007-2015 by Kajona, www.kajona.de                                                              *
*       Published under the GNU LGPL v2.1, see /system/licence_lgpl.txt                                 *
*-------------------------------------------------------------------------------------------------------*
*	$Id$                                            *
********************************************************************************************************/

namespace Kajona\System\System;

use Kajona\System\Admin\ToolkitAdmin;
use Kajona\System\Portal\ToolkitPortal;
use Kajona\System\System\CacheManager;

/**
 * Heart of the system - granting access to all needed objects e.g. the database or the session-object
 *
 * @package module_system
 * @author sidler@mulchprod.de
 */
class Carrier
{

    const INT_CACHE_TYPE_DBQUERIES = 2;
    const INT_CACHE_TYPE_DBSTATEMENTS = 4;
    const INT_CACHE_TYPE_DBTABLES = 256;
    const INT_CACHE_TYPE_ORMCACHE = 8;
    const INT_CACHE_TYPE_OBJECTFACTORY = 16;
    const INT_CACHE_TYPE_MODULES = 32;
    const INT_CACHE_TYPE_CLASSLOADER = 64;
    const INT_CACHE_TYPE_APC = 128;
    const INT_CACHE_TYPE_CHANGELOG = 512;


    /**
     * Internal array of all params passed globally to the script
     *
     * @var array
     */
    private static $arrParams = null;

    /**
     * Current instance
     *
     * @var Carrier
     */
    private static $objCarrier = null;


    private $objContainer;

    /**
     * Constructor for Carrier, doing nothing important,
     * but being private ;), so use getInstance() instead
     */
    private function __construct()
    {
        // create the global DI container
        $this->boot();
    }

    /**
     * Method to get an instance of Carrier though the constructor is private
     *
     * @return Carrier
     */
    public static function getInstance()
    {

        if (self::$objCarrier == null) {
            self::$objCarrier = new Carrier();
        }

        return self::$objCarrier;
    }

    /**
     * Managing access to the database object. Use ONLY this method to
     * get an instance!
     *
     * @return Database
     */
    public function getObjDB()
    {
        return $this->objContainer['db'];
    }


    /**
     * Managing access to the rights object. Use ONLY this method to
     * get an instance!
     *
     * @return Rights
     */
    public function getObjRights()
    {
        return $this->objContainer['rights'];
    }

    /**
     * Managing access to the config object. Use ONLY this method to
     * get an instance!
     *
     * @return Config
     */
    public function getObjConfig()
    {
        return $this->objContainer['config'];
    }

    /**
     * Managing access to the session object. Use ONLY this method to
     * get an instance!
     *
     * @return Session
     */
    public function getObjSession()
    {
        return $this->objContainer['session'];
    }


    /**
     * Managing access to the template object. Use ONLY this method to
     * get an instance!
     *
     * @return Template
     */
    public function getObjTemplate()
    {
        return $this->objContainer['template'];
    }

    /**
     * Managing access to the text object. Use ONLY this method to
     * get an instance!
     *
     * @return Lang
     */
    public function getObjLang()
    {
        return $this->objContainer['lang'];
    }


    /**
     * Managing access to the toolkit object. Use ONLY this method to
     * get an instance!
     *
     * @param string $strArea
     *
     * @return ToolkitAdmin|ToolkitPortal
     */
    public function getObjToolkit($strArea)
    {
        if ($strArea == "admin") {
            return $this->objContainer['admintoolkit'];
        }
        elseif ($strArea == "portal") {
            return $this->objContainer['portaltoolkit'];
        }
        return null;
    }

    /**
     * Returns all params passed to the system, including $_GET, $_POST; $_FILES
     * This array may be modified, changes made are available during the whole request!
     *
     * @return array
     */
    public static function getAllParams()
    {
        self::initParamsArray();
        return self::$arrParams;
    }

    /**
     * Writes a param to the current set of params sent with the current requests.
     *
     * @param string $strKey
     * @param mixed $strValue
     *
     * @return void
     */
    public function setParam($strKey, $strValue)
    {
        self::initParamsArray();
        self::$arrParams[$strKey] = $strValue;
    }

    /**
     * Returns the value of a param sent with the current request.
     *
     * @param string $strKey
     *
     * @return mixed
     */
    public function getParam($strKey)
    {
        self::initParamsArray();
        return (isset(self::$arrParams[$strKey]) ? self::$arrParams[$strKey] : "");
    }

    /**
     * Returns the value of a param sent with the current request.
     *
     * @param string $strKey
     *
     * @return bool
     */
    public function issetParam($strKey)
    {
        self::initParamsArray();
        return isset(self::$arrParams[$strKey]);
    }

    /**
     * Internal helper, loads and merges all params passed with the current request.
     *
     * @static
     * @return void
     */
    private static function initParamsArray()
    {
        if (self::$arrParams === null) {
            self::$arrParams = array_merge(getArrayGet(), getArrayPost(), getArrayFiles());
        }
    }

    /**
     * A general helper to flush the systems various caches.
     *
     * @param int $intCacheType A bitmask of caches to be flushed, e.g. Carrier::INT_CACHE_TYPE_DBQUERIES | Carrier::INT_CACHE_TYPE_ORMCACHE
     */
    public function flushCache($intCacheType = 0)
    {

        if ($intCacheType & self::INT_CACHE_TYPE_DBQUERIES) {
            $this->getObjDB()->flushQueryCache();
        }

        if ($intCacheType & self::INT_CACHE_TYPE_DBSTATEMENTS) {
            $this->getObjDB()->flushPreparedStatementsCache();
        }

        if ($intCacheType & self::INT_CACHE_TYPE_DBTABLES) {
            $this->getObjDB()->flushTablesCache();
        }

        if ($intCacheType & self::INT_CACHE_TYPE_ORMCACHE) {
            OrmRowcache::flushCache();
        }

        if ($intCacheType & self::INT_CACHE_TYPE_OBJECTFACTORY) {
            Objectfactory::getInstance()->flushCache();
        }

        if ($intCacheType & self::INT_CACHE_TYPE_MODULES) {
            SystemModule::flushCache();
        }

        if ($intCacheType & self::INT_CACHE_TYPE_CLASSLOADER) {
            Classloader::getInstance()->flushCache();
        }

        if ($intCacheType & self::INT_CACHE_TYPE_APC) {
            CacheManager::getInstance()->flushCache();
        }

        if ($intCacheType & self::INT_CACHE_TYPE_CHANGELOG) {
            $objChangelog = new SystemChangelog();
            $objChangelog->processCachedInserts();
        }

    }

    /**
     * @return \Pimple\Container
     */
    public function getContainer()
    {
        return $this->objContainer;
    }

    /**
     * Creates a new DI container and register the system services
     */
    public function boot()
    {
        // we include the system autoloader so that we can load all core dependencies
        require_once __DIR__."/../vendor/autoload.php";

        $this->objContainer = new \Pimple\Container();
    }

}

//startup the system....
Carrier::getInstance();
