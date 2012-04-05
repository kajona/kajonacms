<?php

require_once (__DIR__."/../../module_system/system/class_testbase.php");

class class_test_system extends class_testbase  {


    public function testKernel() {
        $objDB = class_carrier::getInstance()->getObjDB();

        //--- system kernel -------------------------------------------------------------------------------------
        echo "\tsystem-kernel...\n";

        $arrSysRecords = array();
        echo "\tcreating 100 system-records without right-records...\n";
        //nr of records currently
        $arrRow = $objDB->getRow("SELECT COUNT(*) FROM "._dbprefix_."system", 0, false);
        $intNrSystemRecords = $arrRow["COUNT(*)"];
        $arrRow = $objDB->getRow("SELECT COUNT(*) FROM "._dbprefix_."system_right", 0, false);
        $intNrRightsRecords = $arrRow["COUNT(*)"];
        $objSystemCommon = new class_module_system_common();
        $arrSysRecords = array();
        for ($intI = 0; $intI < 100; $intI++) {
            $intSysId = $objSystemCommon->createSystemRecord(0, "autotest", false);
            $arrSysRecords[] = $intSysId;
            $arrRow = $objDB->getRow("SELECT COUNT(*) FROM "._dbprefix_."system", 0, false);
            $this->assertEquals($arrRow["COUNT(*)"], $intI+$intNrSystemRecords+1, __FILE__." checkCreateSysRecordsWithoutRights");
            $arrRow = $objDB->getRow("SELECT COUNT(*) FROM "._dbprefix_."system_right", 0, false);
            $this->assertEquals($arrRow["COUNT(*)"], $intNrRightsRecords, __FILE__." checkCreateSysRecordsWithoutRights");
        }


        echo "\tdeleting 100 system-records without right-records...\n";
        foreach($arrSysRecords as $strOneId) {
            $objSystemCommon->deleteSystemRecord($strOneId);
        }
        $arrRow = $objDB->getRow("SELECT COUNT(*) FROM "._dbprefix_."system", 0, false);
        $this->assertEquals($arrRow["COUNT(*)"], $intNrSystemRecords, __FILE__." checkDeleteSysRecordsWithoutRights");
        $arrRow = $objDB->getRow("SELECT COUNT(*) FROM "._dbprefix_."system_right", 0, false);
        $this->assertEquals($arrRow["COUNT(*)"], $intNrRightsRecords, __FILE__." checkDeleteSysRecordsWithoutRights");


        echo "\tcreating 100 system-records with right-records...\n";
        //nr of records currently
        $arrRow = $objDB->getRow("SELECT COUNT(*) FROM "._dbprefix_."system", 0, false);
        $intNrSystemRecords = $arrRow["COUNT(*)"];
        $arrRow = $objDB->getRow("SELECT COUNT(*) FROM "._dbprefix_."system_right", 0, false);
        $intNrRightsRecords = $arrRow["COUNT(*)"];
        $objSystemCommon = new class_module_system_common();
        $arrSysRecords = array();
        for ($intI = 0; $intI <= 100; $intI++) {
            $intSysId = $objSystemCommon->createSystemRecord(0, "autotest");
            $arrSysRecords[] = $intSysId;
            $arrRow = $objDB->getRow("SELECT COUNT(*) FROM "._dbprefix_."system", 0, false);
            $this->assertEquals($arrRow["COUNT(*)"], $intI+$intNrSystemRecords+1, __FILE__." checkCreateSysRecordsWithRights");
            $arrRow = $objDB->getRow("SELECT COUNT(*) FROM "._dbprefix_."system_right", 0, false);
            $this->assertEquals($arrRow["COUNT(*)"], $intNrRightsRecords+$intI+1, __FILE__." checkCreateSysRecordsWithRights");
        }


        echo "\tdeleting 100 system-records with right-records...\n";
        foreach($arrSysRecords as $strOneId) {
            $objSystemCommon->deleteSystemRecord($strOneId);
        }
        $arrRow = $objDB->getRow("SELECT COUNT(*) FROM "._dbprefix_."system", 0, false);
        $this->assertEquals($arrRow["COUNT(*)"], $intNrSystemRecords, __FILE__." checkDeleteSysRecordsWithRights");
        $arrRow = $objDB->getRow("SELECT COUNT(*) FROM "._dbprefix_."system_right", 0, false);
        $this->assertEquals($arrRow["COUNT(*)"], $intNrRightsRecords, __FILE__." checkDeleteSysRecordsWithRights");

    }


