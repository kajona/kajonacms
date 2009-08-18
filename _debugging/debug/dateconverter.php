<?php
/*"******************************************************************************************************
*   (c) 2004-2006 by MulchProductions, www.mulchprod.de                                                 *
*   (c) 2007-2009 by Kajona, www.kajona.de                                                              *
*       Published under the GNU LGPL v2.1, see /system/licence_lgpl.txt                                 *
*-------------------------------------------------------------------------------------------------------*
*   $Id$                                     *
********************************************************************************************************/

require_once("../system/includes.php");
include_once(_realpath_."/system/class_filesystem.php");
include_once(_realpath_."/system/class_date.php");


echo "<pre>\n";
echo "+-------------------------------------------------------------------------------+\n";
echo "| Kajona Debug Subsystem                                                        |\n";
echo "|                                                                               |\n";
echo "| DATECONVERTER                                                                 |\n";
echo "|                                                                               |\n";
echo "+-------------------------------------------------------------------------------+\n";
echo "|loading system kernel...                                                       |\n";

        $objCarrier = class_carrier::getInstance();

echo "|loaded.                                                                        |\n";
echo "+-------------------------------------------------------------------------------+\n\n";


echo "<form method=\"post\">";
echo "--- UNIX timestamp handling -----------------------------------------------------\n\n";
echo "integer to date: \n";
echo "\tinteger: <input type=\"text\" value=\"".getPost("inttime")."\" name=\"inttime\"/>";
echo "  --> ".timeToString(getPost("inttime"))."\n";

echo "date to integer: \n";
echo "\tdate: d:<input type=\"text\" value=\"".getPost("strday")."\" name=\"strday\" size=\"2\" />";
echo " m:<input type=\"text\" value=\"".getPost("strmonth")."\" name=\"strmonth\" size=\"2\" />";
echo " y:<input type=\"text\" value=\"".getPost("stryear")."\" name=\"stryear\" size=\"4\" />";
echo "  --> ".strtotime(getPost("stryear")."-".getPost("strmonth")."-".getPost("strday"))."\n";

echo "\n<input type=\"submit\" value=\"submit\" />\n";


echo "\n--- Kajona timestamp handling ---------------------------------------------------\n\n";
echo "integer to date: \n";
$objDateFromInt = new class_date();
$objDateFromInt->setTimeInOldStyle(getPost("kajonainttime"));
echo "\tinteger: <input type=\"text\" value=\"".getPost("kajonainttime")."\" name=\"kajonainttime\"/>";
echo "  --> ".$objDateFromInt."\n";


echo "\n<input type=\"submit\" value=\"submit\" />\n";
echo "</form>";

echo "\n\n";
echo "+-------------------------------------------------------------------------------+\n";
echo "| (c) www.kajona.de                                                             |\n";
echo "+-------------------------------------------------------------------------------+\n";
echo "</pre>";


?>