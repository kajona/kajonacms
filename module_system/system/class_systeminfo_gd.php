<?php
/*"******************************************************************************************************
*   (c) 2007-2014 by Kajona, www.kajona.de                                                              *
*       Published under the GNU LGPL v2.1, see /system/licence_lgpl.txt                                 *
*-------------------------------------------------------------------------------------------------------*
*	$Id: class_changelog_provider_settings.php 6322 2014-01-02 08:31:49Z sidler $                      *
********************************************************************************************************/

/**
 * General information regarding the current gd lib environment
 *
 * @package module_system
 * @author sidler@mulchprod.de
 * @since 4.5
 */
class class_systeminfo_gd implements interface_systeminfo {
    /**
     * Returns the title of the info-block
     *
     * @return string
     */
    public function getStrTitle() {
        return class_carrier::getInstance()->getObjLang()->getLang("gd", "system");
    }

    /**
     * Returns the contents of the info-block
     *
     * @return mixed
     */
    public function getArrContent() {
        $objLang = class_carrier::getInstance()->getObjLang();
        $arrReturn = array();

        if(function_exists("gd_info")) {
            $arrGd = gd_info();
            $arrReturn[] = array($objLang->getLang("version", "system"),  $arrGd["GD Version"]);
            $arrReturn[] = array($objLang->getLang("gifread", "system"),  (isset($arrGd["GIF Read Support"]) && $arrGd["GIF Read Support"] ? class_carrier::getInstance()->getObjLang()->getLang("commons_yes", "system") : class_carrier::getInstance()->getObjLang()->getLang("commons_no", "system")));
            $arrReturn[] = array($objLang->getLang("gifwrite", "system"),  (isset($arrGd["GIF Create Support"]) && $arrGd["GIF Create Support"] ? class_carrier::getInstance()->getObjLang()->getLang("commons_yes", "system") : class_carrier::getInstance()->getObjLang()->getLang("commons_no", "system")));
            $arrReturn[] = array($objLang->getLang("jpg", "system"),  (((isset($arrGd["JPG Support"]) && $arrGd["JPG Support"]) || (isset($arrGd["JPEG Support"]) && $arrGd["JPEG Support"])) ? class_carrier::getInstance()->getObjLang()->getLang("commons_yes", "system") : class_carrier::getInstance()->getObjLang()->getLang("commons_no", "system")));
            $arrReturn[] = array($objLang->getLang("png", "system"),  (isset($arrGd["PNG Support"]) && $arrGd["PNG Support"] ? class_carrier::getInstance()->getObjLang()->getLang("commons_yes", "system") : class_carrier::getInstance()->getObjLang()->getLang("commons_no", "system")));
        }
        else {
            $arrReturn[] = array("", class_carrier::getInstance()->getObjLang()->getLang("keinegd", "system"));
        }

        return $arrReturn;
    }

    /**
     * Returns the name of extension/plugin the objects wants to contribute to.
     *
     * @return string
     */
    public function getExtensionName() {
        return interface_systeminfo::STR_EXTENSION_POINT;
    }

}
