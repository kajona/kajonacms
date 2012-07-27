<?php
/*"******************************************************************************************************
*   (c) 2007-2012 by Kajona, www.kajona.de                                                              *
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


    public static $STR_PAGES_GROUP          = "1_pages";
    public static $STR_SYSTEM_GROUP         = "3_system";
    public static $STR_USERCONTENT_GROUP    = "2_usercontent";

    /**
     * Adds a menu-button to the second entry of the path-array. The menu renders the list of all modules installed,
     * including a quick-jump link.
     *
     * @static
     * @param $arrPathEntries
     * @return string
     */
    public static function getAdminPathNavi($arrPathEntries) {
        //modify some of the entries
        $strModuleMenuId = generateSystemid();
        $arrMenuEntries = array();
        $arrModules = class_module_system_module::getModulesInNaviAsArray(class_module_system_aspect::getCurrentAspectId());
        foreach($arrModules as $arrOneModule) {
            $arrMenuEntries[] = array(
                "name" => class_carrier::getInstance()->getObjLang()->getLang("modul_titel", $arrOneModule["module_name"]),
                "onclick" => "location.href='".getLinkAdminHref($arrOneModule["module_name"], "", "", false)."'"
            );
        }


        if(isset($arrPathEntries[1]))
            $arrPathEntries[1] = "<a href=\"#\" onclick=\"KAJONA.admin.contextMenu.showElementMenu('".$strModuleMenuId."', this);\">+</a> ".$arrPathEntries[1];

        return class_carrier::getInstance()->getObjToolkit("admin")->getPathNavigation($arrPathEntries).
           class_carrier::getInstance()->getObjToolkit("admin")->registerMenu($strModuleMenuId, $arrMenuEntries);

    }

    /**
     * Writes the main backend navigation, so collects
     * all modules of the current aspect
     * Creates a list of all installed modules
     * Internal helper, collects all modules and prepares the lins
     *
     * @return array
     */
    public static function getOutputMainNaviHelper() {
        if(class_carrier::getInstance()->getObjSession()->isLoggedin()) {
            //Loading all Modules
            $arrModules = class_module_system_module::getModulesInNaviAsArray(class_module_system_aspect::getCurrentAspectId());
            $intI = 0;
            $arrModuleRows = array();
            foreach ($arrModules as $arrModule) {
                $objCommon = new class_module_system_common($arrModule["module_id"]);
                if($objCommon->rightView()) {
                    //Generate a view infos
                    $arrModuleRows[$intI]["rawName"] = $arrModule["module_name"];
                    $arrModuleRows[$intI]["name"] = class_carrier::getInstance()->getObjLang()->getLang("modul_titel", $arrModule["module_name"]);
                    $arrModuleRows[$intI]["link"] = getLinkAdmin($arrModule["module_name"], "", "", $arrModule["module_name"], $arrModule["module_name"], "", true, "adminModuleNavi");
                    $arrModuleRows[$intI]["href"] = getLinkAdminHref($arrModule["module_name"], "");
                    $intI++;
                }
            }
            return $arrModuleRows;
        }

        return array();
    }

    /**
     * Fetches the list of action for a single module
     * @static
     * @param class_admin $objAdminModule
     * @return array
     */
    public static function getModuleActionNaviHelper(class_admin $objAdminModule) {
        if(class_carrier::getInstance()->getObjSession()->isLoggedin()) {
            $objModule = $objAdminModule->getObjModule();
            $arrItems = $objAdminModule->getOutputModuleNavi();
            $arrFinalItems = array();
            //build array of final items
            foreach($arrItems as $arrOneItem) {
                $bitAdd = false;
                switch ($arrOneItem[0]) {
                    case "view":
                        if($objModule->rightView())
                            $bitAdd = true;
                        break;
                    case "edit":
                        if($objModule->rightEdit())
                            $bitAdd = true;
                        break;
                    case "delete":
                        if($objModule->rightDelete())
                            $bitAdd = true;
                        break;
                    case "right":
                        if($objModule->rightRight())
                            $bitAdd = true;
                        break;
                    case "right1":
                        if($objModule->rightRight1())
                            $bitAdd = true;
                        break;
                    case "right2":
                        if($objModule->rightRight2())
                            $bitAdd = true;
                        break;
                    case "right3":
                        if($objModule->rightRight3())
                            $bitAdd = true;
                        break;
                    case "right4":
                        if($objModule->rightRight4())
                            $bitAdd = true;
                        break;
                    case "right5":
                        if($objModule->rightRight5())
                            $bitAdd = true;
                        break;
                    case "":
                        $bitAdd = true;
                        break;
                    default:
                        break;
                }

                if($bitAdd || $arrOneItem[1] == "")
                    $arrFinalItems[] = $arrOneItem[1];
            }

            return $arrFinalItems;
        }
        return array();
    }


}
