<?php
/*"******************************************************************************************************
*   (c) 2004-2006 by MulchProductions, www.mulchprod.de                                                 *
*   (c) 2007-2016 by Kajona, www.kajona.de                                                              *
*       Published under the GNU LGPL v2.1, see /system/licence_lgpl.txt                                 *
********************************************************************************************************/

namespace Kajona\System\System;


/**
 * Model for a single system-setting
 * Setting are not represented by a record in the system-table
 *
 * @package module_system
 * @author sidler@mulchprod.de
 *
 * @module system
 * @moduleId _system_modul_id_
 * @targetTable system_config.system_config_id
 *
 * @blockFromAutosave
 *
 */
class SystemSetting extends Model implements ModelInterface, VersionableInterface
{

    /**
     * @var SystemSetting[]
     */
    private static $arrInstanceCache = null;

    /**
     * @var array
     */
    private static $arrValueMap = array();


    //0 = bool, 1 = int, 2 = string, 3 = page
    //use ONLY the following static vars to assign a type to your constant.
    //integer values may change without warnings!
    /**
     * Used for bool values = 0
     *
     * @var int
     */
    public static $int_TYPE_BOOL = 0;

    /**
     * Used for integer values = 1
     *
     * @var int
     */
    public static $int_TYPE_INT = 1;

    /**
     * Used for string values = 2
     *
     * @var int
     */
    public static $int_TYPE_STRING = 2;

    /**
     * Used for pages = 3
     *
     * @var int
     */
    public static $int_TYPE_PAGE = 3;

    const STR_CACHE_NAME = "SystemSetting_CACHE";

    /**
     * @var int
     * @tableColumn system_config.system_config_module
     * @tableColumnDatatype int
     * @listOrder
     */
    private $intModule = 0;

    /**
     * @var string
     * @versionable
     * @tableColumn system_config.system_config_name
     * @tableColumnDatatype char254
     * @listOrder
     */
    private $strName = "";

    /**
     * @var string
     * @versionable
     * @tableColumn system_config.system_config_value
     * @tableColumnDatatype char254
     */
    private $strValue = "";

    /**
     * @var int
     * @tableColumn system_config.system_config_type
     * @tableColumnDatatype int
     */
    private $intType = 0;





    /**
     * Initalises the current object, if a systemid was given
     *
     * @return void
     */
    protected function initObjectInternal()
    {
        parent::initObjectInternal();

        $this->specialConfigInits();
    }

    /**
     * Internal helper to trigger special events, e.g. to change phps runtime settings based on some config vars.
     */
    private function specialConfigInits()
    {
        if ($this->strName == "_system_timezone_" && !defined("_system_timezone_")) {
            if ($this->getStrValue() != "") {
                date_default_timezone_set($this->getStrValue());
                define("_system_timezone_", $this->getStrValue());
            }
        }
    }



    /**
     * Returns the name to be used when rendering the current object, e.g. in admin-lists.
     *
     * @return string
     */
    public function getStrDisplayName()
    {
        return $this->getStrName();
    }


    /**
     * Updates the current object to the database.
     * Only the value is updated!!!
     *
     * @param bool $strPrevId
     *
     * @return bool
     */
    public function updateObjectToDb($strPrevId = false)
    {
        $bitReturn = parent::updateObjectToDb($strPrevId);

        self::$arrValueMap = null;
        self::$arrInstanceCache = null;

        /** @var CacheManager $objCacheManager */
        $objCacheManager = Carrier::getInstance()->getContainer()->offsetGet("system_cache_manager");
        $objCacheManager->removeValue(self::STR_CACHE_NAME);

        return $bitReturn;
    }



    /**
     * Fetches all Configs from the database
     *
     * @return SystemSetting[]
     * @static
     */
    public static function getAllConfigValues()
    {
        if (self::$arrInstanceCache == null) {
            if (count(Database::getInstance()->getTables()) == 0) {
                return array();
            }

            /** @var SystemSetting $objOneSetting */
            foreach (static::getObjectListFiltered() as $objOneSetting) {
                self::$arrInstanceCache[$objOneSetting->getStrName()] = $objOneSetting;
                self::$arrValueMap[$objOneSetting->getStrName()] = $objOneSetting->getStrValue();
            }
        }

        if (self::$arrInstanceCache == null) {
            return array();
        }

        return self::$arrInstanceCache;
    }

    /**
     * Internal helper to fill the key-value-map of settings. by default filled from the cache. secondary, as a fallback,
     * the "real" settings-objects are read and written to the the array, the cache is filled too.
     *
     * @return array|mixed
     */
    private static function getConfigValueMap()
    {
        if (self::$arrValueMap == null) {
            /** @var CacheManager $objCacheManager */
            $objCacheManager = Carrier::getInstance()->getContainer()->offsetGet("system_cache_manager");
            self::$arrValueMap = $objCacheManager->getValue(self::STR_CACHE_NAME);
            if (self::$arrValueMap === false) {
                self::getAllConfigValues();
                $objCacheManager->addValue(self::STR_CACHE_NAME, self::$arrValueMap, 0, CacheManager::TYPE_APC | CacheManager::TYPE_FILESYSTEM);
            }
        }

        return self::$arrValueMap;
    }

    /**
     * Fetches a Configs selected by name
     *
     * @param $strName
     *
     * @return SystemSetting|null
     * @static
     */
    public static function getConfigByName($strName)
    {
        $arrSettings = self::getAllConfigValues();
        if (isset($arrSettings[$strName])) {
            return $arrSettings[$strName];
        }
        return null;
    }

    /**
     * Returns the value of a config or null if the config does not exist.
     *
     * @param $strName - the name of the config
     *
     * @return string or null
     */
    public static function getConfigValue($strName)
    {
        self::getConfigValueMap();
        if (isset(self::$arrValueMap[$strName])) {
            return self::$arrValueMap[$strName];
        }

        return null;
    }

    public function getVersionActionName($strAction)
    {
        return $strAction;
    }

    public function renderVersionValue($strProperty, $strValue)
    {
        return $strValue;
    }

    public function getVersionPropertyName($strProperty)
    {
        return $strProperty;
    }

    public function getVersionRecordName()
    {
        return Carrier::getInstance()->getObjLang()->getLang("change_type_setting", "system");
    }

    public function getStrName()
    {
        return $this->strName;
    }

    public function getStrValue()
    {
        return $this->strValue;
    }

    public function getIntType()
    {
        return $this->intType;
    }

    public function getIntModule()
    {
        return $this->intModule;
    }

    public function setStrName($strName)
    {
        $this->strName = $strName;
    }

    public function setStrValue($strValue)
    {
        $this->strValue = $strValue;
    }

    public function setIntType($intType)
    {
        $this->intType = $intType;
    }

    public function setIntModule($intModule)
    {
        $this->intModule = $intModule;
    }

}
