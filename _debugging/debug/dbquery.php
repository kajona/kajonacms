<?php
/*"******************************************************************************************************
*   (c) 2004-2006 by MulchProductions, www.mulchprod.de                                                 *
*   (c) 2007-2010 by Kajona, www.kajona.de                                                              *
*       Published under the GNU LGPL v2.1, see /system/licence_lgpl.txt                                 *
*-------------------------------------------------------------------------------------------------------*
*	$Id: dbimport.php 3081 2010-01-03 10:14:41Z sidler $                                     *
********************************************************************************************************/

require_once("../system/includes.php");


echo "<pre>\n";
echo "+-------------------------------------------------------------------------------+\n";
echo "| Kajona Debug Subsystem                                                        |\n";
echo "|                                                                               |\n";
echo "| DB Query Panel                                                                |\n";
echo "|                                                                               |\n";
echo "+-------------------------------------------------------------------------------+\n";
echo "|loading system kernel...                                                       |\n";

$objCarrier = class_carrier::getInstance();

echo "|loaded.                                                                        |\n";
echo "+-------------------------------------------------------------------------------+\n\n";

if(issetPost("doquery")) {
	$strQuery = getPost("dbquery");
	$objDb = $objCarrier->getObjDB();
	echo "query to run ".$strQuery."\n";

	if($objDb->_query($strQuery))
		echo "\n\nquery successfull.\n";
	else
		echo "\n\nquery failed.\n";
}
else {

	echo "Provide the query to execute.\nPlease be aware of the consequences!\n\n";
	
	echo "<form method=\"post\">";
	echo "<textarea name=\"dbquery\" cols=\"75\" rows=\"10\">";
	echo "</textarea><br />";
	echo "<input type=\"hidden\" name=\"doquery\" value=\"1\" />";
	echo "<input type=\"submit\" value=\"Execute\" />";
	echo "</form>";
}


echo "\n\n";
echo "+-------------------------------------------------------------------------------+\n";
echo "| (c) www.kajona.de                                                             |\n";
echo "+-------------------------------------------------------------------------------+\n";
echo "</pre>";


?>