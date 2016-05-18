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
        $intCountTotal = SystemAspect::getObjectCountFiltered();

        OrmBase::setObjHandleLogicalDeletedGlobal(OrmDeletedhandlingEnum::EXCLUDED);
        $intCountActive = SystemAspect::getObjectCountFiltered();

        OrmBase::setObjHandleLogicalDeletedGlobal(OrmDeletedhandlingEnum::EXCLUSIVE);
        $intCountDeleted = SystemAspect::getObjectCountFiltered();

        OrmBase::setObjHandleLogicalDeletedGlobal(OrmDeletedhandlingEnum::EXCLUDED);

        $objAspect1 = new SystemAspect();
        $objAspect1->setStrName("Dummy");
        $objAspect1->updateObjectToDb();


        $objAspect = new SystemAspect();
        $objAspect->setStrName("logical delete");
        $objAspect->updateObjectToDb();
        $strAspectId = $objAspect->getSystemid();

        $this->assertEquals($intCountActive + 2, SystemAspect::getObjectCountFiltered());

        $arrAspects = SystemAspect::getObjectListFiltered();
        $arrAspects = array_filter($arrAspects, function (SystemAspect $objAspect) use ($strAspectId) {
            return $objAspect->getSystemid() == $strAspectId;
        });

        $this->assertEquals(1, count($arrAspects));


        $this->assertEquals($objAspect->getIntRecordDeleted(), 0);
        $objAspect->deleteObject();

        $this->assertEquals($objAspect->getIntRecordDeleted(), 1);
        $this->assertEquals($objAspect->getIntSort(), -1);


        OrmBase::setObjHandleLogicalDeletedGlobal(OrmDeletedhandlingEnum::EXCLUDED);
        $this->assertEquals($intCountActive + 1, SystemAspect::getObjectCountFiltered());

        $arrAspects = SystemAspect::getObjectListFiltered();
        $arrAspects = array_filter($arrAspects, function (SystemAspect $objAspect) use ($strAspectId) {
            return $objAspect->getSystemid() == $strAspectId;
        });

        $this->assertEquals(0, count($arrAspects));

        OrmBase::setObjHandleLogicalDeletedGlobal(OrmDeletedhandlingEnum::EXCLUSIVE);

        $arrAspects = SystemAspect::getObjectListFiltered();
        $arrAspects = array_filter($arrAspects, function (SystemAspect $objAspect) use ($strAspectId) {
            return $objAspect->getSystemid() == $strAspectId;
        });

        $this->assertEquals($intCountDeleted + 1, SystemAspect::getObjectCountFiltered());
        $this->assertEquals(1, count($arrAspects));


        OrmBase::setObjHandleLogicalDeletedGlobal(OrmDeletedhandlingEnum::INCLUDED);

        $arrAspects = SystemAspect::getObjectListFiltered();
        $arrAspects = array_filter($arrAspects, function (SystemAspect $objAspect) use ($strAspectId) {
            return $objAspect->getSystemid() == $strAspectId;
        });

        $this->assertEquals($intCountTotal + 2, SystemAspect::getObjectCountFiltered());
        $this->assertEquals(1, count($arrAspects));


        $objAspect->deleteObjectFromDatabase();

        OrmBase::setObjHandleLogicalDeletedGlobal(OrmDeletedhandlingEnum::EXCLUDED);
        $this->assertEquals($intCountActive + 1, SystemAspect::getObjectCountFiltered());
        $arrAspects = SystemAspect::getObjectListFiltered();
        $arrAspects = array_filter($arrAspects, function (SystemAspect $objAspect) use ($strAspectId) {
            return $objAspect->getSystemid() == $strAspectId;
        });
        $this->assertEquals(0, count($arrAspects));

        OrmBase::setObjHandleLogicalDeletedGlobal(OrmDeletedhandlingEnum::EXCLUSIVE);
        $arrAspects = SystemAspect::getObjectListFiltered();
        $arrAspects = array_filter($arrAspects, function (SystemAspect $objAspect) use ($strAspectId) {
            return $objAspect->getSystemid() == $strAspectId;
        });
        $this->assertEquals($intCountDeleted, SystemAspect::getObjectCountFiltered());
        $this->assertEquals(0, count($arrAspects));


        $objAspect1->deleteObjectFromDatabase();


        OrmBase::setObjHandleLogicalDeletedGlobal(OrmDeletedhandlingEnum::EXCLUDED);
        $this->assertEquals($intCountActive, SystemAspect::getObjectCountFiltered());

        OrmBase::setObjHandleLogicalDeletedGlobal(OrmDeletedhandlingEnum::EXCLUSIVE);
        $this->assertEquals($intCountDeleted, SystemAspect::getObjectCountFiltered());


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

