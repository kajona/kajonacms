<?php

class class_test_pages implements interface_testable {



    public function test() {

        $objDB = class_carrier::getInstance()->getObjDB();

        // includes
        include_once(_systempath_."/class_modul_pages_element.php");
        include_once(_systempath_."/class_modul_pages_folder.php");
        include_once(_systempath_."/class_modul_pages_page.php");
        include_once(_systempath_."/class_modul_pages_pageelement.php");
        

        echo "testing module_pages\n";

        //pages at startup:
        $intPagesAtStartup = count(class_modul_pages_folder::getPagesInFolder("0"));
        $objDB->flushQueryCache();


        echo "\tcreate a new folder...\n";
        $objFolder = new class_modul_pages_folder();
        $objFolder->setStrName("autotest");
        $objFolder->saveObjectToDb("0");
        $strTestFolderID = $objFolder->getSystemid();

        echo "\tcreate 100 folders using the model...\n";
        $arrFoldersCreated = array();
        for($intI =0; $intI < 100; $intI++) {
            $objFolder = new class_modul_pages_folder();
            $objFolder->setStrName("testfolder_".$intI);
            $objFolder->saveObjectToDb($strTestFolderID);
            $strFolderID = $objFolder->getSystemid();
            $arrFoldersCreated[] = $strFolderID;
            $objFolder = new class_modul_pages_folder($strFolderID);
            class_assertions::assertEqual($objFolder->getStrName(), "testfolder_".$intI, __FILE__." checkNameOfFolderCreated");
            class_assertions::assertEqual($objFolder->getPrevId(), $strTestFolderID, __FILE__." checkPrevIDOfFolderCreated");
        }

        $arrFoldersAtLevel = class_modul_pages_folder::getFolderList($strTestFolderID);
        class_assertions::assertEqual(count($arrFoldersAtLevel), 100, __FILE__." checkNrOfFoldersCreatedByModel");


        echo "\tcreate 100 pages on root level using the model...\n";
        $arrPagesCreated = array();
        for($intI =0; $intI < 100; $intI++) {
            $objPages = new class_modul_pages_page();
            $objPages->setStrName("autotest_".$intI);
            $objPages->setStrTemplate("kajona_demo.tpl");
            $objPages->saveObjectToDb();
            $strPageID = $objPages->getSystemid();
            $arrPagesCreated[] = $strPageID;
            $objPage = new class_modul_pages_page($strPageID);
            class_assertions::assertEqual($objPage->getStrName(), "autotest_".$intI, __FILE__." checkNameOfPageCreated");
            class_assertions::assertEqual($objPage->getStrTemplate(), "kajona_demo.tpl", __FILE__." checkTemplateOfPageCreated");
        }

        $arrPagesAtLevel = class_modul_pages_folder::getPagesInFolder("0");
        class_assertions::assertEqual(count($arrPagesAtLevel), 100+$intPagesAtStartup, __FILE__." checkNrOfPagesCreatedByModel");

        /**
         * TODO: Pages testen
         */

        echo "\tdeleting pages created...\n";
        foreach($arrPagesCreated as $strOnePageID) {
            class_modul_pages_page::deletePage($strOnePageID);
            $objDB->flushQueryCache();
        }
        echo "\tcheck number of pages installed...\n";
        $arrPagesAtLevel = class_modul_pages_folder::getPagesInFolder("0");
        class_assertions::assertEqual(count($arrPagesAtLevel), $intPagesAtStartup, __FILE__." checkNrOfPagesAtLevel");

        echo "\tdeleting folders created...\n";
        foreach($arrFoldersCreated as $strOneFolderID) {
            class_modul_pages_folder::deleteFolder($strOneFolderID);
            $objDB->flushQueryCache();
        }
        echo "\tcheck number of folders installed...\n";
        $arrFoldersAtLevel = class_modul_pages_folder::getFolderList($strTestFolderID);
        class_assertions::assertEqual(count($arrFoldersAtLevel), 0, __FILE__." checkNrOfFoldersAtLevel");


        echo "\ttesting to copy a page...\n";
        $objOriginalPage = class_modul_pages_page::getPageByName("index");
        class_assertions::assertTrue($objOriginalPage->copyPage(), __FILE__." checkCopyPageCopy");
        $objCopy = class_modul_pages_page::getPageByName("index_1");
        class_assertions::assertTrue($objCopy->getSystemid() != "", __FILE__." checkCopyPageHasSysid");
        class_assertions::assertEqual($objOriginalPage->getNumberOfElementsOnPage(), $objCopy->getNumberOfElementsOnPage(), __FILE__." checkCopyPageNrOfElements");

        $arrOrigElements = class_modul_pages_pageelement::getAllElementsOnPage($objOriginalPage->getSystemid());
        $arrCopyElements = class_modul_pages_pageelement::getAllElementsOnPage($objCopy->getSystemid());

        foreach ($arrOrigElements as $intKey => $objElement) {
            class_assertions::assertEqual($arrOrigElements[$intKey]->getStrName(),$arrCopyElements[$intKey]->getStrName(),__FILE__." checkCopyPageElementName");
        }




    }

}



?>