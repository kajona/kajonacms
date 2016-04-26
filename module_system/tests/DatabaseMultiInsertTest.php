<?php

namespace Kajona\System\Tests;

use Kajona\System\System\Carrier;

class DatabaseMultiInsertTest extends Testbase
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

    public function testInserts()
    {

        $objDB = Carrier::getInstance()->getObjDB();

        echo "testing database...\n";
        echo "current driver: " . Carrier::getInstance()->getObjConfig()->getConfig("dbdriver") . "\n";


        echo "\tcreating a new table...\n";

        $arrFields = array();
        $arrFields["temp_id"] = array("char20", false);
        $arrFields["temp_char100"] = array("char100", true);
        $arrFields["temp_char254"] = array("char254", true);

        $this->assertTrue($objDB->createTable("temp_autotest", $arrFields, array("temp_id")), "testDataBase createTable");

        echo "\tcreating 50 records...\n";

        $arrValues = array();
        for ($intI = 1; $intI <= 50; $intI++) {
            $arrValues[] = array(generateSystemid(), "text long " . $intI, "text " . $intI);
        }

        $this->assertTrue($objDB->multiInsert("temp_autotest", array("temp_id", "temp_char254", "temp_char100"), $arrValues));

        $arrRow = $objDB->getPRow("SELECT COUNT(*) FROM " . _dbprefix_ . "temp_autotest", array());
        $this->assertEquals($arrRow["COUNT(*)"], 50);

        for ($intI = 1; $intI <= 50; $intI++) {
            $arrRow = $objDB->getPRow("SELECT COUNT(*) FROM " . _dbprefix_ . "temp_autotest WHERE temp_char100 = ?", array("text " . $intI));
            $this->assertEquals($arrRow["COUNT(*)"], 1);

            $arrRow = $objDB->getPRow("SELECT * FROM " . _dbprefix_ . "temp_autotest WHERE temp_char100 = ?", array("text " . $intI));
            $this->assertEquals($arrRow["temp_char254"], "text long " . $intI);
        }


        $strQuery = "DELETE FROM " . _dbprefix_ . "temp_autotest";
        $this->assertTrue($objDB->_pQuery($strQuery, array()), "testDataBase truncateTable");
        $objDB->flushQueryCache();

        $strQuery = "SELECT COUNT(*) FROM " . _dbprefix_ . "temp_autotest";
        $this->assertEquals(0, $objDB->getPRow($strQuery, array())["COUNT(*)"], "testDataBase countLimitReach");

        $objDB->flushQueryCache();
        echo "\tcreating 1200 records...\n";

        $arrValues = array();
        for ($intI = 1; $intI <= 1200; $intI++) {
            $arrValues[] = array(generateSystemid(), "text long " . $intI, "text " . $intI);
        }
        $this->assertTrue($objDB->multiInsert("temp_autotest", array("temp_id", "temp_char254", "temp_char100"), $arrValues));
        $strQuery = "SELECT COUNT(*) FROM " . _dbprefix_ . "temp_autotest";
        $this->assertEquals(1200, $objDB->getPRow($strQuery, array())["COUNT(*)"], "testDataBase countLimitReach");


        $strQuery = "DROP TABLE " . _dbprefix_ . "temp_autotest";
        $this->assertTrue($objDB->_pQuery($strQuery, array()), "testDataBase dropTable");

    }


}

