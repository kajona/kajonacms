<?php
/*"******************************************************************************************************
*   (c) 2004-2006 by MulchProductions, www.mulchprod.de                                                 *
*   (c) 2007-2016 by Kajona, www.kajona.de                                                              *
*       Published under the GNU LGPL v2.1, see /system/licence_lgpl.txt                                 *
*-------------------------------------------------------------------------------------------------------*
*	$Id$                                               *
********************************************************************************************************/

namespace Kajona\System\System;

use Kajona\System\Admin\LoginAdmin;
use Kajona\System\Xml;
use Kajona\V4skin\Admin\SkinAdminController;

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
     */
    public function processRequest($bitAdmin, $strModule, $strAction, $strLanguageParam)
    {

        CoreEventdispatcher::getInstance()->notifyGenericListeners(SystemEventidentifier::EVENT_SYSTEM_REQUEST_STARTPROCESSING, array($bitAdmin, $strModule, $strAction, $strLanguageParam));

        if ($bitAdmin) {
            $strReturn = $this->processAdminRequest($strModule, $strAction, $strLanguageParam);
            $strReturn = $this->callScriptlets($strReturn, ScriptletInterface::BIT_CONTEXT_ADMIN);
        } else {
            $strReturn = $this->processPortalRequest($strModule, $strAction, $strLanguageParam);
            $strReturn = $this->callScriptlets($strReturn, ScriptletInterface::BIT_CONTEXT_PORTAL_PAGE);
        }


        $strReturn = $this->cleanupOutput($strReturn);
        $strReturn = $this->getDebugInfo($strReturn);

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
            $arrHeaderNames = Carrier::getInstance()->getObjConfig()->getConfig("https_header");
            if (!is_array($arrHeaderNames)) {
                $arrHeaderNames = array($arrHeaderNames);
            }
            $strHeaderValue = strtolower(Carrier::getInstance()->getObjConfig()->getConfig("https_header_value"));

            $bitRedirectRequired = true;
            foreach ($arrHeaderNames as $strSingleName) {
                if (issetServer($strSingleName) && $strHeaderValue == strtolower(getServer($strSingleName))) {
                    $bitRedirectRequired = false;
                    break;
                }
            }

            if ($bitRedirectRequired) {
                //reload to https
                ResponseObject::getInstance()->setStrRedirectUrl(
                    StringUtil::replace("http:", "https:", ResponseObject::getInstance()->getObjEntrypoint()->equals(RequestEntrypointEnum::XML()) ? _xmlpath_ : _indexpath_)."?".getServer("QUERY_STRING")
                );
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
                if (empty($strModule) || $objModuleRequested != null) {
                    //see if there is data from a previous, failed request
                    if (Carrier::getInstance()->getObjSession()->getSession(LoginAdmin::SESSION_LOAD_FROM_PARAMS) === "true") {
                        foreach (Carrier::getInstance()->getObjSession()->getSession(LoginAdmin::SESSION_PARAMS) as $strOneKey => $strOneVal) {
                            Carrier::getInstance()->setParam($strOneKey, $strOneVal);
                        }

                        Carrier::getInstance()->getObjSession()->sessionUnset(LoginAdmin::SESSION_LOAD_FROM_PARAMS);
                        Carrier::getInstance()->getObjSession()->sessionUnset(LoginAdmin::SESSION_PARAMS);
                    }


                    //fill the history array to track actions
                    if (ResponseObject::getInstance()->getObjEntrypoint()->equals(RequestEntrypointEnum::INDEX()) && empty(Carrier::getInstance()->getParam("folderview"))) {
                        $objHistory = new History();
                        //Writing to the history
                        $objHistory->setAdminHistory();
                    }


                    $strReturn = "";

                    //try to rewrite some redirect urls internally
                    if (ResponseObject::getInstance()->getObjEntrypoint()->equals(RequestEntrypointEnum::INDEX()) && $_SERVER['REQUEST_METHOD'] != 'POST' && !empty($strModule) && empty(Carrier::getInstance()->getParam("contentFill"))) {

                        $arrParams = Carrier::getAllParams();
                        unset($arrParams["module"]);
                        unset($arrParams["action"]);
                        unset($arrParams["admin"]);

                        $strUrl = Link::getLinkAdminHref($strModule, $strAction, $arrParams, false, true);
                        $strUrl = StringUtil::replace(_webpath_."/index.php?admin=1", "", $strUrl);

                        $strReturn = "<script type='text/javascript'>
                            routie('{$strUrl}');
                        </script>";



                        $objHelper = new SkinAdminController();
                        if (empty(Carrier::getInstance()->getParam("folderview"))) {
                            return "<html><head></head><body><script type='text/javascript'>document.location='".Link::getLinkAdminHref($strModule, $strAction, $arrParams, false, true)."';</script></body></html>";
//                            $strReturn = $objHelper->actionGenerateMainTemplate($strReturn);
                        } else {
                            $strReturn = $objHelper->actionGenerateFolderviewTemplate($strReturn);
                        }
                        return $strReturn;




                        $arrParams = Carrier::getAllParams();
                        unset($arrParams["module"]);
                        unset($arrParams["action"]);
                        unset($arrParams["admin"]);
                        return "<html><head></head><body><script type='text/javascript'>document.location='".Link::getLinkAdminHref($strModule, $strAction, $arrParams, false, true)."';</script></body></html>";

                    }



                    if (Carrier::getInstance()->getParam("blockAction") != "1") {
                        if (!empty($strModule)) {
                            $objConcreteModule = $objModuleRequested->getAdminInstanceOfConcreteModule();
                            try {
                                //process e.g. in case of post requests
                                $strReturn = $objConcreteModule->action();

                                //if we resulted in a redirect, rewrite it to a js based on
                                if (ResponseObject::getInstance()->getStrRedirectUrl() != "") {
                                    $strUrl = ResponseObject::getInstance()->getStrRedirectUrl();
                                    $strUrl = StringUtil::replace(array(_indexpath_, _webpath_, "?"), "", $strUrl);
                                    $strUrl = StringUtil::replace("&amp;", "&", $strUrl);
                                    $arrFragments = explode("&", $strUrl);

                                    $strRedirectModule = "";
                                    $strRedirectAction = "";

                                    foreach ($arrFragments as $intPartKey => $strOnePart) {
                                        if ($strOnePart == "admin=1") {
                                            unset($arrFragments[$intPartKey]);
                                            continue;
                                        }

                                        $arrKeyValue = explode("=", $strOnePart);

                                        if ($arrKeyValue[0] == "module") {
                                            $strRedirectModule = $arrKeyValue[1];
                                            unset($arrFragments[$intPartKey]);
                                            continue;
                                        }

                                        if ($arrKeyValue[0] == "action") {
                                            $strRedirectAction = $arrKeyValue[1];
                                            unset($arrFragments[$intPartKey]);
                                            continue;
                                        }
                                    }

                                    $strRoutieRedirect = Link::getLinkAdminHref($strRedirectModule, $strRedirectAction, implode("&", $arrFragments), true, true);
                                    $strRoutieRedirect = StringUtil::replace(_webpath_."/index.php?admin=1", "", $strRoutieRedirect);
                                    //parse module, action, systemid and generate a hash url

                                    $strReturn = "<script type='text/javascript'>
                                        routie('{$strRoutieRedirect}');
                                    </script>";
                                    ResponseObject::getInstance()->setStrRedirectUrl("");
                                }


                            } catch (ActionNotFoundException $objEx) {
                                $strReturn = $objEx->getMessage();
                            }
                        } else {
//                            $strReturn = "<script type='text/javascript'>routie('/dashboard')</script>";
                        }

                        if (ResponseObject::getInstance()->getObjEntrypoint()->equals(RequestEntrypointEnum::INDEX()) && empty(Carrier::getInstance()->getParam("contentFill"))) {
                            $objHelper = new SkinAdminController();
                            if (empty(Carrier::getInstance()->getParam("folderview"))) {
                                $strReturn = $objHelper->actionGenerateMainTemplate($strReturn);
                            } else {
                                $strReturn = $objHelper->actionGenerateFolderviewTemplate($strReturn);
                            }
                        }

                    }

                    //React, if admin was opened by the portaleditor
                    if (Carrier::getInstance()->getParam("peClose") == "1") {
                        if (getGet("peRefreshPage") != "") {
                            $strReloadUrl = xssSafeString(getGet("peRefreshPage"));
                            $strReturn = "<html><head></head><body><script type='text/javascript'>if(window.opener) { window.opener.location = '".$strReloadUrl."'; window.close(); } else { parent.location = '".$strReloadUrl."'; }</script></body></html>";
                        } else {
                            $strReturn = "<html><head></head><body><script type='text/javascript'>if(window.opener) { window.opener.location.reload(); window.close(); } else { parent.location.reload(); }</script></body></html>";
                        }
                    }

                } else {
                    throw new Exception("Requested module ".$strModule." not existing", Exception::$level_FATALERROR);
                }
            } else {
                throw new Exception("Sorry, but you don't have the needed permissions to access the admin-area", Exception::$level_FATALERROR);
            }
        } else {
            $bitLogin = true;

            if ($strModule != "login") {
                $strAction = "";
            }
        }

        if ($bitLogin) {
            //skip in case of xml requests
            if (ResponseObject::getInstance()->getObjEntrypoint()->equals(RequestEntrypointEnum::XML())) {
                ResponseObject::getInstance()->setStrStatusCode(HttpStatuscodes::SC_UNAUTHORIZED);
                ResponseObject::getInstance()->setStrResponseType(HttpResponsetypes::STR_TYPE_XML);
                Xml::setBitSuppressXmlHeader(true);
                return Exception::renderException(new ActionNotFoundException("you are not authorized/authenticated to call this action", Exception::$level_FATALERROR));
            }

            if (count(Carrier::getInstance()->getObjDB()->getTables()) == 0 && file_exists(_realpath_."installer.php")) {
                ResponseObject::getInstance()->setStrRedirectUrl(_webpath_."/installer.php");
                return "";
            }

            $objHelper = new SkinAdminController();
            $objLogin = $this->objBuilder->factory(LoginAdmin::class);
            $strReturn = $objLogin->action($strAction);
            $strReturn = $objHelper->actionGenerateLoginTemplate($strReturn);

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
            if ($strModule == "pages") {
                $strAction = "";
            }

            //fill the history array to track actions
            if (ResponseObject::getInstance()->getObjEntrypoint()->equals(RequestEntrypointEnum::INDEX())) {
                $objHistory = new History();
                $objHistory->setPortalHistory();
            }

            $objModuleRequested = $objModule->getPortalInstanceOfConcreteModule();

            //catch problems on top level
            try {
                $strReturn = $objModuleRequested->action($strAction);
            } catch (ActionNotFoundException $objException) {
                $strReturn = Exception::renderException($objException);
            }


        } else {
            if (!ResponseObject::getInstance()->getObjEntrypoint()->equals(RequestEntrypointEnum::XML())) {
                if (count(Carrier::getInstance()->getObjDB()->getTables()) == 0 && file_exists(_realpath_."installer.php")) {
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

            //memory
            if (_memory_ === true) {
                $strDebug .= "<b>Memory/Max Memory:</b> ".bytesToString(memory_get_usage())."/".bytesToString(memory_get_peak_usage())." ";
                $strDebug .= "<b>Classes Loaded:</b> ".Classloader::getInstance()->getIntNumberOfClassesLoaded()." ";
            }

            if (ResponseObject::getInstance()->getObjEntrypoint()->equals(RequestEntrypointEnum::XML())) {
                ResponseObject::getInstance()->addHeader("Kajona Debug: ".$strDebug);
            } else {
                $strDebug = "<pre style='z-index: 2000000; position: fixed; background-color: white; width: 100%; top: 0; font-size: 10px; padding: 0; margin: 0;'>Kajona Debug: ".$strDebug."</pre>";

                $intBodyPos = StringUtil::indexOf($strReturn, "</body>");
                if ($intBodyPos !== false) {
                    $strReturn = StringUtil::substring($strReturn, 0, $intBodyPos).$strDebug.StringUtil::substring($strReturn, $intBodyPos);
                } else {
                    $strReturn = $strDebug.$strReturn;
                }
            }

        }

        return $strReturn;
    }

}

