<?php
/*"******************************************************************************************************
*   (c) 2007-2015 by Kajona, www.kajona.de                                                              *
*       Published under the GNU LGPL v2.1, see /system/licence_lgpl.txt                                 *
********************************************************************************************************/


class class_project_setup {
    
    private static $strRealPath = "";
    
    public static function setUp() {
        
        self::$strRealPath = __DIR__."/../";

        echo "<b>Kajona V4 project setup.</b>\nCreates the folder-structure required to build a new project.\n\n";

        $strCurFolder = __DIR__;

        echo "core-path: ".$strCurFolder.", folder found: ".substr($strCurFolder, -4)."\n";

        if(substr($strCurFolder, -4) != "core") {
            echo "current folder must be named core!";
            return;
        }


        $arrExcludedModules = array();
        if(is_file(self::$strRealPath."project/system/config/packageconfig.php")) {
            include self::$strRealPath."project/system/config/packageconfig.php";
        }

        //Module-Constants
        $arrModules = array();
        foreach(scandir(self::$strRealPath) as $strRootFolder) {

            if(!isset($arrExcludedModules[$strRootFolder]))
                $arrExcludedModules[$strRootFolder] = array();

            if(strpos($strRootFolder, "core") === false)
                continue;

            foreach(scandir(self::$strRealPath."/".$strRootFolder) as $strOneModule) {

                if(preg_match("/^(module|element|_)+.*/i", $strOneModule) && !in_array($strOneModule, $arrExcludedModules[$strRootFolder])) {
                    $arrModules[] = $strRootFolder."/".$strOneModule;
                }

            }
        }


        self::checkDir("/admin");
        self::createAdminRedirect();

        self::checkDir("/project");
        self::checkDir("/project/log");
        self::makeWritable("/project/log");
        self::checkDir("/project/dbdumps");
        self::makeWritable("/project/dbdumps");
        self::checkDir("/project/lang");
        self::checkDir("/project/system");
        self::checkDir("/project/system/config");
        self::makeWritable("/project/system/config");
        self::checkDir("/project/portal");
        self::checkDir("/project/temp");
        self::makeWritable("/project/temp");
        self::checkDir("/templates");
        self::makeWritable("/templates");
        self::checkDir("/files");
        self::checkDir("/files/cache");
        self::makeWritable("/files/cache");
        self::checkDir("/files/downloads");
        self::checkDir("/files/images");
        self::makeWritable("/files/images");
        self::checkDir("/files/public");
        self::makeWritable("/files/public");
        self::checkDir("/files/extract");
        self::makeWritable("/files/extract");

        self::checkDir("/templates/default");
        self::checkDir("/templates/default/js");
        self::checkDir("/templates/default/css");
        self::checkDir("/templates/default/tpl");
        self::checkDir("/templates/default/pics");

        self::createLangProjectEntry();
        self::createDefaultTemplateEntry();


        echo "searching for files on root-path...\n";
        foreach($arrModules as $strSingleModule) {
            if(!is_dir(self::$strRealPath."/".$strSingleModule))
                continue;

            $arrContent = scandir(self::$strRealPath."/".$strSingleModule);
            foreach($arrContent as $strSingleEntry) {
                if(substr($strSingleEntry, -5) == ".root" && !is_file(self::$strRealPath."/".substr($strSingleEntry, 0, -5))) {
                    echo "copy ".$strSingleEntry." to ".self::$strRealPath."/".substr($strSingleEntry, 0, -5)."\n";
                    copy(self::$strRealPath."/".$strSingleModule."/".$strSingleEntry, self::$strRealPath."/".substr($strSingleEntry, 0, -5));
                }
            }
        }


        echo "\n<b>Kajona V4 template setup.</b>\nCreates the default-template-pack required to render pages.\n";
        echo "Files already existing are NOT overwritten.\n";


        foreach($arrModules as $strSingleModule) {
            if(is_dir(self::$strRealPath."/".$strSingleModule."/templates")) {
                //TODO: check against excluded modules

                $arrEntries = scandir(self::$strRealPath."/".$strSingleModule."/templates");
                foreach($arrEntries as $strOneFolder) {
                    if($strOneFolder != "." && $strOneFolder != ".." && is_dir(self::$strRealPath."/".$strSingleModule."/templates/".$strOneFolder)) {
                        if($strOneFolder == "default")
                            self::copyFolder(self::$strRealPath."/".$strSingleModule."/templates", self::$strRealPath."/templates", array(".tpl"));
                        else
                            self::copyFolder(self::$strRealPath."/".$strSingleModule."/templates", self::$strRealPath."/templates");
                    }
                }
            }

            if(is_dir(self::$strRealPath."/".$strSingleModule."/files"))
                self::copyFolder(self::$strRealPath."/".$strSingleModule."/files", self::$strRealPath."/files");
        }


        echo "\n<b>Kajona V4 htaccess setup</b>\n";
        self::createAllowHtaccess("/files/cache/.htaccess");
        self::createAllowHtaccess("/files/images/.htaccess");
        self::createAllowHtaccess("/files/public/.htaccess");
        self::createAllowHtaccess("/files/extract/.htaccess");
        self::createAllowHtaccess("/templates/.htaccess");

        self::createDenyHtaccess("/project/.htaccess");
        self::createDenyHtaccess("/files/.htaccess");

        self::scanComposer();

        echo "\n<b>Done.</b>\nIf everything went well, <a href=\"../installer.php\">open the installer</a>\n";

    }


