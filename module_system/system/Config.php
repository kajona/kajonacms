<?php
/*"******************************************************************************************************
*   (c) 2004-2006 by MulchProductions, www.mulchprod.de                                                 *
*   (c) 2007-2015 by Kajona, www.kajona.de                                                              *
*       Published under the GNU LGPL v2.1, see /system/licence_lgpl.txt                                 *
********************************************************************************************************/

namespace Kajona\System\System;


/**
 * Class to manage and access config-values.
 * By default, the config-file has to be placed in the modules /system/config folder.
 * When reading the file, the system merges the entries with identically named config-files
 * located at /project/system/config/.
 * This avoids that the original file has to be changed and may get invalid on system-updates.
 *
 * @package module_system
 * @author sidler@mulchprod.de
 */
class Config
{
    private $arrConfig = null;
    private $arrDebug = null;

    private static $arrInstances = array();

    /**
     * Just an ordinary constructor
     *
     * @param string $strModule
     * @param string $strConfigFile the config-file to parse
     */
    private function __construct($strModule = "module_system", $strConfigFile = "config.php")
    {

        $this->readConfigFile($strModule, $strConfigFile);
        if ($strConfigFile == "config.php" && $strModule == "module_system") {
            $this->setUpConstants();
        }

    }

    /**
     * Resolves, reads and merges the config-files
     *
     * @param string $strConfigFile
     *
     * @return void
     */
    private function readConfigFile($strModule, $strConfigFile)
    {
        $config = array();
        $debug = array();


        //fetch the module path in order to load the default config
        $strAbsPath = Resourceloader::getInstance()->getAbsolutePathForModule($strModule)."/system/config/".$strConfigFile;
        if (is_file($strAbsPath)) {
            include $strAbsPath;
        } elseif (strpos(__DIR__, '.phar') !== false) {
            die("Error reading {$strAbsPath} config-file");
        }

        // project config
        $strProjPath = _realpath_."/project/".$strModule."/system/config/".$strConfigFile;
        if (is_file($strProjPath)) {
            include $strProjPath;
        }

        $this->arrConfig = $config;
        $this->arrDebug = $debug;
    }


    /**
     * Internal helper, sets up a few system-wide constants.
     * Being called on instantiation mapped to config.pgp automatically.
     *
     * @return void
     */
    private function setUpConstants()
    {
        define("_dbprefix_", $this->getConfig("dbprefix"));
        define("_templatepath_", $this->getConfig("dirtemplates"));
        define("_projectpath_", $this->getConfig("dirproject"));
        define("_filespath_", $this->getConfig("dirfiles"));
        define("_langpath_", $this->getConfig("dirlang"));
        define("_indexpath_", _webpath_."/index.php");
        define("_xmlpath_", _webpath_."/xml.php");
        define("_dblog_", $this->getDebug("dblog"));
        define("_timedebug_", $this->getDebug("time"));
        define("_dbnumber_", $this->getDebug("dbnumber"));
        define("_templatenr_", $this->getDebug("templatenr"));
        define("_memory_", $this->getDebug("memory"));
        define("_cache_", $this->getDebug("cache"));


        if ($this->getConfig("images_cachepath") != "") {
            define("_images_cachepath_", $this->getConfig("images_cachepath"));
        }
        else {
            define("_images_cachepath_", "/files/cache/");
        }
    }


    /**
     * Static function to read a value from the config.phps' config-array.
     * <b>Attention:</b> Use this method only, if you know what you do!
     * This method allows access to those values even before the kernel started.
     * In nearly all cases the access via the instance-object is sufficient!
     *
     * @param string $strEntryName
     *
     * @return string
     * @since 3.4.0
     */
    public static function readPlainConfigsFromFilesystem($strEntryName)
    {
        $config = array();

        // default config
        $strFile = __DIR__."/config/config.php";
        if (is_file($strFile)) {
            include $strFile;
        } elseif (strpos(__DIR__, '.phar') !== false) {
            include $strFile;
        }

        // project config
        $strFile = __DIR__."/../../../project/system/config/config.php";
        if (is_file($strFile)) {
            include $strFile;
        }

        return isset($config[$strEntryName]) ? $config[$strEntryName] : "";
    }

    /**
     * Using a singleton to get an instance
     *
     * @param string $strModule the module to load the config file from
     * @param string $strConfigFile the config-file to parse
     *
     * @return Config
     */
    public static function getInstance($strModule = "module_system", $strConfigFile = "config.php")
    {
        if (!isset(self::$arrInstances[$strModule.$strConfigFile])) {
            self::$arrInstances[$strModule.$strConfigFile] = new Config($strModule, $strConfigFile);
        }

        return self::$arrInstances[$strModule.$strConfigFile];
    }

    /**
     * Returns a value from the config-file
     *
     * @param string $strName
     *
     * @return string
     */
    public function getConfig($strName)
    {
        if (isset($this->arrConfig[$strName])) {
            return $this->arrConfig[$strName];
        }
        else {
            return "";
        }
    }

    /**
     * Writes a value to the config-array
     *
     * @param string $strName
     * @param string $strValue
     *
     * @return void
     */
    public function setConfig($strName, $strValue)
    {
        $this->arrConfig[$strName] = $strValue;
    }

    /**
     * Returns a value from the debug-array
     *
     * @param string $strName
     *
     * @return string
     */
    public function getDebug($strName)
    {
        if (isset($this->arrDebug[$strName])) {
            return $this->arrDebug[$strName];
        }
        else {
            return "";
        }
    }

    /**
     * Sets a value to the debug-array
     *
     * @param string $strName
     * @param string $strValue
     *
     * @return void
     */
    public function setDebug($strName, $strValue)
    {
        $this->arrDebug[$strName] = $strValue;
    }

    /**
     * Returns a php.ini value
     *
     * @param string $strKey
     *
     * @return string
     */
    public function getPhpIni($strKey)
    {
        return ini_get($strKey);
    }

    /**
     * Returns the max upload size in bytes
     *
     * @return int
     */
    public function getPhpMaxUploadSize()
    {
        if (phpSizeToBytes($this->getPhpIni("post_max_size")) > phpSizeToBytes($this->getPhpIni("upload_max_filesize"))) {
            return phpSizeToBytes($this->getPhpIni("upload_max_filesize"));
        }
        else {
            return phpSizeToBytes($this->getPhpIni("post_max_size"));
        }
    }

    /**
     * Loads all config-files from the filesystem
     *
     * @deprecated This Method may be removed from future releases. If you need filesystem-based configs,
     *             invoke this method on your own. This method is no longer called at system startup!
     * @throws Exception
     * @return void
     */
    public function loadConfigsFilesystem()
    {
        throw new Exception("no longer supported", Exception::$level_FATALERROR);
    }

    /**
     * Loads all configs from the db and initializations the constants
     *
     * @param Database $objDB
     *
     * @return void
     */
    public function loadConfigsDatabase(Database $objDB)
    {
//        if(count($objDB->getTables()) > 0) {
//            $strQuery = "SELECT * FROM " . _dbprefix_ . "system_config ORDER BY system_config_module ASC";
//            $arrConfigs = $objDB->getPArray($strQuery, array());
//            foreach($arrConfigs as $arrOneConfig) {
//                if(!defined($arrOneConfig["system_config_name"])) {
//                    define($arrOneConfig["system_config_name"], $arrOneConfig["system_config_value"]);
//                }
//            }
//        }

        //set the relevant values to the php env
        if (defined("_system_timezone_") && _system_timezone_ != "") {
            date_default_timezone_set(_system_timezone_);
        }
    }
}

