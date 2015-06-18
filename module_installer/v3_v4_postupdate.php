<?php
/*"******************************************************************************************************
*   (c) 2007-2015 by Kajona, www.kajona.de                                                              *
*       Published under the GNU LGPL v2.1, see /system/licence_lgpl.txt                                 *
*-------------------------------------------------------------------------------------------------------*
*	$Id$                                               *
********************************************************************************************************/


require_once './core/module_system/bootstrap.php';



class class_v3_v4_postupdate {

    /**
     * @var class_db
     */
    private $objDB;

    public function postUpdate() {
        if(@ini_get("max_execution_time") < 600 && @ini_get("max_execution_time") > 0)
            @ini_set("max_execution_time", 600);

        $this->objDB = class_carrier::getInstance()->getObjDB();

        echo "<pre>";
        echo "<b>Kajona v4 Post Update</b>\n\n";
        echo "Running post-update scripts to finalize a v3 to v4 update\n\n";

        echo "<b>Attention!</b> \nIn order to have v4 working properly, you have to remove the following entries (if present) \nfrom your config file at /system/config/config.php:\n\n";
        echo "    \$config[\"images_cachepath\"]     = \"/portal/pics/cache/\" \n";
        echo "All \$config = array(); lines \n";
        echo "All \$debug = array(); lines \n";

        echo "\n";
        echo "Please make sure to update your global_includes.php (/project/portal/global_includes.php) file to the latest version.\n";
        echo "If you didn't modify the file in your v3 installation, you are safe to delete the file at /project/portal/global_includes.php.\n";
        echo "More information can be found at <a href='http://www.kajona.de/update_342_to_40.html'>http://www.kajona.de/update_342_to_40.html</a>\n\n";

        echo "Checking installation state of mediamanager...\n";
        if(!in_array(_dbprefix_."mediamanager_repo", $this->objDB->getTables())) {
            echo "<b>Install the module mediamanager before proceeding the upgrade. Aborting.</b>";
            return false;
        }
        echo "... installed.\n";

        echo "\n\n<b>Galleries</b>\n";
        if(!$this->updateGalleries())
            return false;

        echo "\n\n<b>Downloads</b>\n";
        if(!$this->updateDownloads())
            return false;

        echo "\n\n<b>Filemanager</b>\n";
        if(!$this->updateFilemanager())
            return false;

        echo "\n\n<b>v3 Templates</b>\n";
        if(!$this->activateTemplate())
            return false;

        echo "\n\n<b>Backend-Skin</b>\n";
        $this->updateBackendSkin();

        echo "\n\n<b>Linked Files</b>\n";
        $this->updateFilesystemReferences();

        echo "\n\n<b>Set default chart-engine to flot</b>";
        $objSetting = class_module_system_setting::getConfigByName("_system_graph_type_");
        $objSetting->setStrValue("jqplot");
        $objSetting->updateObjectToDb();

        echo "\n\n<b>Update succeeded. \nPlease remove the file ".__FILE__." from the filesystem.</b>\n";

        echo "</pre>";

        return true;
    }


