<?php
/*"******************************************************************************************************
*   (c) 2007-2014 by Kajona, www.kajona.de                                                              *
*       Published under the GNU LGPL v2.1, see /system/licence_lgpl.txt                                 *
*-------------------------------------------------------------------------------------------------------*
*	$Id: class_changelog_provider_settings.php 6322 2014-01-02 08:31:49Z sidler $                      *
********************************************************************************************************/

/**
 * General information regarding the current timezone environment
 *
 * @package module_system
 * @author sidler@mulchprod.de
 * @since 4.5
 */
class class_systeminfo_webserver implements interface_systeminfo {
    /**
     * Returns the title of the info-block
     *
     * @return string
     */
    public function getStrTitle() {
        return class_carrier::getInstance()->getObjLang()->getLang("server", "system");
    }

    /**
     * Returns the contents of the info-block
     *
     * @return mixed
     */
    public function getStrContent() {
        $objLang = class_carrier::getInstance()->getObjLang();
        

        $arrReturn = array();
        $arrReturn[] = array($objLang->getLang("operatingsystem", "system"),  php_uname());
        $arrReturn[] = array($objLang->getLang("systeminfo_webserver_version", "system"),  $_SERVER["SERVER_SOFTWARE"]);
        if(function_exists("apache_get_modules")) {
            $arrReturn[] = array($objLang->getLang("systeminfo_webserver_modules", "system"),  implode(", ", @apache_get_modules()));
        }
        if(@disk_total_space(_realpath_)) {
            $arrReturn[] = array($objLang->getLang("speicherplatz", "system"),  bytesToString(@disk_free_space(_realpath_)) . "/" . bytesToString(@disk_total_space(_realpath_)) . $objLang->getLang("diskspace_free", "system"));
        }
        $arrReturn[] = array($objLang->getLang("system_realpath", "system"),  _realpath_);
        $arrReturn[] = array($objLang->getLang("system_webpath", "system"),  _webpath_);

        return class_carrier::getInstance()->getObjToolkit("admin")->dataTable(null, $arrReturn);
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
