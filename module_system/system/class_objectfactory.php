<?php
/*"******************************************************************************************************
*   (c) 2007-2013 by Kajona, www.kajona.de                                                              *
*       Published under the GNU LGPL v2.1, see /system/licence_lgpl.txt                                 *
*-------------------------------------------------------------------------------------------------------*
*   $Id$                                        *
********************************************************************************************************/

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
     * @var string[]
     */
    private $arrClassCache = array();

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

    private function __construct() {
        $this->strObjectsCacheFile = _realpath_."/project/temp/objects.cache";

        $this->objDB = class_carrier::getInstance()->getObjDB();

        $this->arrClassCache = class_apc_cache::getInstance()->getValue(__CLASS__."classes");
        if($this->arrClassCache == false) {
            $this->arrClassCache = array();

            if(is_file($this->strObjectsCacheFile)) {
                $this->arrClassCache = unserialize(file_get_contents($this->strObjectsCacheFile));
            }
        }
    }

    function __destruct() {
        if($this->bitCacheSaveRequired && class_config::getInstance()->getConfig('resourcecaching') == true) {
            class_apc_cache::getInstance()->addValue(__CLASS__."classes", $this->arrClassCache);
            file_put_contents($this->strObjectsCacheFile, serialize($this->arrClassCache));
        }
    }

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
        if(isset($this->arrClassCache[$strSystemid])) {
            $strClass = $this->arrClassCache[$strSystemid];
        }
        else {
            $strQuery = "SELECT * FROM "._dbprefix_."system where system_id = ?";
            $arrRow = $this->objDB->getPRow($strQuery, array($strSystemid));
            if(isset($arrRow["system_class"])) {
                $strClass = $arrRow["system_class"];
                $this->arrClassCache[$strSystemid] = $strClass;
                $this->bitCacheSaveRequired = true;
            }
        }
        
        return $strClass;
    }


    /**
     * Flushes the internal instance cache
     */
    public function flushCache() {
        $this->arrObjectCache = array();
        $this->arrClassCache = array();
    }
}

