<?php
/*"******************************************************************************************************
*   (c) 2004-2006 by MulchProductions, www.mulchprod.de                                                 *
*   (c) 2007-2013 by Kajona, www.kajona.de                                                              *
*       Published under the GNU LGPL v2.1, see /system/licence_lgpl.txt                                 *
*-------------------------------------------------------------------------------------------------------*
*	$Id: autotest.php 5409 2012-12-30 13:09:07Z sidler $                                     *
********************************************************************************************************/

echo "+-------------------------------------------------------------------------------+\n";
echo "| Kajona Debug Subsystem                                                        |\n";
echo "|                                                                               |\n";
echo "| Samplecontent installer                                                       |\n";
echo "|                                                                               |\n";
echo "+-------------------------------------------------------------------------------+\n";
echo "|loading system kernel...                                                       |\n";

$objCarrier = class_carrier::getInstance();

echo "|loaded.                                                                        |\n";
echo "+-------------------------------------------------------------------------------+\n\n";

if(function_exists("apache_setenv"))
    @apache_setenv('no-gzip', 1);
@ini_set('zlib.output_compression', 0);
@ini_set('implicit_flush', 1);
for ($i = 0; $i < ob_get_level(); $i++) {
    ob_end_flush();
}
ob_implicit_flush(1);

