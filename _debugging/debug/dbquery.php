<?php
/*"******************************************************************************************************
*   (c) 2004-2006 by MulchProductions, www.mulchprod.de                                                 *
*   (c) 2007-2016 by Kajona, www.kajona.de                                                              *
*       Published under the GNU LGPL v2.1, see /system/licence_lgpl.txt                                 *
********************************************************************************************************/

namespace Kajona\Debugging\Debug;

echo "+-------------------------------------------------------------------------------+\n";
echo "| Kajona Debug Subsystem                                                        |\n";
echo "|                                                                               |\n";
echo "| DB Query Panel                                                                |\n";
echo "|                                                                               |\n";
echo "+-------------------------------------------------------------------------------+\n";

if(issetPost("doquery")) {
	$strQuery = getPost("dbquery");

    if(get_magic_quotes_gpc() == 1)
        $strQuery = stripslashes($strQuery);

	$objDb = \Kajona\System\System\Carrier::getInstance()->getObjDB();
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


