<?php
/*"******************************************************************************************************
*   (c) 2004-2006 by MulchProductions, www.mulchprod.de                                                 *
*   (c) 2007-2014 by Kajona, www.kajona.de                                                              *
*       Published under the GNU LGPL v2.1, see /system/licence_lgpl.txt                                 *
*-------------------------------------------------------------------------------------------------------*
*	$Id$	                                            *
********************************************************************************************************/

/**
 * The Base-Class for all admin-interface classes.
 * Extend this class (or one of its subclasses) to generate a
 * user interface.
 *
 * The action-method() takes care of calling your action-handlers.
 * If the URL-param "action" is set to "list", the controller calls your
 * action method "actionList()". Return the rendered output, everything else is generated
 * automatically.
 *
 * @package module_system
 * @author sidler@mulchprod.de
 * @see class_admin_controller::action()
 */
abstract class class_admin_controller extends class_abstract_controller {

    /**
     * String containing the current module to be used to load texts
     * @var array
     */
    private $arrOutput;

    /**
     * Constructor
     *
     * @param string $strSystemid
     *
     * @internal param array $arrModul
     */
    public function __construct($strSystemid = "") {

        parent::__construct($strSystemid);

        //default-template: main.tpl
        if($this->getArrModule("template") == "") {
            $this->setArrModuleEntry("template", "/main.tpl");
        }

        if($this->getParam("folderview") != "") {
            $this->setArrModuleEntry("template", "/folderview.tpl");
        }

        $this->objToolkit = class_carrier::getInstance()->getObjToolkit("admin");

        //set the correct language to the text-object
        $this->getObjLang()->setStrTextLanguage($this->objSession->getAdminLanguage(true));
    }

    /**
     * Returns the data for a registered module
     * FIXME: validate if still required
     *
     * @param string $strName
     * @param bool $bitCache
     *
     * @return mixed
     * @deprecated
     */
    public function getModuleData($strName, $bitCache = true) {
        return class_module_system_module::getPlainModuleData($strName, $bitCache);

    }

    /**
     * Returns the SystemID of a installed module
     *
     * @param string $strModule
     *
     * @return string "" in case of an error
     * @deprecated
     */
    public function getModuleSystemid($strModule) {
        $objModule = class_module_system_module::getModuleByName($strModule);
        if($objModule != null) {
            return $objModule->getSystemid();
        }
        else {
            return "";
        }
    }

    /**
     * Creates a text-based description of the current module.
     * Therefore the text-entry module_description should be available.
     *
     * @return string
     * @since 3.2.1
     */
    public function getModuleDescription() {
        $strDesc = $this->getLang("module_description");
        if($strDesc != "!module_description!") {
            return $strDesc;
        }
        else {
            return "";
        }
    }

    // --- HistoryMethods -----------------------------------------------------------------------------------

    /**
     * Returns the URL at the given position (from HistoryArray)
     *
     * @param int $intPosition
     * @deprecated use class_history::getAdminHistory() instead
     * @see class_history::getAdminHistory()
     * @return string
     */
    protected function getHistory($intPosition = 0) {
        $objHistory = new class_history();
        return $objHistory->getAdminHistory($intPosition);
    }


    // --- OutputMethods ------------------------------------------------------------------------------------