    private function updateFilesystemReferences($strSearch = null, $strReplace = null) {
        echo "Searching for linked files...\n";
        $arrTablesGiven = $this->objDB->getTables();

        $arrReplacements = array(
            "/portal/pics/" => "/files/images/",
            "/portal/downloads/" => "/files/downloads/"
        );

        if($strSearch !== null && $strReplace !== null) {
            $arrReplacements = array($strSearch => $strReplace);
        }

        $arrTablesToScan = array(
            array("element_paragraph", "content_id", "paragraph_content"),
            array("element_paragraph", "content_id", "paragraph_image"),
            array("element_formular", "content_id", "formular_success"),
            array("element_formular", "content_id", "formular_error"),
            array("element_image", "content_id", "image_image"),
            array("element_tellafriend", "content_id", "tellafriend_error"),
            array("element_tellafriend", "content_id", "tellafriend_success"),
            array("element_universal", "content_id", "char1"),
            array("element_universal", "content_id", "char2"),
            array("element_universal", "content_id", "char3"),

            array("em_event", "em_ev_id", "em_ev_description"),
            array("faqs", "faqs_id", "faqs_answer"),
            array("news", "news_id", "news_text"),
            array("news", "news_id", "news_image")
        );

        echo "Scanning...\n";
        foreach($arrTablesToScan as $arrConfig) {

            if(in_array(_dbprefix_.$arrConfig[0], $arrTablesGiven)) {
                echo " ".$arrConfig[0].", column ".$arrConfig[2]."\n";


                foreach($arrReplacements as $strSearch => $strReplace) {


                    $strQuery = "SELECT * FROM "._dbprefix_.$arrConfig[0]." WHERE ".$arrConfig[2]." LIKE ?";
                    $arrRows = $this->objDB->getPArray($strQuery, array("%".$strSearch."%"));
                    if(count($arrRows) > 0) {
                        echo "   searching for ".$strSearch.", found ".count($arrRows)." references\n";
                        foreach($arrRows as $arrOneRow) {
                            $strQuery = "UPDATE "._dbprefix_.$arrConfig[0]." SET ".$arrConfig[2]." = ? WHERE ".$arrConfig[1]." = ?";

                            $this->objDB->_pQuery($strQuery, array(uniStrReplace($strSearch, $strReplace, $arrOneRow[$arrConfig[2]]), $arrOneRow[$arrConfig[1]]), array(false));
                            echo "      updated entry with id ".$arrOneRow[$arrConfig[1]]."\n";
                        }
                    }

                }

            }

        }
    }


    private function updateBackendSkin() {
        echo "Updating users to use v4 instead of v3 skin\n";
        $strQuery = "UPDATE "._dbprefix_."user SET user_admin_skin = 'kajona_v4' WHERE user_admin_skin = 'kajona_v3' OR user_admin_skin=''";
        return $this->objDB->_pQuery($strQuery, array());
    }


    private function activateTemplate() {

        echo "Searching for a v3 template to activate...\n";
        if(!is_dir(_realpath_."/templates/v3template")) {
            echo "no template found, nothing to do.\n";
            return true;
        }

        echo "Templatepack v3template found\n";
        class_module_packagemanager_template::syncTemplatepacks();
        $arrPackages = class_module_packagemanager_template::getObjectList();
        /** @var class_module_packagemanager_template $objOnePackage */
        foreach($arrPackages as $objOnePackage) {
            if($objOnePackage->getStrName() == "v3template") {
                echo "setting the template-pack active...\n";
                $objOnePackage->setIntRecordStatus(1);
                $objOnePackage->updateObjectToDb();

                echo "If you didn't change the templates in v3, delete the unmodified ones from /templates/v3template/tpl.\n";
                echo "Otherwise make sure to update the references to js and css files and consider to upgrade your js-implementation to the now-standard jQuery instead of YUI.\n";
                echo "For css-files, the new path would be:\n";
                echo "  _webpath_/templates/v3template/css/filename.css\n";
                echo "For js-files, the new path would be:\n";
                echo "  _webpath_/templates/v3template/js/filename.js\n";

                return true;
            }
        }

        echo "ERROR: no matching template-pack found in the database. set up your templates manually.\n";
        return true;
    }



