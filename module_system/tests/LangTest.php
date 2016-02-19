<?php

namespace Kajona\System\Tests;
require_once __DIR__."/../../../core/module_system/system/Testbase.php";
use Kajona\System\System\Lang;
use Kajona\System\System\Testbase;

class LangTest extends Testbase  {


    public function testStringToPlaceholder() {

        $objLang = Lang::getInstance();

        $this->assertEquals($objLang->stringToPlaceholder("test_PlaceHolder"), "test_place_holder");
        $this->assertEquals($objLang->stringToPlaceholder("Test_PlaceHolder"), "test_place_holder");
    }


    public function testStringParameters() {
        $objLang = Lang::getInstance();

        $this->assertEquals($objLang->replaceParams("lorem {0} dolor {1} amet", array("ipsum", "sit")), "lorem ipsum dolor sit amet");
        $this->assertEquals($objLang->replaceParams("lorem {0} dolor {1} amet", array()), "lorem {0} dolor {1} amet");
        $this->assertEquals($objLang->replaceParams("lorem {0} dolor {1} amet", array("ipsum")), "lorem ipsum dolor {1} amet");

        $this->assertEquals($objLang->replaceParams("lorem {0} dolor {0} amet", array("ipsum")), "lorem ipsum dolor ipsum amet");
    }




    public function testPerformanceTest() {

        $objLang = Lang::getInstance();

        $arrParameters = array("lorem", "ipsum", "dolor", "sit", "amet");
        $strPropertyRaw = "lorem {0} ipsum {1} dolor {2} sit {3} amet {4} {0}";

        $intStart = microtime(true);
        for($intI = 0; $intI <= 100; $intI++) {
            $strProperty = $strPropertyRaw;
            foreach($arrParameters as $intKey => $strParameter) {
                $strProperty = uniStrReplace("{".$intKey."}", $strParameter, $strProperty);
            }
            $this->assertEquals($strProperty, "lorem lorem ipsum ipsum dolor dolor sit sit amet amet lorem");
        }
        $intEnd = microtime(true);

        echo "uniStrReplace: ".($intEnd-$intStart)." sec\n";


        $intStart = microtime(true);
        for($intI = 0; $intI <= 100; $intI++) {
            $strProperty = uniStrReplace(array_map(function($i) {return "{".$i."}";}, array_keys($arrParameters)), $arrParameters, $strPropertyRaw);
            $this->assertEquals($strProperty, "lorem lorem ipsum ipsum dolor dolor sit sit amet amet lorem");
        }
        $intEnd = microtime(true);

        echo "array based uniStrReplace: ".($intEnd-$intStart)." sec\n";


        $intStart = microtime(true);
        for($intI = 0; $intI <= 100; $intI++) {
            $strProperty = preg_replace_callback("/{(\d)}/", function($hit) use ($arrParameters) { return $arrParameters[$hit[1]]; } , $strPropertyRaw);
            $this->assertEquals($strProperty, "lorem lorem ipsum ipsum dolor dolor sit sit amet amet lorem");
        }
        $intEnd = microtime(true);

        echo "preg_replace based : ".($intEnd-$intStart)." sec\n";


        $intStart = microtime(true);
        for($intI = 0; $intI <= 100; $intI++) {
            $strProperty = $objLang->replaceParams($strPropertyRaw, $arrParameters);
            $this->assertEquals($strProperty, "lorem lorem ipsum ipsum dolor dolor sit sit amet amet lorem");
        }
        $intEnd = microtime(true);

        echo "current implementation : ".($intEnd-$intStart)." sec\n";

    }
}