    /**
     * Basic controller method invoking all further methods in order to generate an admin view.
     * Takes care of generating the navigation, title, common JS variables, loading quickhelp texts,...
     *
     * @throws class_exception
     * @return string
     * @final
     * @todo could be moved to a general admin-skin helper
     */
    public final function getModuleOutput() {

        //skip rendering everything if we just want to redirect...
        if($this->strOutput == "" && class_response_object::getInstance()->getStrRedirectUrl() != "") {
            return "";
        }


        $this->validateAndUpdateCurrentAspect();

        //Calling the content-setter, including a default dialog
        $this->arrOutput["content"] = $this->strOutput;
        if($this->getArrModule("template") != "/folderview.tpl") {
            $this->arrOutput["path"] = class_admin_helper::getAdminPathNavi($this->getArrOutputNaviEntries(), $this->getArrModule("modul"));
            $this->arrOutput["moduleSitemap"] = $this->objToolkit->getAdminSitemap($this->getArrModule("modul"));
            $this->arrOutput["moduletitle"] = $this->getOutputModuleTitle();
            $this->arrOutput["actionTitle"] = $this->getOutputActionTitle();
            if(class_module_system_aspect::getActiveObjectCount() > 1) {
                $this->arrOutput["aspectChooser"] = $this->objToolkit->getAspectChooser($this->getArrModule("modul"), $this->getAction(), $this->getSystemid());
            }
            $this->arrOutput["login"] = $this->getOutputLogin();
            $this->arrOutput["quickhelp"] = $this->getQuickHelp();
        }
        $this->arrOutput["languageswitch"] = (class_module_system_module::getModuleByName("languages") != null ? class_module_system_module::getModuleByName("languages")->getAdminInstanceOfConcreteModule()->getLanguageSwitch() : "");
        $this->arrOutput["module_id"] = $this->getArrModule("moduleId");
        $this->arrOutput["webpathTitle"] = urldecode(str_replace(array("http://", "https://"), array("", ""), _webpath_));
        $this->arrOutput["head"] = "<script type=\"text/javascript\">KAJONA_DEBUG = ".$this->objConfig->getDebug("debuglevel")."; KAJONA_WEBPATH = '"._webpath_."'; KAJONA_BROWSER_CACHEBUSTER = "._system_browser_cachebuster_.";</script>";

        //see if there are any hooks to be called
        $this->onRenderOutput($this->arrOutput);

        //Loading the desired Template
        //if requested the pe, load different template
        $strTemplateID = "";
        if($this->getParam("peClose") == 1 || $this->getParam("pe") == 1) {
            //add suffix
            try {
                $strTemplate = "/folderview.tpl";
                $strTemplateID = $this->objTemplate->readTemplate($strTemplate, "", false, true);
            }
            catch(class_exception $objException) {
                //An error occurred. In most cases, this is because the user ist not logged in, so the login-template was requested.
                if($this->getArrModule("template") == "/login.tpl") {
                    throw new class_exception("You have to be logged in to use the portal editor!!!", class_exception::$level_ERROR);
                }
            }
        }
        else {
            $strTemplateID = $this->objTemplate->readTemplate(class_adminskin_helper::getPathForSkin($this->objSession->getAdminSkin()) . $this->getArrModule("template"), "", true);
        }
        return $this->objTemplate->fillTemplate($this->arrOutput, $strTemplateID);
    }

    /**
     * Hook-method to modify some parts of the rendered content right before rendered into the template.
     * May also be used to add additional elements to the array rendered into
     * the admin-template.
     *
     * @param array &$arrContent
     * @see class_admin::getModuleOutput
     * @return void
     */
    protected function onRenderOutput(&$arrContent) {

    }

    /**
     * Validates if the requested module is valid for the current aspect.
     * If necessary, the current aspect is updated.
     *
     * @return void
     */
    private function validateAndUpdateCurrentAspect() {
        if(_xmlLoader_ === true || $this->getArrModule("template") == "/folderview.tpl") {
            return;
        }

        $objModule = $this->getObjModule();
        $strCurrentAspect = class_module_system_aspect::getCurrentAspectId();
        if($objModule != null && $objModule->getStrAspect() != "") {
            $arrAspects = explode(",", $objModule->getStrAspect());
            if(count($arrAspects) == 1 && $arrAspects[0] != $strCurrentAspect) {
                $objAspect = new class_module_system_aspect($arrAspects[0]);
                if($objAspect->rightView())
                    class_module_system_aspect::setCurrentAspectId($arrAspects[0]);
            }

        }
    }

