<?php

namespace Kajona\System\Tests;

use Kajona\System\System\Carrier;
use Kajona\System\System\DbDatatypes;

class DatabaseTest extends Testbase
{

    public function tearDown()
    {
        $this->flushDBCache();
        if (in_array(_dbprefix_ . "temp_autotest", Carrier::getInstance()->getObjDB()->getTables())) {
            $strQuery = "DROP TABLE " . _dbprefix_ . "temp_autotest";
            Carrier::getInstance()->getObjDB()->_pQuery($strQuery, array());
        }

        if (in_array(_dbprefix_ . "temp_autotest_new", Carrier::getInstance()->getObjDB()->getTables())) {
            $strQuery = "DROP TABLE " . _dbprefix_ . "temp_autotest_new";
            Carrier::getInstance()->getObjDB()->_pQuery($strQuery, array());
        }
        if (in_array(_dbprefix_ . "temp_autotest_temp", Carrier::getInstance()->getObjDB()->getTables())) {
            $strQuery = "DROP TABLE " . _dbprefix_ . "temp_autotest_temp";
            Carrier::getInstance()->getObjDB()->_pQuery($strQuery, array());
        }

        parent::tearDown();
    }


    public function testRenameTable()
    {
        $objDb = Carrier::getInstance()->getObjDB();
        $this->createTable();

        $this->assertTrue(in_array(_dbprefix_ . "temp_autotest", Carrier::getInstance()->getObjDB()->getTables()));
        $this->assertTrue(!in_array(_dbprefix_ . "temp_autotest_new", Carrier::getInstance()->getObjDB()->getTables()));

        $this->assertTrue($objDb->renameTable("temp_autotest", "temp_autotest_new"));
        $this->flushDBCache();

        $this->assertTrue(!in_array(_dbprefix_ . "temp_autotest", Carrier::getInstance()->getObjDB()->getTables()));
        $this->assertTrue(in_array(_dbprefix_ . "temp_autotest_new", Carrier::getInstance()->getObjDB()->getTables()));
    }

    public function testChangeColumn()
    {
        $objDb = Carrier::getInstance()->getObjDB();
        $this->tearDown();
        $this->createTable();

        $strQuery = "INSERT INTO " . _dbprefix_ . "temp_autotest (temp_id, temp_long) VALUES (?,?)";
        $objDb->_pQuery($strQuery, array("aaa", 111));
        $objDb->_pQuery($strQuery, array("bbb", 222));

        $arrColumnNames = array_map(function ($arrValue) {
            return $arrValue["columnName"];
        }, $objDb->getColumnsOfTable(_dbprefix_ . "temp_autotest"));

        $this->assertTrue(in_array("temp_id", $arrColumnNames));
        $this->assertTrue(in_array("temp_long", $arrColumnNames));

        $this->assertTrue($objDb->changeColumn("temp_autotest", "temp_long", "temp_long_new", DbDatatypes::STR_TYPE_INT));
        $this->flushDBCache();

        $arrColumnNames = array_map(function ($arrValue) {
            return $arrValue["columnName"];
        }, $objDb->getColumnsOfTable(_dbprefix_ . "temp_autotest"));

        $this->assertTrue(in_array("temp_id", $arrColumnNames));
        $this->assertTrue(!in_array("temp_long", $arrColumnNames));
        $this->assertTrue(in_array("temp_long_new", $arrColumnNames));

        $arrRows = $objDb->getPArray("SELECT * FROM " . _dbprefix_ . "temp_autotest ORDER BY temp_long_new ASC", array());

        $this->assertTrue(count($arrRows) == 2);
        $this->assertEquals($arrRows[0]["temp_id"], "aaa");
        $this->assertEquals($arrRows[0]["temp_long_new"], 111);
        $this->assertEquals($arrRows[1]["temp_id"], "bbb");
        $this->assertEquals($arrRows[1]["temp_long_new"], 222);

    }

