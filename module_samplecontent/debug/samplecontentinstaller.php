<?php
/*"******************************************************************************************************
*   (c) 2004-2006 by MulchProductions, www.mulchprod.de                                                 *
*   (c) 2007-2015 by Kajona, www.kajona.de                                                              *
*       Published under the GNU LGPL v2.1, see /system/licence_lgpl.txt                                 *
********************************************************************************************************/

echo "+-------------------------------------------------------------------------------+\n";
echo "| Kajona Debug Subsystem                                                        |\n";
echo "|                                                                               |\n";
echo "| Samplecontent installer                                                       |\n";
echo "|                                                                               |\n";
echo "+-------------------------------------------------------------------------------+\n";

if(function_exists("apache_setenv"))
    @apache_setenv('no-gzip', 1);
@ini_set('zlib.output_compression', 0);
@ini_set('implicit_flush', 1);
for ($i = 0; $i < ob_get_level(); $i++) {
    ob_end_flush();
}
ob_implicit_flush(1);

//search for installers available
$arrInstaller = \Kajona\System\System\Resourceloader::getInstance()->getFolderContent("/installer", array(".php"), false, function($strFile) {
   return strpos($strFile, "installer_sc_") !== false;
});

asort($arrInstaller);

echo "found ".count($arrInstaller)." installers(s)\n\n";

echo "<form method=\"post\">";
echo "Test to run:\n";
foreach ($arrInstaller as $strOneFile)
    echo "<input type=\"checkbox\" id=\"installer[".$strOneFile."]\" name=\"installer[".$strOneFile."]\" ".(getPost("installername") == $strOneFile ? "selected" : "")." /><label for=\"installer[".$strOneFile."]\">".$strOneFile."</label><br />";
echo "<input type=\"hidden\" name=\"debugfile\" value=\"autotest.php\" />";
echo "<input type=\"hidden\" name=\"doinstall\" value=\"1\" />";
echo "<input type=\"submit\" value=\"Run Installer\" />";
echo "</form>";



if(issetPost("doinstall")) {
    $intStart = time();

    $arrFiles = \Kajona\System\System\Resourceloader::getInstance()->getFolderContent("/installer", array(".php"), false, function($strFile) {
        return strpos($strFile, "installer_sc_") !== false && substr($strFile, -4) == ".php";
    });

    foreach(getPost("installer") as $strFilename => $strValue) {
        $strSearched = array_search($strFilename, $arrFiles);

        if($strSearched !== false) {
            echo " \n\nfound installer ".$strFilename." \n";
            include_once _realpath_.$strSearched;

            $strName = $strClass = "class_".str_replace(".php", "", $strFilename);
            $objInstaller = new $strName();
            $objLang = new \Kajona\System\System\LanguagesLanguage();

            if($objInstaller instanceof \Kajona\System\System\SamplecontentInstallerInterface ) {
                $strModule = $objInstaller->getCorrespondingModule();
                echo "Module ".$strModule."...\n";
                $objModule = \Kajona\System\System\SystemModule::getModuleByName($strModule);
                if($objModule == null) {
                    echo "\t... not installed!\n";
                }
                else {
                    echo "\t... installed.\n";
                    $objInstaller->setObjDb(\Kajona\System\System\Carrier::getInstance()->getObjDB());
                    $objInstaller->setStrContentlanguage($objLang->getStrAdminLanguageToWorkOn());
                    echo $objInstaller->install();
                }
            }
        }


        echo "time needed: ".round(((time()-$intStart)/60), 3)." min\n\n\n";
    }


}





echo "\n\n";
echo "+-------------------------------------------------------------------------------+\n";
echo "| (c) www.kajona.de                                                             |\n";
echo "+-------------------------------------------------------------------------------+\n";

