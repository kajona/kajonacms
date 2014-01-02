<?php
/*"******************************************************************************************************
*   (c) 2004-2006 by MulchProductions, www.mulchprod.de                                                 *
*   (c) 2007-2014 by Kajona, www.kajona.de                                                              *
*       Published under the GNU LGPL v2.1, see /system/licence_lgpl.txt                                 *
*-------------------------------------------------------------------------------------------------------*
*	$Id$                                             *
********************************************************************************************************/

/**
 * The APC cache depends on the optional apc-bytecode cache.
 *
 * If not installed, the cache falls back to a static cache, storing objects during the current
 * request.
 * Make sure you only store relatively small entries, caching a complete net of objects will
 * lead to outOfMemory errors.
 *
 * @package module_system
 * @author sidler@mulchprod.de
 * @since 3.4.2
 */
class class_apc_cache  {

    /**
     * @var class_apc_cache
     */
    private static $objInstance = null;

    private static $arrFallbackCache = array();

    private $bitAPCInstalled = false;

    private $strSystemKey = "";

    private function __construct() {
        $this->bitAPCInstalled = function_exists("apc_store") && function_exists("apc_cache_info") && @apc_cache_info() !== false;
        $this->strSystemKey = md5(__FILE__);
    }


    /**
     * Returns a valid instance
     * @static
     * @return class_apc_cache
     */
    public static function getInstance() {
        if(self::$objInstance == null)
            self::$objInstance = new class_apc_cache();

        return self::$objInstance;
    }

    /**
     * Adds a value to the cache. The third param is the time to live in seconds, defaulted to 180
     * @param $strKey
     * @param $objValue
     * @param int $intTtl
     * @return array|bool
     */
    public function addValue($strKey, $objValue, $intTtl = 180) {

        if(!is_numeric($intTtl))
            $intTtl = 180;

        $strKey = $this->strSystemKey.$strKey;
        if(!$this->bitAPCInstalled)
            return self::$arrFallbackCache[$strKey] = $objValue;

        return @apc_store($strKey, $objValue, $intTtl);
    }

    /**
     * Fetches a value from the cache
     *
     * @param $strKey
     * @param bool|mixed $objDefaultValue The value to be returned in case the key is not found in the store.
     *
     * @return bool|mixed false if the entry is not existing
     */
    public function getValue($strKey, &$objDefaultValue = false) {
        $strKey = $this->strSystemKey.$strKey;
        if(!$this->bitAPCInstalled) {
            if(isset(self::$arrFallbackCache[$strKey]))
                return self::$arrFallbackCache[$strKey];
            else
                return $objDefaultValue;
        }

        $bitSuccess = null;
        $mixedValue = apc_fetch($strKey, $bitSuccess);

        if($bitSuccess === false)
            return $objDefaultValue;
        else
            return $mixedValue;
    }

    public function flushCache() {
        if(!$this->bitAPCInstalled)
            self::$arrFallbackCache = array();
        else {
            apc_clear_cache("user");
            apc_clear_cache();
        }

    }

    public function getBitAPCInstalled() {
        return $this->bitAPCInstalled;
    }

}
