<?php
/*"******************************************************************************************************
*   (c) 2004-2006 by MulchProductions, www.mulchprod.de                                                 *
*   (c) 2007-2016 by Kajona, www.kajona.de                                                              *
*       Published under the GNU LGPL v2.1, see /system/licence_lgpl.txt                                 *
*-------------------------------------------------------------------------------------------------------*
*	$Id$	                                            *
********************************************************************************************************/

namespace Kajona\System\System;

use Monolog\Handler\RotatingFileHandler;
use Psr\Log\LoggerInterface;

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
     * @var \Monolog\Logger
     */
    private $objLogger;

    /**
     * Doing nothing but being private
     *
     * @param string $strLogfile
     */
    private function __construct($strLogfile)
    {
        $arrOverwriteLevel = Carrier::getInstance()->getObjConfig()->getDebug("debuglogging_overwrite");
        if (isset($arrOverwriteLevel[$strLogfile])) {
            $intLogLevel = $arrOverwriteLevel[$strLogfile];
        } else {
            $intLogLevel = Carrier::getInstance()->getObjConfig()->getDebug("debuglogging");
        }

        $this->strFilename = $strLogfile;
        $this->intLogLevel = $intLogLevel;

        $objFileHandler = new RotatingFileHandler(_realpath_."project/log/".$strLogfile, 0, $this->toMonologLevel($intLogLevel));

        $this->objLogger = new \Monolog\Logger($strLogfile);
        $this->objLogger->pushHandler($objFileHandler);
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
    public function emergency($message, array $context = array())
    {
        $this->objLogger->emergency($message, $context);
    }

    /**
     * @inheritdoc
     */
    public function alert($message, array $context = array())
    {
        $this->objLogger->alert($message, $context);
    }

    /**
     * @inheritdoc
     */
    public function critical($message, array $context = array())
    {
        $this->objLogger->critical($message, $context);
    }

    /**
     * @inheritdoc
     */
    public function error($message, array $context = array())
    {
        $this->objLogger->error($message, $context);
    }

    /**
     * @inheritdoc
     */
    public function warning($message, array $context = array())
    {
        $this->objLogger->warning($message, $context);
    }

    /**
     * @inheritdoc
     */
    public function notice($message, array $context = array())
    {
        $this->objLogger->notice($message, $context);
    }

    /**
     * @inheritdoc
     */
    public function info($message, array $context = array())
    {
        $this->objLogger->info($message, $context);
    }

    /**
     * @inheritdoc
     */
    public function debug($message, array $context = array())
    {
        $this->objLogger->debug($message, $context);
    }

    /**
     * @inheritdoc
     */
    public function log($level, $message, array $context = array())
    {
        $this->objLogger->log($level, $message, $context);
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
        $this->objLogger->log($this->toMonologLevel($intLevel), $strMessage);
    }

    /**
     * Returns the complete log-file as one string
     *
     * @return string
     * @deprecated
     */
    public function getLogFileContent()
    {
        // @TODO this cant work anymore since the filename is handled by monolog
        return "";
    }


    /**
     * Returns the complete log-file as one string
     *
     * @return string
     * @deprecated
     */
    public function getPhpLogFileContent()
    {
        // @TODO this cant work anymore since the filename is handled by monolog
        return "";
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

    /**
     * @param int $intLevel
     * @return int
     */
    private function toMonologLevel($intLevel)
    {
        if ($intLevel == self::$levelError) {
            return \Monolog\Logger::ERROR;
        } elseif ($intLevel == self::$levelWarning) {
            return \Monolog\Logger::WARNING;
        } elseif ($intLevel == self::$levelInfo) {
            return \Monolog\Logger::INFO;
        } elseif ($intLevel >= 4) {
            return \Monolog\Logger::DEBUG;
        } else {
            return \Monolog\Logger::ERROR;
        }
    }
}
