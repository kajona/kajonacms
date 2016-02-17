<?php
/*"******************************************************************************************************
*   (c) 2007-2015 by Kajona, www.kajona.de                                                              *
*       Published under the GNU LGPL v2.1, see /system/licence_lgpl.txt                                 *
*-------------------------------------------------------------------------------------------------------*
*	$Id$	                                            *
********************************************************************************************************/

namespace Kajona\System\Admin;


/**
 * A class holding common helper-methods for the backend.
 * The main purpose is to reduce the code stored at class_admin_controller
 *
 * @package module_system
 * @author  sidler@mulchprod.de
 * @since   4.0
 */
class AdminHelper {

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
    public static function getAdminPathNavi($arrPathEntries, $strSourceModule = "") {
        //modify some of the entries
        $arrMenuEntries = array();
        $arrModules = class_module_system_module::getModulesInNaviAsArray();
        foreach($arrModules as $arrOneModule) {
            $objModule = class_module_system_module::getModuleByName($arrOneModule["module_name"]);

            if(!$objModule->rightView())
                continue;

            $arrCurMenuEntry = array(
                "name" => class_carrier::getInstance()->getObjLang()->getLang("modul_titel", $arrOneModule["module_name"]),
                "onclick" => "location.href='".class_link::getLinkAdminHref($arrOneModule["module_name"], "", "", false)."'",
                "link" => "#"
            );

            //fetch the submenu entries
            if($objModule != null) {
                $arrActionMenuEntries = array();
                $arrModuleActions = self::getModuleActionNaviHelper($objModule);
                foreach($arrModuleActions as $strOneAction) {
                    if($strOneAction != "") {
                        $arrLink = splitUpLink($strOneAction);

                        if($arrLink["name"] != "" && $arrLink["href"] != "")
                            $arrActionMenuEntries[] = array(
                                "name" => $arrLink["name"],
                                "onclick" => "location.href='".$arrLink["href"]."'",
                                "link" => $arrLink["href"]
                            );


                    }
                    else if($strOneAction == "") {
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
                    ".class_carrier::getInstance()->getObjToolkit("admin")->registerMenu($strModuleMenuId, $arrMenuEntries)."</span>";

        array_unshift($arrPathEntries, $strModuleSwitcher);
        return class_carrier::getInstance()->getObjToolkit("admin")->getPathNavigation($arrPathEntries);

    }


    /**
     * Fetches the list of actions for a single module, saved to the session for performance reasons
     *
     * @param class_module_system_module $objModule
     * @static
     *
     * @return array
     */
    public static function getModuleActionNaviHelper(class_module_system_module $objModule) {
        if(class_carrier::getInstance()->getObjSession()->isLoggedin()) {

            $strKey = __CLASS__."adminNaviEntries".$objModule->getSystemid().class_module_system_aspect::getCurrentAspectId();

            $arrFinalItems = class_carrier::getInstance()->getObjSession()->getSession($strKey);
            if($arrFinalItems !== false)
                return $arrFinalItems;

            $objAdminInstance = $objModule->getAdminInstanceOfConcreteModule();
            $arrItems = $objAdminInstance->getOutputModuleNavi();
            $arrItems = array_merge($arrItems, $objAdminInstance->getModuleRightNaviEntry());
            $arrFinalItems = array();
            //build array of final items
            $intI = 0;
            foreach($arrItems as $arrOneItem) {
                if($arrOneItem[0] == "")
                    $bitAdd = true;
                else
                    $bitAdd = class_carrier::getInstance()->getObjRights()->validatePermissionString($arrOneItem[0], $objModule);

                if($bitAdd || $arrOneItem[1] == "") {

                    if($arrOneItem[1] != "" || (!isset($arrFinalItems[$intI-1]) || $arrFinalItems[$intI-1] != "")) {
                        $arrFinalItems[] = $arrOneItem[1];
                        $intI++;
                    }
                }
            }

            //if the last one is a divider, remove it
            if($arrFinalItems[count($arrFinalItems)-1] == "")
                unset($arrFinalItems[count($arrFinalItems)-1]);

            class_carrier::getInstance()->getObjSession()->setSession($strKey, $arrFinalItems);
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
    public static function flushActionNavigationCache() {

        $arrAspects = class_module_system_aspect::getObjectList();

        foreach(class_module_system_module::getModulesInNaviAsArray() as $arrOneModule) {
            $objOneModule = class_module_system_module::getModuleByName($arrOneModule["module_name"]);
            foreach($arrAspects as $objOneAspect)
                class_carrier::getInstance()->getObjSession()->sessionUnset(__CLASS__."adminNaviEntries".$objOneModule->getSystemid().$objOneAspect->getSystemid());
        }

    }

}
