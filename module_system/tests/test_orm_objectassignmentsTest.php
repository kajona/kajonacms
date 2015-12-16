<?php

require_once (__DIR__."/../../module_system/system/class_testbase.php");

/**
 * Class class_test_orm_schemamanagerTest
 *
 */
class class_test_orm_objectassignmentsTest extends class_testbase_object {
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
        if($strClassName == "class_module_languages_language")
            return class_module_system_module::getModuleByName("languages")->getSystemid();

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
        parent::tearDown();
        $objDb = class_carrier::getInstance()->getObjDB();
        $objDb->_pQuery("DROP TABLE "._dbprefix_."testclass", array());
        $objDb->_pQuery("DROP TABLE "._dbprefix_."testclass_rel", array());
        $objDb->_pQuery("DROP TABLE "._dbprefix_."testclass2_rel", array());
        class_carrier::getInstance()->flushCache(class_carrier::INT_CACHE_TYPE_DBTABLES);
    }


    public function testLogicalDeleteUpdateHandlingExcluded() {
        $objDB = class_carrier::getInstance()->getObjDB();

        /** @var orm_objectlist_testclass $objTestobject */
        $objTestobject = $this->getObject("testobject");
        $arrAspects = array($this->getObject("aspect1"), $this->getObject("aspect2"), $this->getObject("aspect3"));


        $objTestobject->setArrObject1($arrAspects);
        $objTestobject->updateObjectToDb();

        $arrRow = $objDB->getPRow("SELECT COUNT(*) FROM "._dbprefix_."testclass_rel WHERE testclass_source_id = ?", array($objTestobject->getSystemid()));
        $this->assertEquals(3, $arrRow["COUNT(*)"]);

        //delete one aspect logically
        $arrAspects[1]->deleteObject();


        $objTestobject = new orm_objectlist_testclass($objTestobject->getSystemid());
        $this->assertEquals(count($objTestobject->getArrObject1()), 2);
        $objTestobject->setArrObject1(array($arrAspects[0], $arrAspects[2]));
        $objTestobject->updateObjectToDb();

        $arrRow = $objDB->getPRow("SELECT COUNT(*) FROM "._dbprefix_."testclass_rel WHERE testclass_source_id = ?", array($objTestobject->getSystemid()));
        $this->assertEquals(3, $arrRow["COUNT(*)"]);
    }


    public function testLogicalDeleteUpdateHandlingExcludedVariant2() {
        $objDB = class_carrier::getInstance()->getObjDB();

        /** @var orm_objectlist_testclass $objTestobject */
        $objTestobject = $this->getObject("testobject");
        $arrAspects = array($this->getObject("aspect1"), $this->getObject("aspect2"), $this->getObject("aspect3"));


        $objTestobject->setArrObject1($arrAspects);
        $objTestobject->updateObjectToDb();

        $arrRow = $objDB->getPRow("SELECT COUNT(*) FROM "._dbprefix_."testclass_rel WHERE testclass_source_id = ?", array($objTestobject->getSystemid()));
        $this->assertEquals(3, $arrRow["COUNT(*)"]);

        //delete one aspect logically
        $arrAspects[1]->deleteObject();


        $objTestobject = new orm_objectlist_testclass($objTestobject->getSystemid());
        $this->assertEquals(count($objTestobject->getArrObject1()), 2);
        $objTestobject->setArrObject1(array($arrAspects[0], $arrAspects[1]));
        $objTestobject->updateObjectToDb();

        $arrRow = $objDB->getPRow("SELECT COUNT(*) FROM "._dbprefix_."testclass_rel WHERE testclass_source_id = ?", array($objTestobject->getSystemid()));
        $this->assertEquals(2, $arrRow["COUNT(*)"]);
    }

    public function testLogicalDeleteUpdateHandlingIncluded() {
        $objDB = class_carrier::getInstance()->getObjDB();

        /** @var orm_objectlist_testclass $objTestobject */
        $objTestobject = $this->getObject("testobject");
        $arrAspects = array($this->getObject("aspect1"), $this->getObject("aspect2"), $this->getObject("aspect3"));


        $objTestobject->setArrObject1($arrAspects);
        $objTestobject->updateObjectToDb();

        $arrRow = $objDB->getPRow("SELECT COUNT(*) FROM "._dbprefix_."testclass_rel WHERE testclass_source_id = ?", array($objTestobject->getSystemid()));
        $this->assertEquals(3, $arrRow["COUNT(*)"]);

        //delete one aspect logically
        $arrAspects[1]->deleteObject();

        class_orm_base::setObjHandleLogicalDeletedGlobal(class_orm_deletedhandling_enum::INCLUDED);
        $objTestobject = new orm_objectlist_testclass($objTestobject->getSystemid());
        class_orm_base::setObjHandleLogicalDeletedGlobal(null);
        $this->assertEquals(count($objTestobject->getArrObject1()), 3);
        $objTestobject->updateObjectToDb();

        $arrRow = $objDB->getPRow("SELECT COUNT(*) FROM "._dbprefix_."testclass_rel WHERE testclass_source_id = ?", array($objTestobject->getSystemid()));
        $this->assertEquals(3, $arrRow["COUNT(*)"]);
    }




    public function testLogicalDeleteLoadHandling() {
        $objDB = class_carrier::getInstance()->getObjDB();

        /** @var orm_objectlist_testclass $objTestobject */
        $objTestobject = $this->getObject("testobject");
        $arrAspects = array($this->getObject("aspect1"), $this->getObject("aspect2"), $this->getObject("aspect3"));


        $objTestobject->setArrObject1($arrAspects);
        $objTestobject->updateObjectToDb();

        $arrRow = $objDB->getPRow("SELECT COUNT(*) FROM "._dbprefix_."testclass_rel WHERE testclass_source_id = ?", array($objTestobject->getSystemid()));
        $this->assertEquals(3, $arrRow["COUNT(*)"]);

        //delete one aspect logically
        $arrAspects[1]->deleteObject();


        $objTestobject = new orm_objectlist_testclass($objTestobject->getSystemid());
        $this->assertEquals(count($objTestobject->getArrObject1()), 2);

        class_orm_base::setObjHandleLogicalDeletedGlobal(class_orm_deletedhandling_enum::INCLUDED);
        $objTestobject = new orm_objectlist_testclass($objTestobject->getSystemid());
        class_orm_base::setObjHandleLogicalDeletedGlobal(null);
        $this->assertEquals(count($objTestobject->getArrObject1()), 3);

        class_orm_base::setObjHandleLogicalDeletedGlobal(class_orm_deletedhandling_enum::EXCLUSIVE);
        $objTestobject = new orm_objectlist_testclass($objTestobject->getSystemid());
        class_orm_base::setObjHandleLogicalDeletedGlobal(null);
        $this->assertEquals(count($objTestobject->getArrObject1()), 1);
        $this->assertEquals($objTestobject->getArrObject1()[0]->getSystemid(), $arrAspects[1]->getSystemid());

        class_orm_base::setObjHandleLogicalDeletedGlobal(null);
        $objTestobject = new orm_objectlist_testclass($objTestobject->getSystemid());
        class_orm_base::setObjHandleLogicalDeletedGlobal(null);
        $this->assertEquals(count($objTestobject->getArrObject1()), 2);
        $this->assertTrue(in_array($objTestobject->getArrObject1()[0]->getSystemid(), array($arrAspects[0]->getSystemid(), $arrAspects[2]->getSystemid())));




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

        //change the assignments
        $objTestobject = $this->getObject("testobject");
        $arrAspects = array($this->getObject("aspect2"));
        $objTestobject->setArrObject1($arrAspects);
        $objTestobject->updateObjectToDb();

        $arrRow = $objDB->getPRow("SELECT COUNT(*) FROM "._dbprefix_."testclass_rel WHERE testclass_source_id = ?", array($objTestobject->getSystemid()));
        $this->assertEquals(1, $arrRow["COUNT(*)"]);

        //change the assignments
        $objTestobject = $this->getObject("testobject");
        $objTestobject->setArrObject1(array());
        $objTestobject->updateObjectToDb();

        $arrRow = $objDB->getPRow("SELECT COUNT(*) FROM "._dbprefix_."testclass_rel WHERE testclass_source_id = ?", array($objTestobject->getSystemid()));
        $this->assertEquals(0, $arrRow["COUNT(*)"]);

        //change the assignments
        $objTestobject = $this->getObject("testobject");
        $arrAspects = array($this->getObject("aspect2"), $this->getObject("aspect1")->getSystemid());
        $objTestobject->setArrObject1($arrAspects);
        $objTestobject->updateObjectToDb();

        $arrRow = $objDB->getPRow("SELECT COUNT(*) FROM "._dbprefix_."testclass_rel WHERE testclass_source_id = ?", array($objTestobject->getSystemid()));
        $this->assertEquals(2, $arrRow["COUNT(*)"]);

    }

    public function testObjectassignmentsOnNonSavedObjects() {

        $objDB = class_carrier::getInstance()->getObjDB();

        /** @var orm_objectlist_testclass $objTestobject */
        $objTestobject = new orm_objectlist_testclass();
        $arrAspects = array($this->getObject("aspect1"), $this->getObject("aspect2"));


        $objTestobject->setArrObject1($arrAspects);
        $objTestobject->updateObjectToDb(class_module_system_module::getModuleByName("system")->getSystemid());

        $arrRow = $objDB->getPRow("SELECT COUNT(*) FROM "._dbprefix_."testclass_rel WHERE testclass_source_id = ?", array($objTestobject->getSystemid()));
        $this->assertEquals(2, $arrRow["COUNT(*)"]);

        $objTestobject->deleteObjectFromDatabase();



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


    public function testObjectassignmentEventHandling() {

        $objDB = class_carrier::getInstance()->getObjDB();


        $objHandler = new orm_objectlist_testhandler();
        class_core_eventdispatcher::getInstance()->removeAndAddListener(class_system_eventidentifier::EVENT_SYSTEM_OBJECTASSIGNMENTSUPDATED, $objHandler);

        /** @var orm_objectlist_testclass $objTestobject */
        $objTestobject = $this->getObject("testobject");
        $arrAspects = array($this->getObject("aspect1"), $this->getObject("aspect2"));
        $objTestobject->setArrObject1($arrAspects);

        $this->assertTrue($objHandler->arrCurrentAssignments == null);
        $this->assertTrue($objHandler->arrNewAssignments == null);
        $this->assertTrue($objHandler->arrRemovedAssignments == null);
        $this->assertTrue($objHandler->objObject == null);
        $this->assertTrue($objHandler->strProperty == null);
        $objTestobject->updateObjectToDb();

        $this->assertEquals(count($objHandler->arrNewAssignments), 2);
        $this->assertEquals(count($objHandler->arrRemovedAssignments), 0);
        $this->assertEquals(count($objHandler->arrCurrentAssignments), 2);
        $this->assertTrue(in_array($objHandler->arrNewAssignments[0], array($this->getObject("aspect1")->getSystemid(), $this->getObject("aspect2")->getSystemid())));
        $this->assertTrue(in_array($objHandler->arrNewAssignments[1], array($this->getObject("aspect1")->getSystemid(), $this->getObject("aspect2")->getSystemid())));
        $this->assertTrue(in_array($objHandler->arrCurrentAssignments[0], array($this->getObject("aspect1")->getSystemid(), $this->getObject("aspect2")->getSystemid())));
        $this->assertTrue(in_array($objHandler->arrCurrentAssignments[1], array($this->getObject("aspect1")->getSystemid(), $this->getObject("aspect2")->getSystemid())));

        //change the assignments
        $objHandler = new orm_objectlist_testhandler();
        class_core_eventdispatcher::getInstance()->removeAndAddListener(class_system_eventidentifier::EVENT_SYSTEM_OBJECTASSIGNMENTSUPDATED, $objHandler);

        $objTestobject = $this->getObject("testobject");
        $arrAspects = array($this->getObject("aspect2"));
        $objTestobject->setArrObject1($arrAspects);

        $this->assertTrue($objHandler->arrCurrentAssignments == null);
        $this->assertTrue($objHandler->arrNewAssignments == null);
        $this->assertTrue($objHandler->arrRemovedAssignments == null);
        $this->assertTrue($objHandler->objObject == null);
        $this->assertTrue($objHandler->strProperty == null);
        $objTestobject->updateObjectToDb();

        $this->assertEquals(count($objHandler->arrNewAssignments), 0);
        $this->assertEquals(count($objHandler->arrRemovedAssignments), 1);
        $this->assertEquals(count($objHandler->arrCurrentAssignments), 1);
        $this->assertTrue(in_array($objHandler->arrRemovedAssignments[0], array($this->getObject("aspect1")->getSystemid())));
        $this->assertTrue(in_array($objHandler->arrCurrentAssignments[0], array($this->getObject("aspect2")->getSystemid())));


        //change the assignments
        $objHandler = new orm_objectlist_testhandler();
        class_core_eventdispatcher::getInstance()->removeAndAddListener(class_system_eventidentifier::EVENT_SYSTEM_OBJECTASSIGNMENTSUPDATED, $objHandler);

        $objTestobject = $this->getObject("testobject");
        $objTestobject->setArrObject1(array());

        $this->assertTrue($objHandler->arrCurrentAssignments == null);
        $this->assertTrue($objHandler->arrNewAssignments == null);
        $this->assertTrue($objHandler->arrRemovedAssignments == null);
        $this->assertTrue($objHandler->objObject == null);
        $this->assertTrue($objHandler->strProperty == null);
        $objTestobject->updateObjectToDb();


        $this->assertEquals(count($objHandler->arrNewAssignments), 0);
        $this->assertEquals(count($objHandler->arrRemovedAssignments), 1);
        $this->assertEquals(count($objHandler->arrCurrentAssignments), 0);
        $this->assertTrue(in_array($objHandler->arrRemovedAssignments[0], array($this->getObject("aspect2")->getSystemid())));

        //change the assignments

        $objHandler = new orm_objectlist_testhandler();
        class_core_eventdispatcher::getInstance()->removeAndAddListener(class_system_eventidentifier::EVENT_SYSTEM_OBJECTASSIGNMENTSUPDATED, $objHandler);

        $objTestobject = $this->getObject("testobject");
        $arrAspects = array($this->getObject("aspect2"), $this->getObject("aspect1")->getSystemid());
        $objTestobject->setArrObject1($arrAspects);

        $this->assertTrue($objHandler->arrCurrentAssignments == null);
        $this->assertTrue($objHandler->arrNewAssignments == null);
        $this->assertTrue($objHandler->arrRemovedAssignments == null);
        $this->assertTrue($objHandler->objObject == null);
        $this->assertTrue($objHandler->strProperty == null);
        $objTestobject->updateObjectToDb();

        $this->assertEquals(count($objHandler->arrNewAssignments), 2);
        $this->assertEquals(count($objHandler->arrRemovedAssignments), 0);
        $this->assertEquals(count($objHandler->arrCurrentAssignments), 2);
        $this->assertTrue(in_array($objHandler->arrNewAssignments[0], array($this->getObject("aspect1")->getSystemid(), $this->getObject("aspect2")->getSystemid())));
        $this->assertTrue(in_array($objHandler->arrNewAssignments[1], array($this->getObject("aspect1")->getSystemid(), $this->getObject("aspect2")->getSystemid())));
        $this->assertTrue(in_array($objHandler->arrCurrentAssignments[0], array($this->getObject("aspect1")->getSystemid(), $this->getObject("aspect2")->getSystemid())));
        $this->assertTrue(in_array($objHandler->arrCurrentAssignments[1], array($this->getObject("aspect1")->getSystemid(), $this->getObject("aspect2")->getSystemid())));



        //do nothing

        $objHandler = new orm_objectlist_testhandler();
        class_core_eventdispatcher::getInstance()->removeAndAddListener(class_system_eventidentifier::EVENT_SYSTEM_OBJECTASSIGNMENTSUPDATED, $objHandler);

        $objTestobject = $this->getObject("testobject");
        $arrAspects = array($this->getObject("aspect2"), $this->getObject("aspect1")->getSystemid());
        $objTestobject->setArrObject1($arrAspects);

        $this->assertTrue($objHandler->arrCurrentAssignments == null);
        $this->assertTrue($objHandler->arrNewAssignments == null);
        $this->assertTrue($objHandler->arrRemovedAssignments == null);
        $this->assertTrue($objHandler->objObject == null);
        $this->assertTrue($objHandler->strProperty == null);
        $objTestobject->updateObjectToDb();

        $this->assertTrue($objHandler->arrCurrentAssignments == null);
        $this->assertTrue($objHandler->arrNewAssignments == null);
        $this->assertTrue($objHandler->arrRemovedAssignments == null);
        $this->assertTrue($objHandler->objObject == null);
        $this->assertTrue($objHandler->strProperty == null);

    }

    public function testAssignmentsDelete() {

        $objDB = class_carrier::getInstance()->getObjDB();

        /** @var orm_objectlist_testclass $objTestobject */
        $objTestobject = $this->getObject("testobject");
        $arrAspects = array($this->getObject("aspect2"), $this->getObject("aspect1")->getSystemid());
        $objTestobject->setArrObject1($arrAspects);
        $objTestobject->updateObjectToDb();

        $arrRow = $objDB->getPRow("SELECT COUNT(*) FROM "._dbprefix_."testclass_rel WHERE testclass_source_id = ?", array($objTestobject->getSystemid()));
        $this->assertEquals(2, $arrRow["COUNT(*)"]);

        $objTestobject->deleteObjectFromDatabase();

        $arrRow = $objDB->getPRow("SELECT COUNT(*) FROM "._dbprefix_."testclass_rel WHERE testclass_source_id = ?", array($objTestobject->getSystemid()), 0, false);
        $this->assertEquals(0, $arrRow["COUNT(*)"]);
    }


    public function testAssignmentClassTypeCheck() {
        $objDB = class_carrier::getInstance()->getObjDB();

        /** @var orm_objectlist_testclass $objTestobject */
        $objTestobject = $this->getObject("testobject");
        $arrAspects = array($this->getObject("aspect2"), $this->getObject("aspect1")->getSystemid(), $this->getObject("language"));
        $objTestobject->setArrObject2($arrAspects);
        $objTestobject->updateObjectToDb();

        $arrRow = $objDB->getPRow("SELECT COUNT(*) FROM "._dbprefix_."testclass2_rel WHERE testclass_source_id = ?", array($objTestobject->getSystemid()));
        $this->assertEquals(2, $arrRow["COUNT(*)"]);

        $objTestobject = new orm_objectlist_testclass($objTestobject->getSystemid());
        $this->assertEquals(2, count($objTestobject->getArrObject2()));

        $strQuery = "INSERT INTO "._dbprefix_."testclass2_rel  (testclass_source_id, testclass_target_id) VALUES (?, ?)";
        $objDB->_pQuery($strQuery, array($objTestobject->getSystemid(), $this->getObject("language")->getSystemid()));

        $objTestobject = new orm_objectlist_testclass($objTestobject->getSystemid());
        $this->assertEquals(2, count($objTestobject->getArrObject2()));


    }
}

class orm_objectlist_testhandler implements interface_genericevent_listener {

    public $arrNewAssignments = null;
    public $arrRemovedAssignments = null;
    public $arrCurrentAssignments = null;
    public $objObject = null;
    public $strProperty = null;

    public function handleEvent($strEventIdentifier, array $arrArguments) {
        list($this->arrNewAssignments, $this->arrRemovedAssignments, $this->arrCurrentAssignments, $this->objObject, $this->strProperty) = $arrArguments;
        return true;
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
     * @var array
     * @objectList testclass2_rel (source="testclass_source_id", target="testclass_target_id", type={"class_module_system_aspect"})
     */
    private $arrObject2 = array();

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

    /**
     * @return array
     */
    public function getArrObject2() {
        return $this->arrObject2;
    }

    /**
     * @param array $arrObject2
     */
    public function setArrObject2($arrObject2) {
        $this->arrObject2 = $arrObject2;
    }




}


