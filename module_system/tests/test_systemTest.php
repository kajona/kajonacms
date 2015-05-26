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
        $arrRow = $objDB->getPRow("SELECT COUNT(*) FROM "._dbprefix_."system", array(), 0, false);
        $intNrSystemRecords = $arrRow["COUNT(*)"];
        $arrRow = $objDB->getPRow("SELECT COUNT(*) FROM "._dbprefix_."system_right", array(), 0, false);
        $intNrRightsRecords = $arrRow["COUNT(*)"];
        $objSystemCommon = new class_module_system_common();
        $arrSysRecords = array();
        for ($intI = 0; $intI < 100; $intI++) {
            $intSysId = $objSystemCommon->createSystemRecord(0, "autotest", false);
            $arrSysRecords[] = $intSysId;
            $arrRow = $objDB->getPRow("SELECT COUNT(*) FROM "._dbprefix_."system", array(), 0, false);
            $this->assertEquals($arrRow["COUNT(*)"], $intI+$intNrSystemRecords+1, __FILE__." checkCreateSysRecordsWithoutRights");
            $arrRow = $objDB->getPRow("SELECT COUNT(*) FROM "._dbprefix_."system_right", array(), 0, false);
            $this->assertEquals($arrRow["COUNT(*)"], $intNrRightsRecords, __FILE__." checkCreateSysRecordsWithoutRights");
        }


        echo "\tdeleting 100 system-records without right-records...\n";
        foreach($arrSysRecords as $strOneId) {
            $objSystemCommon->deleteSystemRecord($strOneId);
        }
        $arrRow = $objDB->getPRow("SELECT COUNT(*) FROM "._dbprefix_."system", array(), 0, false);
        $this->assertEquals($arrRow["COUNT(*)"], $intNrSystemRecords, __FILE__." checkDeleteSysRecordsWithoutRights");
        $arrRow = $objDB->getPRow("SELECT COUNT(*) FROM "._dbprefix_."system_right", array(), 0, false);
        $this->assertEquals($arrRow["COUNT(*)"], $intNrRightsRecords, __FILE__." checkDeleteSysRecordsWithoutRights");


        echo "\tcreating 100 system-records with right-records...\n";
        //nr of records currently
        $arrRow = $objDB->getPRow("SELECT COUNT(*) FROM "._dbprefix_."system", array(), 0, false);
        $intNrSystemRecords = $arrRow["COUNT(*)"];
        $arrRow = $objDB->getPRow("SELECT COUNT(*) FROM "._dbprefix_."system_right", array(), 0, false);
        $intNrRightsRecords = $arrRow["COUNT(*)"];
        $objSystemCommon = new class_module_system_common();
        $arrSysRecords = array();
        for ($intI = 0; $intI <= 100; $intI++) {
            $intSysId = $objSystemCommon->createSystemRecord(0, "autotest");
            $arrSysRecords[] = $intSysId;
            $arrRow = $objDB->getPRow("SELECT COUNT(*) FROM "._dbprefix_."system", array(), 0, false);
            $this->assertEquals($arrRow["COUNT(*)"], $intI+$intNrSystemRecords+1, __FILE__." checkCreateSysRecordsWithRights");
            $arrRow = $objDB->getPRow("SELECT COUNT(*) FROM "._dbprefix_."system_right", array(), 0, false);
            $this->assertEquals($arrRow["COUNT(*)"], $intNrRightsRecords+$intI+1, __FILE__." checkCreateSysRecordsWithRights");
        }


        echo "\tdeleting 100 system-records with right-records...\n";
        foreach($arrSysRecords as $strOneId) {
            $objSystemCommon->deleteSystemRecord($strOneId);
        }
        $arrRow = $objDB->getPRow("SELECT COUNT(*) FROM "._dbprefix_."system", array(), 0, false);
        $this->assertEquals($arrRow["COUNT(*)"], $intNrSystemRecords, __FILE__." checkDeleteSysRecordsWithRights");
        $arrRow = $objDB->getPRow("SELECT COUNT(*) FROM "._dbprefix_."system_right", array(), 0, false);
        $this->assertEquals($arrRow["COUNT(*)"], $intNrRightsRecords, __FILE__." checkDeleteSysRecordsWithRights");

    }


    function testSectionHandling() {

        $objDB = class_carrier::getInstance()->getObjDB();

        //test sections
        echo "\tsection-handling of class db...\n";
        //create 10 test records
        $objSystemCommon = new class_module_system_common();
        //new base-node
        $strBaseNodeId = $objSystemCommon->createSystemRecord(0, "sectionTest");
        $arrNodes = array();
        for($intI = 1; $intI <= 10; $intI++) {
            $arrNodes[] = $objSystemCommon->createSystemRecord($strBaseNodeId, "sectionTest_".$intI);
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

    public function testPrevIdHandling() {

        $objAspect = new class_module_system_aspect();
        $objAspect->setStrName("autotest");

        $bitThrown = false;
        try {
            $objAspect->updateObjectToDb("invalid");
        }
        catch(class_exception $objEx) {
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

