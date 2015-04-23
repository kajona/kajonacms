<?php

require_once (__DIR__."/../../module_system/system/class_testbase.php");

class class_enumTest extends class_testbase  {

    public function testEnumValid() {

        $objEnum = class_test_enum::a();

        $this->assertNotNull($objEnum);
        $this->assertEquals($objEnum."", "a");

        $this->assertTrue($objEnum->equals(class_test_enum::a()));
        $this->assertTrue(!$objEnum->equals(class_test_enum::b()));

    }

    /**
     * @expectedException class_exception
     */
    public function testEnumInvalid() {
        $objEnum = class_test_enum::d();
        $this->assertNull($objEnum);
    }

}

/**
 * Class class_test_enum
 * @method static class_test_enum a()
 * @method static class_test_enum b()
 * @method static class_test_enum c()
 */
class class_test_enum extends class_enum {
    protected function getArrValues() {
        return array("a", "b", "c");
    }
}

