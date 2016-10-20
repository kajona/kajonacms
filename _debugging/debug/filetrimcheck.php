<?php
/*"******************************************************************************************************
*   (c) 2004-2006 by MulchProductions, www.mulchprod.de                                                 *
*   (c) 2007-2016 by Kajona, www.kajona.de                                                              *
*       Published under the GNU LGPL v2.1, see /system/licence_lgpl.txt                                 *
*-------------------------------------------------------------------------------------------------------*
*   $Id$                                           *
********************************************************************************************************/
namespace Kajona\Debugging\Debug;

use Kajona\System\System\Filesystem;

echo "+-------------------------------------------------------------------------------+\n";
echo "| Kajona Debug Subsystem                                                        |\n";
echo "|                                                                               |\n";
echo "| Filetrimcheck                                                                 |\n";
echo "|                                                                               |\n";
echo "| Searching for files containing whitespace characters outside the php-tag.     |\n";
echo "| This may cause errors when using gzip-compression.                            |\n";
echo "+-------------------------------------------------------------------------------+\n";

//loop over files and folders in order to find erroneous script-files


function walkFolderRecursive($strStartFolder) {
    $objFilesystem = new Filesystem();
    $arrFilesAndFolders = $objFilesystem->getCompleteList($strStartFolder, array(".php"), array(), array(".", "..", ".svn", "vendor"));

    foreach($arrFilesAndFolders["files"] as $arrOneFile) {
        $strFilename = $arrOneFile["filename"];

        //include the filecontent
        $strContent = file_get_contents($strStartFolder."/".$strFilename);
        if(StringUtil::substring($strContent, 0, 5) != "<?php")
            echo "Whitespace at the beginning of file >> ".$strStartFolder."/".$strFilename." is:>".StringUtil::substring($strContent, 0, 1)."< << \n";
    }

    foreach($arrFilesAndFolders["folders"] as $strOneFolder)
        walkFolderRecursive($strStartFolder."/".$strOneFolder);

    ob_flush();
    flush();
}

echo "Invoking check at "._realpath_.", listing files below...\n\n";

walkFolderRecursive(_realpath_);

echo "\n\n...check finished.";

echo "\n\n";
echo "+-------------------------------------------------------------------------------+\n";
echo "| (c) www.kajona.de                                                             |\n";
echo "+-------------------------------------------------------------------------------+\n";


