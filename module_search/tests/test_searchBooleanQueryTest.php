<?php

require_once(__DIR__ . "/../../module_system/system/class_testbase.php");

class class_test_searchBooleanQueryTest extends class_testbase {

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




    public function testBooleanQuery() {

        $objSearchTerm1 = new class_module_search_term("hallo");
        $objSearchTerm2 = new class_module_search_term("welt");

        $objBooleanSearch = new class_module_search_boolean_query();
        $objBooleanSearch->add($objSearchTerm1, class_module_search_boolean_query::BOOLEAN_CLAUSE_OCCUR_MUST);
        $objBooleanSearch->add($objSearchTerm2, class_module_search_boolean_query::BOOLEAN_CLAUSE_OCCUR_MUST);

        $arrResult = $this->getResultsFromQuery($objBooleanSearch);

        $this->assertEquals(count($arrResult), 2);
        $this->assertEquals($arrResult[0]["score"], 2);
        $this->assertEquals($this->getCountFromQuery($objBooleanSearch), 2);

        // One more optional search term
        $objSearchTerm3 = new class_module_search_term("blub");

        $objBooleanSearch->add($objSearchTerm3, class_module_search_boolean_query::BOOLEAN_CLAUSE_OCCUR_SHOULD);

        $arrResult = $this->getResultsFromQuery($objBooleanSearch);

        $this->assertEquals(count($arrResult), 2);
        $this->assertEquals($arrResult[0]["score"], 4);
        $this->assertEquals($arrResult[1]["score"], 2);

        $this->assertEquals($this->getCountFromQuery($objBooleanSearch), 2);

        // Module metadata filter
        $objMetadataFilter = new class_module_search_metadata_filter();
        $objMetadataFilter->setFilterModules(array(_system_modul_id_));
        $objBooleanSearch->setMetadataFilter($objMetadataFilter);

        $arrResult = $this->getResultsFromQuery($objBooleanSearch);
        $this->assertEquals(count($arrResult), 1, "Metadata module filter");
        $this->assertEquals($arrResult[0]["score"], 4);

        $this->assertEquals($this->getCountFromQuery($objBooleanSearch), 1);

        $objMetadataFilter->setFilterModules(array(_system_modul_id_, _search_module_id_));
        $objBooleanSearch->setMetadataFilter($objMetadataFilter);

        $arrResult = $this->getResultsFromQuery($objBooleanSearch);
        $this->assertEquals(count($arrResult), 2, "Metadata module filter");

        $this->assertEquals($this->getCountFromQuery($objBooleanSearch), 2);

        // must must mustNot Search
        $objParser = new class_module_search_query_parser();
        $objQuery = $objParser->parseText("+hallo +welt -blub");
        $arrResult = $this->getResultsFromQuery($objQuery);

        $this->assertEquals(count($arrResult), 1);
        $this->assertEquals($this->getCountFromQuery($objQuery), 1);

        $objParser = new class_module_search_query_parser();
        $objQuery = $objParser->parseText("+hallo +subtitle:blub");
        $arrResult = $this->getResultsFromQuery($objQuery);

        $this->assertEquals(count($arrResult), 1);
        $this->assertEquals($arrResult[0]["score"], 2, "field filter scoring");
        $this->assertEquals($this->getCountFromQuery($objQuery), 1);

        $objQuery = $objParser->parseText("title:hallo");
        $arrResult = $this->getResultsFromQuery($objQuery);
        $this->assertEquals(count($arrResult), 2);
        $this->assertEquals($arrResult[0]["score"], 1, "field filter scoring");
        $this->assertEquals($this->getCountFromQuery($objQuery), 2);

        //test new Scoring
        $objQuery = $objParser->parseText("lorem ipsum");
        $arrResult = $this->getResultsFromQuery($objQuery);
        $this->assertEquals(count($arrResult), 1);
        $this->assertEquals($arrResult[0]["score"], 4, "'lorem ipsum' scoring");
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

