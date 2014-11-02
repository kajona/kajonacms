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


        $strSectionID = $objTemplate->setTemplate($objTemplate->getSectionFromTemplate($strTemplate, "list"));
        $strFilled = trim($objTemplate->fillCurrentTemplate(array(), false));
        $this->assertEquals("<content>test</content> test %%ende%%", $strFilled);

        $strFilled = trim($objTemplate->fillTemplate(array(), $strSectionID, false));
        $this->assertEquals("<content>test</content> test %%ende%%", $strFilled);

        $objTemplate->setTemplate($objTemplate->getSectionFromTemplate($strTemplate, "list"));
        $strFilled = trim($objTemplate->fillCurrentTemplate(array(), true));
        $this->assertEquals("<content>test</content> test", $strFilled);

        $strFilled = trim($objTemplate->fillTemplate(array(), $strSectionID, true));
        $this->assertEquals("<content>test</content> test", $strFilled);
    }


    public function testFillCurrentTemplate() {
        $strTemplate = <<<HTML
            test %%ende%%
HTML;

        $objTemplate = class_template::getInstance();
        $objTemplate->setTemplate($strTemplate);
        $strContent = trim($objTemplate->fillCurrentTemplate(array("ende" => "filled")));
        $this->assertEquals("test filled", $strContent);
    }


    public function testSectionWithAttributesParser() {
        $strTemplate = <<<HTML

        <list attribute1="value1" attribute2="value2">
            <content>test</content> test %%ende%%
        </list>
HTML;


        $objTemplate = class_template::getInstance();
        $strTemplateID = $objTemplate->setTemplate($strTemplate);

        $this->assertTrue($objTemplate->containsSection($strTemplateID, "list"));
        $this->assertTrue(!$objTemplate->containsSection($strTemplateID, "lista"));


        $strSectionID = $objTemplate->setTemplate($objTemplate->getSectionFromTemplate($strTemplate, "list"));
        $strFilled = trim($objTemplate->fillCurrentTemplate(array(), false));
        $this->assertEquals("<content>test</content> test %%ende%%", $strFilled);

        $strFilled = trim($objTemplate->fillTemplate(array(), $strSectionID, false));
        $this->assertEquals("<content>test</content> test %%ende%%", $strFilled);

        $objTemplate->setTemplate($objTemplate->getSectionFromTemplate($strTemplate, "list"));
        $strFilled = trim($objTemplate->fillCurrentTemplate(array(), true));
        $this->assertEquals("<content>test</content> test", $strFilled);

        $strFilled = trim($objTemplate->fillTemplate(array(), $strSectionID, true));
        $this->assertEquals("<content>test</content> test", $strFilled);
    }


}

