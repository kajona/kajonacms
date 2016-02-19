<?php

namespace Kajona\System\Tests;

require_once __DIR__ . "/../../../core/module_system/system/Testbase.php";

use Kajona\System\System\Template;
use Kajona\System\System\Testbase;

/**
 * Class class_test_templateTest
 * @todo add test to parse the attributes themselves
 *
 */
class TemplateTest extends Testbase
{

    public function testBasicSectionParser()
    {
        $strTemplate = <<<HTML

        <list>
            <content>test</content> test %%ende%%
        </list>
HTML;


        $objTemplate = Template::getInstance();
        $strTemplateID = $objTemplate->setTemplate($strTemplate);

        $this->assertTrue($objTemplate->containsSection($strTemplateID, "list"));
        $this->assertTrue(!$objTemplate->containsSection($strTemplateID, "lista"));


        $this->assertEquals("<content>test</content> test %%ende%%", trim($objTemplate->getSectionFromTemplate($strTemplate, "list")));
        $this->assertEquals(trim($strTemplate), trim($objTemplate->getSectionFromTemplate($strTemplate, "list", true)));

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

    public function testEmptySectionParser()
    {
        $strTemplate = <<<HTML

        <list1></list1>

        <list2>content</list2>

        <list3></list3>
HTML;


        $objTemplate = Template::getInstance();
        $strTemplateID = $objTemplate->setTemplate($strTemplate);

        $this->assertTrue($objTemplate->containsSection($strTemplateID, "list1"));
        $this->assertTrue($objTemplate->containsSection($strTemplateID, "list2"));
        $this->assertTrue($objTemplate->containsSection($strTemplateID, "list3"));
        $this->assertTrue(!$objTemplate->containsSection($strTemplateID, "lista"));


        $this->assertEquals("", trim($objTemplate->getSectionFromTemplate($strTemplate, "list1")));
        $this->assertEquals("<list1></list1>", trim($objTemplate->getSectionFromTemplate($strTemplate, "list1", true)));

        $this->assertEquals("content", trim($objTemplate->getSectionFromTemplate($strTemplate, "list2")));
        $this->assertEquals("<list2>content</list2>", trim($objTemplate->getSectionFromTemplate($strTemplate, "list2", true)));

        $this->assertEquals("", trim($objTemplate->getSectionFromTemplate($strTemplate, "list3")));
        $this->assertEquals("<list3></list3>", trim($objTemplate->getSectionFromTemplate($strTemplate, "list3", true)));

    }


    public function testFillCurrentTemplate()
    {
        $strTemplate = <<<HTML
            test %%ende%%
HTML;

        $objTemplate = Template::getInstance();
        $objTemplate->setTemplate($strTemplate);
        $strContent = trim($objTemplate->fillCurrentTemplate(array("ende" => "filled")));
        $this->assertEquals("test filled", $strContent);
    }


    public function testSectionWithAttributesParser()
    {
        $strTemplate = <<<HTML

        <list attribute1="value1" attribute2="value2">
            <content>test</content> test %%ende%%
        </list>
HTML;


        $objTemplate = Template::getInstance();
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


    public function testRemoveSection()
    {
        $strTemplate1 = <<<HTML
a
<list attribute1="value1" attribute2="value2">
    <content>test</content> test %%ende%%
</list>
b
HTML;

        $strTemplate2 = <<<HTML
a
<list>
    <content>test</content> test %%ende%%
</list>
b
HTML;

        $strTemplate3 = <<<HTML
a
<list attribute1="value1" attribute2="value2">
    <content>test</content> test %%ende%%
</list>
b
<list attribute1="value1" attribute2="value2">
    <content>test</content> test %%ende%%
</list>
c
HTML;

        $objTemplate = Template::getInstance();
        $this->assertEquals(
            "a

b", $objTemplate->removeSection($strTemplate1, "list"));

        $this->assertEquals(
            "a

b", $objTemplate->removeSection($strTemplate2, "list"));

        $this->assertEquals(
            "a

b

c", $objTemplate->removeSection($strTemplate3, "list"));


    }


    public function testGetElements()
    {
        $strTemplate = <<<HTML

        <list attribute1="value1" attribute2="value2">
            %%element1_type%%
            %%element2_type1|type2%%
            %%masterelement3_type2%%
        </list>
HTML;


        $objTemplate = Template::getInstance();


        $strSectionID = $objTemplate->setTemplate($objTemplate->getSectionFromTemplate($strTemplate, "list"));

        $arrPlaceholderPage = $objTemplate->getElements($strSectionID, Template::INT_ELEMENT_MODE_REGULAR);
        $this->assertEquals(count($arrPlaceholderPage), 2);
        $this->assertEquals($arrPlaceholderPage[0]["placeholder"], "element1_type");
        $this->assertEquals(count($arrPlaceholderPage[0]["elementlist"]), 1);
        $this->assertEquals($arrPlaceholderPage[0]["elementlist"][0]["name"], "element1");
        $this->assertEquals($arrPlaceholderPage[0]["elementlist"][0]["element"], "type");

        $this->assertEquals($arrPlaceholderPage[1]["placeholder"], "element2_type1|type2");
        $this->assertEquals(count($arrPlaceholderPage[1]["elementlist"]), 2);
        $this->assertEquals($arrPlaceholderPage[1]["elementlist"][0]["name"], "element2");
        $this->assertEquals($arrPlaceholderPage[1]["elementlist"][0]["element"], "type1");
        $this->assertEquals($arrPlaceholderPage[1]["elementlist"][1]["name"], "element2");
        $this->assertEquals($arrPlaceholderPage[1]["elementlist"][1]["element"], "type2");


        $arrPlaceholderMaster = $objTemplate->getElements($strSectionID, Template::INT_ELEMENT_MODE_MASTER);

        $this->assertEquals(count($arrPlaceholderMaster), 3);
        $this->assertEquals($arrPlaceholderMaster[0]["placeholder"], "element1_type");
        $this->assertEquals(count($arrPlaceholderMaster[0]["elementlist"]), 1);
        $this->assertEquals($arrPlaceholderMaster[0]["elementlist"][0]["name"], "element1");
        $this->assertEquals($arrPlaceholderMaster[0]["elementlist"][0]["element"], "type");

        $this->assertEquals($arrPlaceholderMaster[1]["placeholder"], "element2_type1|type2");
        $this->assertEquals(count($arrPlaceholderMaster[1]["elementlist"]), 2);
        $this->assertEquals($arrPlaceholderMaster[1]["elementlist"][0]["name"], "element2");
        $this->assertEquals($arrPlaceholderMaster[1]["elementlist"][0]["element"], "type1");
        $this->assertEquals($arrPlaceholderMaster[1]["elementlist"][1]["name"], "element2");
        $this->assertEquals($arrPlaceholderMaster[1]["elementlist"][1]["element"], "type2");

        $this->assertEquals($arrPlaceholderMaster[2]["placeholder"], "masterelement3_type2");
        $this->assertEquals(count($arrPlaceholderMaster[2]["elementlist"]), 1);
        $this->assertEquals($arrPlaceholderMaster[2]["elementlist"][0]["name"], "masterelement3");
        $this->assertEquals($arrPlaceholderMaster[2]["elementlist"][0]["element"], "type2");

    }


}

