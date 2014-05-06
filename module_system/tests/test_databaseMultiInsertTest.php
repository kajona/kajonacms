<?php

require_once (__DIR__."/../../module_system/system/class_testbase.php");

class class_test_databaseMultiInsert extends class_testbase {


    public function tearDown() {
        $this->flushDBCache();
        if(in_array(_dbprefix_."temp_autotest", class_carrier::getInstance()->getObjDB()->getTables())) {
            $strQuery = "DROP TABLE "._dbprefix_."temp_autotest";
            class_carrier::getInstance()->getObjDB()->_pQuery($strQuery, array());
        }

        parent::tearDown();
    }

    public function testInserts() {

        $objDB = class_carrier::getInstance()->getObjDB();

        echo "testing database...\n";
        echo "current driver: ".class_carrier::getInstance()->getObjConfig()->getConfig("dbdriver")."\n";


        echo "\tcreating a new table...\n";

        $arrFields = array();
        $arrFields["temp_id"]       = array("char20", false);
        $arrFields["temp_char100"]  = array("char100", true);

        $this->assertTrue($objDB->createTable("temp_autotest", $arrFields, array("temp_id")), "testDataBase createTable");

        echo "\tcreating 50 records...\n";

        $arrValues = array();
        for($intI = 1; $intI <= 50; $intI++) {
            $arrValues[] = array(generateSystemid(), "text ".$intI);
        }

        $this->assertTrue($objDB->multiInsert("temp_autotest", array("temp_id", "temp_char100"), $arrValues));

        $arrRow = $objDB->getPRow("SELECT COUNT(*) FROM "._dbprefix_."temp_autotest", array());
        $this->assertEquals($arrRow["COUNT(*)"], 50);


        $strQuery = "DROP TABLE "._dbprefix_."temp_autotest";
        $this->assertTrue($objDB->_pQuery($strQuery, array()), "testDataBase dropTable");

    }




}

