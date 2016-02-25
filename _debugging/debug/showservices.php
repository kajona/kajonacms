<?php
/*"******************************************************************************************************
*   (c) 2004-2006 by MulchProductions, www.mulchprod.de                                                 *
*   (c) 2007-2015 by Kajona, www.kajona.de                                                              *
*       Published under the GNU LGPL v2.1, see /system/licence_lgpl.txt                                 *
*-------------------------------------------------------------------------------------------------------*
*   $Id$                                     *
********************************************************************************************************/
namespace Kajona\Debugging\Debug;

use Kajona\System\System\Carrier;

echo "+-------------------------------------------------------------------------------+\n";
echo "| Kajona Debug Subsystem                                                        |\n";
echo "|                                                                               |\n";
echo "+-------------------------------------------------------------------------------+\n";

echo "Shows all available service definitions which can be used in a controller or workflow: \n";

$objContainer = Carrier::getInstance()->getContainer();
$arrServices = $objContainer->keys();

foreach ($arrServices as $strService) {
    echo " - " . $strService . " => " . get_class($objContainer[$strService]) . "\n";
}


