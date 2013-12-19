<?php

require_once (__DIR__."/../../module_system/system/class_testbase.php");

class class_validatorTest extends class_testbase  {
    private static $arrDelete = array();

    protected function setUp() {
        parent::setUp();
    }


    public function testValidatorNames() {
        $strInterfaceName = "interface_validator";
        //load classes
        $arrFiles = class_resourceloader::getInstance()->getFolderContent("/system/validators", array(".php"));
        $arrValidatorClasses = array();
        foreach($arrFiles as $strOneFile) {
            $strClassname = uniStrReplace(".php", "", $strOneFile);
            //create instance
            $objClass = new ReflectionClass($strClassname);
            if($objClass->implementsInterface("interface_validator")) {
                $arrValidatorClasses[$strClassname] = $objClass->newInstance();
            }
        }

        foreach($arrValidatorClasses as $strValidatorClassName => $objValidator) {
            $arrMatches = array();
            preg_match("/class_([a-z_]*)_validator/", $strValidatorClassName, $arrMatches);
            $strName = $objValidator->getStrName();
            $this->assertEquals($arrMatches[1], $strName);
        }

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

