<?php
/*"******************************************************************************************************
*   (c) 2012 Kajona, mr.bashshell                                                                           *
*-------------------------------------------------------------------------------------------------------*
*   $Id$                                           *
********************************************************************************************************/

echo "<pre>\n";
echo "+-------------------------------------------------------------------------------+\n";
echo "|                                                                               |\n";
echo "|   Selenium Testsuite Generator                                                |\n";
echo "|                                                                               |\n";
echo "+-------------------------------------------------------------------------------+\n";

class class_testing_helper {

    function __construct() {
        class_carrier::getInstance();
    }

    public function resetConfig() {
        $strProjectConfigPath =  "project/system/config/";
        echo "\nLooking in ".$strProjectConfigPath;
        if(file_exists(_realpath_."/".$strProjectConfigPath."config.php")) {
            echo "\n\nconfig.php found. I will rename your config.php to config.php.bck-selenium...";
            $objFilesystem = new class_filesystem();
            $objFilesystem->fileRename($strProjectConfigPath."config.php", $strProjectConfigPath."config.php.bck-selenium", $bitForce = true);
        }
        else
            echo "\n\nNo config.php in project folder found.";
    }
    
    public function resetSQLite() {
        $strProjectSQLitePath =  "project/dbdumps/";
        echo "\n\n\nLooking for Selenium SQLite file in ".$strProjectSQLitePath;
        if(file_exists(_realpath_."/".$strProjectSQLitePath."seleniumtest.db3")) {
            echo "\n\nseleniumtest.db3 found. I will rename it to selold.bck-selenium...";
            $objFilesystem = new class_filesystem();
            $objFilesystem->fileRename($strProjectSQLitePath."seleniumtest.db3", $strProjectSQLitePath."selold.bck-selenium", $bitForce = true);
        }
        else
            echo "\n\nNo seleniumtest.db3 in project folder found.";
    }
    
    
    public function resetMySQL() {
        $strSeleniumDbPrefix = "selenium_";
        echo "\n\n\nI will delete the tables with the prefix 'selenium_' now...";
        $arrTables = class_carrier::getInstance()->getObjDB()->getTables();
        foreach($arrTables as $strOneTable) {
            if(substr($strOneTable, 0,9) == "selenium_") {
                $strQuery = "DROP TABLE ".$strOneTable;         
                echo "\n   Found table ".$strOneTable.": executing ".$strQuery;
                class_carrier::getInstance()->getObjDB()->_pQuery($strQuery, array());
            }
        }
    }
    
    
    
    
    
}


header("Content-Type: text/html; charset=utf-8");

$objTesting = new class_testing_helper();
$objTesting->resetConfig();
$objTesting->resetSQLite();
$objTesting->resetMySQL();

echo "\n\n";
echo "+-------------------------------------------------------------------------------+\n";
echo "| (c) www.kajona.de                                                             |\n";
echo "+-------------------------------------------------------------------------------+\n";
echo "</pre>";