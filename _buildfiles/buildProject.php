#!/usr/bin/php
<?php

class Testmanager {

    public $strProjectPath = "";

    public $bitOnlyProjectsetup = false;

    public $strConfigFile = "";

    public function main() {

        //trigger the setup script
        require(dirname(__FILE__)."/".$this->strProjectPath."/core/setupproject.php");

        if($this->bitOnlyProjectsetup) {
            return;
        }

        //include config
        echo "include config.php -> ".dirname(__FILE__)."/config.php\n";
        require(dirname(__FILE__)."/".$this->strConfigFile);


        echo "creating modified config.php...\n";
        echo "using db-driver ".DB_DRIVER."...\n";
        $strConfigfile = file_get_contents(_realpath_."/core/module_system/system/config/config.php");
        $strConfigfile = str_replace(
            array("%%defaulthost%%", "%%defaultusername%%", "%%defaultpassword%%", "%%defaultdbname%%", "%%defaultprefix%%", "%%defaultdriver%%", "%%defaultport%%"),
            array(DB_HOST, DB_USER, DB_PASS, DB_DB, "autotest_", DB_DRIVER, ""),
            $strConfigfile
        );

        $strSearch = "/\[\'debuglevel\'\]\s* = 0/";
        $strReplace = "['debuglevel'] = 1";
        $strConfigfile = preg_replace($strSearch, $strReplace, $strConfigfile);
        $strSearch = "/\[\'debuglogging\'\]\s* = 1/";
        $strReplace = "['debuglogging'] = 2";
        $strConfigfile = preg_replace($strSearch, $strReplace, $strConfigfile);
        file_put_contents(_realpath_."/project/system/config/config.php", $strConfigfile);

        echo "starting up system-kernel...\n";
        $objCarrier = class_carrier::getInstance();

        echo "dropping old tables...\n";
        $objDB = $objCarrier->getObjDB();
        $arrTables = $objDB->getTables();

        foreach($arrTables as $strOneTable) {
            echo " ... drop table ".$strOneTable."\n";
            $objDB->_query("DROP TABLE ".$strOneTable);
        }

        $objDB->flushQueryCache();


        echo "\n\n\n";
        echo "Searching for packages to be installed...";
        $objManager = new class_module_packagemanager_manager();
        $arrPackageMetadata = $objManager->getAvailablePackages();

        $arrPackagesToInstall = array();
        foreach($arrPackageMetadata as $objOneMetadata) {
            if(!in_array($objOneMetadata->getStrTitle(), array("samplecontent")))
                $arrPackagesToInstall[] = $objOneMetadata;
        }

        echo "nr of packages found to install: ".count($arrPackagesToInstall)."\n";
        echo "\n\n";

        $intMaxLoops = 0;
        echo "starting installations...\n";
        while(count($arrPackagesToInstall) > 0 && ++$intMaxLoops < 100) {
            foreach($arrPackagesToInstall as $intKey => $objOneMetadata) {

                echo "---------------------------------------------------------------\n";

                if(!$objOneMetadata->getBitProvidesInstaller()) {
                    echo "skipping ".$objOneMetadata->getStrTitle().", no installer provided...\n\n";
                    unset($arrPackagesToInstall[$intKey]);
                    continue;
                }


                echo "Installing ".$objOneMetadata->getStrTitle()."...\n\n";
                $objHandler = $objManager->getPackageManagerForPath($objOneMetadata->getStrPath());

                if(!$objHandler->isInstallable()) {
                    echo "skipping ".$objOneMetadata->getStrTitle()." due to unresolved requirements\n";
                    continue;
                }

                echo $objHandler->installOrUpdate();

                unset($arrPackagesToInstall[$intKey]);

                echo "\n\n";
            }
        }


        echo "Installing samplecontent...\n\n";
        $objHandler = $objManager->getPackageManagerForPath("/core/module_samplecontent");
        echo $objHandler->installOrUpdate();

    }
}

$objTestmanager = new Testmanager();
$objTestmanager->strProjectPath = $argv[1];
$objTestmanager->bitOnlyProjectsetup = $argv[2] == "onlySetup";
if(isset($argv[3]))
    $objTestmanager->strConfigFile = $argv[3];
$objTestmanager->main();

