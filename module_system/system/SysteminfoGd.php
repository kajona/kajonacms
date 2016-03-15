<?php
/*"******************************************************************************************************
*   (c) 2007-2016 by Kajona, www.kajona.de                                                              *
*       Published under the GNU LGPL v2.1, see /system/licence_lgpl.txt                                 *
********************************************************************************************************/

namespace Kajona\System\System;


/**
 * General information regarding the current gd lib environment
 *
 * @package module_system
 * @author sidler@mulchprod.de
 * @since 4.5
 */
class SysteminfoGd implements SysteminfoInterface
{

    /**
     * Returns the title of the info-block
     *
     * @return string
     */
    public function getStrTitle()
    {
        return Carrier::getInstance()->getObjLang()->getLang("gd", "system");
    }

    /**
     * Returns the contents of the info-block
     *
     * @return mixed
     */
    public function getArrContent()
    {
        $objLang = Carrier::getInstance()->getObjLang();
        $arrReturn = array();

        if (function_exists("gd_info")) {
            $arrGd = gd_info();
            $arrReturn[] = array($objLang->getLang("version", "system"), $arrGd["GD Version"]);
            $arrReturn[] = array($objLang->getLang("gifread", "system"), (isset($arrGd["GIF Read Support"]) && $arrGd["GIF Read Support"] ? Carrier::getInstance()->getObjLang()->getLang("commons_yes", "system") : Carrier::getInstance()->getObjLang()->getLang("commons_no", "system")));
            $arrReturn[] = array($objLang->getLang("gifwrite", "system"), (isset($arrGd["GIF Create Support"]) && $arrGd["GIF Create Support"] ? Carrier::getInstance()->getObjLang()->getLang("commons_yes", "system") : Carrier::getInstance()->getObjLang()->getLang("commons_no", "system")));
            $arrReturn[] = array($objLang->getLang("jpg", "system"), (((isset($arrGd["JPG Support"]) && $arrGd["JPG Support"]) || (isset($arrGd["JPEG Support"]) && $arrGd["JPEG Support"])) ? Carrier::getInstance()->getObjLang()->getLang("commons_yes", "system") : Carrier::getInstance()->getObjLang()->getLang("commons_no", "system")));
            $arrReturn[] = array($objLang->getLang("png", "system"), (isset($arrGd["PNG Support"]) && $arrGd["PNG Support"] ? Carrier::getInstance()->getObjLang()->getLang("commons_yes", "system") : Carrier::getInstance()->getObjLang()->getLang("commons_no", "system")));
        }
        else {
            $arrReturn[] = array("", Carrier::getInstance()->getObjLang()->getLang("keinegd", "system"));
        }

        return $arrReturn;
    }

    /**
     * Returns the name of extension/plugin the objects wants to contribute to.
     *
     * @return string
     */
    public static function getExtensionName()
    {
        return SysteminfoInterface::STR_EXTENSION_POINT;
    }

}