    function testSectionHandling() {

        $objSystemCommon = new class_module_system_common();
        $objDB = class_carrier::getInstance()->getObjDB();

        //test sections
        echo "\tsection-handling of class db...\n";
        //create 10 test records
        $objSystemCommon = new class_module_system_common();
        //new base-node
        $strBaseNodeId = $objSystemCommon->createSystemRecord(0, "sectionTest");
        $arrNodes = array();
        for($intI = 1; $intI <= 10; $intI++) {
            $arrNodes[] = $objSystemCommon->createSystemRecord($strBaseNodeId, "sectionTest_"+$intI);
        }
        $arrNodes = $objDB->getPArray("SELECT system_id FROM "._dbprefix_."system WHERE system_prev_id = ? ORDER BY system_sort ASC", array($strBaseNodeId));
        $arrNodesSection = $objDB->getPArray("SELECT system_id FROM "._dbprefix_."system WHERE system_prev_id = ? ORDER BY system_sort ASC", array($strBaseNodeId),  2, 4, false);
        $this->assertEquals($arrNodesSection[0]["system_id"], $arrNodes[2]["system_id"], __FILE__." checkSectionLoading");
        $this->assertEquals($arrNodesSection[1]["system_id"], $arrNodes[3]["system_id"], __FILE__." checkSectionLoading");
        $this->assertEquals($arrNodesSection[2]["system_id"], $arrNodes[4]["system_id"], __FILE__." checkSectionLoading");

        //deleting all records created
        foreach ($arrNodes as $arrOneNode)
            $objSystemCommon->deleteSystemRecord($arrOneNode["system_id"]);
        $objSystemCommon->deleteSystemRecord($strBaseNodeId);
    }


