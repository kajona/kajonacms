<?php

namespace Kajona\System\Tests;

use Kajona\System\System\Carrier;
use Kajona\System\System\ServiceProvider;
use Kajona\System\System\Template;
use Kajona\System\System\Testbase;

/**
 * Class class_test_templateTest
 *
 */
class TemplateFullParseTest extends Testbase
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

        /** @var Template $objTemplate */
        $objTemplate = Carrier::getInstance()->getContainer()->offsetGet(ServiceProvider::STR_TEMPLATE);
        $arrBlocks = $objTemplate->parsePageTemplateString($strTemplate, Template::INT_ELEMENT_MODE_REGULAR);

        $this->assertEquals(count($arrBlocks->getArrBlocks()), 2);
        $this->assertEquals(count($arrBlocks->getArrPlaceholder()), 2);

        $this->assertEquals($arrBlocks->getArrBlocks()["content"]->getStrName(), "content");
        $this->assertEquals($arrBlocks->getArrBlocks()["2ndcontent"]->getStrName(), "2ndcontent");

        $this->assertEquals(count($arrBlocks->getArrBlocks()["content"]->getArrBlocks()), 2);
        $this->assertEquals(count($arrBlocks->getArrBlocks()["2ndcontent"]->getArrBlocks()), 1);
    }


}

