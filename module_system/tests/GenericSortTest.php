<?php

namespace Kajona\System\Tests;

require_once __DIR__ . "../../../core/module_system/system/Testbase.php";

use Kajona\System\System\Carrier;
use Kajona\System\System\Database;
use Kajona\System\System\OrmObjectlist;
use Kajona\System\System\SystemAspect;
use Kajona\System\System\Testbase;

class SortTest extends Testbase
{


    function testSortOnDelete()
    {

        $objRootAspect = new SystemAspect();
        $objRootAspect->setStrName("testroot");
        $objRootAspect->updateObjectToDb();

        /** @var SystemAspect[] $arrAspects */
        $arrAspects = array();
        for ($intI = 0; $intI < 100; $intI++) {
            $objAspect = new SystemAspect();
            $objAspect->setStrName("autotest_" . $intI);
            $objAspect->updateObjectToDb($objRootAspect->getSystemid());
            $arrAspects[] = $objAspect;
        }

        //delete the 5th element - massive queries required
        $intQueriesPre = Database::getInstance()->getNumber();
        echo " Setting new position\n";
        $arrAspects[5]->deleteObjectFromDatabase();

        $intQueriesPost = Database::getInstance()->getNumber();
        echo "Queries: " . ($intQueriesPost - $intQueriesPre) . " \n";

        $objOrm = new OrmObjectlist();
        $arrChilds = $objOrm->getObjectList("Kajona\\System\\System\\SystemAspect", $objRootAspect->getSystemid());
        $this->assertEquals(count($arrChilds), 99);
        for ($intI = 1; $intI <= 99; $intI++) {
            $this->assertEquals($arrChilds[$intI - 1]->getIntSort(), $intI);
        }


        $objRootAspect->deleteObjectFromDatabase();
    }


    function testTreeSortBehaviour()
    {

        $objDB = Carrier::getInstance()->getObjDB();

        //test the setToPos
        echo "\tposition handling...\n";
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
        echo "\trelative shiftings...\n";
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
        echo "\tabsolute shifting..\n";
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

