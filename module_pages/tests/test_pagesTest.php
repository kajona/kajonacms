<?php

require_once __DIR__."../../../core/module_system/system/Testbase.php";
class class_test_pages extends class_testbase {


    public function test() {

        $objDB = class_carrier::getInstance()->getObjDB();

        echo "testing module_pages\n";

        //pages at startup:
        $intPagesAtStartup = count(class_module_pages_folder::getPagesInFolder(class_module_system_module::getModuleByName("pages")->getSystemid()));
        $objDB->flushQueryCache();


        echo "\tcreate a new folder...\n";
        $objFolder = new class_module_pages_folder();
        $objFolder->setStrName("autotest");
        $objFolder->updateObjectToDb(class_module_system_module::getModuleByName("pages")->getSystemid());
        $strTestFolderID = $objFolder->getSystemid();

        echo "\tcreate 10 folders using the model...\n";
        $arrFoldersCreated = array();
        for($intI = 0; $intI < 10; $intI++) {
            $objFolder = new class_module_pages_folder();
            $objFolder->setStrName("testfolder_".$intI);
            $objFolder->updateObjectToDb($strTestFolderID);
            $strFolderID = $objFolder->getSystemid();
            $arrFoldersCreated[] = $strFolderID;
            $objFolder = new class_module_pages_folder($strFolderID);
            $this->assertEquals($objFolder->getStrName(), "testfolder_".$intI, __FILE__." checkNameOfFolderCreated");
            $this->assertEquals($objFolder->getPrevId(), $strTestFolderID, __FILE__." checkPrevIDOfFolderCreated");
        }

        $arrFoldersAtLevel = class_module_pages_folder::getFolderList($strTestFolderID);
        $this->assertEquals(count($arrFoldersAtLevel), 10, __FILE__." checkNrOfFoldersCreatedByModel");


        echo "\tcreate 10 pages on root level using the model...\n";
        $arrPagesCreated = array();
        for($intI = 0; $intI < 10; $intI++) {
            $objPages = new class_module_pages_page();
            $objPages->setStrName("autotest_".$intI);
            $objPages->setStrTemplate("standard.tpl");
            $objPages->updateObjectToDb();
            $strPageID = $objPages->getSystemid();
            $arrPagesCreated[] = $strPageID;
            $objPage = new class_module_pages_page($strPageID);
            $this->assertEquals($objPage->getStrName(), "autotest_".$intI, __FILE__." checkNameOfPageCreated");
            $this->assertEquals($objPage->getStrTemplate(), "standard.tpl", __FILE__." checkTemplateOfPageCreated");
        }

        $arrPagesAtLevel = class_module_pages_folder::getPagesInFolder(class_module_system_module::getModuleByName("pages")->getSystemid());
        $this->assertEquals(count($arrPagesAtLevel), 10 + $intPagesAtStartup, __FILE__." checkNrOfPagesCreatedByModel");

        echo "\tdeleting pages created...\n";
        foreach($arrPagesCreated as $strOnePageID) {
            $objDelPage = new class_module_pages_page($strOnePageID);
            $objDelPage->deleteObjectFromDatabase();
            $objDB->flushQueryCache();
        }
        echo "\tcheck number of pages installed...\n";
        $arrPagesAtLevel = class_module_pages_folder::getPagesInFolder(class_module_system_module::getModuleByName("pages")->getSystemid());
        $this->assertEquals(count($arrPagesAtLevel), $intPagesAtStartup, __FILE__." checkNrOfPagesAtLevel");

        echo "\tdeleting folders created...\n";
        foreach($arrFoldersCreated as $strOneFolderID) {
            $objFolder = new class_module_pages_folder($strOneFolderID);
            $objFolder->deleteObjectFromDatabase();
            $objDB->flushQueryCache();
        }
        echo "\tcheck number of folders installed...\n";
        $arrFoldersAtLevel = class_module_pages_folder::getFolderList($strTestFolderID);
        $this->assertEquals(count($arrFoldersAtLevel), 0, __FILE__." checkNrOfFoldersAtLevel");


        echo"\tdeleting folder...\n";
        $objFolder = new class_module_pages_folder($strTestFolderID);
        $objFolder->deleteObjectFromDatabase();

    }


