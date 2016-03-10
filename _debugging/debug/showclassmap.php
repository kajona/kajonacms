<?php
/*"******************************************************************************************************
*   (c) 2004-2006 by MulchProductions, www.mulchprod.de                                                 *
*   (c) 2007-2015 by Kajona, www.kajona.de                                                              *
*       Published under the GNU LGPL v2.1, see /system/licence_lgpl.txt                                 *
*-------------------------------------------------------------------------------------------------------*
*   $Id$                                     *
********************************************************************************************************/

namespace Kajona\Debugging\Debug;

echo "+-------------------------------------------------------------------------------+\n";
echo "| Kajona Debug Subsystem                                                        |\n";
echo "|                                                                               |\n";
echo "| Class map                                                                     |\n";
echo "|                                                                               |\n";
echo "+-------------------------------------------------------------------------------+\n";

use Kajona\System\System\BootstrapCache;


foreach (BootstrapCache::getInstance()->getCacheContent(BootstrapCache::CACHE_CLASSES) as $strClass => $strOneFile) {
    echo str_pad($strClass, 128, ' ') . ' => ' . $strOneFile . "\n";
}
