<?php

namespace Kajona\Search\Tests;

require_once __DIR__ . "/../../../core/module_system/system/Testbase.php";

use Kajona\Search\System\SearchDocument;
use Kajona\Search\System\SearchIndexwriter;
use Kajona\Search\System\SearchMetadataFilter;
use Kajona\Search\System\SearchQueryInterface;
use Kajona\Search\System\SearchTerm;
use Kajona\Search\System\SearchTermQuery;
use Kajona\System\System\Carrier;
use Kajona\System\System\Objectfactory;
use Kajona\System\System\Testbase;

class SearchMetadataFilterTest extends Testbase
{

    private static $arrObjectIds = array();

    private static $objTimestamp1 = null;
    private static $objTimestamp2 = null;
    private static $objTimestamp3 = null;


    protected function setUp()
    {

        self::$objTimestamp1 = new \Kajona\System\System\Date();
        $objObject = $this->createObject("Kajona\\System\\System\\SystemAspect", "");
        self::$arrObjectIds[] = $objObject->getSystemid();
        sleep(3);

        $objSearchIndexWriter = new SearchIndexwriter();
        $objSearchIndexWriter->clearIndex();

        $objSearchDocument_1 = new SearchDocument();
        $objSearchDocument_1->setStrSystemId($objObject->getSystemid());
        $objSearchDocument_1->setDocumentId(generateSystemid());
        $objSearchDocument_1->addContent("title", "hallo welt and even more");
        $objSearchDocument_1->addContent("text", "welt");
        $objSearchDocument_1->addContent("subtitle", "blub");
        $objSearchDocument_1->addContent("text2", "blub");

        self::$objTimestamp2 = new \Kajona\System\System\Date();
        $objObject = $this->createObject("Kajona\\Search\\System\\SearchSearch", "");
        self::$arrObjectIds[] = $objObject->getSystemid();
        sleep(3);

        $objSearchDocument_2 = new SearchDocument();
        $objSearchDocument_2->setStrSystemId($objObject->getSystemid());
        $objSearchDocument_2->setDocumentId(generateSystemid());
        $objSearchDocument_2->addContent("title", "hallo");
        $objSearchDocument_2->addContent("text", "welt");

        self::$objTimestamp3 = new \Kajona\System\System\Date();
        $objObject = $this->createObject("Kajona\\System\\System\\SystemAspect", "");
        self::$arrObjectIds[] = $objObject->getSystemid();

        $objSearchDocument_3 = new SearchDocument();
        $objSearchDocument_3->setStrSystemId($objObject->getSystemid());
        $objSearchDocument_3->setDocumentId(generateSystemid());
        $objSearchDocument_3->addContent("title", "lorem ipsum dolor ipsum");
        $objSearchDocument_3->addContent("text", "dolor ipsum sit amet, consetetur.");

        $objSearchIndexWriter->updateSearchDocumentToDb($objSearchDocument_1);
        $objSearchIndexWriter->updateSearchDocumentToDb($objSearchDocument_2);
        $objSearchIndexWriter->updateSearchDocumentToDb($objSearchDocument_3);

        parent::setUp();
    }

    protected function tearDown()
    {
        //since the test cleared the index, we want a fresh index again :)
        $objSearchIndexWriter = new SearchIndexwriter();
        $objSearchIndexWriter->indexRebuild();

        foreach (self::$arrObjectIds as $intKey => $strId) {
            $objObject = Objectfactory::getInstance()->getObject($strId);
            if ($objObject !== null) {
                $objObject->deleteObjectFromDatabase();
            }

            unset(self::$arrObjectIds[$intKey]);
        }
    }


    public function testTermQuery()
    {

        //simple module filter
        $objSearchTerm = new SearchTerm("blub");
        $objSearchQuery = new SearchTermQuery($objSearchTerm);
        $objMetadataFilter = new SearchMetadataFilter();
        $objMetadataFilter->setFilterModules(array(_search_module_id_));
        $objSearchQuery->setMetadataFilter($objMetadataFilter);

        $arrResults = $this->getResultsFromQuery($objSearchQuery);
        $this->assertEquals(count($arrResults), 0);


        $objMetadataFilter = new SearchMetadataFilter();
        $objMetadataFilter->setFilterModules(array(_system_modul_id_));
        $objSearchQuery->setMetadataFilter($objMetadataFilter);

        $arrResults = $this->getResultsFromQuery($objSearchQuery);
        $this->assertEquals(count($arrResults), 1);

        //last modified time
        $objSearchTerm = new SearchTerm("hallo");
        $objSearchQuery = new SearchTermQuery($objSearchTerm);

        //start date
        $objMetadataFilter = new SearchMetadataFilter();
        $objMetadataFilter->setFilterChangeStartDate(self::$objTimestamp3);
        $objSearchQuery->setMetadataFilter($objMetadataFilter);

        $arrResults = $this->getResultsFromQuery($objSearchQuery);
        $this->assertEquals(count($arrResults), 0);

        //start date 2
        $objMetadataFilter = new SearchMetadataFilter();
        $objMetadataFilter->setFilterChangeStartDate(self::$objTimestamp1);
        $objSearchQuery->setMetadataFilter($objMetadataFilter);

        $arrResults = $this->getResultsFromQuery($objSearchQuery);
        $this->assertEquals(count($arrResults), 2);

        //end date
        $objMetadataFilter = new SearchMetadataFilter();
        $objMetadataFilter->setFilterChangeEndDate(self::$objTimestamp3);
        $objSearchQuery->setMetadataFilter($objMetadataFilter);

        $arrResults = $this->getResultsFromQuery($objSearchQuery);
        $this->assertEquals(count($arrResults), 2);

        //interval
        $objMetadataFilter = new SearchMetadataFilter();
        $objMetadataFilter->setFilterChangeStartDate(self::$objTimestamp2);
        $objMetadataFilter->setFilterChangeEndDate(self::$objTimestamp3);
        $objSearchQuery->setMetadataFilter($objMetadataFilter);

        $arrResults = $this->getResultsFromQuery($objSearchQuery);
        $this->assertEquals(count($arrResults), 1);

    }


    public function testClassFilter()
    {
        //simple module filter
        $objSearchTerm = new SearchTerm("blub");
        $objSearchQuery = new SearchTermQuery($objSearchTerm);
        $objMetadataFilter = new SearchMetadataFilter();
        $objMetadataFilter->setArrFilterClasses(array("Kajona\\System\\System\\SystemSetting"));
        $objSearchQuery->setMetadataFilter($objMetadataFilter);

        $arrResults = $this->getResultsFromQuery($objSearchQuery);
        $this->assertEquals(count($arrResults), 0);


        $objMetadataFilter = new SearchMetadataFilter();
        $objMetadataFilter->setArrFilterClasses(array("Kajona\\System\\System\\SystemAspect"));
        $objSearchQuery->setMetadataFilter($objMetadataFilter);

        $arrResults = $this->getResultsFromQuery($objSearchQuery);
        $this->assertEquals(count($arrResults), 1);
    }


    private function getResultsFromQuery(SearchQueryInterface $objSearchQuery)
    {
        $arrParameters = array();
        $objSearchQuery->getListQuery($strQuery, $arrParameters);
        return Carrier::getInstance()->getObjDB()->getPArray($strQuery, $arrParameters);
    }
}

