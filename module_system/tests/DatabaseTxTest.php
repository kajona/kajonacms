<?php

namespace Kajona\System\Tests;
use Kajona\System\System\Carrier;
use Kajona\System\System\Testbase;

class DatabaseTxTest extends Testbase {


    public function test() {

        $objDB = Carrier::getInstance()->getObjDB();

        echo "testing database...\n";
        echo "current driver: ".Carrier::getInstance()->getObjConfig()->getConfig("dbdriver")."\n";

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

        $this->assertTrue($objDB->createTable("temp_autotest", $arrFields, array("temp_id")), "testTx createTable");

        echo "\ttesting non-tx mode..\n";


        echo "\tcreating 50 records...\n";

        $intI = 1;
        $strQuery = "INSERT INTO "._dbprefix_."temp_autotest
            (temp_id, temp_long, temp_double, temp_char10, temp_char20, temp_char100, temp_char254, temp_char500, temp_text)
            VALUES
            ('".generateSystemid()."', 123456".$intI.", 23.45".$intI.", '".$intI."', 'char20".$intI."', 'char100".$intI."', 'char254".$intI."', 'char500".$intI."', 'text".$intI."')";

        $this->assertTrue($objDB->_query($strQuery), "testTx insert");

        echo "\tgetRow test\n";
        $strQuery = "SELECT * FROM "._dbprefix_."temp_autotest ORDER BY temp_long ASC";
        $arrRow = $objDB->getPArray($strQuery, array());
        $this->assertEquals(count($arrRow), 1, "testDataBase getRow count");
        $this->assertEquals($arrRow[0]["temp_char10"], "1", "testTx getRow content");

        $objDB->flushQueryCache();
        echo "starting tx...\n";
        $objDB->transactionBegin();

        $intI = 2;
        $strQuery = "INSERT INTO "._dbprefix_."temp_autotest
            (temp_id, temp_long, temp_double, temp_char10, temp_char20, temp_char100, temp_char254, temp_char500, temp_text)
            VALUES
            ('".generateSystemid()."', 123456".$intI.", 23.45".$intI.", '".$intI."', 'char20".$intI."', 'char100".$intI."', 'char254".$intI."', 'char500".$intI."', 'text".$intI."')";

        $this->assertTrue($objDB->_query($strQuery), "testTx insert");

        echo "rollback...\n";
        $objDB->transactionRollback();
        $arrCount = $objDB->getPRow("SELECT COUNT(*) FROM "._dbprefix_."temp_autotest", array());
        $this->assertEquals($arrCount["COUNT(*)"], 1, "testTx rollback");

        $objDB->flushQueryCache();

        echo "starting tx...\n";
        $objDB->transactionBegin();
        $this->assertTrue($objDB->_query($strQuery), "testTx insert");
        echo "commit...\n";
        $objDB->transactionCommit();

        $arrCount = $objDB->getPRow("SELECT COUNT(*) FROM "._dbprefix_."temp_autotest", array());
        $this->assertEquals($arrCount["COUNT(*)"], 2, "testTx rollback");

        $objDB->flushQueryCache();


        echo "\tdeleting table\n";

        $strQuery = "DROP TABLE "._dbprefix_."temp_autotest";
        $this->assertTrue($objDB->_query($strQuery), "testTx dropTable");

    }

}

