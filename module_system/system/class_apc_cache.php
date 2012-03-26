<?php
/*"******************************************************************************************************
*   (c) 2004-2006 by MulchProductions, www.mulchprod.de                                                 *
*   (c) 2007-2012 by Kajona, www.kajona.de                                                              *
*       Published under the GNU LGPL v2.1, see /system/licence_lgpl.txt                                 *
*-------------------------------------------------------------------------------------------------------*
*	$Id: class_cache.php 3885 2011-05-26 11:20:45Z sidler $                                             *
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
        $this->bitAPCInstalled = function_exists("apc_store") && function_exists("apc_fetch");
        $this->strSystemKey = class_module_system_module::getModuleByName("system")->getSystemid();
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
     * Adds a value to the cache. The third param is the time to live in seconds.
     * @param $strKey
     * @param $objValue
     * @param int $intTtl
     * @return array|bool
     */
    public function addValue($strKey, $objValue, $intTtl = 0) {
        $strKey = $this->strSystemKey.$strKey;
        if(!$this->bitAPCInstalled)
            return self::$arrFallbackCache[$strKey] = $objValue;

        return apc_store($strKey, $objValue, $intTtl);
    }

    /**
     * Fetches a value from the cache
     * @param $strKey
     * @return bool|mixed false if the entry is not existing
     */
    public function getValue($strKey) {
        $strKey = $this->strSystemKey.$strKey;
        if(!$this->bitAPCInstalled) {
            if(isset(self::$arrFallbackCache[$strKey]))
                return self::$arrFallbackCache[$strKey];
            else
                return false;
        }

        return apc_fetch($strKey);
    }

    public function flushCache() {
        if(!$this->bitAPCInstalled)
            self::$arrFallbackCache = array();
        else {
            apc_clear_cache("user");
            apc_clear_cache();
        }

    }

}
