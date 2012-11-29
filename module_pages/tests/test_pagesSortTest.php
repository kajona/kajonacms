<?php

require_once (__DIR__."/../../module_system/system/class_testbase.php");

class class_test_pagesSortTest extends class_testbase {

    public function testPagesSortTest() {

        $objRootPage = new class_module_pages_page();
        $objRootPage->setStrName("pagesSortTest");
        $objRootPage->updateObjectToDb();


        $objSubPage1 = new class_module_pages_page();
        $objSubPage1->setStrName("pagesSortTest_1");
        $objSubPage1->updateObjectToDb($objRootPage->getSystemid());

        $objSubPage2 = new class_module_pages_page();
        $objSubPage2->setStrName("pagesSortTest_2");
        $objSubPage2->updateObjectToDb($objRootPage->getSystemid());

        //check basic sort
        $arrNodes = class_module_pages_folder::getPagesAndFolderList($objRootPage->getSystemid());
        for($intI = 1; $intI <= count($arrNodes); $intI++) {
            $this->assertEquals($intI, $arrNodes[$intI-1]->getIntSort());
        }

        //add elements tp page2
        $objPagelement = new class_module_pages_pageelement();
        $objPagelement->setStrPlaceholder("headline_row");
        $objPagelement->setStrName("headline");
        $objPagelement->setStrElement("row");
        $objPagelement->updateObjectToDb($objRootPage->getSystemid());

        $objPagelement = new class_module_pages_pageelement();
        $objPagelement->setStrPlaceholder("headline_row");
        $objPagelement->setStrName("headline");
        $objPagelement->setStrElement("row");
        $objPagelement->updateObjectToDb($objRootPage->getSystemid());


        $objSubPage3 = new class_module_pages_page();
        $objSubPage3->setStrName("pagesSortTest_3");
        $objSubPage3->updateObjectToDb($objRootPage->getSystemid());


        $this->flushDBCache();


        $arrNodes = class_module_pages_folder::getPagesAndFolderList($objRootPage->getSystemid());
        for($intI = 1; $intI <= count($arrNodes); $intI++) {
            $this->assertEquals($intI, $arrNodes[$intI-1]->getIntSort());
        }



        $objRootPage->deleteObject();
    }


    public function testCombinedFolderAndPagesSort() {
        $objRootPage = new class_module_pages_page();
        $objRootPage->setStrName("pagesSortTest2");
        $objRootPage->updateObjectToDb();

        $objSubPage1 = new class_module_pages_page();
        $objSubPage1->setStrName("pagesSortTest_1");
        $objSubPage1->updateObjectToDb($objRootPage->getSystemid());

        $objSubFolder1 = new class_module_pages_folder();
        $objSubFolder1->setStrName("subfolder1");
        $objSubFolder1->updateObjectToDb($objRootPage);

        $objSubPage2 = new class_module_pages_page();
        $objSubPage2->setStrName("pagesSortTest_2");
        $objSubPage2->updateObjectToDb($objRootPage->getSystemid());


        $arrNodes = class_module_pages_folder::getPagesAndFolderList($objRootPage->getSystemid());
        for($intI = 1; $intI <= count($arrNodes); $intI++) {
            $this->assertEquals($intI, $arrNodes[$intI-1]->getIntSort());
        }


        $this->flushDBCache();

        $objSubPage2->setAbsolutePosition(2);

        $this->flushDBCache();

        $arrNodes = class_module_pages_folder::getPagesAndFolderList($objRootPage->getSystemid());
        for($intI = 1; $intI <= count($arrNodes); $intI++) {
            $this->assertEquals($intI, $arrNodes[$intI-1]->getIntSort());
        }

        $this->assertEquals($arrNodes[0]->getSystemid(), $objSubPage1->getSystemid());
        $this->assertEquals($arrNodes[1]->getSystemid(), $objSubPage2->getSystemid());
        $this->assertEquals($arrNodes[2]->getSystemid(), $objSubFolder1->getSystemid());

        $objRootPage->deleteObject();
    }

}