    function testTreeBehaviour() {

        $objSystemCommon = new class_module_system_common();
        $objDB = class_carrier::getInstance()->getObjDB();
        echo "\ttesting tree-behaviour...\n";
        //nr of records currently
        $arrSysRecords = array();
        $arrRow = $objDB->getPRow("SELECT COUNT(*) FROM "._dbprefix_."system", array(), 0, false);
        $intNrSystemRecords = $arrRow["COUNT(*)"];
        $arrRow = $objDB->getPRow("SELECT COUNT(*) FROM "._dbprefix_."system_right", array(), 0, false);
        //base-id
        echo "\tcreating root node...\n";
        $intBaseId = $objSystemCommon->createSystemRecord(0, "autotest");
        //two under the base
        echo "\tcreating child nodes...\n";
        $intSecOneId = $objSystemCommon->createSystemRecord($intBaseId, "autotest");
        $intSecTwoId = $objSystemCommon->createSystemRecord($intBaseId, "autotest");
        $arrSysRecords[] = $intBaseId;
        $arrSysRecords[] = $intSecOneId;
        $arrSysRecords[] = $intSecTwoId;
        //twenty under both levels
        for ($intI = 0; $intI < 20; $intI++) {
            $arrSysRecords[] = $objSystemCommon->createSystemRecord($intSecOneId, "autotest");
            $arrSysRecords[] = $objSystemCommon->createSystemRecord($intSecTwoId, "autotest");
            $arrSysRecords[] = $objSystemCommon->createSystemRecord($intBaseId, "autotest");
        }
        //check nr of records
        $intCount = $objSystemCommon->getNumberOfSiblings($intSecOneId);
        $this->assertEquals($intCount, 22, __FILE__." checkNrOfSiblingsInTree");
        //check nr of childs
        $arrRow = $objDB->getPRow("SELECT COUNT(*) FROM "._dbprefix_."system WHERE system_prev_id = ?", array($intBaseId));
        $this->assertEquals($arrRow["COUNT(*)"], 22, __FILE__." checkNrOfChildsInTree1");
        $arrRow = $objDB->getPRow("SELECT COUNT(*) FROM "._dbprefix_."system WHERE system_prev_id = ?", array($intSecOneId));
        $this->assertEquals($arrRow["COUNT(*)"], 20, __FILE__." checkNrOfChildsInTree2");
        $arrRow = $objDB->getPRow("SELECT COUNT(*) FROM "._dbprefix_."system WHERE system_prev_id = ?", array($intSecTwoId));
        $this->assertEquals($arrRow["COUNT(*)"], 20, __FILE__." checkNrOfChildsInTree3");

        //deleting all records
        echo "\tdeleting nodes...\n";
        foreach($arrSysRecords as $strOneId) {
            $objSystemCommon->deleteSystemRecord($strOneId);
        }



        //test the setToPos
        echo "\tposition handling...\n";
        //create 10 test records
        $objSystemCommon = new class_module_system_common();
        //new base-node
        $strBaseNodeId = $objSystemCommon->createSystemRecord(0, "positionShiftTest");
        $arrNodes = array();
        for($intI = 1; $intI <= 10; $intI++) {
            $arrNodes[] = $objSystemCommon->createSystemRecord($strBaseNodeId, "positionShiftTest_"+$intI);
        }

        //initial movings
        $objSystemCommon = new class_module_system_common($arrNodes[1]);
        $objSystemCommon->setPosition("upwards");
        $arrNodes = $objDB->getPArray("SELECT system_id FROM "._dbprefix_."system WHERE system_prev_id = ? ORDER BY system_sort ASC", array($strBaseNodeId));
        echo "\trelative shiftings...\n";
        //move the third to the first pos
        $objSystemCommon = new class_module_system_common($arrNodes[2]["system_id"]);
        $objSystemCommon->setPosition("upwards");
        $objSystemCommon->setPosition("upwards");
        $objSystemCommon->setPosition("upwards");
        //next one should be with no effect
        $objSystemCommon->setPosition("upwards");
        $objDB->flushQueryCache();
        $arrNodesAfter = $objDB->getPArray("SELECT system_id FROM "._dbprefix_."system WHERE system_prev_id = ? ORDER BY system_sort ASC", array($strBaseNodeId));

        $this->assertEquals($arrNodesAfter[0]["system_id"], $arrNodes[2]["system_id"], __FILE__." checkPositionShitftingByRelativeShift");
        $this->assertEquals($arrNodesAfter[1]["system_id"], $arrNodes[0]["system_id"], __FILE__." checkPositionShitftingByRelativeShift");
        $this->assertEquals($arrNodesAfter[2]["system_id"], $arrNodes[1]["system_id"], __FILE__." checkPositionShitftingByRelativeShift");

        //moving by set pos
        echo "\tabsolute shifting..\n";
        $objDB->flushQueryCache();
        $arrNodes = $objDB->getPArray("SELECT system_id FROM "._dbprefix_."system WHERE system_prev_id = ? ORDER BY system_sort ASC", array($strBaseNodeId));
        $objDB->flushQueryCache();
        $objSystemCommon = new class_module_system_common($arrNodes[2]["system_id"]);
        $objSystemCommon->setAbsolutePosition(1);
        $arrNodesAfter = $objDB->getPArray("SELECT system_id FROM "._dbprefix_."system WHERE system_prev_id = ? ORDER BY system_sort ASC", array($strBaseNodeId));
        $this->assertEquals($arrNodesAfter[0]["system_id"], $arrNodes[2]["system_id"], __FILE__." checkPositionShitftingByAbsoluteShift");
        $this->assertEquals($arrNodesAfter[1]["system_id"], $arrNodes[0]["system_id"], __FILE__." checkPositionShitftingByAbsoluteShift");
        $this->assertEquals($arrNodesAfter[2]["system_id"], $arrNodes[1]["system_id"], __FILE__." checkPositionShitftingByAbsoluteShift");
        //and back...
        $objDB->flushQueryCache();
        $objSystemCommon = new class_module_system_common($arrNodes[2]["system_id"]);
        $objSystemCommon->setAbsolutePosition(3);
        $objDB->flushQueryCache();
        $arrNodesAfter = $objDB->getPArray("SELECT system_id FROM "._dbprefix_."system WHERE system_prev_id = ? ORDER BY system_sort ASC", array($strBaseNodeId));
        $this->assertEquals($arrNodesAfter[0]["system_id"], $arrNodes[0]["system_id"], __FILE__." checkPositionShitftingByAbsoluteShift");
        $this->assertEquals($arrNodesAfter[1]["system_id"], $arrNodes[1]["system_id"], __FILE__." checkPositionShitftingByAbsoluteShift");
        $this->assertEquals($arrNodesAfter[2]["system_id"], $arrNodes[2]["system_id"], __FILE__." checkPositionShitftingByAbsoluteShift");

        //deleting all records created
        foreach ($arrNodes as $arrOneNode)
            $objSystemCommon->deleteSystemRecord($arrOneNode["system_id"]);
        $objSystemCommon->deleteSystemRecord($strBaseNodeId);
    }



