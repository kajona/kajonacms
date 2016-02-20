#!/usr/bin/php
<?php

class BuildHelper {

    public $strProjectPath = "";

    public $bitOnlyProjectsetup = false;

    public $strConfigFile = "";

    public function main() {


        echo "\n\n";
        echo "Kajona Build Project Helper\n";
        echo " Params:\n";
        echo "   projectPath: ".$this->strProjectPath."\n";
        echo "   configFile: ".$this->strConfigFile."\n";
        echo "   onlySetup: ".($this->bitOnlyProjectsetup ? "true" : "false")."\n";
        echo "\n\n";



        $arrCores = array();
        foreach(scandir(__DIR__."/".$this->strProjectPath) as $strRootFolder) {
            if(strpos($strRootFolder, "core") === false)
                continue;
            $arrCores[] = $strRootFolder;
        }

        //trigger the setup script, try to get the matching one
        foreach(array_reverse($arrCores) as $strOneCore) {
            if(file_exists(__DIR__."/".$this->strProjectPath."/".$strOneCore."/setupproject.php")) {
                require(__DIR__."/".$this->strProjectPath."/".$strOneCore."/setupproject.php");
                break;
            }

        }

        echo "calling cleanCore script: php -f '".__DIR__."/cleanCore.php' '".$this->strProjectPath."'\n";
        $arrReturn = array();
        exec("php -f \"".__DIR__."/cleanCore.php\" \"".$this->strProjectPath."\"", $arrReturn);
        echo implode("\n", $arrReturn)."\n";


        if($this->bitOnlyProjectsetup) {
            return;
        }

        //include config
        echo "include config.php -> ".__DIR__."/".$this->strConfigFile."\n";
        require(__DIR__."/".$this->strConfigFile);


        echo "creating modified config.php...\n";
        echo "using db-driver ".DB_DRIVER."...\n";
        $strConfigfile = file_get_contents(__DIR__."/".$this->strProjectPath."/core/module_system/system/config/config.php");
        $strConfigfile = str_replace(
            array("%%defaulthost%%", "%%defaultusername%%", "%%defaultpassword%%", "%%defaultdbname%%", "%%defaultprefix%%", "%%defaultdriver%%", "%%defaultport%%"),
            array(DB_HOST, DB_USER, DB_PASS, DB_DB, "autotest_", DB_DRIVER, ""),
            $strConfigfile
        );

        $strSearch = "/\[\'debuglevel\'\]\s* = 0/";
        $strReplace = "['debuglevel'] = 1";
        $strConfigfile = preg_replace($strSearch, $strReplace, $strConfigfile);
        $strSearch = "/\[\'debuglogging\'\]\s* = 1/";
        $strReplace = "['debuglogging'] = 3";
        $strConfigfile = preg_replace($strSearch, $strReplace, $strConfigfile);
        file_put_contents(__DIR__."/".$this->strProjectPath."/project/system/config/config.php", $strConfigfile);

        echo "starting up system-kernel...\n";
        echo "including ".__DIR__."/".$this->strProjectPath."/core/module_system/bootstrap.php...\n";
        include __DIR__."/".$this->strProjectPath."/core/module_system/bootstrap.php";
        echo "creating instance of \\Kajona\\System\\System\\Carrier...\n";
        $objCarrier = \Kajona\System\System\Carrier::getInstance();

        echo "dropping old tables...\n";
        $objDB = $objCarrier->getObjDB();
        $arrTables = $objDB->getTables();

        foreach($arrTables as $strOneTable) {
            echo " ... drop table ".$strOneTable."\n";
            $objDB->_pQuery("DROP TABLE ".$strOneTable, array());
        }

        \Kajona\System\System\Carrier::getInstance()->flushCache(\Kajona\System\System\Carrier::INT_CACHE_TYPE_DBQUERIES | \Kajona\System\System\Carrier::INT_CACHE_TYPE_DBTABLES | \Kajona\System\System\Carrier::INT_CACHE_TYPE_MODULES | \Kajona\System\System\Carrier::INT_CACHE_TYPE_OBJECTFACTORY | \Kajona\System\System\Carrier::INT_CACHE_TYPE_ORMCACHE);

        echo "\n\n";
        echo "Blocking browscap update\n";
        touch(__DIR__."/".$this->strProjectPath."/project/temp/cache.lock");

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
            /** @var class_module_packagemanager_metadata $objOneMetadata */
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
        try {
            $objHandler = $objManager->getPackageManagerForPath(class_resourceloader::getInstance()->getCorePathForModule("module_samplecontent")."/module_samplecontent");
        }
        catch (class_exception $objEx) {
            $objHandler = null;
        }
        if($objHandler !== null)
            echo $objHandler->installOrUpdate();

    }

}

$objTestmanager = new BuildHelper();
$objTestmanager->strProjectPath = $argv[1];
$objTestmanager->bitOnlyProjectsetup = $argv[2] == "onlySetup";
if(isset($argv[3]))
    $objTestmanager->strConfigFile = $argv[3];
$objTestmanager->main();

