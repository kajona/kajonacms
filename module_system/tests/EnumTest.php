<?php

namespace Kajona\System\Tests;

use Kajona\System\System\EnumBase;
use Kajona\System\System\Exception;

class EnumTest extends Testbase
{

    public function testEnumValid()
    {

        $objEnum = TestEnum::a();

        $this->assertNotNull($objEnum);
        $this->assertEquals($objEnum . "", "a");

        $this->assertTrue($objEnum->equals(TestEnum::a()));
        $this->assertTrue(!$objEnum->equals(TestEnum::b()));

    }

    /**
     * @expectedException Exception
     */
    public function testEnumInvalid()
    {
        $objEnum = TestEnum::d();
        $this->assertNull($objEnum);
    }

}

/**
 * Class class_test_enum
 * @method static TestEnum a()
 * @method static TestEnum b()
 * @method static TestEnum c()
 */
class TestEnum extends EnumBase
{
    protected function getArrValues()
    {
        return array("a", "b", "c");
    }
}

