<?php
/*"******************************************************************************************************
*   (c) 2004-2006 by MulchProductions, www.mulchprod.de                                                 *
*   (c) 2007-2012 by Kajona, www.kajona.de                                                              *
*       Published under the GNU LGPL v2.1, see /system/licence_lgpl.txt                                 *
*-------------------------------------------------------------------------------------------------------*
*	$Id$                                     *
********************************************************************************************************/

echo "+-------------------------------------------------------------------------------+\n";
echo "| Kajona Debug Subsystem                                                        |\n";
echo "|                                                                               |\n";
echo "| DB Importer                                                                   |\n";
echo "|                                                                               |\n";
echo "+-------------------------------------------------------------------------------+\n";
echo "|loading system kernel...                                                       |\n";

$objCarrier = class_carrier::getInstance();

echo "|loaded.                                                                        |\n";
echo "+-------------------------------------------------------------------------------+\n\n";

if(issetPost("doimport")) {
    $strFilename = getPost("dumpname");
    $objDb = $objCarrier->getObjDB();
    echo "importing ".$strFilename."\n";
    if($objDb->importDb($strFilename))
        echo "import successfull.\n";
    else
        echo "import failed.\n";
}
else {

    $objFilesystem = new class_filesystem();

    if($objFilesystem->isWritable("/project/dbdumps")) {
        echo "Searching dbdumps available...\n";

        $arrFiles = $objFilesystem->getFilelist(_projectpath_."dbdumps/", array(".zip", ".gz", ".sql"));
        echo "Found ".count($arrFiles)." dump(s)\n\n";

        echo "<form method='post'>";
        echo "Dump to import:\n";
        echo "<select name='dumpname' type='dropdown'>";
        foreach ($arrFiles as $strOneFile) {
            $arrDetails = $objFilesystem->getFileDetails(_projectpath_."dbdumps/".$strOneFile);

            $strTimestamp = "";
            if(uniStrpos($strOneFile, "_") !== false)
                $strTimestamp = uniSubstr($strOneFile, uniStrrpos($strOneFile, "_")+1, (uniStrpos($strOneFile, ".")-uniStrrpos($strOneFile, "_")));

            if(uniStrlen($strTimestamp) > 9 && is_numeric($strTimestamp))
                echo "<option id='".$strOneFile."' value='".$strOneFile."'>".$strOneFile." (".timeToString($strTimestamp)." - ".bytesToString($arrDetails["filesize"]).")</option>";
            else
                echo "<option id='".$strOneFile."' value='".$strOneFile."'>".$strOneFile." (".bytesToString($arrDetails["filesize"]).")</option>";
        }
        echo "</select>";
        echo "<input type='hidden' name='doimport' value='1' />";
        echo "<input type='submit' value='import' />";
        echo "</form>";
    }
    else
        echo "<span style='color: red;'>WARNING!!\n\nThe system/dbdumps is NOT writeable. DB dumps can NOT be imported! </span>\n\n";


    echo "searching dbdumps available...\n";

}


echo "\n\n";
echo "+-------------------------------------------------------------------------------+\n";
echo "| (c) www.kajona.de                                                             |\n";
echo "+-------------------------------------------------------------------------------+\n";


