#!/usr/bin/php
<?php

class Testmanager {

	public $strProjectPath = "";
	
	public $bitOnlyProjectsetup = false;
	
	public $strConfigFile = "config_sqlite3.php";

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
            array("localhost",        DB_USER,                DB_PASS,              DB_DB,                  "autotest_",        DB_DRIVER,             ""),
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

        foreach ($arrTables as $strOneTable) {
            echo " ... drop table ".$strOneTable."\n";
            $objDB->_query("DROP TABLE ".$strOneTable);
        }

        $objDB->flushQueryCache();
        
        
        $objSystemData = new class_module_packagemanager_metadata();
        $objSystemData->autoInit("/core/module_system");
        $objTemplateData = new class_module_packagemanager_metadata();
        $objTemplateData->autoInit("/core/module_packagemanager");
        $objPagesData = new class_module_packagemanager_metadata();
        $objPagesData->autoInit("/core/module_pages");
        $objMediamanagerData = new class_module_packagemanager_metadata();
        $objMediamanagerData->autoInit("/core/module_mediamanager");
        $arrPackagesToInstall = array(
            $objSystemData, $objTemplateData, $objPagesData, $objMediamanagerData
        );

        echo "\n\n\n";
        echo "Searching for packages to be installed...";
        $objManager = new class_module_packagemanager_manager();
        $arrPackageMetadata = $objManager->getAvailablePackages();

        foreach($arrPackageMetadata as $objOneMetadata) {
            if(!in_array($objOneMetadata->getStrTitle(), array("system", "pages", "packagemanager", "samplecontent", "mediamanager")))
                $arrPackagesToInstall[] = $objOneMetadata;
        }

        echo "nr of packages found to install: ".count($arrPackagesToInstall)."\n";
        echo "\n\n";

        echo "starting installations...\n";
        foreach($arrPackagesToInstall as $objOneMetadata) {

            echo "---------------------------------------------------------------\n";

            if(!$objOneMetadata->getBitProvidesInstaller()) {
                echo "skipping ".$objOneMetadata->getStrTitle().", no installer provided...\n\n";
                continue;
            }


            echo "Installing ".$objOneMetadata->getStrTitle()."...\n\n";
            $objHandler = $objManager->getPackageManagerForPath($objOneMetadata->getStrPath());
            echo $objHandler->installOrUpdate();
            
            echo "\n\n";
        }


        echo "Installing samplecontent...\n\n";
        $objHandler = $objManager->getPackageManagerForPath("/core/module_samplecontent");
        echo $objHandler->installOrUpdate();

    }
}

$objTestmanager = new Testmanager();
$objTestmanager->strProjectPath = $argv[1];
$objTestmanager->bitOnlyProjectsetup = $argv[2] == "onlySetup";
$objTestmanager->strConfigFile = $argv[3];
$objTestmanager->main();

