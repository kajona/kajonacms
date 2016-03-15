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
class SysteminfoTime implements SysteminfoInterface
{
    /**
     * Returns the title of the info-block
     *
     * @return string
     */
    public function getStrTitle()
    {
        return Carrier::getInstance()->getObjLang()->getLang("timeinfo", "system");
    }

    /**
     * Returns the contents of the info-block
     *
     * @return mixed
     */
    public function getArrContent()
    {
        $strOldTimezone = date_default_timezone_get();
        $objLang = Carrier::getInstance()->getObjLang();
        $arrReturn = array();
        $arrReturn[] = array($objLang->getLang("time_phptimestamp", "system"), time());
        $arrReturn[] = array($objLang->getLang("time_systemtimezone", "system"), date_default_timezone_get());
        $arrReturn[] = array($objLang->getLang("time_localsystemtime", "system"), timeToString(time()));
        date_default_timezone_set("UTC");
        $arrReturn[] = array($objLang->getLang("time_systemtime_UTC", "system"), date('Y-m-d H:i:s'));
        $arrReturn[] = array($objLang->getLang("time_systemzone_manual_setting", "system"), SystemSetting::getConfigValue("_system_timezone_"));

        date_default_timezone_set($strOldTimezone);
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
