<?php
/*"******************************************************************************************************
*   (c) 2004-2006 by MulchProductions, www.mulchprod.de                                                 *
*   (c) 2007-2012 by Kajona, www.kajona.de                                                              *
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

    /**
     * Level to be used for real errors
     *
     * @var int
     * @static
     */
    public static $levelError = 0;

    /**
     * Level to be used for warnings
     *
     * @var int
     * @static
     */
    public static $levelWarning = 1;

    /**
     * Level to be used for infos
     *
     * @var int
     * @static
     */
    public static $levelInfo = 2;

    /**
     * Instance of the logger
     *
     * @var class_logger
     */
    private static $objInstance = null;

    /**
     * Constant defining the filename
     *
     * @var string
     */
    private $strFilename = "systemlog.log";

    private $intLogLevel = 0;

    /**
     * Doing nothing but being private
     *
     */
    private function __construct() {
        $this->intLogLevel = class_carrier::getInstance()->getObjConfig()->getDebug("debuglogging");
    }

    /**
     * returns the current instance of this class
     *
     * @return class_logger
     */
    public static function getInstance() {
        if (class_logger::$objInstance == null)
            class_logger::$objInstance = new class_logger();

        return self::$objInstance;
    }

    /**
     * Adds a row to the current log
     * For $intLevel use on of the static level provided by this class
     *
     * @param string $strMessage
     * @param int $intLevel
     */
    public function addLogRow($strMessage, $intLevel) {

        //check, if there someting to write
        if($this->intLogLevel == 0)
            return;
        //errors in level >=1
        if($intLevel == self::$levelError && $this->intLogLevel < 1)
            return;
        //warnings in level >=2
        if($intLevel == self::$levelWarning && $this->intLogLevel < 2)
            return;
        //infos in level >=3
        if($intLevel == self::$levelInfo && $this->intLogLevel < 3)
            return;

        //a log row has the following scheme:
        // YYYY-MM-DD HH:MM:SS LEVEL USERID (USERNAME) MESSAGE
        $strDate = strftime("%Y-%m-%d %H:%M:%S", time());
        $strLevel = "";
        if($intLevel == self::$levelError)
            $strLevel = "ERROR";
        elseif ($intLevel == self::$levelInfo)
            $strLevel = "INFO";
        elseif ($intLevel == self::$levelWarning)
            $strLevel = "WARNING";

        $strSessid = "";
        if(class_carrier::getInstance()->getObjSession()->getBitLazyLoaded()) {
            $strSessid = class_carrier::getInstance()->getObjSession()->getInternalSessionId();
            $strSessid .= " (".class_carrier::getInstance()->getObjSession()->getUsername().")";
        }

        $strMessage = uniStrReplace(array("\r", "\n"), array(" ", " "), $strMessage);

        $strText = $strDate." ".$strLevel." ".$strSessid." ".$strMessage."\r\n";

		$handle = fopen(_realpath_._projectpath_."/log/".$this->strFilename, "a");
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
        $objFile->openFilePointer(_projectpath_."/log/".$this->strFilename, "r");
        return $objFile->readLastLinesFromFile(25);
    }


    /**
     * Returns the complete log-file as one string
     *
     * @return string
     */
    public function getPhpLogFileContent() {
        $objFile = new class_filesystem();
        $objFile->openFilePointer(_projectpath_."/log/php.log", "r");
        return $objFile->readLastLinesFromFile(25);
    }
}


