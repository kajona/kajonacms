<?php
/*"******************************************************************************************************
*   (c) 2004-2006 by MulchProductions, www.mulchprod.de                                                 *
*   (c) 2007-2012 by Kajona, www.kajona.de                                                              *
*       Published under the GNU LGPL v2.1, see /system/licence_lgpl.txt                                 *
*-------------------------------------------------------------------------------------------------------*
*	$Id$                                             *
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
 * @package module_system
 * @author sidler@mulchprod.de
 * @since 3.3.1
 */
class class_cache  {

    private $strSourceName = "";
    private $strHash1 = "";
    private $strHash2 = "";
    private $strLanguage = "";
    private $strContent = "";

    private $intLeasetime = 0;
    private $intEntryHits = 0;

    private $strCacheId = "";

    /**
     *
     * @var class_db
     */
    private $objDB;

    /**
     * Internal counter of cleanups.
     * If set to true, a cleanup was done and won't be fired again.
     *
     * @var bool
     */
    private static $bitCleanupDone = false;

    private static $intHits = 0;
    private static $intRequests = 0;
    private static $intSaves = 0;

    private static $arrInternalCache = array();


    /**
     * Constructor, being private. Please note that
     * there are factory methods in order to get instances.
     *
     * @see class_cache::getCachedEntry()
     * @see class_cache::createNewInstance()
     * @param $strSourceName
     * @param $strHash1
     * @param $strHash2
     * @param $strLanguage
     * @param $strContent
     * @param $intLeasetime
     * @param $strCacheId
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

        self::$intRequests++;

        //first run - search the internal cache
        foreach(self::$arrInternalCache as $arrSingleCacheEntry) {
            if($arrSingleCacheEntry["cache_source"] == $strSourceName
                && $arrSingleCacheEntry["cache_hash1"] == $strHash1
                && ($strHash2 == null || $arrSingleCacheEntry["cache_hash2"] == $strHash2)
                && ($strLanguage == null || $arrSingleCacheEntry["cache_language"] == $strLanguage)
            ) {

                $objCacheEntry = new class_cache(
                    $arrSingleCacheEntry["cache_source"],
                    $arrSingleCacheEntry["cache_hash1"],
                    $arrSingleCacheEntry["cache_hash2"],
                    $arrSingleCacheEntry["cache_language"],
                    $arrSingleCacheEntry["cache_content"],
                    $arrSingleCacheEntry["cache_leasetime"],
                    $arrSingleCacheEntry["cache_id"]
                );
                self::$intHits++;
                if(_cache_ === true)
                    $objCacheEntry->increaseCacheEntryHits();

                return $objCacheEntry;
            }
        }

        //search in the database to find a matching entry
        $strQuery = "SELECT *
                       FROM "._dbprefix_."cache
                      WHERE cache_source = ?
                        AND cache_hash1 = ?
                        ".($strHash2 != null ? " AND cache_hash2 = ? " : "" )."
                        ".($strLanguage != null ? " AND cache_language = ? " : "" )."
                        AND cache_leasetime > ? ";

        $arrParams = array($strSourceName, $strHash1);
        if($strHash2 != null)
            $arrParams[] = $strHash2;
        if($strLanguage != null)
            $arrParams[] = $strLanguage;

        $arrParams[] = time();

        $arrRow = class_carrier::getInstance()->getObjDB()->getPRow($strQuery, $arrParams);
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

            self::$intHits++;
            if(_cache_ === true)
                $objCacheEntry->increaseCacheEntryHits();

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
     * @throws class_exception
     * @return bool
     */
    public function updateObjectToDb() {


        //run a cleanup
        class_cache::cleanCache();

        //at least a source and hash1 given?
        if($this->strSourceName == "" && $this->strHash1 == "")
            throw new class_exception("not all required params given", class_exception::$level_ERROR);

        //check if the new entry will be valid at least a second, otherwise quit saving
        if(time() > $this->intLeasetime)
            return false;

        $strQuery = "";
        $arrParams = array();
        $arrEscape = array();
        if($this->strCacheId == null) {
            $this->strCacheId = generateSystemid();
            //insert
            $strQuery = "INSERT INTO "._dbprefix_."cache
                       (cache_id, cache_source, cache_hash1, cache_hash2, cache_language, cache_content, cache_leasetime, cache_hits) VALUES
                       (   ?, ?, ?, ?, ?, ?, ?, 1) ";
            $arrParams = array(
                $this->strCacheId,
                $this->getStrSourceName(),
                $this->getStrHash1(),
                $this->getStrHash2(),
                $this->getStrLanguage(),
                $this->getStrContent(),
                $this->getIntLeasetime()
            );
            $arrEscape = array(true, true, true, true, true, false, true);
        }
        else {
            //update
            $strQuery = "UPDATE "._dbprefix_."cache
                            SET cache_source = ?,
                                cache_hash1 = ?,
                                cache_hash2 = ?,
                                cache_language = ?,
                                cache_content = ?,
                                cache_leasetime = ?
                          WHERE cache_id = ?";
            $arrParams = array(
                $this->getStrSourceName(),
                $this->getStrHash1(),
                $this->getStrHash2(),
                $this->getStrLanguage(),
                $this->getStrContent(),
                $this->getIntLeasetime(),
                $this->strCacheId
            );
            $arrEscape = array(true, true, true, true, false, true, true);
        }

        self::$intSaves++;
        return $this->objDB->_pQuery($strQuery, $arrParams, $arrEscape);

    }

