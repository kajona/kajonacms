<?php

namespace Kajona\News\Tests;

use Kajona\News\Portal\NewsPortal;
use Kajona\News\Portal\NewsPortalXml;
use Kajona\News\System\NewsCategory;
use Kajona\News\System\NewsFeed;
use Kajona\News\System\NewsNews;
use Kajona\System\System\StringUtil;
use Kajona\System\System\XmlParser;
use Kajona\System\Tests\Testbase;

class NewsTest extends Testbase
{
    public function testCreateDelete()
    {
        $objNews = new NewsNews();
        $objNews->setStrTitle("autotest");
        $objNews->setStrIntro("autotest");
        $objNews->setStrText("autotest");

        $this->assertTrue($objNews->updateObjectToDb(), __FILE__." save news");

        $objCat = new NewsCategory();
        $objCat->setStrTitle("autotest");
        $this->assertTrue($objCat->updateObjectToDb(), __FILE__." save cat");

        $this->flushDBCache();
        $this->assertEquals(0, count(NewsCategory::getNewsMember($objNews->getSystemid())), __FILE__ . " check cats for news");
        $this->assertEquals(0, count(NewsNews::getObjectListFiltered(null, $objCat->getSystemid())), __FILE__ . " check news for cat");


        $objNews->setArrCats(array($objCat->getSystemid()));
        $this->assertTrue($objNews->updateObjectToDb(), __FILE__." update news");

        $strNewsId = $objNews->getSystemid();
        $strCatId = $objCat->getSystemid();

        $this->flushDBCache();

        $objNews = new NewsNews($strNewsId);
        $objCat = new NewsCategory($strCatId);

        $this->assertEquals(1, count(NewsCategory::getNewsMember($objNews->getSystemid())), __FILE__ . " check cats for news");
        $this->assertEquals(1, count(NewsNews::getObjectListFiltered(null, $objCat->getSystemid())), __FILE__ . " check news for cat");

        $this->assertTrue($objNews->deleteObjectFromDatabase(), __FILE__." delete news");

        $this->flushDBCache();
        $this->assertEquals(0, count(NewsNews::getObjectListFiltered(null, $objCat->getSystemid())), __FILE__ . " check news for cat");

        $this->assertTrue($objCat->deleteObjectFromDatabase(), __FILE__." delete cat");
    }


    public function testRssFeed()
    {
        $objNews = new NewsNews();
        $objNews->setStrTitle("autotest");
        $objNews->setStrIntro("autotest");
        $objNews->setStrText("autotest");
        $this->assertTrue($objNews->updateObjectToDb(), __FILE__." save news");


        $objNews2 = new NewsNews();
        $objNews2->setStrTitle("autotest2");
        $objNews2->setStrIntro("autotest2");
        $objNews2->setStrText("autotest2");
        $this->assertTrue($objNews2->updateObjectToDb(), __FILE__." save news");

        $objCat = new NewsCategory();
        $objCat->setStrTitle("autotest");
        $this->assertTrue($objCat->updateObjectToDb(), __FILE__." save cat");
        $this->flushDBCache();

        $objNews->setArrCats(array($objCat->getSystemid()));
        $this->assertTrue($objNews->updateObjectToDb(), __FILE__." update news");
        $this->flushDBCache();


        $objFeed = new NewsFeed();
        $objFeed->setStrTitle("testfeed");
        $objFeed->setStrCat($objCat->getSystemid());
        $objFeed->setStrUrlTitle("autotest");
        $objFeed->setStrPage("autotest");
        $objFeed->updateObjectToDb();

        $this->flushDBCache();

        $this->assertEquals(1, count(NewsFeed::getNewsList($objFeed->getStrCat())), __FILE__." check news for feed");
        $this->assertEquals(1, count(NewsFeed::getNewsList($objFeed->getStrCat(), 1)), __FILE__." check news for feed");


        $objNewsPortalXML = new NewsPortal();
        $objNewsPortalXML->setParam("feedTitle", "autotest");
        $strFeed = $objNewsPortalXML->action("newsFeed");
        $this->assertTrue(StringUtil::indexOf($strFeed, "<title>autotest</title>") !== false, __FILE__." check rss feed");

        $objXmlParser = new XmlParser();
        $objXmlParser->loadString($strFeed);
        $arrFeed = $objXmlParser->xmlToArray();
        $intNrOfNews = count($arrFeed["rss"][0]["channel"][0]["item"]);
        $this->assertEquals(1, $intNrOfNews, __FILE__." check items for feed");
        $strTitle = $arrFeed["rss"][0]["channel"][0]["item"][0]["title"][0]["value"];
        $this->assertEquals("autotest", $strTitle, __FILE__." check items-title for feed");


        $objNews2->setArrCats(array($objCat->getSystemid()));
        $this->assertTrue($objNews2->updateObjectToDb(), __FILE__." update news");
        $this->flushDBCache();


        $objNewsPortalXML = new NewsPortal();
        $objNewsPortalXML->setParam("feedTitle", "autotest");
        $strFeed = $objNewsPortalXML->action("newsFeed");
        $this->assertTrue(StringUtil::indexOf($strFeed, "<title>autotest</title>") !== false, __FILE__." check rss feed");

        $objXmlParser = new XmlParser();
        $objXmlParser->loadString($strFeed);
        $arrFeed = $objXmlParser->xmlToArray();
        //var_dump($arrFeed["rss"][0]["channel"][0]["item"]);
        $intNrOfNews = count($arrFeed["rss"][0]["channel"][0]["item"]);
        $this->assertEquals(2, $intNrOfNews, __FILE__." check items for feed");


        $this->assertTrue($objNews->deleteObjectFromDatabase(), __FILE__." delete news");
        $this->assertTrue($objNews2->deleteObjectFromDatabase(), __FILE__." delete news");
        $this->assertTrue($objCat->deleteObjectFromDatabase(), __FILE__." delete cat");
        $this->assertTrue($objFeed->deleteObjectFromDatabase(), __FILE__." delete feed");
    }


}



