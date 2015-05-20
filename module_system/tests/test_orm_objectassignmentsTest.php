<?php

require_once (__DIR__."/../../module_system/system/class_testbase.php");

/**
 * Class class_test_orm_schemamanagerTest
 *
 * @todo event handler validation
 */
class class_test_orm_schemamanagerTest extends class_testbase_object {
    /**
     * Returns an path to an xml fixture file which can be used to create and delete database structures
     *
     * @return string
     */
    protected function getFixtureFile() {
        return __DIR__."/objectassignmentsTest_fixture.xml";
    }

        /**
     * Returns the default root id for an given class name
     *
     * @param string $strClassName
     *
     * @return string
     */
    protected function getDefaultRootId($strClassName) {
        return class_module_system_module::getModuleByName("system")->getSystemid();
    }

    /**
     * Assigns an reference to an object
     *
     * @param class_model $objSource
     * @param class_model $objReference
     */
    protected function assignReferenceToObject(class_model $objSource, class_model $objReference) {

    }

    protected function setUp() {
        $objSchema = new class_orm_schemamanager();
        $objSchema->createTable("orm_objectlist_testclass");
        parent::setUp();
    }

    protected function tearDown() {
        $objDb = class_carrier::getInstance()->getObjDB();
        $objDb->_pQuery("DROP TABLE "._dbprefix_."testclass", array());
        $objDb->_pQuery("DROP TABLE "._dbprefix_."testclass_rel", array());
        class_carrier::getInstance()->flushCache(class_carrier::INT_CACHE_TYPE_DBTABLES);
        parent::tearDown();
    }


    public function testObjectassignmentsSaving() {

        $objDB = class_carrier::getInstance()->getObjDB();

        /** @var orm_objectlist_testclass $objTestobject */
        $objTestobject = $this->getObject("testobject");
        $arrAspects = array($this->getObject("aspect1"), $this->getObject("aspect2"));


        $objTestobject->setArrObject1($arrAspects);
        $objTestobject->updateObjectToDb();

        $arrRow = $objDB->getPRow("SELECT COUNT(*) FROM "._dbprefix_."testclass_rel WHERE testclass_source_id = ?", array($objTestobject->getSystemid()));
        $this->assertEquals(2, $arrRow["COUNT(*)"]);

        $objDB->flushQueryCache();

        //change the assignments
        $objTestobject = $this->getObject("testobject");
        $arrAspects = array($this->getObject("aspect2"));
        $objTestobject->setArrObject1($arrAspects);
        $objTestobject->updateObjectToDb();

        $arrRow = $objDB->getPRow("SELECT COUNT(*) FROM "._dbprefix_."testclass_rel WHERE testclass_source_id = ?", array($objTestobject->getSystemid()));
        $this->assertEquals(1, $arrRow["COUNT(*)"]);

        $objDB->flushQueryCache();

        //change the assignments
        $objTestobject = $this->getObject("testobject");
        $objTestobject->setArrObject1(array());
        $objTestobject->updateObjectToDb();

        $arrRow = $objDB->getPRow("SELECT COUNT(*) FROM "._dbprefix_."testclass_rel WHERE testclass_source_id = ?", array($objTestobject->getSystemid()));
        $this->assertEquals(0, $arrRow["COUNT(*)"]);


        $objDB->flushQueryCache();

        //change the assignments
        $objTestobject = $this->getObject("testobject");
        $arrAspects = array($this->getObject("aspect2"), $this->getObject("aspect1")->getSystemid());
        $objTestobject->setArrObject1($arrAspects);
        $objTestobject->updateObjectToDb();

        $arrRow = $objDB->getPRow("SELECT COUNT(*) FROM "._dbprefix_."testclass_rel WHERE testclass_source_id = ?", array($objTestobject->getSystemid()));
        $this->assertEquals(2, $arrRow["COUNT(*)"]);

    }


    public function testObjectassignmentsLazyLoad() {
        /** @var orm_objectlist_testclass $objTestobject */
        $objTestobject = $this->getObject("testobject");
        $arrAspects = array($this->getObject("aspect1"), $this->getObject("aspect2"));

        $objTestobject->setArrObject1($arrAspects);
        $objTestobject->updateObjectToDb();

        //reinit
        $objNewInstance = new orm_objectlist_testclass($objTestobject->getSystemid());

        $this->assertTrue($objNewInstance->getArrObject1() instanceof class_orm_assignment_array);
        $this->assertTrue(!$objNewInstance->getArrObject1()->getBitInitialized());

        $this->assertEquals(2, count($objNewInstance->getArrObject1()));
        $this->assertTrue($objNewInstance->getArrObject1()->getBitInitialized());

        foreach($objNewInstance->getArrObject1() as $objOneObject) {
            $this->assertTrue(in_array($objOneObject->getSystemid(), array($this->getObject("aspect1")->getSystemid(), $this->getObject("aspect2")->getSystemid())));
        }

        $this->assertTrue($objNewInstance->getArrObject1()->getBitInitialized());
    }
}

/**
 * Class orm_schematest_testclass
 *
 * @targetTable testclass.testclass_id
 */
class orm_objectlist_testclass extends class_model implements interface_model {

    /**
     * @var array
     * @objectList testclass_rel (source="testclass_source_id", target="testclass_target_id")
     */
    private $arrObject1 = array();

    /**
     * @var string
     * @tableColumn testclass.name
     * @tableColumnDatatype char254
     */
    private $strName = "";

    /**
     * Returns the name to be used when rendering the current object, e.g. in admin-lists.
     *
     * @return string
     */
    public function getStrDisplayName() {
        return $this->getStrName();
    }


    /**
     * @return string
     */
    public function getStrName() {
        return $this->strName;
    }

    /**
     * @param string $strName
     */
    public function setStrName($strName) {
        $this->strName = $strName;
    }


    /**
     * @return array
     */
    public function getArrObject1() {
        return $this->arrObject1;
    }

    /**
     * @param array $arrObject1
     */
    public function setArrObject1($arrObject1) {
        $this->arrObject1 = $arrObject1;
    }




}


