<?php

require_once(__DIR__ . "/../../module_system/system/class_testbase.php");

class class_test_searchIndexerTest extends class_testbase {


    public function testUnicodeIndexer() {
        $strText = "Hänsel und Gretel verirrten sich schließlich im Wald";

        $objAnalyzer = new class_module_search_standard_analyzer();
        $arrResults = $objAnalyzer->analyze($strText);

        $arrResults = array_keys($arrResults);
        $this->assertEquals(count($arrResults), 7);
        $this->assertEquals($arrResults[0], "hänsel");
        $this->assertEquals($arrResults[1], "und");
        $this->assertEquals($arrResults[2], "gretel");
        $this->assertEquals($arrResults[3], "verirrten");
        $this->assertEquals($arrResults[4], "sich");
        $this->assertEquals($arrResults[5], "schließlich");
        $this->assertEquals($arrResults[6], "wald");
    }

    public function testIndexCreate() {

        $objSearchDocument = new class_module_search_document();
        $objSearchDocument->setDocumentId(1);

        $objSearchContent = new class_module_search_content();
        $objSearchContent->setFieldName('title');
        $objSearchContent->setContent('bla');
        $objSearchContent->setDocumentId(1);

        $objSearchDocument->addContentObj($objSearchContent);

        $objSearchContent = new class_module_search_content();
        $objSearchContent->setFieldName('title');
        $objSearchContent->setContent('blub');
        $objSearchContent->setDocumentId(1);

        $objSearchDocument->addContentObj($objSearchContent);

        $this->assertEquals(count($objSearchDocument->getContent()), 2, "manual added test");

        // test tokenizer
        $objSearchDocument->addContent("title", "hAha DUdub");
        $this->assertEquals(count($objSearchDocument->getContent()), 4);

        // test lowerize
        $this->assertEquals($objSearchDocument->getContent()[2]->getContent(), 'haha');
        $this->assertEquals($objSearchDocument->getContent()[3]->getContent(), 'dudub');

        // test id management
        $this->assertEquals($objSearchDocument->getContent()[2]->getDocumentId(), $objSearchDocument->getDocumentId(), "id management test");

        // test short entry ignoring
        $objSearchDocument->addContent("title", "in hIulk, It, fs fslong or.");
        $arrContent = $objSearchDocument->getContent();
        $this->assertEquals(count($arrContent), 6);
        $this->assertEquals($arrContent[4]->getContent(), 'hiulk');
        $this->assertEquals($arrContent[5]->getContent(), 'fslong');

        //test blacklisting
        $objSearchDocument2 = new class_module_search_document();
        $objSearchDocument2->setDocumentId(2);
        $objSearchDocument2->addContent("title", " this is allowed and this is not ");
        $arrContent = $objSearchDocument2->getContent();
        $this->assertEquals(count($arrContent), 3);
        $this->assertEquals($arrContent[0]->getContent(), 'this');
        $this->assertEquals($arrContent[0]->getScore(), 2);
        $this->assertEquals($arrContent[1]->getContent(), 'allowed');
        $this->assertEquals($arrContent[2]->getContent(), 'not');
    }



    public function testStandardAnalyzer() {
        $objAnalyzer = new class_module_search_standard_analyzer();
        $arrResults = $objAnalyzer->analyze("bl");
        $this->assertEquals(count($arrResults), 0);
        $arrResults = $objAnalyzer->analyze("bl  blub");
        $this->assertEquals(count($arrResults), 1);
        $arrResults = $objAnalyzer->analyze(" blub bl ");
        $this->assertEquals(count($arrResults), 1);

    }



    public function testFullIndexWriter() {
        if(@ini_get("max_execution_time") < 300)
            @ini_set("max_execution_time", 300);

        $indexWriter = new class_module_search_indexwriter();

        $time_start = microtime(true);
        $indexWriter->indexRebuild();
        $time_end = microtime(true);
        $time = $time_end - $time_start;
        echo "Index erstellt: ", sprintf('%f', $time), " sec.\n";

    }


}

