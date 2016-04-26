<?php

namespace Kajona\System\Tests;

use Kajona\System\System\Carrier;
use Kajona\System\System\Database;
use Kajona\System\System\Exception;
use Kajona\System\System\SystemAspect;

class SystemTest extends Testbase
{


    public function testKernel()
    {
        $objDB = Carrier::getInstance()->getObjDB();

        //--- system kernel -------------------------------------------------------------------------------------
        echo "\tsystem-kernel...\n";

        echo "\tcreating 100 system-records with right-records...\n";
        //nr of records currently
        $arrRow = $objDB->getPRow("SELECT COUNT(*) FROM " . _dbprefix_ . "system", array(), 0, false);
        $intNrSystemRecords = $arrRow["COUNT(*)"];
        $arrRow = $objDB->getPRow("SELECT COUNT(*) FROM " . _dbprefix_ . "system_right", array(), 0, false);
        $intNrRightsRecords = $arrRow["COUNT(*)"];
        $objAspect = new SystemAspect();
        $arrSysRecords = array();
        for ($intI = 0; $intI <= 100; $intI++) {
            $objAspect = new SystemAspect();
            $objAspect->updateObjectToDb();
            $arrSysRecords[] = $objAspect->getSystemid();

            $arrRow = $objDB->getPRow("SELECT COUNT(*) FROM " . _dbprefix_ . "system", array(), 0, false);
            $this->assertEquals($arrRow["COUNT(*)"], $intI + $intNrSystemRecords + 1, __FILE__ . " checkCreateSysRecordsWithRights");
            $arrRow = $objDB->getPRow("SELECT COUNT(*) FROM " . _dbprefix_ . "system_right", array(), 0, false);
            $this->assertEquals($arrRow["COUNT(*)"], $intNrRightsRecords + $intI + 1, __FILE__ . " checkCreateSysRecordsWithRights");
        }


        echo "\tdeleting 100 system-records with right-records...\n";
        foreach ($arrSysRecords as $strOneId) {
            $objAspect = new SystemAspect($strOneId);
            $objAspect->deleteObjectFromDatabase();
        }
        $arrRow = $objDB->getPRow("SELECT COUNT(*) FROM " . _dbprefix_ . "system", array(), 0, false);
        $this->assertEquals($arrRow["COUNT(*)"], $intNrSystemRecords, __FILE__ . " checkDeleteSysRecordsWithRights");
        $arrRow = $objDB->getPRow("SELECT COUNT(*) FROM " . _dbprefix_ . "system_right", array(), 0, false);
        $this->assertEquals($arrRow["COUNT(*)"], $intNrRightsRecords, __FILE__ . " checkDeleteSysRecordsWithRights");

    }


    function testSectionHandling()
    {

        $objDB = Carrier::getInstance()->getObjDB();

        //test sections
        echo "\tsection-handling of class db...\n";
        //create 10 test records
        $objAspect = new SystemAspect();
        //new base-node
        $objAspect->updateObjectToDb();
        $strBaseNodeId = $objAspect->getSystemid();
        $arrNodes = array();
        for ($intI = 1; $intI <= 10; $intI++) {
            $objAspect = new SystemAspect();
            $objAspect->setStrName("sectionTest_" . $intI);
            $objAspect->updateObjectToDb($strBaseNodeId);
            $arrNodes[] = $objAspect->getSystemid();
        }
        $arrNodes = $objDB->getPArray("SELECT system_id FROM " . _dbprefix_ . "system WHERE system_prev_id = ? ORDER BY system_sort ASC", array($strBaseNodeId));
        $arrNodesSection = $objDB->getPArray("SELECT system_id FROM " . _dbprefix_ . "system WHERE system_prev_id = ? ORDER BY system_sort ASC", array($strBaseNodeId), 2, 4, false);
        $this->assertEquals($arrNodesSection[0]["system_id"], $arrNodes[2]["system_id"], __FILE__ . " checkSectionLoading");
        $this->assertEquals($arrNodesSection[1]["system_id"], $arrNodes[3]["system_id"], __FILE__ . " checkSectionLoading");
        $this->assertEquals($arrNodesSection[2]["system_id"], $arrNodes[4]["system_id"], __FILE__ . " checkSectionLoading");

        //deleting all records created
        foreach ($arrNodes as $arrOneNode) {
            $objAspect = new SystemAspect($arrOneNode["system_id"]);
            $objAspect->deleteObjectFromDatabase();
        }
        $objAspect = new SystemAspect($strBaseNodeId);
        $objAspect->deleteObjectFromDatabase($strBaseNodeId);
    }


