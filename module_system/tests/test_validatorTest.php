<?php

require_once (__DIR__."/../../module_system/system/class_testbase.php");

class class_validatorTest extends class_testbase  {
    private static $arrDelete = array();

    protected function setUp() {
        parent::setUp();
    }

    public function testIntValidator() {
        $objValidator = new class_int_validator();
        $this->assertEquals($objValidator->validate("-1"), true);
        $this->assertEquals($objValidator->validate("0"), true);
        $this->assertEquals($objValidator->validate("1"), true);
        $this->assertEquals($objValidator->validate("1.1"), false);
        $this->assertEquals($objValidator->validate("-1.1"), false);
        $this->assertEquals($objValidator->validate(""), false);
        $this->assertEquals($objValidator->validate("abc"), false);
        $this->assertEquals($objValidator->validate("-abc"), false);
        $this->assertEquals($objValidator->validate("-1abc"), false);
        $this->assertEquals($objValidator->validate("1-abc"), false);
    }

    public function testPosIntValidator() {
        $objValidator = new class_posint_validator();

        $this->assertEquals($objValidator->validate("-1"), false);
        $this->assertEquals($objValidator->validate("0"), true);
        $this->assertEquals($objValidator->validate("1"), true);
        $this->assertEquals($objValidator->validate("1.1"), false);
        $this->assertEquals($objValidator->validate("-1.1"), false);
        $this->assertEquals($objValidator->validate(""), false);
        $this->assertEquals($objValidator->validate("abc"), false);
        $this->assertEquals($objValidator->validate("-abc"), false);
        $this->assertEquals($objValidator->validate("-1abc"), false);
        $this->assertEquals($objValidator->validate("1-abc"), false);
    }


    protected function tearDown() {
        parent::tearDown();
    }


}

