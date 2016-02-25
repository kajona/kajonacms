#!/usr/bin/php
<?php

class CleanCoreHelper {

    public $strProjectPath = "";


    public function main() {

        echo "\n\n";
        echo "Kajona Clean CoreHelper\n";
        echo " Params:\n";
        echo "   projectPath: ".$this->strProjectPath."\n";
        echo "\n\n";

        $arrCores = array();
        foreach(scandir(__DIR__."/".$this->strProjectPath) as $strRootFolder) {
            if(strpos($strRootFolder, "core") === false)
                continue;
            $arrCores[] = $strRootFolder;
        }


        //trigger cleanups if required, e.g. since a module is excluded or an explicit include list is present
        echo "\n\nSearching for excluded modules at ".__DIR__."/".$this->strProjectPath."/project/packageconfig.php"."\n\n";
        if(file_exists(__DIR__."/".$this->strProjectPath."/project/packageconfig.php")) {
            $arrIncludedModules = array();
            $arrExcludedModules = array();
            include(__DIR__."/".$this->strProjectPath."/project/packageconfig.php");

            foreach($arrCores as $strCoreFolder) {
                foreach(scandir(__DIR__."/".$this->strProjectPath."/".$strCoreFolder) as $strOneModule) {

                    if(preg_match("/^(module|element|_)+.*/i", $strOneModule)) {

                        //skip excluded modules
                        if(isset($arrExcludedModules[$strCoreFolder]) && in_array($strOneModule, $arrExcludedModules[$strCoreFolder])) {
                            echo " Deleting ".__DIR__."/".$this->strProjectPath."/".$strCoreFolder."/".$strOneModule."\n";
                            $this->rrmdir(__DIR__."/".$this->strProjectPath."/".$strCoreFolder."/".$strOneModule);
                            continue;
                        }

                        //skip module if not marked as to be included
                        if(count($arrIncludedModules) > 0 && (isset($arrIncludedModules[$strCoreFolder]) && !in_array($strOneModule, $arrIncludedModules[$strCoreFolder]))) {
                            echo " Deleting ".__DIR__."/".$this->strProjectPath."/".$strCoreFolder."/".$strOneModule."\n";
                            $this->rrmdir(__DIR__."/".$this->strProjectPath."/".$strCoreFolder."/".$strOneModule);
                            continue;
                        }

                    }


                }
            }


        }


    }

    /**
     * @param $strDir
     *
     * @see http://www.php.net/manual/de/function.rmdir.php#98622
     */
    private function rrmdir($strDir) {
        if (is_dir($strDir)) {
            $arrObjects = scandir($strDir);
            foreach ($arrObjects as $objObject) {
                if ($objObject != "." && $objObject != "..") {
                    if (filetype($strDir."/".$objObject) == "dir") $this->rrmdir($strDir."/".$objObject); else unlink($strDir."/".$objObject);
                }
            }
            reset($arrObjects);
            rmdir($strDir);
        }
    }
}

$objCoreCleaner = new CleanCoreHelper();
$objCoreCleaner->strProjectPath = $argv[1];
$objCoreCleaner->main();

