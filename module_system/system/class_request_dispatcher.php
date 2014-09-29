<?php
/*"******************************************************************************************************
*   (c) 2004-2006 by MulchProductions, www.mulchprod.de                                                 *
*   (c) 2007-2014 by Kajona, www.kajona.de                                                              *
*       Published under the GNU LGPL v2.1, see /system/licence_lgpl.txt                                 *
*-------------------------------------------------------------------------------------------------------*
*	$Id$                                               *
********************************************************************************************************/

/**
 * The request-dispatcher is called by all external request-entries and acts as a controller.
 * It dispatches the requests to the matching modules and areas, taking care of login-status and more.
 *
 * @package module_system
 * @author sidler@mulchprod.de
 * @since 3.4.1
 */
class class_request_dispatcher {

    private $arrTimestampStart;

    /**
     * @var class_response_object
     */
    private $objResponse = null;

    /**
     * @var class_session
     */
    private $objSession;

    /**
     * Standard constructor
     *
     * @param class_response_object $objResponse
     *
     * @return \class_request_dispatcher
     */
    public function __construct(class_response_object $objResponse) {
        $this->arrTimestampStart = gettimeofday();
        $this->objSession = class_carrier::getInstance()->getObjSession();
        $this->objResponse = $objResponse;
    }

    /**
     * Global controller entry, triggers all further actions, splits up admin- and portal loading
     *
     * @param bool $bitAdmin
     * @param string $strModule
     * @param string $strAction
     * @param string $strLanguageParam
     *
     * @return string
     */
    public function processRequest($bitAdmin, $strModule, $strAction, $strLanguageParam) {

        class_core_eventdispatcher::getInstance()->notifyGenericListeners(class_system_eventidentifier::EVENT_SYSTEM_REQUEST_STARTPROCESSING, array($bitAdmin, $strModule, $strAction, $strLanguageParam));

        if($bitAdmin) {
            $strReturn = $this->processAdminRequest($strModule, $strAction, $strLanguageParam);
            $strReturn = $this->callScriptlets($strReturn, interface_scriptlet::BIT_CONTEXT_ADMIN);
        }
        else {
            $strReturn = $this->processPortalRequest($strModule, $strAction, $strLanguageParam);
            $strReturn = $this->callScriptlets($strReturn, interface_scriptlet::BIT_CONTEXT_PORTAL_PAGE);
        }


        $strReturn = $this->cleanupOutput($strReturn);
        $strReturn = $this->getDebugInfo($strReturn);
        $this->sendConditionalGetHeaders($strReturn);

        $this->objResponse->setStrContent($strReturn);

        class_core_eventdispatcher::getInstance()->notifyGenericListeners(class_system_eventidentifier::EVENT_SYSTEM_REQUEST_ENDPROCESSING, array($bitAdmin, $strModule, $strAction, $strLanguageParam));

        $this->objSession->sessionClose();
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
            if(!issetServer($strHeaderName)) {
                //reload to https
                if(_xmlLoader_ === true) {
                    class_response_object::getInstance()->setStrRedirectUrl(uniStrReplace("http:", "https:", _xmlpath_) . "?" . getServer("QUERY_STRING"));
                }
                else {
                    class_response_object::getInstance()->setStrRedirectUrl(uniStrReplace("http:", "https:", _indexpath_) . "?" . getServer("QUERY_STRING"));
                }

                class_response_object::getInstance()->sendHeaders();
                die("Reloading using https...");
            }
            //value of header correct?
            else if($strHeaderValue != "" && $strHeaderValue != strtolower(getServer($strHeaderName))) {
                //reload to https
                if(_xmlLoader_ === true) {
                    class_response_object::getInstance()->setStrRedirectUrl(uniStrReplace("http:", "https:", _xmlpath_) . "?" . getServer("QUERY_STRING"));
                }
                else {
                    class_response_object::getInstance()->setStrRedirectUrl(uniStrReplace("http:", "https:", _indexpath_) . "?" . getServer("QUERY_STRING"));
                }

                class_response_object::getInstance()->sendHeaders();
                die("Reloading using https...");
            }
        }

