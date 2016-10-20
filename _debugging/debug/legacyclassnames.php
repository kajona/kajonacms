<?php
/*"******************************************************************************************************
*   (c) 2004-2006 by MulchProductions, www.mulchprod.de                                                 *
*   (c) 2007-2016 by Kajona, www.kajona.de                                                              *
*       Published under the GNU LGPL v2.1, see /system/licence_lgpl.txt                                 *
*-------------------------------------------------------------------------------------------------------*
*   $Id$                                           *
********************************************************************************************************/
namespace Kajona\Debugging\Debug;

use Kajona\System\System\Classloader;
use Kajona\System\System\Filesystem;
use Kajona\System\System\StringUtil;

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
    if($intHit == 0)
        continue;

    echo str_pad($strSearch, 40)." ".$intHit." Hits\n";
}

echo "\n";
echo str_pad("Total Hits", 40)." ".array_sum($arrOccurences)." Hits\n";



echo "\n\n...check finished.";

echo "\n\n";
echo "+-------------------------------------------------------------------------------+\n";
echo "| (c) www.kajona.de                                                             |\n";
echo "+-------------------------------------------------------------------------------+\n";


function DEBUG_getLegacyClassNames()
{
    $arrReturn = array();
    $arrModules = Classloader::getInstance()->getArrModules();
    $objFilesystem = new Filesystem();

    foreach($arrModules as $strPath => $strModule) {
        $strFolder = _realpath_.$strPath."/legacy";
        if(is_dir($strFolder)) {
            $arrClasses = $objFilesystem->getFilelist($strFolder, array(".php"));

            $arrReturn = array_merge($arrReturn, array_map(function ($strFilename) {
                return StringUtil::substring($strFilename, 0, -4);
            }, $arrClasses));
        }
    }

    return $arrReturn;
}


function DEBUG_walkFolderRecursive($strStartFolder, $arrSearchPatterns, &$arrOccurences)
{
    $objFilesystem = new Filesystem();
    $arrFilesAndFolders = $objFilesystem->getCompleteList($strStartFolder, array(".php", ".md"), array(), array(".", "..", ".svn", ".git", "vendor", "legacy", "buildproject"));

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

        ob_flush();
        flush();

    }

    foreach($arrFilesAndFolders["folders"] as $strOneFolder)
        DEBUG_walkFolderRecursive($strStartFolder."/".$strOneFolder, $arrSearchPatterns, $arrOccurences);
}