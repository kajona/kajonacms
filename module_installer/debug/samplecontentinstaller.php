<?php
/*"******************************************************************************************************
*   (c) 2004-2006 by MulchProductions, www.mulchprod.de                                                 *
*   (c) 2007-2016 by Kajona, www.kajona.de                                                              *
*       Published under the GNU LGPL v2.1, see /system/licence_lgpl.txt                                 *
********************************************************************************************************/

namespace Kajona\Installer\Debug;

use Kajona\Installer\System\SamplecontentInstallerHelper;

echo "+-------------------------------------------------------------------------------+\n";
echo "| Kajona Debug Subsystem                                                        |\n";
echo "|                                                                               |\n";
echo "| Samplecontent installer                                                       |\n";
echo "|                                                                               |\n";
echo "+-------------------------------------------------------------------------------+\n";

if (function_exists("apache_setenv")) {
    @apache_setenv('no-gzip', 1);
}
@ini_set('zlib.output_compression', 0);
@ini_set('implicit_flush', 1);
for ($i = 0; $i < ob_get_level(); $i++) {
    ob_end_flush();
}
ob_implicit_flush(1);

//search for installers available
$arrInstaller = SamplecontentInstallerHelper::getSamplecontentInstallers();

echo "found ".count($arrInstaller)." installers(s)\n\n";

echo "<form method=\"post\">";
echo "Test to run:\n";
foreach ($arrInstaller as $objOneInstaller) {
    echo "<input type=\"checkbox\" id=\"installer[".get_class($objOneInstaller)."]\" name=\"installer[".get_class($objOneInstaller)."]\" /><label for=\"installer[".get_class($objOneInstaller)."]\">".get_class($objOneInstaller)."</label><br />";
}
echo "<input type=\"hidden\" name=\"debugfile\" value=\"autotest.php\" />";
echo "<input type=\"hidden\" name=\"doinstall\" value=\"1\" />";
echo "<input type=\"submit\" value=\"Run Installer\" />";
echo "</form>";


if (issetPost("doinstall")) {
    $intStart = time();

    foreach (getPost("installer") as $strClassname => $strChecked) {
        $objInstaller = new $strClassname;

        if ($objInstaller instanceof \Kajona\System\System\SamplecontentInstallerInterface) {
            echo SamplecontentInstallerHelper::install($objInstaller);
        }
    }

    echo "time needed: ".round(((time() - $intStart) / 60), 3)." min\n\n\n";

}


echo "\n\n";
echo "+-------------------------------------------------------------------------------+\n";
echo "| (c) www.kajona.de                                                             |\n";
echo "+-------------------------------------------------------------------------------+\n";

