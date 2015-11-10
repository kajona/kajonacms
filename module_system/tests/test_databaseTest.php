<?php

require_once (__DIR__."/../../module_system/system/class_testbase.php");

class class_test_database extends class_testbase {

    public function tearDown() {
        echo "Dropping tables...\n";
        $this->flushDBCache();
        if(in_array(_dbprefix_."temp_autotest", class_carrier::getInstance()->getObjDB()->getTables())) {
            $strQuery = "DROP TABLE "._dbprefix_."temp_autotest";
            class_carrier::getInstance()->getObjDB()->_pQuery($strQuery, array());
        }

        if(in_array(_dbprefix_."temp_autotest_new", class_carrier::getInstance()->getObjDB()->getTables())) {
            $strQuery = "DROP TABLE "._dbprefix_."temp_autotest_new";
            class_carrier::getInstance()->getObjDB()->_pQuery($strQuery, array());
        }
        if(in_array(_dbprefix_."temp_autotest_temp", class_carrier::getInstance()->getObjDB()->getTables())) {
            $strQuery = "DROP TABLE "._dbprefix_."temp_autotest_temp";
            class_carrier::getInstance()->getObjDB()->_pQuery($strQuery, array());
        }

        parent::tearDown();
    }


    public function testRenameTable() {
        $objDb = class_carrier::getInstance()->getObjDB();
        $this->createTable();

        $this->assertTrue(in_array(_dbprefix_."temp_autotest", class_carrier::getInstance()->getObjDB()->getTables()));
        $this->assertTrue(!in_array(_dbprefix_."temp_autotest_new", class_carrier::getInstance()->getObjDB()->getTables()));

        $this->assertTrue($objDb->renameTable("temp_autotest", "temp_autotest_new"));
        $this->flushDBCache();

        $this->assertTrue(!in_array(_dbprefix_."temp_autotest", class_carrier::getInstance()->getObjDB()->getTables()));
        $this->assertTrue(in_array(_dbprefix_."temp_autotest_new", class_carrier::getInstance()->getObjDB()->getTables()));
    }

    public function testChangeColumn() {
        $objDb = class_carrier::getInstance()->getObjDB();
        $this->tearDown();
        $this->createTable();

        $strQuery = "INSERT INTO "._dbprefix_."temp_autotest (temp_id, temp_long) VALUES (?,?)";
        $objDb->_pQuery($strQuery, array("aaa", 111));
        $objDb->_pQuery($strQuery, array("bbb", 222));

        $arrColumnNames = array_map(function($arrValue) {
            return $arrValue["columnName"];
        }, $objDb->getColumnsOfTable(_dbprefix_."temp_autotest"));

        $this->assertTrue(in_array("temp_id", $arrColumnNames));
        $this->assertTrue(in_array("temp_long", $arrColumnNames));

        $this->assertTrue($objDb->changeColumn("temp_autotest", "temp_long", "temp_long_new", class_db_datatypes::STR_TYPE_INT));
        $this->flushDBCache();

        $arrColumnNames = array_map(function($arrValue) {
            return $arrValue["columnName"];
        }, $objDb->getColumnsOfTable(_dbprefix_."temp_autotest"));

        $this->assertTrue(in_array("temp_id", $arrColumnNames));
        $this->assertTrue(!in_array("temp_long", $arrColumnNames));
        $this->assertTrue(in_array("temp_long_new", $arrColumnNames));

        $arrRows = $objDb->getPArray("SELECT * FROM "._dbprefix_."temp_autotest ORDER BY temp_long_new ASC", array());

        $this->assertTrue(count($arrRows) == 2);
        $this->assertEquals($arrRows[0]["temp_id"], "aaa");
        $this->assertEquals($arrRows[0]["temp_long_new"], 111);
        $this->assertEquals($arrRows[1]["temp_id"], "bbb");
        $this->assertEquals($arrRows[1]["temp_long_new"], 222);

    }

    public function testAddColumn() {
        $objDb = class_carrier::getInstance()->getObjDB();
        $this->createTable();

        $arrColumnNames = array_map(function($arrValue) {
            return $arrValue["columnName"];
        }, $objDb->getColumnsOfTable(_dbprefix_."temp_autotest"));

        $this->assertTrue(!in_array("temp_new_col", $arrColumnNames));

        $this->assertTrue($objDb->addColumn("temp_autotest", "temp_new_col", class_db_datatypes::STR_TYPE_INT));
        $this->flushDBCache();

        $arrColumnNames = array_map(function($arrValue) {
            return $arrValue["columnName"];
        }, $objDb->getColumnsOfTable(_dbprefix_."temp_autotest"));

        $this->assertTrue(in_array("temp_new_col", $arrColumnNames));
    }


