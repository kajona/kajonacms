<?php

namespace Kajona\Search\Tests;
require_once __DIR__."../../../core/module_system/system/Testbase.php";
use Kajona\News\System\NewsNews;
use Kajona\Search\Event\SearchRequestEndprocessinglistener;
use Kajona\Search\System\SearchEnumIndexaction;
use Kajona\Search\System\SearchIndexqueue;
use Kajona\System\System\Database;
use Kajona\System\System\Objectfactory;
use Kajona\System\System\SystemChangelog;
use Kajona\System\System\SystemEventidentifier;
use Kajona\System\System\SystemModule;
use Kajona\System\System\SystemSetting;
use Kajona\System\System\Testbase;

class SearchDeferredIndexerTest extends Testbase {




    public function testObjectIndexer() {
        //use a news-record, if available
        if(SystemModule::getModuleByName("news") === null)
            return;

        $objConfig = SystemSetting::getConfigByName("_search_deferred_indexer_");
        $objConfig->setStrValue("true");
        $objConfig->updateObjectToDb();

        $objNews = new NewsNews();
        $objNews->setStrTitle("demo 1");
        $objNews->setStrIntro("intro demo news");
        $objNews->setStrText("text demo news");
        $objNews->updateObjectToDb();
        $strNewsId = $objNews->getSystemid();

        //trigger the endprocessinglistener
        $objHandler = new SearchRequestEndprocessinglistener();
        $objHandler->handleEvent(SystemEventidentifier::EVENT_SYSTEM_REQUEST_AFTERCONTENTSEND, array());


        //query queue table
        $objQueue = new SearchIndexqueue();
        $arrRows = $objQueue->getRowsBySystemid(SearchEnumIndexaction::INDEX(), $strNewsId);
        $this->assertTrue(count($arrRows) == 1);
        $this->assertTrue($arrRows[0]["search_queue_systemid"] == $objNews->getSystemid());


        Objectfactory::getInstance()->getObject($strNewsId)->deleteObjectFromDatabase();
        $objHandler->handleEvent(SystemEventidentifier::EVENT_SYSTEM_REQUEST_AFTERCONTENTSEND, array());


        $arrRows = $objQueue->getRowsBySystemid(SearchEnumIndexaction::DELETE(), $strNewsId);
        $this->assertTrue(count($arrRows) == 1);
        $this->assertTrue($arrRows[0]["search_queue_systemid"] == $objNews->getSystemid());


        $objConfig = SystemSetting::getConfigByName("_search_deferred_indexer_");
        $objQueue->deleteBySystemid($strNewsId);
        $objConfig->setStrValue("false");
        $objConfig->updateObjectToDb();

    }

    public function testObjectIndexerPerformance() {
        if(SystemModule::getModuleByName("news") === null)
            return;

        $arrNewsIds = array();



        echo "Indexing without deferred indexer...\n";
        SystemChangelog::$bitChangelogEnabled = false;
        $intTimeStart = microtime(true);
        $intQueriesStart = Database::getInstance()->getNumber();

        for($intI = 0; $intI < 15; $intI++) {
            $objNews = new NewsNews();
            $objNews->setStrTitle("demo 1");
            $objNews->setStrIntro("intro demo news");
            $objNews->setStrText("text demo news");
            $objNews->updateObjectToDb();
            $arrNewsIds[] = $objNews->getSystemid();
        }

        echo "Queries pre indexing: ", Database::getInstance()->getNumber() - $intQueriesStart. " \n";

        $objHandler = new SearchRequestEndprocessinglistener();
        $objHandler->handleEvent(SystemEventidentifier::EVENT_SYSTEM_REQUEST_AFTERCONTENTSEND, array());

        $intTimeEnd = microtime(true);
        $time = $intTimeEnd - $intTimeStart;
        echo "Object updates: ", sprintf('%f', $time), " sec.\n";
        echo "Queries total: ", Database::getInstance()->getNumber() - $intQueriesStart. " \n";


        echo "\nIndexing with deferred indexer...\n";
        $objConfig = SystemSetting::getConfigByName("_search_deferred_indexer_");
        $objConfig->setStrValue("true");
        $objConfig->updateObjectToDb();

        $intTimeStart = microtime(true);
        $intQueriesStart = Database::getInstance()->getNumber();

        for($intI = 0; $intI < 15; $intI++) {
            $objNews = new NewsNews();
            $objNews->setStrTitle("demo 1");
            $objNews->setStrIntro("intro demo news");
            $objNews->setStrText("text demo news");
            $objNews->updateObjectToDb();
            $arrNewsIds[] = $objNews->getSystemid();
        }

        echo "Queries pre indexing: ", Database::getInstance()->getNumber() - $intQueriesStart. " \n";

        echo "Triggering queue update event...\n";
        $objHandler = new SearchRequestEndprocessinglistener();
        $objHandler->handleEvent(SystemEventidentifier::EVENT_SYSTEM_REQUEST_AFTERCONTENTSEND, array());

        $intTimeEnd = microtime(true);
        $time = $intTimeEnd - $intTimeStart;
        echo "Object updates: ", sprintf('%f', $time), " sec.\n";
        echo "Queries total: ", Database::getInstance()->getNumber() - $intQueriesStart. " \n";


        $objConfig = SystemSetting::getConfigByName("_search_deferred_indexer_");
        $objConfig->setStrValue("false");
        $objConfig->updateObjectToDb();

        foreach($arrNewsIds as $strNewsId)
            Objectfactory::getInstance()->getObject($strNewsId)->deleteObjectFromDatabase();

    }
}


