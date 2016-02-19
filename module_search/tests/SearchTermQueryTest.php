<?php

namespace Kajona\Search\Tests;

require_once __DIR__ . "../../../core/module_system/system/Testbase.php";

use Kajona\Search\System\SearchDocument;
use Kajona\Search\System\SearchIndexwriter;
use Kajona\Search\System\SearchMetadataFilter;
use Kajona\Search\System\SearchQueryInterface;
use Kajona\Search\System\SearchQueryParser;
use Kajona\Search\System\SearchTerm;
use Kajona\Search\System\SearchTermQuery;
use Kajona\System\System\Carrier;
use Kajona\System\System\Objectfactory;
use Kajona\System\System\Testbase;

class SearchTermQueryTest extends Testbase
{

    private static $arrObjectIds = array();

    protected function setUp()
    {

        $objObject = $this->createObject("Kajona\\System\\System\\SystemAspect", "");
        self::$arrObjectIds[] = $objObject->getSystemid();

        $objSearchIndexWriter = new SearchIndexwriter();
        $objSearchIndexWriter->clearIndex();

        $objSearchDocument_1 = new SearchDocument();
        $objSearchDocument_1->setStrSystemId($objObject->getSystemid());
        $objSearchDocument_1->setDocumentId(generateSystemid());
        $objSearchDocument_1->addContent("title", "hallo");
        $objSearchDocument_1->addContent("text", "welt");
        $objSearchDocument_1->addContent("subtitle", "blub");
        $objSearchDocument_1->addContent("text2", "blub");

        $objObject = $this->createObject("Kajona\\Search\\System\\SearchSearch", "");
        self::$arrObjectIds[] = $objObject->getSystemid();

        $objSearchDocument_2 = new SearchDocument();
        $objSearchDocument_2->setStrSystemId($objObject->getSystemid());
        $objSearchDocument_2->setDocumentId(generateSystemid());
        $objSearchDocument_2->addContent("title", "hallo");
        $objSearchDocument_2->addContent("text", "welt");

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

        $objSearchTerm = new SearchTerm("blub");
        $objSearchQuery = new SearchTermQuery($objSearchTerm);
        $arrResults = $this->getResultsFromQuery($objSearchQuery);
        $this->assertEquals(count($arrResults), 1, "simple term query (blub)");
        $this->assertEquals($this->getCountFromQuery($objSearchQuery), 1);

        $objMetadataFilter = new SearchMetadataFilter();
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

        $objParser = new SearchQueryParser();
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


    private function getResultsFromQuery(SearchQueryInterface $objSearchQuery)
    {
        $arrParameters = array();
        $objSearchQuery->getListQuery($strQuery, $arrParameters);
        return Carrier::getInstance()->getObjDB()->getPArray($strQuery, $arrParameters);
    }

    private function getCountFromQuery(SearchQueryInterface $objSearchQuery)
    {
        $arrParameters = array();
        $objSearchQuery->getCountQuery($strQuery, $arrParameters);
        $arrRow = Carrier::getInstance()->getObjDB()->getPRow($strQuery, $arrParameters);
        return $arrRow["COUNT(*)"];
    }
}

