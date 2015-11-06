<?php
/*"******************************************************************************************************
*   (c) 2004-2006 by MulchProductions, www.mulchprod.de                                                 *
*   (c) 2007-2015 by Kajona, www.kajona.de                                                              *
*       Published under the GNU LGPL v2.1, see /system/licence_lgpl.txt                                 *
*-------------------------------------------------------------------------------------------------------*
*   $Id$                                           *
********************************************************************************************************/

echo "+-------------------------------------------------------------------------------+\n";
echo "| Kajona Debug Subsystem                                                        |\n";
echo "|                                                                               |\n";
echo "| Legacy class-name usage                                                       |\n";
echo "|                                                                               |\n";
echo "| Searching for files makeing use of deprecated v4 classes                      |\n";
echo "+-------------------------------------------------------------------------------+\n";

$arrLegacy = DEBUG_getLegacyClassNames();
$arrOccurences = array();
foreach($arrLegacy as $strOneFile) {
    $arrOccurences[$strOneFile] = 0;
}


echo "Invoking check at "._realpath_.", listing files below...\n\n";
DEBUG_walkFolderRecursive(_realpath_, $arrLegacy, $arrOccurences);

echo "Class-Names to search:\n";
foreach($arrOccurences as $strSearch => $intHit) {
    echo str_pad($strSearch, 40)." ".$intHit." Hits\n";
}




echo "\n\n...check finished.";

echo "\n\n";
echo "+-------------------------------------------------------------------------------+\n";
echo "| (c) www.kajona.de                                                             |\n";
echo "+-------------------------------------------------------------------------------+\n";


function DEBUG_getLegacyClassNames()
{
    $arrReturn = array();
    $arrModules = class_resourceloader::getInstance()->getArrModules();
    $objFilesystem = new class_filesystem();

    foreach($arrModules as $strPath => $strModule) {
        $strFolder = _realpath_.$strPath."/legacy";
        if(is_dir($strFolder)) {
            $arrClasses = $objFilesystem->getFilelist($strFolder, array(".php"));

            $arrReturn = array_merge($arrReturn, array_map(function ($strFilename) {
                return uniSubstr($strFilename, 0, -4);
            }, $arrClasses));
        }
    }

    return $arrReturn;
}


function DEBUG_walkFolderRecursive($strStartFolder, $arrSearchPatterns, &$arrOccurences)
{
    $objFilesystem = new class_filesystem();
    $arrFilesAndFolders = $objFilesystem->getCompleteList($strStartFolder, array(".php"), array(), array(".", "..", ".svn", ".git", "vendor"));

    foreach($arrFilesAndFolders["files"] as $arrOneFile) {
        $strFilename = $arrOneFile["filename"];

        //include the filecontent
        $strContent = file_get_contents($strStartFolder."/".$strFilename);

        $arrHits = array();
        foreach($arrSearchPatterns as $strOnePattern) {
            $arrHits[$strOnePattern] = substr_count($strContent, $strOnePattern);
        }

        if(array_sum($arrHits) > 0) {
            echo uniStrReplace(_realpath_, "", $strStartFolder)."/<b>".$strFilename."</b>\n";
            foreach($arrHits as $strOneHit => $intCount) {
                $arrOccurences[$strOneHit] += $intCount;
                if($intCount > 0) {
                    echo "  ".$strOneHit." [".$intCount."]\n";
                }
            }
            echo "\n";
        }


    }

    foreach($arrFilesAndFolders["folders"] as $strOneFolder)
        DEBUG_walkFolderRecursive($strStartFolder."/".$strOneFolder, $arrSearchPatterns, $arrOccurences);
}