    public function testCopySysrecord() {
        $objDB = class_carrier::getInstance()->getObjDB();

        $objModule = new class_module_system_module();
        $objModule->setStrName("autotest module");
        $objModule->setIntNr(99129293);

        $objModule->updateObjectToDb("0");

        $strModuleId = $objModule->getSystemid();

        $arrSourceRow = $objDB->getPRow("SELECT * FROM "._dbprefix_."system WHERE system_id = ?", array($strModuleId));

        $objCommon = new class_module_system_common($strModuleId);

        $strNewId = generateSystemid();
        $objCommon->copyCurrentSystemrecord($strNewId);

        $arrCopiedRow = $objDB->getPRow("SELECT * FROM "._dbprefix_."system WHERE system_id = ?", array($strNewId));

        foreach($arrSourceRow as $strKey => $strValue) {
            if(!in_array($strKey, array("system_id", "system_sort")) && !is_numeric($strKey))
                $this->assertEquals($strValue, $arrCopiedRow[$strKey], __FILE__." key ".$strKey." copy sysrecord");
        }

        $objModule->deleteObject();

        $objCommon->deleteSystemRecord($strNewId);

    }

    public function testTreeDelete() {
        $objCommon = new class_module_system_common("0");

        $strRootNodeId = $objCommon->createSystemRecord("0", "autotest");
        $strSub1Node1Id = $objCommon->createSystemRecord($strRootNodeId, "autotest");
        $strSub1Node2Id = $objCommon->createSystemRecord($strRootNodeId, "autotest");
        $strSub1Node2Id = $objCommon->createSystemRecord($strRootNodeId, "autotest");

        $strSub2Node1aId = $objCommon->createSystemRecord($strSub1Node1Id, "autotest");
        $strSub2Node1bId = $objCommon->createSystemRecord($strSub1Node1Id, "autotest");
        $strSub2Node1cId = $objCommon->createSystemRecord($strSub1Node1Id, "autotest");

        $this->assertEquals(3, count($objCommon->getChildNodesAsIdArray($strRootNodeId)));

        $this->assertEquals(3, count($objCommon->getChildNodesAsIdArray($strSub1Node1Id)));

        $objCommon->setSystemid($strRootNodeId);
        $objCommon->deleteObject();


        $this->assertEquals(0, count($objCommon->getChildNodesAsIdArray($strRootNodeId)));

        $this->assertEquals(0, count($objCommon->getChildNodesAsIdArray($strSub1Node1Id)));
    }
}

