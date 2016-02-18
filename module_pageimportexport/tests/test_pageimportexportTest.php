<?php

use Kajona\Pageimportexport\Admin\Systemtasks\SystemtaskPageexport;
use Kajona\Pageimportexport\Admin\Systemtasks\SystemtaskPageimport;
use Kajona\Pages\Admin\Elements\ElementParagraphAdmin;
use Kajona\Pages\System\PagesPage;
use Kajona\Pages\System\PagesPageelement;

require_once __DIR__."../../../core/module_system/system/Testbase.php";

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


        $objPage = new PagesPage();
        $objPage->setStrName($strName);
        $objPage->setStrBrowsername($strBrowsername);
        $objPage->setStrSeostring($strSeoString);
        $objPage->setStrDesc($strDesc);
        $objPage->setStrTemplate("standard.tpl");
        $objPage->updateObjectToDb();

        $strPagesystemid = $objPage->getSystemid();


        $objPagelement = new PagesPageelement();
        $objPagelement->setStrPlaceholder("text_paragraph");
        $objPagelement->setStrName("text");
        $objPagelement->setStrElement("paragraph");
        $objPagelement->updateObjectToDb($objPage->getSystemid());

        $objElement = new ElementParagraphAdmin($objPagelement->getSystemid());
        $objElement->setSystemid($objPagelement->getSystemid());
        $objElement->loadElementData();
        $objElement->setStrTitle("para_title");
        $objElement->updateForeignElement();
        $objPagelement = new PagesPageelement($objPagelement->getSystemid());


        class_carrier::getInstance()->setParam("pageExport", $strName);
        $objPageExport = new SystemtaskPageexport();
        $objPageExport->executeTask();

        $objPage->deleteObjectFromDatabase();
        class_orm_rowcache::flushCache();
        class_db::getInstance()->flushQueryCache();

        $this->assertNull(PagesPage::getPageByName($strName));

        $this->assertFileExists(_realpath_._projectpath_."/temp/".$strPagesystemid.".xml");

        class_carrier::getInstance()->setParam("pageimport_file", _projectpath_."/temp/".$strPagesystemid.".xml");
        $objImport = new SystemtaskPageimport();
        $objImport->executeTask();

        $objPage = PagesPage::getPageByName($strName);
        $this->assertNotNull($objPage);

        $this->assertEquals($objPage->getStrName(), $strName);
        $this->assertEquals($objPage->getStrDesc(), $strDesc);
        $this->assertEquals($objPage->getStrSeostring(), $strSeoString);
        $this->assertEquals($objPage->getStrBrowsername(), $strBrowsername);

        $objElements = PagesPageelement::getAllElementsOnPage($objPage->getSystemid());

        $this->assertEquals(1, count($objElements));
        $objElements = $objElements[0];
        $this->assertEquals($objElements->getStrClassAdmin(), "ElementParagraphAdmin.php");


        $objElement = $objElements->getConcreteAdminInstance();
        $objElement->setSystemid($objElements->getSystemid());
        $objElement->loadElementData();

        $this->assertEquals("para_title", $objElement->getStrTitle());


        $objPage->deleteObjectFromDatabase();
    }
}

