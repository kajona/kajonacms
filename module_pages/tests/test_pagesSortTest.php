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


    public function testSortAtPlaceholder() {

        $objPage = new class_module_pages_page();
        $objPage->setStrName("sortTest");
        $objPage->updateObjectToDb();

        $objLangugage = new class_module_languages_language();

        $objPagelementb1 = new class_module_pages_pageelement();
        $objPagelementb1->setStrPlaceholder("b_test");
        $objPagelementb1->setStrName("b");
        $objPagelementb1->setStrElement("row");
        $objPagelementb1->setStrLanguage($objLangugage->getStrAdminLanguageToWorkOn());
        $objPagelementb1->updateObjectToDb($objPage->getSystemid());

        $objPagelementb2 = new class_module_pages_pageelement();
        $objPagelementb2->setStrPlaceholder("b_test");
        $objPagelementb2->setStrName("b");
        $objPagelementb2->setStrElement("row");
        $objPagelementb2->setStrLanguage($objLangugage->getStrAdminLanguageToWorkOn());
        $objPagelementb2->updateObjectToDb($objPage->getSystemid());

        $objPagelementa1 = new class_module_pages_pageelement();
        $objPagelementa1->setStrPlaceholder("a_test");
        $objPagelementa1->setStrName("a");
        $objPagelementa1->setStrElement("row");
        $objPagelementa1->setStrLanguage($objLangugage->getStrAdminLanguageToWorkOn());
        $objPagelementa1->updateObjectToDb($objPage->getSystemid());

        $objPagelementd1 = new class_module_pages_pageelement();
        $objPagelementd1->setStrPlaceholder("d_test");
        $objPagelementd1->setStrName("d");
        $objPagelementd1->setStrElement("row");
        $objPagelementd1->setStrLanguage($objLangugage->getStrAdminLanguageToWorkOn());
        $objPagelementd1->updateObjectToDb($objPage->getSystemid());

        $objPagelementd2 = new class_module_pages_pageelement();
        $objPagelementd2->setStrPlaceholder("d_test");
        $objPagelementd2->setStrName("d");
        $objPagelementd2->setStrElement("row");
        $objPagelementd2->setStrLanguage($objLangugage->getStrAdminLanguageToWorkOn());
        $objPagelementd2->updateObjectToDb($objPage->getSystemid());

        $objPagelementd3 = new class_module_pages_pageelement();
        $objPagelementd3->setStrPlaceholder("d_test");
        $objPagelementd3->setStrName("d");
        $objPagelementd3->setStrElement("row");
        $objPagelementd3->setStrLanguage($objLangugage->getStrAdminLanguageToWorkOn());
        $objPagelementd3->updateObjectToDb($objPage->getSystemid());


        $this->flushDBCache();
        $arrElements = class_module_pages_pageelement::getElementsByPlaceholderAndPage($objPage->getSystemid(), "b_test", $objLangugage->getStrAdminLanguageToWorkOn(), false);
        $this->assertEquals(2, count($arrElements));
        $this->assertEquals(1, $arrElements[0]->getIntSort()); $this->assertEquals($objPagelementb1->getSystemid(), $arrElements[0]->getSystemid());
        $this->assertEquals(2, $arrElements[1]->getIntSort()); $this->assertEquals($objPagelementb2->getSystemid(), $arrElements[1]->getSystemid());


        $arrElements = class_module_pages_pageelement::getElementsByPlaceholderAndPage($objPage->getSystemid(), "a_test", $objLangugage->getStrAdminLanguageToWorkOn(), false);
        $this->assertEquals(1, count($arrElements));
        $this->assertEquals(1, $arrElements[0]->getIntSort()); $this->assertEquals($objPagelementa1->getSystemid(), $arrElements[0]->getSystemid());

        $arrElements = class_module_pages_pageelement::getElementsByPlaceholderAndPage($objPage->getSystemid(), "d_test", $objLangugage->getStrAdminLanguageToWorkOn(), false);
        $this->assertEquals(3, count($arrElements));
        $this->assertEquals(1, $arrElements[0]->getIntSort()); $this->assertEquals($objPagelementd1->getSystemid(), $arrElements[0]->getSystemid());
        $this->assertEquals(2, $arrElements[1]->getIntSort()); $this->assertEquals($objPagelementd2->getSystemid(), $arrElements[1]->getSystemid());
        $this->assertEquals(3, $arrElements[2]->getIntSort()); $this->assertEquals($objPagelementd3->getSystemid(), $arrElements[2]->getSystemid());


        $objPagelementb2 = new class_module_pages_pageelement($objPagelementb2->getSystemid());
        $objPagelementb2->setAbsolutePosition(1);

        $this->flushDBCache();

        $arrElements = class_module_pages_pageelement::getElementsByPlaceholderAndPage($objPage->getSystemid(), "b_test", $objLangugage->getStrAdminLanguageToWorkOn(), false);
        $this->assertEquals(2, count($arrElements));
        $this->assertEquals(1, $arrElements[0]->getIntSort()); $this->assertEquals($objPagelementb2->getSystemid(), $arrElements[0]->getSystemid());
        $this->assertEquals(2, $arrElements[1]->getIntSort()); $this->assertEquals($objPagelementb1->getSystemid(), $arrElements[1]->getSystemid());

        $objPagelementd1 = new class_module_pages_pageelement($objPagelementd1->getSystemid());
        $objPagelementd1->setPosition("down");

        $this->flushDBCache();

        $arrElements = class_module_pages_pageelement::getElementsByPlaceholderAndPage($objPage->getSystemid(), "d_test", $objLangugage->getStrAdminLanguageToWorkOn(), false);
        $this->assertEquals(3, count($arrElements));
        $this->assertEquals(1, $arrElements[0]->getIntSort()); $this->assertEquals($objPagelementd2->getSystemid(), $arrElements[0]->getSystemid());
        $this->assertEquals(2, $arrElements[1]->getIntSort()); $this->assertEquals($objPagelementd1->getSystemid(), $arrElements[1]->getSystemid());
        $this->assertEquals(3, $arrElements[2]->getIntSort()); $this->assertEquals($objPagelementd3->getSystemid(), $arrElements[2]->getSystemid());

        $objPage->deleteObject();
    }
}



