<?php
/*"******************************************************************************************************
*   (c) 2004-2006 by MulchProductions, www.mulchprod.de                                                 *
*   (c) 2007-2016 by Kajona, www.kajona.de                                                              *
*       Published under the GNU LGPL v2.1, see /system/licence_lgpl.txt                                 *
*-------------------------------------------------------------------------------------------------------*
*   $Id$                                           *
********************************************************************************************************/
namespace Kajona\Debugging\Debug;

use RecursiveDirectoryIterator;
use RecursiveIteratorIterator;
use RecursiveRegexIterator;
use RegexIterator;

echo "+-------------------------------------------------------------------------------+\n";
echo "| Kajona Debug Subsystem                                                        |\n";
echo "|                                                                               |\n";
echo "| Analyze external packages                                                     |\n";
echo "+-------------------------------------------------------------------------------+\n";

echo "Searching for *_external.json files...\n\n";


$objRegex = new RegexIterator(new RecursiveIteratorIterator(new RecursiveDirectoryIterator(_realpath_)), '/^.+_external\.json/i', RecursiveRegexIterator::GET_MATCH);
$arrExternals = array();
foreach($objRegex as $arrOne) {
    $objContent = json_decode(file_get_contents($arrOne[0]));

    $strSimplePath = uniStrReplace(_realpath_, "", $arrOne[0]);

    if(is_array($objContent)) {
        foreach($objContent as $objOneExternal) {
            $objOneExternal->path = $strSimplePath;
            $arrExternals[] = $objOneExternal;
        }
    }
    else {
        $objContent->path = $strSimplePath;
        $arrExternals[] = $objContent;
    }
}

usort($arrExternals, function($objOneEntry, $objSecondEntry) {
    return strcmp($objOneEntry->name, $objSecondEntry->name);
});


$arrHeader = array();
$arrHeader[] = "Component";
$arrHeader[] = "Version";
$arrHeader[] = "Source";
$arrHeader[] = "Path";
$arrHeader[] = "Licence";

$arrUserRows = array();
foreach($arrExternals as $intI => $objContent) {
    $arrUserRows[$intI]["name"] = $objContent->name;
    $arrUserRows[$intI]["version"] = $objContent->version;


    $strSource = $objContent->source;
    if(is_array($strSource))
        $strSource = implode("<br />", $strSource);

    $arrUserRows[$intI]["sourceurl"] = "<a href=\"$strSource\" target=\"_blank\">".$strSource."</a>";

    $arrUserRows[$intI]["path"] = $objContent->path;
    $arrUserRows[$intI]["licence"] = "<a href=\"$objContent->licenseurl\" target=\"_blank\">$objContent->license</a>";
}
echo "<style>table,td,tr,th { border:1px solid grey;border-spacing: 0px; border-collapse: collapse;}</style>";

echo \Kajona\System\System\Carrier::getInstance()->getObjToolkit("admin")->dataTable($arrHeader, $arrUserRows);
