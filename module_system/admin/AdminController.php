<?php
/*"******************************************************************************************************
*   (c) 2004-2006 by MulchProductions, www.mulchprod.de                                                 *
*   (c) 2007-2016 by Kajona, www.kajona.de                                                              *
*       Published under the GNU LGPL v2.1, see /system/licence_lgpl.txt                                 *
*-------------------------------------------------------------------------------------------------------*
*	$Id$	                                            *
********************************************************************************************************/

namespace Kajona\System\Admin;

use Kajona\System\System\AbstractController;
use Kajona\System\System\AdminskinHelper;
use Kajona\System\System\Carrier;
use Kajona\System\System\Classloader;
use Kajona\System\System\Exception;
use Kajona\System\System\History;
use Kajona\System\System\HttpResponsetypes;
use Kajona\System\System\HttpStatuscodes;
use Kajona\System\System\LanguagesLanguage;
use Kajona\System\System\Link;
use Kajona\System\System\Objectfactory;
use Kajona\System\System\Reflection;
use Kajona\System\System\RequestEntrypointEnum;
use Kajona\System\System\Resourceloader;
use Kajona\System\System\ResponseObject;
use Kajona\System\System\Rights;
use Kajona\System\System\StringUtil;
use Kajona\System\System\SystemAspect;
use Kajona\System\System\SystemModule;
use Kajona\System\System\SystemSetting;
use Kajona\System\Xml;
use ReflectionClass;

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
 * @see AdminController::action()
 */
abstract class AdminController extends AbstractController
{

    /**
     * String containing the current module to be used to load texts
     *
     * @var array
     */
    private $arrOutput;

    /**
     * @inject system_admintoolkit
     * @var ToolkitAdmin
     */
    protected $objToolkit;

    /**
     * @inject system_object_builder
     * @var \Kajona\System\System\ObjectBuilder
     */
    protected $objBuilder;

    /**
     * @inject system_resource_loader
     * @var Resourceloader
     */
    protected $objResourceLoader;

    /**
     * @inject system_class_loader
     * @var Classloader
     */
    protected $objClassLoader;