    private function updateDownloads() {
        echo "Updating downloads to new mediamanager structure...\n";

        echo "<b>Please note: Permission-Settings can't be migrated. Please adopt the permissions, e.g. to download manually.</b>\n";

        if(!in_array(_dbprefix_."downloads_archive", $this->objDB->getTables())) {
            echo "Downloads table missing, no update required.\n";
            return true;
        }

        echo "Migrating old archives to new mediamanager repos...\n";
        $strQuery = "SELECT * FROM "._dbprefix_."downloads_archive";
        $arrArchives = $this->objDB->getPArray($strQuery, array());
        foreach($arrArchives as $arrOneArchive) {
            //load foreign repo data
            $strQuery = "SELECT * FROM "._dbprefix_."filemanager WHERE filemanager_foreign_id = ?";
            $arrRepoData = $this->objDB->getPRow($strQuery, array($arrOneArchive["archive_id"]));

            echo "migrating old archive ".$arrOneArchive["archive_title"]."\n";
            echo "  old path: ".$arrRepoData["filemanager_path"]."\n";

            //convert the path
            $strPath = $this->convertOldPath($arrRepoData["filemanager_path"]);
            echo "  new path: ".$strPath."\n";
            $objRepo = new class_module_mediamanager_repo();
            $objRepo->setStrPath($strPath);
            $objRepo->setStrTitle($arrOneArchive["archive_title"]);
            $objRepo->setStrViewFilter($arrRepoData["filemanager_view_filter"]);
            $objRepo->setStrUploadFilter($arrRepoData["filemanager_upload_filter"]);
            $objRepo->updateObjectToDb();
            $objRepo->syncRepo();

            echo "deleting old filemanager repo...\n";
            $strQuery = "DELETE FROM "._dbprefix_."filemanager WHERE filemanager_id = ?";
            $this->objDB->_pQuery($strQuery, array($arrRepoData["filemanager_id"]));
            $this->deleteSystemRecord($arrRepoData["filemanager_id"]);

            echo "migrating downloads data...\n";
            $this->updateDownloadsLevel($objRepo->getSystemid(), $arrOneArchive["archive_id"]);

            echo "updating existing downloads-elements...\n";
            $strQuery = "UPDATE "._dbprefix_."element_downloads SET download_id = ? WHERE download_id = ?";
            $this->objDB->_pQuery($strQuery, array($objRepo->getSystemid(), $arrOneArchive["archive_id"]));

            echo "deleting downloads-toplist element...\n";
            $objElement = class_module_pages_element::getElement("downloadstoplist");
            if($objElement != null)
                $objElement->deleteObjectFromDatabase();

            echo "deleting old adminwidgets...\n";
            $arrWidgets = class_module_dashboard_widget::getAllWidgets();
            foreach($arrWidgets as $objOneWidget) {
                if($objOneWidget->getStrClass() == "class_adminwidget_downloads")
                    $objOneWidget->deleteObjectFromDatabase();
            }

            echo "deleting archive and contained downloads data...\n";
            $this->deleteDownloadsLevel($arrOneArchive["archive_id"]);
            $this->objDB->_pQuery("DELETE FROM "._dbprefix_."downloads_archive WHERE archive_id = ?", array($arrOneArchive["archive_id"]));
            $this->deleteSystemRecord($arrOneArchive["archive_id"]);

        }

        echo "Migrating old log-table...\n";
        $this->objDB->_pQuery("INSERT INTO "._dbprefix_."mediamanager_dllog SELECT * FROM "._dbprefix_."downloads_log", array());

        echo "Deleting downloads module...\n";
        $this->removeModule("downloads");
        $this->objDB->_pQuery("DROP TABLE "._dbprefix_."downloads_archive", array());
        $this->objDB->_pQuery("DROP TABLE "._dbprefix_."downloads_file", array());
        $this->objDB->_pQuery("DROP TABLE "._dbprefix_."downloads_log", array());
        $this->removeSetting("_downloads_suche_seite_");

        return true;
    }


    private function updateDownloadsLevel($strNewPrevid, $strOldPrevid) {
        $arrFiles = class_module_mediamanager_file::loadFilesDB($strNewPrevid);
        $arrOldFiles = $this->objDB->getPArray("SELECT * FROM  "._dbprefix_."system, "._dbprefix_."downloads_file WHERE system_id = downloads_id AND system_prev_id = ?", array($strOldPrevid));

        foreach($arrFiles as $objOneFile) {
            $strNewFilename = basename($objOneFile->getStrFilename());
            echo "  searching file/folder ".$strNewFilename."\n";

            foreach($arrOldFiles as $arrOneOldFile) {
                if(basename($arrOneOldFile["downloads_filename"]) == $strNewFilename) {
                    $objOneFile->setStrName($arrOneOldFile["downloads_name"]);
                    $objOneFile->setStrDescription($arrOneOldFile["downloads_description"]);
                    $objOneFile->setIntHits($arrOneOldFile["downloads_hits"]);
                    $objOneFile->updateObjectToDb();

                    $this->moveComments($arrOneOldFile["downloads_id"], $objOneFile->getSystemid());
                    $this->migrateRatings($arrOneOldFile["downloads_id"], $objOneFile->getSystemid());
                    $this->updateFilesystemReferences("download.php?systemid=".$arrOneOldFile["downloads_id"], "download.php?systemid=".$objOneFile->getSystemid());

                    echo "...update succeeded\n";

                    if($objOneFile->getIntType() == class_module_mediamanager_file::$INT_TYPE_FOLDER)
                        $this->updateDownloadsLevel($objOneFile->getSystemid(), $arrOneOldFile["downloads_id"]);
                }
            }
        }
    }

