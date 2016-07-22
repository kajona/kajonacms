<?php

namespace Kajona\System\Tests;

use Kajona\System\System\Carrier;

class DatabaseMultiInsertTest extends Testbase
{


    public function tearDown()
    {
        $this->flushDBCache();
        if (in_array(_dbprefix_ . "temp_upserttest", Carrier::getInstance()->getObjDB()->getTables())) {
            $strQuery = "DROP TABLE " . _dbprefix_ . "temp_upserttest";
            Carrier::getInstance()->getObjDB()->_pQuery($strQuery, array());
        }

        parent::tearDown();
    }

    public function testInsertSinglePrimaryColumn()
    {

        $objDB = Carrier::getInstance()->getObjDB();


        if (in_array(_dbprefix_ . "temp_upserttest", Carrier::getInstance()->getObjDB()->getTables())) {
            $strQuery = "DROP TABLE " . _dbprefix_ . "temp_upserttest";
            Carrier::getInstance()->getObjDB()->_pQuery($strQuery, array());
        }

        $arrFields = array();
        $arrFields["temp_id"] = array("char20", false);
        $arrFields["temp_int"] = array("int", true);
        $arrFields["temp_text"] = array("text", true);

        $this->assertTrue($objDB->createTable("temp_upserttest", $arrFields, array("temp_id")));
        $strTableName = _dbprefix_."temp_upserttest";

        $this->assertEquals(count($objDB->getPArray("SELECT * FROM {$strTableName}", array())), 0);

        $strId1 = generateSystemid();
        $objDB->insertOrUpdate("temp_upserttest", array("temp_id", "temp_int", "temp_text"), array($strId1, 1, "row 1"), "temp_id");

        $this->assertEquals(count($objDB->getPArray("SELECT * FROM {$strTableName}", array(), null, null, false)), 1);
        $arrRow = $objDB->getPRow("SELECT * FROM {$strTableName} WHERE temp_id = ?", array($strId1));
        $this->assertEquals($arrRow["temp_int"], 1); $this->assertEquals($arrRow["temp_text"], "row 1");

        $objDB->flushQueryCache();

        //first replace
        $objDB->insertOrUpdate("temp_upserttest", array("temp_id", "temp_int", "temp_text"), array($strId1, 2, "row 2"), "temp_id");
        $this->assertEquals(count($objDB->getPArray("SELECT * FROM {$strTableName}", array(), null, null, false)), 1);
        $arrRow = $objDB->getPRow("SELECT * FROM {$strTableName} WHERE temp_id = ?", array($strId1));
        $this->assertEquals($arrRow["temp_int"], 2); $this->assertEquals($arrRow["temp_text"], "row 2");


        $strId2 = generateSystemid();
        $objDB->insertOrUpdate("temp_upserttest", array("temp_id", "temp_int", "temp_text"), array($strId2, 3, "row 3"), "temp_id");

        $strId3 = generateSystemid();
        $objDB->insertOrUpdate("temp_upserttest", array("temp_id", "temp_int", "temp_text"), array($strId3, 4, "row 4"), "temp_id");


        $this->assertEquals(count($objDB->getPArray("SELECT * FROM {$strTableName}", array(), null, null, false)), 3);

        $objDB->insertOrUpdate("temp_upserttest", array("temp_id", "temp_int", "temp_text"), array($strId3, 5, "row 5"), "temp_id");

        $this->assertEquals(count($objDB->getPArray("SELECT * FROM {$strTableName}", array(), null, null, false)), 3);


        $arrRow = $objDB->getPRow("SELECT * FROM {$strTableName} WHERE temp_id = ?", array($strId1));
        $this->assertEquals($arrRow["temp_int"], 2); $this->assertEquals($arrRow["temp_text"], "row 2");

        $arrRow = $objDB->getPRow("SELECT * FROM {$strTableName} WHERE temp_id = ?", array($strId2));
        $this->assertEquals($arrRow["temp_int"], 3); $this->assertEquals($arrRow["temp_text"], "row 3");

        $arrRow = $objDB->getPRow("SELECT * FROM {$strTableName} WHERE temp_id = ?", array($strId3));
        $this->assertEquals($arrRow["temp_int"], 5); $this->assertEquals($arrRow["temp_text"], "row 5");

        $strQuery = "DROP TABLE " . _dbprefix_ . "temp_upserttest";
        $this->assertTrue($objDB->_pQuery($strQuery, array()));

    }



    public function testInsertMultiplePrimaryColumn() {

    }


}

