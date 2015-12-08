<?php
/*"******************************************************************************************************
*   (c) 2004-2006 by MulchProductions, www.mulchprod.de                                                 *
*   (c) 2007-2015 by Kajona, www.kajona.de                                                              *
*       Published under the GNU LGPL v2.1, see /system/licence_lgpl.txt                                 *
*-------------------------------------------------------------------------------------------------------*
*	$Id$                                                 *
********************************************************************************************************/

/**
 * @package module_system
 */


//helper for bad bad bad cases
function rawIncludeError($strFileMissed) {
    $strErrorMessage = "<html><head></head><body><div style=\"border: 1px solid red; padding: 5px; margin: 20px; font-family: arial,verdana, serif; font-size: 12px; \">\n";
    $strErrorMessage .= "<div style=\"background-color: #cccccc; color: #000000; font-weight: bold; \">An error occurred:</div>\n";
    $strErrorMessage .= "Error including necessary files. Can't proceed.<br />";
    $strErrorMessage .= "Searched for ".$strFileMissed." but failed. Going home now...<br />";
    $strErrorMessage .= "</div></body></html>";
    header("HTTP/1.0 500 Internal Server Error");
    die($strErrorMessage);
}

//---The Path on the filesystem--------------------------------------------------------------------------

//Determine the current path on the filesystem. Use the dir-name of the current file minus core/module_system
//fetch the current include path - phar or filesystem based
if(substr(__DIR__, 0, 7) == "phar://") {
    define("_realpath_", str_replace(" ", "\040", substr(__DIR__, 7, -23)));
}
else {
    define("_realpath_", str_replace(" ", "\040", substr(__DIR__, 0, -18)));
}


define("_corepath_", str_replace(" ", "\040", substr(__DIR__, 0, -13)));

//--- Loader pre-configuration
if(!defined("_xmlLoader_"))
    define("_xmlLoader_", false);

//---Include Section 1-----------------------------------------------------------------------------------

//Setting up the default timezone, determined by the server / environment. may be redefined by _system_timezone_
@date_default_timezone_set(date_default_timezone_get());

//Functions to have fun & check for mb-string
$intI = __DIR__;
if(!@include_once __DIR__."/system/functions.php")
    rawIncludeError(__DIR__."/system/functions.php");

//Exception-Handler
if(!@include_once __DIR__."/system/class_exception.php")
    rawIncludeError("global exception handler");
//register global exception handler for exceptions thrown but not catched (bad style ;) )
@set_exception_handler(array("class_exception", "globalExceptionHandler"));

//Include the logging-engine
if(!@include_once __DIR__."/system/class_logger.php")
    rawIncludeError("logging engine");


//see if there's a custom bootstrap.php to include
if(file_exists(_realpath_."/project/bootstrap.php"))
    @include_once _realpath_."/project/bootstrap.php";

//---The Path on web-------------------------------------------------------------------------------------

require_once __DIR__."/system/class_config.php";
$strHeaderName = class_config::readPlainConfigsFromFilesystem("https_header");
$strHeaderValue = strtolower(class_config::readPlainConfigsFromFilesystem("https_header_value"));

if(!defined("_webpath_")) {
    if (strpos($_SERVER['SCRIPT_FILENAME'], "/debug/")) {
        //Determine the current path on the web
        $strWeb = dirname(
            (isset($_SERVER[$strHeaderName]) && (strtolower($_SERVER[$strHeaderName]) == $strHeaderValue) ? "https://" : "http://").
            $_SERVER['HTTP_HOST'].$_SERVER['SCRIPT_NAME']
        );
        define("_webpath_", saveUrlEncode(substr_replace($strWeb, "", strrpos($strWeb, "/"))));
    }
    else {
        //Determine the current path on the web
        $strWeb = dirname(
            (isset($_SERVER[$strHeaderName]) && (strtolower($_SERVER[$strHeaderName]) == $strHeaderValue) ? "https://" : "http://").
            (isset($_SERVER['HTTP_HOST']) ? $_SERVER['HTTP_HOST'] : "localhost").$_SERVER['SCRIPT_NAME']
        );
        define("_webpath_", saveUrlEncode($strWeb));
    }

}

//---Auto-Loader for classes-----------------------------------------------------------------------------
require_once __DIR__."/system/class_classloader.php";
spl_autoload_register(array(class_classloader::getInstance(), "loadClass"));

//The Carrier-Class
if(!include_once __DIR__."/system/class_carrier.php")
    rawIncludeError("carrier-class");


//trigger the phar-extractor-----------------------------------------------------------------------------
\Kajona\System\System\PharModuleExtractor::bootstrapPharContent();
