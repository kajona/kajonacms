<?php

require_once(__DIR__ . "/../../module_system/system/class_testbase.php");

class class_test_searchTermQueryTest extends class_testbase {

    private static $arrObjectIds = array();

    protected function setUp() {

        $objObject = $this->createObject("class_module_system_aspect", "");
        self::$arrObjectIds[] = $objObject->getSystemid();

        $objSearchIndexWriter = new class_module_search_indexwriter();
        $objSearchIndexWriter->clearIndex();

        $objSearchDocument_1 = new class_module_search_document();
        $objSearchDocument_1->setStrSystemId($objObject->getSystemid());
        $objSearchDocument_1->setDocumentId(generateSystemid());
        $objSearchDocument_1->addContent("title", "hallo");
        $objSearchDocument_1->addContent("text", "welt");
        $objSearchDocument_1->addContent("subtitle", "blub");
        $objSearchDocument_1->addContent("text2", "blub");

        $objObject = $this->createObject("class_module_search_search", "");
        self::$arrObjectIds[] = $objObject->getSystemid();

        $objSearchDocument_2 = new class_module_search_document();
        $objSearchDocument_2->setStrSystemId($objObject->getSystemid());
        $objSearchDocument_2->setDocumentId(generateSystemid());
        $objSearchDocument_2->addContent("title", "hallo");
        $objSearchDocument_2->addContent("text", "welt");

        $objObject = $this->createObject("class_module_system_aspect", "");
        self::$arrObjectIds[] = $objObject->getSystemid();

        $objSearchDocument_3 = new class_module_search_document();
        $objSearchDocument_3->setStrSystemId($objObject->getSystemid());
        $objSearchDocument_3->setDocumentId(generateSystemid());
        $objSearchDocument_3->addContent("title", "lorem ipsum dolor ipsum");
        $objSearchDocument_3->addContent("text", "dolor ipsum sit amet, consetetur.");

        $objSearchIndexWriter->updateSearchDocumentToDb($objSearchDocument_1);
        $objSearchIndexWriter->updateSearchDocumentToDb($objSearchDocument_2);
        $objSearchIndexWriter->updateSearchDocumentToDb($objSearchDocument_3);

        parent::setUp();
    }

    protected function tearDown() {
        //since the test cleared the index, we want a fresh index again :)
        $objSearchIndexWriter = new class_module_search_indexwriter();
        $objSearchIndexWriter->indexRebuild();

        foreach(self::$arrObjectIds as $intKey => $strId) {
            $objObject = class_objectfactory::getInstance()->getObject($strId);
            if($objObject !== null)
                $objObject->deleteObjectFromDatabase();

            unset(self::$arrObjectIds[$intKey]);
        }
    }


    public function testTermQuery() {

        $objSearchTerm = new class_module_search_term("blub");
        $objSearchQuery = new class_module_search_term_query($objSearchTerm);
        $arrResults = $this->getResultsFromQuery($objSearchQuery);
        $this->assertEquals(count($arrResults), 1, "simple term query (blub)");
        $this->assertEquals($this->getCountFromQuery($objSearchQuery), 1);

        $objMetadataFilter = new class_module_search_metadata_filter();
        $objMetadataFilter->setFilterModules(array(_search_module_id_));
        $objSearchQuery->setMetadataFilter($objMetadataFilter);
        $arrResults = $this->getResultsFromQuery($objSearchQuery);
        $this->assertEquals(count($arrResults), 0, "metadata filter (blub)");
        $this->assertEquals($this->getCountFromQuery($objSearchQuery), 0);

        $objMetadataFilter->setFilterModules(array(_system_modul_id_, _search_module_id_));
        $objSearchQuery->setMetadataFilter($objMetadataFilter);
        $arrResults = $this->getResultsFromQuery($objSearchQuery);
        $this->assertEquals(count($arrResults), 1, "metadata filter (blub)");
        $this->assertEquals($this->getCountFromQuery($objSearchQuery), 1);

        $objParser = new class_module_search_query_parser();
        $objQuery = $objParser->parseText("subtitle:blub");
        $arrResult = $this->getResultsFromQuery($objQuery);
        $this->assertEquals(count($arrResult), 1, "count error (subtitle:blub)");
        $this->assertEquals($arrResult[0]["score"], 1, "field filter scoring (subtitle:blub)");
        $this->assertEquals($this->getCountFromQuery($objSearchQuery), 1);

        $objQuery = $objParser->parseText("title:hallo");
        $arrResult = $this->getResultsFromQuery($objQuery);
        $this->assertEquals(count($arrResult), 2);
        $this->assertEquals($arrResult[0]["score"], 1, "field filter scoring (title:hallo)");
        $this->assertEquals($this->getCountFromQuery($objQuery), 2);

        $objQuery = $objParser->parseText("ipsum");
        $arrResult = $this->getResultsFromQuery($objQuery);
        $this->assertEquals(count($arrResult), 1);
        $this->assertEquals($arrResult[0]["score"], 3, "field filter scoring (ipsum)");
        $this->assertEquals($this->getCountFromQuery($objQuery), 1);

    }




    private function getResultsFromQuery(interface_search_query $objSearchQuery) {
        $arrParameters = array();
        $objSearchQuery->getListQuery($strQuery, $arrParameters);
        return class_carrier::getInstance()->getObjDB()->getPArray($strQuery, $arrParameters);
    }

    private function getCountFromQuery(interface_search_query $objSearchQuery) {
        $arrParameters = array();
        $objSearchQuery->getCountQuery($strQuery, $arrParameters);
        $arrRow = class_carrier::getInstance()->getObjDB()->getPRow($strQuery, $arrParameters);
        return $arrRow["COUNT(*)"];
    }
}

