<?php

require_once (__DIR__."/../../module_system/system/class_testbase.php");

/**
 * Class class_test_templateTest
 *
 */
class class_test_templateParseBlocksTest extends class_testbase  {


    public function testBlocksParser() {
        $strTemplate = <<<HTML

        <kajona-blocks kajona-name="name1" attribute2="value2">
            content1
        </kajona-blocks>

        test

        <kajona-blocks kajona-name="name2" attribute2="value2">
            content2
        </kajona-blocks>

        test

        <kajona-blocks attribute="value2" kajona-name="name3">
            content3
        </kajona-blocks>

        <kajona-blocks attribute="name2" kajona-name="name4">
            content4
        </kajona-blocks>

HTML;


        $objParser = new class_template_blocks_parser();

        $arrBlocks = $objParser->readBlocks($strTemplate, class_template_kajona_sections::BLOCKS);

        $this->assertEquals(count($arrBlocks), 4);
        $this->assertEquals(array_keys($arrBlocks)[0], "name1");
        $this->assertEquals(array_keys($arrBlocks)[1], "name2");
        $this->assertEquals(array_keys($arrBlocks)[2], "name3");
        $this->assertEquals(array_keys($arrBlocks)[3], "name4");

    }


    public function testBlockParser() {
        $strTemplate = <<<HTML

        <kajona-block kajona-name="name1" attribute2="value2">
            content1
        </kajona-block>

        test

        <kajona-block kajona-name="name2" attribute2="value2">
            content2
        </kajona-block>

        test

        <kajona-block attribute="value2" kajona-name="name3">
            content3
        </kajona-block>

        <kajona-block attribute="name2" kajona-name="name4">
            content4
        </kajona-block>

HTML;


        $objParser = new class_template_blocks_parser();

        $arrBlocks = $objParser->readBlocks($strTemplate, class_template_kajona_sections::BLOCK);

        $this->assertEquals(count($arrBlocks), 4);
        $this->assertEquals(array_keys($arrBlocks)[0], "name1");
        $this->assertEquals(array_keys($arrBlocks)[1], "name2");
        $this->assertEquals(array_keys($arrBlocks)[2], "name3");
        $this->assertEquals(array_keys($arrBlocks)[3], "name4");

    }



}

