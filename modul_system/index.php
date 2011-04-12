<?php
/*"******************************************************************************************************
*   (c) 2004-2006 by MulchProductions, www.mulchprod.de                                                 *
*   (c) 2007-2011 by Kajona, www.kajona.de                                                              *
*       Published under the GNU LGPL v2.1, see /system/licence_lgpl.txt                                 *
*-------------------------------------------------------------------------------------------------------*
*   $Id$                                                   *
********************************************************************************************************/

//helper for bad bad bad cases
function rawIncludeError($strFileMissed) {
    $strErrormessage = "<html><head></head><body><div style=\"border: 1px solid red; padding: 5px; margin: 20px; font-family: arial,verdana; font-size: 12px; \">\n";
    $strErrormessage .= "<div style=\"background-color: #cccccc; color: #000000; font-weight: bold; \">An error occured:</div>\n";
    $strErrormessage .= "Error including necessary files. Can't proceed.<br />";
    $strErrormessage .= "Searched for ".$strFileMissed." but failed. Going home now...<br />";
    $strErrormessage .= "</div></body></html>";
	die($strErrormessage);
}

//Requiering the includes
if(!@include_once("./system/includes.php")) {
    rawIncludeError("./system/includes.php");
}



//Determin the area to load
if(issetGet("admin") && getGet("admin") == 1)
	define("_admin_", true);
else
	define("_admin_", false);


// --- The Index Class ----------------------------------------------------------------------------------

/**
 * This class controlles the next steps
 *
 * @package modul_system
 */
class class_index  {
	private $arrTimestampStart;
	private $strOutput;
	/**
	 * class db
	 *
	 * @var class_db
	 */
	private $objDB;

	/**
	 * class template
	 *
	 * @var class_template
	 */
	private $objTemplate;

	/**
	 * class session
	 *
	 * @var class_session
	 */
	private $objSession;

	public function __construct() {

		$this->strOutput = "";
		$objCarrier = class_carrier::getInstance();
		$this->objDB = $objCarrier->getObjDb();
		$this->objTemplate = $objCarrier->getObjTemplate();
		$this->objSession = $objCarrier->getObjSession();

		if(_timedebug_)
			$this->arrTimestampStart = gettimeofday();
	}


	/**
	 * Loads the admin-area. Creates an instance of the requested module and passes control
	 *
	 */
	public function loadAdmin() {
		//Loading the details for the wanted module
		if(issetGet("module") && getGet("module") != "")
			$strModule = getGet("module");
		else
			$strModule = "dashboard";

		if(issetGet("action"))
			$strAction = getGet("action");
		else
			$strAction = "";

		$strModule = htmlspecialchars($strModule);
		$strAction = htmlspecialchars($strAction);

		//Redirect to https?
		if(_admin_only_https_ == "true") {
            //check which headers to compare
            $strHeaderName = class_carrier::getInstance()->getObjConfig()->getConfig("https_header");
            $strHeaderValue = class_carrier::getInstance()->getObjConfig()->getConfig("https_header_value");

            if($strHeaderName == "")
                $strHeaderName = "HTTPS";

            //header itself given?
		    if(!issetServer($strHeaderName) ) {
                //reload to https
                header("Location: ".uniStrReplace("http:", "https:", _indexpath_)."?".getServer("QUERY_STRING"));
                die("Reloading using https...");
		    }
            //value of header correct?
            else if($strHeaderValue != "" && $strHeaderValue != getServer($strHeaderName)) {
                //reload to https
                header("Location: ".uniStrReplace("http:", "https:", _indexpath_)."?".getServer("QUERY_STRING"));
                die("Reloading using https...");
            }
		}

		if($strModule != "login") {
			$objModulData  = $this->getModuleData($strModule);
			//Module existing?
			if($objModulData != null && $objModulData->getStrNameAdmin() != "") {
				if(_admin_) {
					if($this->objSession->isLoggedin()) {
						if($this->objSession->isAdmin()) {
						    //any reaction on language-commmands?
						    if(issetGet("language")) {
						        //languages installed?
						        $objLanguages = $this->getModuleData("languages");
						        if($objLanguages != null && $objLanguages->getStrNameAdmin() != "") {
						            $objLanguage = new class_modul_languages_language();
						            $objLanguage->setStrAdminLanguageToWorkOn(getGet("language"));
						        }
						    }

							//creating an instance of the wanted module
							$strClassname = uniStrReplace(".php", "", $objModulData->getStrNameAdmin());
							$objModule = new $strClassname();
							if(!$objModule instanceof interface_admin || !$objModule instanceof class_admin )
							    throw new class_exception("Module not implementing interface_admin", class_exception::$level_FATALERROR);
							$objModule->action($strAction);
							//React, if admin was opened by the portaleditor
							if(getPost("peClose") == "1" || getGet("peClose") == "1")
							    $this->strOutput = "<html><head></head><body onload=\"parent.location.reload();\"></body></html>";
							else
							    $this->strOutput = $objModule->getModuleOutput();
						}
						else {
						    throw new class_exception("Sorry, but you don't have the needed permissions to access the admin-area", class_exception::$level_FATALERROR);
						}
					}
					else {
						//Loading the login-Object
						$objModule = new class_modul_login_admin();
						$objModule->action("login");
						$this->strOutput = $objModule->getModuleOutput();
					}
				}
			}
			else {
                //try to load the installer, if available. in addition, an emtpy db should be found
    		    if(count($this->objDB->getTables()) == 0 && file_exists(_realpath_."/installer/installer.php")) {
    		        header("Location: "._webpath_."/installer/installer.php");
    		        throw new class_exception("Requested Module '".$strModule."' not exisiting, redirect to installer", class_exception::$level_ERROR);
    		    }
			    throw new class_exception("Requested Module '".$strModule."' not exisiting!", class_exception::$level_FATALERROR);
			}
		}
		elseif ($strModule == "login") {
			$objModule = new class_modul_login_admin();
			$objModule->action($strAction);
            $this->strOutput = $objModule->getModuleOutput();
		}
	}