    public function testAddColumn()
    {
        $objDb = Carrier::getInstance()->getObjDB();
        $this->createTable();

        $arrColumnNames = array_map(function ($arrValue) {
            return $arrValue["columnName"];
        }, $objDb->getColumnsOfTable(_dbprefix_ . "temp_autotest"));

        $this->assertTrue(!in_array("temp_new_col1", $arrColumnNames));
        $this->assertTrue(!in_array("temp_new_col2", $arrColumnNames));
        $this->assertTrue(!in_array("temp_new_col3", $arrColumnNames));
        $this->assertTrue(!in_array("temp_new_col4", $arrColumnNames));

        $this->assertTrue($objDb->addColumn("temp_autotest", "temp_new_col1", DbDatatypes::STR_TYPE_INT));
        $this->assertTrue($objDb->addColumn("temp_autotest", "temp_new_col2", DbDatatypes::STR_TYPE_INT, true, "NULL"));
        $this->assertTrue($objDb->addColumn("temp_autotest", "temp_new_col3", DbDatatypes::STR_TYPE_INT, false, "0"));
        $this->assertTrue($objDb->addColumn("temp_autotest", "temp_new_col4", DbDatatypes::STR_TYPE_INT, true));

        $this->flushDBCache();

        $arrColumnNames = array_map(function ($arrValue) {
            return $arrValue["columnName"];
        }, $objDb->getColumnsOfTable(_dbprefix_ . "temp_autotest"));

        $this->assertTrue(in_array("temp_new_col1", $arrColumnNames));
        $this->assertTrue(in_array("temp_new_col2", $arrColumnNames));
        $this->assertTrue(in_array("temp_new_col3", $arrColumnNames));
        $this->assertTrue(in_array("temp_new_col4", $arrColumnNames));
    }


    public function testRemoveColumn()
    {
        $objDb = Carrier::getInstance()->getObjDB();
        $this->createTable();

        $arrColumnNames = array_map(function ($arrValue) {
            return $arrValue["columnName"];
        }, $objDb->getColumnsOfTable(_dbprefix_ . "temp_autotest"));

        $this->assertTrue(in_array("temp_long", $arrColumnNames));

        $strQuery = "INSERT INTO " . _dbprefix_ . "temp_autotest (temp_id, temp_long) VALUES (?,?)";
        $objDb->_pQuery($strQuery, array("aaa", 111));
        $objDb->_pQuery($strQuery, array("bbb", 222));

        $this->assertTrue($objDb->removeColumn("temp_autotest", "temp_long"));
        $this->flushDBCache();

        $arrColumnNames = array_map(function ($arrValue) {
            return $arrValue["columnName"];
        }, $objDb->getColumnsOfTable(_dbprefix_ . "temp_autotest"));

        $this->assertTrue(!in_array("temp_long", $arrColumnNames));

        $arrRows = $objDb->getPArray("SELECT * FROM " . _dbprefix_ . "temp_autotest ORDER BY temp_id ASC", array());

        $this->assertTrue(count($arrRows) == 2);
        $this->assertEquals($arrRows[0]["temp_id"], "aaa");
        $this->assertEquals($arrRows[1]["temp_id"], "bbb");
    }


