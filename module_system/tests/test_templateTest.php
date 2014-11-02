<?php

require_once (__DIR__."/../../module_system/system/class_testbase.php");

class class_test_templateTest extends class_testbase  {

    public function testBasicSectionParser() {
        $strTemplate = <<<HTML

        <list>
            <content>test</content> test %%ende%%
        </list>
HTML;


        $objTemplate = class_template::getInstance();
        $strTemplateID = $objTemplate->setTemplate($strTemplate);

        $this->assertTrue($objTemplate->containsSection($strTemplateID, "list"));
        $this->assertTrue(!$objTemplate->containsSection($strTemplateID, "lista"));


        $objTemplate->setTemplate($objTemplate->getSectionFromTemplate($strTemplate, "list"));
        $strFilled = trim($objTemplate->fillCurrentTemplate(array(), false));
        $this->assertEquals("<content>test</content> test %%ende%%", $strFilled);

        $objTemplate->setTemplate($objTemplate->getSectionFromTemplate($strTemplate, "list"));
        $strFilled = trim($objTemplate->fillCurrentTemplate(array(), true));
        $this->assertEquals("<content>test</content> test", $strFilled);
    }



}

