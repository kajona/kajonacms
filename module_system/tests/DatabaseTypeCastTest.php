<?php

namespace Kajona\System\Tests;

use Kajona\System\System\Carrier;
use Kajona\System\System\DbDatatypes;

class DatabaseTypeCastTest extends Testbase
{


    public function testTypeCast()
    {
        $objDB = Carrier::getInstance()->getObjDB();
        $strTestTable = _dbprefix_."temp_typecasttest";

        if (in_array($strTestTable, Carrier::getInstance()->getObjDB()->getTables())) {
            Carrier::getInstance()->getObjDB()->_pQuery("DROP TABLE {$strTestTable}", array());
        }

        $arrFields = array();
        $arrFields["temp_id"] = array(DbDatatypes::STR_TYPE_CHAR20, false);
        $arrFields["temp_char254"] = array(DbDatatypes::STR_TYPE_CHAR254, true);
        $arrFields["temp_int"] = array(DbDatatypes::STR_TYPE_INT, true);
        $arrFields["temp_long"] = array(DbDatatypes::STR_TYPE_LONG, true);
        $arrFields["temp_float"] = array(DbDatatypes::STR_TYPE_DOUBLE, true);
        $arrFields["temp_text"] = array(DbDatatypes::STR_TYPE_TEXT, true);
        $arrFields["temp_longtext"] = array(DbDatatypes::STR_TYPE_LONGTEXT, true);

        $objDB->createTable("temp_typecasttest", $arrFields, array("temp_id"));

        $strId = generateSystemid();
        $this->assertTrue($objDB->_pQuery("INSERT INTO {$strTestTable} (temp_id, temp_char254, temp_int, temp_long, temp_float, temp_text, temp_longtext) VALUES (?, ?, ?, ?, ?, ?, ?)", array($strId, "char254", 12345, 20161221144714, 1234.56, "text", "longtext")));
        $arrRow = $objDB->getPRow("SELECT * FROM {$strTestTable} WHERE temp_id = ?", array($strId));

        var_dump($arrRow);

        $this->assertSame($arrRow["temp_id"], $strId);
        $this->assertTrue(is_string($arrRow["temp_id"]));

        $this->assertSame($arrRow["temp_char254"], "char254");
        $this->assertTrue(is_string($arrRow["temp_char254"]));

        $this->assertSame($arrRow["temp_int"], 12345);
        $this->assertTrue(is_int($arrRow["temp_int"]));

        $this->assertSame($arrRow["temp_long"], 20161221144714);
        $this->assertTrue(is_int($arrRow["temp_long"]));

        $this->assertSame($arrRow["temp_float"], 1234.56);
        $this->assertTrue(is_float($arrRow["temp_float"]));

        $this->assertSame($arrRow["temp_text"], "text");
        $this->assertTrue(is_string($arrRow["temp_text"]));

        $this->assertSame($arrRow["temp_longtext"], "longtext");
        $this->assertTrue(is_string($arrRow["temp_longtext"]));


        $strId = generateSystemid();
        $this->assertTrue($objDB->_pQuery("INSERT INTO {$strTestTable} (temp_id, temp_char254, temp_int, temp_long, temp_float, temp_text, temp_longtext) VALUES (?, ?, ?, ?, ?, ?, ?)", array($strId, null, null, null, null, null, null)));
        $arrRow = $objDB->getPRow("SELECT * FROM {$strTestTable} WHERE temp_id = ?", array($strId));

        $this->assertTrue($arrRow["temp_id"] === $strId);
        $this->assertTrue(is_string($arrRow["temp_id"]));

        $this->assertTrue($arrRow["temp_char254"] === null);
        $this->assertTrue(is_null($arrRow["temp_char254"]));

        $this->assertTrue($arrRow["temp_int"] === null);
        $this->assertTrue(is_null($arrRow["temp_int"]));

        $this->assertTrue($arrRow["temp_long"] === null);
        $this->assertTrue(is_null($arrRow["temp_long"]));

        $this->assertTrue($arrRow["temp_float"] === null);
        $this->assertTrue(is_null($arrRow["temp_float"]));

        $this->assertTrue($arrRow["temp_text"] === null);
        $this->assertTrue(is_null($arrRow["temp_text"]));

        $this->assertTrue($arrRow["temp_longtext"] === null);
        $this->assertTrue(is_null($arrRow["temp_longtext"]));



        Carrier::getInstance()->getObjDB()->_pQuery("DROP TABLE {$strTestTable}", array());

    }

}

