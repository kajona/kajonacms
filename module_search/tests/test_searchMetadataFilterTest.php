<?php

require_once(__DIR__ . "/../../module_system/system/class_testbase.php");

class class_test_searchMetadataFilterTest extends class_testbase {

    private static $arrObjectIds = array();

    private static $objTimestamp1 = null;
    private static $objTimestamp2 = null;
    private static $objTimestamp3 = null;


    protected function setUp() {

        self::$objTimestamp1 = new class_date();
        $objObject = $this->createObject("class_module_system_aspect", "");
        self::$arrObjectIds[] = $objObject->getSystemid();
        sleep(3);

        $objSearchIndexWriter = new class_module_search_indexwriter();
        $objSearchIndexWriter->clearIndex();

        $objSearchDocument_1 = new class_module_search_document();
        $objSearchDocument_1->setStrSystemId($objObject->getSystemid());
        $objSearchDocument_1->setDocumentId(generateSystemid());
        $objSearchDocument_1->addContent("title", "hallo welt and even more");
        $objSearchDocument_1->addContent("text", "welt");
        $objSearchDocument_1->addContent("subtitle", "blub");
        $objSearchDocument_1->addContent("text2", "blub");

        self::$objTimestamp2 = new class_date();
        $objObject = $this->createObject("class_module_search_search", "");
        self::$arrObjectIds[] = $objObject->getSystemid();
        sleep(3);

        $objSearchDocument_2 = new class_module_search_document();
        $objSearchDocument_2->setStrSystemId($objObject->getSystemid());
        $objSearchDocument_2->setDocumentId(generateSystemid());
        $objSearchDocument_2->addContent("title", "hallo");
        $objSearchDocument_2->addContent("text", "welt");

        self::$objTimestamp3 = new class_date();
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

        //simple module filter
        $objSearchTerm = new class_module_search_term("blub");
        $objSearchQuery = new class_module_search_term_query($objSearchTerm);
        $objMetadataFilter = new class_module_search_metadata_filter();
        $objMetadataFilter->setFilterModules(array(_search_module_id_));
        $objSearchQuery->setMetadataFilter($objMetadataFilter);

        $arrResults = $this->getResultsFromQuery($objSearchQuery);
        $this->assertEquals(count($arrResults), 0);


        $objMetadataFilter = new class_module_search_metadata_filter();
        $objMetadataFilter->setFilterModules(array(_system_modul_id_));
        $objSearchQuery->setMetadataFilter($objMetadataFilter);

        $arrResults = $this->getResultsFromQuery($objSearchQuery);
        $this->assertEquals(count($arrResults), 1);

        //last modified time
        $objSearchTerm = new class_module_search_term("hallo");
        $objSearchQuery = new class_module_search_term_query($objSearchTerm);

        //start date
        $objMetadataFilter = new class_module_search_metadata_filter();
        $objMetadataFilter->setFilterChangeStartDate(self::$objTimestamp3);
        $objSearchQuery->setMetadataFilter($objMetadataFilter);

        $arrResults = $this->getResultsFromQuery($objSearchQuery);
        $this->assertEquals(count($arrResults), 0);

        //start date 2
        $objMetadataFilter = new class_module_search_metadata_filter();
        $objMetadataFilter->setFilterChangeStartDate(self::$objTimestamp1);
        $objSearchQuery->setMetadataFilter($objMetadataFilter);

        $arrResults = $this->getResultsFromQuery($objSearchQuery);
        $this->assertEquals(count($arrResults), 2);

        //end date
        $objMetadataFilter = new class_module_search_metadata_filter();
        $objMetadataFilter->setFilterChangeEndDate(self::$objTimestamp3);
        $objSearchQuery->setMetadataFilter($objMetadataFilter);

        $arrResults = $this->getResultsFromQuery($objSearchQuery);
        $this->assertEquals(count($arrResults), 2);

        //interval
        $objMetadataFilter = new class_module_search_metadata_filter();
        $objMetadataFilter->setFilterChangeStartDate(self::$objTimestamp2);
        $objMetadataFilter->setFilterChangeEndDate(self::$objTimestamp3);
        $objSearchQuery->setMetadataFilter($objMetadataFilter);

        $arrResults = $this->getResultsFromQuery($objSearchQuery);
        $this->assertEquals(count($arrResults), 1);

    }


    public function testClassFilter() {
        //simple module filter
        $objSearchTerm = new class_module_search_term("blub");
        $objSearchQuery = new class_module_search_term_query($objSearchTerm);
        $objMetadataFilter = new class_module_search_metadata_filter();
        $objMetadataFilter->setArrFilterClasses(array("class_module_system_setting"));
        $objSearchQuery->setMetadataFilter($objMetadataFilter);

        $arrResults = $this->getResultsFromQuery($objSearchQuery);
        $this->assertEquals(count($arrResults), 0);


        $objMetadataFilter = new class_module_search_metadata_filter();
        $objMetadataFilter->setArrFilterClasses(array("class_module_system_aspect"));
        $objSearchQuery->setMetadataFilter($objMetadataFilter);

        $arrResults = $this->getResultsFromQuery($objSearchQuery);
        $this->assertEquals(count($arrResults), 1);
    }


    private function getResultsFromQuery(interface_search_query $objSearchQuery) {
        $arrParameters = array();
        $objSearchQuery->getListQuery($strQuery, $arrParameters);
        return class_carrier::getInstance()->getObjDB()->getPArray($strQuery, $arrParameters);
    }
}

