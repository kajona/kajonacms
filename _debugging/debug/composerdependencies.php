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
echo "+-------------------------------------------------------------------------------+\n";

echo "Shows all used composer dependencies: \n\n";

$objCoreDirs = new \DirectoryIterator(_realpath_);
foreach ($objCoreDirs as $objCoreDir) {
    if ($objCoreDir->isDir() && substr($objCoreDir->getFilename(), 0, 4) == 'core') {
        $objModuleDirs = new \DirectoryIterator($objCoreDir->getRealPath());
        foreach ($objModuleDirs as $objDir) {
            if (substr($objDir->getFilename(), 0, 7) == 'module_') {
                $composerFile = $objDir->getRealPath().'/composer.json';
                if (is_file($composerFile)) {
                    $arrComposer = json_decode(file_get_contents($composerFile), true);
                    echo '<b>' . $objDir->getFileName(). '</b>' . "\n";
                    if (isset($arrComposer["require"])) {
                        foreach ($arrComposer["require"] as $strPackageName => $strVersion) {
                            echo str_pad($strPackageName, 40, " ") . " => " . $strVersion . "\n";
                        }
                    }
                    if (isset($arrComposer["require-dev"])) {
                        foreach ($arrComposer["require-dev"] as $strPackageName => $strVersion) {
                            echo str_pad($strPackageName, 40, " ") . " => " . $strVersion . "\n";
                        }
                    }
                }
            }
        }
    }
}
