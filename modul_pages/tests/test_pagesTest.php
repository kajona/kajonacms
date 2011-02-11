<?php

require_once (dirname(__FILE__)."/../system/class_testbase.php");

class class_test_pages extends class_testbase  {



    public function test() {

        $objDB = class_carrier::getInstance()->getObjDB();

        echo "testing module_pages\n";

        //pages at startup:
        $intPagesAtStartup = count(class_modul_pages_folder::getPagesInFolder( class_modul_system_module::getModuleByName("pages")->getSystemid() ));
        $objDB->flushQueryCache();


        echo "\tcreate a new folder...\n";
        $objFolder = new class_modul_pages_folder();
        $objFolder->setStrName("autotest");
        $objFolder->updateObjectToDb(class_modul_system_module::getModuleByName("pages")->getSystemid());
        $strTestFolderID = $objFolder->getSystemid();

        echo "\tcreate 100 folders using the model...\n";
        $arrFoldersCreated = array();
        for($intI =0; $intI < 100; $intI++) {
            $objFolder = new class_modul_pages_folder();
            $objFolder->setStrName("testfolder_".$intI);
            $objFolder->updateObjectToDb($strTestFolderID);
            $strFolderID = $objFolder->getSystemid();
            $arrFoldersCreated[] = $strFolderID;
            $objFolder = new class_modul_pages_folder($strFolderID);
            $this->assertEquals($objFolder->getStrName(), "testfolder_".$intI, __FILE__." checkNameOfFolderCreated");
            $this->assertEquals($objFolder->getPrevId(), $strTestFolderID, __FILE__." checkPrevIDOfFolderCreated");
        }

        $arrFoldersAtLevel = class_modul_pages_folder::getFolderList($strTestFolderID);
        $this->assertEquals(count($arrFoldersAtLevel), 100, __FILE__." checkNrOfFoldersCreatedByModel");


        echo "\tcreate 100 pages on root level using the model...\n";
        $arrPagesCreated = array();
        for($intI =0; $intI < 100; $intI++) {
            $objPages = new class_modul_pages_page();
            $objPages->setStrName("autotest_".$intI);
            $objPages->setStrTemplate("kajona_demo.tpl");
            $objPages->updateObjectToDb();
            $strPageID = $objPages->getSystemid();
            $arrPagesCreated[] = $strPageID;
            $objPage = new class_modul_pages_page($strPageID);
            $this->assertEquals($objPage->getStrName(), "autotest_".$intI, __FILE__." checkNameOfPageCreated");
            $this->assertEquals($objPage->getStrTemplate(), "kajona_demo.tpl", __FILE__." checkTemplateOfPageCreated");
        }

        $arrPagesAtLevel = class_modul_pages_folder::getPagesInFolder(class_modul_system_module::getModuleByName("pages")->getSystemid());
        $this->assertEquals(count($arrPagesAtLevel), 100+$intPagesAtStartup, __FILE__." checkNrOfPagesCreatedByModel");

        echo "\tdeleting pages created...\n";
        foreach($arrPagesCreated as $strOnePageID) {
            class_modul_pages_page::deletePage($strOnePageID);
            $objDB->flushQueryCache();
        }
        echo "\tcheck number of pages installed...\n";
        $arrPagesAtLevel = class_modul_pages_folder::getPagesInFolder(class_modul_system_module::getModuleByName("pages")->getSystemid());
        $this->assertEquals(count($arrPagesAtLevel), $intPagesAtStartup, __FILE__." checkNrOfPagesAtLevel");

        echo "\tdeleting folders created...\n";
        foreach($arrFoldersCreated as $strOneFolderID) {
            $objFolder = new class_modul_pages_folder($strOneFolderID);
            $objFolder->deleteFolder();
            $objDB->flushQueryCache();
        }
        echo "\tcheck number of folders installed...\n";
        $arrFoldersAtLevel = class_modul_pages_folder::getFolderList($strTestFolderID);
        $this->assertEquals(count($arrFoldersAtLevel), 0, __FILE__." checkNrOfFoldersAtLevel");


        echo "\ttesting to copy a page...\n";
        $objOriginalPage = class_modul_pages_page::getPageByName("index");
        $this->assertTrue($objOriginalPage->copyPage(), __FILE__." checkCopyPageCopy");

        $objDB->flushQueryCache();
        $objCopy = class_modul_pages_page::getPageByName("index_1");
        $this->assertTrue($objCopy->getSystemid() != "", __FILE__." checkCopyPageHasSysid");
        $this->assertEquals($objOriginalPage->getNumberOfElementsOnPage(), $objCopy->getNumberOfElementsOnPage(), __FILE__." checkCopyPageNrOfElements");

        $arrOrigElements = class_modul_pages_pageelement::getAllElementsOnPage($objOriginalPage->getSystemid());
        $arrCopyElements = class_modul_pages_pageelement::getAllElementsOnPage($objCopy->getSystemid());

        foreach ($arrOrigElements as $intKey => $objElement) {
            $this->assertEquals($arrOrigElements[$intKey]->getStrName(),$arrCopyElements[$intKey]->getStrName(),__FILE__." checkCopyPageElementName");
        }


        echo"\tdeleting folder...\n";
        $objFolder = new class_modul_pages_folder($strTestFolderID);
        $objFolder->deleteFolder();

    }

}



?>