    private function createTable()
    {
        echo "current driver: " . Carrier::getInstance()->getObjConfig()->getConfig("dbdriver") . "\n";

        $objDB = Carrier::getInstance()->getObjDB();

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


    public function testCreateTable()
    {

        $objDB = Carrier::getInstance()->getObjDB();

        
        $this->createTable();
        
        for ($intI = 1; $intI <= 50; $intI++) {
            $strQuery = "INSERT INTO " . _dbprefix_ . "temp_autotest
                (temp_id, temp_long, temp_double, temp_char10, temp_char20, temp_char100, temp_char254, temp_char500, temp_text)
                VALUES
                ('" . generateSystemid() . "', 123456" . $intI . ", 23.45" . $intI . ", '" . $intI . "', 'char20" . $intI . "', 'char100" . $intI . "', 'char254" . $intI . "', 'char500" . $intI . "', 'text" . $intI . "')";

            $this->assertTrue($objDB->_pQuery($strQuery, array()), "testDataBase insert");
        }


        $strQuery = "SELECT * FROM " . _dbprefix_ . "temp_autotest ORDER BY temp_long ASC";
        $arrRow = $objDB->getPRow($strQuery, array());
        $this->assertTrue(count($arrRow) >= 9, "testDataBase getRow count");
        
        $this->assertEquals($arrRow["temp_long"], "1234561", "testDataBase getRow content");
        $this->assertEquals($arrRow["temp_double"], "23.451", "testDataBase getRow content");
        $this->assertEquals($arrRow["temp_char10"], "1", "testDataBase getRow content");
        $this->assertEquals($arrRow["temp_char20"], "char201", "testDataBase getRow content");
        $this->assertEquals($arrRow["temp_char100"], "char1001", "testDataBase getRow content");
        $this->assertEquals($arrRow["temp_char254"], "char2541", "testDataBase getRow content");
        $this->assertEquals($arrRow["temp_char500"], "char5001", "testDataBase getRow content");
        $this->assertEquals($arrRow["temp_text"], "text1", "testDataBase getRow content");

        $strQuery = "SELECT * FROM " . _dbprefix_ . "temp_autotest ORDER BY temp_long ASC";
        $arrRow = $objDB->getPArray($strQuery, array());
        $this->assertEquals(count($arrRow), 50, "testDataBase getArray count");

        $intI = 1;
        foreach ($arrRow as $arrSingleRow)
            $this->assertEquals($arrSingleRow["temp_char10"], $intI++, "testDataBase getArray content");

        $strQuery = "SELECT * FROM " . _dbprefix_ . "temp_autotest ORDER BY temp_long ASC";
        $arrRow = $objDB->getPArray($strQuery, array(), 0, 9);
        $this->assertEquals(count($arrRow), 10, "testDataBase getArraySection count");

        $intI = 1;
        foreach ($arrRow as $arrSingleRow)
            $this->assertEquals($arrSingleRow["temp_char10"], $intI++, "testDataBase getArraySection content");

        
        $strQuery = "DROP TABLE " . _dbprefix_ . "temp_autotest";
        $this->assertTrue($objDB->_pQuery($strQuery, array()), "testDataBase dropTable");

    }

    public function testCreateTableIndex()
    {
        //echo "current driver: " . Carrier::getInstance()->getObjConfig()->getConfig("dbdriver") . "\n";

        $objDB = Carrier::getInstance()->getObjDB();

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

        $this->assertTrue($objDB->createTable("temp_autotest", $arrFields, array("temp_id"), array(array("temp_id", "temp_char10", "temp_char100"), "temp_char254")), "testDataBase createTable");
        $this->flushDBCache();
    }

    public function testEscapeText()
    {
        $this->createTable();

        $objDB = Carrier::getInstance()->getObjDB();

        $dbPrefix = _dbprefix_;
        $systemId = generateSystemid();

        $strQuery = <<<SQL
INSERT INTO {$dbPrefix}temp_autotest
    (temp_id, temp_long, temp_double, temp_char10, temp_char20, temp_char100, temp_char254, temp_char500, temp_text)
VALUES
    ('{$systemId}', 123456, 23.45, '', ?, ?, ?, ?, ?)
SQL;

        $this->assertTrue($objDB->_pQuery($strQuery, array('Foo\\Bar\\Baz', 'Foo\\Bar\\Baz', 'Foo\\Bar\\Baz', 'Foo\\Bar\\Baz', 'Foo\\Bar\\Baz')), "testDataBase insert");

        // like must be escaped
        $strQuery = "SELECT * FROM " . _dbprefix_ . "temp_autotest WHERE temp_char20 LIKE ?";
        $arrRow = $objDB->getPRow($strQuery, array($objDB->escape("Foo\\Bar%")));

        $this->assertNotEmpty($arrRow);
        $this->assertEquals('Foo\\Bar\\Baz', $arrRow['temp_char20']);

        // equals needs no escape
        $strQuery = "SELECT * FROM " . _dbprefix_ . "temp_autotest WHERE temp_char20 = ?";
        $arrRow = $objDB->getPRow($strQuery, array("Foo\\Bar\\Baz"));

        $this->assertNotEmpty($arrRow);
        $this->assertEquals('Foo\\Bar\\Baz', $arrRow['temp_char20']);
    }

    public function testGetPArray()
    {
        $objDB = Carrier::getInstance()->getObjDB();

        $this->createTable();

        $arrData = array();
        for ($intI = 0; $intI < 50; $intI++) {
            $arrData[] = array(generateSystemid(), $intI, $intI, $intI, $intI, $intI, $intI, $intI, $intI);
        }

        $objDB->multiInsert("temp_autotest", array("temp_id", "temp_long", "temp_double", "temp_char10", "temp_char20", "temp_char100", "temp_char254", "temp_char500", "temp_text"), $arrData);

        $arrResult = $objDB->getPArray("SELECT * FROM " . _dbprefix_ . "temp_autotest ORDER BY temp_long ASC", array(), 0, 0);
        $this->assertEquals(1, count($arrResult));
        $this->assertEquals(0, $arrResult[0]["temp_long"]);

        $arrResult = $objDB->getPArray("SELECT * FROM " . _dbprefix_ . "temp_autotest ORDER BY temp_long ASC", array(), 0, 7);
        $this->assertEquals(8, count($arrResult));
        for ($intI = 0; $intI < 8; $intI++) {
            $this->assertEquals($intI, $arrResult[$intI]["temp_long"]);
        }

        $arrResult = $objDB->getPArray("SELECT * FROM " . _dbprefix_ . "temp_autotest ORDER BY temp_long ASC", array(), 4, 7);
        $this->assertEquals(4, count($arrResult));
        for ($intI = 4; $intI < 8; $intI++) {
            $this->assertEquals($intI, $arrResult[$intI - 4]["temp_long"]);
        }
    }

    public function testGetAffectedRows()
    {
        $objDB = Carrier::getInstance()->getObjDB();

        // create table
        $arrFields = array();
        $arrFields["temp_id"] = array("char20", false);
        $arrFields["temp_char20"] = array("char20", true);

        $this->assertTrue($objDB->createTable("temp_autotest_temp", $arrFields, array("temp_id")), "testDataBase createTable");
        $this->flushDBCache();

        $strSystemId = generateSystemid();

        // insert which affects onw row
        $objDB->multiInsert("temp_autotest_temp",
            array("temp_id", "temp_char20"),
            array(array(generateSystemid(), $strSystemId))
        );
        $this->assertEquals(1, $objDB->getAffectedRows());

        // insert which affects two rows
        $objDB->multiInsert("temp_autotest_temp",
            array("temp_id", "temp_char20"),
            array(
                array(generateSystemid(), $strSystemId),
                array(generateSystemid(), $strSystemId)
            )
        );
        $this->assertEquals(2, $objDB->getAffectedRows());

        $strNewSystemId = generateSystemid();

        // update which affects multiple rows
        $objDB->_pQuery("UPDATE " . _dbprefix_ . "temp_autotest_temp SET temp_char20 = ? WHERE temp_char20 = ?", array($strNewSystemId, $strSystemId));
        $this->assertEquals(3, $objDB->getAffectedRows());

        // update which does not affect a row
        $objDB->_pQuery("UPDATE " . _dbprefix_ . "temp_autotest_temp SET temp_char20 = ? WHERE temp_char20 = ?", array(generateSystemid(), generateSystemid()));
        $this->assertEquals(0, $objDB->getAffectedRows());

        // delete which affects two rows
        $objDB->_pQuery("DELETE FROM " . _dbprefix_ . "temp_autotest_temp WHERE temp_char20 = ?", array($strNewSystemId));
        $this->assertEquals(3, $objDB->getAffectedRows());

        // delete which affects no rows
        $objDB->_pQuery("DELETE FROM " . _dbprefix_ . "temp_autotest_temp WHERE temp_char20 = ?", array(generateSystemid()));
        $this->assertEquals(0, $objDB->getAffectedRows());
    }
}