    /**
     * Tries to generate a quick-help button.
     * Tests for exisiting help texts
     *
     * @return string
     */
    protected function getQuickHelp() {
        $strReturn = "";
        $strText = "";
        $strTextname = "";

        //Text for the current action available?
        //different loading when editing page-elements
        if($this->getParam("module") == "pages_content" && ($this->getParam("action") == "edit" || $this->getParam("action") == "new")) {
            $objElement = null;
            if($this->getParam("action") == "edit") {
                $objElement = new class_module_pages_pageelement($this->getSystemid());
            }
            else if($this->getParam("action") == "new") {
                $strPlaceholderElement = $this->getParam("element");
                $objElement = class_module_pages_element::getElement($strPlaceholderElement);
            }
            //Build the class-name
            $strElementClass = str_replace(".php", "", $objElement->getStrClassAdmin());
            //and finally create the object
            if($strElementClass != "") {
                /** @var class_element_admin $objElement */
                $objElement = new $strElementClass();
                $strTextname = $this->getObjLang()->stringToPlaceholder("quickhelp_" . $objElement->getArrModule("name"));
                $strText = class_carrier::getInstance()->getObjLang()->getLang($strTextname, $objElement->getArrModule("modul"));
            }
        }
        else {
            $strTextname = $this->getObjLang()->stringToPlaceholder("quickhelp_" . $this->getAction());
            $strText = $this->getLang($strTextname);
        }

        if($strText != "!" . $strTextname . "!") {
            //Text found, embed the quickhelp into the current skin
            $strReturn .= $this->objToolkit->getQuickhelp($strText);
        }

        return $strReturn;
    }

    /**
     * @return array
     */
    protected function getArrOutputNaviEntries() {
        $arrReturn = array(
            class_link::getLinkAdmin("dashboard", "", "", $this->getLang("modul_titel", "dashboard")),
            class_link::getLinkAdmin($this->getArrModule("modul"), "", "", $this->getOutputModuleTitle())
        );

        //see, if the current action may be mapped
        $strActionName = $this->getObjLang()->stringToPlaceholder("action_".$this->getAction());
        $strAction = $this->getLang($strActionName);
        if($strAction != "!" . $strActionName . "!") {
            $arrReturn[] = class_link::getLinkAdmin($this->getArrModule("modul"), $this->getAction(), "&systemid=" . $this->getSystemid(), $strAction);
        }

        return $arrReturn;
    }

    /**
     * Writes the ModuleNavi, overwrite if needed
     * Use two-dim arary:
     * array[
     *     array["right", "link"],
     *     array["right", "link"]
     * ]
     *
     * @return array array containing all links
     */
    public function getOutputModuleNavi() {
        return array();
    }

    /**
     * Renders the "always present" module permissions entry for each module (takes the currents' user permissions into
     * account).
     * If you don't want this default behaviour, overwrite this method.
     * @return array
     */
    public function getModuleRightNaviEntry() {
        $arrLinks = array();
        $arrLinks[] = array("", "");
        $arrLinks[] = array("right", class_link::getLinkAdmin("right", "change", "&changemodule=".$this->getArrModule("modul"), $this->getLang("commons_module_permissions")));
        return $arrLinks;
    }

    /**
     * Writes the ModuleTitle, overwrite if needed
     *
     * @return string
     */
    protected function getOutputModuleTitle() {
        if($this->getLang("modul_titel") != "!modul_titel!") {
            return $this->getLang("modul_titel");
        }
        else {
            return $this->getArrModule("modul");
        }
    }

    /**
     * Creates the action name to be rendered in the output, in most cases below the pathnavigation-bar
     * @return string
     */
    protected function getOutputActionTitle() {
        return $this->getOutputModuleTitle();
    }

    /**
     * Writes the SessionInfo, overwrite if needed
     *
     * @return string
     */
    protected function getOutputLogin() {
        $objLogin = new class_module_login_admin();
        return $objLogin->getLoginStatus();
    }

