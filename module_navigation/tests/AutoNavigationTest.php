<?php

namespace Kajona\Navigation\Tests;

use Kajona\Navigation\System\NavigationTree;
use Kajona\Pages\Admin\Elements\ElementPlaintextAdmin;
use Kajona\Pages\System\PagesFolder;
use Kajona\Pages\System\PagesPage;
use Kajona\Pages\System\SamplecontentContentHelper;
use Kajona\System\System\Carrier;
use Kajona\System\System\LanguagesLanguage;
use Kajona\System\Tests\Testbase;

class AutoNavigationTest extends Testbase
{

    private static $strFolderSystemid;
    private static $strPage1Systemid;
    private static $strPage2Systemid;
    private static $strPage2aSystemid;

    public function setUp()
    {

        $objLang = new LanguagesLanguage();
        $strLang = $objLang->getAdminLanguage();


        //creating a new page-node structure
        $objFolder = new PagesFolder();
        $objFolder->setStrName("naviautotest");
        $objFolder->updateObjectToDb();
        self::$strFolderSystemid = $objFolder->getSystemid();

        $objPage1 = new PagesPage();
        $objPage1->setStrName("testpage1");
        $objPage1->setStrLanguage($strLang);
        $objPage1->setStrBrowsername("testpage1");
        $objPage1->setIntType(PagesPage::$INT_TYPE_PAGE);
        $objPage1->setStrTemplate("standard.tpl");
        $objPage1->updateObjectToDb($objFolder->getSystemid());
        self::$strPage1Systemid = $objPage1->getSystemid();

        $objHelper = new SamplecontentContentHelper();
        $objBlocks = $objHelper->createBlocksElement("Headline", $objPage1, $objPage1->getStrAdminLanguageToWorkOn());
        $objBlock = $objHelper->createBlockElement("Headline", $objBlocks, $objPage1->getStrAdminLanguageToWorkOn());

        $objHeadline = $objHelper->createPageElement("headline_plaintext", $objBlock, $objPage1->getStrAdminLanguageToWorkOn());
        /** @var ElementPlaintextAdmin $objHeadlineAdminin */
        $objHeadlineAdmin = $objHeadline->getConcreteAdminInstance();
        $objHeadlineAdmin->setStrText("demo");
        $objHeadlineAdmin->updateForeignElement();


        $objPage2 = new PagesPage();
        $objPage2->setStrLanguage($strLang);
        $objPage2->setStrName("testpage2");
        $objPage2->setStrBrowsername("testpage2");
        $objPage2->setIntType(PagesPage::$INT_TYPE_ALIAS);
        $objPage2->setStrAlias("testpage2a");
        $objPage2->updateObjectToDb($objFolder->getSystemid());
        self::$strPage2Systemid = $objPage2->getSystemid();

//        $objHelper = new SamplecontentContentHelper();
//        $objBlocks = $objHelper->createBlocksElement("Headline", $objPage2, $objPage1->getStrAdminLanguageToWorkOn());
//        $objBlock = $objHelper->createBlockElement("Headline", $objBlocks, $objPage1->getStrAdminLanguageToWorkOn());


        $objPage3 = new PagesPage();
        $objPage3->setStrLanguage($strLang);
        $objPage3->setStrName("testpage2a");
        $objPage3->setStrBrowsername("testpage2a");
        $objPage3->setIntType(PagesPage::$INT_TYPE_PAGE);
        $objPage3->setStrTemplate("standard.tpl");
        $objPage3->updateObjectToDb($objPage2->getSystemid());
        self::$strPage2aSystemid = $objPage3->getSystemid();

        $objHelper = new SamplecontentContentHelper();
        $objBlocks = $objHelper->createBlocksElement("Headline", $objPage3, $objPage3->getStrAdminLanguageToWorkOn());
        $objBlock = $objHelper->createBlockElement("Headline", $objBlocks, $objPage3->getStrAdminLanguageToWorkOn());


        Carrier::getInstance()->getObjDB()->flushQueryCache();

        parent::setUp();
    }

    public function testGeneration()
    {


        Carrier::getInstance()->getObjDB()->flushQueryCache();

        $objTestNavigation = new NavigationTree();
        $objTestNavigation->setStrName("autotest");

        $objTestNavigation->setStrFolderId(self::$strFolderSystemid);
        $objTestNavigation->updateObjectToDb();

        $arrNodes = $objTestNavigation->getCompleteNaviStructure();


        $this->assertEquals(2, count($arrNodes["subnodes"]));

        $objFirstNode = $arrNodes["subnodes"][0]["node"];
        $this->assertEquals("testpage1", $objFirstNode->getStrName());
        $this->assertEquals("testpage1", $objFirstNode->getStrPageI());

        $objFirstNode = $arrNodes["subnodes"][1]["node"];
        $this->assertEquals("testpage2", $objFirstNode->getStrName());
        $this->assertEquals("testpage2a", $objFirstNode->getStrPageI());


        $arrNodesOnSecLevel = $arrNodes["subnodes"][1]["subnodes"][0];
        $this->assertEquals(0, count($arrNodesOnSecLevel["subnodes"]));


        $objFirstNode = $arrNodesOnSecLevel["node"];
        $this->assertEquals("testpage2a", $objFirstNode->getStrName());
        $this->assertEquals("testpage2a", $objFirstNode->getStrPageI());


        $objTestNavigation->deleteObjectFromDatabase();
    }


    public function tearDown()
    {
        Carrier::getInstance()->getObjDB()->flushQueryCache();
        //delete pages and folders created

        $objPage = new PagesPage(self::$strPage2aSystemid);
        $objPage->deleteObjectFromDatabase();

        $objPage = new PagesPage(self::$strPage2Systemid);
        $objPage->deleteObjectFromDatabase();

        $objPage = new PagesPage(self::$strPage1Systemid);
        $objPage->deleteObjectFromDatabase();

        $objFolder = new PagesFolder(self::$strFolderSystemid);
        $objFolder->deleteObjectFromDatabase();

        parent::tearDown();
    }

}

