<?php
/*"******************************************************************************************************
*   (c) 2004-2006 by MulchProductions, www.mulchprod.de                                                 *
*   (c) 2007-2012 by Kajona, www.kajona.de                                                              *
*       Published under the GNU LGPL v2.1, see /system/licence_lgpl.txt                                 *
*-------------------------------------------------------------------------------------------------------*
*	$Id$                                               *
********************************************************************************************************/

/**
 * The request-dispatcher is called by all external request-entries and acts as a controller.
 * It dispatches the requests to the matching modules and areas, taking care of login-status and more.
 *
 *
 * @package module_system
 * @author sidler@mulchprod.de
 * @since 3.4.1
 */
class class_request_dispatcher {

    private $arrTimestampStart;

    /**
     *
     * @var class_session
     */
    private $objSession;

    /**
     * Standard constructor
     */
    public function __construct() {
        $this->arrTimestampStart = gettimeofday();
        $this->objSession = class_carrier::getInstance()->getObjSession();
    }

    /**
     * Global controller entry, triggers all further actions, splits up admin- and portal loading
     *
     * @param bool $bitAdmin
     * @param string $strModule
     * @param string $strAction
     * @param string $strLanguageParam
     * @return string
     */
    public function processRequest($bitAdmin, $strModule, $strAction, $strLanguageParam) {

        if($bitAdmin)
            $strReturn = $this->processAdminRequest($strModule, $strAction, $strLanguageParam);
        else
            $strReturn = $this->processPortalRequest($strModule, $strAction, $strLanguageParam);


        $strReturn = $this->callScriptlets($strReturn);


        $strReturn = $this->cleanupOutput($strReturn);



        $strReturn = $this->getDebugInfo().$strReturn;

        $this->sendConditionalGetHeaders($strReturn);

        return $strReturn;
    }

    /**
     * Processes an admin-request
     *
     * @param string $strModule
     * @param string $strAction
     * @param string $strLanguageParam
     *
     * @throws class_exception
     * @return string
     */
    private function processAdminRequest($strModule, $strAction, $strLanguageParam) {
        $strReturn = "";
        $bitLogin = false;

        //validate https status
        if(_admin_only_https_ == "true") {
            //check which headers to compare
            $strHeaderName = class_carrier::getInstance()->getObjConfig()->getConfig("https_header");
            $strHeaderValue = strtolower(class_carrier::getInstance()->getObjConfig()->getConfig("https_header_value"));

            //header itself given?
		    if(!issetServer($strHeaderName) ) {
                //reload to https
                if(_xmlLoader_ === true)
                    header("Location: ".uniStrReplace("http:", "https:", _xmlpath_)."?".getServer("QUERY_STRING"));
                else
                    header("Location: ".uniStrReplace("http:", "https:", _indexpath_)."?".getServer("QUERY_STRING"));

                die("Reloading using https...");
		    }
            //value of header correct?
            else if($strHeaderValue != "" && $strHeaderValue != strtolower(getServer($strHeaderName))) {
                //reload to https
                if(_xmlLoader_ === true)
                    header("Location: ".uniStrReplace("http:", "https:", _xmlpath_)."?".getServer("QUERY_STRING"));
                else
                    header("Location: ".uniStrReplace("http:", "https:", _indexpath_)."?".getServer("QUERY_STRING"));

                die("Reloading using https...");
            }
		}

        //process language-param
        $objLanguage = new class_module_languages_language();
        $objLanguage->setStrAdminLanguageToWorkOn($strLanguageParam);

        //validate login-status / process login-request
        if($strModule != "login" && $this->objSession->isLoggedin() ) {
            if($this->objSession->isAdmin()) {
                //try to load the module
                $objModuleRequested = class_module_system_module::getModuleByName($strModule);
                if($objModuleRequested != null) {

                    if(_xmlLoader_) { //FIXME: will be removed
                        if($objModuleRequested->getStrXmlNameAdmin() != "") {
                            $strClassname = str_replace(".php", "", $objModuleRequested->getStrXmlNameAdmin());
                            $objConcreteModule = new $strClassname();
                            $strReturn = $objConcreteModule->action($strAction);
                        }
                        else {
                            //xml-loader not defined, try to use the regular dispatcher
                            $objConcreteModule = $objModuleRequested->getAdminInstanceOfConcreteModule();
                            $strReturn = $objConcreteModule->action($strAction);
                        }
                    }
                    else {
                        $objConcreteModule = $objModuleRequested->getAdminInstanceOfConcreteModule();
                        //$objConcreteModule->action($strAction);
                        $objConcreteModule->action(); //FIXME: action always set via the internal handler?
                        $strReturn = $objConcreteModule->getModuleOutput();

                        //React, if admin was opened by the portaleditor
                        if(getPost("peClose") == "1" || getGet("peClose") == "1") {
                            if(getGet("peRefreshPage") != "")
                                $strReturn = "<html><head></head><body onload=\"parent.location = '".urldecode(getGet("peRefreshPage"))."';\"></body></html>";
                            else
                                $strReturn = "<html><head></head><body onload=\"parent.location.reload();\"></body></html>";
                        }

                    }

                }
                else
                    throw new class_exception("Requested module ".$strModule." not existing", class_exception::$level_FATALERROR);
            }
            else
                throw new class_exception("Sorry, but you don't have the needed permissions to access the admin-area", class_exception::$level_FATALERROR);
        }
        else {
            $bitLogin = true;

            if($strModule != "login")
                $strAction = "";
        }

        if($bitLogin) {
            //FIXME: xml-annotations
            if(_xmlLoader_) {
                $objLogin = new class_module_login_admin_xml();
                $strReturn = $objLogin->action($strAction);
            }
            else {
                $objLogin = new class_module_login_admin();
                $objLogin->action($strAction);
                $strReturn = $objLogin->getModuleOutput();
            }

        }

        return $strReturn;

    }


