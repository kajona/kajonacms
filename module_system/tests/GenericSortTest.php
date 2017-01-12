<?php

namespace Kajona\System\Tests;

use Kajona\System\System\Carrier;
use Kajona\System\System\Database;
use Kajona\System\System\OrmDeletedhandlingEnum;
use Kajona\System\System\OrmObjectlist;
use Kajona\System\System\SystemAspect;

class SortTest extends Testbase
{


    public function testSortOnLogicalDelete()
    {
        $objRootAspect = new SystemAspect();
        $objRootAspect->setStrName("testroot");
        $objRootAspect->updateObjectToDb();

        /** @var SystemAspect[] $arrAspects */
        $arrAspects = array();
        for ($intI = 0; $intI < 5; $intI++) {
            $objAspect = new SystemAspect();
            $objAspect->setStrName("autotest_" . $intI);
            $objAspect->updateObjectToDb($objRootAspect->getSystemid());
            $arrAspects[] = $objAspect;
        }

        $arrAspects[3]->deleteObject();


        $objOrm = new OrmObjectlist();
        $objOrm->setObjHandleLogicalDeleted(OrmDeletedhandlingEnum::INCLUDED);
        /** @var SystemAspect[] $arrList */
        $arrList = $objOrm->getObjectList(SystemAspect::class, $objRootAspect->getSystemid());

        $this->assertEquals(5, count($arrList));

        $this->assertEquals("autotest_0", $arrList[0]->getStrName());
        $this->assertEquals("autotest_1", $arrList[1]->getStrName());
        $this->assertEquals("autotest_2", $arrList[2]->getStrName());
        $this->assertEquals("autotest_4", $arrList[3]->getStrName());
        $this->assertEquals("autotest_3", $arrList[4]->getStrName());

        $this->assertEquals(1, $arrList[0]->getIntSort());
        $this->assertEquals(2, $arrList[1]->getIntSort());
        $this->assertEquals(3, $arrList[2]->getIntSort());
        $this->assertEquals(4, $arrList[3]->getIntSort());
        $this->assertEquals(-1, $arrList[4]->getIntSort());

        //add another record
        $objAspect = new SystemAspect();
        $objAspect->setStrName("autotest_" . $intI);
        $objAspect->updateObjectToDb($objRootAspect->getSystemid());
        $arrAspects[] = $objAspect;

        $this->assertEquals(5, $objAspect->getIntSort());

        $objRootAspect->deleteObjectFromDatabase();

    }





    function testSortOnDelete()
    {

        $objRootAspect = new SystemAspect();
        $objRootAspect->setStrName("testroot");
        $objRootAspect->updateObjectToDb();

        /** @var SystemAspect[] $arrAspects */
        $arrAspects = array();
        for ($intI = 0; $intI < 10; $intI++) {
            $objAspect = new SystemAspect();
            $objAspect->setStrName("autotest_" . $intI);
            $objAspect->updateObjectToDb($objRootAspect->getSystemid());
            $arrAspects[] = $objAspect;
        }

        //delete the 5th element - massive queries required
        $arrAspects[5]->deleteObjectFromDatabase();


        $objOrm = new OrmObjectlist();
        $arrChilds = $objOrm->getObjectList("Kajona\\System\\System\\SystemAspect", $objRootAspect->getSystemid());
        $this->assertEquals(count($arrChilds), 9);
        for ($intI = 1; $intI <= 9; $intI++) {
            $this->assertEquals($arrChilds[$intI - 1]->getIntSort(), $intI);
        }


        $objRootAspect->deleteObjectFromDatabase();
    }


