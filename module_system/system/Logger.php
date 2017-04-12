<?php
/*"******************************************************************************************************
*   (c) 2004-2006 by MulchProductions, www.mulchprod.de                                                 *
*   (c) 2007-2016 by Kajona, www.kajona.de                                                              *
*       Published under the GNU LGPL v2.1, see /system/licence_lgpl.txt                                 *
*-------------------------------------------------------------------------------------------------------*
*	$Id$	                                            *
********************************************************************************************************/

namespace Kajona\System\System;

use Psr\Log\LoggerInterface;
use Psr\Log\LogLevel;

/**
 * The Logger provides a small and fast logging-engine to generate a debug logfile.
 * The granularity of the logging is defined in the config.php
 *
 * @package module_system
 * @author sidler@mulchprod.de
 * @author christoph.kappestein@gmail.com
 */
final class Logger implements LoggerInterface
{
    const SYSTEMLOG = "systemlog.log";
    const DBLOG = "dblayer.log";
    const USERSOURCES = "usersources.log";
    const QUERIES = "dbqueries.log";
    const EVENTS = "events.log";
    const PACKAGEMANAGEMENT = "packagemanagement.log";
    const PAGES = "pages.log";
    const REMOTELOADER = "remoteloader.log";
    const ADMINTASKS = "admintasks.log";

    /**
     * Level to be used for real errors
     *
     * @var int
     * @static
     * @deprecated
     */
    public static $levelError = 1;

    /**
     * Level to be used for warnings
     *
     * @var int
     * @static
     * @deprecated
     */
    public static $levelWarning = 2;

    /**
     * Level to be used for infos
     *
     * @var int
     * @static
     * @deprecated
     */
    public static $levelInfo = 3;

    /**
     * Array of logger-instances
     *
     * @var Logger[]
     */
    private static $arrInstances = array();

    /**
     * Constant defining the filename
     *
     * @var string
     */
    private $strFilename = "";

    /**
     * @var int
     */
    private $intLogLevel = 0;

    /**
     * Doing nothing but being private
     *
     * @param $strLogfile
     */
    private function __construct($strLogfile)
    {
        $this->strFilename = $strLogfile;

        $arrOverwriteLevel = Carrier::getInstance()->getObjConfig()->getDebug("debuglogging_overwrite");
        if (isset($arrOverwriteLevel[$strLogfile])) {
            $this->intLogLevel = $arrOverwriteLevel[$strLogfile];
        }
        else {
            $this->intLogLevel = Carrier::getInstance()->getObjConfig()->getDebug("debuglogging");
        }
    }

    /**
     * returns the current instance of this class
     *
     * @param string $strLogfile
     * @return Logger
     */
    public static function getInstance($strLogfile = "")
    {
        if ($strLogfile == "") {
            $strLogfile = self::SYSTEMLOG;
        }

        if (!isset(self::$arrInstances[$strLogfile])) {
            self::$arrInstances[$strLogfile] = new Logger($strLogfile);
        }

        return self::$arrInstances[$strLogfile];
    }

    /**
     * @inheritdoc
     */
    public function emergency($strMessage, array $arrContext = array())
    {
        $this->log(LogLevel::EMERGENCY, $strMessage, $arrContext);
    }

    /**
     * @inheritdoc
     */
    public function alert($strMessage, array $arrContext = array())
    {
        $this->log(LogLevel::ALERT, $strMessage, $arrContext);
    }

    /**
     * @inheritdoc
     */
    public function critical($strMessage, array $arrContext = array())
    {
        $this->log(LogLevel::CRITICAL, $strMessage, $arrContext);
    }

    /**
     * @inheritdoc
     */
    public function error($strMessage, array $arrContext = array())
    {
        $this->log(LogLevel::ERROR, $strMessage, $arrContext);
    }

    /**
     * @inheritdoc
     */
    public function warning($strMessage, array $arrContext = array())
    {
        $this->log(LogLevel::WARNING, $strMessage, $arrContext);
    }

    /**
     * @inheritdoc
     */
    public function notice($strMessage, array $arrContext = array())
    {
        $this->log(LogLevel::NOTICE, $strMessage, $arrContext);
    }

    /**
     * @inheritdoc
     */
    public function info($strMessage, array $arrContext = array())
    {
        $this->log(LogLevel::INFO, $strMessage, $arrContext);
    }

