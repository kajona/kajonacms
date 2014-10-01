<?php

require_once (__DIR__."/../../module_system/system/class_testbase.php");

class class_enumTest extends class_testbase  {

    public function testEnum() {

        $objEnum = null;
        $objException = null;
        try {
            $objEnum = class_test_enum::a();
        } catch(class_exception $objE) {
            $objException = $objE;
        }

        $this->assertNotNull($objEnum);
        $this->assertNull($objException);
        $this->assertEquals($objEnum."", "a");


        $this->assertTrue($objEnum->equals(class_test_enum::a()));
        $this->assertTrue(!$objEnum->equals(class_test_enum::b()));


        $objEnum = null;
        $objException = null;
        try {
            $objEnum = class_test_enum::d();
        } catch(class_exception $objE) {
            $objException = $objE;
        }

        $this->assertNull($objEnum);
        $this->assertNotNull($objException);

    }

}

class class_test_enum extends class_enum {
    protected static $arrAllowedValues = array("a", "b", "c");
}

