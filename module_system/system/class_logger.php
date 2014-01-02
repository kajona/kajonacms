<?php
/*"******************************************************************************************************
*   (c) 2004-2006 by MulchProductions, www.mulchprod.de                                                 *
*   (c) 2007-2014 by Kajona, www.kajona.de                                                              *
*       Published under the GNU LGPL v2.1, see /system/licence_lgpl.txt                                 *
*-------------------------------------------------------------------------------------------------------*
*	$Id$	                                            *
********************************************************************************************************/

/**
 * The class_logger provides a small and fast logging-engine to generate a debug logfile.
 * The granularity of the logging is defined in the config.php
 *
 * @package module_system
 * @author sidler@mulchprod.de
 */
final class class_logger {

    const SYSTEMLOG = "systemlog.log";
    const DBLOG = "dblayer.log";
    const USERSOURCES = "usersources.log";
    const QUERIES = "dbqueries.log";
    const EVENTS = "events.log";
    const PACKAGEMANAGEMENT = "packagemanagement.log";
    const LANG = "lang.log";
    const REMOTELOADER = "remoteloader.log";
    const ADMINTASKS = "admintasks.log";

    /**
     * Level to be used for real errors
     *
     * @var int
     * @static
     */
    public static $levelError = 1;

    /**
     * Level to be used for warnings
     *
     * @var int
     * @static
     */
    public static $levelWarning = 2;

    /**
     * Level to be used for infos
     *
     * @var int
     * @static
     */
    public static $levelInfo = 3;


    /**
     * Array of logger-instances
     *
     * @var class_logger[]
     */
    private static $arrInstances = array();

    /**
     * Constant defining the filename
     *
     * @var string
     */
    private $strFilename = "";

    private $intLogLevel = 0;

    /**
     * Doing nothing but being private
     *
     * @param $strLogfile
     */
    private function __construct($strLogfile) {
        $this->strFilename = $strLogfile;
        $this->intLogLevel = class_carrier::getInstance()->getObjConfig()->getDebug("debuglogging");
    }

    /**
     * returns the current instance of this class
     *
     * @param string $strLogfile
     *
     * @return class_logger
     */
    public static function getInstance($strLogfile = "") {
        if($strLogfile == "") {
            $strLogfile = self::SYSTEMLOG;
        }

        if(!isset(self::$arrInstances[$strLogfile])) {
            self::$arrInstances[$strLogfile] = new class_logger($strLogfile);
        }

        return self::$arrInstances[$strLogfile];

    }

    /**
     * Adds a row to the current log
     * For $intLevel use on of the static level provided by this class
     *
     * @param string $strMessage
     * @param int $intLevel
     * @param bool $bitSkipSessionData
     *
     * @return void
     */
    public function addLogRow($strMessage, $intLevel, $bitSkipSessionData = false) {

        $arrStack = debug_backtrace();

        //check, if there someting to write
        if($this->intLogLevel == 0) {
            return;
        }
        //errors in level >=1
        if($intLevel == self::$levelError && $this->intLogLevel < 1) {
            return;
        }
        //warnings in level >=2
        if($intLevel == self::$levelWarning && $this->intLogLevel < 2) {
            return;
        }
        //infos in level >=3
        if($intLevel == self::$levelInfo && $this->intLogLevel < 3) {
            return;
        }

        //a log row has the following scheme:
        // YYYY-MM-DD HH:MM:SS LEVEL USERID (USERNAME) MESSAGE
        $strDate = strftime("%Y-%m-%d %H:%M:%S", time());
        $strLevel = "";
        if($intLevel == self::$levelError) {
            $strLevel = "ERROR";
        }
        elseif($intLevel == self::$levelInfo) {
            $strLevel = "INFO";
        }
        elseif($intLevel == self::$levelWarning) {
            $strLevel = "WARNING";
        }

        $strSessid = "";
        if(!$bitSkipSessionData && class_carrier::getInstance()->getObjSession()->getBitLazyLoaded()) {
            $strSessid = class_carrier::getInstance()->getObjSession()->getInternalSessionId();
            $strSessid .= " (" . class_carrier::getInstance()->getObjSession()->getUsername() . ")";
        }

        $strMessage = uniStrReplace(array("\r", "\n"), array(" ", " "), $strMessage);

        $strFileInfo = "";
        if(isset($arrStack[1]) && isset($arrStack[1]["file"])) {
            $strFileInfo = basename($arrStack[1]["file"]) . ":" . $arrStack[1]["function"] . ":" . $arrStack[1]["line"];
        }

        $strText = $strDate . " " . $strLevel . " " . $strSessid . " " . $strFileInfo . " " . $strMessage . "\r\n";

        $handle = fopen(_realpath_ . "/project/log/" . $this->strFilename, "a");
        fwrite($handle, $strText);
        fclose($handle);
    }

    /**
     * Returns the complete log-file as one string
     *
     * @return string
     */
    public function getLogFileContent() {
        $objFile = new class_filesystem();
        if(!is_file(_realpath_ . "/project/log/" . $this->strFilename)) {
            return "";
        }

        $objFile->openFilePointer("/project/log/" . $this->strFilename, "r");
        return $objFile->readLastLinesFromFile(25);
    }


    /**
     * Returns the complete log-file as one string
     *
     * @return string
     */
    public function getPhpLogFileContent() {
        $objFile = new class_filesystem();
        $objFile->openFilePointer("/project/log/php.log", "r");
        return $objFile->readLastLinesFromFile(25);
    }

    /**
     * Sets the loggers logging-level aka. the granularity
     *
     * @param $intLogLevel
     */
    public function setIntLogLevel($intLogLevel) {
        $this->intLogLevel = $intLogLevel;
    }

}


