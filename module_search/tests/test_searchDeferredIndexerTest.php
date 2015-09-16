<?php

require_once(__DIR__ . "/../../module_system/system/class_testbase.php");

class class_test_searchDeferredIndexerTest extends class_testbase {




    public function testObjectIndexer() {
        //use a news-record, if available
        if(class_module_system_module::getModuleByName("news") === null)
            return;

        $objConfig = class_module_system_setting::getConfigByName("_search_deferred_indexer_");
        $objConfig->setStrValue("true");
        $objConfig->updateObjectToDb();

        $objNews = new class_module_news_news();
        $objNews->setStrTitle("demo 1");
        $objNews->setStrIntro("intro demo news");
        $objNews->setStrText("text demo news");
        $objNews->updateObjectToDb();
        $strNewsId = $objNews->getSystemid();

        //trigger the endprocessinglistener
        $objHandler = new class_module_search_request_endprocessinglistener();
        $objHandler->handleEvent(class_system_eventidentifier::EVENT_SYSTEM_REQUEST_AFTERCONTENTSEND, array());


        //query queue table
        $objQueue = new class_search_indexqueue();
        $arrRows = $objQueue->getRowsBySystemid(class_search_enum_indexaction::INDEX(), $strNewsId);
        $this->assertTrue(count($arrRows) == 1);
        $this->assertTrue($arrRows[0]["search_queue_systemid"] == $objNews->getSystemid());


        class_objectfactory::getInstance()->getObject($strNewsId)->deleteObjectFromDatabase();
        $objHandler->handleEvent(class_system_eventidentifier::EVENT_SYSTEM_REQUEST_AFTERCONTENTSEND, array());


        $arrRows = $objQueue->getRowsBySystemid(class_search_enum_indexaction::DELETE(), $strNewsId);
        $this->assertTrue(count($arrRows) == 1);
        $this->assertTrue($arrRows[0]["search_queue_systemid"] == $objNews->getSystemid());


        $objConfig = class_module_system_setting::getConfigByName("_search_deferred_indexer_");
        $objQueue->deleteBySystemid($strNewsId);
        $objConfig->setStrValue("false");
        $objConfig->updateObjectToDb();

    }

    public function testObjectIndexerPerformance() {
        if(class_module_system_module::getModuleByName("news") === null)
            return;

        $arrNewsIds = array();



        echo "Indexing without deferred indexer...\n";
        class_module_system_changelog::$bitChangelogEnabled = false;
        $intTimeStart = microtime(true);
        $intQueriesStart = class_db::getInstance()->getNumber();

        for($intI = 0; $intI < 15; $intI++) {
            $objNews = new class_module_news_news();
            $objNews->setStrTitle("demo 1");
            $objNews->setStrIntro("intro demo news");
            $objNews->setStrText("text demo news");
            $objNews->updateObjectToDb();
            $arrNewsIds[] = $objNews->getSystemid();
        }

        echo "Queries pre indexing: ", class_db::getInstance()->getNumber() - $intQueriesStart. " \n";

        $objHandler = new class_module_search_request_endprocessinglistener();
        $objHandler->handleEvent(class_system_eventidentifier::EVENT_SYSTEM_REQUEST_AFTERCONTENTSEND, array());

        $intTimeEnd = microtime(true);
        $time = $intTimeEnd - $intTimeStart;
        echo "Object updates: ", sprintf('%f', $time), " sec.\n";
        echo "Queries total: ", class_db::getInstance()->getNumber() - $intQueriesStart. " \n";


        echo "\nIndexing with deferred indexer...\n";
        $objConfig = class_module_system_setting::getConfigByName("_search_deferred_indexer_");
        $objConfig->setStrValue("true");
        $objConfig->updateObjectToDb();

        $intTimeStart = microtime(true);
        $intQueriesStart = class_db::getInstance()->getNumber();

        for($intI = 0; $intI < 15; $intI++) {
            $objNews = new class_module_news_news();
            $objNews->setStrTitle("demo 1");
            $objNews->setStrIntro("intro demo news");
            $objNews->setStrText("text demo news");
            $objNews->updateObjectToDb();
            $arrNewsIds[] = $objNews->getSystemid();
        }

        echo "Queries pre indexing: ", class_db::getInstance()->getNumber() - $intQueriesStart. " \n";

        echo "Triggering queue update event...\n";
        $objHandler = new class_module_search_request_endprocessinglistener();
        $objHandler->handleEvent(class_system_eventidentifier::EVENT_SYSTEM_REQUEST_AFTERCONTENTSEND, array());

        $intTimeEnd = microtime(true);
        $time = $intTimeEnd - $intTimeStart;
        echo "Object updates: ", sprintf('%f', $time), " sec.\n";
        echo "Queries total: ", class_db::getInstance()->getNumber() - $intQueriesStart. " \n";


        $objConfig = class_module_system_setting::getConfigByName("_search_deferred_indexer_");
        $objConfig->setStrValue("false");
        $objConfig->updateObjectToDb();

        foreach($arrNewsIds as $strNewsId)
            class_objectfactory::getInstance()->getObject($strNewsId)->deleteObjectFromDatabase();

    }
}


