<?php
/*"******************************************************************************************************
*   (c) 2004-2006 by MulchProductions, www.mulchprod.de                                                 *
*   (c) 2007-2014 by Kajona, www.kajona.de                                                              *
*       Published under the GNU LGPL v2.1, see /system/licence_lgpl.txt                                 *
*-------------------------------------------------------------------------------------------------------*
*   $Id$                                           *
********************************************************************************************************/


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


echo "<b>";

echo str_pad("Component", 20);
echo str_pad("Version", 20);
echo str_pad("Source", 60);
echo str_pad("Path", 60);

echo "</b>\n";

foreach($arrExternals as $objContent) {
    echo str_pad($objContent->name, 20);
    echo str_pad($objContent->version, 20);

    $strSource = $objContent->source;
    if(is_array($strSource))
        $strSource = implode(", ", $strSource);

    echo str_pad($strSource, 60);

    echo str_pad($objContent->path, 60);
    echo "\n";
}