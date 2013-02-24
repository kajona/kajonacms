<?php
/*"******************************************************************************************************
*   (c) 2007-2013 by Kajona, www.kajona.de                                                              *
*       Published under the GNU LGPL v2.1, see /system/licence_lgpl.txt                                 *
*-------------------------------------------------------------------------------------------------------*
*	$Id$	                                            *
********************************************************************************************************/

/**
 * A class holding common helper-methods for the backend.
 * The main purpose is to reduce the code stored at class_admin
 *
 * @package module_system
 * @author sidler@mulchprod.de
 * @since 4.0
 */
class class_admin_helper {

    /**
     * Adds a menu-button to the second entry of the path-array. The menu renders the list of all modules installed,
     * including a quick-jump link.
     *
     * @static
     *
     * @param $arrPathEntries
     * @param string $strSourceModule
     *
     * @internal param array $arrModuleActions
     * @return string
     */
    public static function getAdminPathNavi($arrPathEntries, $strSourceModule = "") {
        //modify some of the entries
        $arrMenuEntries = array();
        $arrModules = class_module_system_module::getModulesInNaviAsArray();
        foreach($arrModules as $arrOneModule) {
            $objModule = class_module_system_module::getModuleByName($arrOneModule["module_name"]);

            if(!$objModule->rightEdit())
                continue;

            $arrCurMenuEntry = array(
                "name" => class_carrier::getInstance()->getObjLang()->getLang("modul_titel", $arrOneModule["module_name"]),
                "onclick" => "location.href='".getLinkAdminHref($arrOneModule["module_name"], "", "", false)."'",
                "link" => "#"
            );

            //fetch the submenu entries
            if($objModule != null) {
                $arrActionMenuEntries = array();
                $arrModuleActions = self::getModuleActionNaviHelper($objModule->getAdminInstanceOfConcreteModule());
                foreach($arrModuleActions as $strOneAction) {
                    if($strOneAction != "") {
                        $arrLink = splitUpLink($strOneAction);

                        $arrActionMenuEntries[] = array(
                            "name" => $arrLink["name"],
                            "onclick" => "location.href='".$arrLink["href"]."'",
                            "link" => $arrLink["href"]
                        );
                    }
                    else {
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
                    <span class='dropdown moduleSwitch'><a href=\"#\" data-toggle='dropdown' class=\"moduleSwitchLink\" role='button' \">+</a>
                    ".class_carrier::getInstance()->getObjToolkit("admin")->registerMenu($strModuleMenuId, $arrMenuEntries)."</span>";

        array_unshift($arrPathEntries, $strModuleSwitcher);
        return class_carrier::getInstance()->getObjToolkit("admin")->getPathNavigation($arrPathEntries);

    }


    /**
     * Fetches the list of actions for a single module, saved to the session for performance reasons
     * @static
     * @param class_admin $objAdminModule
     * @return array
     */
    public static function getModuleActionNaviHelper(class_admin $objAdminModule) {
        if(class_carrier::getInstance()->getObjSession()->isLoggedin()) {

            $arrFinalItems = class_carrier::getInstance()->getObjSession()->getSession(__CLASS__."adminNaviEntries".get_class($objAdminModule));
            if($arrFinalItems !== false)
                return $arrFinalItems;


            $objModule = $objAdminModule->getObjModule();
            $arrItems = $objAdminModule->getOutputModuleNavi();
            $arrFinalItems = array();
            //build array of final items
            foreach($arrItems as $arrOneItem) {
                if($arrOneItem[0] == "")
                    $bitAdd = true;
                else
                    $bitAdd = class_carrier::getInstance()->getObjRights()->validatePermissionString($arrOneItem[0], $objModule);

                if($bitAdd || $arrOneItem[1] == "")
                    $arrFinalItems[] = $arrOneItem[1];
            }

            class_carrier::getInstance()->getObjSession()->setSession(__CLASS__."adminNaviEntries".get_class($objAdminModule), $arrFinalItems);
            return $arrFinalItems;
        }
        return array();
    }


}
