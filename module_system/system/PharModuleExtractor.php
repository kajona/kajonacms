<?php
/*"******************************************************************************************************
*   (c) 2007-2015 by Kajona, www.kajona.de                                                              *
*       Published under the GNU LGPL v2.1, see /system/licence_lgpl.txt                                 *
********************************************************************************************************/

namespace Kajona\System\System;

use class_apc_cache;
use class_config;
use class_logger;


/**
 * Tries to extract the static contents of a phar in order to make them accessible by the webserver
 *
 * @author sidler@mulchprod.de
 * @since 4.8
 */
class PharModuleExtractor
{
    private $strLogName = "pharextractor.log";

    private $strPharMapCacheFile = "";
    private $arrOldPharMap = array();
    private $bitCacheSaveRequired = false;

    private $strExtractPattern = '/\.(jpg|jpeg|gif|png|js|less|css|otf|eot|svg|ttf|woff|woff2)$/i';


    public function __construct()
    {
        $this->strPharMapCacheFile = _realpath_."/project/temp/pharsums.cache";
        $this->arrOldPharMap = class_apc_cache::getInstance()->getValue(__CLASS__."pharsums");


        if($this->arrOldPharMap === false) {
            if (is_file($this->strPharMapCacheFile)) {
                $this->arrOldPharMap = unserialize(file_get_contents($this->strPharMapCacheFile));
            }
            else {
                $this->bitCacheSaveRequired = true;
            }
        }
    }

    public function __destruct()
    {
        if ($this->bitCacheSaveRequired && true /*FIXME reenable!! class_config::getInstance()->getConfig('resourcecaching') == true*/) {
            class_apc_cache::getInstance()->addValue(__CLASS__."pharsums", $this->arrOldPharMap);
            file_put_contents($this->strPharMapCacheFile, serialize($this->arrOldPharMap));
        }
    }


    private function extractStaticContent($arrIndexMap)
    {
        //fetch all phar based modules
        $arrModules = \class_classloader::getInstance()->getArrModules();

        $objFilesystem = new \class_filesystem();
        foreach($arrModules as $strPath => $strModule) {

            //to index?
            if(!isset($arrIndexMap[$strModule])) {
                continue;
            }

            if(!PharModule::isPhar($strPath)) {
                continue;
            }

            //mark revision indexed
            $this->arrOldPharMap[$strModule] = $arrIndexMap[$strModule];
            $this->bitCacheSaveRequired = true;

            class_logger::getInstance($this->strLogName)->addLogRow("extracting phar ".$strPath."\n", class_logger::$levelInfo);

            $objPharModule = new PharModule($strPath);

            foreach($objPharModule->getFileIterator() as $strKey => $arrFileInfo) {
                //check for matching suffix and move to temp dir
                if(preg_match($this->strExtractPattern, $strKey)) {
                    //extract the file and export it
                    $strTargetPath = _realpath_."/files/extract/".$strModule.uniSubstr($strKey, uniStrrpos($strKey, ".phar")+5);
                    $objFilesystem->folderCreate(dirname($strTargetPath), true);
                    //copy
                    copy($strKey, $strTargetPath);
                }
            }

        }


    }


    private function createPharMap()
    {
        $arrPharMap = array();
        $arrModules = \class_classloader::getInstance()->getArrModules();

        foreach($arrModules as $strPath => $strModule) {
            if (!PharModule::isPhar($strPath)) {
                continue;
            }

            $strSum = sha1_file($strPath);
            if(!isset($this->arrOldPharMap[$strModule]) || $this->arrOldPharMap[$strModule] != $strSum) {
                $arrPharMap[$strModule] = sha1_file($strPath);
            }

        }

        return $arrPharMap;
    }


    public static function bootstrapPharContent()
    {
        $objInstance = new PharModuleExtractor();
        $arrIndex = $objInstance->createPharMap();
        if(!empty($arrIndex)) {
            $objInstance->extractStaticContent($arrIndex);
        }
    }

}
