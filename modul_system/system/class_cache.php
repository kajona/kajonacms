<?php
/*"******************************************************************************************************
*   (c) 2004-2006 by MulchProductions, www.mulchprod.de                                                 *
*   (c) 2007-2010 by Kajona, www.kajona.de                                                              *
*       Published under the GNU LGPL v2.1, see /system/licence_lgpl.txt                                 *
*-------------------------------------------------------------------------------------------------------*
*	$Id: class_cache.php 3224 2010-03-31 18:32:49Z sidler $                                             *
********************************************************************************************************/

/**
 * The cache-class is the central way of caching objects in Kajona.
 * The cache can be used by multiple sources, e.g. pages, navigations and so on.
 * Each entry is identified by various key, to be defined by the using element.
 *
 * Currently, a cache-entry can be identified by the following fields:
 *  - source name
 *  - hash1
 *  - hash2
 *  - language
 *
 * When saving a new value, make sure to have maintained:
 *  - content
 *  - leasetime (otherwise the entry will be invalid as soon as saved to the database)
 *
 * @package modul_system
 * @author sidler
 * @since 3.3.1
 */
class class_cache  {

    private $strSourceName = "";
    private $strHash1 = "";
    private $strHash2 = "";
    private $strLanguage = "";
    private $strContent = "";

    private $intLeasetime = 0;

    private $strCacheId = "";

    /**
     *
     * @var class_db
     */
    private $objDB;


    private static $bitCleanupDone = false;


    /**
     * Contructor, being private. Please note that
     * there are factory methods in order to get instances.
     *
     * @see class_cache::getCachedEntry()
     * @see class_cache::createNewInstance()
     */
    private function __construct($strSourceName, $strHash1, $strHash2, $strLanguage, $strContent, $intLeasetime, $strCacheId) {

        $this->objDB = class_carrier::getInstance()->getObjDB();

        $this->strSourceName = $strSourceName;
        $this->strHash1 = $strHash1;
        $this->strHash2 = $strHash2;
        $this->strLanguage = $strLanguage;
        $this->strContent = $strContent;
        $this->intLeasetime = $intLeasetime;
        $this->strCacheId = $strCacheId;
    }


    /**
     * Tries to load a cached entry.
     * If existing, a valid object is returned.
     * Otherwise, e.g. if not found or invalid, 
     * null is returned instead.
     *
     * @param string $strSourceName
     * @param string $strHash1
     * @param string $strHash2
     * @param string $strLanguage
     * @param bool $bitCreateInstanceOnNotFound if no entry was found, a new instance is created and returned. Set the content afterwards!
     * @return class_cache or null
     */
    public static function getCachedEntry($strSourceName, $strHash1, $strHash2 = null, $strLanguage = null, $bitCreateInstanceOnNotFound = false) {
        //search in the database to find a matching entry
        $strQuery = "SELECT *
                       FROM "._dbprefix_."cache
                      WHERE cache_source = '".dbsafeString($strSourceName)."'
                        AND cache_hash1 = '".dbsafeString($strHash1)."'
                        ".($strHash2 != null ? " AND cache_hash2 = '".dbsafeString($strHash2)."' " : "" )."
                        ".($strLanguage != null ? " AND cache_language = '".dbsafeString($strLanguage)."' " : "" )."
                        AND cache_leasetime > ".time()." ";

        $arrRow = class_carrier::getInstance()->getObjDB()->getRow($strQuery);
        if(isset($arrRow["cache_id"])) {
            $objCacheEntry = new class_cache(
                    $arrRow["cache_source"],
                    $arrRow["cache_hash1"],
                    $arrRow["cache_hash2"],
                    $arrRow["cache_language"],
                    $arrRow["cache_content"],
                    $arrRow["cache_leasetime"],
                    $arrRow["cache_id"]
                );

            return $objCacheEntry;
        }
        else {
            //entry not existing, return null or create a new one?
            if($bitCreateInstanceOnNotFound) {
                $objCache = class_cache::createNewInstance($strSourceName);
                $objCache->setStrHash1($strHash1);
                $objCache->setStrHash2($strHash2);
                $objCache->setStrLanguage($strLanguage);

                return $objCache;
            }
            return null;
        }
    }

