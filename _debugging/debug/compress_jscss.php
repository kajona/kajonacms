<?php
/*"******************************************************************************************************
*   (c) 2004-2006 by MulchProductions, www.mulchprod.de                                                 *
*   (c) 2007-2009 by Kajona, www.kajona.de                                                              *
*       Published under the GNU LGPL v2.1, see /system/licence_lgpl.txt                                 *
*-------------------------------------------------------------------------------------------------------*
*   $Id: compress_jscss.php 2353 2008-12-31 15:22:01Z jschroeter $                                           *
********************************************************************************************************/

require_once("../system/includes.php");
include_once(_realpath_."/system/class_filesystem.php");


echo "<pre>\n";
echo "+-------------------------------------------------------------------------------+\n";
echo "| Kajona Debug Subsystem                                                        |\n";
echo "|                                                                               |\n";
echo "| Compress_jscss                                                                |\n";
echo "|                                                                               |\n";
echo "| Use this script to compress all *.js and *.css files to increase website      |\n";
echo "| performance. It's using YUICompressor, so you need Java >= 1.4.               |\n";
echo "| Please refer to http://developer.yahoo.com/yui/compressor/ for detailed       |\n";
echo "| information about how the compression is working.                             |\n";
echo "+-------------------------------------------------------------------------------+\n";
echo "|loading system kernel...                                                       |\n";

        $objCarrier = class_carrier::getInstance();

echo "|loaded.                                                                        |\n";
echo "+-------------------------------------------------------------------------------+\n";

$strYUICompressorCommand = "java -jar \""._realpath_."/debug/compress_jscss/yuicompressor-2.4.2.jar\"";

$strTargetPathFull = "/debug/compress_jscss/output_full";
$strTargetPathCompressed = "/debug/compress_jscss/output_compressed";

//include folders and their subfolders
$arrFoldersToInclude = array(
    "/admin/scripts",
    "/admin/skins",
    "/installer",
    "/portal/css",
    "/portal/scripts"
);

//exclude folders and their subfolders
$arrFoldersToExclude = array(
    "/admin/scripts/yui",
    "/admin/scripts/fckeditor",
    "/portal/scripts/yui"
);

$objFilesystem = new class_filesystem();

function walkFolderRecursive($strStartFolder) {
    global $strYUICompressorCommand;
	global $strTargetPathFull;
	global $strTargetPathCompressed;
	global $objFilesystem;
	global $arrFoldersToExclude;

	$arrFilesAndFolders = $objFilesystem->getCompleteList($strStartFolder, array(".js", ".css"), array(), array(".", "..", ".svn", ".settings", "debug"));
	echo "<b>Scan folder ".$strStartFolder."</b><br/>";

	foreach($arrFilesAndFolders["files"] as $arrOneFile) {
		if (!uniStrpos($arrOneFile["filename"], "-full.") && !uniStrpos($arrOneFile["filename"], "-min.")) {
			$strTargetFileCompressed = $strTargetPathCompressed.uniStrReplace(_realpath_, "", $arrOneFile["filepath"]);
			$strTargetFileFull = $strTargetPathFull.uniStrReplace(array(_realpath_, ".js", ".css"), array("", "-full.js", "-full.css"), $arrOneFile["filepath"]);
			echo "\t".$arrOneFile["filename"]."\n";

			//check if the file has already a compressed version named *-min.*
			$strAlreadyCompressedFile = uniStrReplace(array(".js", ".css"), array("-min.js", "-min.css"), $arrOneFile["filepath"]);
			if (is_file($strAlreadyCompressedFile)) {
			    //yes, skip file
                echo "\t\t<b>SKIPPED, since there's already a *.-min file</b>\n";
			} else {
			    //no, so we do the compression!

				//extract the subfolders
				$strFolder = uniStrReplace(array(_realpath_, "/".$arrOneFile["filename"]), array("", ""), $arrOneFile["filepath"]);

				//create folders for full-copies and copy the original file
				if ($objFilesystem->folderCreate($strTargetPathFull.$strFolder, true)) {
				    $objFilesystem->fileCopy(uniStrReplace(_realpath_, "", $arrOneFile["filepath"]), $strTargetFileFull, true);
				}

				//create folders for compressed files and call YUICompressor
				if ($objFilesystem->folderCreate($strTargetPathCompressed.$strFolder, true)) {
				    $strCommand = $strYUICompressorCommand." --charset utf-8 -o \""._realpath_.$strTargetFileCompressed."\" \""._realpath_.$strTargetFileFull."\"";
			        //Now do a systemfork
			        $intTemp = "";
			        $strResult = system($strCommand, $intTemp);
			        if (is_file(_realpath_.$strTargetFileCompressed)) {
			            @chmod(_realpath_.$strTargetFileCompressed, 0777);
			        } else {
                        echo "\t\t<b>!!ERROR!! output: ".($strResult == 0)." ".$intTemp.")</b>\n";
                        //delete backup of original file
                        $objFilesystem->fileDelete($strTargetFileFull);
			        }
				}
			}
		}
	}

    foreach($arrFilesAndFolders["folders"] as $strOneFolder) {
        $strCurrentFolder = uniStrReplace(_realpath_, "", $strStartFolder."/".$strOneFolder);
        if (!in_array($strCurrentFolder, $arrFoldersToExclude)) {
	        walkFolderRecursive($strStartFolder."/".$strOneFolder);
    	}
    }
}

echo "| Current config:\n";
echo "| \tYUICompressor command:\n";
echo "| \t\t".$strYUICompressorCommand."\n";
echo "| \tIncluded folders:\n";
foreach($arrFoldersToInclude as $strOneFolder) {
    echo "| \t\t".$strOneFolder."\n";
}
echo "| \tExcluded folders:\n";
foreach($arrFoldersToExclude as $strOneFolder) {
    echo "| \t\t".$strOneFolder."\n";
}

echo "+-------------------------------------------------------------------------------+\n\n";

//delete old files
echo "Flushing target folders...\n\n";
$objFilesystem->folderDeleteRecursive($strTargetPathFull);
$objFilesystem->folderDeleteRecursive($strTargetPathCompressed);

echo "Start scanning for *.js/*.css files in "._realpath_.":\n\n";
foreach($arrFoldersToInclude as $strOneFolder) {
    walkFolderRecursive(_realpath_.$strOneFolder);
}

$intFolderSizeFull = $objFilesystem->folderSize($strTargetPathFull);
$intFolderSizeCompressed = $objFilesystem->folderSize($strTargetPathCompressed);

echo "\n\n";
echo "+-------------------------------------------------------------------------------+\n";
echo "...compression done!\n\n";
echo "Original (uncompressed) size:\t".bytesToString($intFolderSizeFull)."\n";
echo "Compressed size:\t\t".bytesToString($intFolderSizeCompressed)."\n";
echo "saved:\t\t\t\t".bytesToString($intFolderSizeFull-$intFolderSizeCompressed)."\n";
echo "\n\n";
echo "Now just upload the files in \"<b>".$strTargetPathCompressed."\"</b>\nto your webserver and overwrite the existing files.\n";
echo "Don't overwrite the files in your local project folder, since it's pretty\nhard to edit compressed files afterwards ;-)\n\n";
echo "A backup of the original files was made in \"".$strTargetPathFull."\". \n";

echo "\n\n";
echo "+-------------------------------------------------------------------------------+\n";
echo "| (c) www.kajona.de                                                             |\n";
echo "+-------------------------------------------------------------------------------+\n";
echo "</pre>";


?>