<?php

namespace Kajona\System\Tests;

require_once __DIR__ . "../../../core/module_system/system/Testbase.php";

use Kajona\System\System\Carrier;
use Kajona\System\System\Rights;
use Kajona\System\System\SystemAspect;
use Kajona\System\System\Testbase;

class CopyTest extends Testbase
{


    function testCopy()
    {


        $objAspect = new SystemAspect();
        $objAspect->setStrName("copytest");
        $objAspect->updateObjectToDb();
        $strSysid = $objAspect->getSystemid();

        $objAspect->copyObject();
        $strCopyId = $objAspect->getSystemid();


        $objAspect = new SystemAspect($strSysid);
        $objCopy = new SystemAspect($strCopyId);

        $this->assertEquals($objAspect->getStrName(), $objCopy->getStrName());
        $this->assertEquals($objAspect->getStrPrevId(), $objCopy->getStrPrevId());
        $this->assertEquals($objAspect->getIntRecordStatus(), $objCopy->getIntRecordStatus());
        $this->assertEquals($objAspect->getStrRecordClass(), $objCopy->getStrRecordClass());
        $this->assertNotEquals($objAspect->getSystemid(), $objCopy->getSystemid());

        $objAspect->deleteObjectFromDatabase();
        $objCopy->deleteObjectFromDatabase();
    }

    function testCopySystemStatus()
    {


        $objAspect = new SystemAspect();
        $objAspect->setStrName("copytest");
        $objAspect->updateObjectToDb();
        $strSysid = $objAspect->getSystemid();
        $objAspect->setIntRecordStatus(0);
        $objAspect->updateObjectToDb();

        $objAspect->copyObject();
        $strCopyId = $objAspect->getSystemid();


        $objAspect = new SystemAspect($strSysid);
        $objCopy = new SystemAspect($strCopyId);

        $this->assertEquals($objAspect->getStrName(), $objCopy->getStrName());
        $this->assertEquals($objAspect->getStrPrevId(), $objCopy->getStrPrevId());
        $this->assertEquals($objAspect->getIntRecordStatus(), $objCopy->getIntRecordStatus());
        $this->assertEquals($objAspect->getStrRecordClass(), $objCopy->getStrRecordClass());
        $this->assertNotEquals($objAspect->getSystemid(), $objCopy->getSystemid());

        $objAspect->deleteObjectFromDatabase();
        $objCopy->deleteObjectFromDatabase();
    }


    function testCopyPermissions()
    {

        $objAspect = new SystemAspect();
        $objAspect->setStrName("copytest");
        $objAspect->updateObjectToDb();
        $strViewId = generateSystemid();
        $strSysid = $objAspect->getSystemid();

        $objRights = Carrier::getInstance()->getObjRights();
        $objRights->addGroupToRight($strViewId, $strSysid, Rights::$STR_RIGHT_RIGHT3);
        $arrRow = $objRights->getArrayRights($strSysid);

        $objAspect->copyObject();
        $strCopyId = $objAspect->getSystemid();
        $arrCopyRow = $objRights->getArrayRights($strCopyId);

        $this->assertEquals($arrRow, $arrCopyRow);

        $objAspect = new SystemAspect($strSysid);
        $objCopy = new SystemAspect($strCopyId);

        $objAspect->deleteObjectFromDatabase();
        $objCopy->deleteObjectFromDatabase();

    }


}

