<?php
namespace Kajona\Pages\Tests;

use Kajona\Pages\Admin\Elements\ElementPlaintextAdmin;
use Kajona\Pages\System\PagesFolder;
use Kajona\Pages\System\PagesPage;
use Kajona\Pages\System\PagesPageelement;
use Kajona\System\System\Carrier;
use Kajona\System\System\SystemModule;
use Kajona\System\Tests\Testbase;

class PagesTest extends Testbase
{


    public function test()
    {

        $objDB = Carrier::getInstance()->getObjDB();

        //pages at startup:
        $intPagesAtStartup = count(PagesFolder::getPagesInFolder(SystemModule::getModuleByName("pages")->getSystemid()));
        $objDB->flushQueryCache();


        $objFolder = new PagesFolder();
        $objFolder->setStrName("autotest");
        $objFolder->updateObjectToDb(SystemModule::getModuleByName("pages")->getSystemid());
        $strTestFolderID = $objFolder->getSystemid();

        $arrFoldersCreated = array();
        for ($intI = 0; $intI < 10; $intI++) {
            $objFolder = new PagesFolder();
            $objFolder->setStrName("testfolder_".$intI);
            $objFolder->updateObjectToDb($strTestFolderID);
            $strFolderID = $objFolder->getSystemid();
            $arrFoldersCreated[] = $strFolderID;
            $objFolder = new PagesFolder($strFolderID);
            $this->assertEquals($objFolder->getStrName(), "testfolder_".$intI, __FILE__." checkNameOfFolderCreated");
            $this->assertEquals($objFolder->getPrevId(), $strTestFolderID, __FILE__." checkPrevIDOfFolderCreated");
        }

        $arrFoldersAtLevel = PagesFolder::getFolderList($strTestFolderID);
        $this->assertEquals(count($arrFoldersAtLevel), 10, __FILE__." checkNrOfFoldersCreatedByModel");


        $arrPagesCreated = array();
        for ($intI = 0; $intI < 10; $intI++) {
            $objPages = new PagesPage();
            $objPages->setStrName("autotest_".$intI);
            $objPages->setStrTemplate("standard.tpl");
            $objPages->updateObjectToDb();
            $strPageID = $objPages->getSystemid();
            $arrPagesCreated[] = $strPageID;
            $objPage = new PagesPage($strPageID);
            $this->assertEquals($objPage->getStrName(), "autotest_".$intI, __FILE__." checkNameOfPageCreated");
            $this->assertEquals($objPage->getStrTemplate(), "standard.tpl", __FILE__." checkTemplateOfPageCreated");
        }

        $arrPagesAtLevel = PagesFolder::getPagesInFolder(SystemModule::getModuleByName("pages")->getSystemid());
        $this->assertEquals(count($arrPagesAtLevel), 10 + $intPagesAtStartup, __FILE__." checkNrOfPagesCreatedByModel");

        foreach ($arrPagesCreated as $strOnePageID) {
            $objDelPage = new PagesPage($strOnePageID);
            $objDelPage->deleteObjectFromDatabase();
            $objDB->flushQueryCache();
        }
        $arrPagesAtLevel = PagesFolder::getPagesInFolder(SystemModule::getModuleByName("pages")->getSystemid());
        $this->assertEquals(count($arrPagesAtLevel), $intPagesAtStartup, __FILE__." checkNrOfPagesAtLevel");

        foreach ($arrFoldersCreated as $strOneFolderID) {
            $objFolder = new PagesFolder($strOneFolderID);
            $objFolder->deleteObjectFromDatabase();
            $objDB->flushQueryCache();
        }
        $arrFoldersAtLevel = PagesFolder::getFolderList($strTestFolderID);
        $this->assertEquals(count($arrFoldersAtLevel), 0, __FILE__." checkNrOfFoldersAtLevel");


        $objFolder = new PagesFolder($strTestFolderID);
        $objFolder->deleteObjectFromDatabase();

    }


    public function testCopyPage()
    {

        $strTitle = generateSystemid();


        $objPage = new PagesPage();
        $objPage->setStrName($strTitle);
        $objPage->setStrBrowsername(generateSystemid());
        $objPage->setStrSeostring(generateSystemid());
        $objPage->setStrDesc(generateSystemid());
        $objPage->setStrTemplate("standard.tpl");
        $objPage->updateObjectToDb();

        $strOldSystemid = $objPage->getSystemid();

        $objPagelement = new PagesPageelement();
        $objPagelement->setStrPlaceholder("text_plaintext");
        $objPagelement->setStrName("text");
        $objPagelement->setStrElement("plaintext");
        $objPagelement->updateObjectToDb($objPage->getSystemid());
        $objPagelement = new PagesPageelement($objPagelement->getSystemid());

        //and finally create the object
        /** @var $objElement ElementPlaintextAdmin */
        $objElement = $objPagelement->getConcreteAdminInstance();

        $objElement->setStrText("autotest");

        $objElement->doBeforeSaveToDb();
        $objElement->updateForeignElement();
        $objElement->doAfterSaveToDb();


        //copy the page itself
        $objPage->copyObject();

        $strNewSystemid = $objPage->getSystemid();


        $this->flushDBCache();

        $objOldPage = new PagesPage($strOldSystemid);
        $objNewPage = new PagesPage($strNewSystemid);

        $this->assertNotEquals($objOldPage->getStrName(), $objNewPage->getStrName());
        $this->assertEquals($objOldPage->getStrBrowsername(), $objNewPage->getStrBrowsername());
        $this->assertEquals($objOldPage->getStrSeostring(), $objNewPage->getStrSeostring());
        $this->assertEquals($objOldPage->getStrDesc(), $objNewPage->getStrDesc());
        $this->assertEquals($objOldPage->getStrTemplate(), $objNewPage->getStrTemplate());

        $arrOldElements = PagesPageelement::getAllElementsOnPage($strOldSystemid);
        $arrNewElements = PagesPageelement::getAllElementsOnPage($strNewSystemid);

        $this->assertEquals(count($arrOldElements), count($arrNewElements));
        $this->assertEquals(1, count($arrOldElements));
        $this->assertEquals(1, count($arrNewElements));

        $objOldElement = $arrOldElements[0];
        $objNewElement = $arrNewElements[0];

        $this->assertEquals($objOldElement->getStrPlaceholder(), $objNewElement->getStrPlaceholder());
        $this->assertEquals($objOldElement->getStrLanguage(), $objNewElement->getStrLanguage());
        $this->assertEquals($objOldElement->getStrElement(), $objNewElement->getStrElement());

        /** @var ElementPlaintextAdmin $objOldElementInstance */
        $objOldElementInstance = $objOldElement->getConcreteAdminInstance();
        $arrOldElementData = $objOldElementInstance->loadElementData();

        /** @var ElementPlaintextAdmin $objNewElementInstance */
        $objNewElementInstance = $objNewElement->getConcreteAdminInstance();
        $arrNewElementData = $objNewElementInstance->loadElementData();

        $this->assertNotEquals($arrOldElementData["content_id"], $arrNewElementData["content_id"]);
        $this->assertEquals($arrOldElementData["text"], $arrNewElementData["text"]);

        $this->assertEquals($objOldElementInstance->getStrText(), $objNewElementInstance->getStrText());
        $this->assertEquals($objOldElementInstance->getStrText(), "autotest");
        $this->assertEquals($objNewElementInstance->getStrText(), "autotest");


        $objNewPage->deleteObjectFromDatabase();
        $objOldPage->deleteObjectFromDatabase();

    }

}



