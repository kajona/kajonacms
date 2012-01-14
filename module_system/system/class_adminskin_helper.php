<?php
/*"******************************************************************************************************
*   (c) 2004-2006 by MulchProductions, www.mulchprod.de                                                 *
*   (c) 2007-2012 by Kajona, www.kajona.de                                                              *
*       Published under the GNU LGPL v2.1, see /system/licence_lgpl.txt                                 *
*-------------------------------------------------------------------------------------------------------*
*	$Id: class_annotations.php 4413 2012-01-03 19:38:11Z sidler $                                            *
********************************************************************************************************/

/**
 * A small class providing a few methods handling the admin-skins available
 *
 * @package module_system
 * @author sidler@mulchprod.de
 * @since 4.0
 */
class class_adminskin_helper {

    /**
     * Returns the list of available admin-skins
     * @static
     * @return array
     */
    public static function getListOfAdminskinsAvailable() {

        $arrFiles = class_resourceloader::getInstance()->getFolderContent("/admin/skins", array(), true);

        $arrReturn = array();
        foreach($arrFiles as $strPath => $strName) {
            if(is_dir(_realpath_.$strPath) && $strName[0] != ".")
                $arrReturn[$strPath] = $strName;
        }

        return $arrReturn;

    }

    /**
     * Loads the file-system path for a single skin
     * @static
     * @param $strSkin
     * @return false|string
     */
    public static function getPathForSkin($strSkin) {
        return class_resourceloader::getInstance()->getPathForFolder("/admin/skins/".$strSkin);
    }

    /**
     * Internal helper, sets the current skinwebpath to be used by skins
     * @static
     *
     */
    public static function defineSkinWebpath() {
        if(!defined("_skinwebpath_"))
            define("_skinwebpath_", _webpath_.self::getPathForSkin(class_carrier::getInstance()->getObjSession()->getAdminSkin()));
    }
}

