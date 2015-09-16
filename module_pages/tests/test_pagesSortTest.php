<?php

require_once (__DIR__."/../../module_system/system/class_testbase.php");

class class_test_pagesSortTest extends class_testbase {

    public function testPagesSortOnPrevIdChange() {

        $objRootPage = $this->createObject("class_module_pages_page", "", array(), array("strName" => "pagesSortTest"));
        $objSubPage1 = $this->createObject("class_module_pages_page", $objRootPage->getSystemid(), array(), array("strName" => "pagesSortTest"));
        $objSubPage2 = $this->createObject("class_module_pages_page", $objRootPage->getSystemid(), array(), array("strName" => "pagesSortTest"));

        $objLangugage = new class_module_languages_language();
        $objPagelement1At1 = $this->createObject("class_module_pages_pageelement", $objSubPage1->getSystemid(), array(), array("strPlaceholder" => "headline_row", "strName" => "headline1", "strElement" => "row", "strLanguage" => $objLangugage->getStrAdminLanguageToWorkOn()));
        $objPagelement2At1 = $this->createObject("class_module_pages_pageelement", $objSubPage1->getSystemid(), array(), array("strPlaceholder" => "headline_row", "strName" => "headline2", "strElement" => "row", "strLanguage" => $objLangugage->getStrAdminLanguageToWorkOn()));

        $objPagelement1At2 = $this->createObject("class_module_pages_pageelement", $objSubPage2->getSystemid(), array(), array("strPlaceholder" => "headline_row", "strName" => "headline1", "strElement" => "row", "strLanguage" => $objLangugage->getStrAdminLanguageToWorkOn()));
        $objPagelement2At2 = $this->createObject("class_module_pages_pageelement", $objSubPage2->getSystemid(), array(), array("strPlaceholder" => "headline_row", "strName" => "headline2", "strElement" => "row", "strLanguage" => $objLangugage->getStrAdminLanguageToWorkOn()));


        //validate sorts pre previd change
        $this->assertEquals(1, $objSubPage1->getIntSort());
        $this->assertEquals(2, $objSubPage2->getIntSort());

        $this->assertEquals(1, $objPagelement1At1->getIntSort());
        $this->assertEquals(2, $objPagelement2At1->getIntSort());

        $this->assertEquals(1, $objPagelement1At2->getIntSort());
        $this->assertEquals(2, $objPagelement2At2->getIntSort());

        $objSubPage2->updateObjectToDb($objSubPage1->getSystemid());


        $this->assertEquals(1, $objSubPage1->getIntSort(), "t1");
        $this->assertEquals(1, $objSubPage2->getIntSort(), "t2");

        $this->assertEquals(1, $objPagelement1At1->getIntSort(), "t3");
        $this->assertEquals(2, $objPagelement2At1->getIntSort(), "t4");

        $this->assertEquals(1, $objPagelement1At2->getIntSort(), "t5");
        $this->assertEquals(2, $objPagelement2At2->getIntSort(), "t6");


        $objRootPage->deleteObjectFromDatabase();
    }

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



        $objRootPage->deleteObjectFromDatabase();
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

        $objRootPage->deleteObjectFromDatabase();
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

