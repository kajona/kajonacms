<?php

namespace Kajona\System\Tests;

use Kajona\System\System\TemplateBlockContainer;
use Kajona\System\System\TemplateBlocksParser;
use Kajona\System\System\TemplateKajonaSections;
use Kajona\System\System\Testbase;

/**
 * Class class_test_templateTest
 *
 */
class TemplateParseBlocksTest extends Testbase
{


    public function testBlocksParser()
    {
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


        $objParser = new TemplateBlocksParser();

        $arrBlocks = $objParser->readBlocks($strTemplate, TemplateKajonaSections::BLOCKS);

        $this->assertEquals(count($arrBlocks), 4);
        $this->assertEquals(array_keys($arrBlocks)[0], "name1");
        $this->assertEquals(array_keys($arrBlocks)[1], "name2");
        $this->assertEquals(array_keys($arrBlocks)[2], "name3");
        $this->assertEquals(array_keys($arrBlocks)[3], "name4");

    }


    public function testBlockParser()
    {
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


        $objParser = new TemplateBlocksParser();

        /** @var TemplateBlockContainer[] $arrBlocks */
        $arrBlocks = $objParser->readBlocks($strTemplate, TemplateKajonaSections::BLOCK);

        $this->assertEquals(count($arrBlocks), 4);
        $this->assertEquals(array_keys($arrBlocks)[0], "name1");
        $this->assertEquals($arrBlocks["name1"]->getStrName(), "name1");
        $this->assertEquals(trim($arrBlocks["name1"]->getStrContent()), "content1");
        $this->assertEquals(trim($arrBlocks["name1"]->getStrType()), TemplateKajonaSections::BLOCK);

        $this->assertEquals(array_keys($arrBlocks)[1], "name2");
        $this->assertEquals(array_keys($arrBlocks)[2], "name3");
        $this->assertEquals(array_keys($arrBlocks)[3], "name4");

    }


    public function testBlockParser2()
    {
        $strTemplate = <<<HTML

                    <kajona-block kajona-name="Row light">
                        <div class="row-light">
                            <h1>%%headline_plaintext%%</h1>
                            %%content_richtext%%
                            %%date_date%%
                        </div>
                    </kajona-block>

                    <kajona-block kajona-name="Row dark" >
                        <div class="row-dark">
                            <h1>%%headline_plaintext%%</h1>
                            %%content_richtext%%
                        </div>
                    </kajona-block>


HTML;


        $objParser = new TemplateBlocksParser();

        /** @var TemplateBlockContainer[] $arrBlocks */
        $arrBlocks = $objParser->readBlocks($strTemplate, TemplateKajonaSections::BLOCK);

        $this->assertEquals(count($arrBlocks), 2);
        $this->assertEquals(array_keys($arrBlocks)[0], "Row light");
        $this->assertEquals($arrBlocks["Row light"]->getStrName(), "Row light");

        $this->assertEquals(array_keys($arrBlocks)[1], "Row dark");

    }


    /**
     * @throws \Kajona\System\System\TemplateBlocksParserException
     * @expectedException \Kajona\System\System\TemplateBlocksParserException
     */
    public function testInvalidBlocks()
    {
        $strTemplate = <<<HTML


                    <kajona-block kajona-name="Row light, 1" >
                        <div class="row-dark">
                            <h1>%%headline_plaintext%%</h1>
                            %%content_richtext%%
                        </div>
                    </kajona-block>

                    <kajona-block kajona-name="Row, light">
                        <div class="row-light">
                            <h1>%%headline_plaintext%%</h1>
                            %%content_richtext%%
                            %%date_date%%
                        </div>
                    </kajona-block>

                    <kajona-block kajona-name="Row dark" >
                        <div class="row-dark">
                            <h1>%%headline_plaintext%%</h1>
                            %%content_richtext%%
                        </div>
                    </kajona-block>


HTML;


        $objParser = new TemplateBlocksParser();

        /** @var TemplateBlockContainer[] $arrBlocks */
        $arrBlocks = $objParser->readBlocks($strTemplate, TemplateKajonaSections::BLOCK);

    }

}

