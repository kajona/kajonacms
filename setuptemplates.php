<?php
/*"******************************************************************************************************
*   (c) 2007-2011 by Kajona, www.kajona.de                                                              *
*       Published under the GNU LGPL v2.1, see /system/licence_lgpl.txt                                 *
*-------------------------------------------------------------------------------------------------------*
*	$Id: class_config.php 4235 2011-11-20 19:10:07Z sidler $                                             *
********************************************************************************************************/


class class_templatesetup {
    public static function setUp() {

        echo "loading core...\n\n";
        include "./bootstrap.php";
        class_carrier::getInstance();

        self::checkDir("/default");
        self::checkDir("/default/js");
        self::checkDir("/default/css");
        self::checkDir("/default/tpl");
        self::checkDir("/default/pics");

        echo "copying folders...\n";

        $arrModules = scandir(_corepath_);

        $arrModules = array_filter($arrModules, function($strValue) {
            return preg_match("/(module|element|_)+.*/i", $strValue);
        });

        foreach($arrModules as $strSingleModule) {
            if(is_dir(_corepath_."/".$strSingleModule."/portal/css"))
                self::copyFolder(_corepath_."/".$strSingleModule."/portal/css", _realpath_._templatepath_."/default/css");

            if(is_dir(_corepath_."/".$strSingleModule."/portal/pics"))
                self::copyFolder(_corepath_."/".$strSingleModule."/portal/pics", _realpath_._templatepath_."/default/pics");

            if(is_dir(_corepath_."/".$strSingleModule."/portal/scripts"))
                self::copyFolder(_corepath_."/".$strSingleModule."/portal/scripts", _realpath_._templatepath_."/default/js");
        }
    }



    private static function checkDir($strFolder) {
        echo "checking dir "._realpath_._templatepath_.$strFolder."\n";
        if(!is_dir(_realpath_._templatepath_.$strFolder)) {
            mkdir(_realpath_._templatepath_.$strFolder);
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
            }
            else if(is_dir($strSourceFolder."/".$strOneEntry)) {
                if(!is_dir($strTargetFolder."/".$strOneEntry))
                    mkdir($strTargetFolder."/".$strOneEntry);
                self::copyFolder($strSourceFolder."/".$strOneEntry, $strTargetFolder."/".$strOneEntry);
            }
        }
    }
}

echo "<pre>";
echo "Kajona V4 template setup.\nCreates the default-template required to render pages.\n";
echo "Files already existing are NOT overwritten.\n";
class_templatesetup::setUp();

echo "Template set up.";
echo "</pre>";