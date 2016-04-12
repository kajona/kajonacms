<?php
/*"******************************************************************************************************
*   (c) 2016 by Kajona, www.kajona.de                                                                   *
*       Published under the GNU LGPL v2.1, see /system/licence_lgpl.txt                                 *
********************************************************************************************************/

namespace Kajona\System;


/**
 * Class V4toV5Migration
 *
 * @package Kajona\System
 * @todo add template pack backup?
 */
class V4toV5Migration
{

    public static function main()
    {
        $objUpater = new V4toV5Migration();

        echo "<pre>\n";
        echo "<b>Welcome to the Kajona v4 to v5 migration helper</b>\n\n";


        $objUpater->updateConfig();
        $objUpater->updateRootLevelFiles();
        $objUpater->updateClassesInDb();
        $objUpater->updateFilenamesInDb();

        echo "</pre>\n";

    }


    private function updateClassesInDb()
    {
        echo "\n\n<b>Updating class-definitions in database</b>\n";
        include_once $this->getPathForModule("module_system")."/bootstrap.php";
        $objDb = \Kajona\System\System\Carrier::getInstance()->getObjDB();


        $arrLegacyFiles = \Kajona\System\System\Resourceloader::getInstance()->getFolderContent("/legacy", array(".php"));

        $arrTablesToRows = array(
            "system"            => "system_class",
            "changelog"         => "change_class",
            "changelog_oprisk"  => "change_class",
            "changelog_proc"    => "change_class",
            "changelog_setting" => "change_class",
            "dashboard"         => "dashboard_class",
            "messages"          => "message_provider",
            "messages_cfg"      => "config_provider",
            "workflows"         => "workflows_class",
            "workflows_handler" => "workflows_handler_class",
            "penitentfee_kpi"   => "kpiamount_variant",
            "proz_unterdim"     => "proz_unterdim_typ",
            "repcfg_report"     => "repcfg_report_targetobject",
            "report_access"     => "access_class"



        );



        $arrTables = $objDb->getTables();

        foreach ($arrTablesToRows as $strTable => $strColumn) {

            echo "\nUpdating table ".$strColumn."@".$strTable."\n";
            if(!in_array(_dbprefix_.$strTable, $arrTables)) {
                echo "Skipping not-present table ".$strTable."\n";
                continue;
            }


            $arrColumns = $objDb->getPArray("SELECT DISTINCT(".$strColumn.") FROM "._dbprefix_.$strTable, array());
            foreach ($arrColumns as $arrOneRow) {
                $strSourceClass = $arrOneRow[$strColumn];

                if ($strSourceClass == "root_node" || substr($strSourceClass, 0, 6) != "class_") {
                    continue;
                }

                $strLegacyFile = array_search($strSourceClass.".php", $arrLegacyFiles);
                if ($strLegacyFile === false) {
                    echo "<b>Failed to find legacy class for ".$strSourceClass.", skipping update </b>\n";
                    continue;
                }

                $strNewName = $this->getNewNameFromLegacyClass($strLegacyFile);
                echo "  updating ".$strSourceClass." to ".$strNewName."\n";
                $strQuery = "UPDATE "._dbprefix_.$strTable." SET ".$strColumn." = ? WHERE ".$strColumn ." = ?";
                $objDb->_pQuery($strQuery, array($strNewName, $strSourceClass));
            }

        }


        $arrMultiContent = array(
            "proz_dim"         => array("proz_dim_ziel", "|"),
            "proz_unterdim"    => array("proz_unterdim_ziel", "|"),
            "report_cfg"       => array("cfg_objects", ",")
        );

        foreach ($arrMultiContent as $strTable => $arrColCfg) {
            $strColumn = $arrColCfg[0];
            $strSeparator = $arrColCfg[1];

            echo "\nUpdating table ".$strColumn."@".$strTable."\n";
            if(!in_array(_dbprefix_.$strTable, $arrTables)) {
                echo "Skipping not-present table ".$strTable."\n";
                continue;
            }


            $arrColumns = $objDb->getPArray("SELECT DISTINCT(".$strColumn.") FROM "._dbprefix_.$strTable, array());
            foreach ($arrColumns as $arrOneRow) {
                $strSourceClass = $arrOneRow[$strColumn];

                //detect already migrated ones
                if (strpos($strSourceClass, "class_") === false) {
                    continue;
                }

                $arrTargets = explode($strSeparator, $strSourceClass);
                $arrTargets = array_filter($arrTargets, function($strValue) { return !empty(trim($strValue)); });

                if(count($arrTargets) == 0) {
                    continue;
                }


                $strNewValue = "";
                foreach($arrTargets as $strOneTargetClass) {
                    $strLegacyFile = array_search($strOneTargetClass.".php", $arrLegacyFiles);
                    if ($strLegacyFile === false) {
                        continue;
                    }

                    if($strSeparator == "|") {
                        $strNewValue .= "|".$this->getNewNameFromLegacyClass($strLegacyFile)."|";
                    }
                    elseif($strSeparator == ",") {
                        if(strlen($strNewValue) > 0) {
                            $strNewValue .= ",";
                        }
                        $strNewValue .= $this->getNewNameFromLegacyClass($strLegacyFile);
                    }
                }

                echo "  updating ".$strSourceClass." to ".$strNewValue."\n";
                $strQuery = "UPDATE "._dbprefix_.$strTable." SET ".$strColumn." = ? WHERE ".$strColumn ." = ?";
                $objDb->_pQuery($strQuery, array($strNewValue, $strSourceClass));
            }

        }


    }