    /**
     * Deletes all cached entries, either for a single source or for all sources,
     * more filters like parametrized
     *
     * @param string $strSource
     * @param string $strHash1
     * @param string $strHash2
     * @return bool
     * @static
     */
    public static function flushCache($strSource = "", $strHash1 = "", $strHash2 = "") {
        $strQuery = "DELETE FROM "._dbprefix_."cache ";

        $arrWhere = array();
        $arrParams = array();

        if($strSource != "") {
            $arrWhere[] = " cache_source = ? ";
            $arrParams[] = $strSource;
        }

        if($strHash1 != "") {
            $arrWhere[] = " cache_hash1 = ? ";
            $arrParams[] = $strHash1;
        }

        if($strHash2 != "") {
            $arrWhere[] = " cache_hash2 = ? ";
            $arrParams[] = $strHash2;
        }

        if(count($arrWhere) > 0)
            $strQuery .= "WHERE ".implode(" AND ", $arrWhere);

        return class_carrier::getInstance()->getObjDB()->_pQuery($strQuery, $arrParams);
    }

    /**
     * Returns the list of sources currently stored to the cache
     * @return string
     */
    public static function getCacheSources() {
        $strQuery = "SELECT DISTINCT cache_source FROM  "._dbprefix_."cache";

        $arrReturn = array();
        $arrSourceRows = class_carrier::getInstance()->getObjDB()->getPArray($strQuery, array());
        foreach($arrSourceRows as $arrSingleRow)
            $arrReturn[] = $arrSingleRow["cache_source"];

        return $arrReturn;
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
            $strQuery = "DELETE FROM "._dbprefix_."cache WHERE cache_leasetime < ?";
            class_cache::$bitCleanupDone = true;
            return class_carrier::getInstance()->getObjDB()->_pQuery($strQuery, array( time() ));
        }
        return true;
    }

    /**
     * This static method can be used to pre-load entries into the internal cache.
     * When loading a list of cached-entries differing in only a single param, this method
     * could be way faster:
     * Preload all elements without taking the param being different into account to load the list within
     * a single query. Later on the regular getCachedEntry() can be used to access entries available in
     * the cache; the loading from the internal cache or the database is done by the class internally and
     * transparent to the using objects.
     *
     * @param string $strSourceName
     * @param string $strHash1
     * @param string $strHash2
     * @param string $strLanguage
     */
    public static function fillInternalCache($strSourceName, $strHash1, $strHash2 = null, $strLanguage = null) {
        $strQuery = "SELECT *
                       FROM "._dbprefix_."cache
                      WHERE cache_source = ?
                        AND cache_hash1 = ?
                        ".($strHash2 != null ? " AND cache_hash2 = ? " : "" )."
                        ".($strLanguage != null ? " AND cache_language = ? " : "" )."
                        AND cache_leasetime > ?  ";

        $arrParams = array($strSourceName, $strHash1);
        if($strHash2 != null)
            $arrParams[] = $strHash2;
        if($strLanguage != null)
            $arrParams[] = $strLanguage;
        $arrParams[] = time();

        $arrRow = class_carrier::getInstance()->getObjDB()->getPArray($strQuery, $arrParams);
        foreach($arrRow as $arrSingleRow) {
            self::$arrInternalCache[$arrSingleRow["cache_id"]] = $arrSingleRow;
        }
    }

    /**
     * Returns all cached entries.
     *
     * @param int $intStart
     * @param int $intEnd
     * @return array
     */
    public static function getAllCacheEntries($intStart = null, $intEnd = null) {
        //search in the database to find a matching entry
        $strQuery = "SELECT *
                       FROM "._dbprefix_."cache
                       ORDER BY cache_leasetime DESC";

        $arrCaches = class_carrier::getInstance()->getObjDB()->getPArray($strQuery, array(), $intStart, $intEnd);

        $arrReturn = array();
        foreach($arrCaches as $arrRow) {
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

                $objCacheEntry->setIntEntryHits($arrRow["cache_hits"]);

                $arrReturn[] = $objCacheEntry;
            }
        }

        return $arrReturn;
    }

    /**
     * Returns the number of entries currently in the cache.
     *
     * @return int
     */
    public static function getAllCacheEntriesCount() {
        //search in the database to find a matching entry
        $strQuery = "SELECT COUNT(*) FROM "._dbprefix_."cache";

        $arrCaches = class_carrier::getInstance()->getObjDB()->getPRow($strQuery, array());
        return $arrCaches["COUNT(*)"];
    }

    public function increaseCacheEntryHits() {
        $strQuery = "UPDATE "._dbprefix_."cache SET cache_hits = cache_hits+1 WHERE cache_id=? ";
        return class_carrier::getInstance()->getObjDB()->_pQuery($strQuery, array($this->strCacheId));
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

    public static function getIntHits() {
        return self::$intHits;
    }

    public static function getIntRequests() {
        return self::$intRequests;
    }

    public static function getIntSaves() {
        return self::$intSaves;
    }

    public static function getIntCachesize() {
        return count(self::$arrInternalCache);
    }

    public function getIntEntryHits() {
        return $this->intEntryHits;
    }

    public function setIntEntryHits($intEntryHits) {
        $this->intEntryHits = $intEntryHits;
    }

}