    /**
     * This method triggers the internal processing.
     * It may be overridden if required, e.g. to implement your own action-handling.
     * By default, the method to be called is set up out of the action-param passed.
     * Example: The action requested is names "newPage". Therefore, the framework tries to
     * call actionNewPage(). If no method matching the schema is found, an exception is being thrown.
     * The actions' output is saved back to self::strOutput and, is returned in addition.
     * Returning the content is only implemented to remain backwards compatible with older implementations.
     * Since Kajona 4.0, the check on declarative permissions via annotations is supported.
     * Therefore the list of permissions, named after the "permissions" annotation are validated against
     * the module currently loaded.
     *
     * @param string $strAction
     *
     * @see class_rights::validatePermissionString
     *
     * @throws class_exception
     * @return string
     * @since 3.4
     */
    public function action($strAction = "") {

        if($strAction != "") {
            $this->setAction($strAction);
        }

        $strAction = $this->getAction();

        //search for the matching method - build method name
        $strMethodName = "action" . uniStrtoupper($strAction[0]) . uniSubstr($strAction, 1);

        if(method_exists($this, $strMethodName)) {

            //validate the permissions required to call this method, the xml-part is validated afterwards
            $objAnnotations = new class_reflection(get_class($this));

            $strPermissions = $objAnnotations->getMethodAnnotationValue($strMethodName, "@permissions");
            if($strPermissions !== false) {

                if(validateSystemid($this->getSystemid()) && class_objectfactory::getInstance()->getObject($this->getSystemid()) != null) {
                    $objObjectToCheck = class_objectfactory::getInstance()->getObject($this->getSystemid());
                }
                else {
                    $objObjectToCheck = $this->getObjModule();
                }

                if(!class_carrier::getInstance()->getObjRights()->validatePermissionString($strPermissions, $objObjectToCheck)) {
                    class_response_object::getInstance()->setStrStatusCode(class_http_statuscodes::SC_UNAUTHORIZED);
                    $this->strOutput = $this->objToolkit->warningBox($this->getLang("commons_error_permissions"));
                    $objException = new class_exception("you are not authorized/authenticated to call this action", class_exception::$level_ERROR);
                    $objException->setIntDebuglevel(0);
                    $objException->processException();
                    return $this->strOutput;
                }
            }


            //validate the loading channel - xml or regular
            if(_xmlLoader_ === true) {
                //check it the method is allowed for xml-requests

                if(!$objAnnotations->hasMethodAnnotation($strMethodName, "@xml") && substr(get_class($this), -3) != "xml") {
                    throw new class_exception("called method " . $strMethodName . " not allowed for xml-requests", class_exception::$level_FATALERROR);
                }

                if($this->getArrModule("modul") != $this->getParam("module") && ($this->getParam("module") != "messaging")) {
                    class_response_object::getInstance()->setStrStatusCode(class_http_statuscodes::SC_UNAUTHORIZED);
                    throw new class_exception("you are not authorized/authenticated to call this action", class_exception::$level_FATALERROR);
                }
            }

            $this->strOutput = $this->$strMethodName();
        }
        else {
            $objReflection = new ReflectionClass($this);
            //if the pe was requested and the current module is a login-module, there are insufficient permissions given
            if($this->getArrModule("template") == "/login.tpl" && $this->getParam("pe") != "") {
                throw new class_exception("You have to be logged in to use the portal editor!!!", class_exception::$level_ERROR);
            }

            if(get_class($this) == "class_module_login_admin_xml") {
                class_response_object::getInstance()->setStrStatusCode(class_http_statuscodes::SC_UNAUTHORIZED);
                throw new class_exception("you are not authorized/authenticated to call this action", class_exception::$level_FATALERROR);
            }

            $this->strOutput = $this->objToolkit->warningBox("called method " . $strMethodName . " not existing for class " . $objReflection->getName());
            $objException = new class_exception("called method " . $strMethodName . " not existing for class " . $objReflection->getName(), class_exception::$level_ERROR);
            $objException->setIntDebuglevel(0);
            $objException->processException();
        }

        return $this->strOutput;
    }


    /**
     * Use this method to reload a specific url.
     * <b>Use ONLY this method and DO NOT use header("Location: ...");</b>
     *
     * @param string $strUrlToLoad
     *
     * @return void
     */
    public function adminReload($strUrlToLoad) {
        //filling constants
        $strUrlToLoad = str_replace("_webpath_", _webpath_, $strUrlToLoad);
        $strUrlToLoad = str_replace("_indexpath_", _indexpath_, $strUrlToLoad);
        //No redirect, if close-Command for admin-area should be sent
        if($this->getParam("peClose") == "") {
            class_response_object::getInstance()->setStrRedirectUrl($strUrlToLoad);
        }
    }

    /**
     * Loads the language to edit content
     *
     * @return string
     */
    public function getLanguageToWorkOn() {
        $objSystemCommon = new class_module_system_common();
        return $objSystemCommon->getStrAdminLanguageToWorkOn();
    }


}

