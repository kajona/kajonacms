<?php
/*"******************************************************************************************************
*   (c) 2004-2006 by MulchProductions, www.mulchprod.de                                                 *
*   (c) 2007-2010 by Kajona, www.kajona.de                                                              *
*       Published under the GNU LGPL v2.1, see /system/licence_lgpl.txt                                 *
*-------------------------------------------------------------------------------------------------------*
*	$Id$                                     *
********************************************************************************************************/



require_once("../system/includes.php");



echo "<pre>\n";
echo "+-------------------------------------------------------------------------------+\n";
echo "| Kajona Debug Subsystem                                                        |\n";
echo "|                                                                               |\n";
echo "| Autotest                                                                      |\n";
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
for ($i = 0; $i < ob_get_level(); $i++) { ob_end_flush(); }
    ob_implicit_flush(1);

echo "Please note: Running tests manually does not replace running them via\n";
echo "             the autotest-framework!\n";
echo "             Some tests require a clean install to run properly.\n\n";
echo "searching tests available...\n";

$objFilesystem = new class_filesystem();
$arrFiles = $objFilesystem->getFilelist("/tests/", array(".php"));
echo "found ".count($arrFiles)." test(s)\n\n";

echo "<form method=\"post\">";
echo "Test to run:\n";
echo "<select name=\"testname\" type=\"dropdown\">";
foreach ($arrFiles as $strOneFile)
    echo "<option id=\"".$strOneFile."\" ".(getPost("testname") == $strOneFile ? "selected" : "")." >".$strOneFile."</option>";
echo "</select>";
echo "<input type=\"hidden\" name=\"dotest\" value=\"1\" />";
echo "<input type=\"submit\" value=\"Run test\" />";
echo "</form>";



if(issetPost("dotest")) {
    $intStart = time();

	$strFilename = getPost("testname");

    if(substr($strFilename, 0, 5) == "test_" && substr($strFilename, -4) == ".php") {
        echo " \n\nfound test-script ".$strFilename." \n";
        include_once _realpath_."/tests/".$strFilename;
        $strClassName = "class_".str_replace(".php", "", $strFilename);
        $objTest = new $strClassName();
        if($objTest instanceof interface_testable) {
            echo " invoking test() on instance of ".$strClassName."\n\n\n\n";
            $objTest->test();
        }

        class_assertions::printStatistics();
        echo "time needed: ".round(((time()-$intStart)/60), 3)." min\n\n\n";

    }

		
}


echo "\n\n";
echo "+-------------------------------------------------------------------------------+\n";
echo "| (c) www.kajona.de                                                             |\n";
echo "+-------------------------------------------------------------------------------+\n";
echo "</pre>";



// --- tools needed to run tests ------------------------------------------------------------------------
interface interface_testable {
    function test();
}

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

    public static function printStatistics() {
        echo "\n\n-------------------------------------------------------\n\n";
        echo "test-statistics:\n";
        echo "nr of tests failed: ".class_assertions::$nrOfFailures."\n";
        echo "nr of tests succeeded: ".class_assertions::$nrOfSuccesses."\n";
        echo "\n\n-------------------------------------------------------\n";
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
        
        $strLevel = "";
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



?>