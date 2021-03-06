<?php

namespace Kajona\System\Tests;

use Kajona\System\System\Resourceloader;
use Kajona\System\System\StringUtil;

class JsFilesTest extends Testbase
{

    public function testJsFiles()
    {


        $arrJsFiles = $this->getJsFiles();

        foreach ($arrJsFiles as $strOneFile => $strFilename) {

            $strFile = file_get_contents($strOneFile);

            $arrMatches = array();
//            if (preg_match_all("/console\.([a-zA-Z]*)\(/i", $strFile, $arrMatches)) {
//                echo $strOneFile . ": " . $arrMatches[0][0] . "\n";
//                $this->assertTrue(false, "console logging found " . $strOneFile . ": " . $arrMatches[0][0] . "\n");
//            }

            if (preg_match_all("/debugger;/i", $strFile, $arrMatches)) {
                echo $strOneFile . ": " . $arrMatches[0][0] . "\n";
                $this->assertTrue(false, "debugger breakpoint found " . $strOneFile . ": " . $arrMatches[0][0] . "\n");
            }
        }
    }


    private function getJsFiles()
    {


        $arrFiles = array();
        $arrFiles = array_merge($arrFiles, Resourceloader::getInstance()->getFolderContent("/scripts", array(), true));

        $arrReturn = array();
        foreach($arrFiles as $strPath => $strFilename) {
            if(StringUtil::endsWith($strFilename, ".js")) {
                $arrReturn[$strPath] = $strFilename;
            } elseif (is_dir($strPath)) {
                $arrReturn = array_merge($arrReturn, Resourceloader::getInstance()->getFolderContent("/scripts/".basename($strPath), array(".js")));
            }

        }

        return $arrReturn;

    }
}

