<?php

namespace Kajona\Search\Tests;
require_once __DIR__."../../../core/module_system/system/Testbase.php";
use Kajona\News\System\NewsNews;
use Kajona\Search\System\SearchContent;
use Kajona\Search\System\SearchDocument;
use Kajona\Search\System\SearchIndexwriter;
use Kajona\Search\System\SearchStandardAnalyzer;
use Kajona\System\System\Carrier;
use Kajona\System\System\Database;
use Kajona\System\System\MessagingMessage;
use Kajona\System\System\Objectfactory;
use Kajona\System\System\SystemChangelog;
use Kajona\System\System\SystemModule;
use Kajona\System\System\Testbase;

class SearchIndexerTest extends Testbase {

    public function testUnicodeIndexer() {
        $strText = "Hänsel und Gretel verirrten sich schließlich im Wald";

        $objAnalyzer = new SearchStandardAnalyzer();
        $arrResults = $objAnalyzer->analyze($strText);

        $arrResults = array_keys($arrResults);
        $this->assertEquals(count($arrResults), 7);
        $this->assertEquals($arrResults[0], "hänsel");
        $this->assertEquals($arrResults[1], "und");
        $this->assertEquals($arrResults[2], "gretel");
        $this->assertEquals($arrResults[3], "verirrten");
        $this->assertEquals($arrResults[4], "sich");
        $this->assertEquals($arrResults[5], "schließlich");
        $this->assertEquals($arrResults[6], "wald");
    }


    public function testNumericIndexer() {
        $strText = "Hänsel und 2 Gretel verirrten sich schließlich 23 mal im 1000 Wald";

        $objAnalyzer = new SearchStandardAnalyzer();
        $arrResults = $objAnalyzer->analyze($strText);

        $arrResults = array_keys($arrResults);
        $this->assertEquals(count($arrResults), 11);
        $this->assertEquals($arrResults[0], "hänsel");
        $this->assertEquals($arrResults[1], "und");
        $this->assertEquals($arrResults[2], "2");
        $this->assertEquals($arrResults[3], "gretel");
        $this->assertEquals($arrResults[4], "verirrten");
        $this->assertEquals($arrResults[5], "sich");
        $this->assertEquals($arrResults[6], "schließlich");
        $this->assertEquals($arrResults[7], "23");
        $this->assertEquals($arrResults[8], "mal");
        $this->assertEquals($arrResults[9], "1000");
    }



    public function testIndexCounter() {
        $strText = "aaa aaa aaa bbb aaa ccc ccc ddd";

        $objAnalyzer = new SearchStandardAnalyzer();
        $arrResults = $objAnalyzer->analyze($strText);

        $arrResultValues = array_keys($arrResults);
        $this->assertEquals(count($arrResultValues), 4);
        $this->assertEquals($arrResultValues[0], "aaa");
        $this->assertEquals($arrResultValues[1], "bbb");
        $this->assertEquals($arrResultValues[2], "ccc");
        $this->assertEquals($arrResultValues[3], "ddd");

        $this->assertEquals($arrResults["aaa"], 4);
        $this->assertEquals($arrResults["bbb"], 1);
        $this->assertEquals($arrResults["ccc"], 2);
        $this->assertEquals($arrResults["ddd"], 1);
    }


    public function testIndexCreate() {

        $objSearchDocument = new SearchDocument();
        $objSearchDocument->setDocumentId(1);

        $objSearchContent = new SearchContent();
        $objSearchContent->setFieldName('title');
        $objSearchContent->setContent('bla');
        $objSearchContent->setDocumentId(1);

        $objSearchDocument->addContentObj($objSearchContent);

        $objSearchContent = new SearchContent();
        $objSearchContent->setFieldName('title');
        $objSearchContent->setContent('blub');
        $objSearchContent->setDocumentId(1);

        $objSearchDocument->addContentObj($objSearchContent);

        $this->assertEquals(count($objSearchDocument->getContent()), 2, "manual added test");

        // test tokenizer
        $objSearchDocument->addContent("title", "hAha DUdub");
        $this->assertEquals(count($objSearchDocument->getContent()), 4);

        // test lowerize
        $this->assertEquals($objSearchDocument->getContent()[2]->getContent(), 'haha');
        $this->assertEquals($objSearchDocument->getContent()[3]->getContent(), 'dudub');

        // test id management
        $this->assertEquals($objSearchDocument->getContent()[2]->getDocumentId(), $objSearchDocument->getDocumentId(), "id management test");

        // test short entry ignoring
        $objSearchDocument->addContent("title", "in hIulk, It, fs fslong or.");
        $arrContent = $objSearchDocument->getContent();
        $this->assertEquals(count($arrContent), 6);
        $this->assertEquals($arrContent[4]->getContent(), 'hiulk');
        $this->assertEquals($arrContent[5]->getContent(), 'fslong');

        //test blacklisting
        $objSearchDocument2 = new SearchDocument();
        $objSearchDocument2->setDocumentId(2);
        $objSearchDocument2->addContent("title", " this is allowed and this is not ");
        $arrContent = $objSearchDocument2->getContent();
        $this->assertEquals(count($arrContent), 3);
        $this->assertEquals($arrContent[0]->getContent(), 'this');
        $this->assertEquals($arrContent[0]->getScore(), 2);
        $this->assertEquals($arrContent[1]->getContent(), 'allowed');
        $this->assertEquals($arrContent[2]->getContent(), 'not');
    }



