<?php
/*"******************************************************************************************************
*   (c) 2004-2006 by MulchProductions, www.mulchprod.de                                                 *
*   (c) 2007-2008 by Kajona, www.kajona.de                                                              *
*       Published under the GNU LGPL v2.1, see /system/licence_lgpl.txt                                 *
*-------------------------------------------------------------------------------------------------------*
*                                                                                                       *
*   gdinfo.php                                                                                          *
*   Script to display the settings of the local gdlib installation                                      *                                                                                                       *
*                                                                                                       *
*-------------------------------------------------------------------------------------------------------*
*   $Id$                                     *
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

var_export(gd_info());

echo "\n\n";
echo "+-------------------------------------------------------------------------------+\n";
echo "| (c) www.kajona.de                                                             |\n";
echo "+-------------------------------------------------------------------------------+\n";
echo "</pre>";


?>
 
