<?php
/*"******************************************************************************************************
*   (c) 2007-2015 by Kajona, www.kajona.de                                                              *
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
class class_systeminfo_db implements interface_systeminfo {
    /**
     * Returns the title of the info-block
     *
     * @return string
     */
    public function getStrTitle() {
        return class_carrier::getInstance()->getObjLang()->getLang("db", "system");
    }

    /**
     * Returns the contents of the info-block
     *
     * @return mixed
     */
    public function getArrContent() {
        $objLang = class_carrier::getInstance()->getObjLang();
        $arrReturn = array();
        
        $arrTables = class_carrier::getInstance()->getObjDB()->getTables(true);
        $intSizeData = 0;
        $intSizeIndex = 0;

        switch(class_config::getInstance()->getConfig("dbdriver")) {
        case "mysqli":
        case "mysql":
            foreach($arrTables as $arrTable) {
                if(isset($arrTable["Data_length"])) {
                    $intSizeData += $arrTable["Data_length"];
                }
                if(isset($arrTable["Index_length"])) {
                    $intSizeIndex += $arrTable["Index_length"];
                }
            }
            $arrInfo = class_carrier::getInstance()->getObjDB()->getDbInfo();
            $arrReturn[] = array($objLang->getLang("datenbanktreiber", "system"),  $arrInfo["dbdriver"]);
            $arrReturn[] = array($objLang->getLang("datenbankserver", "system"),  $arrInfo["dbserver"]);
            $arrReturn[] = array($objLang->getLang("datenbankclient", "system"),  $arrInfo["dbclient"]);
            $arrReturn[] = array($objLang->getLang("datenbankverbindung", "system"),  $arrInfo["dbconnection"]);
            $arrReturn[] = array($objLang->getLang("anzahltabellen", "system"),  count($arrTables));
            $arrReturn[] = array($objLang->getLang("groessegesamt", "system"),  bytesToString($intSizeData + $intSizeIndex));
            $arrReturn[] = array($objLang->getLang("groessedaten", "system"),  bytesToString($intSizeData));
            break;

        case "postgres":
            $arrInfo = class_carrier::getInstance()->getObjDB()->getDbInfo();
            $arrReturn[] = array($objLang->getLang("datenbanktreiber", "system"),  $arrInfo["dbdriver"]);
            $arrReturn[] = array($objLang->getLang("datenbankserver", "system"),  $arrInfo["dbserver"]);
            $arrReturn[] = array($objLang->getLang("datenbankclient", "system"),  $arrInfo["dbclient"]);
            $arrReturn[] = array($objLang->getLang("datenbankverbindung", "system"),  $arrInfo["dbconnection"]);
            $arrReturn[] = array($objLang->getLang("anzahltabellen", "system"),  count($arrTables));
            $arrReturn[] = array($objLang->getLang("groessegesamt", "system"),  bytesToString($intSizeData + $intSizeIndex));
            $arrReturn[] = array($objLang->getLang("groessedaten", "system"),  bytesToString($intSizeData));
            break;

        default:
            $arrInfo = class_carrier::getInstance()->getObjDB()->getDbInfo();
            $arrReturn[] = array($objLang->getLang("datenbanktreiber", "system"),  $arrInfo["dbdriver"]);
            $arrReturn[] = array($objLang->getLang("datenbankserver", "system"),  $arrInfo["dbserver"]);
            $arrReturn[] = array($objLang->getLang("anzahltabellen", "system"),  count($arrTables));
            break;
        }
        return $arrReturn;
    }

    /**
     * Returns the name of extension/plugin the objects wants to contribute to.
     *
     * @return string
     */
    public static function getExtensionName() {
        return interface_systeminfo::STR_EXTENSION_POINT;
    }

}
