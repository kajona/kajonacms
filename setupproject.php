<?php
/*"******************************************************************************************************
*   (c) 2007-2012 by Kajona, www.kajona.de                                                              *
*       Published under the GNU LGPL v2.1, see /system/licence_lgpl.txt                                 *
*-------------------------------------------------------------------------------------------------------*
*	$Id: class_config.php 4235 2011-11-20 19:10:07Z sidler $                                             *
********************************************************************************************************/


class class_project_setup {
    public static function setUp() {

        echo "<b>Kajona V4 project setup.</b>\nCreates the folder-structure required to build a new project.\n\n";

        $strCurFolder = __DIR__;

        echo "core-path: ".$strCurFolder.", folder found: ".substr($strCurFolder, -4)."\n";

        if(substr($strCurFolder, -4) != "core") {
            echo "current folder must be named core!";
            return;
        }




        echo "loading core...\n\n";
        include __DIR__."/bootstrap.php";

        $arrModules = scandir(_corepath_);

        $arrModules = array_filter($arrModules, function($strValue) {
            return preg_match("/(module|element|_)+.*/i", $strValue);
        });


        self::checkDir("/admin");
        self::createAdminRedirect();

        self::checkDir("/project");
        self::checkDir("/project/log");
        self::checkDir("/project/dbdumps");
        self::checkDir("/project/lang");
        self::checkDir("/project/system");
        self::checkDir("/project/system/config");
        self::checkDir("/project/system/classes");
        self::checkDir("/project/portal");
        self::checkDir("/project/temp");
        self::checkDir("/templates");
        self::checkDir("/files");
        self::checkDir("/files/cache");
        self::checkDir("/files/downloads");
        self::checkDir("/files/images");
        self::checkDir("/files/public");

        self::checkDir("/templates/default");
        self::checkDir("/templates/default/js");
        self::checkDir("/templates/default/css");
        self::checkDir("/templates/default/tpl");
        self::checkDir("/templates/default/pics");

        self::createClassloaderConfig();


        echo "searching for files on root-path...\n";
        foreach($arrModules as $strSingleModule) {
            if(!is_dir(_corepath_."/".$strSingleModule))
                continue;

            $arrContent = scandir(_corepath_."/".$strSingleModule);
            foreach($arrContent as $strSingleEntry) {
                if(substr($strSingleEntry, -5) == ".root") {
                    echo "copy ".$strSingleEntry." to "._realpath_."/".substr($strSingleEntry, 0, -5)."\n";
                    copy(_corepath_."/".$strSingleModule."/".$strSingleEntry, _realpath_."/".substr($strSingleEntry, 0, -5));
                }
            }
        }


        echo "\n<b>Kajona V4 template setup.</b>\nCreates the default-template-pack required to render pages.\n";
        echo "Files already existing are NOT overwritten.\n";


        foreach($arrModules as $strSingleModule) {
            if(is_dir(_corepath_."/".$strSingleModule."/templates"))
                self::copyFolder(_corepath_."/".$strSingleModule."/templates", _realpath_."/templates");

            if(is_dir(_corepath_."/".$strSingleModule."/files"))
                self::copyFolder(_corepath_."/".$strSingleModule."/files", _realpath_."/files");
        }


        echo "\n<b>Kajona V4 htaccess setup</b>\n";
        self::createAllowHtaccess("/files/.htaccess");
        self::createAllowHtaccess("/templates/.htaccess");

        self::createDenyHtaccess("/project/.htaccess");

    }


    private static function createClassloaderConfig() {
        $strContent = <<<XML
<?xml version="1.0" encoding="UTF-8"?>
<!--
  Kajona V4 class-loader configuration.

   By default, the Kajona class-loader scans the core-folder for classes not yet known to the class-loader.
   In some cases, it might get necessary to add new classes or overwrite existing classes to your projects'
   needs. Therefore, this file may define classes explicitly.

   Example: If you want to provide your own implementation for class_session, add an entry as following:

    <classloader>
        <class>
            <name>class_session</name>
            <path>/project/system/classes/class_session_test.php</path>
        </class>
    </classloader>

-->
<classloader
        xmlns:xsi="http://www.w3.org/2001/XMLSchema-instance"
        xsi:noNamespaceSchemaLocation="http://apidocs.kajona.de/xsd/classloader.xsd"
        >
</classloader>
XML;

        file_put_contents(_realpath_."/project/system/classes/classloader.xml", $strContent);
    }


    private static function createAdminRedirect() {
        $strContent  = "<html>\n";
        $strContent .= " <head>\n";
        $strContent .= "  <title>Loading</title>\n";
        $strContent .= "  <meta http-equiv='refresh' content='0; URL=../index.php?admin=1'>\n";
        $strContent .= " </head>\n";
        $strContent .= " <body>Loading...</body>\n";
        $strContent .= "</html>\n";

        file_put_contents(_realpath_."/admin/index.html", $strContent);
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

    private static function createDenyHtaccess($strPath) {
        echo "placing deny htaccess in ".$strPath."\n";
        $strContent = "\n\nDeny from all\n\n";
        file_put_contents(_realpath_.$strPath, $strContent);
    }

    private static function createAllowHtaccess($strPath) {
        echo "placing allow htaccess in ".$strPath."\n";
        $strContent = "\n\nAllow from all\n\n";
        file_put_contents(_realpath_.$strPath, $strContent);
    }
}

echo "<pre>";

class_project_setup::setUp();

echo "</pre>";