//search for installers available
$arrInstaller = class_resourceloader::getInstance()->getFolderContent("/installer", array(".php"), false, function($strFile) {
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

    $arrFiles = class_resourceloader::getInstance()->getFolderContent("/installer", array(".php"), false, function($strFile) {
        return strpos($strFile, "installer_sc_") !== false && substr($strFile, -4) == ".php";
    });

    foreach(getPost("installer") as $strFilename => $strValue) {
        $strSearched = array_search($strFilename, $arrFiles);

        if($strSearched !== false) {
            echo " \n\nfound installer ".$strFilename." \n";
            include_once _realpath_.$strSearched;

            $strName = $strClass = "class_".str_replace(".php", "", $strFilename);
            $objInstaller = new $strName();
            $objLang = new class_module_languages_language();

            if($objInstaller instanceof interface_sc_installer ) {
                $strModule = $objInstaller->getCorrespondingModule();
                echo "Module ".$strModule."...\n";
                $objModule = class_module_system_module::getModuleByName($strModule);
                if($objModule == null) {
                    echo "\t... not installed!\n";
                }
                else {
                    echo "\t... installed.\n";
                    $objInstaller->setObjDb(class_carrier::getInstance()->getObjDB());
                    $objInstaller->setStrContentlanguage($objLang->getStrAdminLanguageToWorkOn());
                    echo $objInstaller->install();
                }
            }
        }


        echo "time needed: ".round(((time()-$intStart)/60), 3)." min\n\n\n";
    }


}

function get_php_classes($php_code) {
    $classes = array();
    $tokens = token_get_all($php_code);
    $count = count($tokens);
    for ($i = 2; $i < $count; $i++) {
        if (   $tokens[$i - 2][0] == T_CLASS
            && $tokens[$i - 1][0] == T_WHITESPACE
            && $tokens[$i][0] == T_STRING
        ) {

            $class_name = $tokens[$i][1];
            $classes[] = $class_name;
        }
    }
    return $classes;
}



echo "\n\n";
echo "+-------------------------------------------------------------------------------+\n";
echo "| (c) www.kajona.de                                                             |\n";
echo "+-------------------------------------------------------------------------------+\n";


// --- tools needed to run tests ------------------------------------------------------------------------


class class_assertions {
    private static $nrOfFailures = 0;
    private static $nrOfSuccesses = 0;

    public static function assertEqual($value1, $value2, $strCallingMethod) {
        if($value1 == $value2) {
            class_testLogger::getInstance()->addLogRow("assert equal succeeded: ".$strCallingMethod, class_testLogger::$levelInfo);
            class_assertions::$nrOfSuccesses++;
            return true;
        }
        else {
            class_testLogger::getInstance()->addLogRow("assert equal failed: ".$strCallingMethod."\tfound ".$value1." expected ".$value2, class_testLogger::$levelError);
            class_assertions::$nrOfFailures++;
            return false;
        }
    }

    public static function assertNotEqual($value1, $value2, $strCallingMethod) {
        if($value1 != $value2) {
            class_testLogger::getInstance()->addLogRow("assert not equal succeeded: ".$strCallingMethod, class_testLogger::$levelInfo);
            class_assertions::$nrOfSuccesses++;
            return true;
        }
        else {
            class_testLogger::getInstance()->addLogRow("assert not equal failed: ".$strCallingMethod."\tfound ".$value1." expected to differ ".$value2, class_testLogger::$levelError);
            class_assertions::$nrOfFailures++;
            return false;
        }
    }

    public static function assertTrue($value1, $strCallingMethod) {
        if($value1 === true) {
            class_testLogger::getInstance()->addLogRow("assert true succeeded: ".$strCallingMethod, class_testLogger::$levelInfo);
            class_assertions::$nrOfSuccesses++;
            return true;
        }
        else {
            class_testLogger::getInstance()->addLogRow("assert true failed: ".$strCallingMethod."\tfound ".$value1." expected true", class_testLogger::$levelError);
            class_assertions::$nrOfFailures++;
            return false;
        }
    }

    public static function assertFalse($value1, $strCallingMethod) {
        if($value1 === false) {
            class_testLogger::getInstance()->addLogRow("assert false succeeded: ".$strCallingMethod, class_testLogger::$levelInfo);
            class_assertions::$nrOfSuccesses++;
            return true;
        }
        else {
            class_testLogger::getInstance()->addLogRow("assert false failed: ".$strCallingMethod." -> found ".$value1." expected false", class_testLogger::$levelError);
            class_assertions::$nrOfFailures++;
            return false;
        }
    }

    public static function getStatistics() {
        $strReturn  = "\n\n-------------------------------------------------------\n\n";
        $strReturn .= "test-statistics:\n";
        $strReturn .= "nr of tests failed: ".class_assertions::$nrOfFailures."\n";
        $strReturn .= "nr of tests succeeded: ".class_assertions::$nrOfSuccesses."\n";
        $strReturn .= "\n\n-------------------------------------------------------\n";

        return $strReturn;
    }

    public static function printStatistics() {
        echo self::getStatistics();
    }


}

final class class_testLogger {
    public static $levelError = 0;
    public static $levelWarning = 1;
    public static $levelInfo = 2;
    private static $objInstance = null;
    private $intLogLevel = 0;

    private function __construct() {
        $this->intLogLevel = 2;
    }

    public static function getInstance() {
        if (class_testLogger::$objInstance == null)
            class_testLogger::$objInstance = new class_testLogger();
        return self::$objInstance;
    }

    public function addLogRow($strMessage, $intLevel) {
        if($this->intLogLevel == 0)
            return;
        if($intLevel == self::$levelError && $this->intLogLevel < 1)
            return;
        if($intLevel == self::$levelWarning && $this->intLogLevel < 2)
            return;
        if($intLevel == self::$levelInfo && $this->intLogLevel < 3)
            return;

        if($intLevel == self::$levelError)
            $strMessage = "<span style=\"color: red;\">ERROR &gt;&gt;&gt;</span> ".$strMessage." <span style=\"color: red;\">&lt;&lt;&lt;</span>";
        elseif ($intLevel == self::$levelInfo)
            $strMessage = "INFO ".$strMessage;
        elseif ($intLevel == self::$levelWarning)
            $strMessage = "WARNING ".$strMessage;

        echo("\t".$strMessage."\n");
        flush();
    }

}

class PHPUnit_Framework_TestCase {


    public function kajonaTestTrigger() {
        //setUp
        $this->setUp();

        //loop test methods
        $objReflection = new ReflectionClass($this);
        $arrMethods = $objReflection->getMethods();

        foreach($arrMethods as $objOneMethod) {
            if(uniStrpos($objOneMethod->getName(), "test") !== false) {
                echo "calling ".$objOneMethod->getName()."...\n";
                $objOneMethod->invoke($this);
            }
        }

        //tearDown
        $this->tearDown();
    }

    public function assertTrue($mixedVal, $strComment = "") {
        class_assertions::assertTrue($mixedVal, $strComment);
    }

    public function assertNull($mixedVal, $strComment = "") {
        class_assertions::assertTrue($mixedVal === null, $strComment);
    }

    public function assertNotNull($mixedVal, $strComment = "") {
        class_assertions::assertTrue($mixedVal !== null, $strComment);
    }

    public function assertEquals($mixedVal1, $mixedVal2, $strComment = "") {
        class_assertions::assertEqual($mixedVal1, $mixedVal2, $strComment);
    }

    public function assertNotEquals($mixedVal1, $mixedVal2, $strComment = "") {
        class_assertions::assertNotEqual($mixedVal1, $mixedVal2, $strComment);
    }

    public function assertFileExists($strFile, $strComment = "") {
        class_assertions::assertTrue(is_file($strFile), $strComment);
    }

    protected function setUp() {
    }

    protected function tearDown() {
    }
}