    /**
     * Constructor
     *
     * @param string $strSystemid
     */
    public function __construct($strSystemid = "")
    {
        parent::__construct($strSystemid);

        // default-template: main.tpl
        if ($this->getArrModule("template") == "") {
            $this->setArrModuleEntry("template", "/main.tpl");
        }

        if ($this->getParam("folderview") != "") {
            $this->setArrModuleEntry("template", "/folderview.tpl");
        }

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
    public function getModuleData($strName, $bitCache = true)
    {
        return SystemModule::getPlainModuleData($strName, $bitCache);

    }

    /**
     * Returns the SystemID of a installed module
     *
     * @param string $strModule
     *
     * @return string "" in case of an error
     * @deprecated
     */
    public function getModuleSystemid($strModule)
    {
        $objModule = SystemModule::getModuleByName($strModule);
        if ($objModule != null) {
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
    public function getModuleDescription()
    {
        $strDesc = $this->getLang("module_description");
        if ($strDesc != "!module_description!") {
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
     *
     * @deprecated use History::getAdminHistory() instead
     * @see History::getAdminHistory()
     * @return string
     */
    protected function getHistory($intPosition = 0)
    {
        $objHistory = new History();
        return $objHistory->getAdminHistory($intPosition);
    }


    // --- OutputMethods ------------------------------------------------------------------------------------

    /**
     * Basic controller method invoking all further methods in order to generate an admin view.
     * Takes care of generating the navigation, title, common JS variables, loading quickhelp texts,...
     *
     * @throws Exception
     * @return string
     * @final
     * @todo could be moved to a general admin-skin helper
     */
    final public function getModuleOutput()
    {
        //skip rendering everything if we just want to redirect...
        if ($this->strOutput == "" && ResponseObject::getInstance()->getStrRedirectUrl() != "") {
            return "";
        }

        if (ResponseObject::getInstance()->getObjEntrypoint()->equals(RequestEntrypointEnum::XML())) {
            //fetch addtional content
            $strReturn = $this->strOutput;
            if ($this->getParam("contentFill") == "1") {
                $strReturn .= $this->objToolkit->getPathNavigation($this->getArrOutputNaviEntries());
            }
            return $strReturn;
        }


        $this->validateAndUpdateCurrentAspect();

        //Calling the content-setter, including a default dialog
        $this->arrOutput["content"] = $this->strOutput;
        if ($this->getArrModule("template") != "/folderview.tpl") {
            $this->arrOutput["path_home"] = AdminHelper::getAdminPathNaviHome();
            $this->arrOutput["moduleSitemap"] = $this->objToolkit->getAdminSitemap($this->getArrModule("modul"));
            $this->arrOutput["moduletitle"] = $this->getOutputModuleTitle();
            $this->arrOutput["actionTitle"] = $this->getOutputActionTitle();
            if (SystemAspect::getActiveObjectCount() > 1) {
                $this->arrOutput["aspectChooser"] = $this->objToolkit->getAspectChooser($this->getArrModule("modul"), $this->getAction(), $this->getSystemid());
            }
            $this->arrOutput["login"] = $this->getOutputLogin();
            $this->arrOutput["quickhelp"] = $this->getQuickHelp();
        }
        $objAdminHelper = new AdminHelper();
        $this->arrOutput["languageswitch"] = (SystemModule::getModuleByName("languages") != null ? SystemModule::getModuleByName("languages")->getAdminInstanceOfConcreteModule()->getLanguageSwitch() : "");
        $this->arrOutput["module_id"] = $this->getArrModule("moduleId");
        $this->arrOutput["webpathTitle"] = urldecode(str_replace(array("http://", "https://"), array("", ""), _webpath_));
        $this->arrOutput["head"] = "<script type=\"text/javascript\">KAJONA_DEBUG = ".$this->objConfig->getDebug("debuglevel")."; KAJONA_WEBPATH = '"._webpath_."'; KAJONA_BROWSER_CACHEBUSTER = ".SystemSetting::getConfigValue("_system_browser_cachebuster_")."; KAJONA_LANGUAGE = '".Carrier::getInstance()->getObjSession()->getAdminLanguage()."';KAJONA_PHARMAP = ".json_encode(array_values(Classloader::getInstance()->getArrPharModules()))."; var require = {$objAdminHelper->generateRequireJsConfig()};</script>";
        $this->arrOutput["requirejs_conf"] = $objAdminHelper->generateRequireJsConfig();

        //see if there are any hooks to be called
        $this->onRenderOutput($this->arrOutput);

        //Loading the desired Template
        //if requested the pe, load different template
        $strTemplate = AdminskinHelper::getPathForSkin($this->objSession->getAdminSkin()).$this->getArrModule("template");
        if ($this->getParam("peClose") == 1 || $this->getParam("pe") == 1) {
            $strTemplate = "/folderview.tpl";
        }
        return $this->objTemplate->fillTemplateFile($this->arrOutput, $strTemplate);
    }



    /**
     * Hook-method to modify some parts of the rendered content right before rendered into the template.
     * May also be used to add additional elements to the array rendered into
     * the admin-template.
     *
     * @param array &$arrContent
     *
     * @return void
     */
    protected function onRenderOutput(&$arrContent)
    {

    }

    /**
     * Validates if the requested module is valid for the current aspect.
     * If necessary, the current aspect is updated.
     *
     * @return void
     */
    private function validateAndUpdateCurrentAspect()
    {
        if (ResponseObject::getInstance()->getObjEntrypoint()->equals(RequestEntrypointEnum::XML()) || $this->getArrModule("template") == "/folderview.tpl") {
            return;
        }

        $objModule = $this->getObjModule();
        $strCurrentAspect = SystemAspect::getCurrentAspectId();
        if ($objModule != null && $objModule->getStrAspect() != "") {
            $arrAspects = explode(",", $objModule->getStrAspect());
            if (count($arrAspects) == 1 && $arrAspects[0] != $strCurrentAspect) {
                $objAspect = new SystemAspect($arrAspects[0]);
                if ($objAspect->rightView()) {
                    SystemAspect::setCurrentAspectId($arrAspects[0]);
                }
            }

        }
    }

    /**
     * Tries to generate a quick-help button.
     * Tests for exisiting help texts
     *
     * @return string
     */
    protected function getQuickHelp()
    {
        $strReturn = "";

        $strTextname = $this->objLang->stringToPlaceholder("quickhelp_".$this->getAction());
        $strText = $this->getLang($strTextname);

        if ($strText != "!".$strTextname."!") {
            //Text found, embed the quickhelp into the current skin
            $strReturn .= $this->objToolkit->getQuickhelp($strText);
        }

        return $strReturn;
    }

    /**
     * @return array
     */
    protected function getArrOutputNaviEntries()
    {
        $arrReturn = array(
            Link::getLinkAdmin("dashboard", "", "", $this->getLang("modul_titel", "dashboard")),
            Link::getLinkAdmin($this->getArrModule("modul"), "", "", $this->getOutputModuleTitle())
        );

        //see, if the current action may be mapped
        $strActionName = $this->getObjLang()->stringToPlaceholder("action_".$this->getAction());
        $strAction = $this->getLang($strActionName);
        if ($strAction != "!".$strActionName."!") {
            $arrReturn[] = $strAction;
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
    public function getOutputModuleNavi()
    {
        return array();
    }

    /**
     * Renders the "always present" module permissions entry for each module (takes the currents' user permissions into
     * account).
     * If you don't want this default behaviour, overwrite this method.
     *
     * @return array
     */
    public function getModuleRightNaviEntry()
    {
        $arrLinks = array();
        $arrLinks[] = array("", "");
        $arrLinks[] = array("right", Link::getLinkAdmin("right", "change", "&systemid=".$this->getObjModule()->getStrSystemid(), $this->getLang("commons_module_permissions")));
        return $arrLinks;
    }

    /**
     * Writes the ModuleTitle, overwrite if needed
     *
     * @return string
     */
    protected function getOutputModuleTitle()
    {
        if ($this->getLang("modul_titel") != "!modul_titel!") {
            return $this->getLang("modul_titel");
        }
        else {
            return $this->getArrModule("modul");
        }
    }

    /**
     * Creates the action name to be rendered in the output, in most cases below the pathnavigation-bar
     *
     * @return string
     */
    protected function getOutputActionTitle()
    {
        return $this->getOutputModuleTitle();
    }

    /**
     * Writes the SessionInfo, overwrite if needed
     *
     * @return string
     */
    protected function getOutputLogin()
    {
        $objLogin = $this->objBuilder->factory("Kajona\\System\\Admin\\LoginAdmin");
        return $objLogin->getLoginStatus();
    }

    /**
     * Use this method to reload a specific url.
     * <b>Use ONLY this method and DO NOT use header("Location: ...");</b>
     *
     * @param string $strUrlToLoad
     *
     * @return void
     */
    public function adminReload($strUrlToLoad)
    {
        //filling constants
        $strUrlToLoad = str_replace("_webpath_", _webpath_, $strUrlToLoad);
        $strUrlToLoad = str_replace("_indexpath_", _indexpath_, $strUrlToLoad);
        //No redirect, if close-Command for admin-area should be sent
        if ($this->getParam("peClose") == "") {
            ResponseObject::getInstance()->setStrRedirectUrl($strUrlToLoad);
        }
    }

    /**
     * Loads the language to edit content
     *
     * @return string
     * @deprecated use LanguagesLanguage directly
     */
    public function getLanguageToWorkOn()
    {
        $objLanguage = new LanguagesLanguage();
        return $objLanguage->getAdminLanguage();
    }

}

