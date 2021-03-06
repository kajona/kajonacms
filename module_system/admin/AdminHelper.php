<?php
/*"******************************************************************************************************
*   (c) 2007-2016 by Kajona, www.kajona.de                                                              *
*       Published under the GNU LGPL v2.1, see /system/licence_lgpl.txt                                 *
*-------------------------------------------------------------------------------------------------------*
*	$Id$	                                            *
********************************************************************************************************/

namespace Kajona\System\Admin;

use Kajona\System\System\BootstrapCache;
use Kajona\System\System\Carrier;
use Kajona\System\System\Classloader;
use Kajona\System\System\Link;
use Kajona\System\System\Resourceloader;
use Kajona\System\System\StringUtil;
use Kajona\System\System\SystemAspect;
use Kajona\System\System\SystemModule;


/**
 * A class holding common helper-methods for the backend.
 * The main purpose is to reduce the code stored at AdminController
 *
 * @package module_system
 * @author  sidler@mulchprod.de
 * @since   4.0
 */
class AdminHelper
{

    /**
     * Adds a menu-button to the second entry of the path-array. The menu renders the list of all modules installed,
     * including a quick-jump link.
     *
     *
     * @param array $arrPathEntries
     * @param string $strSourceModule
     *
     * @static
     * @internal param array $arrModuleActions
     * @return string
     */
    public static function getAdminPathNavi($arrPathEntries, $strSourceModule = "")
    {
        //modify some of the entries
        $arrMenuEntries = array();
        $arrModules = SystemModule::getModulesInNaviAsArray();
        foreach ($arrModules as $arrOneModule) {
            $objModule = SystemModule::getModuleByName($arrOneModule["module_name"]);

            if ($objModule == null || !$objModule->rightView()) {
                continue;
            }

            $arrCurMenuEntry = array(
                "name"    => Carrier::getInstance()->getObjLang()->getLang("modul_titel", $arrOneModule["module_name"]),
                "onclick" => "location.href='".Link::getLinkAdminHref($arrOneModule["module_name"], "", "", false)."'",
                "link"    => "#"
            );

            //fetch the submenu entries
            if ($objModule != null) {
                $arrActionMenuEntries = array();
                $arrModuleActions = self::getModuleActionNaviHelper($objModule);
                foreach ($arrModuleActions as $strOneAction) {
                    if ($strOneAction != "") {
                        $arrLink = splitUpLink($strOneAction);

                        if ($arrLink["name"] != "" && $arrLink["href"] != "") {
                            $arrActionMenuEntries[] = array(
                                "name"    => $arrLink["name"],
                                "onclick" => "location.href='".$arrLink["href"]."'",
                                "link"    => $arrLink["href"]
                            );
                        }

                    }
                    elseif ($strOneAction == "") {
                        $arrActionMenuEntries[] = array(
                            "name" => ""
                        );
                    }
                }
                $arrCurMenuEntry["submenu"] = $arrActionMenuEntries;
            }
            $arrMenuEntries[] = $arrCurMenuEntry;
        }


        $strModuleMenuId = generateSystemid();
        $strModuleSwitcher = "
                    <span class='dropdown moduleSwitch'><a href='#' data-toggle='dropdown' class='moduleSwitchLink' role='button'><i class='fa fa-home'></i></a>
                    ".Carrier::getInstance()->getObjToolkit("admin")->registerMenu($strModuleMenuId, $arrMenuEntries)."</span>";

        array_unshift($arrPathEntries, $strModuleSwitcher);
        return Carrier::getInstance()->getObjToolkit("admin")->getPathNavigation($arrPathEntries);

    }


