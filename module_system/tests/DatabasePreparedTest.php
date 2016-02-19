<?php

namespace Kajona\System\Tests;

require_once __DIR__ . "../../../core/module_system/system/Testbase.php";

use Kajona\System\System\Carrier;
use Kajona\System\System\Testbase;

class DatabasePreparedTest extends Testbase
{


    public function tearDown()
    {
        $this->flushDBCache();
        if (in_array(_dbprefix_ . "temp_autotest", Carrier::getInstance()->getObjDB()->getTables())) {
            $strQuery = "DROP TABLE " . _dbprefix_ . "temp_autotest";
            Carrier::getInstance()->getObjDB()->_pQuery($strQuery, array());
        }

        parent::tearDown();
    }

    public function test()
    {

        $objDB = Carrier::getInstance()->getObjDB();

        echo "testing database...\n";
        echo "current driver: " . Carrier::getInstance()->getObjConfig()->getConfig("dbdriver") . "\n";


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

        for ($intI = 1; $intI <= 50; $intI++) {
            $strQuery = "INSERT INTO " . _dbprefix_ . "temp_autotest
                (temp_id, temp_long, temp_double, temp_char10, temp_char20, temp_char100, temp_char254, temp_char500, temp_text)
                VALUES
                (?, ?, ?, ?, ?, ?, ?, ?, ?)";

            $this->assertTrue($objDB->_pQuery($strQuery, array(generateSystemid(), ("123456" . $intI), ("23.45" . $intI), $intI, "char20" . $intI, "char100" . $intI, "char254" . $intI, "char500" . $intI, "text" . $intI)), "testDataBase insert");
        }


        echo "\tgetRow test\n";
        $strQuery = "SELECT * FROM " . _dbprefix_ . "temp_autotest ORDER BY temp_long ASC";
        $arrRow = $objDB->getPRow($strQuery, array());
        $this->assertTrue(count($arrRow) >= 9, "testDataBase getRow count");
        $this->assertEquals($arrRow["temp_char10"], "1", "testDataBase getRow content");


        echo "\tgetRow test2\n";
        $strQuery = "SELECT * FROM " . _dbprefix_ . "temp_autotest WHERE temp_char10 = ? ORDER BY temp_long ASC";
        $arrRow = $objDB->getPRow($strQuery, array('2'));
        $this->assertTrue(count($arrRow) >= 9, "testDataBase getRow count");
        $this->assertEquals($arrRow["temp_char10"], "2", "testDataBase getRow content");

        echo "\tgetArray test\n";
        $strQuery = "SELECT * FROM " . _dbprefix_ . "temp_autotest ORDER BY temp_long ASC";
        $arrRow = $objDB->getPArray($strQuery, array());
        $this->assertEquals(count($arrRow), 50, "testDataBase getArray count");

        $intI = 1;
        foreach ($arrRow as $arrSingleRow)
            $this->assertEquals($arrSingleRow["temp_char10"], $intI++, "testDataBase getArray content");

        echo "\tgetArray test2\n";
        $strQuery = "SELECT * FROM " . _dbprefix_ . "temp_autotest  WHERE temp_char10 = ? ORDER BY temp_long ASC";
        $arrRow = $objDB->getPArray($strQuery, array('2'));
        $this->assertEquals(count($arrRow), 1, "testDataBase getArray count");

        echo "\tgetArraySection test\n";
        $strQuery = "SELECT * FROM " . _dbprefix_ . "temp_autotest ORDER BY temp_long ASC";
        $arrRow = $objDB->getPArray($strQuery, array(), 0, 9);
        $this->assertEquals(count($arrRow), 10, "testDataBase getArraySection count");

        $intI = 1;
        foreach ($arrRow as $arrSingleRow)
            $this->assertEquals($arrSingleRow["temp_char10"], $intI++, "testDataBase getArraySection content");

        $this->flushDBCache();
        echo "\tgetArraySection param test\n";
        $strQuery = "SELECT * FROM " . _dbprefix_ . "temp_autotest WHERE temp_char10 LIKE ? ORDER BY temp_long ASC";
        $arrRow = $objDB->getPArray($strQuery, array("%"), 0, 9);
        $this->assertEquals(count($arrRow), 10, "testDataBase getArraySection param count");

        $intI = 1;
        foreach ($arrRow as $arrSingleRow)
            $this->assertEquals($arrSingleRow["temp_char10"], $intI++, "testDataBase getArraySection param content");


        echo "\tgetArray test 2 params\n";
        $strQuery = "SELECT * FROM " . _dbprefix_ . "temp_autotest  WHERE temp_char10 = ? AND temp_char20 = ? ORDER BY temp_long ASC";
        $arrRow = $objDB->getPArray($strQuery, array('2', 'char202'));
        $this->assertEquals(count($arrRow), 1, "testDataBase getArray 2 params count");


        echo "\tgetArray test null params\n";
        $strQuery = "SELECT * FROM " . _dbprefix_ . "temp_autotest  WHERE temp_char10 = ? AND temp_char20 = ? ORDER BY temp_long ASC";
        $arrRow = $objDB->getPArray($strQuery, array('2', null));
        $this->assertEquals(count($arrRow), 0, "testDataBase getArray 2 params count");

        echo "\tdeleting table\n";

        $strQuery = "DROP TABLE " . _dbprefix_ . "temp_autotest";
        $this->assertTrue($objDB->_pQuery($strQuery, array()), "testDataBase dropTable");

    }


    public function testFloatHandling()
    {

        $objDB = Carrier::getInstance()->getObjDB();

        echo "testing float handling...\n";

        echo "\tcreating a new table...\n";

        $arrFields = array();
        $arrFields["temp_id"] = array("char20", false);
        $arrFields["temp_long"] = array("long", true);
        $arrFields["temp_double"] = array("double", true);

        $this->assertTrue($objDB->createTable("temp_autotest", $arrFields, array("temp_id")), "testDataBase createTable");


        $strQuery = "INSERT INTO " . _dbprefix_ . "temp_autotest
            (temp_id, temp_long, temp_double) VALUES (?, ?, ?)";

        $this->assertTrue($objDB->_pQuery($strQuery, array("id1", 123456, 1.7)), "testTx insert");
        $this->assertTrue($objDB->_pQuery($strQuery, array("id2", "123456", "1.7")), "testTx insert");

        $arrRow = $objDB->getPRow("SELECT * FROM " . _dbprefix_ . "temp_autotest WHERE temp_id = ?", array("id1"));

        $this->assertEquals($arrRow["temp_long"], 123456);
        $this->assertEquals($arrRow["temp_double"], 1.7);

        $arrRow = $objDB->getPRow("SELECT * FROM " . _dbprefix_ . "temp_autotest WHERE temp_id = ?", array("id2"));

        $this->assertEquals($arrRow["temp_long"], 123456);
        $this->assertEquals($arrRow["temp_double"], 1.7);

        $strQuery = "DROP TABLE " . _dbprefix_ . "temp_autotest";
        $this->assertTrue($objDB->_pQuery($strQuery, array()), "testDataBase dropTable");

        echo "\tdeleting table\n";
    }
}

