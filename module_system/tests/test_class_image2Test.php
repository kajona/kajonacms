<?php

require_once (__DIR__."/../../module_system/system/class_testbase.php");

class class_test_class_image2 extends class_testbase  {

    public function testParseColorRgb() {

        list($red, $green, $blue) = class_image2::parseColorRgb("#ff0010");
        $this->assertEquals(255, $red);
        $this->assertEquals(0, $green);
        $this->assertEquals(16, $blue);

        list($red, $green, $blue, $alpha) = class_image2::parseColorRgb("#FF0010FF");
        $this->assertEquals(255, $red);
        $this->assertEquals(0, $green);
        $this->assertEquals(16, $blue);
        $this->assertEquals(127, $alpha);
    }

}