    /**
     * Fetches the list of actions for a single module, saved to the session for performance reasons
     *
     * @param SystemModule $objModule
     *
     * @static
     *
     * @return array
     */
    public static function getModuleActionNaviHelper(SystemModule $objModule)
    {
        if (Carrier::getInstance()->getObjSession()->isLoggedin()) {

            $strKey = __CLASS__."adminNaviEntries".$objModule->getSystemid().SystemAspect::getCurrentAspectId();

            $arrFinalItems = Carrier::getInstance()->getObjSession()->getSession($strKey);
            if ($arrFinalItems !== false) {
                return $arrFinalItems;
            }

            $objAdminInstance = $objModule->getAdminInstanceOfConcreteModule();
            if($objAdminInstance == null) {
                return array();
            }
            
            $arrItems = $objAdminInstance->getOutputModuleNavi();
            $arrItems = array_merge($arrItems, $objAdminInstance->getModuleRightNaviEntry());
            $arrFinalItems = array();
            //build array of final items
            $intI = 0;
            foreach ($arrItems as $arrOneItem) {
                if ($arrOneItem[0] == "") {
                    $bitAdd = true;
                }
                else {
                    $bitAdd = Carrier::getInstance()->getObjRights()->validatePermissionString($arrOneItem[0], $objModule);
                }

                if ($bitAdd || $arrOneItem[1] == "") {

                    if ($arrOneItem[1] != "" || (!isset($arrFinalItems[$intI - 1]) || $arrFinalItems[$intI - 1] != "")) {
                        $arrFinalItems[] = $arrOneItem[1];
                        $intI++;
                    }
                }
            }

            //if the last one is a divider, remove it
            if ($arrFinalItems[count($arrFinalItems) - 1] == "") {
                unset($arrFinalItems[count($arrFinalItems) - 1]);
            }

            Carrier::getInstance()->getObjSession()->setSession($strKey, $arrFinalItems);
            return $arrFinalItems;
        }
        return array();
    }

    /**
     * Static helper to flush the complete backend navigation cache for the current session
     * May be used during language-changes or user-switches
     *
     * @return void
     */
    public static function flushActionNavigationCache()
    {

        $arrAspects = SystemAspect::getObjectListFiltered();

        foreach (SystemModule::getModulesInNaviAsArray() as $arrOneModule) {
            $objOneModule = SystemModule::getModuleByName($arrOneModule["module_name"]);
            foreach ($arrAspects as $objOneAspect) {
                Carrier::getInstance()->getObjSession()->sessionUnset(__CLASS__."adminNaviEntries".$objOneModule->getSystemid().$objOneAspect->getSystemid());
            }
        }

    }

    /**
     * Method which generates the global requirejs config
     *
     * @return string
     */
    public function generateRequireJsConfig()
    {
        $arrRequireConf = BootstrapCache::getInstance()->getCacheContent(BootstrapCache::CACHE_REQUIREJS);
        if (empty($arrRequireConf)) {
            //base config
            $arrRequireConf = array(
                "baseUrl" => '_webpath_',
                "paths" => array(),
                "shim" => array(),
            );

            foreach (Classloader::getInstance()->getArrModules() as $strFolder => $strModule) {
                $strJsonFile = Resourceloader::getInstance()->getAbsolutePathForModule($strModule)."/scripts/provides.json";
                if (is_file($strJsonFile)) {
                    $arrProvidesJs = json_decode(file_get_contents($strJsonFile), true);
                    $strBasePath = StringUtil::substring(Resourceloader::getInstance()->getWebPathForModule($strModule), 1)."/scripts/";


                    if (isset($arrProvidesJs["paths"]) && is_array($arrProvidesJs["paths"])) {
                        foreach ($arrProvidesJs["paths"] as $strUniqueName => $strPath) {
                            $arrRequireConf["paths"][$strUniqueName] = $strBasePath . $strPath;
                        }
                    }

                    if (isset($arrProvidesJs["shim"]) && is_array($arrProvidesJs["shim"])) {
                        foreach ($arrProvidesJs["shim"] as $strUniqueName => $strValue) {
                            $arrRequireConf["shim"][$strUniqueName] = $strValue;
                        }
                    }
                }
            }

            BootstrapCache::getInstance()->updateCache(BootstrapCache::CACHE_REQUIREJS, $arrRequireConf);
        }

        return json_encode($arrRequireConf);
    }

}
