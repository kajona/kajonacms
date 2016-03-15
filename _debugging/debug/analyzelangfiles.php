<?php
/*"******************************************************************************************************
*   (c) 2004-2006 by MulchProductions, www.mulchprod.de                                                 *
*   (c) 2007-2016 by Kajona, www.kajona.de                                                              *
*       Published under the GNU LGPL v2.1, see /system/licence_lgpl.txt                                 *
*-------------------------------------------------------------------------------------------------------*
*   $Id$                                           *
********************************************************************************************************/

namespace Kajona\Debugging\Debug;

use Kajona\System\System\Resourceloader;

echo "+-------------------------------------------------------------------------------+\n";
echo "| Kajona Debug Subsystem                                                        |\n";
echo "|                                                                               |\n";
echo "| Analyze lang-files                                                            |\n";
echo "|                                                                               |\n";
echo "| Prints all lang-entries in all languages given in order to find duplicates.   |\n";
echo "| Therefore different algorithms are used to identify similar ones.             |\n";
echo "+-------------------------------------------------------------------------------+\n";

echo "parsing lang-files...\n";
flush();

$strStartFolder = "/lang";
$arrEntries = array();
debug_parse_foldercontent($strStartFolder, $arrEntries);

echo "found ".count($arrEntries)." entries to analyze...\n\n";
flush();

echo "calculating soundex for en...\n";
flush();

debug_get_soundex($arrEntries);

usort($arrEntries, 'debug_sort');

echo "<table border=\"1\">";
echo "<tr>";
echo "  <th>Module</th>";
echo "  <th>Key</th>";
echo "  <th>Soundex</th>";
echo "  <th>EN</th>";
echo "  <th>DE</th>";
echo "  <th>PT</th>";
echo "  <th>BG</th>";
echo "  <th>RU</th>";
echo "  <th>SV</th>";
echo "</tr>";

$strPrevHash = "";
foreach($arrEntries as $objOneEntry) {

    if($objOneEntry->strSoundex == "")
        continue;

    $strStyle = "";

    if($objOneEntry->strHash == $strPrevHash)
        $strStyle = "background-color: green;";

    $strPrevHash = $objOneEntry->strHash;

    echo "<tr style=\"".$strStyle."\">";
    echo "  <td>".$objOneEntry->strModul."</td>";
    echo "  <td>".$objOneEntry->strKey."</td>";
    echo "  <td>".$objOneEntry->strSoundex."</td>";
    echo "  <td>".htmlentities($objOneEntry->strEn, ENT_COMPAT, "UTF-8")."</td>";
    echo "  <td>".htmlentities($objOneEntry->strDe, ENT_COMPAT, "UTF-8")."</td>";
    echo "  <td>".htmlentities($objOneEntry->strPt, ENT_COMPAT, "UTF-8")."</td>";
    echo "  <td>".htmlentities($objOneEntry->strBg, ENT_COMPAT, "UTF-8")."</td>";
    echo "  <td>".htmlentities($objOneEntry->strRu, ENT_COMPAT, "UTF-8")."</td>";
    echo "  <td>".htmlentities($objOneEntry->strSv, ENT_COMPAT, "UTF-8")."</td>";
    echo "</tr>";
}
echo "</table>";


function debug_parse_foldercontent($strSourceFolder, &$arrEntries) {
    $arrContent = Resourceloader::getInstance()->getFolderContent($strSourceFolder, array(), true);
    foreach($arrContent as $strPath => $strOneEntry) {
        if($strOneEntry == "." || $strOneEntry == "..")
            continue;

        if(is_file($strPath) && substr($strOneEntry, 0, 5) == "lang_") {

            $arrTemp = explode("_", substr($strOneEntry, 0, -4));
            //regular lang file found, parse contents
            $lang = array();
            include $strPath;

            foreach($lang as $strKey => $strValue) {

                $strModul = $arrTemp[1];

                $objTemp = debug_get_langhelper($arrEntries, $strModul, $strKey);
                if($arrTemp[2] == "de") $objTemp->strDe = $strValue;
                if($arrTemp[2] == "en") $objTemp->strEn = $strValue;
                if($arrTemp[2] == "pt") $objTemp->strPt = $strValue;
                if($arrTemp[2] == "bg") $objTemp->strBg = $strValue;
                if($arrTemp[2] == "ru") $objTemp->strRu = $strValue;
                if($arrTemp[2] == "sv") $objTemp->strSv = $strValue;


            }
        }

        if(is_dir($strPath) && $strOneEntry != ".svn") {
            debug_parse_foldercontent($strSourceFolder."/".$strOneEntry, $arrEntries);
        }
    }
}

function debug_get_langhelper(&$arrEntries, $strModul, $strKey) {
    foreach($arrEntries as $objOneHelper) {
        if($objOneHelper->strModul == $strModul && $objOneHelper->strKey == $strKey) {
            return $objOneHelper;
        }

    }

    $objOneHelper = new DebugLangHelper();
    $objOneHelper->strModul = $strModul;
    $objOneHelper->strKey = $strKey;
    $arrEntries[] = $objOneHelper;
    return $objOneHelper;
}

function debug_get_soundex($arrEntries) {
    foreach($arrEntries as $objOneHelper) {
        if(is_string($objOneHelper->strEn)) {
            $objOneHelper->strSoundex = soundex(strtolower($objOneHelper->strEn));
            $objOneHelper->strHash = md5(strtolower($objOneHelper->strEn));
        }
    }
}


function debug_sort( $objA, $objB ) {
    if($objA->strSoundex == $objB->strSoundex) {
        if($objA->strEn == $objB->strEn) {
            return 0 ;
        }
        return ($objA->strEn < $objB->strEn) ? -1 : 1;
    }
    return ($objA->strSoundex < $objB->strSoundex) ? -1 : 1;
}




class DebugLangHelper {
    public $strModul;
    public $strKey;
    public $strDe;
    public $strEn;
    public $strPt;
    public $strBg;
    public $strRu;
    public $strSv;

    public $strSoundex;
    public $strHash;
}


echo "\n\n";
echo "+-------------------------------------------------------------------------------+\n";
echo "| (c) www.kajona.de                                                             |\n";
echo "+-------------------------------------------------------------------------------+\n";