    function testTreeBehaviour()
    {


        $objDB = Carrier::getInstance()->getObjDB();
        echo "\ttesting tree-behaviour...\n";
        //nr of records currently
        $arrSysRecords = array();
        $arrRow = $objDB->getPRow("SELECT COUNT(*) FROM " . _dbprefix_ . "system", array(), 0, false);
        $intNrSystemRecords = $arrRow["COUNT(*)"];
        $arrRow = $objDB->getPRow("SELECT COUNT(*) FROM " . _dbprefix_ . "system_right", array(), 0, false);
        //base-id
        echo "\tcreating root node...\n";
        $objAspect = new SystemAspect();
        $objAspect->updateObjectToDb();
        $intBaseId = $objAspect->getSystemid();
        //two under the base
        echo "\tcreating child nodes...\n";
        $objAspect = new SystemAspect();
        $objAspect->updateObjectToDb($intBaseId);
        $intSecOneId = $objAspect->getSystemid();
        $objAspect = new SystemAspect();
        $objAspect->updateObjectToDb($intBaseId);
        $intSecTwoId = $objAspect->getSystemid();
        $arrSysRecords[] = $intBaseId;
        $arrSysRecords[] = $intSecOneId;
        $arrSysRecords[] = $intSecTwoId;
        //twenty under both levels
        for ($intI = 0; $intI < 20; $intI++) {
            $objAspect = new SystemAspect();
            $objAspect->updateObjectToDb($intSecOneId);
            $arrSysRecords[] = $objAspect->getSystemid();
            $objAspect = new SystemAspect();
            $objAspect->updateObjectToDb($intSecTwoId);
            $arrSysRecords[] = $objAspect->getSystemid();
            $objAspect = new SystemAspect();
            $objAspect->updateObjectToDb($intBaseId);
            $arrSysRecords[] = $objAspect->getSystemid();
        }
        //check nr of records
        $intCount = $objAspect->getNumberOfSiblings($intSecOneId);
        $this->assertEquals($intCount, 22, __FILE__ . " checkNrOfSiblingsInTree");
        //check nr of childs
        $arrRow = $objDB->getPRow("SELECT COUNT(*) FROM " . _dbprefix_ . "system WHERE system_prev_id = ?", array($intBaseId));
        $this->assertEquals($arrRow["COUNT(*)"], 22, __FILE__ . " checkNrOfChildsInTree1");
        $arrRow = $objDB->getPRow("SELECT COUNT(*) FROM " . _dbprefix_ . "system WHERE system_prev_id = ?", array($intSecOneId));
        $this->assertEquals($arrRow["COUNT(*)"], 20, __FILE__ . " checkNrOfChildsInTree2");
        $arrRow = $objDB->getPRow("SELECT COUNT(*) FROM " . _dbprefix_ . "system WHERE system_prev_id = ?", array($intSecTwoId));
        $this->assertEquals($arrRow["COUNT(*)"], 20, __FILE__ . " checkNrOfChildsInTree3");

        //deleting all records
        echo "\tdeleting nodes...\n";
        foreach ($arrSysRecords as $strOneId) {
            $objAspect->deleteSystemRecord($strOneId);
        }

    }


    public function testTreeDelete()
    {

        $objAspect = new SystemAspect();
        $objAspect->updateObjectToDb("0");
        $strRootNodeId = $objAspect->getSystemid();

        $objAspect = new SystemAspect();
        $objAspect->updateObjectToDb($strRootNodeId);
        $strSub1Node1Id = $objAspect->getSystemid();
        $objAspect = new SystemAspect();
        $objAspect->updateObjectToDb($strRootNodeId);
        $strSub1Node2Id = $objAspect->getSystemid();
        $objAspect = new SystemAspect();
        $objAspect->updateObjectToDb($strRootNodeId);
        $strSub1Node2Id = $objAspect->getSystemid();


        $objAspect = new SystemAspect();
        $objAspect->updateObjectToDb($strSub1Node1Id);
        $strSub2Node1aId = $objAspect->getSystemid();

        $objAspect = new SystemAspect();
        $objAspect->updateObjectToDb($strSub1Node1Id);
        $strSub2Node1bId = $objAspect->getSystemid();

        $objAspect = new SystemAspect();
        $objAspect->updateObjectToDb($strSub1Node1Id);
        $strSub2Node1cId = $objAspect->getSystemid();


        $this->assertEquals(3, count($objAspect->getChildNodesAsIdArray($strRootNodeId)));
        $this->assertEquals(3, count($objAspect->getChildNodesAsIdArray($strSub1Node1Id)));

        $objAspect = new SystemAspect($strRootNodeId);
        $objAspect->deleteObjectFromDatabase();
        Database::getInstance()->flushQueryCache();


        $this->assertEquals(0, count($objAspect->getChildNodesAsIdArray($strRootNodeId)));
        $this->assertEquals(0, count($objAspect->getChildNodesAsIdArray($strSub1Node1Id)));
    }

    public function testPrevIdHandling()
    {

        $objAspect = new SystemAspect();
        $objAspect->setStrName("autotest");

        $bitThrown = false;
        try {
            $objAspect->updateObjectToDb("invalid");
        } catch (Exception $objEx) {
            $bitThrown = true;
        }
        $this->assertTrue($bitThrown);
        $this->assertTrue($objAspect->getSystemid() == "");
        $this->assertTrue(!validateSystemid($objAspect->getSystemid()));
        $this->assertTrue(!validateSystemid($objAspect->getStrPrevId()));

        $this->assertTrue($objAspect->updateObjectToDb());
        $this->assertTrue($objAspect->getSystemid() != "");
        $this->assertTrue(validateSystemid($objAspect->getSystemid()));
        $this->assertTrue(validateSystemid($objAspect->getStrPrevId()));

    }
}

