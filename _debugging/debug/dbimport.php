<?php
/*"******************************************************************************************************
*   (c) 2004-2006 by MulchProductions, www.mulchprod.de                                                 *
*   (c) 2007-2008 by Kajona, www.kajona.de                                                              *
*       Published under the GNU LGPL v2.1, see /system/licence_lgpl.txt                                 *
*-------------------------------------------------------------------------------------------------------*
* 																										*
* 	dbimport.php															  						    *
*   Script to import a database without having a running system											*																										*
*																										*
*-------------------------------------------------------------------------------------------------------*
*	$Id$                                     *
********************************************************************************************************/

require_once("../system/includes.php");
include_once(_realpath_."/system/class_filesystem.php");


echo "<pre>\n";
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

	echo "searching dbdumps available...\n";
	
	$objFilesystem = new class_filesystem();
	$arrFiles = $objFilesystem->getFilelist("/system/dbdumps/");
	echo "found ".count($arrFiles)." dump(s)\n\n";
	
	echo "<form method=\"post\">";
	echo "dump to import:\n";
	echo "<select name=\"dumpname\" type=\"dropdown\">";
	foreach ($arrFiles as $strOneFile)
		echo "<option id=\"".$strOneFile."\">".$strOneFile."</option>";
	echo "</select>";
	echo "<input type=\"hidden\" name=\"doimport\" value=\"1\" />";
	echo "<input type=\"submit\" value=\"import\" />";
	echo "</form>";
}


echo "\n\n";
echo "+-------------------------------------------------------------------------------+\n";
echo "| (c) www.kajona.de                                                             |\n";
echo "+-------------------------------------------------------------------------------+\n";
echo "</pre>";


?>