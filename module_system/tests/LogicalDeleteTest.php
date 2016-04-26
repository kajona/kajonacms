<?php

namespace Kajona\System\Tests;

use Kajona\System\System\OrmBase;
use Kajona\System\System\OrmDeletedhandlingEnum;
use Kajona\System\System\SystemAspect;

class LogicalDeleteTest extends Testbase
{

    public function testLogicalDelete()
    {

        OrmBase::setObjHandleLogicalDeletedGlobal(OrmDeletedhandlingEnum::INCLUDED);
        $intCountTotal = SystemAspect::getObjectCount();

        OrmBase::setObjHandleLogicalDeletedGlobal(OrmDeletedhandlingEnum::EXCLUDED);
        $intCountActive = SystemAspect::getObjectCount();

        OrmBase::setObjHandleLogicalDeletedGlobal(OrmDeletedhandlingEnum::EXCLUSIVE);
        $intCountDeleted = SystemAspect::getObjectCount();

        OrmBase::setObjHandleLogicalDeletedGlobal(OrmDeletedhandlingEnum::EXCLUDED);

        echo "Creating aspect\n";

        $objAspect1 = new SystemAspect();
        $objAspect1->setStrName("Dummy");
        $objAspect1->updateObjectToDb();


        $objAspect = new SystemAspect();
        $objAspect->setStrName("logical delete");
        $objAspect->updateObjectToDb();
        $strAspectId = $objAspect->getSystemid();

        $this->assertEquals($intCountActive + 2, SystemAspect::getObjectCount());

        $arrAspects = SystemAspect::getObjectList();
        $arrAspects = array_filter($arrAspects, function (SystemAspect $objAspect) use ($strAspectId) {
            return $objAspect->getSystemid() == $strAspectId;
        });

        $this->assertEquals(1, count($arrAspects));


        echo "Deleting logically\n";
        $this->assertEquals($objAspect->getIntRecordDeleted(), 0);
        $objAspect->deleteObject();

        $this->assertEquals($objAspect->getIntRecordDeleted(), 1);
        $this->assertEquals($objAspect->getIntSort(), -1);


        OrmBase::setObjHandleLogicalDeletedGlobal(OrmDeletedhandlingEnum::EXCLUDED);
        echo "Loading non-deleted only\n";
        $this->assertEquals($intCountActive + 1, SystemAspect::getObjectCount());

        $arrAspects = SystemAspect::getObjectList();
        $arrAspects = array_filter($arrAspects, function (SystemAspect $objAspect) use ($strAspectId) {
            return $objAspect->getSystemid() == $strAspectId;
        });

        $this->assertEquals(0, count($arrAspects));

        echo "Loading deleted only\n";
        OrmBase::setObjHandleLogicalDeletedGlobal(OrmDeletedhandlingEnum::EXCLUSIVE);

        $arrAspects = SystemAspect::getObjectList();
        $arrAspects = array_filter($arrAspects, function (SystemAspect $objAspect) use ($strAspectId) {
            return $objAspect->getSystemid() == $strAspectId;
        });

        $this->assertEquals($intCountDeleted + 1, SystemAspect::getObjectCount());
        $this->assertEquals(1, count($arrAspects));


        echo "Loading mixed deleted and non-deleted\n";
        OrmBase::setObjHandleLogicalDeletedGlobal(OrmDeletedhandlingEnum::INCLUDED);

        $arrAspects = SystemAspect::getObjectList();
        $arrAspects = array_filter($arrAspects, function (SystemAspect $objAspect) use ($strAspectId) {
            return $objAspect->getSystemid() == $strAspectId;
        });

        $this->assertEquals($intCountTotal + 2, SystemAspect::getObjectCount());
        $this->assertEquals(1, count($arrAspects));


        echo "Deleting from database\n";
        $objAspect->deleteObjectFromDatabase();

        OrmBase::setObjHandleLogicalDeletedGlobal(OrmDeletedhandlingEnum::EXCLUDED);
        echo "Loading non-deleted only\n";
        $this->assertEquals($intCountActive + 1, SystemAspect::getObjectCount());
        $arrAspects = SystemAspect::getObjectList();
        $arrAspects = array_filter($arrAspects, function (SystemAspect $objAspect) use ($strAspectId) {
            return $objAspect->getSystemid() == $strAspectId;
        });
        $this->assertEquals(0, count($arrAspects));

        echo "Loading deleted only\n";
        OrmBase::setObjHandleLogicalDeletedGlobal(OrmDeletedhandlingEnum::EXCLUSIVE);
        $arrAspects = SystemAspect::getObjectList();
        $arrAspects = array_filter($arrAspects, function (SystemAspect $objAspect) use ($strAspectId) {
            return $objAspect->getSystemid() == $strAspectId;
        });
        $this->assertEquals($intCountDeleted, SystemAspect::getObjectCount());
        $this->assertEquals(0, count($arrAspects));


        echo "Deleting dummy node directly\n";
        $objAspect1->deleteObjectFromDatabase();


        echo "Loading non-deleted only\n";
        OrmBase::setObjHandleLogicalDeletedGlobal(OrmDeletedhandlingEnum::EXCLUDED);
        $this->assertEquals($intCountActive, SystemAspect::getObjectCount());

        echo "Loading deleted only\n";
        OrmBase::setObjHandleLogicalDeletedGlobal(OrmDeletedhandlingEnum::EXCLUSIVE);
        $this->assertEquals($intCountDeleted, SystemAspect::getObjectCount());


    }

    protected function tearDown()
    {
        OrmBase::setObjHandleLogicalDeletedGlobal(OrmDeletedhandlingEnum::EXCLUDED);
        parent::tearDown();
    }


    public function testDeleteSortHandling()
    {

    }
}

