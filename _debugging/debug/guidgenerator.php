<?php
/*"******************************************************************************************************
*   (c) 2004-2006 by MulchProductions, www.mulchprod.de                                                 *
*   (c) 2007-2011 by Kajona, www.kajona.de                                                              *
*       Published under the GNU LGPL v2.1, see /system/licence_lgpl.txt                                 *
*-------------------------------------------------------------------------------------------------------*
*   $Id$                                     *
********************************************************************************************************/

header("Content-Type: text/html; charset=utf-8");
require_once("../system/bootstrap.php");


echo "<pre>\n";
echo "+-------------------------------------------------------------------------------+\n";
echo "| Kajona Debug Subsystem                                                        |\n";
echo "|                                                                               |\n";
echo "| GUID-Generator                                                                |\n";
echo "|                                                                               |\n";
echo "+-------------------------------------------------------------------------------+\n";
echo "|loading system kernel...                                                       |\n";

        $objCarrier = class_carrier::getInstance();

echo "|loaded.                                                                        |\n";
echo "+-------------------------------------------------------------------------------+\n\n";

echo "Generating 10 guids...\n\n";
echo "\t".generateSystemid()."\n";
echo "\t".generateSystemid()."\n";
echo "\t".generateSystemid()."\n";
echo "\t".generateSystemid()."\n";
echo "\t".generateSystemid()."\n";
echo "\t".generateSystemid()."\n";
echo "\t".generateSystemid()."\n";
echo "\t".generateSystemid()."\n";
echo "\t".generateSystemid()."\n";
echo "\t".generateSystemid()."\n";

echo "\n\n";
echo "+-------------------------------------------------------------------------------+\n";
echo "| (c) www.kajona.de                                                             |\n";
echo "+-------------------------------------------------------------------------------+\n";
echo "</pre>";


