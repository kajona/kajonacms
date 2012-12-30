<?php
/*"******************************************************************************************************
*   (c) 2004-2006 by MulchProductions, www.mulchprod.de                                                 *
*   (c) 2007-2013 by Kajona, www.kajona.de                                                              *
*       Published under the GNU LGPL v2.1, see /system/licence_lgpl.txt                                 *
*-------------------------------------------------------------------------------------------------------*
*   $Id$                               *
********************************************************************************************************/

//
//echo "<pre>\n";
//echo "+-------------------------------------------------------------------------------+\n";
//echo "| Kajona Debug Subsystem                                                        |\n";
//echo "|                                                                               |\n";
//echo "| System Table Visualizer                                                       |\n";
//echo "|                                                                               |\n";
//echo "| Providing a tree-like view on your system-table.                              |\n";
//echo "+-------------------------------------------------------------------------------+\n";
//echo "|loading system kernel...                                                       |\n";
//
        $objCarrier = class_carrier::getInstance();
//
//echo "|loaded.                                                                        |\n";
//echo "+-------------------------------------------------------------------------------+\n\n";
//
//



$objSystemtask = new class_systemtask_workflows();
$objSystemtask->executeTask();




//echo "\n\n";
//echo "+-------------------------------------------------------------------------------+\n";
//echo "| (c) www.kajona.de                                                             |\n";
//echo "+-------------------------------------------------------------------------------+\n";
//echo "</pre>";



