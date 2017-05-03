<?php

namespace Kajona\System\Tests;

use Kajona\System\System\Carrier;

class DatabaseUpsertTest extends Testbase
{


    public function tearDown()
    {
        $this->flushDBCache();
        foreach(array("temp_upserttest", "temp_upserttest2", "temp_upserttest3") as $strOneTable) {

            if (in_array(_dbprefix_.$strOneTable, Carrier::getInstance()->getObjDB()->getTables())) {
                $strQuery = "DROP TABLE "._dbprefix_.$strOneTable;
                Carrier::getInstance()->getObjDB()->_pQuery($strQuery, array());
            }

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
        $objDB->insertOrUpdate("temp_upserttest", array("temp_id", "temp_int", "temp_text"), array($strId1, 1, "row 1"), array("temp_id"));

        $this->assertEquals(count($objDB->getPArray("SELECT * FROM {$strTableName}", array(), null, null, false)), 1);
        $arrRow = $objDB->getPRow("SELECT * FROM {$strTableName} WHERE temp_id = ?", array($strId1));
        $this->assertEquals($arrRow["temp_int"], 1); $this->assertEquals($arrRow["temp_text"], "row 1");

        $objDB->flushQueryCache();

        //first replace
        $objDB->insertOrUpdate("temp_upserttest", array("temp_id", "temp_int", "temp_text"), array($strId1, 2, "row 2"), array("temp_id"));
        $this->assertEquals(count($objDB->getPArray("SELECT * FROM {$strTableName}", array(), null, null, false)), 1);
        $arrRow = $objDB->getPRow("SELECT * FROM {$strTableName} WHERE temp_id = ?", array($strId1));
        $this->assertEquals($arrRow["temp_int"], 2); $this->assertEquals($arrRow["temp_text"], "row 2");


        $strId2 = generateSystemid();
        $objDB->insertOrUpdate("temp_upserttest", array("temp_id", "temp_int", "temp_text"), array($strId2, 3, "row 3"), array("temp_id"));

        $strId3 = generateSystemid();
        $objDB->insertOrUpdate("temp_upserttest", array("temp_id", "temp_int", "temp_text"), array($strId3, 4, "row 4"), array("temp_id"));


        $this->assertEquals(count($objDB->getPArray("SELECT * FROM {$strTableName}", array(), null, null, false)), 3);

        $objDB->insertOrUpdate("temp_upserttest", array("temp_id", "temp_int", "temp_text"), array($strId3, 5, "row 5"), array("temp_id"));

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



    public function testInsertMultiplePrimaryColumn()
    {
        $objDB = Carrier::getInstance()->getObjDB();


        if (in_array(_dbprefix_ . "temp_upserttest2", Carrier::getInstance()->getObjDB()->getTables())) {
            $strQuery = "DROP TABLE " . _dbprefix_ . "temp_upserttest2";
            Carrier::getInstance()->getObjDB()->_pQuery($strQuery, array());
        }

        $arrFields = array();
        $arrFields["temp_id"] = array("char20", false);
        $arrFields["temp_id2"] = array("int", false);
        $arrFields["temp_int"] = array("int", true);
        $arrFields["temp_text"] = array("text", true);

        $this->assertTrue($objDB->createTable("temp_upserttest2", $arrFields, array("temp_id", "temp_id2")));
        $strTableName = _dbprefix_."temp_upserttest2";

        $this->assertEquals(count($objDB->getPArray("SELECT * FROM {$strTableName}", array())), 0);

        $strId = generateSystemid();

        $objDB->insertOrUpdate("temp_upserttest2", array("temp_id", "temp_id2", "temp_int", "temp_text"), array($strId, 1, 1, "row 1"), array("temp_id", "temp_id2"));

        $this->assertEquals(count($objDB->getPArray("SELECT * FROM {$strTableName}", array(), null, null, false)), 1);
        $arrRow = $objDB->getPRow("SELECT * FROM {$strTableName} WHERE temp_id = ? AND temp_id2 = ?", array($strId, 1));
        $this->assertEquals($arrRow["temp_int"], 1); $this->assertEquals($arrRow["temp_text"], "row 1");

        $objDB->flushQueryCache();

        //first replace
        $objDB->insertOrUpdate("temp_upserttest2", array("temp_id", "temp_id2", "temp_int", "temp_text"), array($strId, 1, 2, "row 2"), array("temp_id", "temp_id2"));
        $this->assertEquals(count($objDB->getPArray("SELECT * FROM {$strTableName}", array(), null, null, false)), 1);
        $arrRow = $objDB->getPRow("SELECT * FROM {$strTableName} WHERE temp_id = ? AND temp_id2 = ?", array($strId, 1));
        $this->assertEquals($arrRow["temp_int"], 2); $this->assertEquals($arrRow["temp_text"], "row 2");


        $objDB->insertOrUpdate("temp_upserttest2", array("temp_id", "temp_id2", "temp_int", "temp_text"), array($strId, 2, 3, "row 3"), array("temp_id", "temp_id2"));
        $objDB->insertOrUpdate("temp_upserttest2", array("temp_id", "temp_id2", "temp_int", "temp_text"), array($strId, 3, 4, "row 4"), array("temp_id", "temp_id2"));


        $this->assertEquals(count($objDB->getPArray("SELECT * FROM {$strTableName}", array(), null, null, false)), 3);

        $objDB->insertOrUpdate("temp_upserttest2", array("temp_id", "temp_id2", "temp_int", "temp_text"), array($strId, 3, 5, "row 5"), array("temp_id", "temp_id2"));

        $this->assertEquals(count($objDB->getPArray("SELECT * FROM {$strTableName}", array(), null, null, false)), 3);


        $arrRow = $objDB->getPRow("SELECT * FROM {$strTableName} WHERE temp_id = ? AND temp_id2 = ?", array($strId, 1));
        $this->assertEquals($arrRow["temp_int"], 2); $this->assertEquals($arrRow["temp_text"], "row 2");

        $arrRow = $objDB->getPRow("SELECT * FROM {$strTableName} WHERE temp_id = ? AND temp_id2 = ?", array($strId, 2));
        $this->assertEquals($arrRow["temp_int"], 3); $this->assertEquals($arrRow["temp_text"], "row 3");

        $arrRow = $objDB->getPRow("SELECT * FROM {$strTableName} WHERE temp_id = ? AND temp_id2 = ?", array($strId, 3));
        $this->assertEquals($arrRow["temp_int"], 5); $this->assertEquals($arrRow["temp_text"], "row 5");

        $strQuery = "DROP TABLE " . _dbprefix_ . "temp_upserttest2";
        $this->assertTrue($objDB->_pQuery($strQuery, array()));
    }


    public function testUpsertPerformance()
    {
        $objDB = Carrier::getInstance()->getObjDB();
        if (in_array(_dbprefix_ . "temp_upserttest3", Carrier::getInstance()->getObjDB()->getTables())) {
            $strQuery = "DROP TABLE " . _dbprefix_ . "temp_upserttest3";
            Carrier::getInstance()->getObjDB()->_pQuery($strQuery, array());
        }

        $arrFields = array();
        $arrFields["temp_id"] = array("char20", false);
        $arrFields["temp_id2"] = array("int", false);
        $arrFields["temp_int"] = array("int", true);
        $arrFields["temp_text"] = array("text", true);

        $this->assertTrue($objDB->createTable("temp_upserttest3", $arrFields, array("temp_id", "temp_id2")));


        $strId1 = generateSystemid();
        $strId2 = generateSystemid();
        $strId3 = generateSystemid();

        $arrTestData = array(
            array($strId1, 1, 1, "text 1"),
            array($strId1, 1, 1, "text 1"),
            array($strId1, 1, 1, "text 2"),
            array($strId1, 2, 1, "text 1"),
            array($strId1, 2, 3, "text 1"),
            array($strId2, 1, 1, "text 1"),
            array($strId2, 1, 1, "text 1"),
            array($strId2, 1, 3, "text 1"),
            array($strId1, 1, 3, "text 4"),
            array($strId1, 1, 3, "text 5"),
            array($strId3, 3, 3, "text 3"),
            array($strId3, 3, 3, "text 4"),
            array($strId3, 4, 3, "text 4"),
            array($strId3, 4, 3, "text 4"),
            array($strId3, 4, 5, "text 4"),
        );


        $intTime = -microtime(true);
        foreach($arrTestData as $arrOneRow) {
            $this->runInsertAndUpdate($arrOneRow[0], $arrOneRow[1], $arrOneRow[2], $arrOneRow[3]);
        }
        $intTime += microtime(true);
        //echo "runInsertAndUpdate: ".sprintf('%f', $intTime) ." sec".PHP_EOL;



        $intTime2 = -microtime(true);
        foreach($arrTestData as $arrOneRow) {
            $this->runUpsert($arrOneRow[0], $arrOneRow[1], $arrOneRow[2], $arrOneRow[3]);
        }
        $intTime2 += microtime(true);
        //echo "runUpsert:          ".sprintf('%f', $intTime2) ." sec".PHP_EOL;


        //Disbaled due to performance glitches on oracle
        //$this->assertTrue($intTime2 < $intTime, "compare upsert performance");

        $strQuery = "DROP TABLE " . _dbprefix_ . "temp_upserttest3";
        $this->assertTrue($objDB->_pQuery($strQuery, array()));
    }

    private function runUpsert($intId, $intId2, $intInt, $strText)
    {
        Carrier::getInstance()->getObjDB()->insertOrUpdate("temp_upserttest3", array("temp_id", "temp_id2", "temp_int", "temp_text"), array($intId, $intId2, $intInt, $strText), array("temp_id", "temp_id2"));
    }

    private function runInsertAndUpdate($intId, $intId2, $intInt, $strText)
    {
        $objDb = Carrier::getInstance()->getObjDB();
        $arrRow = $objDb->getPRow("SELECT COUNT(*) AS cnt FROM "._dbprefix_."temp_upserttest3 WHERE temp_id = ? AND temp_id2 = ?", array($intId, $intId2), 0, false);
        if($arrRow["cnt"] == "0") {
            $strQuery = "INSERT INTO "._dbprefix_."temp_upserttest3 (temp_id, temp_id2, temp_int, temp_text) VALUES (?, ?, ?, ?)";
            $objDb->_pQuery($strQuery, array($intId, $intId2, $intInt, $strText));
        }
        else {
            $strQuery = "UPDATE "._dbprefix_."temp_upserttest3 SET temp_int = ?, temp_text = ? WHERE temp_id = ? AND temp_id2 = ?";
            $objDb->_pQuery($strQuery, array($intInt, $strText, $intId, $intId2));
        }

    }

    // this approach is not feasible! in an update matches a row with the same data, at least mysql returns 0.
    // where not matching: 0 affected, where matching but update not required: 0 affected
    private function runInsertAndUpdateChangedRows($intId, $intId2, $intInt, $strText)
    {
        $objDb = Carrier::getInstance()->getObjDB();

        $strQuery = "UPDATE "._dbprefix_."temp_upserttest3 SET temp_int = ?, temp_text = ? WHERE temp_id = ? AND temp_id2 = ?";
        $objDb->_pQuery($strQuery, array($intInt, $strText, $intId, $intId2));
        if($objDb->getIntAffectedRows() == 0) {
            $strQuery = "INSERT INTO "._dbprefix_."temp_upserttest3 (temp_id, temp_id2, temp_int, temp_text) VALUES (?, ?, ?, ?)";
            $objDb->_pQuery($strQuery, array($intId, $intId2, $intInt, $strText));
        }
    }

}

