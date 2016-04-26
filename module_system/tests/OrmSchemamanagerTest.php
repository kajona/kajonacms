<?php

namespace Kajona\System\Tests;

use Kajona\System\System\Carrier;
use Kajona\System\System\OrmException;
use Kajona\System\System\OrmSchemamanager;

class OrmSchemamanagerTest extends Testbase
{


    protected function tearDown()
    {
        $objDb = Carrier::getInstance()->getObjDB();

        foreach (array("ormtest", "testclass", "testclass_rel", "testclass2_rel") as $strOneTable) {
            if (in_array(_dbprefix_ . $strOneTable, $objDb->getTables())) {
                $objDb->_pQuery("DROP TABLE " . _dbprefix_ . $strOneTable, array());
                Carrier::getInstance()->flushCache(Carrier::INT_CACHE_TYPE_DBTABLES);
            }
        }

        parent::tearDown();
    }


    public function testSchemamanager()
    {
        $objDb = Carrier::getInstance()->getObjDB();

        $objManager = new OrmSchemamanager();

        $arrTables = $objDb->getTables();
        $this->assertTrue(!in_array(_dbprefix_ . "ormtest", $arrTables));

        $objManager->createTable("Kajona\\System\\Tests\\OrmSchematestTestclass");
        Carrier::getInstance()->flushCache(Carrier::INT_CACHE_TYPE_DBTABLES);

        $arrTables = $objDb->getTables();
        $this->assertTrue(in_array(_dbprefix_ . "ormtest", $arrTables));

        //fetch table informations
        $arrTable = $objDb->getColumnsOfTable(_dbprefix_ . "ormtest");

        $arrColumnNamesToDatatype = array();
        array_walk($arrTable, function ($arrValue) use (&$arrColumnNamesToDatatype) {
            $arrColumnNamesToDatatype[$arrValue["columnName"]] = $arrValue["columnType"];
        });

        $arrColumnNames = array_map(function ($arrValue) {
            return $arrValue["columnName"];
        }, $arrTable);


        $this->assertTrue(in_array("content_id", $arrColumnNames));
        $this->assertTrue(in_array("col1", $arrColumnNames));
        $this->assertTrue(in_array("col2", $arrColumnNames));
        $this->assertTrue(in_array("col3", $arrColumnNames));
    }

    public function testTargetTableException1()
    {
        $objManager = new OrmSchemamanager();

        $objEx = null;
        try {
            $objManager->createTable("Kajona\\System\\Tests\\OrmSchematestTestclassTargettable1");
        } catch (OrmException $objException) {
            $objEx = $objException;
        }

        $this->assertNotNull($objEx);
        $this->assertTrue(uniStrpos($objEx->getMessage(), "provides no target-table!") !== false);
    }

    public function testTargetTableException2()
    {
        $objManager = new OrmSchemamanager();

        $objEx = null;
        try {
            $objManager->createTable("Kajona\\System\\Tests\\OrmSchematestTestclassTargettable2");
        } catch (OrmException $objException) {
            $objEx = $objException;
        }

        $this->assertNotNull($objEx);
        $this->assertTrue(uniStrpos($objEx->getMessage(), "is not in table.primaryColumn format") !== false);
    }

    public function testDataTypeException()
    {
        $objManager = new OrmSchemamanager();

        $objEx = null;
        try {
            $objManager->createTable("Kajona\\System\\Tests\\OrmSchematestTestclassDatatype");
        } catch (OrmException $objException) {
            $objEx = $objException;
        }

        $this->assertNotNull($objEx);
        $this->assertTrue(uniStrpos($objEx->getMessage(), " is unknown (") !== false);
    }

    public function testTableColumnSyntaxException()
    {
        $objManager = new OrmSchemamanager();

        $objEx = null;
        try {
            $objManager->createTable("Kajona\\System\\Tests\\OrmSchematestTestclassTablecolumn");
        } catch (OrmException $objException) {
            $objEx = $objException;
        }

        $this->assertNotNull($objEx);
        $this->assertTrue(uniStrpos($objEx->getMessage(), "Syntax for tableColumn annotation at property") !== false);
    }


    public function testAssignmentTableCreation()
    {
        $objDb = Carrier::getInstance()->getObjDB();

        $objManager = new OrmSchemamanager();

        $arrTables = $objDb->getTables();
        $this->assertTrue(!in_array(_dbprefix_ . "testclass", $arrTables));
        $this->assertTrue(!in_array(_dbprefix_ . "testclass_rel", $arrTables));
        $this->assertTrue(!in_array(_dbprefix_ . "testclass2_rel", $arrTables));

        $objManager->createTable("Kajona\\System\\Tests\\OrmSchematestTestclassAssignments");
        Carrier::getInstance()->flushCache(Carrier::INT_CACHE_TYPE_DBTABLES);

        $arrTables = $objDb->getTables();
        $this->assertTrue(in_array(_dbprefix_ . "testclass", $arrTables));
        $this->assertTrue(in_array(_dbprefix_ . "testclass_rel", $arrTables));
        $this->assertTrue(in_array(_dbprefix_ . "testclass2_rel", $arrTables));

        //fetch table informations
        $arrTable = $objDb->getColumnsOfTable(_dbprefix_ . "testclass_rel");

        $arrColumnNames = array_map(function ($arrValue) {
            return $arrValue["columnName"];
        }, $arrTable);


        $this->assertTrue(in_array("testclass_source_id", $arrColumnNames));
        $this->assertTrue(in_array("testclass_target_id", $arrColumnNames));

        $arrTable = $objDb->getColumnsOfTable(_dbprefix_ . "testclass2_rel");

        $arrColumnNames = array_map(function ($arrValue) {
            return $arrValue["columnName"];
        }, $arrTable);


        $this->assertTrue(in_array("testclass_source_id", $arrColumnNames));
        $this->assertTrue(in_array("testclass_target_id", $arrColumnNames));

    }
}

/**
 *
 * @targetTable ormtest.content_id
 */
class OrmSchematestTestclass
{

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
 *
 * @targetTable ormtest.content_id
 */
class OrmSchematestTestclassDatatype
{

    /**
     * @var int
     * @tableColumn ormtest.col3
     * @tableColumnDatatype extralong
     */
    private $longCol3 = 0;
}

/**
 *
 * @targetTable ormtest.content_id
 * @targetTable ormtest2.content_id
 */
class OrmSchematestTestclassTablecolumn
{

    /**
     * @var int
     * @tableColumn ormtestcol3
     * @tableColumnDatatype long
     */
    private $longCol3 = 0;
}


/**
 *
 */
class OrmSchematestTestclassTargettable1
{


}

/**
 * @targetTable ormtest
 */
class OrmSchematestTestclassTargettable2
{


}

/**
 *
 * @targetTable testclass.testclass_id
 */
class OrmSchematestTestclassAssignments
{

    /**
     * @var array
     * @objectList testclass_rel (source="testclass_source_id", target="testclass_target_id")
     */
    private $arrObject1 = array();


    /**
     * @var array
     * @objectList testclass2_rel (source="testclass_source_id", target="testclass_target_id")
     */
    private $arrObject2 = array();

}