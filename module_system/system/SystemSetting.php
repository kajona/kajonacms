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
 *
 * @blockFromAutosave
 *
 * @todo make settings "real" objects, so with a systemid
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
     * @var string
     * @versionable
     */
    private $strName = "";

    /**
     * @var string
     * @versionable
     */
    private $strValue = "";
    private $intType = 0;
    private $intModule = 0;

    private $strOldValue = "";


    private function initRowCache()
    {
        $strQuery = "SELECT * FROM "._dbprefix_."system_config";
        $arrRows = $this->objDB->getPArray($strQuery, array());
        foreach ($arrRows as $arrSingleRow) {
            $arrSingleRow["system_id"] = $arrSingleRow["system_config_id"];
            OrmRowcache::addSingleInitRow($arrSingleRow);
        }
    }


    /**
     * Initalises the current object, if a systemid was given
     *
     * @return void
     */
    protected function initObjectInternal()
    {
        $arrRow = OrmRowcache::getCachedInitRow($this->getSystemid());
        if ($arrRow === null) {
            $this->initRowCache();
            $arrRow = OrmRowcache::getCachedInitRow($this->getSystemid());
        }

        $this->setArrInitRow(array("system_id" => ""));

        $this->setStrName($arrRow["system_config_name"]);
        $this->setStrValue($arrRow["system_config_value"]);
        $this->setIntType($arrRow["system_config_type"]);
        $this->setIntModule($arrRow["system_config_module"]);

        $this->strOldValue = $this->strValue;

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
     * Deletes the current object from the system
     *
     * @return bool
     */
    public function deleteObject()
    {
        return true;
    }

    public function deleteObjectFromDatabase()
    {
        $strQuery = "DELETE FROM "._dbprefix_."system_config WHERE system_config_id = ?";
        return Carrier::getInstance()->getObjDB()->_pQuery($strQuery, array($this->getSystemid()));
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
     * Called whenever a update-request was fired.
     * Use this method to synchronize yourselves with the database.
     * Use only updates, inserts are not required to be implemented.
     *
     * @return bool
     */
    protected function updateStateToDb()
    {
        return true;
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

        $objChangelog = new SystemChangelog();
        $objChangelog->createLogEntry($this, SystemChangelog::$STR_ACTION_EDIT);

        self::$arrInstanceCache = null;
        self::$arrValueMap = null;

        /** @var CacheManager $objCacheManager */
        $objCacheManager = Carrier::getInstance()->getContainer()->offsetGet("system_cache_manager");
        $objCacheManager->removeValue(self::STR_CACHE_NAME);

        if (!SystemSetting::checkConfigExisting($this->getStrName())) {
            Logger::getInstance()->addLogRow("new constant ".$this->getStrName()." with value ".$this->getStrValue(), Logger::$levelInfo);

            $strQuery = "INSERT INTO "._dbprefix_."system_config
                        (system_config_id, system_config_name, system_config_value, system_config_type, system_config_module) VALUES
                        (?, ?, ?, ?, ?)";
            return $this->objDB->_pQuery($strQuery, array(generateSystemid(), $this->getStrName(), $this->getStrValue(), (int)$this->getIntType(), (int)$this->getIntModule()));
        } else {
            Logger::getInstance()->addLogRow("updated constant ".$this->getStrName()." to value ".$this->getStrValue(), Logger::$levelInfo);

            $strQuery = "UPDATE "._dbprefix_."system_config
                        SET system_config_value = ?
                      WHERE system_config_name = ?";
            return $this->objDB->_pQuery($strQuery, array($this->getStrValue(), $this->getStrName()));
        }
    }


    /**
     * Renames a constant in the database.
     *
     * @param string $strNewName
     *
     * @return bool
     */
    public function renameConstant($strNewName)
    {
        Logger::getInstance()->addLogRow("renamed constant ".$this->getStrName()." to ".$strNewName, Logger::$levelInfo);

        $strQuery = "UPDATE "._dbprefix_."system_config
                    SET system_config_name = ? WHERE system_config_name = ?";

        $bitReturn = $this->objDB->_pQuery($strQuery, array($strNewName, $this->getStrName()));
        $this->strName = $strNewName;
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
            SystemChangelog::$bitChangelogEnabled = false;
            if (count(Database::getInstance()->getTables()) == 0) {
                return array();
            }

            $strQuery = "SELECT * FROM "._dbprefix_."system_config ORDER BY system_config_module ASC, system_config_name DESC";
            $arrIds = Carrier::getInstance()->getObjDB()->getPArray($strQuery, array(), null, null, false);
            foreach ($arrIds as $arrOneId) {
                $arrOneId["system_id"] = $arrOneId["system_config_id"];
                OrmRowcache::addSingleInitRow($arrOneId);
                self::$arrInstanceCache[$arrOneId["system_config_name"]] = new SystemSetting($arrOneId["system_config_id"]);
                self::$arrValueMap[$arrOneId["system_config_name"]] = $arrOneId["system_config_value"];
            }

            SystemChangelog::$bitChangelogEnabled = null;
        }

        if (self::$arrInstanceCache == null) {
            return array();
        }

        return self::$arrInstanceCache;
    }

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
     * Checks, if a config-value is already existing
     *
     * @param string $strName
     *
     * @return boolean
     */
    public static function checkConfigExisting($strName)
    {
        $strQuery = "SELECT COUNT(*) FROM "._dbprefix_."system_config WHERE system_config_name = ?";
        $arrRow = Carrier::getInstance()->getObjDB()->getPRow($strQuery, array($strName));
        return $arrRow["COUNT(*)"] == 1;
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
