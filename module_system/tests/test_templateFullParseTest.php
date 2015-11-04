<?php

require_once (__DIR__."/../../module_system/system/class_testbase.php");

/**
 * Class class_test_templateTest
 *
 */
class class_test_templateFullParseTest extends class_testbase
{


    public function testFullParser()
    {
        $strTemplate = <<<HTML

        <div>
            <div class="contentLeft">
                %%mastertopnews_news%%
            </div>
            <div class="contentRight">
                %%headline_row%%

                <kajona-blocks kajona-name="content">

                    <kajona-block kajona-name="Row light" >
                        <div class="row-light">
                            <h1>%%headline_plaintext%%</h1>
                            %%content_richtext%%
                            %%date_date%%
                        </div>
                    </kajona-block>

                    <kajona-block kajona-name="Row dark" kajona-name-de="Zeile dunkel">
                        <div class="row-dark">
                            <h1>%%headline_plaintext%%</h1>
                            %%content_richtext%%
                        </div>
                    </kajona-block>
                </kajona-blocks>


                <kajona-blocks kajona-name="2ndcontent">

                    <kajona-block kajona-name="Row light" kajona-name-de="Zeile hell">
                        <div class="row-light">
                            %%date_date%%
                        </div>
                    </kajona-block>


                </kajona-blocks>
            %%content_paragraph%%
            </div>
        </div>


HTML;


        $objTemplate = class_template::getInstance();
        $arrBlocks = $objTemplate->parsePageTemplateString($strTemplate, class_template::INT_ELEMENT_MODE_REGULAR);

        $this->assertEquals(count($arrBlocks->getArrBlocks()), 2);
        $this->assertEquals(count($arrBlocks->getArrPlaceholder()), 2);

        $this->assertEquals($arrBlocks->getArrBlocks()["content"]->getStrName(), "content");
        $this->assertEquals($arrBlocks->getArrBlocks()["2ndcontent"]->getStrName(), "2ndcontent");

        $this->assertEquals(count($arrBlocks->getArrBlocks()["content"]->getArrBlocks()), 2);
        $this->assertEquals(count($arrBlocks->getArrBlocks()["2ndcontent"]->getArrBlocks()), 1);
    }




}

