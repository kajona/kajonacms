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
        self::checkDir("/project/lang/admin");
        self::checkDir("/project/lang/portal"); //FIXME: remove after merge of portal and lang-files
        self::checkDir("/project/system");
        self::checkDir("/project/system/config");
        self::checkDir("/templates");


        echo "copy index.php.root to index.php\n";
        copy(_corepath_."/index.php.root", _realpath_."/index.php");

        echo "copy xml.php.root to index.php\n";
        copy(_corepath_."/xml.php.root", _realpath_."/xml.php");

        echo "copy installer.php.root to installer.php\n";
        copy(_corepath_."/installer.php.root", _realpath_."/installer.php");

    }




    private static function checkDir($strFolder) {
        echo "checking dir "._realpath_.$strFolder."\n";
        if(!is_dir(_realpath_.$strFolder)) {
            mkdir(_realpath_.$strFolder);
            echo " \t\t... directory created\n";
        }
        else {
            echo " \t\t... already existing.\n";
        }
    }
}

echo "<pre>";
echo "Kajona V4 project setup.\nCreates the folder-structure required to build a new project.\n\n";
class_project_setup::setUp();

echo "Project set up.";
echo "</pre>";
