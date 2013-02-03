<?php

require_once (__DIR__."/../../module_system/system/class_testbase.php");

class class_test_lang extends class_testbase  {

    protected function setUp() {
    }


    public function testStringToPlaceholder() {

        $objLang = class_lang::getInstance();

        $this->assertEquals($objLang->stringToPlaceholder("test_PlaceHolder"), "test_place_holder");
        $this->assertEquals($objLang->stringToPlaceholder("Test_PlaceHolder"), "test_place_holder");
    }

}