        //process language-param
        $objLanguage = new class_module_languages_language();
        $objLanguage->setStrAdminLanguageToWorkOn($strLanguageParam);

        //set the current backend skin. right here to do it only once.
        class_adminskin_helper::defineSkinWebpath();

        //validate login-status / process login-request
        if($strModule != "login" && $this->objSession->isLoggedin()) {
            if($this->objSession->isAdmin()) {
                //try to load the module
                $objModuleRequested = class_module_system_module::getModuleByName($strModule);
                if($objModuleRequested != null) {

                    //see if there is data from a previous, failed request
                    if(class_carrier::getInstance()->getObjSession()->getSession(class_module_login_admin::SESSION_LOAD_FROM_PARAMS) === "true") {
                        foreach(class_carrier::getInstance()->getObjSession()->getSession(class_module_login_admin::SESSION_PARAMS) as $strOneKey => $strOneVal)
                            class_carrier::getInstance()->setParam($strOneKey, $strOneVal);

                        class_carrier::getInstance()->getObjSession()->sessionUnset(class_module_login_admin::SESSION_LOAD_FROM_PARAMS);
                        class_carrier::getInstance()->getObjSession()->sessionUnset(class_module_login_admin::SESSION_PARAMS);
                    }


                    if(_xmlLoader_) {
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

                        //fill the history array to track actions
                        $objHistory = new class_history();
                        //Writing to the history
                        if(class_carrier::getInstance()->getParam("folderview") == "") {
                            $objHistory->setAdminHistory();
                        }

                        $objConcreteModule = $objModuleRequested->getAdminInstanceOfConcreteModule();


                        //React, if admin was opened by the portaleditor
                        if(class_carrier::getInstance()->getParam("peClose") == "1") {

                            if(class_carrier::getInstance()->getParam("blockAction") != "1")
                                $objConcreteModule->action();

                            if(getGet("peRefreshPage") != "") {
                                $strReturn = "<html><head></head><body onload=\"parent.location = '" . urldecode(getGet("peRefreshPage")) . "';\"></body></html>";
                            }
                            else {
                                $strReturn = "<html><head></head><body onload=\"parent.location.reload();\"></body></html>";
                            }
                        }
                        else {
                            $objConcreteModule->action();
                            $strReturn = $objConcreteModule->getModuleOutput();
                        }

                    }

                }
                else {
                    throw new class_exception("Requested module " . $strModule . " not existing", class_exception::$level_FATALERROR);
                }
            }
            else {
                throw new class_exception("Sorry, but you don't have the needed permissions to access the admin-area", class_exception::$level_FATALERROR);
            }
        }
        else {
            $bitLogin = true;

            if($strModule != "login") {
                $strAction = "";
            }
        }