        $objPage->deleteObjectFromDatabase();
    }


    public function testSortAtPlaceholderMultiLanguage() {

        $objPage = new class_module_pages_page();
        $objPage->setStrName("sortTest");
        $objPage->updateObjectToDb();

        $objPagelementb1 = new class_module_pages_pageelement();
        $objPagelementb1->setStrPlaceholder("b_test");
        $objPagelementb1->setStrName("b");
        $objPagelementb1->setStrElement("row");
        $objPagelementb1->setStrLanguage("a1");
        $objPagelementb1->updateObjectToDb($objPage->getSystemid());

        $objPagelementb2 = new class_module_pages_pageelement();
        $objPagelementb2->setStrPlaceholder("b_test");
        $objPagelementb2->setStrName("b");
        $objPagelementb2->setStrElement("row");
        $objPagelementb2->setStrLanguage("a1");
        $objPagelementb2->updateObjectToDb($objPage->getSystemid());

        $objPagelementbA2 = new class_module_pages_pageelement();
        $objPagelementbA2->setStrPlaceholder("b_test");
        $objPagelementbA2->setStrName("b");
        $objPagelementbA2->setStrElement("row");
        $objPagelementbA2->setStrLanguage("a2");
        $objPagelementbA2->updateObjectToDb($objPage->getSystemid());

        $objPagelementa1 = new class_module_pages_pageelement();
        $objPagelementa1->setStrPlaceholder("a_test");
        $objPagelementa1->setStrName("a");
        $objPagelementa1->setStrElement("row");
        $objPagelementa1->setStrLanguage("a1");
        $objPagelementa1->updateObjectToDb($objPage->getSystemid());

        $objPagelementd1 = new class_module_pages_pageelement();
        $objPagelementd1->setStrPlaceholder("d_test");
        $objPagelementd1->setStrName("d");
        $objPagelementd1->setStrElement("row");
        $objPagelementd1->setStrLanguage("a1");
        $objPagelementd1->updateObjectToDb($objPage->getSystemid());

        $objPagelementd2 = new class_module_pages_pageelement();
        $objPagelementd2->setStrPlaceholder("d_test");
        $objPagelementd2->setStrName("d");
        $objPagelementd2->setStrElement("row");
        $objPagelementd2->setStrLanguage("a1");
        $objPagelementd2->updateObjectToDb($objPage->getSystemid());

        $objPagelementdA2 = new class_module_pages_pageelement();
        $objPagelementdA2->setStrPlaceholder("d_test");
        $objPagelementdA2->setStrName("d");
        $objPagelementdA2->setStrElement("row");
        $objPagelementdA2->setStrLanguage("a2");
        $objPagelementdA2->updateObjectToDb($objPage->getSystemid());

        $objPagelementdA3 = new class_module_pages_pageelement();
        $objPagelementdA3->setStrPlaceholder("d_test");
        $objPagelementdA3->setStrName("d");
        $objPagelementdA3->setStrElement("row");
        $objPagelementdA3->setStrLanguage("a2");
        $objPagelementdA3->updateObjectToDb($objPage->getSystemid());

        $objPagelementd3 = new class_module_pages_pageelement();
        $objPagelementd3->setStrPlaceholder("d_test");
        $objPagelementd3->setStrName("d");
        $objPagelementd3->setStrElement("row");
        $objPagelementd3->setStrLanguage("a1");
        $objPagelementd3->updateObjectToDb($objPage->getSystemid());

        $this->flushDBCache();

        $arrElements = class_module_pages_pageelement::getElementsByPlaceholderAndPage($objPage->getSystemid(), "b_test", "a1", false);
        $this->assertEquals(2, count($arrElements));
        $this->assertEquals(1, $arrElements[0]->getIntSort()); $this->assertEquals($objPagelementb1->getSystemid(), $arrElements[0]->getSystemid());
        $this->assertEquals(2, $arrElements[1]->getIntSort()); $this->assertEquals($objPagelementb2->getSystemid(), $arrElements[1]->getSystemid());

        $arrElements = class_module_pages_pageelement::getElementsByPlaceholderAndPage($objPage->getSystemid(), "b_test", "a2", false);
        $this->assertEquals(1, count($arrElements));
        $this->assertEquals(1, $arrElements[0]->getIntSort()); $this->assertEquals($objPagelementbA2->getSystemid(), $arrElements[0]->getSystemid());


        $arrElements = class_module_pages_pageelement::getElementsByPlaceholderAndPage($objPage->getSystemid(), "a_test", "a1", false);
        $this->assertEquals(1, count($arrElements));
        $this->assertEquals(1, $arrElements[0]->getIntSort()); $this->assertEquals($objPagelementa1->getSystemid(), $arrElements[0]->getSystemid());

        $arrElements = class_module_pages_pageelement::getElementsByPlaceholderAndPage($objPage->getSystemid(), "d_test", "a1", false);
        $this->assertEquals(3, count($arrElements));
        $this->assertEquals(1, $arrElements[0]->getIntSort()); $this->assertEquals($objPagelementd1->getSystemid(), $arrElements[0]->getSystemid());
        $this->assertEquals(2, $arrElements[1]->getIntSort()); $this->assertEquals($objPagelementd2->getSystemid(), $arrElements[1]->getSystemid());
        $this->assertEquals(3, $arrElements[2]->getIntSort()); $this->assertEquals($objPagelementd3->getSystemid(), $arrElements[2]->getSystemid());

        $arrElements = class_module_pages_pageelement::getElementsByPlaceholderAndPage($objPage->getSystemid(), "d_test", "a2", false);
        $this->assertEquals(2, count($arrElements));
        $this->assertEquals(1, $arrElements[0]->getIntSort()); $this->assertEquals($objPagelementdA2->getSystemid(), $arrElements[0]->getSystemid());
        $this->assertEquals(2, $arrElements[1]->getIntSort()); $this->assertEquals($objPagelementdA3->getSystemid(), $arrElements[1]->getSystemid());


        $objPagelementb2 = new class_module_pages_pageelement($objPagelementb2->getSystemid());
        $objPagelementb2->setAbsolutePosition(1);

        $this->flushDBCache();

        $arrElements = class_module_pages_pageelement::getElementsByPlaceholderAndPage($objPage->getSystemid(), "b_test", "a1", false);
        $this->assertEquals(2, count($arrElements));
        $this->assertEquals(1, $arrElements[0]->getIntSort()); $this->assertEquals($objPagelementb2->getSystemid(), $arrElements[0]->getSystemid());
        $this->assertEquals(2, $arrElements[1]->getIntSort()); $this->assertEquals($objPagelementb1->getSystemid(), $arrElements[1]->getSystemid());

        $objPagelementd1 = new class_module_pages_pageelement($objPagelementd1->getSystemid());
        $objPagelementd1->setPosition("down");

        $this->flushDBCache();

        $arrElements = class_module_pages_pageelement::getElementsByPlaceholderAndPage($objPage->getSystemid(), "d_test", "a1", false);
        $this->assertEquals(3, count($arrElements));
        $this->assertEquals(1, $arrElements[0]->getIntSort()); $this->assertEquals($objPagelementd2->getSystemid(), $arrElements[0]->getSystemid());
        $this->assertEquals(2, $arrElements[1]->getIntSort()); $this->assertEquals($objPagelementd1->getSystemid(), $arrElements[1]->getSystemid());
        $this->assertEquals(3, $arrElements[2]->getIntSort()); $this->assertEquals($objPagelementd3->getSystemid(), $arrElements[2]->getSystemid());

        $arrElements = class_module_pages_pageelement::getElementsByPlaceholderAndPage($objPage->getSystemid(), "b_test", "a2", false);
        $this->assertEquals(1, count($arrElements));
        $this->assertEquals(1, $arrElements[0]->getIntSort()); $this->assertEquals($objPagelementbA2->getSystemid(), $arrElements[0]->getSystemid());

        $arrElements = class_module_pages_pageelement::getElementsByPlaceholderAndPage($objPage->getSystemid(), "d_test", "a2", false);
        $this->assertEquals(2, count($arrElements));
        $this->assertEquals(1, $arrElements[0]->getIntSort()); $this->assertEquals($objPagelementdA2->getSystemid(), $arrElements[0]->getSystemid());
        $this->assertEquals(2, $arrElements[1]->getIntSort()); $this->assertEquals($objPagelementdA3->getSystemid(), $arrElements[1]->getSystemid());


        $objPage->deleteObjectFromDatabase();
    }



    public function testPageElementDeleteSorting() {

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

        $objPagelementb3 = new class_module_pages_pageelement();
        $objPagelementb3->setStrPlaceholder("b_test");
        $objPagelementb3->setStrName("b");
        $objPagelementb3->setStrElement("row");
        $objPagelementb3->setStrLanguage($objLangugage->getStrAdminLanguageToWorkOn());
        $objPagelementb3->updateObjectToDb($objPage->getSystemid());


        $arrElements = class_module_pages_pageelement::getElementsByPlaceholderAndPage($objPage->getSystemid(), "b_test", $objLangugage->getStrAdminLanguageToWorkOn(), false);
        $this->assertEquals(3, count($arrElements));
        $this->assertEquals(1, $arrElements[0]->getIntSort()); $this->assertEquals($objPagelementb1->getSystemid(), $arrElements[0]->getSystemid());
        $this->assertEquals(2, $arrElements[1]->getIntSort()); $this->assertEquals($objPagelementb2->getSystemid(), $arrElements[1]->getSystemid());
        $this->assertEquals(3, $arrElements[2]->getIntSort()); $this->assertEquals($objPagelementb3->getSystemid(), $arrElements[2]->getSystemid());


        $objPagelementb2->deleteObjectFromDatabase();
        $this->flushDBCache();

        $arrElements = class_module_pages_pageelement::getElementsByPlaceholderAndPage($objPage->getSystemid(), "b_test", $objLangugage->getStrAdminLanguageToWorkOn(), false);
        $this->assertEquals(2, count($arrElements));
        $this->assertEquals(1, $arrElements[0]->getIntSort()); $this->assertEquals($objPagelementb1->getSystemid(), $arrElements[0]->getSystemid());
        $this->assertEquals(2, $arrElements[1]->getIntSort()); $this->assertEquals($objPagelementb3->getSystemid(), $arrElements[1]->getSystemid());


        $objPage->deleteObjectFromDatabase();

    }


    public function testPageElementDeleteSortingMultiLanguage() {

        $objPage = new class_module_pages_page();
        $objPage->setStrName("sortTest");
        $objPage->updateObjectToDb();



        $objPagelementb1 = new class_module_pages_pageelement();
        $objPagelementb1->setStrPlaceholder("b_test");
        $objPagelementb1->setStrName("b");
        $objPagelementb1->setStrElement("row");
        $objPagelementb1->setStrLanguage("a1");
        $objPagelementb1->updateObjectToDb($objPage->getSystemid());

        $objPagelementc1 = new class_module_pages_pageelement();
        $objPagelementc1->setStrPlaceholder("b_test");
        $objPagelementc1->setStrName("b");
        $objPagelementc1->setStrElement("row");
        $objPagelementc1->setStrLanguage("a2");
        $objPagelementc1->updateObjectToDb($objPage->getSystemid());

        $objPagelementb2 = new class_module_pages_pageelement();
        $objPagelementb2->setStrPlaceholder("b_test");
        $objPagelementb2->setStrName("b");
        $objPagelementb2->setStrElement("row");
        $objPagelementb2->setStrLanguage("a1");
        $objPagelementb2->updateObjectToDb($objPage->getSystemid());

        $objPagelementb3 = new class_module_pages_pageelement();
        $objPagelementb3->setStrPlaceholder("b_test");
        $objPagelementb3->setStrName("b");
        $objPagelementb3->setStrElement("row");
        $objPagelementb3->setStrLanguage("a1");
        $objPagelementb3->updateObjectToDb($objPage->getSystemid());

        $objPagelementc2 = new class_module_pages_pageelement();
        $objPagelementc2->setStrPlaceholder("b_test");
        $objPagelementc2->setStrName("b");
        $objPagelementc2->setStrElement("row");
        $objPagelementc2->setStrLanguage("a2");
        $objPagelementc2->updateObjectToDb($objPage->getSystemid());

        $objPagelementc3 = new class_module_pages_pageelement();
        $objPagelementc3->setStrPlaceholder("b_test");
        $objPagelementc3->setStrName("b");
        $objPagelementc3->setStrElement("row");
        $objPagelementc3->setStrLanguage("a2");
        $objPagelementc3->updateObjectToDb($objPage->getSystemid());


        $arrElements = class_module_pages_pageelement::getElementsByPlaceholderAndPage($objPage->getSystemid(), "b_test", "a1", false);
        $this->assertEquals(3, count($arrElements));
        $this->assertEquals(1, $arrElements[0]->getIntSort()); $this->assertEquals($objPagelementb1->getSystemid(), $arrElements[0]->getSystemid());
        $this->assertEquals(2, $arrElements[1]->getIntSort()); $this->assertEquals($objPagelementb2->getSystemid(), $arrElements[1]->getSystemid());
        $this->assertEquals(3, $arrElements[2]->getIntSort()); $this->assertEquals($objPagelementb3->getSystemid(), $arrElements[2]->getSystemid());


        $arrElements = class_module_pages_pageelement::getElementsByPlaceholderAndPage($objPage->getSystemid(), "b_test", "a2", false);
        $this->assertEquals(3, count($arrElements));
        $this->assertEquals(1, $arrElements[0]->getIntSort()); $this->assertEquals($objPagelementc1->getSystemid(), $arrElements[0]->getSystemid());
        $this->assertEquals(2, $arrElements[1]->getIntSort()); $this->assertEquals($objPagelementc2->getSystemid(), $arrElements[1]->getSystemid());
        $this->assertEquals(3, $arrElements[2]->getIntSort()); $this->assertEquals($objPagelementc3->getSystemid(), $arrElements[2]->getSystemid());


        $objPagelementb2->deleteObjectFromDatabase();
        $this->flushDBCache();

        $arrElements = class_module_pages_pageelement::getElementsByPlaceholderAndPage($objPage->getSystemid(), "b_test", "a1", false);
        $this->assertEquals(2, count($arrElements));
        $this->assertEquals(1, $arrElements[0]->getIntSort()); $this->assertEquals($objPagelementb1->getSystemid(), $arrElements[0]->getSystemid());
        $this->assertEquals(2, $arrElements[1]->getIntSort()); $this->assertEquals($objPagelementb3->getSystemid(), $arrElements[1]->getSystemid());


        $arrElements = class_module_pages_pageelement::getElementsByPlaceholderAndPage($objPage->getSystemid(), "b_test", "a2", false);
        $this->assertEquals(3, count($arrElements));
        $this->assertEquals(1, $arrElements[0]->getIntSort()); $this->assertEquals($objPagelementc1->getSystemid(), $arrElements[0]->getSystemid());
        $this->assertEquals(2, $arrElements[1]->getIntSort()); $this->assertEquals($objPagelementc2->getSystemid(), $arrElements[1]->getSystemid());
        $this->assertEquals(3, $arrElements[2]->getIntSort()); $this->assertEquals($objPagelementc3->getSystemid(), $arrElements[2]->getSystemid());


        $objPage->deleteObjectFromDatabase();

    }
}



