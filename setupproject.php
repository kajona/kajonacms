<?php
/*"******************************************************************************************************
*   (c) 2007-2011 by Kajona, www.kajona.de                                                              *
*       Published under the GNU LGPL v2.1, see /system/licence_lgpl.txt                                 *
*-------------------------------------------------------------------------------------------------------*
*	$Id: class_config.php 4235 2011-11-20 19:10:07Z sidler $                                             *
********************************************************************************************************/


class class_project_setup {
    public static function setUp() {

        $strCurFolder = dirname(__FILE__);

        echo "core-path: ".$strCurFolder.", ".substr($strCurFolder, -4)."\n";

        if(substr($strCurFolder, -4) != "core") {
            echo "current folder must be named core!";
            return;
        }


        echo "loading core...\n\n";
        include "./bootstrap.php";

        self::checkDir("/project");
        self::checkDir("/project/log");
        self::checkDir("/project/dbdumps");
        self::checkDir("/project/lang");
        self::checkDir("/project/system");
        self::checkDir("/project/system/config");
        self::checkDir("/project/portal");
        self::checkDir("/templates");
        self::checkDir("/files");
        self::checkDir("/files/cache");
        self::checkDir("/files/downloads");
        self::checkDir("/files/images");
        self::checkDir("/files/public");

        self::checkDir("/default");
        self::checkDir("/default/js");
        self::checkDir("/default/css");
        self::checkDir("/default/tpl");
        self::checkDir("/default/pics");



        echo "copy index.php.root to index.php\n";
        copy(_corepath_."/index.php.root", _realpath_."/index.php");

        echo "copy xml.php.root to xml.php\n";
        copy(_corepath_."/xml.php.root", _realpath_."/xml.php");

        echo "copy image.php.root to image.php\n";
        copy(_corepath_."/image.php.root", _realpath_."/image.php");

        echo "copy installer.php.root to installer.php\n";
        copy(_corepath_."/installer.php.root", _realpath_."/installer.php");


        echo "Kajona V4 template setup.\nCreates the default-template-pack required to render pages.\n";
        echo "Files already existing are NOT overwritten.\n";

        $arrModules = scandir(_corepath_);

        $arrModules = array_filter($arrModules, function($strValue) {
            return preg_match("/(module|element|_)+.*/i", $strValue);
        });

        foreach($arrModules as $strSingleModule) {
            if(is_dir(_corepath_."/".$strSingleModule."/templates"))
                self::copyFolder(_corepath_."/".$strSingleModule."/templates", _realpath_._templatepath_."");

            if(is_dir(_corepath_."/".$strSingleModule."/files"))
                self::copyFolder(_corepath_."/".$strSingleModule."/files", _realpath_._filespath_."");
        }

    }




    private static function checkDir($strFolder) {
        echo "checking dir "._realpath_.$strFolder."\n";
        if(!is_dir(_realpath_.$strFolder)) {
            mkdir(_realpath_.$strFolder, 0777);
            echo " \t\t... directory created\n";
        }
        else {
            echo " \t\t... already existing.\n";
        }
    }


    private static function copyFolder($strSourceFolder, $strTargetFolder) {
        $arrEntries = scandir($strSourceFolder);
        foreach($arrEntries as $strOneEntry) {
            if($strOneEntry == "." || $strOneEntry == ".." || $strOneEntry == ".svn")
                continue;

            if(is_file($strSourceFolder."/".$strOneEntry) && !is_file($strTargetFolder."/".$strOneEntry)) {
                echo "copying file ".$strSourceFolder."/".$strOneEntry." to ".$strTargetFolder."/".$strOneEntry."\n";
                copy($strSourceFolder."/".$strOneEntry, $strTargetFolder."/".$strOneEntry);
                chmod($strTargetFolder."/".$strOneEntry, 0777);
            }
            else if(is_dir($strSourceFolder."/".$strOneEntry)) {
                if(!is_dir($strTargetFolder."/".$strOneEntry))
                    mkdir($strTargetFolder."/".$strOneEntry, 0777);

                self::copyFolder($strSourceFolder."/".$strOneEntry, $strTargetFolder."/".$strOneEntry);
            }
        }
    }
}

echo "<pre>";
echo "Kajona V4 project setup.\nCreates the folder-structure required to build a new project.\n\n";
class_project_setup::setUp();

echo "Project set up.";
echo "</pre>";
