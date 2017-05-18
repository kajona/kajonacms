<?php
/*"******************************************************************************************************
*   (c) 2004-2006 by MulchProductions, www.mulchprod.de                                                 *
*   (c) 2007-2016 by Kajona, www.kajona.de                                                              *
*       Published under the GNU LGPL v2.1, see /system/licence_lgpl.txt                                 *
********************************************************************************************************/

namespace Kajona\V4skin\Admin;

use Kajona\System\Admin\AdminController;
use Kajona\System\Admin\AdminEvensimpler;
use Kajona\System\Admin\AdminHelper;
use Kajona\System\Admin\AdminInterface;
use Kajona\System\System\AdminskinHelper;
use Kajona\System\System\Carrier;
use Kajona\System\System\Classloader;
use Kajona\System\System\SystemAspect;
use Kajona\System\System\SystemModule;
use Kajona\System\System\SystemSetting;

/**
 * Backend Controller to handle various, general actions / callbacks
 *
 * @author sidler@mulchprod.de
 *
 * @module v4skin
 * @moduleId _v4skin_module_id_
 */
class SkinAdminController extends AdminEvensimpler implements AdminInterface
{

    public function actionGetPathNavigation(AdminController $objAdminModule)
    {
        return Carrier::getInstance()->getObjToolkit("admin")->getPathNavigation($objAdminModule->getArrOutputNaviEntries());
    }

    public function actionGetQuickHelp(AdminController $objAdminModule)
    {
        return $objAdminModule->getQuickHelp();
    }


    public function actionGenerateMainTemplate($strContent)
    {
        $arrTemplate = ["content" => $strContent];


        //move to separate getters
        $arrTemplate["path_home"] = AdminHelper::getAdminPathNaviHome();
//        $arrTemplate["moduleSitemap"] = $this->objToolkit->getAdminSitemap();

        if (SystemAspect::getActiveObjectCount() > 1) {
            $arrTemplate["aspectChooser"] = $this->objToolkit->getAspectChooser($this->getArrModule("modul"), $this->getAction(), $this->getSystemid());
        }

        $arrTemplate["login"] = $this->getOutputLogin();
        $arrTemplate["quickhelp"] = $this->getQuickHelp();

        $objAdminHelper = new AdminHelper();
        $arrTemplate["languageswitch"] = (SystemModule::getModuleByName("languages") != null ? SystemModule::getModuleByName("languages")->getAdminInstanceOfConcreteModule()->getLanguageSwitch() : "");
        $arrTemplate["webpathTitle"] = urldecode(str_replace(["http://", "https://"], ["", ""], _webpath_));
        $arrTemplate["head"] = "<script type=\"text/javascript\">KAJONA_DEBUG = ".$this->objConfig->getDebug("debuglevel")."; KAJONA_WEBPATH = '"._webpath_."'; KAJONA_BROWSER_CACHEBUSTER = ".SystemSetting::getConfigValue("_system_browser_cachebuster_")."; KAJONA_LANGUAGE = '".Carrier::getInstance()->getObjSession()->getAdminLanguage()."';KAJONA_PHARMAP = ".json_encode(array_values(Classloader::getInstance()->getArrPharModules()))."; var require = {$objAdminHelper->generateRequireJsConfig()};</script>";

        $strTemplate = AdminskinHelper::getPathForSkin($this->objSession->getAdminSkin()).$this->getArrModule("template");
        if ($this->getParam("peClose") == 1 || $this->getParam("pe") == 1) {
            $strTemplate = "/folderview.tpl";
        }
        return $this->objTemplate->fillTemplateFile($arrTemplate, $strTemplate);
    }

    public function actionGenerateFolderviewTemplate($strContent)
    {
        $arrTemplate = ["content" => $strContent];

        $objAdminHelper = new AdminHelper();
        $arrTemplate["webpathTitle"] = urldecode(str_replace(["http://", "https://"], ["", ""], _webpath_));
        $arrTemplate["head"] = "<script type=\"text/javascript\">KAJONA_DEBUG = ".$this->objConfig->getDebug("debuglevel")."; KAJONA_WEBPATH = '"._webpath_."'; KAJONA_BROWSER_CACHEBUSTER = ".SystemSetting::getConfigValue("_system_browser_cachebuster_")."; KAJONA_LANGUAGE = '".Carrier::getInstance()->getObjSession()->getAdminLanguage()."';KAJONA_PHARMAP = ".json_encode(array_values(Classloader::getInstance()->getArrPharModules()))."; var require = {$objAdminHelper->generateRequireJsConfig()};</script>";

        $strTemplate = "/folderview.tpl";
        return $this->objTemplate->fillTemplateFile($arrTemplate, $strTemplate);
    }


    public function actionGenerateLoginTemplate($strContent)
    {
        $arrTemplate = ["content" => $strContent];


        $objAdminHelper = new AdminHelper();
        $arrTemplate["webpathTitle"] = urldecode(str_replace(["http://", "https://"], ["", ""], _webpath_));
        $arrTemplate["head"] = "<script type=\"text/javascript\">KAJONA_DEBUG = ".$this->objConfig->getDebug("debuglevel")."; KAJONA_WEBPATH = '"._webpath_."'; KAJONA_BROWSER_CACHEBUSTER = ".SystemSetting::getConfigValue("_system_browser_cachebuster_")."; KAJONA_LANGUAGE = '".Carrier::getInstance()->getObjSession()->getAdminLanguage()."';KAJONA_PHARMAP = ".json_encode(array_values(Classloader::getInstance()->getArrPharModules()))."; var require = {$objAdminHelper->generateRequireJsConfig()};</script>";

        $strTemplate = "/login.tpl";
        return $this->objTemplate->fillTemplateFile($arrTemplate, $strTemplate);
    }

    /**
     * @responseType html
     */
    protected function actionGetBackendNavi()
    {
        $strAspectId = $this->getParam("aspect") ?: SystemAspect::getCurrentAspectId();
        return $this->objToolkit->getAdminSitemap($strAspectId);
    }

    /**
     * @responseType html
     */
    protected function actionGetLanguageswitch()
    {
        return (SystemModule::getModuleByName("languages") != null ? "<span>".SystemModule::getModuleByName("languages")->getAdminInstanceOfConcreteModule()->getLanguageSwitch()."</span>" : "<span/>");
    }

}
