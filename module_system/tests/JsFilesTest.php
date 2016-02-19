<?php

namespace Kajona\System\Tests;
require_once __DIR__."/../../../core/module_system/system/Testbase.php";
use Kajona\System\System\Resourceloader;
use Kajona\System\System\Testbase;

class JsFilesTest extends Testbase  {

    public function testJsFiles() {



        $arrJsFiles = $this->getJsFiles();// class_resourceloader::getInstance()->getFolderContent("", array(".js"), true);

        foreach($arrJsFiles as $strOneFile => $strFilename) {

            $strFile = file_get_contents($strOneFile);

            $arrMatches = array();
            if(preg_match_all("/console\.([a-zA-Z]*)\(/i", $strFile, $arrMatches)) {
                echo $strOneFile.": ".$arrMatches[0][0]."\n";
                $this->assertTrue(false, "console logging found ".$strOneFile.": ".$arrMatches[0][0]."\n");
            }

            if(preg_match_all("/debugger;/i", $strFile, $arrMatches)) {
                echo $strOneFile.": ".$arrMatches[0][0]."\n";
                $this->assertTrue(false, "debugger breakpoint found ".$strOneFile.": ".$arrMatches[0][0]."\n");
            }
        }
    }



    private function getJsFiles() {


        $arrFiles = array();
        $arrFiles = array_merge($arrFiles, Resourceloader::getInstance()->getFolderContent("/admin/scripts", array(".js")));
        $arrFiles = array_merge($arrFiles, Resourceloader::getInstance()->getFolderContent("/system/scripts", array(".js")));
        $arrFiles = array_merge($arrFiles, Resourceloader::getInstance()->getFolderContent("/portal/scripts", array(".js")));
        return $arrFiles;

    }
}

