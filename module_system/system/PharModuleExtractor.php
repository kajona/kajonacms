<?php
/*"******************************************************************************************************
*   (c) 2007-2015 by Kajona, www.kajona.de                                                              *
*       Published under the GNU LGPL v2.1, see /system/licence_lgpl.txt                                 *
********************************************************************************************************/

namespace Kajona\System\System;


/**
 * Tries to extract the static contents of a phar in order to make them accessible by the webserver
 *
 * @author sidler@mulchprod.de
 * @since 4.8
 */
class PharModuleExtractor
{
    private $strLogName = "pharextractor.log";

    private $arrExtractTypes = array(
        ".jpg",
        ".jpeg",
        ".gif",
        ".png",
        ".js",
        ".less",
        ".css",
        ".otf",
        ".eot",
        ".svg",
        ".ttf",
        ".woff",
        ".woff2"
    );

    public function extractStaticContent()
    {
        //fetch all phar based modules
        $arrModules = \class_classloader::getInstance()->getArrModules();

        foreach($arrModules as $strPath => $strModule) {
            if(!PharModule::isPhar($strPath)) {
                continue;
            }

//            \class_logger::getInstance($this->strLogName)->addLogRow("extracting phar ".$strPath."\n", \class_logger::$levelWarning);

            $objPharModule = new PharModule($strPath);

            foreach($objPharModule->getFileIterator() as $strKey => $strValue) {
                //check for matching suffix and move to temp dir
            }

        }


    }



    public static function bootstrapPharContent()
    {
        $objInstance = new PharModuleExtractor();
        $objInstance->extractStaticContent();
    }
}