    /**
     * Creates a new instance of class_cache.
     * All values are initialized, the one to
     * specify at contruction time is only the source-name.
     * The leasetime is set to the current time, so make sure
     * to increase the time in order to profit from a
     * cached entry.
     * Use the setters to populate the entry with data.
     * 
     * @param string $strSourceName
     * @return class_cache 
     */
    public static function createNewInstance($strSourceName) {
        return new class_cache($strSourceName, "", "", "", "", time(), null);
    }

    /**
     * Saves the object to the database.
     * Differs between update or insert.
     *
     * @return bool
     */
    public function updateObjectToDb() {

        //run a cleanup
        class_cache::cleanCache();

        //at least a source and hash1 given?
        if($this->strSourceName == "" && $this->strHash1 == "")
            throw new class_exception("not all required params given", class_exception::$level_ERROR);

        $strQuery = "";
        if($this->strCacheId == null) {
            $this->strCacheId = generateSystemid();
            //insert
            $strQuery = "INSERT INTO "._dbprefix_."cache
                       (cache_id, cache_source, cache_hash1, cache_hash2, cache_language, cache_content, cache_leasetime) VALUES
                       (   '".dbsafeString($this->strCacheId)."',
                           '".dbsafeString($this->getStrSourceName())."',
                           '".dbsafeString($this->getStrHash1())."',
                           '".dbsafeString($this->getStrHash2())."',
                           '".dbsafeString($this->getStrLanguage())."',
                           '".dbsafeString($this->getStrContent(), false)."',
                           ".(int)dbsafeString($this->getIntLeasetime()).") ";
        }
        else {
            //update
            $strQuery = "UPDATE "._dbprefix_."cache
                            SET cache_source = '".dbsafeString($this->getStrSourceName())."',
                                cache_hash1 = '".dbsafeString($this->getStrHash1())."',
                                cache_hash2 = '".dbsafeString($this->getStrHash2())."',
                                cache_language = '".dbsafeString($this->getStrLanguage())."',
                                cache_content = '".dbsafeString($this->getStrContent(), false)."',
                                cache_leasetime = ".dbsafeString($this->getIntLeasetime())."
                          WHERE cache_id = '".dbsafeString($this->strCacheId)."'";
        }

        return $this->objDB->_query($strQuery);

    }

    /**
     * Deletes all cached entries
     *
     * @return bool
     * @static
     */
    public static function flushCache() {
        $strQuery = "DELETE FROM "._dbprefix_." cache";
        return class_carrier::getInstance()->getObjDB()->_query($strQuery);
    }

    /**
     * Deletes all invalid entries from the cache.
     * Does this only once per run, so all calls after
     * the first one will be skipped.
     *
     * @return bool
     * @static
     */
    public static function cleanCache() {
        if(class_cache::$bitCleanupDone === false) {
            $strQuery = "DELETE FROM "._dbprefix_."cache WHERE cache_leasetime < ".time()."";
            class_cache::$bitCleanupDone = true;
            return class_carrier::getInstance()->getObjDB()->_query($strQuery);
        }
        return true;
    }

	

   public function getStrSourceName() {
       return $this->strSourceName;
   }

   public function setStrSourceName($strSourceName) {
       $this->strSourceName = $strSourceName;
   }

   public function getStrHash1() {
       return $this->strHash1;
   }

   public function setStrHash1($strHash1) {
       if($strHash1 != null)
           $this->strHash1 = $strHash1;
   }

   public function getStrHash2() {
       return $this->strHash2;
   }

   public function setStrHash2($strHash2) {
       if($strHash2 != null)
           $this->strHash2 = $strHash2;
   }

   public function getStrLanguage() {
       return $this->strLanguage;
   }

   public function setStrLanguage($strLanguage) {
       if($strLanguage != null)
           $this->strLanguage = $strLanguage;
   }

   public function getStrContent() {
       return $this->strContent;
   }

   public function setStrContent($strContent) {
       $this->strContent = $strContent;
   }

   public function getIntLeasetime() {
       return $this->intLeasetime;
   }

   public function setIntLeasetime($intLeasetime) {
       $this->intLeasetime = $intLeasetime;
   }

} 

?>