    private function deleteDownloadsLevel($strOldPrevid) {
        $arrOldFiles = $this->objDB->getPArray("SELECT * FROM  "._dbprefix_."system, "._dbprefix_."downloads_file WHERE system_id = downloads_id AND system_prev_id = ?", array($strOldPrevid));

        foreach($arrOldFiles as $arrOneFile) {
            $this->deleteDownloadsLevel($arrOneFile["downloads_id"]);

            $this->objDB->_pQuery("DELETE FROM "._dbprefix_."downloads_file WHERE downloads_id = ?", array($arrOneFile["downloads_id"]));
            $this->deleteSystemRecord($arrOneFile["downloads_id"]);
        }
    }











    private function updateGalleries() {
        echo "Updating galleries to new mediamanager structure...\n";

        if(!in_array(_dbprefix_."gallery_gallery", $this->objDB->getTables())) {
            echo "Gallery table missing, no update required.\n";
            return true;
        }

        echo "Migrating old galleries to new mediamanager repos...\n";
        $strQuery = "SELECT * FROM "._dbprefix_."gallery_gallery";
        $arrGalleries = $this->objDB->getPArray($strQuery, array());
        foreach($arrGalleries as $arrOneGallery) {
            //load foreign repo data
            $strQuery = "SELECT * FROM "._dbprefix_."filemanager WHERE filemanager_foreign_id = ?";
            $arrRepoData = $this->objDB->getPRow($strQuery, array($arrOneGallery["gallery_id"]));

            echo "migrating old gallery ".$arrOneGallery["gallery_title"]."\n";
            echo "  old path: ".$arrRepoData["filemanager_path"]."\n";

            //convert the path
            $strPath = $this->convertOldPath($arrRepoData["filemanager_path"]);
            echo "  new path: ".$strPath."\n";
            $objRepo = new class_module_mediamanager_repo();
            $objRepo->setStrPath($strPath);
            $objRepo->setStrTitle($arrOneGallery["gallery_title"]);
            $objRepo->setStrViewFilter($arrRepoData["filemanager_view_filter"]);
            $objRepo->setStrUploadFilter($arrRepoData["filemanager_upload_filter"]);
            $objRepo->updateObjectToDb();
            $objRepo->syncRepo();

            echo "deleting old filemanager repo...\n";
            $strQuery = "DELETE FROM "._dbprefix_."filemanager WHERE filemanager_id = ?";
            $this->objDB->_pQuery($strQuery, array($arrRepoData["filemanager_id"]));
            $this->deleteSystemRecord($arrRepoData["filemanager_id"]);

            echo "migrating image data...\n";
            $this->updateGalleryLevel($objRepo->getSystemid(), $arrOneGallery["gallery_id"]);

            echo "updating existing gallery-elements...\n";
            $strQuery = "UPDATE "._dbprefix_."element_gallery SET gallery_id = ? WHERE gallery_id = ?";
            $this->objDB->_pQuery($strQuery, array($objRepo->getSystemid(), $arrOneGallery["gallery_id"]));

            echo "deleting gallery and contained-image data...\n";
            $this->deleteGalleryLevel($arrOneGallery["gallery_id"]);
            $this->objDB->_pQuery("DELETE FROM "._dbprefix_."gallery_gallery WHERE gallery_id = ?", array($arrOneGallery["gallery_id"]));
            $this->deleteSystemRecord($arrOneGallery["gallery_id"]);

        }

        echo "Deleting gallery module...\n";
        $this->removeModule("gallery");
        $this->objDB->_pQuery("DROP TABLE "._dbprefix_."gallery_gallery", array());
        $this->objDB->_pQuery("DROP TABLE "._dbprefix_."gallery_pic", array());
        $this->removeSetting("_gallery_search_resultpage_");

        return true;
    }


