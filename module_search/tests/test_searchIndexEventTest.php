<?php

require_once(__DIR__ . "/../../module_system/system/class_testbase.php");

class class_test_searchIndexEventTest extends class_testbase {

    protected function setUp() {
        parent::setUp();
        class_module_search_objectdeletedlistener::$BIT_UPDATE_INDEX_ON_END_OF_REQUEST = false;
        class_module_search_recordupdatedlistener::$BIT_UPDATE_INDEX_ON_END_OF_REQUEST = false;
    }

    protected function tearDown() {
        parent::tearDown();
        class_module_search_objectdeletedlistener::$BIT_UPDATE_INDEX_ON_END_OF_REQUEST = true;
        class_module_search_recordupdatedlistener::$BIT_UPDATE_INDEX_ON_END_OF_REQUEST = true;
    }


    public function testIndexEvent() {

        if(class_module_system_module::getModuleByName("tags") == null || class_module_system_module::getModuleByName("system") == null)
            return;


        $strSearchKey1 = generateSystemid();

        $objAspect = new class_module_system_aspect();
        $objAspect->setStrName($strSearchKey1);
        $objAspect->updateObjectToDb();

        $objSearchCommons = new class_module_search_commons();

        $objSearchParams = new class_module_search_search();
        $objSearchParams->setStrQuery($strSearchKey1);
        $arrResult = $objSearchCommons->doIndexedSearch($objSearchParams, null);
        $this->assertEquals(count($arrResult), 1);
        $this->assertEquals($arrResult[0]->getObjObject()->getStrSystemid(), $objAspect->getStrSystemid());





        $strSearchKey2 = generateSystemid();
        $objTag = new class_module_tags_tag();
        $objTag->setStrName($strSearchKey2);
        $objTag->updateObjectToDb();


        $objSearchParams = new class_module_search_search();
        $objSearchParams->setStrQuery($strSearchKey2);
        $arrResult = $objSearchCommons->doIndexedSearch($objSearchParams, null);
        $this->assertEquals(count($arrResult), 1);
        $this->assertEquals($arrResult[0]->getObjObject()->getStrSystemid(), $objTag->getStrSystemid());


        $objTag->assignToSystemrecord($objAspect->getStrSystemid());

        $arrResult = $objSearchCommons->doIndexedSearch($objSearchParams, null);
        $this->assertEquals(count($arrResult), 2);

        $objSearchParams->setStrInternalFilterModules(_system_modul_id_);
        $arrResult = $objSearchCommons->doIndexedSearch($objSearchParams, null);
        $this->assertEquals(count($arrResult), 1);
        $this->assertEquals($arrResult[0]->getObjObject()->getStrSystemid(), $objAspect->getStrSystemid());


        $objTag->removeFromSystemrecord($objAspect->getStrSystemid());

        //the aspect itself should not be found any more
        $objSearchParams = new class_module_search_search();
        $objSearchParams->setStrQuery($strSearchKey2);
        $arrResult = $objSearchCommons->doIndexedSearch($objSearchParams, null);
        $this->assertEquals(count($arrResult), 1);
        $this->assertEquals($arrResult[0]->getObjObject()->getStrSystemid(), $objTag->getStrSystemid());




        $objAspect->deleteObjectFromDatabase();
        $objTag->deleteObjectFromDatabase();

    }

}

