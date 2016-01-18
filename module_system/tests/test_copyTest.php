<?php

require_once (__DIR__."/../../module_system/system/class_testbase.php");

class class_test_copyTest extends class_testbase  {


    function testCopy() {


        $objAspect = new class_module_system_aspect();
        $objAspect->setStrName("copytest");
        $objAspect->updateObjectToDb();
        $strSysid = $objAspect->getSystemid();

        $objAspect->copyObject();
        $strCopyId = $objAspect->getSystemid();


        $objAspect = new class_module_system_aspect($strSysid);
        $objCopy = new class_module_system_aspect($strCopyId);

        $this->assertEquals($objAspect->getStrName(), $objCopy->getStrName());
        $this->assertEquals($objAspect->getStrPrevId(), $objCopy->getStrPrevId());
        $this->assertEquals($objAspect->getIntRecordStatus(), $objCopy->getIntRecordStatus());
        $this->assertEquals($objAspect->getStrRecordClass(), $objCopy->getStrRecordClass());
        $this->assertNotEquals($objAspect->getSystemid(), $objCopy->getSystemid());

        $objAspect->deleteObjectFromDatabase();
        $objCopy->deleteObjectFromDatabase();
    }

    function testCopySystemStatus() {


        $objAspect = new class_module_system_aspect();
        $objAspect->setStrName("copytest");
        $objAspect->updateObjectToDb();
        $strSysid = $objAspect->getSystemid();
        $objAspect->setIntRecordStatus(0);
        $objAspect->updateObjectToDb();

        $objAspect->copyObject();
        $strCopyId = $objAspect->getSystemid();


        $objAspect = new class_module_system_aspect($strSysid);
        $objCopy = new class_module_system_aspect($strCopyId);

        $this->assertEquals($objAspect->getStrName(), $objCopy->getStrName());
        $this->assertEquals($objAspect->getStrPrevId(), $objCopy->getStrPrevId());
        $this->assertEquals($objAspect->getIntRecordStatus(), $objCopy->getIntRecordStatus());
        $this->assertEquals($objAspect->getStrRecordClass(), $objCopy->getStrRecordClass());
        $this->assertNotEquals($objAspect->getSystemid(), $objCopy->getSystemid());

        $objAspect->deleteObjectFromDatabase();
        $objCopy->deleteObjectFromDatabase();
    }



    function testCopyPermissions() {

        $objAspect = new class_module_system_aspect();
        $objAspect->setStrName("copytest");
        $objAspect->updateObjectToDb();
        $strViewId = generateSystemid();
        $strSysid = $objAspect->getSystemid();

        $objRights = class_carrier::getInstance()->getObjRights();
        $objRights->addGroupToRight($strViewId, $strSysid, class_rights::$STR_RIGHT_RIGHT3);
        $arrRow = $objRights->getArrayRights($strSysid);

        $objAspect->copyObject();
        $strCopyId = $objAspect->getSystemid();
        $arrCopyRow = $objRights->getArrayRights($strCopyId);

        $this->assertEquals($arrRow, $arrCopyRow);

        $objAspect = new class_module_system_aspect($strSysid);
        $objCopy = new class_module_system_aspect($strCopyId);

        $objAspect->deleteObjectFromDatabase();
        $objCopy->deleteObjectFromDatabase();

    }


}