    private function updateGalleryLevel($strNewPrevid, $strOldPrevid) {
        $arrFiles = class_module_mediamanager_file::loadFilesDB($strNewPrevid);
        $arrOldFiles = $this->objDB->getPArray("SELECT * FROM  "._dbprefix_."system, "._dbprefix_."gallery_pic WHERE system_id = pic_id AND system_prev_id = ?", array($strOldPrevid));

        foreach($arrFiles as $objOneFile) {
            $strNewFilename = basename($objOneFile->getStrFilename());
            echo "  searching image/folder ".$strNewFilename."\n";

            foreach($arrOldFiles as $arrOneOldFile) {
                if(basename($arrOneOldFile["pic_filename"]) == $strNewFilename) {
                    $objOneFile->setStrName($arrOneOldFile["pic_name"]);
                    $objOneFile->setStrDescription($arrOneOldFile["pic_description"]);
                    $objOneFile->setStrSubtitle($arrOneOldFile["pic_subtitle"]);
                    $objOneFile->setIntHits((int)$arrOneOldFile["pic_hits"]);
                    $objOneFile->updateObjectToDb();

                    $this->moveComments($arrOneOldFile["pic_id"], $objOneFile->getSystemid());
                    $this->migrateRatings($arrOneOldFile["pic_id"], $objOneFile->getSystemid());

                    echo "...update succeeded\n";

                    if($objOneFile->getIntType() == class_module_mediamanager_file::$INT_TYPE_FOLDER)
                        $this->updateGalleryLevel($objOneFile->getSystemid(), $arrOneOldFile["pic_id"]);
                }
            }
        }
    }

    private function deleteGalleryLevel($strOldPrevid) {
        $arrOldFiles = $this->objDB->getPArray("SELECT * FROM  "._dbprefix_."system, "._dbprefix_."gallery_pic WHERE system_id = pic_id AND system_prev_id = ?", array($strOldPrevid));

        foreach($arrOldFiles as $arrOneFile) {
            $this->deleteGalleryLevel($arrOneFile["pic_id"]);

            $this->objDB->_pQuery("DELETE FROM "._dbprefix_."gallery_pic WHERE pic_id = ?", array($arrOneFile["pic_id"]));
            $this->deleteSystemRecord($arrOneFile["pic_id"]);
        }
    }















    private function updateFilemanager() {
        echo "Updating filemanager to new mediamanager structure...\n";
        echo "Checking installation state of mediamanager...\n";

        if(!in_array(_dbprefix_."filemanager", $this->objDB->getTables())) {
            echo "Filemanager table missing, no update required.\n";
            return true;
        }

        $strOldDefaultImagesRepo = $this->getValueOfSetting("_filemanager_default_imagesrepoid_");
        $strOldDefaultFilesRepo = $this->getValueOfSetting("_filemanager_default_filesrepoid_");

        echo "Migrating old filemanager repos to new mediamanager repos...\n";

        $strQuery = "SELECT * FROM "._dbprefix_."filemanager";
        $arrRows = $this->objDB->getPArray($strQuery, array());
        foreach($arrRows as $arrOneRow) {
            if(!validateSystemid($arrOneRow["filemanager_foreign_id"])) {

                echo "migrating old repo ".$arrOneRow["filemanager_name"]."\n";
                echo "  old path: ".$arrOneRow["filemanager_path"]."\n";

                //convert the path
                $strPath = $this->convertOldPath($arrOneRow["filemanager_path"]);
                echo "  new path: ".$strPath."\n";

                $objRepo = new class_module_mediamanager_repo();
                $objRepo->setStrPath($strPath);
                $objRepo->setStrTitle($arrOneRow["filemanager_name"]);
                $objRepo->setStrViewFilter($arrOneRow["filemanager_view_filter"]);
                $objRepo->setStrUploadFilter($arrOneRow["filemanager_upload_filter"]);
                $objRepo->updateObjectToDb();
                $objRepo->syncRepo();

                if($arrOneRow["filemanager_id"] == $strOldDefaultFilesRepo) {
                    $objSetting = class_module_system_setting::getConfigByName("_mediamanager_default_filesrepoid_");
                    $objSetting->setStrValue($objRepo->getSystemid());
                    $objSetting->updateObjectToDb();
                    echo "  setting as default files repo\n";
                }

                if($arrOneRow["filemanager_id"] == $strOldDefaultImagesRepo) {
                    $objSetting = class_module_system_setting::getConfigByName("_mediamanager_default_imagesrepoid_");
                    $objSetting->setStrValue($objRepo->getSystemid());
                    $objSetting->updateObjectToDb();
                    echo "  setting as default images repo\n";
                }
            }

            echo "  deleting repo from tables\n";
            $strQuery = "DELETE FROM "._dbprefix_."filemanager WHERE filemanager_id = ?";
            $this->objDB->_pQuery($strQuery, array($arrOneRow["filemanager_id"]));
            $this->deleteSystemRecord($arrOneRow["filemanager_id"]);
        }


        echo "Deleting filemanager module...\n";
        $this->removeModule("filemanager");
        $this->objDB->_pQuery("DROP TABLE "._dbprefix_."filemanager", array());
        $this->removeSetting("_filemanager_default_imagesrepoid_");
        $this->removeSetting("_filemanager_default_filesrepoid_");
        $this->removeSetting("_filemanager_foldersize_");
        $this->removeSetting("_filemanager_show_foreign_");

        return true;
    }