    private function updateFilenamesInDb()
    {

        echo "\n\n<b>Updating filename-definitions in database</b>\n";
        include_once $this->getPathForModule("module_system")."/bootstrap.php";
        $objDb = \Kajona\System\System\Carrier::getInstance()->getObjDB();

        $arrTablesToRows = array(
            "element" => array("element_class_portal", "element_class_admin"),
            "system_module" => array("module_filenameportal", "module_xmlfilenameportal", "module_filenameadmin", "module_xmlfilenameadmin")
        );



        $arrTables = $objDb->getTables();

        foreach ($arrTablesToRows as $strTable => $arrColumns) {

            foreach ($arrColumns as $strColumn) {

                echo "\nUpdating table ".$strColumn."@".$strTable."\n";
                if (!in_array(_dbprefix_.$strTable, $arrTables)) {
                    echo "Skipping not-present table ".$strTable."\n";
                    continue;
                }


                $arrColumns = $objDb->getPArray("SELECT DISTINCT(".$strColumn.") FROM "._dbprefix_.$strTable, array());
                foreach ($arrColumns as $arrOneRow) {
                    $strSourceClass = $arrOneRow[$strColumn];


                    if (substr($strSourceClass, 0, 6) != "class_") {
                        continue;
                    }

                    //convert to new name
                    $strNewClassname = $this->getNameNameFromOldFilename($strSourceClass);
                    echo "  updating ".$strSourceClass." to ".$strNewClassname."\n";
                    $strQuery = "UPDATE "._dbprefix_.$strTable." SET ".$strColumn." = ? WHERE ".$strColumn." = ?";
                    $objDb->_pQuery($strQuery, array($strNewClassname, $strSourceClass));
                }

            }
        }
    }


    private function updateRootLevelFiles()
    {
        echo "\n\n<b>Updating top-level files</b>\n";


        foreach (array(
                     "debug.php"     => "_debugging",
                     "download.php"  => "module_mediamanager",
                     "image.php"     => "module_system",
                     "index.php"     => "module_system",
                     "installer.php" => "module_installer",
                     "xml.php"       => "module_system"
                 ) as $strFile => $strSourceModule) {


            if (is_file(__DIR__."/../".$strFile)) {
                echo "Updating ".__DIR__."/../".$strFile."\n";
                if (!copy($this->getPathForModule($strSourceModule)."/".$strFile.".root", __DIR__."/../".$strFile)) {
                    echo "<b>Failed to update ".__DIR__."/../".$strFile.", aborting update </b>\n";
                }
            }
        }

    }


