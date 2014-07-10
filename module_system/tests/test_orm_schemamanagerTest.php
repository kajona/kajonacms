<?php

require_once (__DIR__."/../../module_system/system/class_testbase.php");

class class_test_orm_schemamanagerTest extends class_testbase {


    public function testSchemamanager() {
        $objDb = class_carrier::getInstance()->getObjDB();

        $objManager = new class_orm_schemamanager();

        $arrTables = $objDb->getTables();
        $this->assertTrue(!in_array(_dbprefix_."ormtest", $arrTables));

        $objManager->createTable("orm_schematest_testclass");
        $objDb->flushTablesCache();

        $arrTables = $objDb->getTables();
        $this->assertTrue(in_array(_dbprefix_."ormtest", $arrTables));

        //fetch table informations
        $arrTable = $objDb->getColumnsOfTable(_dbprefix_."ormtest");

        $arrColumnNames = array_map(function($arrValue) {
            return $arrValue["columnName"];
        }, $arrTable);


        $this->assertTrue(in_array("col1", $arrColumnNames));
        $this->assertTrue(in_array("col2", $arrColumnNames));
        $this->assertTrue(in_array("col3", $arrColumnNames));

        $objDb->_pQuery("DROP TABLE "._dbprefix_."ormtest", array());
    }

}

/**
 * Class orm_schematest_testclass
 *
 * @targetTable ormtest.content_id
 */
class orm_schematest_testclass {

    /**
     * @var string
     * @tableColumn ormtest.col1
     */
    private $strCol1 = "";

    /**
     * @var string
     * @tableColumn ormtest.col2
     * @tableColumnDatatype text
     */
    private $strCol2 = "";

    /**
     * @var int
     * @tableColumn ormtest.col3
     * @tableColumnDatatype long
     */
    private $longCol3 = 0;
}

/**
 * Class orm_schematest_testclass
 *
 * @targetTable ormtest.content_id
 */
class orm_schematest_testclass_error {

    /**
     * @var string
     * @tableColumn col1
     */
    private $strCol1 = "";

}