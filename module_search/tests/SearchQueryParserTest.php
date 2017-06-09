<?php

namespace Kajona\Search\Tests;

use Kajona\Search\System\SearchBooleanQuery;
use Kajona\Search\System\SearchQueryParser;
use Kajona\Search\System\SearchTermQuery;
use Kajona\System\Tests\Testbase;

class SearchQueryParserTest extends Testbase
{


    public function testSpecialCharacterReplacement()
    {
        $objParser = new SearchQueryParser();

        $this->assertEquals("test", $objParser->safeReplaceCharacter("t-est", "-"));
        $this->assertEquals("-test", $objParser->safeReplaceCharacter("-test", "-"));
        $this->assertEquals("-test", $objParser->safeReplaceCharacter("-te-st", "-"));
        $this->assertEquals("test", $objParser->safeReplaceCharacter("test", "-"));
        $this->assertEquals("test -test2", $objParser->safeReplaceCharacter("test -test2", "-"));
        $this->assertEquals("test -test2", $objParser->safeReplaceCharacter("te-st -te-st2", "-"));
        $this->assertEquals("test test2", $objParser->safeReplaceCharacter("te-st te-st2", "-"));
        $this->assertEquals("testtest2", $objParser->safeReplaceCharacter("te-stte-st2", "-"));
        $this->assertEquals("-testtest2", $objParser->safeReplaceCharacter("-te-sttes-t2", "-"));
        $this->assertEquals("testtest2", $objParser->safeReplaceCharacter("te-stte-st2", "-"));
        $this->assertEquals("test+test2", $objParser->safeReplaceCharacter("te-st+te-st2", "-"));
        $this->assertEquals("test +test2", $objParser->safeReplaceCharacter("te-st +te-st2", "-"));
        $this->assertEquals("test +test2", $objParser->safeReplaceCharacter("te-st +test2", "-"));
    }


    public function testQueryParserMust()
    {
        $objParser = new SearchQueryParser();
        // Must
        $objQuery = $objParser->parseText("hello");
        /** @var $objQuery SearchTermQuery */
        $this->assertTrue($objQuery instanceof SearchTermQuery, "wrong query type");
        $this->assertEquals($objQuery->getObjTerm()->getStrText(), "hello");

        $objQuery = $objParser->parseText("glückwunsch");
        /** @var $objQuery SearchTermQuery */
        $this->assertTrue($objQuery instanceof SearchTermQuery, "wrong query type");
        $this->assertEquals($objQuery->getObjTerm()->getStrText(), "glückwunsch");

    }

    public function testQueryParserMustMust()
    {
        $objParser = new SearchQueryParser();

        // Must - Must
        $objQuery = $objParser->parseText("hello world");
        $this->assertTrue($objQuery instanceof SearchBooleanQuery, "wrong query type");
        if ($objQuery instanceof SearchBooleanQuery) {
            /** @var $objQuery SearchBooleanQuery */
            $this->assertEquals(count($objQuery->getMustOccurs()), 2);
            $this->assertEquals(count($objQuery->getMustNotOccurs()), 0);
            $this->assertEquals(count($objQuery->getShouldNotOccurs()), 0);
            $this->assertEquals(count($objQuery->getMustNotOccurs()), 0);

            $this->assertEquals($objQuery->getMustOccurs()[0]->getStrText(), "hello");
            $this->assertEquals($objQuery->getMustOccurs()[1]->getStrText(), "world");
        }


    }

    public function testQueryParserMustShould()
    {
        $objParser = new SearchQueryParser();
        // Must - Should
        $objQuery = $objParser->parseText("+hello world");
        $this->assertTrue($objQuery instanceof SearchBooleanQuery, "wrong query type");
        if ($objQuery instanceof SearchBooleanQuery) {
            /** @var $objQuery SearchBooleanQuery */
            $this->assertEquals(count($objQuery->getMustOccurs()), 1);
            $this->assertEquals(count($objQuery->getShouldOccurs()), 1);
            $this->assertEquals(count($objQuery->getMustNotOccurs()), 0);
            $this->assertEquals(count($objQuery->getShouldNotOccurs()), 0);
            $this->assertEquals($objQuery->getMustOccurs()[0]->getStrText(), "hello");
            $this->assertEquals($objQuery->getShouldOccurs()[0]->getStrText(), "world");
        }

    }

    public function testQueryParserMustMustShould()
    {

        $objParser = new SearchQueryParser();
        // Must - Must - Should
        $objQuery = $objParser->parseText("+hello +world blub");
        $this->assertTrue($objQuery instanceof SearchBooleanQuery, "wrong query type");
        if ($objQuery instanceof SearchBooleanQuery) {
            /** @var $objQuery SearchBooleanQuery */
            $this->assertEquals(count($objQuery->getMustOccurs()), 2);
            $this->assertEquals(count($objQuery->getShouldOccurs()), 1);
            $this->assertEquals(count($objQuery->getMustNotOccurs()), 0);
            $this->assertEquals(count($objQuery->getShouldNotOccurs()), 0);
            $this->assertEquals($objQuery->getMustOccurs()[0]->getStrText(), "hello");
            $this->assertEquals($objQuery->getMustOccurs()[1]->getStrText(), "world");
            $this->assertEquals($objQuery->getShouldOccurs()[0]->getStrText(), "blub");
        }

    }

    public function testQueryParserMustMustMustNot()
    {

        $objParser = new SearchQueryParser();
        // Must - Must - MustNot
        $objQuery = $objParser->parseText("+hello +world -blub");
        $this->assertTrue($objQuery instanceof SearchBooleanQuery, "wrong query type");
        if ($objQuery instanceof SearchBooleanQuery) {
            /** @var $objQuery SearchBooleanQuery */
            $this->assertEquals(count($objQuery->getMustOccurs()), 2);
            $this->assertEquals(count($objQuery->getShouldOccurs()), 0);
            $this->assertEquals(count($objQuery->getMustNotOccurs()), 1);
            $this->assertEquals(count($objQuery->getShouldNotOccurs()), 0);
            $this->assertEquals($objQuery->getMustOccurs()[0]->getStrText(), "hello");
            $this->assertEquals($objQuery->getMustOccurs()[1]->getStrText(), "world");
            $this->assertEquals($objQuery->getMustNotOccurs()[0]->getStrText(), "blub");
        }


    }

    public function testQueryParserMustMustNot()
    {
        $objParser = new SearchQueryParser();

        // Must - MustNot
        $objQuery = $objParser->parseText("hello -world");
        $this->assertTrue($objQuery instanceof SearchBooleanQuery, "wrong query type");
        if ($objQuery instanceof SearchBooleanQuery) {
            /** @var $objQuery SearchBooleanQuery */
            $this->assertEquals(count($objQuery->getMustOccurs()), 1);
            $this->assertEquals(count($objQuery->getMustNotOccurs()), 1);
            $this->assertEquals(count($objQuery->getShouldOccurs()), 0);
            $this->assertEquals(count($objQuery->getShouldNotOccurs()), 0);

            $this->assertEquals($objQuery->getMustOccurs()[0]->getStrText(), "hello");
            $this->assertEquals($objQuery->getMustNotOccurs()[0]->getStrText(), "world");
        }

    }

}

