<?php
/*"******************************************************************************************************
*   (c) 2007-2012 by Kajona, www.kajona.de                                                              *
*       Published under the GNU LGPL v2.1, see /system/licence_lgpl.txt                                 *
*-------------------------------------------------------------------------------------------------------*
*	$Id: class_admin.php 4363 2011-12-12 15:34:56Z sidler $	                                            *
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
     * Writes the main backend navigation, so collects
     * all modules of the current aspect
     * Creates a list of all installed modules
     *
     * @param $strCurrentModule
     *
     * @return string
     */
    public static function getOutputMainNavi($strCurrentModule) {
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
            //NOTE: Some special Modules need other highlights
            if($strCurrentModule == "elemente")
                $strCurrentModule = "pages";

            return class_carrier::getInstance()->getObjToolkit("admin")->getAdminModuleNavi($arrModuleRows, $strCurrentModule);
        }
    }


    public static function getModuleActionNavi(class_admin $objAdminModule) {
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

            //Pass to the skin-object
            return class_carrier::getInstance()->getObjToolkit("admin")->getAdminModuleActionNavi($arrFinalItems);
        }
    }


}