        if($bitLogin) {
            if(_xmlLoader_) {
                $objLogin = new class_module_login_admin_xml();
                $strReturn = $objLogin->action($strAction);
            }
            else {

                if(count(class_carrier::getInstance()->getObjDB()->getTables()) == 0 && file_exists(_realpath_ . "/installer.php")) {
                    class_response_object::getInstance()->setStrRedirectUrl(_webpath_ . "/installer.php");
                    return "";
                }

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


        //Load the portal parts
        $objModule = class_module_system_module::getModuleByName($strModule);
        if($objModule != null) {

            if(_xmlLoader_) {
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
                if($strModule == "pages") {
                    $strAction = "";
                }

                //fill the history array to track actions
                $objHistory = new class_history();
                $objHistory->setPortalHistory();

                $objModuleRequested = $objModule->getPortalInstanceOfConcreteModule();
                $strReturn = $objModuleRequested->action($strAction);
            }

        }
        else {

            if(_xmlLoader_ === false) {
                if(count(class_carrier::getInstance()->getObjDB()->getTables()) == 0 && file_exists(_realpath_ . "/installer.php")) {
                    class_response_object::getInstance()->setStrRedirectUrl(_webpath_ . "/installer.php");
                    return "";
                    //throw new class_exception("Module Pages not installed, redirect to installer", class_exception::$level_ERROR);
                }
            }

            throw new class_exception("module " . $strModule . " not installed!", class_exception::$level_FATALERROR);
        }


        return $strReturn;
    }

    /**
     * Strips unused contents from the generated output, e.g. placeholders
     *
     * @param string $strContent
     *
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
     * @param string $strContent
     * @param int $intContext
     *
     * @return string
     */
    private function callScriptlets($strContent, $intContext) {
        $objScriptlet = new class_scriptlet_helper();
        return $objScriptlet->processString($strContent, $intContext);
    }


    /**
     * Sends conditional get headers and tries to match sent ones.
     *
     * @param string $strContent
     * @return void
     */
    private function sendConditionalGetHeaders($strContent) {

        //check headers, maybe execution could be terminated right here
        //yes, this doesn't save us from generating the page, but the traffic towards the client can be reduced
        if(checkConditionalGetHeaders(md5($_SERVER["REQUEST_URI"] . $this->objSession->getSessionId() . $strContent))) {
            class_response_object::getInstance()->sendHeaders();
            flush();
            die();
        }

        //send headers if not an ie
        if(strpos(getServer("HTTP_USER_AGENT"), "IE") === false) {
            setConditionalGetHeaders(md5($_SERVER["REQUEST_URI"] . $this->objSession->getSessionId() . $strContent));
        }
    }


    /**
     * Generates debugging-infos, but only in non-xml mode
     *
     * @param string $strReturn
     *
     * @return string
     */
    private function getDebugInfo($strReturn) {
        $strDebug = "";
        if(_timedebug_ || _dbnumber_ || _templatenr_ || _memory_) {

            //Maybe we need the time used to generate this page
            if(_timedebug_ === true) {
                $arrTimestampEnde = gettimeofday();
                $intTimeUsed = (($arrTimestampEnde['sec'] * 1000000 + $arrTimestampEnde['usec'])
                    - ($this->arrTimestampStart['sec'] * 1000000 + $this->arrTimestampStart['usec'])) / 1000000;

                $strDebug .= "<b>PHP-Time:</b> " . number_format($intTimeUsed, 6) . " sec ";
            }

            //Hows about the queries?
            if(_dbnumber_ === true) {
                $strDebug .= "<b>Queries db/cachesize/cached/fired:</b> " . class_carrier::getInstance()->getObjDB()->getNumber() . "/" .
                    class_carrier::getInstance()->getObjDB()->getCacheSize() . "/" .
                    class_carrier::getInstance()->getObjDB()->getNumberCache() . "/" .
                    (class_carrier::getInstance()->getObjDB()->getNumber() - class_carrier::getInstance()->getObjDB()->getNumberCache()) . " ";
            }

            //anything to say about the templates?
            if(_templatenr_ === true) {
                $strDebug .= "<b>Templates cached:</b> " . class_carrier::getInstance()->getObjTemplate()->getNumberCacheSize() . " ";
            }

            //memory
            if(_memory_ === true) {
                $strDebug .= "<b>Memory/Max Memory:</b> " . bytesToString(memory_get_usage()) . "/" . bytesToString(memory_get_peak_usage()) . " ";
                $strDebug .= "<b>Classes Loaded:</b> " . class_classloader::getInstance()->getIntNumberOfClassesLoaded() . " ";
            }

            //and check the cache-stats
            if(_cache_ === true) {
                $strDebug .= "<b>Cache requests/hits/saves/cachesize:</b> " .
                    class_cache::getIntRequests() . "/" . class_cache::getIntHits() . "/" . class_cache::getIntSaves() . "/" . class_cache::getIntCachesize() . " ";
            }

            if(_xmlLoader_ === true) {
                class_response_object::getInstance()->addHeader("Kajona Debug: ".$strDebug);
            }
            else {
                $strDebug = "<pre style='z-index: 2000000; position: fixed; background-color: white; width: 100%; top: 0px; font-size: 10px; padding: 0; margin: 0;'>Kajona Debug: " . $strDebug . "</pre>";

                $intBodyPos = uniStrpos($strReturn, "</body>");
                if($intBodyPos !== false) {
                    $strReturn = uniSubstr($strReturn, 0, $intBodyPos).$strDebug.uniSubstr($strReturn, $intBodyPos);
                }
                else
                    $strReturn = $strDebug.$strReturn;
            }


        }

        return $strReturn;
    }

}

