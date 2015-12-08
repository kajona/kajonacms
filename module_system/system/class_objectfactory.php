<?php
/*"******************************************************************************************************
*   (c) 2007-2015 by Kajona, www.kajona.de                                                              *
*       Published under the GNU LGPL v2.1, see /system/licence_lgpl.txt                                 *
*-------------------------------------------------------------------------------------------------------*
*   $Id$                                        *
********************************************************************************************************/
use Kajona\System\System\BootstrapCache;

/**
 * The objectfactory is a central place to create instances of common objects.
 * Therefore, a systemid is passed and the system returns the matching business object.
 *
 * Instantiations are cached, so recreating instances is a rather cheap operation.
 * To ensure a proper caching, the factory itself reflects the singleton pattern.
 *
 * In addition, common helper-methods regarding objects are placed right here.
 *
 * @package module_system
 * @author sidler@mulchprod.de
 * @since 4.0
 */
class class_objectfactory {

    /**
     * @var class_model[]
     */
    private $arrObjectCache = array();

    /**
     * @var class_db
     */
    private $objDB;

    /**
     * @var class_objectfactory
     */
    private static $objInstance = null;

    private $strObjectsCacheFile;
    private $bitCacheSaveRequired = false;

        /**
     * Returns an instance of the objectfactory.
     *
     * @static
     * @return class_objectfactory
     */
    public static function getInstance() {
        if(self::$objInstance == null)
            self::$objInstance = new class_objectfactory();

        return self::$objInstance;
    }


    /**
     * Creates a new object-instance. Therefore, the passed system-id
     * is searched in the cache, afterwards the instance is created - as long
     * as the matching class could be found, otherwise null
     *
     * @param string $strSystemid
     * @param bool $bitIgnoreCache
     * @return null|class_model|interface_model
     */
    public function getObject($strSystemid, $bitIgnoreCache = false) {

        if(!$bitIgnoreCache && isset($this->arrObjectCache[$strSystemid]))
            return $this->arrObjectCache[$strSystemid];

        $strClass = $this->getClassNameForId($strSystemid);
        
        //load the object itself
        if($strClass != "") {
            $objReflection = new ReflectionClass($strClass);
            $objObject = $objReflection->newInstance($strSystemid);
            $this->arrObjectCache[$strSystemid] = $objObject;
            return $objObject;
        }

        return null;
    }
    
    
    /**
     * Get the class name for a system-id.
     * 
     * @param string $strSystemid
     * @return string
     */
    public function getClassNameForId($strSystemid) {
        $strClass = "";
        if(BootstrapCache::getInstance()->getCacheRow(BootstrapCache::CACHE_OBJECTS, $strSystemid)) {
            return BootstrapCache::getInstance()->getCacheRow(BootstrapCache::CACHE_OBJECTS, $strSystemid);
        }

        //maybe the orm handler has already fetched this row
        $arrCacheRow = class_orm_rowcache::getCachedInitRow($strSystemid);
        if($arrCacheRow != null && isset($arrCacheRow["system_class"])) {
            $strClass = $arrCacheRow["system_class"];
        }
        else {
            $strQuery = "SELECT * FROM "._dbprefix_."system where system_id = ?";
            $arrRow = $this->objDB->getPRow($strQuery, array($strSystemid));
            if(isset($arrRow["system_class"])) {
                $strClass = $arrRow["system_class"];
            }
        }

        if($strClass != "") {
            BootstrapCache::getInstance()->addCacheRow(BootstrapCache::CACHE_OBJECTS, $strSystemid, $strClass);
        }

        return $strClass;
    }


    /**
     * Flushes the internal instance cache
     */
    public function flushCache() {
        $this->arrObjectCache = array();
    }

    /**
     * Removes a single entry from the instance cache
     * @param $strSystemid
     */
    public function removeFromCache($strSystemid) {
        unset($this->arrObjectCache[$strSystemid]);
    }

    /**
     * Adds a single object to the cache
     * @param class_model $objObject
     */
    public function addObjectToCache(class_model $objObject) {
        $this->arrObjectCache[$objObject->getSystemid()] = $objObject;
    }
}