    /**
     * Processes a portal-request
     *
     * @param string $strModule
     * @param string $strAction
     * @param string $strLanguageParam
     *
     * @throws class_exception
     * @return string
     */
    private function processPortalRequest($strModule, $strAction, $strLanguageParam) {
        $strReturn = "";

        //process language-param
        if(class_module_system_module::getModuleByName("languages") != null) {
            $objLanguage = new class_module_languages_language();
            $objLanguage->setStrPortalLanguage($strLanguageParam);
        }

        //process stats request
        /** @var $objStats class_module_stats_portal */
        $objStats = class_module_system_module::getModuleByName("stats");
        if($objStats != null) {
            $objStats = $objStats->getPortalInstanceOfConcreteModule();
            $objStats->insertStat();
        }


        //Load the portal parts
        $objModule = class_module_system_module::getModuleByName($strModule);
        if($objModule != null) {

            if(_xmlLoader_) { //FIXME: will be removed
                if($objModule->getStrXmlNamePortal() != "") {
                    $strClassname = str_replace(".php", "", $objModule->getStrXmlNamePortal());
                    $objModuleRequested = new $strClassname();
                    $strReturn = $objModuleRequested->action($strAction);
                }
                else {
                    $objModuleRequested = $objModule->getPortalInstanceOfConcreteModule();
                    $strReturn = $objModuleRequested->action($strAction);
                }
            }
            else {
                if($strModule == "pages")
                    $strAction = ""; //FIXME: action always set via the internal handler?
                $objModuleRequested = $objModule->getPortalInstanceOfConcreteModule();
                $strReturn = $objModuleRequested->action($strAction);
            }


        }
        else {

            if(_xmlLoader_ === false) {
                if(count(class_carrier::getInstance()->getObjDB()->getTables()) == 0 && file_exists(_realpath_."/installer/installer.php")) {
                    header("Location: "._webpath_."/installer/installer.php");
                    throw new class_exception("Module Pages not installed, redirect to installer", class_exception::$level_ERROR);
                }
            }

            throw new class_exception("module ".$strModule." not installed!", class_exception::$level_FATALERROR);
        }


        return $strReturn;
    }

