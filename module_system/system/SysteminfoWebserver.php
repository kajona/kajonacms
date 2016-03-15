<?php
/*"******************************************************************************************************
*   (c) 2007-2016 by Kajona, www.kajona.de                                                              *
*       Published under the GNU LGPL v2.1, see /system/licence_lgpl.txt                                 *
********************************************************************************************************/

namespace Kajona\System\System;


/**
 * General information regarding the current timezone environment
 *
 * @package module_system
 * @author sidler@mulchprod.de
 * @since 4.5
 */
class SysteminfoWebserver implements SysteminfoInterface
{
    /**
     * Returns the title of the info-block
     *
     * @return string
     */
    public function getStrTitle()
    {
        return Carrier::getInstance()->getObjLang()->getLang("server", "system");
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
        $arrReturn[] = array($objLang->getLang("operatingsystem", "system"), php_uname());
        $arrReturn[] = array($objLang->getLang("systeminfo_webserver_version", "system"), $_SERVER["SERVER_SOFTWARE"]);
        if (function_exists("apache_get_modules")) {
            $arrReturn[] = array($objLang->getLang("systeminfo_webserver_modules", "system"), implode(", ", @apache_get_modules()));
        }
        if (@disk_total_space(_realpath_)) {
            $arrReturn[] = array($objLang->getLang("speicherplatz", "system"), bytesToString(@disk_free_space(_realpath_))."/".bytesToString(@disk_total_space(_realpath_)).$objLang->getLang("diskspace_free", "system"));
        }
        $arrReturn[] = array($objLang->getLang("system_realpath", "system"), _realpath_);
        $arrReturn[] = array($objLang->getLang("system_webpath", "system"), _webpath_);

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
