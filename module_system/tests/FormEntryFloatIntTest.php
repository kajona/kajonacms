<?php
/*"******************************************************************************************************
*   (c) 2010-2016 ARTEMEON                                                                              *
*-------------------------------------------------------------------------------------------------------*
*	$Id$                                         *
********************************************************************************************************/
namespace AGP\Agp_Commons\Tests;

use Kajona\System\Admin\Formentries\FormentryFloat;
use Kajona\System\Admin\Formentries\FormentryInt;
use Kajona\System\System\Carrier;
use Kajona\System\System\Lang;
use Kajona\System\System\Model;
use Kajona\System\Tests\Testbase;

class FormEntryFloatIntTest extends Testbase
{

    private $strLang = null;

    protected function setUp()
    {
        if($this->strLang == null) {
            $this->strLang = Lang::getInstance()->getStrTextLanguage();
        }
    }

    protected function tearDown()
    {
        //unset params array
        $this->unsetParam("name_maxvverlnachmassnahmen");
        $this->unsetParam("name_eintrittswrvormassnahmen");
        Lang::getInstance()->setStrTextLanguage($this->strLang);
    }

    /**
     * @dataProvider dataProvider
     */
    public function testFormentryFloat($strLang)
    {
        Lang::getInstance()->setStrTextLanguage($strLang);

        $objSourceObject = new FormentryTestModel();
        $objFormEntryFloat = new FormentryFloat("name", "eintrittswrvormassnahmen", $objSourceObject);
        $objLang = Carrier::getInstance()->getObjLang();
        $strStyleThousand = $objLang->getLang("numberStyleThousands", "system");
        $strStyleDecimal = $objLang->getLang("numberStyleDecimal", "system");

        //check output
        $arrTestValues = array();
        $arrTestValues[""] = "";
        $arrTestValues["2"] = "2{$strStyleDecimal}00";
        $arrTestValues["1000"] = "1{$strStyleThousand}000{$strStyleDecimal}00";
        $arrTestValues["10000"] = "10{$strStyleThousand}000{$strStyleDecimal}00";
        $arrTestValues["10000.23"] = "10{$strStyleThousand}000{$strStyleDecimal}23";
        foreach ($arrTestValues as $strDBValue => $strExpectedOutput) {
            $objFormEntryFloat->setStrValue($strDBValue);
            $strValueAsText = $objFormEntryFloat->getValueAsText();
            $this->assertEquals($strExpectedOutput, $strValueAsText);
        }

        //check input
        $arrTestValues = array();
        $arrTestValues["2"] = "2";
        $arrTestValues["2{$strStyleDecimal}00"] = "2.00";
        $arrTestValues["1{$strStyleThousand}000{$strStyleDecimal}00"] = "1000.00";
        $arrTestValues["10{$strStyleThousand}000{$strStyleDecimal}00"] = "10000.00";
        $arrTestValues["10{$strStyleThousand}000{$strStyleDecimal}23"] = "10000.23";
        $arrTestValues["1{$strStyleThousand}0{$strStyleThousand}0{$strStyleThousand}000{$strStyleThousand}000{$strStyleDecimal}23"] = "100000000.23";
        foreach ($arrTestValues as $strInputValue => $strExpectedDBValue) {
            Carrier::getInstance()->setParam("name_eintrittswrvormassnahmen", $strInputValue);
            $objFormEntryFloat = new FormentryFloat("name", "eintrittswrvormassnahmen", $objSourceObject);
            $objFormEntryFloat->setValueToObject();
            $this->assertEquals($strExpectedDBValue, $objSourceObject->getFloatEintrittswrVorMassnahmen());
        }
    }