    public function testTreeSortBehaviour()
    {

        $objDB = Carrier::getInstance()->getObjDB();

        //test the setToPos
        //create 10 test records
        $objAspect = new SystemAspect();
        //new base-node
        $objAspect->updateObjectToDb();
        $strBaseNodeId = $objAspect->getSystemid();
        $arrNodes = array();
        for ($intI = 1; $intI <= 10; $intI++) {
            $objAspect = new SystemAspect();
            $objAspect->updateObjectToDb($strBaseNodeId);
            $arrNodes[] = $objAspect->getSystemid();
        }

        //initial movings
        $objAspect = new SystemAspect($arrNodes[1]);
        $objAspect->setPosition("upwards");
        $arrNodes = $objDB->getPArray("SELECT system_id FROM " . _dbprefix_ . "system WHERE system_prev_id = ? ORDER BY system_sort ASC", array($strBaseNodeId));
        //move the third to the first pos
        $objAspect = new SystemAspect($arrNodes[2]["system_id"]);
        $objAspect->setPosition("upwards");
        $objAspect->setPosition("upwards");
        $objAspect->setPosition("upwards");
        //next one should be with no effect
        $objAspect->setPosition("upwards");
        $objDB->flushQueryCache();
        $arrNodesAfter = $objDB->getPArray("SELECT system_id FROM " . _dbprefix_ . "system WHERE system_prev_id = ? ORDER BY system_sort ASC", array($strBaseNodeId));

        $this->assertEquals($arrNodesAfter[0]["system_id"], $arrNodes[2]["system_id"], __FILE__ . " checkPositionShitftingByRelativeShift");
        $this->assertEquals($arrNodesAfter[1]["system_id"], $arrNodes[0]["system_id"], __FILE__ . " checkPositionShitftingByRelativeShift");
        $this->assertEquals($arrNodesAfter[2]["system_id"], $arrNodes[1]["system_id"], __FILE__ . " checkPositionShitftingByRelativeShift");

        //moving by set pos
        $objDB->flushQueryCache();
        $arrNodes = $objDB->getPArray("SELECT system_id FROM " . _dbprefix_ . "system WHERE system_prev_id = ? ORDER BY system_sort ASC", array($strBaseNodeId));
        $objDB->flushQueryCache();
        $objAspect = new SystemAspect($arrNodes[2]["system_id"]);
        $objAspect->setAbsolutePosition(1);
        $arrNodesAfter = $objDB->getPArray("SELECT system_id FROM " . _dbprefix_ . "system WHERE system_prev_id = ? ORDER BY system_sort ASC", array($strBaseNodeId));
        $this->assertEquals($arrNodesAfter[0]["system_id"], $arrNodes[2]["system_id"], __FILE__ . " checkPositionShitftingByAbsoluteShift");
        $this->assertEquals($arrNodesAfter[1]["system_id"], $arrNodes[0]["system_id"], __FILE__ . " checkPositionShitftingByAbsoluteShift");
        $this->assertEquals($arrNodesAfter[2]["system_id"], $arrNodes[1]["system_id"], __FILE__ . " checkPositionShitftingByAbsoluteShift");
        //and back...
        $objDB->flushQueryCache();
        $objAspect = new SystemAspect($arrNodes[2]["system_id"]);
        $objAspect->setAbsolutePosition(3);
        $objDB->flushQueryCache();
        $arrNodesAfter = $objDB->getPArray("SELECT system_id FROM " . _dbprefix_ . "system WHERE system_prev_id = ? ORDER BY system_sort ASC", array($strBaseNodeId));
        $this->assertEquals($arrNodesAfter[0]["system_id"], $arrNodes[0]["system_id"], __FILE__ . " checkPositionShitftingByAbsoluteShift");
        $this->assertEquals($arrNodesAfter[1]["system_id"], $arrNodes[1]["system_id"], __FILE__ . " checkPositionShitftingByAbsoluteShift");
        $this->assertEquals($arrNodesAfter[2]["system_id"], $arrNodes[2]["system_id"], __FILE__ . " checkPositionShitftingByAbsoluteShift");

        //deleting all records created
        foreach ($arrNodes as $arrOneNode) {
            $objAspect = new SystemAspect($arrOneNode["system_id"]);
            $objAspect->deleteObjectFromDatabase();
        }
        $objAspect = new SystemAspect($strBaseNodeId);
        $objAspect->deleteObjectFromDatabase();
    }


}

