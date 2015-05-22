<?php

require_once (__DIR__."/../../module_system/system/class_testbase.php");

class class_test_orm_schemamanagerTest extends class_testbase {


    protected function tearDown() {
        $objDb = class_carrier::getInstance()->getObjDB();
        $objDb->_pQuery("DROP TABLE "._dbprefix_."ormtest", array());
        class_carrier::getInstance()->flushCache(class_carrier::INT_CACHE_TYPE_DBTABLES);
        parent::tearDown(); //
    }


    public function testSchemamanager() {
        $objDb = class_carrier::getInstance()->getObjDB();

        $objManager = new class_orm_schemamanager();

        $arrTables = $objDb->getTables();
        $this->assertTrue(!in_array(_dbprefix_."ormtest", $arrTables));

        $objManager->createTable("orm_schematest_testclass");
        class_carrier::getInstance()->flushCache(class_carrier::INT_CACHE_TYPE_DBTABLES);

        $arrTables = $objDb->getTables();
        $this->assertTrue(in_array(_dbprefix_."ormtest", $arrTables));

        //fetch table informations
        $arrTable = $objDb->getColumnsOfTable(_dbprefix_."ormtest");

        $arrColumnNamesToDatatype = array();
        array_walk($arrTable, function($arrValue) use (&$arrColumnNamesToDatatype) {
            $arrColumnNamesToDatatype[$arrValue["columnName"]] = $arrValue["columnType"];
        });

        $arrColumnNames = array_map(function($arrValue) {
            return $arrValue["columnName"];
        }, $arrTable);


        $this->assertTrue(in_array("content_id", $arrColumnNames));
        $this->assertTrue(in_array("col1", $arrColumnNames));
        $this->assertTrue(in_array("col2", $arrColumnNames));
        $this->assertTrue(in_array("col3", $arrColumnNames));

    }

    public function testTargetTableException1() {
        $objManager = new class_orm_schemamanager();

        $objEx = null;
        try {
            $objManager->createTable("orm_schematest_testclass_targettable1");
        }
        catch(class_orm_exception $objException) {
            $objEx = $objException;
        }

        $this->assertNotNull($objEx);
        $this->assertTrue(uniStrpos($objEx->getMessage(), "provides no target-table!") !== false);
    }

    public function testTargetTableException2() {
        $objManager = new class_orm_schemamanager();

        $objEx = null;
        try {
            $objManager->createTable("orm_schematest_testclass_targettable2");
        }
        catch(class_orm_exception $objException) {
            $objEx = $objException;
        }

        $this->assertNotNull($objEx);
        $this->assertTrue(uniStrpos($objEx->getMessage(), "is not in table.primaryColumn format") !== false);
    }

    public function testDataTypeException() {
        $objManager = new class_orm_schemamanager();

        $objEx = null;
        try {
            $objManager->createTable("orm_schematest_testclass_datatype");
        }
        catch(class_orm_exception $objException) {
            $objEx = $objException;
        }

        $this->assertNotNull($objEx);
        $this->assertTrue(uniStrpos($objEx->getMessage(), " is unknown (") !== false);
    }

    public function testTableColumnSyntaxException() {
        $objManager = new class_orm_schemamanager();

        $objEx = null;
        try {
            $objManager->createTable("orm_schematest_testclass_tablecolumn");
        }
        catch(class_orm_exception $objException) {
            $objEx = $objException;
        }

        $this->assertNotNull($objEx);
        $this->assertTrue(uniStrpos($objEx->getMessage(), "Syntax for tableColumn annotation at property") !== false);
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
class orm_schematest_testclass_datatype {

    /**
     * @var int
     * @tableColumn ormtest.col3
     * @tableColumnDatatype extralong
     */
    private $longCol3 = 0;
}

/**
 * Class orm_schematest_testclass
 *
 * @targetTable ormtest.content_id
 * @targetTable ormtest2.content_id
 */
class orm_schematest_testclass_tablecolumn {

    /**
     * @var int
     * @tableColumn ormtestcol3
     * @tableColumnDatatype long
     */
    private $longCol3 = 0;
}


/**
 * Class orm_schematest_testclass
 *
 */
class orm_schematest_testclass_targettable1 {


}

/**
 * Class orm_schematest_testclass
 * @targetTable ormtest
 */
class orm_schematest_testclass_targettable2 {


}

