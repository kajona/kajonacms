<?php
/*"******************************************************************************************************
*   (c) 2004-2006 by MulchProductions, www.mulchprod.de                                                 *
*   (c) 2007-2009 by Kajona, www.kajona.de                                                              *
*       Published under the GNU LGPL v2.1, see /system/licence_lgpl.txt                                 *
*-------------------------------------------------------------------------------------------------------*
*	$Id$                                                 *
********************************************************************************************************/

/**
 * @package modul_system
 *
 */

//---The Path on the filesystem------------------------------------------------------------------------------
	//Determing the current path on the filesystem. Use the dirname of the current file, cut "/system"
	define("_realpath_",  substr(dirname(__FILE__), 0, -7));

//---Include Section 1-----------------------------------------------------------------------------------
	//Functions to have fun & check for mb-string
	if(!@include_once(_realpath_."/system/functions.php"))
		rawIncludeError("./system/functions.php");

	//Exception-Handler
	if(!@include_once(_realpath_."/system/class_exception.php"))
		rawIncludeError("global exception handler");
	//register global exception handler for exceptions thrown but not catched (bad style ;) )
	@set_exception_handler(array("class_exception", "globalExceptionHandler"));

	//Include the logging-engine
    if(!@include_once(_realpath_."/system/class_logger.php"))
		rawIncludeError("logging engine");


//---The Path on web-------------------------------------------------------------------------------------
	if(strpos($_SERVER['SCRIPT_FILENAME'], "installer.php") || strpos($_SERVER['SCRIPT_FILENAME'], "autotest.php")) {
		//Determing the current path on the web
		$strWeb = dirname((isset($_SERVER["HTTPS"]) && $_SERVER["HTTPS"] == "on" ? "https://" : "http://").$_SERVER['HTTP_HOST'].$_SERVER['SCRIPT_NAME']);
		$strWeb = substr_replace($strWeb, "", strrpos($strWeb, "/"));
		define("_webpath_", saveUrlEncode($strWeb));
	}
	else {
		//Determing the current path on the web
		$strWeb = dirname((isset($_SERVER["HTTPS"]) && $_SERVER["HTTPS"] == "on" ? "https://" : "http://").$_SERVER['HTTP_HOST'].$_SERVER['SCRIPT_NAME']);
		$strWeb = saveUrlEncode($strWeb);
		define("_webpath_", $strWeb);
	}

//---Include Section 2-----------------------------------------------------------------------------------
	//Modul-Constants
	foreach(scandir(_realpath_."/system/config/") as $strDirEntry ) {
	   if(preg_match("/modul\_([a-z])+\_id\.php/", $strDirEntry))
	       @include_once(_realpath_."/system/config/".$strDirEntry);
	}

	//The Carrier-Class
	if(!@include_once(_realpath_."/system/class_carrier.php"))
		rawIncludeError("carrier-class");

//---Autoloader for classes------------------------------------------------------------------------------

    function __autoload($strClassName) {

        //---ADMIN CLASSES-------------------------------------------------------------------------------
        //adminwidgets
        if(preg_match("/(class|interface)_adminwidget(.*)/", $strClassName)) {
            if(require(_adminpath_."/widgets/".$strClassName.".php"))
                return;
        }

        //systemtasks
        if(preg_match("/(class|interface)(.*)systemtask(.*)/", $strClassName)) {
            if(require(_adminpath_."/systemtasks/".$strClassName.".php"))
                return;
        }

        //statsreports
        if(preg_match("/(class)_(.*)stats_report(.*)/", $strClassName)) {
            if(require(_adminpath_."/statsreports/".$strClassName.".php"))
                return;
        }

        //admin classes
        //TODO: wtf? why strpos needed? whats wron with that regex?
        if(preg_match("/(class|interface)_(.*)admin(_xml)?/", $strClassName) && !strpos($strClassName, "adminwidget")) {
            if(require(_adminpath_."/".$strClassName.".php"))
                return;
        }


        //---PORTAL CLASSES------------------------------------------------------------------------------

        //search plugins
        if(preg_match("/interface_search(.*)/", $strClassName)) {
            if(require(_portalpath_."/searchplugins/".$strClassName.".php"))
                return;
        }

        //portal classes
        if(preg_match("/(class|interface)_(.*)portal(.*)/", $strClassName)) {
            if(require(_portalpath_."/".$strClassName.".php"))
                return;
        }

        //---SYSTEM CLASSES------------------------------------------------------------------------------
        //db-drivers
        //system-classes
        if(preg_match("/(class|interface)_db_(.*)/", $strClassName)) {
            if(require(_systempath_."/db/".$strClassName.".php"))
                return;
        }

        //system-classes
        if(preg_match("/(class|interface)_(.*)/", $strClassName)) {
            if(require(_systempath_."/".$strClassName.".php"))
                return;
        }

        

        

        
        
    }


?>