	/**
	 * Loads the portal. Invokes the stats, if installed, an passes control to modul_pages
	 *
	 */
	public function loadPortal() {

	    //check, if languages are installed
	    $objLanguages = $this->getModuleData("languages");
	    if($objLanguages != null && $objLanguages->getStrNameAdmin() != "") {
            $objLanguage = new class_modul_languages_language();
            //any reaction on language-commmands?
            if(issetGet("language"))
	            $objLanguage->setStrPortalLanguage(getGet("language"));
	    }


		//if stats are installed, log the request now
		$objStatsModul = $this->getModuleData("stats");
		if($objStatsModul != null && $objStatsModul->getStrNamePortal() != "") {
			$strClassname = uniStrReplace(".php", "", $objStatsModul->getStrNamePortal());
			$objStats = new $strClassname();
			$objStats->insertStat();
		}


		//Loading the pages-module
		$objPagesModule = $this->getModuleData("pages");

		//Create Object the object
		if($objPagesModule != null && $objPagesModule->getStrNamePortal() != "") {
			$strClassname = uniStrReplace(".php", "", $objPagesModule->getStrNamePortal());
			$objPages = new $strClassname();
			//Load the Elements & generate the page
			$objPages->generatePage();
			//Load the templates
			$this->strOutput = $objPages->getModuleOutput();
		}
		else {
		    //try to load the installer, if available
		    if(count($this->objDB->getTables()) == 0 && file_exists(_realpath_."/installer/installer.php")) {
		        header("Location: "._webpath_."/installer/installer.php");
		        throw new class_exception("Module Pages not installed, redirect to installer", class_exception::$level_ERROR);
		    }
			throw new class_exception("Module Pages not installed!", class_exception::$level_FATALERROR);
		}


	}

	public function getOutput() {
		$strDebug = "";
		//Cleaning up the output
		$this->objTemplate->setTemplate($this->strOutput);
		$this->objTemplate->fillConstants();
		$this->objTemplate->deletePlaceholder();

		$this->strOutput = $this->objTemplate->getTemplate();
		//Update masked placeholders
		$this->strOutput = str_replace("\%\%", "%%", $this->strOutput);

		if(_timedebug_ || _dbnumber_ || _templatenr_ || _memory_) {

			$strDebug .= "<pre>Kajona Debug: ";

    		//Maybe we need the time used to generate this page
    		if(_timedebug_ === true) {
    			$arrTimestampEnde = gettimeofday();
    			$intTimeUsed = (($arrTimestampEnde['sec'] * 1000000 + $arrTimestampEnde['usec'])
    							-($this->arrTimestampStart['sec'] * 1000000 + $this->arrTimestampStart['usec']))/1000000;

    			 $strDebug .= "<b>PHP-Time:</b> ".number_format($intTimeUsed, 6)." sec ";
    		}

    		//Hows about the queries?
    		if(_dbnumber_ === true) {
    			$strDebug .= "<b>Queries db/cachesize/cached/fired:</b> ".$this->objDB->getNumber()."/".$this->objDB->getCacheSize()."/".$this->objDB->getNumberCache()."/".($this->objDB->getNumber()-$this->objDB->getNumberCache())." ";
    		}

    		//anything to say about the templates?
    		if(_templatenr_ === true) {
    			$strDebug .= "<b>Templates cached:</b> ".$this->objTemplate->getNumberCacheSize()." ";
    		}

    		//memory
    		if(_memory_ === true) {
    		    $strDebug .= "<b>Memory:</b> ".bytesToString(memory_get_usage())." ";
    		}

            //and check the cache-stats
            if(_cache_ === true) {
    		    $strDebug .= "<b>Cache requests/hits/saves/cachesize:</b> ".class_cache::getIntRequests()."/".class_cache::getIntHits()."/".class_cache::getIntSaves()."/".class_cache::getIntCachesize()." ";
    		}

			$strDebug .= "</pre>\n";
		}



	    //check headers, maybe execution could be terminated right here
	    //yes, this doesn't save us from generating the page, but the traffic towards the client can be reduced
        if(checkConditionalGetHeaders(md5($_SERVER["REQUEST_URI"].session_id().$this->strOutput))) {
            return;
        }

        //send headers if not an ie
        if(strpos(getServer("HTTP_USER_AGENT"), "IE") === false)
            sendConditionalGetHeaders(md5($_SERVER["REQUEST_URI"].session_id().$this->strOutput));

		//compress output
		$objGzip = new class_gzip();
		return $objGzip->compressOutput($strDebug.$this->strOutput);
	}


	/**
	 * Returns the data for a registered module
	 *
	 * @param string $strName
	 * @return class_modul_system_module
	 */
	public function getModuleData($strName) {
	    return class_modul_system_module::getModuleByName($strName);
	}


}

//And loading all the stuff
//Here we go - loading the index-oject
$objIndex = new class_index();
if(_admin_) {
	$objIndex->loadAdmin();
	header('Content-Type: text/html; charset=utf-8');
	echo $objIndex->getOutput();
}
else {
	$objIndex->loadPortal();
	header('Content-Type: text/html; charset=utf-8');
	echo $objIndex->getOutput();
}
?>