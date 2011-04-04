<?php
/*"******************************************************************************************************
*   (c) 2004-2006 by MulchProductions, www.mulchprod.de                                                 *
*   (c) 2007-2011 by Kajona, www.kajona.de                                                              *
*       Published under the GNU LGPL v2.1, see /system/licence_lgpl.txt                                 *
*-------------------------------------------------------------------------------------------------------*
*	$Id$                                             *
********************************************************************************************************/

/**
 * Class to manage and access Config-Values
 *
 * @package modul_system
 */
class class_config {
	private $arrConfig = null;
	private $arrDebug = null;
	private $arrModul = null;

	private static $objConfig = null;

	/**
	 * Just an ordinary constructor
	 *
	 */
	private function __construct() 	{
		$this->arrModul["name"] 		= "config";
		$this->arrModul["author"] 		= "sidler@mulchprod.de";

		$config = array();
		$debug = array();

		//Include the config-File
		if(!@include_once(_realpath_."/system/config/config.php"))
			die("Error reading config-file!");

		$this->arrConfig = $config;
		$this->arrDebug = $debug;

		//Now we have to set up some more constants...
		define("_dbprefix_" , 		$this->getConfig("dbprefix"));
		define("_systempath_" , 	_realpath_ . $this->getConfig("dirsystem"));
		define("_portalpath_" , 	_realpath_ . $this->getConfig("dirportal"));
		define("_adminpath_" , 		_realpath_ . $this->getConfig("diradmin"));
		define("_templatepath_" , 	_realpath_ . $this->getConfig("dirtemplates"));
		define("_langpath_" , 		_realpath_ . $this->getConfig("dirlang"));
		define("_skinpath_" , 		_adminpath_ . $this->getConfig("dirskins"));
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
            define("_images_cachepath_", "/portal/pics/cache/");

	}

	/**
	 * Using a singleton to get an instance
	 *
	 * @return class_config
	 */
	public static function getInstance() {
		if(self::$objConfig == null) {
			self::$objConfig = new class_config();
		}

		return self::$objConfig;
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
		$objFilesystem = new class_filesystem();
		$arrFiles = $objFilesystem->getFilelist("/system/config/", ".php");

		foreach($arrFiles as $strFile)
			if($strFile != "config.php" && uniStrpos($strFile, "~") === false)
				include_once(_systempath_."/config/".$strFile);

		return;
	}

	/**
	 * Loads all configs from the db and inits the contants
	 *
	 * @param object $objDB
	 */
	public function loadConfigsDatabase($objDB) {
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

?>