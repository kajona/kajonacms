<?php
/*"******************************************************************************************************
*   (c) 2004-2006 by MulchProductions, www.mulchprod.de                                                 *
*   (c) 2007-2012 by Kajona, www.kajona.de                                                              *
*       Published under the GNU LGPL v2.1, see /system/licence_lgpl.txt                                 *
*-------------------------------------------------------------------------------------------------------*
*	$Id$                                             *
********************************************************************************************************/

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
class class_config {
	private $arrConfig = null;
	private $arrDebug = null;

	private static $arrInstances = array();

	/**
	 * Just an ordinary constructor
	 *
     * @param string $strConfigFile the config-file to parse
	 */
	private function __construct($strConfigFile = "config.php") 	{

        $this->readConfigFile($strConfigFile);
        if($strConfigFile == "config.php") {
            $this->setUpConstants();
        }

	}

    /**
     * Resolves, reads and merges the config-files
     * @param $strConfigFile
     */
    private function readConfigFile($strConfigFile) {
        $config = array();
        $debug = array();

        //Include the config-File
        $strPath = class_resourceloader::getInstance()->getPathForFile("/system/config/".$strConfigFile, false);
        if($strPath === false || !@include _corepath_."/module_system/system/config/".$strConfigFile)
            die("Error reading config-file!");

        //overwrite with settings from project
        if(is_file(_realpath_."/project/system/config/".$strConfigFile) )
            if(!@include _realpath_."/project/system/config/".$strConfigFile)
                die("Error reading config-file: ".$strConfigFile);

        $this->arrConfig = $config;
        $this->arrDebug = $debug;
}



    /**
     * Internal helper, sets up a few system-wide constants.
     * Being called on instantiation mapped to config.pgp automatically.
     */
    private function setUpConstants() {
        define("_dbprefix_" , 		$this->getConfig("dbprefix"));
        define("_systempath_" , 	$this->getConfig("dirsystem"));
        define("_portalpath_" , 	$this->getConfig("dirportal"));
        define("_adminpath_" , 		$this->getConfig("diradmin"));
        define("_templatepath_" , 	$this->getConfig("dirtemplates"));
        define("_projectpath_" , 	$this->getConfig("dirproject"));
        define("_filespath_" , 	    $this->getConfig("dirfiles"));
        define("_langpath_" , 		$this->getConfig("dirlang"));
        define("_skinpath_" , 		$this->getConfig("dirskins"));
        define("_indexpath_",		_webpath_."/index.php");
        define("_xmlpath_",         _webpath_."/xml.php");
        define("_dblog_", 			$this->getDebug("dblog"));
        define("_timedebug_", 		$this->getDebug("time"));
        define("_dbnumber_", 		$this->getDebug("dbnumber"));
        define("_templatenr_", 		$this->getDebug("templatenr"));
        define("_memory_",          $this->getDebug("memory"));
        define("_cache_",           $this->getDebug("cache"));


        if($this->getConfig("images_cachepath") != "")
            define("_images_cachepath_", $this->getConfig("images_cachepath"));
        else
            define("_images_cachepath_", "/files/cache/");
    }


    /**
     * Static function to read a value from the config.phps' config-array.
     * <b>Attention:</b> Use this method only, if you know what you do!
     * This method allows access to those values even before the kernel started.
     * In nearly all cases the access via the instance-object is sufficient!
     *
     * @param string $strEntryName
     * @return string
     * @since 3.4.0
     */
    public static function readPlainConfigsFromFilesystem($strEntryName) {

        $config = array();

        if(is_file(__DIR__."/../../../project/system/config/config.php") ) {
            if(!@include __DIR__."/../../../project/system/config/config.php")
                die("Error reading config-file!");
        }
        else if(!@include __DIR__."/config/config.php")
            die("Error reading config-file!");

        return isset($config[$strEntryName]) ? $config[$strEntryName] : "";

    }

	/**
	 * Using a singleton to get an instance
	 *
     * @param string $strConfigFile the config-file to parse
	 * @return class_config
	 */
	public static function getInstance($strConfigFile = "config.php") {
		if(!isset(self::$arrInstances[$strConfigFile])) {
			self::$arrInstances[$strConfigFile] = new class_config($strConfigFile);
		}

		return self::$arrInstances[$strConfigFile];
	}

	/**
	 * Returns a value from the config-file
	 *
	 * @param string $strName
	 * @return string
	 */
	public function getConfig($strName) {
		if(isset($this->arrConfig[$strName]))
			return $this->arrConfig[$strName];
		else
			return "";
	}

	/**
	 * Writes a value to the config-array
	 *
	 * @param string $strName
	 * @param string $strValue
	 */
	public function setConfig($strName, $strValue) {
		$this->arrConfig[$strName] = $strValue;
	}

	/**
	 * Returns a value from the debug-array
	 *
	 * @param string $strName
	 * @return string
	 */
	public function getDebug($strName) {
		if(isset($this->arrDebug[$strName]))
			return $this->arrDebug[$strName];
		else
			return "";
	}

	/**
	 * Returns a php.ini value
	 *
	 * @param string $strKey
	 * @return string
	 */
	public function getPhpIni($strKey) {
		return ini_get($strKey);
	}

	/**
	 * Returns the max upload size in bytes
	 *
	 * @return int
	 */
	public function getPhpMaxUploadSize() {
	    if(phpSizeToBytes($this->getPhpIni("post_max_size")) > phpSizeToBytes($this->getPhpIni("upload_max_filesize")))
            return phpSizeToBytes($this->getPhpIni("upload_max_filesize"));
        else
            return phpSizeToBytes($this->getPhpIni("post_max_size"));
    }

	/**
	 * Loads all config-files from the filesystem
	 *
	 * @deprecated This Method may be removed from future releases. If you need filesystem-based configs,
	 *             invoke this method on your own. This method is no longer called at system startup!
	 */
	public function loadConfigsFilesystem() 	{
        throw new class_exception("no longer supported", class_exception::$level_FATALERROR);
	}

	/**
	 * Loads all configs from the db and initializations the constants
	 *
	 * @param class_db $objDB
	 */
	public function loadConfigsDatabase(class_db $objDB) {
	    if(count($objDB->getTables()) > 0) {
            $strQuery = "SELECT * FROM "._dbprefix_."system_config ORDER BY system_config_module ASC";
            $arrConfigs = $objDB->getPArray($strQuery, array());
            foreach($arrConfigs as $arrOneConfig) {
                if(!defined($arrOneConfig["system_config_name"]))
                    define($arrOneConfig["system_config_name"], $arrOneConfig["system_config_value"]);
            }
	    }
	}

}