    public function testStandardAnalyzer() {
        $objAnalyzer = new SearchStandardAnalyzer();
        $arrResults = $objAnalyzer->analyze("bl");
        $this->assertEquals(count($arrResults), 0);
        $arrResults = $objAnalyzer->analyze("bl  blub");
        $this->assertEquals(count($arrResults), 1);
        $arrResults = $objAnalyzer->analyze(" blub bl ");
        $this->assertEquals(count($arrResults), 1);

    }



    public function testFullIndexWriter() {
        if(@ini_get("max_execution_time") < 300 && @ini_get("max_execution_time") > 0)
            @ini_set("max_execution_time", 300);

        $indexWriter = new SearchIndexwriter();

        $intQueriesStart = Database::getInstance()->getNumber();
        $intTimeStart = microtime(true);
        $indexWriter->indexRebuild();
        $intTimeEnd = microtime(true);
        $time = $intTimeEnd - $intTimeStart;
        echo "Index erstellt in ". sprintf('%f', $time). " sec.\n";
        echo "Index erstellt mit ".(Database::getInstance()->getNumber()-$intQueriesStart). " queries.\n";

    }


    public function testObjectIndexerPerformance() {
        if(SystemModule::getModuleByName("news") === null)
            return;
        
        $objNews = new NewsNews();
        $objNews->setStrTitle("demo 1");
        $objNews->setStrIntro("intro demo news");
        $objNews->setStrText("text demo news");
        $objNews->updateObjectToDb();
        $strNewsId = $objNews->getSystemid();

        echo "Status changes with disabled changelog indexer integration...\n";
        SystemChangelog::$bitChangelogEnabled = false;
        $intTimeStart = microtime(true);
        $intQueriesStart = Database::getInstance()->getNumber();

        for($intI = 0; $intI < 150; $intI++) {
            $objNews->setIntRecordStatus($intI % 2);
            $objNews->updateObjectToDb();
        }

        $intTimeEnd = microtime(true);
        $time = $intTimeEnd - $intTimeStart;
        echo "Object updates: ", sprintf('%f', $time), " sec.\n";
        echo "Queries: ", Database::getInstance()->getNumber() - $intQueriesStart. " \n";


        echo "Status changes with enabled changelog indexer integration...\n";
        SystemChangelog::$bitChangelogEnabled = true;
        $intTimeStart = microtime(true);
        $intQueriesStart = Database::getInstance()->getNumber();

        for($intI = 0; $intI < 150; $intI++) {
            $objNews->setIntRecordStatus($intI % 2);
            $objNews->updateObjectToDb();
        }

        $intTimeEnd = microtime(true);
        $time = $intTimeEnd - $intTimeStart;
        echo "Object updates: ", sprintf('%f', $time), " sec.\n";
        echo "Queries: ", Database::getInstance()->getNumber() - $intQueriesStart. " \n";


        Objectfactory::getInstance()->getObject($strNewsId)->deleteObjectFromDatabase();

    }


    public function testIndexRemoval()
    {
        if(SystemModule::getModuleByName("messaging") === null)
            return;

        $objMessage = new MessagingMessage();
        $objMessage->setStrTitle("unittest demo message");
        $objMessage->setStrBody("unittest demo message body");
        $objMessage->setStrMessageProvider("Kajona\\System\\System\\Messageproviders\\MessageproviderPersonalmessage");
        $objMessage->updateObjectToDb();

        $objIndexWriter = new SearchIndexwriter();
        $objIndexWriter->indexObject($objMessage);

        $arrRow = Database::getInstance()->getPRow("SELECT COUNT(*) as anz FROM "._dbprefix_."search_ix_document WHERE search_ix_system_id = ?", array($objMessage->getSystemid()));
        $this->assertEquals(1, $arrRow["anz"]);

        $arrRow = Database::getInstance()->getPRow("SELECT search_ix_document_id FROM "._dbprefix_."search_ix_document WHERE search_ix_system_id = ?", array($objMessage->getSystemid()));
        $strDocumentId = $arrRow["search_ix_document_id"];

//        $arrRow = Database::getInstance()->getPArray("SELECT * FROM "._dbprefix_."search_ix_content WHERE search_ix_content_document_id = ?", array($strDocumentId));
        $arrRow = Database::getInstance()->getPRow("SELECT COUNT(*) as anz FROM "._dbprefix_."search_ix_content WHERE search_ix_content_document_id = ?", array($strDocumentId));
        $this->assertEquals(7, $arrRow["anz"]);

        Carrier::getInstance()->flushCache(Carrier::INT_CACHE_TYPE_DBQUERIES);
        $objIndexWriter->removeRecordFromIndex($objMessage->getSystemid());
        $arrRow = Database::getInstance()->getPRow("SELECT COUNT(*) as anz FROM "._dbprefix_."search_ix_document WHERE search_ix_system_id = ?", array($objMessage->getSystemid()));
        $this->assertEquals(0, $arrRow["anz"]);

        $arrRow = Database::getInstance()->getPRow("SELECT COUNT(*) as anz FROM "._dbprefix_."search_ix_content WHERE search_ix_content_document_id = ?", array($strDocumentId));
        $this->assertEquals(0, $arrRow["anz"]);


        $objMessage->deleteObjectFromDatabase();

    }
}


