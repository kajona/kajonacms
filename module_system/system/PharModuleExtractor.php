<?php
/*"******************************************************************************************************
*   (c) 2007-2016 by Kajona, www.kajona.de                                                              *
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
    private $strExtractPattern = '/\.(jpg|jpeg|gif|png|js|less|css|otf|eot|svg|ttf|woff|woff2)$/i';


    private function extractStaticContent($arrIndexMap)
    {
        //fetch all phar based modules
        $arrModules = Classloader::getInstance()->getArrModules();

        $objFilesystem = new Filesystem();
        foreach ($arrModules as $strPath => $strModule) {

            //to index?
            if (!isset($arrIndexMap[$strModule])) {
                continue;
            }

            if (!PharModule::isPhar($strPath)) {
                continue;
            }

            //mark revision indexed
            BootstrapCache::getInstance()->addCacheRow(BootstrapCache::CACHE_PHARSUMS, $strModule, $arrIndexMap[$strModule]);
            Logger::getInstance($this->strLogName)->addLogRow("extracting phar ".$strPath."\n", Logger::$levelInfo);

            $objPharModule = new PharModule($strPath);

            foreach ($objPharModule->getContentMap() as $strKey => $strFullPath) {

                //check for matching suffix and move to temp dir
                if (preg_match($this->strExtractPattern, $strKey)) {
                    //extract the file and export it
                    $strTargetPath = _realpath_."/files/extract/".$strModule."/".$strKey;

                    try {
                        $objFilesystem->folderCreate(dirname($strTargetPath), true, true);
                    } catch (Exception $objEx) {
                        throw new Exception("Failed to copy to "._realpath_."files/extract/, please make sure the directory is writable and reload the page.", Exception::$level_FATALERROR, $objEx);
                    }
                    //copy
                    copy($strFullPath, $strTargetPath);
                }
            }

        }

    }


    private function createPharMap()
    {
        $arrPharMap = array();
        $arrOldMap = BootstrapCache::getInstance()->getCacheContent(BootstrapCache::CACHE_PHARSUMS);
        $arrModules = Classloader::getInstance()->getArrModules();

        foreach ($arrModules as $strPath => $strModule) {
            if (!PharModule::isPhar($strPath)) {
                continue;
            }

            $strSum = filemtime(_realpath_.$strPath);
            if (!isset($arrOldMap[$strModule]) || $arrOldMap[$strModule] != $strSum) {
                $arrPharMap[$strModule] = $strSum;
            }

        }

        return $arrPharMap;
    }


    public static function bootstrapPharContent()
    {
        $objInstance = new PharModuleExtractor();
        $arrIndex = $objInstance->createPharMap();
        if (!empty($arrIndex)) {
            Classloader::getInstance()->flushCache();
            $objInstance->extractStaticContent($arrIndex);
        }
    }

}
