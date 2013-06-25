<?php
/*"******************************************************************************************************
*   (c) 2004-2006 by MulchProductions, www.mulchprod.de                                                 *
*   (c) 2007-2013 by Kajona, www.kajona.de                                                              *
*       Published under the GNU LGPL v2.1, see /system/licence_lgpl.txt                                 *
*-------------------------------------------------------------------------------------------------------*
*	$Id$                                            *
********************************************************************************************************/

/**
 * A small class providing a few methods handling the admin-skins available
 *
 * @package module_system
 * @author sidler@mulchprod.de
 * @since 4.0
 */
class class_adminskin_helper {

    private static $strSkinPath = null;

    /**
     * @var interface_adminskin_imageresolver
     */
    private static $objAdminImageResolver = null;

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
     * @return string
     */
    public static function getPathForSkin($strSkin) {
        if(self::$strSkinPath == null)
            self::$strSkinPath = class_resourceloader::getInstance()->getPathForFolder("/admin/skins/".$strSkin);

        return self::$strSkinPath;
    }

    /**
     * Internal helper, sets the current skinwebpath to be used by skins
     * @static
     */
    public static function defineSkinWebpath() {
        if(!defined("_skinwebpath_"))
            define("_skinwebpath_", _webpath_.self::getPathForSkin(class_carrier::getInstance()->getObjSession()->getAdminSkin()));
    }

    /**
     * Makes use of the admin-skin image-mapper to resolve an image-name into a
     * real image-tag / script / whatever
     *
     * @param $strName
     * @param string $strAlt
     * @param bool $bitBlockTooltip
     * @param string $strEntryId
     *
     * @return string
     */
    public static function getAdminImage($strName, $strAlt = "", $bitBlockTooltip = false, $strEntryId = "") {
        if(self::$objAdminImageResolver == null) {
            include_once _realpath_.self::getPathForSkin(class_carrier::getInstance()->getObjSession()->getAdminSkin())."/class_adminskin_imageresolver.php";
            self::$objAdminImageResolver = new class_adminskin_imageresolver();
        }

        return self::$objAdminImageResolver->getImage($strName, $strAlt, $bitBlockTooltip, $strEntryId);
    }
}

