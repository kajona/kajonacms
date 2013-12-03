<?php

require_once (__DIR__."/../../module_system/system/class_testbase.php");

class class_test_lang extends class_testbase  {


    public function testStringToPlaceholder() {

        $objLang = class_lang::getInstance();

        $this->assertEquals($objLang->stringToPlaceholder("test_PlaceHolder"), "test_place_holder");
        $this->assertEquals($objLang->stringToPlaceholder("Test_PlaceHolder"), "test_place_holder");
    }


    public function testStringParameters() {
        $objLang = class_lang::getInstance();

        $this->assertEquals($objLang->replaceParams("lorem {0} dolor {1} amet", array("ipsum", "sit")), "lorem ipsum dolor sit amet");
        $this->assertEquals($objLang->replaceParams("lorem {0} dolor {1} amet", array()), "lorem {0} dolor {1} amet");
        $this->assertEquals($objLang->replaceParams("lorem {0} dolor {1} amet", array("ipsum")), "lorem ipsum dolor {1} amet");

        $this->assertEquals($objLang->replaceParams("lorem {0} dolor {0} amet", array("ipsum")), "lorem ipsum dolor ipsum amet");
    }
}

