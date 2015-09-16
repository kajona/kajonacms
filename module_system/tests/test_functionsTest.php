<?php

require_once (__DIR__."/../../module_system/system/class_testbase.php");

/**
 * Class class_test_functions
 */
class class_test_functions extends class_testbase  {

    /**
     * @return void
     */
    public function testReplaceTextLinks() {
        /**
         * @todo: white-space handling is still messed up
         */

        //change nothing
        $this->assertEquals("hello world", replaceTextLinks("hello world"));

        //simple link
        $this->assertEquals("hello<a href=\"http://www.kajona.de\"> http://www.kajona.de</a> world", replaceTextLinks("hello http://www.kajona.de world"));
        $this->assertEquals("hello<a href=\"https://www.kajona.de\"> https://www.kajona.de</a> world", replaceTextLinks("hello https://www.kajona.de world"));
        $this->assertEquals("hello<a href=\"ftp://www.kajona.de\"> ftp://www.kajona.de</a> world", replaceTextLinks("hello ftp://www.kajona.de world"));

        $this->assertEquals("hello<a href=\"ftp://www.kajona.de\"> ftp://www.kajona.de</a> world hello<a href=\"ftp://www.kajona.de\"> ftp://www.kajona.de</a> world", replaceTextLinks("hello ftp://www.kajona.de world hello ftp://www.kajona.de world"));

        //no replacement if protocol is missing
        $this->assertEquals("hello www.kajona.de world", replaceTextLinks("hello www.kajona.de world"));

        //keep links already existing
        $this->assertEquals("hello <a href=\"http://www.kajona.de\">aaaa</a> world", replaceTextLinks("hello <a href=\"http://www.kajona.de\">aaaa</a> world"));
        $this->assertEquals("hello <a href=\"http://www.kajona.de\">http://www.kajona.de</a> world", replaceTextLinks("hello <a href=\"http://www.kajona.de\">http://www.kajona.de</a> world"));
    }

    /**
     * @return void
     */
    public function testDateToString() {

        class_carrier::getInstance()->getObjLang()->setStrTextLanguage("de");
        if(class_carrier::getInstance()->getObjLang()->getLang("dateStyleShort", "system") != "d.m.Y") {
            return;
        }

        $this->assertEquals("15.05.2013", dateToString(new class_date(20130515122324), false));
        $this->assertEquals("15.05.2013 12:23:24", dateToString(new class_date(20130515122324), true));

        $this->assertEquals("15.05.2013", dateToString(new class_date("20130515122324"), false));
        $this->assertEquals("15.05.2013 12:23:24", dateToString(new class_date("20130515122324"), true));

        $this->assertEquals("15.05.2013", dateToString(20130515122324, false));
        $this->assertEquals("15.05.2013 12:23:24", dateToString(20130515122324, true));

        $this->assertEquals("15.05.2013", dateToString("20130515122324", false));
        $this->assertEquals("15.05.2013 12:23:24", dateToString("20130515122324", true));


        $this->assertEquals("", dateToString(null));
        $this->assertEquals("", dateToString(""));
        $this->assertEquals("", dateToString("asdfsfdsfdsfds"));



        class_carrier::getInstance()->getObjLang()->setStrTextLanguage("en");
        if(class_carrier::getInstance()->getObjLang()->getLang("dateStyleShort", "system") != "m/d/Y") {
            return;
        }

        $this->assertEquals("05/15/2013", dateToString(new class_date(20130515122324), false));
        $this->assertEquals("05/15/2013 12:23:24", dateToString(new class_date(20130515122324), true));

        $this->assertEquals("05/15/2013", dateToString(new class_date("20130515122324"), false));
        $this->assertEquals("05/15/2013 12:23:24", dateToString(new class_date("20130515122324"), true));

        $this->assertEquals("05/15/2013", dateToString(20130515122324, false));
        $this->assertEquals("05/15/2013 12:23:24", dateToString(20130515122324, true));

        $this->assertEquals("05/15/2013", dateToString("20130515122324", false));
        $this->assertEquals("05/15/2013 12:23:24", dateToString("20130515122324", true));
    }


    public function testValidateSystemid() {
        $this->assertTrue(validateSystemid("12345678901234567890"));
        $this->assertTrue(validateSystemid("abcdefghijklmnopqrst"));

        $this->assertTrue(!validateSystemid("123456789012345678901"));
        $this->assertTrue(!validateSystemid("abcdefghijklmnopqrstu"));

        $this->assertTrue(!validateSystemid("1234567890123456789"));
        $this->assertTrue(!validateSystemid("abcdefghijklmnopqrs"));

        $this->assertTrue(!validateSystemid("12345678901234567890 123"));
        $this->assertTrue(!validateSystemid("abcdefghijklmnopqrst abc"));

        $this->assertTrue(!validateSystemid("abc 12345678901234567890 123"));
        $this->assertTrue(!validateSystemid("123 abcdefghijklmnopqrst abc"));

        $this->assertTrue(!validateSystemid("1234567890!234567890"));
        $this->assertTrue(!validateSystemid("abcdefghij!lmnopqrst"));

        $this->assertTrue(!validateSystemid("1234567890 234567890"));
        $this->assertTrue(!validateSystemid("abcdefghij lmnopqrst"));
    }

    public function testSysIdValidationPerformanceTest() {

        $strTest = "1234567890AbCdEfghij";


        $intStart = microtime(true);
        for($intI = 0; $intI < 10000; $intI++) {
            $this->assertTrue(strlen($strTest) == 20 && preg_match("/([a-z|A-a|0-9]){20}/", $strTest));
        }
        $intEnd = microtime(true);
        echo "preg based : ".($intEnd-$intStart)." sec\n";

        $intStart = microtime(true);

        for($intI = 0; $intI < 10000; $intI++) {
            $this->assertTrue(strlen($strTest) == 20 && ctype_alnum($strTest));
        }
        $intEnd = microtime(true);
        echo "ctype based : ".($intEnd-$intStart)." sec\n";
    }
}

