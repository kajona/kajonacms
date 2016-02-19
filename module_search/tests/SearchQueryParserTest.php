<?php

namespace Kajona\Search\Tests;
require_once __DIR__."/../../../core/module_system/system/Testbase.php";
use Kajona\Search\System\SearchBooleanQuery;
use Kajona\Search\System\SearchQueryParser;
use Kajona\Search\System\SearchTermQuery;
use Kajona\System\System\Testbase;

class SearchQueryParserTest extends Testbase {


    public function testSpecialCharacterReplacement()
    {
        $objParser = new SearchQueryParser();

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


    public function testQueryParser() {
        echo "Test Query Parser\n";

        $objParser = new SearchQueryParser();

        // Must
        echo "Must...\n";
        $objQuery = $objParser->parseText("hello");
        /** @var $objQuery SearchTermQuery */
        $this->assertTrue($objQuery instanceof SearchTermQuery, "wrong query type");
        $this->assertEquals($objQuery->getObjTerm()->getStrText(), "hello");

        echo "Must...\n";
        $objQuery = $objParser->parseText("glückwunsch");
        /** @var $objQuery SearchTermQuery */
        $this->assertTrue($objQuery instanceof SearchTermQuery, "wrong query type");
        $this->assertEquals($objQuery->getObjTerm()->getStrText(), "glückwunsch");


        // Must - Must
        echo "Must - Must...\n";
        $objQuery = $objParser->parseText("hello world");
        $this->assertTrue($objQuery instanceof SearchBooleanQuery, "wrong query type");
        if($objQuery instanceof SearchBooleanQuery) {
            /** @var $objQuery SearchBooleanQuery */
            $this->assertEquals(count($objQuery->getMustOccurs()), 2);
            $this->assertEquals(count($objQuery->getMustNotOccurs()), 0);
            $this->assertEquals(count($objQuery->getShouldNotOccurs()), 0);
            $this->assertEquals(count($objQuery->getMustNotOccurs()), 0);

            $this->assertEquals($objQuery->getMustOccurs()[0]->getStrText(), "hello");
            $this->assertEquals($objQuery->getMustOccurs()[1]->getStrText(), "world");
        }

        // Must - Should
        echo "Must - Should...\n";
        $objQuery = $objParser->parseText("+hello world");
        $this->assertTrue($objQuery instanceof SearchBooleanQuery, "wrong query type");
        if($objQuery instanceof SearchBooleanQuery) {
            /** @var $objQuery SearchBooleanQuery */
            $this->assertEquals(count($objQuery->getMustOccurs()), 1);
            $this->assertEquals(count($objQuery->getShouldOccurs()), 1);
            $this->assertEquals(count($objQuery->getMustNotOccurs()), 0);
            $this->assertEquals(count($objQuery->getShouldNotOccurs()), 0);
            $this->assertEquals($objQuery->getMustOccurs()[0]->getStrText(), "hello");
            $this->assertEquals($objQuery->getShouldOccurs()[0]->getStrText(), "world");
        }

        // Must - Must - Should
        echo "Must - Must - Should...\n";
        $objQuery = $objParser->parseText("+hello +world blub");
        $this->assertTrue($objQuery instanceof SearchBooleanQuery, "wrong query type");
        if($objQuery instanceof SearchBooleanQuery) {
            /** @var $objQuery SearchBooleanQuery */
            $this->assertEquals(count($objQuery->getMustOccurs()), 2);
            $this->assertEquals(count($objQuery->getShouldOccurs()), 1);
            $this->assertEquals(count($objQuery->getMustNotOccurs()), 0);
            $this->assertEquals(count($objQuery->getShouldNotOccurs()), 0);
            $this->assertEquals($objQuery->getMustOccurs()[0]->getStrText(), "hello");
            $this->assertEquals($objQuery->getMustOccurs()[1]->getStrText(), "world");
            $this->assertEquals($objQuery->getShouldOccurs()[0]->getStrText(), "blub");
        }

        // Must - Must - MustNot
        echo "Must - Must - MustNot...\n";
        $objQuery = $objParser->parseText("+hello +world -blub");
        $this->assertTrue($objQuery instanceof SearchBooleanQuery, "wrong query type");
        if($objQuery instanceof SearchBooleanQuery) {
            /** @var $objQuery SearchBooleanQuery */
            $this->assertEquals(count($objQuery->getMustOccurs()), 2);
            $this->assertEquals(count($objQuery->getShouldOccurs()), 0);
            $this->assertEquals(count($objQuery->getMustNotOccurs()), 1);
            $this->assertEquals(count($objQuery->getShouldNotOccurs()), 0);
            $this->assertEquals($objQuery->getMustOccurs()[0]->getStrText(), "hello");
            $this->assertEquals($objQuery->getMustOccurs()[1]->getStrText(), "world");
            $this->assertEquals($objQuery->getMustNotOccurs()[0]->getStrText(), "blub");
        }

        // Must - MustNot
        echo "Must - MustNot...\n";
        $objQuery = $objParser->parseText("hello -world");
        $this->assertTrue($objQuery instanceof SearchBooleanQuery, "wrong query type");
        if($objQuery instanceof SearchBooleanQuery) {
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