    private static function createLangProjectEntry() {
        $strContent = <<<TXT

Kajona V4 lang subsystem.

    Since Kajona V4, it is possible to change the default-lang files by deploying them inside the projects'
    lang-folder.
    This provides a way to change texts and labels without breaking them during the next system-update.

    Example: By default, the Template-Manager is titled "Packagemanagement".
    The entry is created by the file

    /core/module_packagemanager/lang/module_packagemanager/lang_packagemanager_en.php -> \$lang["modul_titel"].

    To change the entry to "Packages" or "Modules" copy the original lang-file into the matching folder
    under the project root. Using the example above, that would be:

    /project/lang/module_packagemanager/lang_packagemanager_en.php

    Now change the entry
    \$lang["modul_titel"] = "Packagemanagement";
    to
    \$lang["modul_titel"] = "Packages";

    Reload your browser and enjoy the relabeled interface.


TXT;
        file_put_contents(self::$strRealPath."/project/lang/readme.txt", $strContent);
    }

    private static function createDefaultTemplateEntry() {
        $strContent = <<<TXT

Kajona V4 default template-pack.

Please don't change anything within this folder, updates may break your changes
and overwrite them without further warning.

If you want to adjust or change anything, create a new template pack using the
backend (module package-management, list templates, create new template) and
select the templates to redefine.

Afterwards change the files in your new templatepack and activate the pack
in the backend via the package-management.

If you wonder why the folder tpl is empty: The default template-pack loads all templates
directly aut of the core-packages. If you want to modify the default template tr if you
want to create a new template, just follow the steps written above:

- Open the backend and click packagemanagement => installed Templates
- Create a new template-pack and select the templates you want to modify
  (you are able to add additional templates afterwards)
- Activate the template pack afterwards
- The new templatepack is available under /templates, start to browse and modify the files
  there

Have fun!


TXT;
        file_put_contents(self::$strRealPath."/templates/default/readme.txt", $strContent);
    }


    private static function createAdminRedirect() {
        $strContent  = "<html>\n";
        $strContent .= " <head>\n";
        $strContent .= "  <title>Loading</title>\n";
        $strContent .= "  <meta http-equiv='refresh' content='0; URL=../index.php?admin=1'>\n";
        $strContent .= " </head>\n";
        $strContent .= " <body>Loading...</body>\n";
        $strContent .= "</html>\n";

        file_put_contents(self::$strRealPath."/admin/index.html", $strContent);
    }

    private static function checkDir($strFolder) {
        echo "checking dir ".self::$strRealPath.$strFolder."\n";
        if(!is_dir(self::$strRealPath.$strFolder)) {
            mkdir(self::$strRealPath.$strFolder, 0777);
            echo " \t\t... directory created\n";
        }
        else {
            echo " \t\t... already existing.\n";
        }
    }

    private static function makeWritable($strFolder) {
        chmod(self::$strRealPath.$strFolder, 0777);
    }


    private static function copyFolder($strSourceFolder, $strTargetFolder, $arrExcludeSuffix = array()) {
        $arrEntries = scandir($strSourceFolder);
        foreach($arrEntries as $strOneEntry) {
            if($strOneEntry == "." || $strOneEntry == ".." || $strOneEntry == ".svn" || in_array(substr($strOneEntry, strrpos($strOneEntry, ".")), $arrExcludeSuffix))
                continue;

            if(is_file($strSourceFolder."/".$strOneEntry) && !is_file($strTargetFolder."/".$strOneEntry)) {
                echo "copying file ".$strSourceFolder."/".$strOneEntry." to ".$strTargetFolder."/".$strOneEntry."\n";
                if(!is_dir($strTargetFolder))
                    mkdir($strTargetFolder, 0777, true);

                copy($strSourceFolder."/".$strOneEntry, $strTargetFolder."/".$strOneEntry);
                chmod($strTargetFolder."/".$strOneEntry, 0777);
            }
            elseif(is_dir($strSourceFolder."/".$strOneEntry)) {
                self::copyFolder($strSourceFolder."/".$strOneEntry, $strTargetFolder."/".$strOneEntry, $arrExcludeSuffix);
            }
        }
    }

    private static function createDenyHtaccess($strPath) {
        if(is_file(self::$strRealPath.$strPath))
            return;

        echo "placing deny htaccess in ".$strPath."\n";
        $strContent = "\n\nDeny from all\n\n";
        file_put_contents(self::$strRealPath.$strPath, $strContent);
    }

    private static function createAllowHtaccess($strPath) {
        if(is_file(self::$strRealPath.$strPath))
            return;

        echo "placing allow htaccess in ".$strPath."\n";
        $strContent = "\n\nAllow from all\n\n";
        file_put_contents(self::$strRealPath.$strPath, $strContent);
    }

    private static function scanComposer() {
        $objCoreDirs = new DirectoryIterator(__DIR__ . "/../");
        foreach ($objCoreDirs as $objCoreDir) {
            if ($objCoreDir->isDir() && substr($objCoreDir->getFilename(), 0, 4) == 'core') {
                $objModuleDirs = new DirectoryIterator($objCoreDir->getRealPath());
                foreach ($objModuleDirs as $objDir) {
                    if (substr($objDir->getFilename(), 0, 7) == 'module_') {
                        $composerFile = $objDir->getRealPath() . '/composer.json';
                        if (is_file($composerFile)) {
                            $arrOutput = array();
                            $intReturn = 0;
                            exec('composer install --prefer-source --no-dev --working-dir '.dirname($composerFile), $arrOutput, $intReturn);
                            if($intReturn == 127) {
                                echo "<span style='color: red;'>composer was not found. please run 'composer install --prefer-source --no-dev --working-dir ".dirname($composerFile)."' manually</span>\n";
                                continue;
                            }
                            echo "Composer install finished for ".$composerFile.": \n";

                            echo "   ".implode("\n   ", $arrOutput);
                        }
                    }
                }
            }
        }
    }
}

echo "<pre>";

class_project_setup::setUp();

echo "</pre>";
