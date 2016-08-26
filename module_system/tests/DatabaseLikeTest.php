<?php

namespace Kajona\System\Tests;

use Kajona\System\System\Carrier;
use Kajona\System\System\Config;

class DatabaseLikeTest extends Testbase
{


    public static function tearDownAfterClass()
    {
        Carrier::getInstance()->getObjDB()->flushQueryCache();
        Carrier::getInstance()->getObjDB()->flushTablesCache();
        foreach(array("temp_liketest") as $strOneTable) {

            if (in_array(_dbprefix_.$strOneTable, Carrier::getInstance()->getObjDB()->getTables())) {
                $strQuery = "DROP TABLE "._dbprefix_.$strOneTable;
                Carrier::getInstance()->getObjDB()->_pQuery($strQuery, array());
            }

        }

        parent::tearDownAfterClass();
    }

    /**
     * @inheritDoc
     */
    public static function setUpBeforeClass()
    {
        $objDB = Carrier::getInstance()->getObjDB();


        if (in_array(_dbprefix_ . "temp_liketest", Carrier::getInstance()->getObjDB()->getTables())) {
            $strQuery = "DROP TABLE " . _dbprefix_ . "temp_liketest";
            Carrier::getInstance()->getObjDB()->_pQuery($strQuery, array());
        }

        $arrFields = array();
        $arrFields["temp_id"] = array("int", false);
        $arrFields["temp_name"] = array("char254", true);
        $arrFields["temp_text"] = array("text", true);

        $objDB->createTable("temp_liketest", $arrFields, array("temp_id"));

        //insert a few rows
        $objDB->multiInsert(
            "temp_liketest",
            array("temp_id", "temp_name", "temp_text"),
            array(
                array(1, "lower", "abc die katze"),
                array(2, "capitalized", "Abc Die Katze"),
                array(3, "upper", "ABC DIE KATZE"),
                array(4, "key", "lower"),
                array(5, "Key", "capitalized"),
                array(6, "KEY", "upper"),
            )
        );

        parent::setUpBeforeClass();
    }


    public function testEquals()
    {
        $this->markTestSkipped('currently problems on mysql');

        $strTableName = _dbprefix_."temp_liketest";
        $objDB = Carrier::getInstance()->getObjDB();
        $this->assertEquals(count($objDB->getPArray("SELECT * FROM {$strTableName}", array())), 6);

        $arrRows = $objDB->getPArray("SELECT * FROM {$strTableName} WHERE temp_text = ?", array('abc die katze'));
        $this->assertEquals(count($arrRows), 1);
        $this->assertEquals($arrRows[0]["temp_name"], "lower");

        $arrRows = $objDB->getPArray("SELECT * FROM {$strTableName} WHERE temp_text = ?", array('Abc Die Katze'));
        $this->assertEquals(count($arrRows), 1);
        $this->assertEquals($arrRows[0]["temp_name"], "capitalized");

        $arrRows = $objDB->getPArray("SELECT * FROM {$strTableName} WHERE temp_text = ?", array('ABC DIE KATZE'));
        $this->assertEquals(count($arrRows), 1);
        $this->assertEquals($arrRows[0]["temp_name"], "upper");

        $arrRows = $objDB->getPArray("SELECT * FROM {$strTableName} WHERE temp_text = ?", array('aBC DIE katze'));
        $this->assertEquals(count($arrRows), 0);


        $arrRows = $objDB->getPArray("SELECT * FROM {$strTableName} WHERE temp_name = ?", array('key'));
        $this->assertEquals(count($arrRows), 1);
        $this->assertEquals($arrRows[0]["temp_text"], "lower");

        $arrRows = $objDB->getPArray("SELECT * FROM {$strTableName} WHERE temp_name = ?", array('Key'));
        $this->assertEquals(count($arrRows), 1);
        $this->assertEquals($arrRows[0]["temp_text"], "capitalized");

        $arrRows = $objDB->getPArray("SELECT * FROM {$strTableName} WHERE temp_name = ?", array('KEY'));
        $this->assertEquals(count($arrRows), 1);
        $this->assertEquals($arrRows[0]["temp_text"], "upper");

        $arrRows = $objDB->getPArray("SELECT * FROM {$strTableName} WHERE temp_name = ?", array('kEy'));
        $this->assertEquals(count($arrRows), 0);

    }


    public function testLike()
    {
        $objDB = Carrier::getInstance()->getObjDB();
        $strTableName = _dbprefix_."temp_liketest";
        $this->assertEquals(count($objDB->getPArray("SELECT * FROM {$strTableName}", array())), 6);

        $arrRows = $objDB->getPArray("SELECT * FROM {$strTableName} WHERE temp_text LIKE ? ORDER BY temp_id ASC", array('abc%'));
        $this->assertEquals(count($arrRows), 3);
        $this->assertEquals($arrRows[0]["temp_name"], "lower");
        $this->assertEquals($arrRows[1]["temp_name"], "capitalized");
        $this->assertEquals($arrRows[2]["temp_name"], "upper");

        $arrRows = $objDB->getPArray("SELECT * FROM {$strTableName} WHERE temp_name LIKE ? ORDER BY temp_id ASC", array('key%'));
        $this->assertEquals(count($arrRows), 3);
        $this->assertEquals($arrRows[0]["temp_text"], "lower");
        $this->assertEquals($arrRows[1]["temp_text"], "capitalized");
        $this->assertEquals($arrRows[2]["temp_text"], "upper");



    }



}

