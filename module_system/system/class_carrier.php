<?php
/*"******************************************************************************************************
*   (c) 2004-2006 by MulchProductions, www.mulchprod.de                                                 *
*   (c) 2007-2015 by Kajona, www.kajona.de                                                              *
*       Published under the GNU LGPL v2.1, see /system/licence_lgpl.txt                                 *
*-------------------------------------------------------------------------------------------------------*
*	$Id$                                            *
********************************************************************************************************/

/**
 * Heart of the system - granting access to all needed objects e.g. the database or the session-object
 *
 * @package module_system
 * @author sidler@mulchprod.de
 */
class class_carrier
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

    private $objDB = null;
    private $objConfig = null;
    private $objSession = null;
    private $objTemplate = null;
    private $objRights = null;
    private $objLang = null;
    private $objToolkitAdmin = null;
    private $objToolkitPortal = null;

    /**
     * Current instance
     *
     * @var class_carrier
     */
    private static $objCarrier = null;

    /**
     * Constructor for class_carrier, doing nothing important,
     * but being private ;), so use getInstance() instead
     */
    private function __construct()
    {
    }

    /**
     * Method to get an instance of class_carrier though the constructor is private
     *
     * @return class_carrier
     */
    public static function getInstance()
    {

        if (self::$objCarrier == null) {
            self::$objCarrier = new class_carrier();
//            $objConfig = self::$objCarrier->getObjConfig();
//            $objDB = self::$objCarrier->getObjDB();
            //so, lets init the constants

            //and init the internal session
            //SIR 2010/03: deactivated session startup right here.
            //The session-start is handled by class_session internally to avoid
            //senseless db-updates, e.g. when manipulating images
            //self::$objCarrier->getObjSession()->initInternalSession();

            //include relevant classes for possible static init blocks
            class_classloader::getInstance()->includeClasses();
        }

        return self::$objCarrier;
    }

    /**
     * Managing access to the database object. Use ONLY this method to
     * get an instance!
     *
     * @return class_db
     */
    public function getObjDB()
    {
        //Do we have to generate the object?
        if ($this->objDB == null) {
            $this->objDB = class_db::getInstance();
            //Now we have to set up the database connection
            //SIR 2010/03: connection is established on request, lazy loading
            //$this->objDB->dbconnect();
        }
        return $this->objDB;
    }


    /**
     * Managing access to the rights object. Use ONLY this method to
     * get an instance!
     *
     * @return class_rights
     */
    public function getObjRights()
    {
        //Do we have to generate the object?
        if ($this->objRights == null) {
            $this->objRights = class_rights::getInstance();
        }
        return $this->objRights;
    }

    /**
     * Managing access to the config object. Use ONLY this method to
     * get an instance!
     *
     * @return class_config
     */
    public function getObjConfig()
    {
        //Do we have to generate the object?
        if ($this->objConfig == null) {
            $this->objConfig = class_config::getInstance();

            //Loading the config-Files
            //Invocation removed, all configs from database
            //$this->objConfig->loadConfigsFilesystem();
        }
        return $this->objConfig;
    }

    /**
     * Managing access to the session object. Use ONLY this method to
     * get an instance!
     *
     * @return class_session
     */
    public function getObjSession()
    {
        //Do we have to generate the object?
        if ($this->objSession == null) {
            $this->objSession = class_session::getInstance();
        }
        return $this->objSession;
    }


    /**
     * Managing access to the template object. Use ONLY this method to
     * get an instance!
     *
     * @return class_template
     */
    public function getObjTemplate()
    {
        //Do we have to generate the object?
        if ($this->objTemplate == null) {
            $this->objTemplate = class_template::getInstance();
        }
        return $this->objTemplate;
    }

    /**
     * Managing access to the text object. Use ONLY this method to
     * get an instance!
     *
     * @return class_lang
     */
    public function getObjLang()
    {
        //Do we have to generate the object?
        if ($this->objLang == null) {
            $this->objLang = class_lang::getInstance();
        }
        return $this->objLang;
    }


    /**
     * Managing access to the toolkit object. Use ONLY this method to
     * get an instance!
     *
     * @param string $strArea
     *
     * @return class_toolkit_admin|class_toolkit_portal
     */
    public function getObjToolkit($strArea)
    {
        //Do we have to generate the object?
        if ($strArea == "admin") {
            //Get the object
            if ($this->objToolkitAdmin == null) {
                //decide which class to load
                $strAdminToolkitClass = $this->getObjConfig()->getConfig("admintoolkit");
                if ($strAdminToolkitClass == "") {
                    $strAdminToolkitClass = "class_toolkit_admin";
                }

                $strPath = class_resourceloader::getInstance()->getPathForFile("/admin/".$strAdminToolkitClass.".php");
                include_once $strPath;

                $this->objToolkitAdmin = new $strAdminToolkitClass();
            }
            return $this->objToolkitAdmin;
        }
        elseif ($strArea == "portal") {
            if ($this->objToolkitPortal == null) {
                $strPath = class_resourceloader::getInstance()->getPathForFile("/portal/class_toolkit_portal.php");
                include_once $strPath;
                $this->objToolkitPortal = new class_toolkit_portal();
            }
            return $this->objToolkitPortal;
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
     * @param int $intCacheType A bitmask of caches to be flushed, e.g. class_carrier::INT_CACHE_TYPE_DBQUERIES | class_carrier::INT_CACHE_TYPE_ORMCACHE
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
            class_orm_rowcache::flushCache();
        }

        if ($intCacheType & self::INT_CACHE_TYPE_OBJECTFACTORY) {
            class_objectfactory::getInstance()->flushCache();
        }

        if ($intCacheType & self::INT_CACHE_TYPE_MODULES) {
            class_module_system_module::flushCache();
        }

        if ($intCacheType & self::INT_CACHE_TYPE_CLASSLOADER) {
            class_classloader::getInstance()->flushCache();
        }

        if ($intCacheType & self::INT_CACHE_TYPE_APC) {
            \Kajona\System\System\CacheManager::getInstance()->flushCache();
        }

        if ($intCacheType & self::INT_CACHE_TYPE_CHANGELOG) {
            $objChangelog = new class_module_system_changelog();
            $objChangelog->processCachedInserts();
        }

    }

}

//startup the system....
class_carrier::getInstance();