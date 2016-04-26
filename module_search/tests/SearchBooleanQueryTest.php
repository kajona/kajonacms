<?php

namespace Kajona\Search\Tests;

use Kajona\Search\System\SearchBooleanQuery;
use Kajona\Search\System\SearchDocument;
use Kajona\Search\System\SearchIndexwriter;
use Kajona\Search\System\SearchMetadataFilter;
use Kajona\Search\System\SearchQueryInterface;
use Kajona\Search\System\SearchQueryParser;
use Kajona\Search\System\SearchTerm;
use Kajona\System\System\Carrier;
use Kajona\System\System\Objectfactory;
use Kajona\System\Tests\Testbase;

class SearchBooleanQueryTest extends Testbase
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


    public function testBooleanQuery()
    {

        $objSearchTerm1 = new SearchTerm("hallo");
        $objSearchTerm2 = new SearchTerm("welt");

        $objBooleanSearch = new SearchBooleanQuery();
        $objBooleanSearch->add($objSearchTerm1, SearchBooleanQuery::BOOLEAN_CLAUSE_OCCUR_MUST);
        $objBooleanSearch->add($objSearchTerm2, SearchBooleanQuery::BOOLEAN_CLAUSE_OCCUR_MUST);

        $arrResult = $this->getResultsFromQuery($objBooleanSearch);

        $this->assertEquals(count($arrResult), 2);
        $this->assertEquals($arrResult[0]["score"], 2);
        $this->assertEquals($this->getCountFromQuery($objBooleanSearch), 2);

        // One more optional search term
        $objSearchTerm3 = new SearchTerm("blub");

        $objBooleanSearch->add($objSearchTerm3, SearchBooleanQuery::BOOLEAN_CLAUSE_OCCUR_SHOULD);

        $arrResult = $this->getResultsFromQuery($objBooleanSearch);

        $this->assertEquals(count($arrResult), 2);
        $this->assertEquals($arrResult[0]["score"], 4);
        $this->assertEquals($arrResult[1]["score"], 2);

        $this->assertEquals($this->getCountFromQuery($objBooleanSearch), 2);

        // Module metadata filter
        $objMetadataFilter = new SearchMetadataFilter();
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
        $objParser = new SearchQueryParser();
        $objQuery = $objParser->parseText("+hallo +welt -blub");
        $arrResult = $this->getResultsFromQuery($objQuery);

        $this->assertEquals(count($arrResult), 1);
        $this->assertEquals($this->getCountFromQuery($objQuery), 1);

        $objParser = new SearchQueryParser();
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

