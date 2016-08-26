<?php
/*"******************************************************************************************************
*   (c) 2007-2016 by Kajona, www.kajona.de                                                              *
*       Published under the GNU LGPL v2.1, see /system/licence_lgpl.txt                                 *
********************************************************************************************************/

namespace Kajona\System\System;


/**
 * General information regarding the current database environment
 *
 * @author sidler@mulchprod.de
 * @since 4.5
 */
class SysteminfoDb implements SysteminfoInterface
{
    /**
     * Returns the title of the info-block
     *
     * @return string
     */
    public function getStrTitle()
    {
        return Carrier::getInstance()->getObjLang()->getLang("db", "system");
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

        $arrTables = Carrier::getInstance()->getObjDB()->getTables(true);
        $arrReturn[] = array($objLang->getLang("datenbanktreiber", "system"), Config::getInstance()->getConfig("dbdriver"));
        $arrReturn[] = array($objLang->getLang("datenbankserver", "system"),  Config::getInstance()->getConfig("dbhost"));
        $arrReturn[] = array($objLang->getLang("db", "system"),  Config::getInstance()->getConfig("dbname"));
        $arrReturn[] = array($objLang->getLang("anzahltabellen", "system"), count($arrTables));

        $arrInfo = Carrier::getInstance()->getObjDB()->getDbInfo();
        foreach($arrInfo as $strKey => $strValue) {
            $arrReturn[] = array($strKey, $strValue);
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
