<?php

require_once (__DIR__."/../../module_system/system/class_testbase.php");

class class_test_pageimportexportTest extends class_testbase
{
    protected function tearDown()
    {
        parent::tearDown();
        class_carrier::getInstance()->getObjRights()->setBitTestMode(false);
    }


    public function testImportExport()
    {

        class_carrier::getInstance()->getObjRights()->setBitTestMode(true);
        $strName = generateSystemid();
        $strBrowsername = generateSystemid();
        $strSeoString = generateSystemid();
        $strDesc = generateSystemid();


        $objPage = new class_module_pages_page();
        $objPage->setStrName($strName);
        $objPage->setStrBrowsername($strBrowsername);
        $objPage->setStrSeostring($strSeoString);
        $objPage->setStrDesc($strDesc);
        $objPage->setStrTemplate("standard.tpl");
        $objPage->updateObjectToDb();

        $strPagesystemid = $objPage->getSystemid();


        $objPagelement = new class_module_pages_pageelement();
        $objPagelement->setStrPlaceholder("text_paragraph");
        $objPagelement->setStrName("text");
        $objPagelement->setStrElement("paragraph");
        $objPagelement->updateObjectToDb($objPage->getSystemid());

        $objElement = new class_element_paragraph_admin($objPagelement->getSystemid());
        $objElement->setSystemid($objPagelement->getSystemid());
        $objElement->loadElementData();
        $objElement->setStrTitle("para_title");
        $objElement->updateForeignElement();
        $objPagelement = new class_module_pages_pageelement($objPagelement->getSystemid());


        class_carrier::getInstance()->setParam("pageExport", $strName);
        $objPageExport = new class_systemtask_pageexport();
        $objPageExport->executeTask();

        $objPage->deleteObjectFromDatabase();
        class_orm_rowcache::flushCache();
        class_db::getInstance()->flushQueryCache();

        $this->assertNull(class_module_pages_page::getPageByName($strName));

        $this->assertFileExists(_realpath_._projectpath_."/temp/".$strPagesystemid.".xml");

        class_carrier::getInstance()->setParam("pageimport_file", _projectpath_."/temp/".$strPagesystemid.".xml");
        $objImport = new class_systemtask_pageimport();
        $objImport->executeTask();

        $objPage = class_module_pages_page::getPageByName($strName);
        $this->assertNotNull($objPage);

        $this->assertEquals($objPage->getStrName(), $strName);
        $this->assertEquals($objPage->getStrDesc(), $strDesc);
        $this->assertEquals($objPage->getStrSeostring(), $strSeoString);
        $this->assertEquals($objPage->getStrBrowsername(), $strBrowsername);

        $objElements = class_module_pages_pageelement::getAllElementsOnPage($objPage->getSystemid());

        $this->assertEquals(1, count($objElements));
        $objElements = $objElements[0];
        $this->assertEquals($objElements->getStrClassAdmin(), "class_element_paragraph_admin.php");


        $objElement = $objElements->getConcreteAdminInstance();
        $objElement->setSystemid($objElements->getSystemid());
        $objElement->loadElementData();

        $this->assertEquals("para_title", $objElement->getStrTitle());


        $objPage->deleteObjectFromDatabase();
    }
}