    private function moveComments($strOldSystemid, $strNewSystemid) {
        $strQuery = "UPDATE "._dbprefix_."postacomment
                        SET postacomment_systemid = ?
                      WHERE postacomment_systemid = ?";

        return $this->objDB->_pQuery($strQuery, array($strNewSystemid, $strOldSystemid));
    }




    private function removeModule($strModuleName) {
        $strQuery = "SELECT * FROM "._dbprefix_."system_module WHERE module_name = ?";
        $arrRow = $this->objDB->getPRow($strQuery, array($strModuleName));

        $strQuery = "DELETE FROM "._dbprefix_."system_module WHERE module_id = ?";
        $this->objDB->_pQuery($strQuery, array($arrRow["module_id"]));
        $this->deleteSystemRecord($arrRow["module_id"]);
    }

    private function removeSetting($strName) {
        $this->objDB->_pQuery("DELETE FROM "._dbprefix_."system_config WHERE system_config_name = ? ", array($strName));
    }

    private function getValueOfSetting($strName) {
        $strValue = $this->objDB->getPRow("SELECT * FROM "._dbprefix_."system_config WHERE system_config_name = ? ", array($strName));
        $strValue = $strValue["system_config_value"];
        return $strValue;
    }

    private function deleteSystemRecord($strSystemid) {
        //Start a tx before deleting anything
        $strQuery = "DELETE FROM "._dbprefix_."system WHERE system_id = ?";
        $this->objDB->_pQuery($strQuery, array($strSystemid));

        $strQuery = "DELETE FROM "._dbprefix_."system_right WHERE right_id = ?";
        $this->objDB->_pQuery($strQuery, array($strSystemid));

        $strQuery = "DELETE FROM "._dbprefix_."system_date WHERE system_date_id = ?";
        $this->objDB->_pQuery($strQuery, array($strSystemid));
    }

    private function convertOldPath($strOldPath) {
        if(uniStripos($strOldPath, "/portal/downloads") !== false) {
            return uniStrReplace("/portal/downloads", "/files/downloads", $strOldPath);
        }

        if(uniStripos($strOldPath, "/portal/pics") !== false) {
            return uniStrReplace("/portal/pics", "/files/images", $strOldPath);
        }

        return $strOldPath;
    }

    private function migrateRatings($strOldSystemid, $strNewSystemid) {
        if(class_module_system_module::getModuleByName("rating") != null) {
            $strQuery = "UPDATE "._dbprefix_."rating SET rating_systemid = ? WHERE rating_systemid = ?";
            $this->objDB->_pQuery($strQuery, array($strNewSystemid, $strOldSystemid));
        }
    }
}

$objUpdate = new class_v3_v4_postupdate();
$objUpdate->postUpdate();