    public function testCopyPage() {

        $strTitle = generateSystemid();


        $objPage = new class_module_pages_page();
        $objPage->setStrName($strTitle);
        $objPage->setStrBrowsername(generateSystemid());
        $objPage->setStrSeostring(generateSystemid());
        $objPage->setStrDesc(generateSystemid());
        $objPage->setStrTemplate("standard.tpl");
        $objPage->updateObjectToDb();

        $strOldSystemid = $objPage->getSystemid();

        $objPagelement = new class_module_pages_pageelement();
        $objPagelement->setStrPlaceholder("text_paragraph");
        $objPagelement->setStrName("text");
        $objPagelement->setStrElement("paragraph");
        $objPagelement->updateObjectToDb($objPage->getSystemid());
        $objPagelement = new class_module_pages_pageelement($objPagelement->getSystemid());

        //and finally create the object
        /** @var $objElement class_element_paragraph_admin */
        $objElement = $objPagelement->getConcreteAdminInstance();

        $objElement->setStrTitle("autotest");

        $objElement->doBeforeSaveToDb();
        $objElement->updateForeignElement();
        $objElement->doAfterSaveToDb();


        //copy the page itself
        $objPage->copyObject();

        $strNewSystemid = $objPage->getSystemid();


        $this->flushDBCache();

        $objOldPage = new class_module_pages_page($strOldSystemid);
        $objNewPage = new class_module_pages_page($strNewSystemid);

        $this->assertNotEquals($objOldPage->getStrName(), $objNewPage->getStrName());
        $this->assertEquals($objOldPage->getStrBrowsername(), $objNewPage->getStrBrowsername());
        $this->assertEquals($objOldPage->getStrSeostring(), $objNewPage->getStrSeostring());
        $this->assertEquals($objOldPage->getStrDesc(), $objNewPage->getStrDesc());
        $this->assertEquals($objOldPage->getStrTemplate(), $objNewPage->getStrTemplate());

        $arrOldElements = class_module_pages_pageelement::getAllElementsOnPage($strOldSystemid);
        $arrNewElements = class_module_pages_pageelement::getAllElementsOnPage($strNewSystemid);

        $this->assertEquals(count($arrOldElements), count($arrNewElements));
        $this->assertEquals(1, count($arrOldElements));
        $this->assertEquals(1, count($arrNewElements));

        $objOldElement = $arrOldElements[0];
        $objNewElement = $arrNewElements[0];

        $this->assertEquals($objOldElement->getStrPlaceholder(), $objNewElement->getStrPlaceholder());
        $this->assertEquals($objOldElement->getStrLanguage(), $objNewElement->getStrLanguage());
        $this->assertEquals($objOldElement->getStrElement(), $objNewElement->getStrElement());

        /** @var class_element_paragraph_admin $objOldElementInstance */
        $objOldElementInstance = $objOldElement->getConcreteAdminInstance();
        $arrOldElementData = $objOldElementInstance->loadElementData();

        /** @var class_element_paragraph_admin $objNewElementInstance */
        $objNewElementInstance = $objNewElement->getConcreteAdminInstance();
        $arrNewElementData = $objNewElementInstance->loadElementData();

        $this->assertNotEquals($arrOldElementData["content_id"], $arrNewElementData["content_id"]);
        $this->assertEquals($arrOldElementData["paragraph_title"], $arrNewElementData["paragraph_title"]);

        $this->assertEquals($objOldElementInstance->getStrTitle(), $objNewElementInstance->getStrTitle());
        $this->assertEquals($objOldElementInstance->getStrTitle(), "autotest");
        $this->assertEquals($objNewElementInstance->getStrTitle(), "autotest");


        $objNewPage->deleteObjectFromDatabase();
        $objOldPage->deleteObjectFromDatabase();

    }

}



