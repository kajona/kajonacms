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

        $arrBlocks = $objParser->readBlocks($strTemplate);


    }

}

