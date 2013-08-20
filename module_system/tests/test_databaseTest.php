<?php

require_once (__DIR__."/../../module_system/system/class_testbase.php");

class class_test_database extends class_testbase {

    public function tearDown() {
        $this->flushDBCache();
        if(in_array(_dbprefix_."temp_autotest", class_carrier::getInstance()->getObjDB()->getTables())) {
            $strQuery = "DROP TABLE "._dbprefix_."temp_autotest";
            class_carrier::getInstance()->getObjDB()->_query($strQuery);
        }

        parent::tearDown();
    }


    public function test() {

        $objDB = class_carrier::getInstance()->getObjDB();

        echo "testing database...\n";
        echo "current driver: ".class_carrier::getInstance()->getObjConfig()->getConfig("dbdriver")."\n";

        echo "\tcreating a new table...\n";

        $arrFields = array();
        $arrFields["temp_id"] = array("char20", false);
        $arrFields["temp_long"] = array("long", true);
        $arrFields["temp_double"] = array("double", true);
        $arrFields["temp_char10"] = array("char10", true);
        $arrFields["temp_char20"] = array("char20", true);
        $arrFields["temp_char100"] = array("char100", true);
        $arrFields["temp_char254"] = array("char254", true);
        $arrFields["temp_char500"] = array("char500", true);
        $arrFields["temp_text"] = array("text", true);

        $this->assertTrue($objDB->createTable("temp_autotest", $arrFields, array("temp_id")), "testDataBase createTable");

        echo "\tcreating 50 records...\n";

        for($intI = 1; $intI <= 50; $intI++) {
            $strQuery = "INSERT INTO "._dbprefix_."temp_autotest
                (temp_id, temp_long, temp_double, temp_char10, temp_char20, temp_char100, temp_char254, temp_char500, temp_text)
                VALUES
                ('".generateSystemid()."', 123456".$intI.", 23.45".$intI.", '".$intI."', 'char20".$intI."', 'char100".$intI."', 'char254".$intI."', 'char500".$intI."', 'text".$intI."')";

            $this->assertTrue($objDB->_query($strQuery), "testDataBase insert");
        }


        echo "\tgetRow test\n";
        $strQuery = "SELECT * FROM "._dbprefix_."temp_autotest ORDER BY temp_long ASC";
        $arrRow = $objDB->getRow($strQuery);
        $this->assertTrue(count($arrRow) == 18 || count($arrRow) == 9, "testDataBase getRow count");
        $this->assertEquals($arrRow["temp_char10"], "1", "testDataBase getRow content");

        echo "\tgetArray test\n";
        $strQuery = "SELECT * FROM "._dbprefix_."temp_autotest ORDER BY temp_long ASC";
        $arrRow = $objDB->getArray($strQuery);
        $this->assertEquals(count($arrRow), 50, "testDataBase getArray count");

        $intI = 1;
        foreach($arrRow as $arrSingleRow)
            $this->assertEquals($arrSingleRow["temp_char10"], $intI++, "testDataBase getArray content");

        echo "\tgetArraySection test\n";
        $strQuery = "SELECT * FROM "._dbprefix_."temp_autotest ORDER BY temp_long ASC";
        $arrRow = $objDB->getArraySection($strQuery, 0, 9);
        $this->assertEquals(count($arrRow), 10, "testDataBase getArraySection count");

        $intI = 1;
        foreach($arrRow as $arrSingleRow)
            $this->assertEquals($arrSingleRow["temp_char10"], $intI++, "testDataBase getArraySection content");


        echo "\tdeleting table\n";

        $strQuery = "DROP TABLE "._dbprefix_."temp_autotest";
        $this->assertTrue($objDB->_query($strQuery), "testDataBase dropTable");

    }

}

