<?php
/*"******************************************************************************************************
*   (c) 2007-2015 by Kajona, www.kajona.de                                                              *
*       Published under the GNU LGPL v2.1, see /system/licence_lgpl.txt                                 *
********************************************************************************************************/

namespace Kajona\System\System;


/**
 * General information regarding the current php environment
 *
 * @package module_system
 * @author sidler@mulchprod.de
 * @since 4.5
 */
class SysteminfoPhp implements SysteminfoInterface
{
    /**
     * Returns the title of the info-block
     *
     * @return string
     */
    public function getStrTitle()
    {
        return Carrier::getInstance()->getObjLang()->getLang("php", "system");
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
        $arrReturn[] = array($objLang->getLang("version", "system"), phpversion());
        $arrReturn[] = array($objLang->getLang("geladeneerweiterungen", "system"), implode(", ", get_loaded_extensions()));
        $arrReturn[] = array($objLang->getLang("executiontimeout", "system"), Carrier::getInstance()->getObjConfig()->getPhpIni("max_execution_time")."s");
        $arrReturn[] = array($objLang->getLang("inputtimeout", "system"), Carrier::getInstance()->getObjConfig()->getPhpIni("max_input_time")."s");
        $arrReturn[] = array($objLang->getLang("memorylimit", "system"), bytesToString(ini_get("memory_limit"), true));
        $arrReturn[] = array($objLang->getLang("errorlevel", "system"), Carrier::getInstance()->getObjConfig()->getPhpIni("error_reporting"));
        $arrReturn[] = array($objLang->getLang("systeminfo_php_safemode", "system"), (ini_get("safe_mode") ? $objLang->getLang("commons_yes", "system") : $objLang->getLang("commons_no", "system")));
        $arrReturn[] = array($objLang->getLang("systeminfo_php_urlfopen", "system"), (ini_get("allow_url_fopen") ? $objLang->getLang("commons_yes", "system") : $objLang->getLang("commons_no", "system")));
        $arrReturn[] = array($objLang->getLang("systeminfo_php_regglobal", "system"), (ini_get("register_globals") ? $objLang->getLang("commons_yes", "system") : $objLang->getLang("commons_no", "system")));
        $arrReturn[] = array($objLang->getLang("postmaxsize", "system"), bytesToString(ini_get("post_max_size"), true));
        $arrReturn[] = array($objLang->getLang("uploadmaxsize", "system"), bytesToString(ini_get("upload_max_filesize"), true));
        $arrReturn[] = array($objLang->getLang("uploads", "system"), (Carrier::getInstance()->getObjConfig()->getPhpIni("file_uploads") == 1 ? $objLang->getLang("commons_yes", "system") : $objLang->getLang("commons_no", "system")));
        $arrReturn[] = array($objLang->getLang("timezone", "system"), date_default_timezone_get());
        $arrReturn[] = array($objLang->getLang("datekajona", "system"), dateToString(new Date()));

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
