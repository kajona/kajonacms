<?php

namespace Kajona\System\Tests;

use Kajona\System\System\Date;
use Kajona\System\System\StringUtil;

class StringTest extends Testbase
{

    public function testStringTest()
    {
    }

    public function testStrToDate()
    {
        $strString = "";
        $objResult = StringUtil::toDate($strString);
        $this->assertNull($objResult);

        $strString = "0";
        $objResult = StringUtil::toDate($strString);
        $this->assertTrue($objResult instanceof Date);

        $strString = new Date();
        $objResult = StringUtil::toDate($strString);
        $this->assertTrue($objResult instanceof Date);
    }

    public function testStrToInt()
    {
        $strString = "";
        $intResult = StringUtil::toInt($strString);
        $this->assertNull($intResult);

        $strString = 0;
        $intResult = StringUtil::toInt($strString);
        $this->assertEquals(0, $intResult);

        $strString = "0";
        $intResult = StringUtil::toInt($strString);
        $this->assertEquals(0, $intResult);

        $strString = "-42";
        $intResult = StringUtil::toInt($strString);
        $this->assertEquals(-42, $intResult);
    }

    public function testStrToArray()
    {

        //empty string
        $strString = "";
        $arrResult = StringUtil::toArray($strString);
        $this->assertNull($arrResult);

        //null value
        $strString = null;
        $arrResult = StringUtil::toArray($strString);
        $this->assertNull($arrResult);

        //empty string
        $strString = "null";
        $arrResult = StringUtil::toArray($strString);
        $this->assertCount(1, $arrResult);
        $this->assertEquals("null", $arrResult[0]);

        //standard cal with string
        $strString = "1,0,3";
        $arrResult = StringUtil::toArray($strString);
        $this->assertTrue(is_array($arrResult));
        $this->assertCount(3, $arrResult);

        //Empty delimiter
        $strString = "1,0,3";
        $arrResult = StringUtil::toArray($strString, "");
        $this->assertNull($arrResult);

        //Delimiter "."
        $strString = "1.0.3";
        $arrResult = StringUtil::toArray($strString, ".");
        $this->assertTrue(is_array($arrResult));
        $this->assertCount(3, $arrResult);

        $strString = array();
        $arrResult = StringUtil::toArray($strString);
        $this->assertTrue(is_array($arrResult));
        $this->assertCount(0, $arrResult);

        $strString = array(2,3,4);
        $arrResult = StringUtil::toArray($strString);
        $this->assertTrue(is_array($arrResult));
        $this->assertCount(3, $arrResult);
        $this->assertEquals(2, $arrResult[0]);
        $this->assertEquals(3, $arrResult[1]);
        $this->assertEquals(4, $arrResult[2]);
    }

    /**
     * @dataProvider startsWithProvider()
     */
    public function testStartsWith($intExpectedResult, $strString, $strSearch)
    {
        $result = StringUtil::startsWith($strString, $strSearch);
        $this->assertEquals($intExpectedResult, $result);
    }

    /**
     * @dataProvider endsWithProvider()
     */
    public function testEndsWith($intExpectedResult, $strString, $strSearch)
    {
        $result = StringUtil::endsWith($strString, $strSearch);
        $this->assertEquals($intExpectedResult, $result);
    }

    public function startsWithProvider()
    {
        return array(
            array(true, '123 456', '123'),
            array(true, '123 456', '123 456'),
            array(true, '123 456', '123 '),
            array(true, '%&§$%/()', '%&'),
            array(false, '123 456', '456'),
            array(false, '123 456', '123 4567'),
            array(false, 'ABC', 'abc'),
        );
    }

    public function endsWithProvider()
    {
        return array(
            array(true, '123 456', '456'),
            array(true, '123 456', '123 456'),
            array(true, '123 456', ' 456'),
            array(true, '%&§$%/()', '()'),
            array(false, '123 456', '123'),
            array(false, '123 456', '123 4567'),
            array(false, 'ABC', 'abc'),
        );
    }
}

