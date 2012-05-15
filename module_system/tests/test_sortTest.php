<?php

require_once (__DIR__."/../../module_system/system/class_testbase.php");

class class_test_sort extends class_testbase {



    public function testSorting() {
        echo "testing sorting-behaviour....\n";


        $objRootPage = new class_module_pages_page();

        $objRootPage->setStrName("test1");
        $objRootPage->updateObjectToDb();


        for($intI = 1; $intI <= 10; $intI++) {
            $objPage = new class_module_pages_page();
            $objPage->setStrName("sortsubpage_".$intI);
            $objPage->updateObjectToDb($objRootPage->getSystemid());
        }


        //check initial sort
        $arrPages = class_module_pages_folder::getPagesAndFolderList($objRootPage->getSystemid());
        for($intI = 1; $intI <= count($arrPages); $intI++) {
            $objPage = $arrPages[$intI-1];
            $this->assertEquals($objPage->getStrName(), "sortsubpage_".$intI);
        }


        //shift record 7 to pos 1
        $objPage = $arrPages[6];
        $objPage->setAbsolutePosition(1);
        //new key:   0, 1, 2, 3, 4, 5, 6, 7, 8, 9
        //new order: 7, 1, 2, 3, 4, 5, 6, 8, 9, 10
        $arrPages = class_module_pages_folder::getPagesAndFolderList($objRootPage->getSystemid());
        $objPage = $arrPages[0]; $this->assertEquals($objPage->getStrName(), "sortsubpage_7");  $this->assertEquals(1, $objPage->getIntSort());
        $objPage = $arrPages[1]; $this->assertEquals($objPage->getStrName(), "sortsubpage_1");  $this->assertEquals(2, $objPage->getIntSort());
        $objPage = $arrPages[2]; $this->assertEquals($objPage->getStrName(), "sortsubpage_2");  $this->assertEquals(3, $objPage->getIntSort());
        $objPage = $arrPages[3]; $this->assertEquals($objPage->getStrName(), "sortsubpage_3");  $this->assertEquals(4, $objPage->getIntSort());
        $objPage = $arrPages[4]; $this->assertEquals($objPage->getStrName(), "sortsubpage_4");  $this->assertEquals(5, $objPage->getIntSort());
        $objPage = $arrPages[5]; $this->assertEquals($objPage->getStrName(), "sortsubpage_5");  $this->assertEquals(6, $objPage->getIntSort());
        $objPage = $arrPages[6]; $this->assertEquals($objPage->getStrName(), "sortsubpage_6");  $this->assertEquals(7, $objPage->getIntSort());
        $objPage = $arrPages[7]; $this->assertEquals($objPage->getStrName(), "sortsubpage_8");  $this->assertEquals(8, $objPage->getIntSort());
        $objPage = $arrPages[8]; $this->assertEquals($objPage->getStrName(), "sortsubpage_9");  $this->assertEquals(9, $objPage->getIntSort());
        $objPage = $arrPages[9]; $this->assertEquals($objPage->getStrName(), "sortsubpage_10"); $this->assertEquals(10, $objPage->getIntSort());


        //shirt record 3 to pos 8
        $objPage = $arrPages[2];
        $objPage->setAbsolutePosition(8);
        //old order: 7, 1, 2, 3, 4, 5, 6, 8, 9, 10
        //logical    1, 2, 3, 4, 5, 6, 7, 8, 9, 10
        //new key:   0, 1, 2, 3, 4, 5, 6, 7, 8, 9
        //new order: 7, 1, 3, 4, 5, 6, 8, 2, 9, 10
        $arrPages = class_module_pages_folder::getPagesAndFolderList($objRootPage->getSystemid());
        $objPage = $arrPages[0]; $this->assertEquals($objPage->getStrName(), "sortsubpage_7");  $this->assertEquals(1, $objPage->getIntSort());
        $objPage = $arrPages[1]; $this->assertEquals($objPage->getStrName(), "sortsubpage_1");  $this->assertEquals(2, $objPage->getIntSort());
        $objPage = $arrPages[2]; $this->assertEquals($objPage->getStrName(), "sortsubpage_3");  $this->assertEquals(3, $objPage->getIntSort());
        $objPage = $arrPages[3]; $this->assertEquals($objPage->getStrName(), "sortsubpage_4");  $this->assertEquals(4, $objPage->getIntSort());
        $objPage = $arrPages[4]; $this->assertEquals($objPage->getStrName(), "sortsubpage_5");  $this->assertEquals(5, $objPage->getIntSort());
        $objPage = $arrPages[5]; $this->assertEquals($objPage->getStrName(), "sortsubpage_6");  $this->assertEquals(6, $objPage->getIntSort());
        $objPage = $arrPages[6]; $this->assertEquals($objPage->getStrName(), "sortsubpage_8");  $this->assertEquals(7, $objPage->getIntSort());
        $objPage = $arrPages[7]; $this->assertEquals($objPage->getStrName(), "sortsubpage_2");  $this->assertEquals(8, $objPage->getIntSort());
        $objPage = $arrPages[8]; $this->assertEquals($objPage->getStrName(), "sortsubpage_9");  $this->assertEquals(9, $objPage->getIntSort());
        $objPage = $arrPages[9]; $this->assertEquals($objPage->getStrName(), "sortsubpage_10"); $this->assertEquals(10, $objPage->getIntSort());

        $objRootPage->deleteObject();

    }




}