    /**
     * @dataProvider dataProvider
     */
    public function testFormentryInt($strLang)
    {
        Lang::getInstance()->setStrTextLanguage($strLang);

        $objSourceObject = new FormentryTestModel();
        $objFormEntryInt = new FormentryInt("name", "maxvverlnachmassnahmen", $objSourceObject);

        $objLang = Carrier::getInstance()->getObjLang();
        $strStyleThousand = $objLang->getLang("numberStyleThousands", "system");
        $strStyleDecimal = $objLang->getLang("numberStyleDecimal", "system");

        //check output
        $arrTestValues = array();
        $arrTestValues[""] = "";
        $arrTestValues["2"] = "2";
        $arrTestValues["1000"] = "1{$strStyleThousand}000";
        $arrTestValues["10000"] = "10{$strStyleThousand}000";
        $arrTestValues["100000000"] = "100{$strStyleThousand}000{$strStyleThousand}000";
        foreach ($arrTestValues as $strDBValue => $strExpectedOutput) {
            $objFormEntryInt->setStrValue($strDBValue);
            $strValueAsText = $objFormEntryInt->getValueAsText();
            $this->assertEquals($strExpectedOutput, $strValueAsText);
        }

        //check invalid inputs
        $arrTestValues = array();
        $arrTestValues["2{$strStyleDecimal}00"] = "2.00";
        $arrTestValues["2{$strStyleThousand}000{$strStyleThousand}000{$strStyleThousand}000{$strStyleThousand}000{$strStyleDecimal}00"] = "2000000000000.00";
        $arrTestValues["1{$strStyleThousand}000{$strStyleDecimal}00"] = "1000.00";
        $arrTestValues["10{$strStyleThousand}000{$strStyleDecimal}00"] = "10000.00";
        $arrTestValues["100{$strStyleThousand}000{$strStyleThousand}000{$strStyleDecimal}00"] = "100000000.00";
        $arrTestValues["1{$strStyleThousand}0{$strStyleThousand}0{$strStyleThousand}000{$strStyleThousand}000{$strStyleDecimal}23"] = "100000000.23";
        foreach ($arrTestValues as $strInputValue => $strExpectedValue) {
            Carrier::getInstance()->setParam("name_maxvverlnachmassnahmen", $strInputValue);
            $objFormEntryInt = new FormentryInt("name", "maxvverlnachmassnahmen", $objSourceObject);
            $this->assertEquals($strExpectedValue, $objFormEntryInt->getStrValue());
        }


        //check input
        $arrTestValues = array();
        $arrTestValues["2"] = "2";
        $arrTestValues["2{$strStyleThousand}000{$strStyleThousand}000{$strStyleThousand}000{$strStyleThousand}000"] = "2000000000000";
        $arrTestValues["1{$strStyleThousand}000"] = "1000";
        $arrTestValues["10{$strStyleThousand}000"] = "10000";
        $arrTestValues["100{$strStyleThousand}000{$strStyleThousand}000"] = "100000000";
        $arrTestValues["1{$strStyleThousand}0{$strStyleThousand}0{$strStyleThousand}000{$strStyleThousand}000"] = "100000000";
        foreach ($arrTestValues as $strInputValue => $strExpectedDBValue) {
            Carrier::getInstance()->setParam("name_maxvverlnachmassnahmen", $strInputValue);
            $objFormEntryInt = new FormentryInt("name", "maxvverlnachmassnahmen", $objSourceObject);
            $objFormEntryInt->setValueToObject();

            $this->assertTrue($objFormEntryInt->validateValue());
            $this->assertEquals($strExpectedDBValue, $objSourceObject->getIntMaxvVerlNachMassnahmen());
        }
    }


    public function dataProvider() {
        return array(
            array("de"),
            array("en")
        );
    }

    private function unsetParam($strKey) {
        $objReflection = new \ReflectionClass(Carrier::class);
        $objProperty = $objReflection->getProperty("arrParams");
        $objProperty->setAccessible(true);

        $arrParams = Carrier::getAllParams();
        unset($arrParams[$strKey]);

        $objProperty->setValue($arrParams);
    }
}

class FormentryTestModel extends Model
{

    /**
     * @var int
     * @fieldType Kajona\System\Admin\Formentries\FormentryInt
     */
    private $intMaxvverlnachmassnahmen = 0;

    /**
     * @var int
     * @fielType Kajona\System\Admin\Formentries\FormentryFloat
     */
    private $floatEintrittswrvormassnahmen = 0;

    /**
     * @param int $intEintrittswrvormassnahmen
     */
    public function setFloatEintrittswrvormassnahmen($intEintrittswrvormassnahmen)
    {
        $this->floatEintrittswrvormassnahmen = $intEintrittswrvormassnahmen;
    }

    /**
     * @return int
     */
    public function getFloatEintrittswrvormassnahmen()
    {
        return $this->floatEintrittswrvormassnahmen;
    }

    /**
     * @param int $intMaxvverlnachmassnahmen
     */
    public function setIntMaxvverlnachmassnahmen($intMaxvverlnachmassnahmen)
    {
        $this->intMaxvverlnachmassnahmen = $intMaxvverlnachmassnahmen;
    }

    /**
     * @return int
     */
    public function getIntMaxvverlnachmassnahmen()
    {
        return $this->intMaxvverlnachmassnahmen;
    }


}