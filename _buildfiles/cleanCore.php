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



        //trigger cleanups if required, e.g. since a module is excluded
        echo "\n\nSearching for excluded modules at ".__DIR__."/".$this->strProjectPath."/project/system/config/excludedmodules.php"."\n\n";
        if(file_exists(__DIR__."/".$this->strProjectPath."/project/system/config/excludedmodules.php")) {
            $arrExcludedModules = array();
            include(__DIR__."/".$this->strProjectPath."/project/system/config/excludedmodules.php");
            foreach($arrExcludedModules as $strCore => $arrIgnoredModules) {
                foreach($arrIgnoredModules as $strOneIgnoredModule) {
                    if(file_exists(__DIR__."/".$this->strProjectPath."/".$strCore."/".$strOneIgnoredModule)) {
                        echo " Deleting ".__DIR__."/".$this->strProjectPath."/".$strCore."/".$strOneIgnoredModule."\n";
                        $this->rrmdir(__DIR__."/".$this->strProjectPath."/".$strCore."/".$strOneIgnoredModule);
                    }
                }
            }
        }


    }

    /**
     * @param $dir
     * @see http://www.php.net/manual/de/function.rmdir.php#98622
     */
    private function rrmdir($dir) {
        if (is_dir($dir)) {
            $objects = scandir($dir);
            foreach ($objects as $object) {
                if ($object != "." && $object != "..") {
                    if (filetype($dir."/".$object) == "dir") $this->rrmdir($dir."/".$object); else unlink($dir."/".$object);
                }
            }
            reset($objects);
            rmdir($dir);
        }
    }
}

$objCoreCleaner = new CleanCoreHelper();
$objCoreCleaner->strProjectPath = $argv[1];
$objCoreCleaner->main();