    private function updateConfig()
    {
        echo "\n\n<b>Updating /project location</b>\n";
        echo "Starting with Kajona 5.0, the files under /project should be stored in a structure similar to /core, e.g.\n";
        echo "/project/lang/module_pages -> /project/module_pages/lang/module_pages\n";
        echo "/project/system/config/config.php -> /project/module_system/system/config/config.php\n";

        echo "\n<b>Searching for config.php</b>\n";
        if (is_file(__DIR__."/../project/system/config/config.php")) {
            echo "Found ".__DIR__."/../project/system/config/config.php, moving to new location\n";

            if (!is_dir(__DIR__."/../project/module_system/system/config")) {
                echo "Creating directory ".__DIR__."/../project/module_system/system/config\n";

                if (!mkdir(__DIR__."/../project/module_system/system/config", 0777, true)) {
                    echo "<b>Failed to create ".__DIR__."/../project/module_system/system/config, aborting update </b>\n";
                    die;
                }
            }

            if (!copy(__DIR__."/../project/system/config/config.php", __DIR__."/../project/module_system/system/config/config.php")) {
                echo "<b>Failed to copy config to ".__DIR__."/../project/module_system/system/config, aborting update </b>\n";
            }

            echo "Removing old config-file\n";
            if (!unlink(__DIR__."/../project/system/config/config.php")) {
                echo "<b>Failed to remove old config ".__DIR__."/../project/system/config, aborting update </b>\n";
            }

            //remove the folder, if empty
            if (count(scandir(__DIR__."/../project/system/config/")) == 2) {
                echo "Removing emtpy ".__DIR__."/../project/system/config/\n";
                rmdir(__DIR__."/../project/system/config/");

                if (count(scandir(__DIR__."/../project/system/")) == 2) {
                    echo "Removing emtpy ".__DIR__."/../project/system/\n";
                    rmdir(__DIR__."/../project/system/");
                }
            }

        }
        else {
            echo "Not found, proceeding\n";
        }


        echo "Search for legacy content in new config.php file...\n";

        /*$arrSearch = array("class_toolkit_admin");
        $arrReplace = array("ToolkitAdmin");
        $strContent = file_get_contents(__DIR__."/../project/module_system/system/config/config.php");
        $strContent = str_replace($arrSearch, $arrReplace, $strContent);
        file_put_contents(__DIR__."/../project/module_system/system/config/config.php", $strContent);*/




    }

    private function getPathForModule($strModule)
    {
        if (is_file(__DIR__."/".$strModule.".phar")) {
            return "phar://".__DIR__."/".$strModule.".phar";
        }
        else {
            return __DIR__."/".$strModule;
        }

    }

    private function getNewNameFromLegacyClass($strFilename)
    {
        $strContent = file_get_contents($strFilename);
        $arrMatches = array();
        preg_match("/extends ([a-zA-Z0-9_\\\\]+)/i", $strContent, $arrMatches);
        $strClassname = $arrMatches[1];

        if($strClassname[0] == "\\") {
            $strClassname = substr($strClassname, 1);
        }

        return $strClassname;
    }

    private function getNameNameFromOldFilename($strFilename)
    {

        //special replacements, hardcoded
        if($strFilename == "class_module_pages_portal.php") {
            return "PagesPortalController.php";
        }

        if($strFilename == "class_module_pages_admin.php") {
            return "PagesAdminController.php";
        }



        $strFilename = substr($strFilename, 0, -4);
        $strFilename = str_replace("class_module_", "", $strFilename);
        $strFilename = str_replace("class_", "", $strFilename);

        $arrNewClassname = explode("_", $strFilename);
        $arrNewClassname = array_map(function($strPath) {
            return ucfirst($strPath);
        }, $arrNewClassname);
        return implode("", $arrNewClassname).".php";
    }

}

V4toV5Migration::main();