    /**
     * Strips unused contents from the generated output, e.g. placeholders
     *
     * @param string $strContent
     * @return string
     */
    private function cleanupOutput($strContent) {
        $objTemplate = class_carrier::getInstance()->getObjTemplate();
        $objTemplate->setTemplate($strContent);
        $objTemplate->deletePlaceholder();
        $strContent = $objTemplate->getTemplate();
        $strContent = str_replace("\%\%", "%%", $strContent);

        return $strContent;
    }

    /**
     * Calls the scriptlets in order to process additional tags and in order to enrich the content.
     *
     * @param $strContent
     * @return string
     */
    private function callScriptlets($strContent) {
        $arrScriptletFiles = class_resourceloader::getInstance()->getFolderContent("/system/scriptlets", array(".php"));

        foreach($arrScriptletFiles as $strOneScriptlet) {
            $strOneScriptlet = uniSubstr($strOneScriptlet, 0, -4);
            /** @var $objScriptlet interface_scriptlet */
            $objScriptlet = new $strOneScriptlet();

            if($objScriptlet instanceof interface_scriptlet)
                $strContent = $objScriptlet->processContent($strContent);
        }

        return $strContent;
    }


    /**
     * Sends conditional get headers and tries to match sent ones.
     *
     * @param string $strContent
     */
    private function sendConditionalGetHeaders($strContent) {

        //check headers, maybe execution could be terminated right here
	    //yes, this doesn't save us from generating the page, but the traffic towards the client can be reduced
        if(checkConditionalGetHeaders(md5($_SERVER["REQUEST_URI"].$this->objSession->getSessionId().$strContent))) {
            flush();
            die();
        }

        //send headers if not an ie
        if(strpos(getServer("HTTP_USER_AGENT"), "IE") === false)
            sendConditionalGetHeaders(md5($_SERVER["REQUEST_URI"].$this->objSession->getSessionId().$strContent));
    }


    /**
     * Generates debugging-infos, but only in non-xml mode
     *
     * @return string
     */
    private function getDebugInfo() {
        $strDebug = "";
        if(_timedebug_ || _dbnumber_ || _templatenr_ || _memory_) {

    		//Maybe we need the time used to generate this page
    		if(_timedebug_ === true) {
    			$arrTimestampEnde = gettimeofday();
    			$intTimeUsed = (($arrTimestampEnde['sec'] * 1000000 + $arrTimestampEnde['usec'])
    							-($this->arrTimestampStart['sec'] * 1000000 + $this->arrTimestampStart['usec']))/1000000;

    			 $strDebug .= "<b>PHP-Time:</b> ".number_format($intTimeUsed, 6)." sec ";
    		}

    		//Hows about the queries?
    		if(_dbnumber_ === true) {
    			$strDebug .= "<b>Queries db/cachesize/cached/fired:</b> ".class_carrier::getInstance()->getObjDB()->getNumber()."/".
                                                        class_carrier::getInstance()->getObjDB()->getCacheSize()."/".
                                                        class_carrier::getInstance()->getObjDB()->getNumberCache()."/".
                                                        (class_carrier::getInstance()->getObjDB()->getNumber()-class_carrier::getInstance()->getObjDB()->getNumberCache())." ";
    		}

    		//anything to say about the templates?
    		if(_templatenr_ === true) {
    			$strDebug .= "<b>Templates cached:</b> ".class_carrier::getInstance()->getObjTemplate()->getNumberCacheSize()." ";
    		}

    		//memory
    		if(_memory_ === true) {
                $strDebug .= "<b>Memory/Max Memory:</b> ".bytesToString(memory_get_usage())."/".bytesToString(memory_get_peak_usage())." ";
    		}

            //and check the cache-stats
            if(_cache_ === true) {
    		    $strDebug .= "<b>Cache requests/hits/saves/cachesize:</b> ".
                    class_cache::getIntRequests()."/".class_cache::getIntHits()."/".class_cache::getIntSaves()."/".class_cache::getIntCachesize()." ";
    		}

            if(_xmlLoader_ === true)
                $strDebug = "<!-- Kajona Debug: ".$strDebug ." -->";
            else
                $strDebug = "<pre style='z-index: 2000000; position: absolute; background-color: white; width: 100%; top: 0px;'>Kajona Debug: ".$strDebug."</pre>";

            $strDebug .= "\n";

		}

        return $strDebug;
    }

}

