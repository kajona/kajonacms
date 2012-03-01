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
        $strConfigfile = str_replace(array("%%defaulthost%%", "%%defaultusername%%", "%%defaultpassword%%", "%%defaultdbname%%", "%%defaultprefix%%", "%%defaultdriver%%", "%%defaultport%%"),
            array("localhost",        DB_USER,                DB_PASS,              DB_DB,                  "autotest_",        DB_DRIVER,             ""),
            $strConfigfile) ;

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
        
        
        $arrInstallersToRun = array(
            "/core/module_system/installer/installer_system.php" => "installer_system.php",
            "/core/module_templatemanager/installer/installer_templatemanager.php" => "installer_templatemanager.php",
            "/core/module_pages/installer/installer_pages.php" => "installer_pages.php"
        );


        echo "\n\n\n";
        echo "Searching installers to run...\n";
        
        $arrInstallersOnFileystem = class_resourceloader::getInstance()->getFolderContent("/installer", array(".php"));
        foreach($arrInstallersOnFileystem as $strPath => $strOneInstallerFile) {
            if(substr($strOneInstallerFile, 0, 10) == "installer_" && substr($strOneInstallerFile, -4) == ".php" && strpos($strOneInstallerFile, "_sc_") === false) {
                if(strpos($strOneInstallerFile, "_element_") === false && $strOneInstallerFile != "installer_samplecontent.php") {
                    if(!in_array($strOneInstallerFile, $arrInstallersToRun))
                        $arrInstallersToRun[$strPath] = $strOneInstallerFile;
                }
            }
        }

        foreach($arrInstallersOnFileystem as $strPath => $strOneInstallerFile) {
            if(substr($strOneInstallerFile, 0, 10) == "installer_" && substr($strOneInstallerFile, -4) == ".php" && strpos($strOneInstallerFile, "_sc_") === false) {
                if(strpos($strOneInstallerFile, "_element_") !== false) {
                    if(!in_array($strOneInstallerFile, $arrInstallersToRun))
                        $arrInstallersToRun[$strPath] = $strOneInstallerFile;
                }
            }
        }

        echo "installers found to execute:\n";
        echo implode(", ", $arrInstallersToRun);
        echo "\n\n";

        echo "module-installs...\n";
        foreach($arrInstallersToRun as $strPath => $strOneInstaller) {
            echo "---------------------------------------------------------------\n";
            echo "Installing ".$strOneInstaller."...\n\n";
            include_once(_realpath_.$strPath);
            $strClassname = "class_".str_replace(".php", "", $strOneInstaller, $strClassname);
            $objInstaller = new $strClassname();
            echo $objInstaller->install();
            

        }

        echo "post-installs...\n";
        foreach($arrInstallersToRun as $strPath => $strOneInstaller) {
            echo "---------------------------------------------------------------\n";
            echo "Installing ".$strOneInstaller."...\n\n";
            include_once(_realpath_.$strPath);
            $strClassname = "class_".str_replace(".php", "", $strOneInstaller, $strClassname);
            $objInstaller = new $strClassname();
            if($objInstaller->hasPostInstalls())
                $objInstaller->postInstall();

        }
        

        echo "Installing samplecontent...\n\n";
        include_once(_realpath_."/core/module_samplecontent/installer/installer_samplecontent.php");
        $objInstaller = new class_installer_samplecontent();
        echo $objInstaller->install();
        echo $objInstaller->postInstall("element");


    }
}

$objTestmanager = new Testmanager();
$objTestmanager->strProjectPath = $argv[1];
$objTestmanager->bitOnlyProjectsetup = $argv[2] == "onlySetup";
$objTestmanager->strConfigFile = $argv[3];
$objTestmanager->main();

