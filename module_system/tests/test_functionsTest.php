<?php

require_once (__DIR__."/../../module_system/system/class_testbase.php");

class class_test_functions extends class_testbase  {

    public function testReplaceTextLinks() {
        /**
         * @todo: white-space handling is still messed up
         */

        //change nothing
        $this->assertEquals("hello world", replaceTextLinks("hello world"));

        //simple link
        $this->assertEquals("hello<a href=\" http://www.kajona.de\"> http://www.kajona.de</a> world", replaceTextLinks("hello http://www.kajona.de world"));
        $this->assertEquals("hello<a href=\" https://www.kajona.de\"> https://www.kajona.de</a> world", replaceTextLinks("hello https://www.kajona.de world"));
        $this->assertEquals("hello<a href=\" ftp://www.kajona.de\"> ftp://www.kajona.de</a> world", replaceTextLinks("hello ftp://www.kajona.de world"));

        $this->assertEquals("hello<a href=\" ftp://www.kajona.de\"> ftp://www.kajona.de</a> world hello<a href=\" ftp://www.kajona.de\"> ftp://www.kajona.de</a> world", replaceTextLinks("hello ftp://www.kajona.de world hello ftp://www.kajona.de world"));

        //no replacement if protocol is missing
        $this->assertEquals("hello www.kajona.de world", replaceTextLinks("hello www.kajona.de world"));

        //keep links already existing
        $this->assertEquals("hello <a href=\"http://www.kajona.de\">aaaa</a> world", replaceTextLinks("hello <a href=\"http://www.kajona.de\">aaaa</a> world"));
        $this->assertEquals("hello <a href=\"http://www.kajona.de\">http://www.kajona.de</a> world", replaceTextLinks("hello <a href=\"http://www.kajona.de\">http://www.kajona.de</a> world"));
    }

}

