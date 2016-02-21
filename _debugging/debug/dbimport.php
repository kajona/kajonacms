<?php
/*"******************************************************************************************************
*   (c) 2004-2006 by MulchProductions, www.mulchprod.de                                                 *
*   (c) 2007-2015 by Kajona, www.kajona.de                                                              *
*       Published under the GNU LGPL v2.1, see /system/licence_lgpl.txt                                 *
********************************************************************************************************/
namespace Kajona\Debugging\Debug;

use Kajona\System\System\Filesystem;

echo "+-------------------------------------------------------------------------------+\n";
echo "| Kajona Debug Subsystem                                                        |\n";
echo "|                                                                               |\n";
echo "| DB Importer                                                                   |\n";
echo "|                                                                               |\n";
echo "+-------------------------------------------------------------------------------+\n";

if(issetPost("doimport")) {
    $strFilename = getPost("dumpname");
    $objDb = \Kajona\System\System\Carrier::getInstance()->getObjDB();
    echo "importing ".$strFilename."\n";
    if($objDb->importDb($strFilename))
        echo "\n<span style='color: green;font-weight:bold;'>import successfull.</span>\n";
    else
        echo "\n<span style='color: red;font-weight:bold;'>import failed!!</span>\n";
}
else {
    echo "Searching for dumps in dbdumps under: "._projectpath_."\n";

    $objFilesystem = new Filesystem();
    if($objFilesystem->isWritable("/project/dbdumps")) {
        echo "Searching dbdumps available...\n";

        $arrFiles = $objFilesystem->getFilelist(_projectpath_."/dbdumps/", array(".zip", ".gz", ".sql"));
        echo "Found ".count($arrFiles)." dump(s)\n\n";

        echo "<form method='post'>";
        echo "Dump to import:\n";
        
        $arrImportfileData = array();
        foreach ($arrFiles as $strOneFile) {
            $strFileInfo ="";
            $arrDetails = $objFilesystem->getFileDetails(_projectpath_."/dbdumps/".$strOneFile);

            $strTimestamp = "";
            if(uniStrpos($strOneFile, "_") !== false)
                $strTimestamp = uniSubstr($strOneFile, uniStrrpos($strOneFile, "_")+1, (uniStrpos($strOneFile, ".")-uniStrrpos($strOneFile, "_")));

            
            if(uniStrlen($strTimestamp) > 9 && is_numeric($strTimestamp))
                //if the timestamp is the last part of the filename, we can use $strTimestamp
                $strFileInfo = $strOneFile
                    ." (".bytesToString($arrDetails['filesize']).")"
                    ."\n    Timestamp according to file name: ".timeToString($strTimestamp)
                    ."\n    Timestamp according to file info: ".timeToString($arrDetails['filechange']);
            else
                $strFileInfo = $strOneFile
                    ." (".bytesToString($arrDetails['filesize']).")"
                    ."\n    Timestamp according to file info: ".timeToString($arrDetails['filechange']);
            
            $arrImportfileData[$strOneFile] = $strFileInfo;            
        }        
        
        $bitShowButton = false;
        foreach($arrImportfileData as $strFilename => $strFileInfo) {
            echo "\n<input type='radio' name='dumpname' id='dumpname_".$strFilename."' value='$strFilename' /><label for='dumpname_".$strFilename."'>".$strFileInfo."</label>";
            $bitShowButton = true;
        } 

        if($bitShowButton) {
            echo "\n\n<input type='hidden' name='doimport' value='1' />";
            echo "<input type='submit' value='Import dump' />";
        }
    else
        echo "\nNo dump found.";
        
        echo "</form>";
    }
    else
        echo "<span style='color: red;'>WARNING!!\n\nThe folder system/dbdumps is NOT writeable. DB dumps can NOT be imported! </span>\n\n";




}


echo "\n\n";
echo "+-------------------------------------------------------------------------------+\n";
echo "| (c) www.kajona.de                                                             |\n";
echo "+-------------------------------------------------------------------------------+\n";