    public function testRemoveColumn() {
        $objDb = class_carrier::getInstance()->getObjDB();
        $this->createTable();

        $arrColumnNames = array_map(function($arrValue) {
            return $arrValue["columnName"];
        }, $objDb->getColumnsOfTable(_dbprefix_."temp_autotest"));

        $this->assertTrue(in_array("temp_long", $arrColumnNames));

        $strQuery = "INSERT INTO "._dbprefix_."temp_autotest (temp_id, temp_long) VALUES (?,?)";
        $objDb->_pQuery($strQuery, array("aaa", 111));
        $objDb->_pQuery($strQuery, array("bbb", 222));

        $this->assertTrue($objDb->removeColumn("temp_autotest", "temp_long"));
        $this->flushDBCache();

        $arrColumnNames = array_map(function($arrValue) {
            return $arrValue["columnName"];
        }, $objDb->getColumnsOfTable(_dbprefix_."temp_autotest"));

        $this->assertTrue(!in_array("temp_long", $arrColumnNames));

        $arrRows = $objDb->getPArray("SELECT * FROM "._dbprefix_."temp_autotest ORDER BY temp_id ASC", array());

        $this->assertTrue(count($arrRows) == 2);
        $this->assertEquals($arrRows[0]["temp_id"], "aaa");
        $this->assertEquals($arrRows[1]["temp_id"], "bbb");
    }


    private function createTable() {
        echo "testing database...\n";
        echo "current driver: ".class_carrier::getInstance()->getObjConfig()->getConfig("dbdriver")."\n";

        echo "\tcreating a new table...\n";
        $objDB = class_carrier::getInstance()->getObjDB();

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
        $this->flushDBCache();
    }


    public function testCreateTable() {

        $objDB = class_carrier::getInstance()->getObjDB();



        echo "\tcreating a new table...\n";

        $this->createTable();

        echo "\tcreating 50 records...\n";

        for($intI = 1; $intI <= 50; $intI++) {
            $strQuery = "INSERT INTO "._dbprefix_."temp_autotest
                (temp_id, temp_long, temp_double, temp_char10, temp_char20, temp_char100, temp_char254, temp_char500, temp_text)
                VALUES
                ('".generateSystemid()."', 123456".$intI.", 23.45".$intI.", '".$intI."', 'char20".$intI."', 'char100".$intI."', 'char254".$intI."', 'char500".$intI."', 'text".$intI."')";

            $this->assertTrue($objDB->_pQuery($strQuery, array()), "testDataBase insert");
        }


        echo "\tgetRow test\n";
        $strQuery = "SELECT * FROM "._dbprefix_."temp_autotest ORDER BY temp_long ASC";
        $arrRow = $objDB->getPRow($strQuery, array());
        $this->assertTrue(count($arrRow) >= 9, "testDataBase getRow count");
        $this->assertEquals($arrRow["temp_char10"], "1", "testDataBase getRow content");

        echo "\tgetArray test\n";
        $strQuery = "SELECT * FROM "._dbprefix_."temp_autotest ORDER BY temp_long ASC";
        $arrRow = $objDB->getPArray($strQuery, array());
        $this->assertEquals(count($arrRow), 50, "testDataBase getArray count");

        $intI = 1;
        foreach($arrRow as $arrSingleRow)
            $this->assertEquals($arrSingleRow["temp_char10"], $intI++, "testDataBase getArray content");

        echo "\tgetArraySection test\n";
        $strQuery = "SELECT * FROM "._dbprefix_."temp_autotest ORDER BY temp_long ASC";
        $arrRow = $objDB->getPArray($strQuery, array(), 0, 9);
        $this->assertEquals(count($arrRow), 10, "testDataBase getArraySection count");

        $intI = 1;
        foreach($arrRow as $arrSingleRow)
            $this->assertEquals($arrSingleRow["temp_char10"], $intI++, "testDataBase getArraySection content");


        echo "\tdeleting table\n";

        $strQuery = "DROP TABLE "._dbprefix_."temp_autotest";
        $this->assertTrue($objDB->_pQuery($strQuery, array()), "testDataBase dropTable");

    }

    public function testEscapeText()
    {
        $this->createTable();

        $objDB = class_carrier::getInstance()->getObjDB();

        $dbPrefix = _dbprefix_;
        $systemId = generateSystemid();

        $strQuery = <<<SQL
INSERT INTO {$dbPrefix}temp_autotest
    (temp_id, temp_long, temp_double, temp_char10, temp_char20, temp_char100, temp_char254, temp_char500, temp_text)
VALUES
    ('{$systemId}', 123456, 23.45, '', 'Foo\\Bar\\Baz', 'Foo\\Bar\\Baz', 'Foo\\Bar\\Baz', 'Foo\\Bar\\Baz', 'Foo\\Bar\\Baz')
SQL;

        $this->assertTrue($objDB->_pQuery($strQuery, array()), "testDataBase insert");

        $strQuery = "SELECT * FROM "._dbprefix_."temp_autotest WHERE temp_char20 LIKE ?";
        $arrRow = $objDB->getPRow($strQuery, array("Foo\\Bar%"));

        $this->assertEquals('Foo\\Bar\\Baz', $arrRow['temp_char20']);
    }

}

