<?php

namespace Kajona\System\Tests;

use Kajona\System\System\Image2;
use Kajona\System\System\Testbase;

class Image2Test extends Testbase
{

    public function testParseColorRgbHex()
    {

        list($red, $green, $blue) = Image2::parseColorRgb("#ff0010");
        $this->assertEquals($red, 255);
        $this->assertEquals($green, 0);
        $this->assertEquals($blue, 16);

        list($red, $green, $blue, $alpha) = Image2::parseColorRgb("#FF0010FF");
        $this->assertEquals($red, 255);
        $this->assertEquals($green, 0);
        $this->assertEquals($blue, 16);
        $this->assertEquals($alpha, 127);
    }

    public function testParseColorRgbDecimal()
    {

        list($red, $green, $blue) = Image2::parseColorRgb("rgb(255,0,16)");
        $this->assertEquals($red, 255);
        $this->assertEquals($green, 0);
        $this->assertEquals($blue, 16);

        list($red, $green, $blue) = Image2::parseColorRgb("rgb( 256, 0, 16 )");
        $this->assertEquals($red, 255);
        $this->assertEquals($green, 0);
        $this->assertEquals($blue, 16);
    }

    public function testParseColorRgbaDecimal()
    {

        list($red, $green, $blue, $alpha) = Image2::parseColorRgb("rgba(255,0,16,1.0)");
        $this->assertEquals($red, 255);
        $this->assertEquals($green, 0);
        $this->assertEquals($blue, 16);
        $this->assertEquals($alpha, 127);

        list($red, $green, $blue, $alpha) = Image2::parseColorRgb("rgba(255,0,16,1.5)");
        $this->assertEquals($red, 255);
        $this->assertEquals($green, 0);
        $this->assertEquals($blue, 16);
        $this->assertEquals($alpha, 127);

        list($red, $green, $blue, $alpha) = Image2::parseColorRgb("rgba(255,0,16,00.83)");
        $this->assertEquals($red, 255);
        $this->assertEquals($green, 0);
        $this->assertEquals($blue, 16);
        $this->assertEquals($alpha, 105);
    }

}