    /**
     * @inheritdoc
     */
    public function debug($strMessage, array $arrContext = array())
    {
        $this->log(LogLevel::DEBUG, $strMessage, $arrContext);
    }

    /**
     * @inheritdoc
     */
    public function log($level, $strMessage, array $arrContext = array())
    {
        if ($level == LogLevel::EMERGENCY || $level == LogLevel::ALERT || $level == LogLevel::CRITICAL || $level == LogLevel::ERROR) {
            $intLevel = self::$levelError;
        } elseif ($level == LogLevel::WARNING || $level == LogLevel::NOTICE) {
            $intLevel = self::$levelWarning;
        } else {
            $intLevel = self::$levelInfo;
        }

        $this->addLogRow($strMessage, $intLevel, true);
    }

    /**
     * Adds a row to the current log
     * For $intLevel use on of the static level provided by this class
     *
     * @param string $strMessage
     * @param int $intLevel
     * @param bool $bitSkipSessionData
     * @return void
     * @deprecated
     */
    public function addLogRow($strMessage, $intLevel, $bitSkipSessionData = false)
    {
        //check, if there someting to write
        if ($this->intLogLevel == 0) {
            return;
        }
        //errors in level >=1
        if ($intLevel == self::$levelError && $this->intLogLevel < 1) {
            return;
        }
        //warnings in level >=2
        if ($intLevel == self::$levelWarning && $this->intLogLevel < 2) {
            return;
        }
        //infos in level >=3
        if ($intLevel == self::$levelInfo && $this->intLogLevel < 3) {
            return;
        }

        //a log row has the following scheme:
        // YYYY-MM-DD HH:MM:SS LEVEL USERID (USERNAME) MESSAGE
        $strDate = strftime("%Y-%m-%d %H:%M:%S", time());
        $strLevel = "";
        if ($intLevel == self::$levelError) {
            $strLevel = "ERROR";
        }
        elseif ($intLevel == self::$levelInfo) {
            $strLevel = "INFO";
        }
        elseif ($intLevel == self::$levelWarning) {
            $strLevel = "WARNING";
        }

        $strSessid = "";
        if (!$bitSkipSessionData && Carrier::getInstance()->getObjSession()->getBitLazyLoaded()) {
            $strSessid = Carrier::getInstance()->getObjSession()->getInternalSessionId();
            $strSessid .= " (".Carrier::getInstance()->getObjSession()->getUsername().")";
        }

        $strMessage = StringUtil::replace(array("\r", "\n"), array(" ", " "), $strMessage);

        $strFileInfo = "";
        $arrStack = debug_backtrace();

        if (isset($arrStack[1]) && isset($arrStack[1]["file"])) {
            $strFileInfo = basename($arrStack[1]["file"]).":".$arrStack[1]["function"].":".$arrStack[1]["line"];
        }

        $strText = $strDate." ".$strLevel." ".$strSessid." ".$strFileInfo." ".$strMessage."\r\n";

        $handle = @fopen(_realpath_."project/log/".$this->strFilename, "a");
        @fwrite($handle, $strText);
        @fclose($handle);
    }

    /**
     * Returns the complete log-file as one string
     *
     * @return string
     * @deprecated
     */
    public function getLogFileContent()
    {
        $objFile = new Filesystem();
        if (!is_file(_realpath_."project/log/".$this->strFilename)) {
            return "";
        }

        $objFile->openFilePointer("/project/log/".$this->strFilename, "r");
        return $objFile->readLastLinesFromFile(25);
    }


    /**
     * Returns the complete log-file as one string
     *
     * @return string
     * @deprecated
     */
    public function getPhpLogFileContent()
    {
        $objFile = new Filesystem();
        $objFile->openFilePointer("/project/log/php.log", "r");
        return $objFile->readLastLinesFromFile(25);
    }

    /**
     * Sets the loggers logging-level aka. the granularity
     *
     * @param $intLogLevel
     * @deprecated
     */
    public function setIntLogLevel($intLogLevel)
    {
        $this->intLogLevel = $intLogLevel;
    }

    /**
     * @return int
     * @deprecated
     */
    public function getIntLogLevel()
    {
        return $this->intLogLevel;
    }
}


