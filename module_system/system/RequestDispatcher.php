<?php
/*"******************************************************************************************************
*   (c) 2004-2006 by MulchProductions, www.mulchprod.de                                                 *
*   (c) 2007-2015 by Kajona, www.kajona.de                                                              *
*       Published under the GNU LGPL v2.1, see /system/licence_lgpl.txt                                 *
*-------------------------------------------------------------------------------------------------------*
*	$Id$                                               *
********************************************************************************************************/

namespace Kajona\System\System;

use Kajona\System\Admin\LoginAdmin;


/**
 * The request-dispatcher is called by all external request-entries and acts as a controller.
 * It dispatches the requests to the matching modules and areas, taking care of login-status and more.
 *
 * @package module_system
 * @author sidler@mulchprod.de
 * @since 3.4.1
 */
class RequestDispatcher
{

    private $arrTimestampStart;

    /**
     * @var ResponseObject
     */
    private $objResponse = null;

    /**
     * @var \Kajona\System\System\ObjectBuilder
     */
    private $objBuilder;

    /**
     * @var Session
     */
    private $objSession;

    /**
     * Standard constructor
     *
     * @param ResponseObject $objResponse
     *
     * @return RequestDispatcher
     */
    public function __construct(ResponseObject $objResponse, \Kajona\System\System\ObjectBuilder $objBuilder)
    {
        $this->arrTimestampStart = gettimeofday();
        $this->objSession = Carrier::getInstance()->getObjSession();
        $this->objResponse = $objResponse;
        $this->objBuilder = $objBuilder;
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
    public function processRequest($bitAdmin, $strModule, $strAction, $strLanguageParam)
    {

        CoreEventdispatcher::getInstance()->notifyGenericListeners(SystemEventidentifier::EVENT_SYSTEM_REQUEST_STARTPROCESSING, array($bitAdmin, $strModule, $strAction, $strLanguageParam));

        if ($bitAdmin) {
            $strReturn = $this->processAdminRequest($strModule, $strAction, $strLanguageParam);
            $strReturn = $this->callScriptlets($strReturn, ScriptletInterface::BIT_CONTEXT_ADMIN);
        }
        else {
            $strReturn = $this->processPortalRequest($strModule, $strAction, $strLanguageParam);
            $strReturn = $this->callScriptlets($strReturn, ScriptletInterface::BIT_CONTEXT_PORTAL_PAGE);
        }


        $strReturn = $this->cleanupOutput($strReturn);
        $strReturn = $this->getDebugInfo($strReturn);
        $this->sendConditionalGetHeaders($strReturn);

        $this->objResponse->setStrContent($strReturn);

        CoreEventdispatcher::getInstance()->notifyGenericListeners(SystemEventidentifier::EVENT_SYSTEM_REQUEST_ENDPROCESSING, array($bitAdmin, $strModule, $strAction, $strLanguageParam));

        $this->objSession->sessionClose();
    }

    /**
     * Processes an admin-request
     *
     * @param string $strModule
     * @param string $strAction
     * @param string $strLanguageParam
     *
     * @throws Exception
     * @return string
     */
    private function processAdminRequest($strModule, $strAction, $strLanguageParam)
    {
        $strReturn = "";
        $bitLogin = false;

        //validate https status
        if (SystemSetting::getConfigValue("_admin_only_https_") == "true") {
            //check which headers to compare
            $strHeaderName = Carrier::getInstance()->getObjConfig()->getConfig("https_header");
            $strHeaderValue = strtolower(Carrier::getInstance()->getObjConfig()->getConfig("https_header_value"));

            //header itself given?
            if (!issetServer($strHeaderName)) {
                //reload to https
                if (_xmlLoader_ === true) {
                    ResponseObject::getInstance()->setStrRedirectUrl(uniStrReplace("http:", "https:", _xmlpath_)."?".getServer("QUERY_STRING"));
                }
                else {
                    ResponseObject::getInstance()->setStrRedirectUrl(uniStrReplace("http:", "https:", _indexpath_)."?".getServer("QUERY_STRING"));
                }

                ResponseObject::getInstance()->sendHeaders();
                die("Reloading using https...");
            }
            //value of header correct?
            elseif ($strHeaderValue != "" && $strHeaderValue != strtolower(getServer($strHeaderName))) {
                //reload to https
                if (_xmlLoader_ === true) {
                    ResponseObject::getInstance()->setStrRedirectUrl(uniStrReplace("http:", "https:", _xmlpath_)."?".getServer("QUERY_STRING"));
                }
                else {
                    ResponseObject::getInstance()->setStrRedirectUrl(uniStrReplace("http:", "https:", _indexpath_)."?".getServer("QUERY_STRING"));
                }

                ResponseObject::getInstance()->sendHeaders();
                die("Reloading using https...");
            }
        }

        //process language-param
        $objLanguage = new LanguagesLanguage();
        $objLanguage->setStrAdminLanguageToWorkOn($strLanguageParam);

        //set the current backend skin. right here to do it only once.
        AdminskinHelper::defineSkinWebpath();

        //validate login-status / process login-request
        if ($strModule != "login" && $this->objSession->isLoggedin()) {
            if ($this->objSession->isAdmin()) {
                //try to load the module
                $objModuleRequested = SystemModule::getModuleByName($strModule);
                if ($objModuleRequested != null) {

                    //see if there is data from a previous, failed request
                    if (Carrier::getInstance()->getObjSession()->getSession(LoginAdmin::SESSION_LOAD_FROM_PARAMS) === "true") {
                        foreach (Carrier::getInstance()->getObjSession()->getSession(LoginAdmin::SESSION_PARAMS) as $strOneKey => $strOneVal) {
                            Carrier::getInstance()->setParam($strOneKey, $strOneVal);
                        }

                        Carrier::getInstance()->getObjSession()->sessionUnset(LoginAdmin::SESSION_LOAD_FROM_PARAMS);
                        Carrier::getInstance()->getObjSession()->sessionUnset(LoginAdmin::SESSION_PARAMS);
                    }


                    if (_xmlLoader_) {
                        if ($objModuleRequested->getStrXmlNameAdmin() != "") {
                            $objConcreteModule = $objModuleRequested->getAdminInstanceOfConcreteModule("", true);
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
                        $objHistory = new History();
                        //Writing to the history
                        if (Carrier::getInstance()->getParam("folderview") == "") {
                            $objHistory->setAdminHistory();
                        }

                        $objConcreteModule = $objModuleRequested->getAdminInstanceOfConcreteModule();

                        if (Carrier::getInstance()->getParam("blockAction") != "1") {
                            $objConcreteModule->action();
                            $strReturn = $objConcreteModule->getModuleOutput();
                        }

                        //React, if admin was opened by the portaleditor
                        if (Carrier::getInstance()->getParam("peClose") == "1") {

                            if (getGet("peRefreshPage") != "") {
                                $strReturn = "<html><head></head><body onload=\"parent.location = '".urldecode(getGet("peRefreshPage"))."';\"></body></html>";
                            }
                            else {
                                $strReturn = "<html><head></head><body onload=\"parent.location.reload();\"></body></html>";
                            }
                        }

                    }

                }
                else {
                    throw new Exception("Requested module ".$strModule." not existing", Exception::$level_FATALERROR);
                }
            }
            else {
                throw new Exception("Sorry, but you don't have the needed permissions to access the admin-area", Exception::$level_FATALERROR);
            }
        }
        else {
            $bitLogin = true;

            if ($strModule != "login") {
                $strAction = "";
            }
        }

        if ($bitLogin) {
            if (_xmlLoader_) {
                $objLogin = $this->objBuilder->factory("Kajona\\System\\Admin\\LoginAdminXml");
                $strReturn = $objLogin->action($strAction);
            }
            else {

                if (count(Carrier::getInstance()->getObjDB()->getTables()) == 0 && file_exists(_realpath_."/installer.php")) {
                    ResponseObject::getInstance()->setStrRedirectUrl(_webpath_."/installer.php");
                    return "";
                }

                $objLogin = $this->objBuilder->factory("Kajona\\System\\Admin\\LoginAdmin");
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
     * @throws Exception
     * @return string
     */
    private function processPortalRequest($strModule, $strAction, $strLanguageParam)
    {
        $strReturn = "";

        //process language-param
        if (SystemModule::getModuleByName("languages") != null) {
            $objLanguage = new LanguagesLanguage();
            $objLanguage->setStrPortalLanguage($strLanguageParam);
        }


        //Load the portal parts
        $objModule = SystemModule::getModuleByName($strModule);
        if ($objModule != null) {

            if (_xmlLoader_) {
                if ($objModule->getStrXmlNamePortal() != "") {
                    $objModuleRequested = $objModule->getPortalInstanceOfConcreteModule(null, true);
                    $strReturn = $objModuleRequested->action($strAction);
                }
                else {
                    $objModuleRequested = $objModule->getPortalInstanceOfConcreteModule();
                    $strReturn = $objModuleRequested->action($strAction);
                }
            }
            else {
                if ($strModule == "pages") {
                    $strAction = "";
                }

                //fill the history array to track actions
                $objHistory = new History();
                $objHistory->setPortalHistory();

                $objModuleRequested = $objModule->getPortalInstanceOfConcreteModule();
                $strReturn = $objModuleRequested->action($strAction);
            }

        }
        else {

            if (_xmlLoader_ === false) {
                if (count(Carrier::getInstance()->getObjDB()->getTables()) == 0 && file_exists(_realpath_."/installer.php")) {
                    ResponseObject::getInstance()->setStrRedirectUrl(_webpath_."/installer.php");
                    return "";
                }
            }

            throw new Exception("module ".$strModule." not installed!", Exception::$level_FATALERROR);
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
    private function cleanupOutput($strContent)
    {
        $objTemplate = Carrier::getInstance()->getObjTemplate();
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
    private function callScriptlets($strContent, $intContext)
    {
        $objScriptlet = new ScriptletHelper();
        return $objScriptlet->processString($strContent, $intContext);
    }


    /**
     * Sends conditional get headers and tries to match sent ones.
     *
     * @param string $strContent
     *
     * @return void
     */
    private function sendConditionalGetHeaders($strContent)
    {

        //check headers, maybe execution could be terminated right here
        //yes, this doesn't save us from generating the page, but the traffic towards the client can be reduced
        if (checkConditionalGetHeaders(md5($_SERVER["REQUEST_URI"].$this->objSession->getSessionId().$strContent))) {
            ResponseObject::getInstance()->sendHeaders();
            flush();
            die();
        }

        //send headers if not an ie
        if (strpos(getServer("HTTP_USER_AGENT"), "IE") === false) {
            setConditionalGetHeaders(md5($_SERVER["REQUEST_URI"].$this->objSession->getSessionId().$strContent));
        }
    }


    /**
     * Generates debugging-infos, but only in non-xml mode
     *
     * @param string $strReturn
     *
     * @return string
     */
    private function getDebugInfo($strReturn)
    {
        $strDebug = "";
        if (_timedebug_ || _dbnumber_ || _templatenr_ || _memory_) {

            //Maybe we need the time used to generate this page
            if (_timedebug_ === true) {
                $arrTimestampEnde = gettimeofday();
                $intTimeUsed = (($arrTimestampEnde['sec'] * 1000000 + $arrTimestampEnde['usec'])
                        - ($this->arrTimestampStart['sec'] * 1000000 + $this->arrTimestampStart['usec'])) / 1000000;

                $strDebug .= "<b>PHP-Time:</b> ".number_format($intTimeUsed, 6)." sec ";
            }

            //Hows about the queries?
            if (_dbnumber_ === true) {
                $strDebug .= "<b>Queries db/cachesize/cached/fired:</b> ".Carrier::getInstance()->getObjDB()->getNumber()."/".
                    Carrier::getInstance()->getObjDB()->getCacheSize()."/".
                    Carrier::getInstance()->getObjDB()->getNumberCache()."/".
                    (Carrier::getInstance()->getObjDB()->getNumber() - Carrier::getInstance()->getObjDB()->getNumberCache())." ";
            }

            //anything to say about the templates?
            if (_templatenr_ === true) {
                $strDebug .= "<b>Templates cached:</b> ".Carrier::getInstance()->getObjTemplate()->getNumberCacheSize()." ";
            }

            //memory
            if (_memory_ === true) {
                $strDebug .= "<b>Memory/Max Memory:</b> ".bytesToString(memory_get_usage())."/".bytesToString(memory_get_peak_usage())." ";
                $strDebug .= "<b>Classes Loaded:</b> ".Classloader::getInstance()->getIntNumberOfClassesLoaded()." ";
            }

            //and check the cache-stats
            if (_cache_ === true) {
                $strDebug .= "<b>Cache requests/hits/saves/cachesize:</b> ".
                    Cache::getIntRequests()."/".Cache::getIntHits()."/".Cache::getIntSaves()."/".Cache::getIntCachesize()." ";
            }

            if (_xmlLoader_ === true) {
                ResponseObject::getInstance()->addHeader("Kajona Debug: ".$strDebug);
            }
            else {
                $strDebug = "<pre style='z-index: 2000000; position: fixed; background-color: white; width: 100%; top: 0; font-size: 10px; padding: 0; margin: 0;'>Kajona Debug: ".$strDebug."</pre>";

                $intBodyPos = uniStrpos($strReturn, "</body>");
                if ($intBodyPos !== false) {
                    $strReturn = uniSubstr($strReturn, 0, $intBodyPos).$strDebug.uniSubstr($strReturn, $intBodyPos);
                }
                else {
                    $strReturn = $strDebug.$strReturn;
                }
            }

        }

        return $strReturn;
    }

}

