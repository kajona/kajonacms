<?php

require_once (__DIR__."/../../module_system/system/class_testbase.php");

class class_test_rights extends class_testbase {

    private $objRights ;
    private $strUserId;


    public function testInheritance() {
        echo "\tRIGHTS INHERITANCE...\n";
        $objDB = class_carrier::getInstance()->getObjDB();
        $objRights = class_carrier::getInstance()->getObjRights();
        $this->objRights = class_carrier::getInstance()->getObjRights();
        $objSystemCommon = new class_module_system_common();



        //create a new user & group to be used during testing
        echo "\tcreating a test user\n";
        $objUser = new class_module_user_user();
        //$objUser->setStrEmail(generateSystemid()."@".generateSystemid()."de");
        $strUsername = "user_".generateSystemid();
        $objUser->setStrUsername($strUsername);
        $objUser->updateObjectToDb();
        echo "\tid of user: ".$objUser->getSystemid()."\n";
        $this->strUserId = $objUser->getSystemid();

        echo "\tcreating a test group\n";
        $objGroup = new class_module_user_group();
        $strName = "name_".generateSystemid();
        $objGroup->setStrName($strName);
        $objGroup->updateObjectToDb();
        echo "\tid of group: ".$objGroup->getSystemid()."\n";

        echo "\tadding user to group\n";
        $objGroup->getObjSourceGroup()->addMember($objUser->getObjSourceUser());

        echo "\tcreating node-tree\n";
        $strRootId = $objSystemCommon->createSystemRecord(0, "autotest 0");
        echo "\tid of root-node: ".$strRootId."\n";
        echo "\tcreating child nodes...\n";
        $strSecOne = $objSystemCommon->createSystemRecord($strRootId, "autotest 01");
        $strSecTwo = $objSystemCommon->createSystemRecord($strRootId, "autotest 02");

        $strThirdOne1 = $objSystemCommon->createSystemRecord($strSecOne, "autotest 011");
        $strThirdOne2 = $objSystemCommon->createSystemRecord($strSecOne, "autotest 012");
        $strThirdTwo1 = $objSystemCommon->createSystemRecord($strSecTwo, "autotest 021");
        $strThirdTwo2 = $objSystemCommon->createSystemRecord($strSecTwo, "autotest 022");

        $strThird111 = $objSystemCommon->createSystemRecord($strThirdOne1, "autotest 0111");
        $strThird112 = $objSystemCommon->createSystemRecord($strThirdOne1, "autotest 0112");
        $strThird121 = $objSystemCommon->createSystemRecord($strThirdOne2, "autotest 0121");
        $strThird122 = $objSystemCommon->createSystemRecord($strThirdOne2, "autotest 0122");
        $strThird211 = $objSystemCommon->createSystemRecord($strThirdTwo1, "autotest 0211");
        $strThird212 = $objSystemCommon->createSystemRecord($strThirdTwo1, "autotest 0212");
        $strThird221 = $objSystemCommon->createSystemRecord($strThirdTwo2, "autotest 0221");
        $strThird222 = $objSystemCommon->createSystemRecord($strThirdTwo2, "autotest 0222");
        $arrThirdLevelNodes = array($strThird111, $strThird112, $strThird121, $strThird122, $strThird211, $strThird212, $strThird221, $strThird222);



        echo "\tadding group with right view & edit\n";
        $objRights->addGroupToRight($objGroup->getSystemid(), $strRootId, "view");
        $objRights->addGroupToRight($objGroup->getSystemid(), $strRootId, "edit");

        $objDB->flushQueryCache();

        echo "\tchecking leaf nodes for inherited rights\n";
        foreach($arrThirdLevelNodes as $strOneRootNode) {
            $this->checkNodeRights($strOneRootNode, true, true);
        }

//        echo "\n\n\n";
//        $this->printTree($strRootId, 1);
//        echo "\n\n\n";

        echo "\tremoving right view from node secTwo\n";
        $objRights->removeGroupFromRight($objGroup->getSystemid(), $strSecTwo, "view");
        echo "\tchecking node rights\n";
        $this->checkNodeRights($strRootId, true, true);
        $this->checkNodeRights($strSecOne, true, true);
        $this->checkNodeRights($strSecTwo, false, true);
        $this->checkNodeRights($strThirdOne1, true, true);
        $this->checkNodeRights($strThirdOne2, true, true);
        $this->checkNodeRights($strThirdTwo1, false, true);
        $this->checkNodeRights($strThirdTwo2, false, true);
        $this->checkNodeRights($strThird111, true, true);
        $this->checkNodeRights($strThird112, true, true);
        $this->checkNodeRights($strThird121, true, true);
        $this->checkNodeRights($strThird122, true, true);
        $this->checkNodeRights($strThird211, false, true);
        $this->checkNodeRights($strThird212, false, true);
        $this->checkNodeRights($strThird221, false, true);
        $this->checkNodeRights($strThird222, false, true);



        echo "\tmove SecOne as child to 221\n";
        $objTempCommons = new class_module_system_common($strSecOne);
        $objTempCommons->setStrPrevId($strThird221);
        $objTempCommons->updateObjectToDb();
        //$objSystemCommon->setPrevId($strThird221, $strSecOne);
        echo "\tchecking node rights\n";
        $this->checkNodeRights($strRootId, true, true);
        $this->checkNodeRights($strSecOne, false, true);
        $this->checkNodeRights($strSecTwo, false, true);
        $this->checkNodeRights($strThirdOne1, false, true);
        $this->checkNodeRights($strThirdOne2, false, true);
        $this->checkNodeRights($strThirdTwo1, false, true);
        $this->checkNodeRights($strThirdTwo2, false, true);
        $this->checkNodeRights($strThird111, false, true);
        $this->checkNodeRights($strThird112, false, true);
        $this->checkNodeRights($strThird121, false, true);
        $this->checkNodeRights($strThird122, false, true);
        $this->checkNodeRights($strThird211, false, true);
        $this->checkNodeRights($strThird212, false, true);
        $this->checkNodeRights($strThird221, false, true);
        $this->checkNodeRights($strThird222, false, true);



        echo "\tsetting rights of third21 to only view\n";
        $objRights->removeGroupFromRight($objGroup->getSystemid(), $strThirdTwo1, "edit");
        $objRights->addGroupToRight($objGroup->getSystemid(), $strThirdTwo1, "view");
        echo "\tchecking node rights\n";
        $this->checkNodeRights($strRootId, true, true);
        $this->checkNodeRights($strSecOne, false, true);
        $this->checkNodeRights($strSecTwo, false, true);
        $this->checkNodeRights($strThirdOne1, false, true);
        $this->checkNodeRights($strThirdOne2, false, true);
        $this->checkNodeRights($strThirdTwo1, true);
        $this->checkNodeRights($strThirdTwo2, false, true);
        $this->checkNodeRights($strThird111, false, true);
        $this->checkNodeRights($strThird112, false, true);
        $this->checkNodeRights($strThird121, false, true);
        $this->checkNodeRights($strThird122, false, true);
        $this->checkNodeRights($strThird211, true);
        $this->checkNodeRights($strThird212, true);
        $this->checkNodeRights($strThird221, false, true);
        $this->checkNodeRights($strThird222, false, true);



        echo "\tsetting 211 as parent node for third11\n";
        $objTempCommons = new class_module_system_common($strThirdOne1);
        $objTempCommons->setStrPrevId($strThird211);
        $objTempCommons->updateObjectToDb();
        //$objSystemCommon->setPrevId($strThird211, $strThirdOne1);
        echo "\tchecking node rights\n";
        $this->checkNodeRights($strRootId, true, true);
        $this->checkNodeRights($strSecOne, false, true);
        $this->checkNodeRights($strSecTwo, false, true);
        $this->checkNodeRights($strThirdOne1, true);
        $this->checkNodeRights($strThirdOne2, false, true);
        $this->checkNodeRights($strThirdTwo1, true);
        $this->checkNodeRights($strThirdTwo2, false, true);
        $this->checkNodeRights($strThird111, true);
        $this->checkNodeRights($strThird112, true);
        $this->checkNodeRights($strThird121, false, true);
        $this->checkNodeRights($strThird122, false, true);
        $this->checkNodeRights($strThird211, true);
        $this->checkNodeRights($strThird212, true);
        $this->checkNodeRights($strThird221, false, true);
        $this->checkNodeRights($strThird222, false, true);

//        echo "\n\n\n";
//        $this->printTree($strRootId, 1);
//        echo "\n\n\n";


        echo "\trebuilding initial tree structure\n";
        $objTempCommons = new class_module_system_common($strSecOne);
        $objTempCommons->setStrPrevId($strRootId);
        $objTempCommons->updateObjectToDb();
        //$objSystemCommon->setPrevId($strRootId, $strSecOne); //SecOne still inheriting
        $objTempCommons = new class_module_system_common($strThirdOne1);
        $objTempCommons->setStrPrevId($strSecOne);
        $objTempCommons->updateObjectToDb();
        //$objSystemCommon->setPrevId($strSecOne, $strThirdOne1);
        $objRights->setInherited(true, $strThirdOne1);
        echo "\tchecking node rights\n";
        $this->checkNodeRights($strRootId, true, true);
        $this->checkNodeRights($strSecOne, true, true);
        $this->checkNodeRights($strSecTwo, false, true);
        $this->checkNodeRights($strThirdOne1, true, true);
        $this->checkNodeRights($strThirdOne2, true, true);
        $this->checkNodeRights($strThirdTwo1, true);
        $this->checkNodeRights($strThirdTwo2, false, true);
        $this->checkNodeRights($strThird111, true, true);
        $this->checkNodeRights($strThird112, true, true);
        $this->checkNodeRights($strThird121, true, true);
        $this->checkNodeRights($strThird122, true, true);
        $this->checkNodeRights($strThird211, true);
        $this->checkNodeRights($strThird212, true);
        $this->checkNodeRights($strThird221, false, true);
        $this->checkNodeRights($strThird222, false, true);

//        echo "\n\n\n";
//        $this->printTree($strRootId, 1);
//        echo "\n\n\n";

        echo "\trebuilding initial inheritance structure\n";
        $objRights->setInherited(true, $strSecTwo);
        $objRights->setInherited(true, $strThirdTwo1);
        echo "\tchecking node rights\n";
        $this->checkNodeRights($strRootId, true, true);
        $this->checkNodeRights($strSecOne, true, true);
        $this->checkNodeRights($strSecTwo, true, true);
        $this->checkNodeRights($strThirdOne1, true, true);
        $this->checkNodeRights($strThirdOne2, true, true);
        $this->checkNodeRights($strThirdTwo1, true, true);
        $this->checkNodeRights($strThirdTwo2, true, true);
        $this->checkNodeRights($strThird111, true, true);
        $this->checkNodeRights($strThird112, true, true);
        $this->checkNodeRights($strThird121, true, true);
        $this->checkNodeRights($strThird122, true, true);
        $this->checkNodeRights($strThird211, true, true);
        $this->checkNodeRights($strThird212, true, true);
        $this->checkNodeRights($strThird221, true, true);
        $this->checkNodeRights($strThird222, true, true);




//        echo "\n\n\n";
//        $this->printTree($strRootId, 1);
//        echo "\n\n\n";

        echo "\tdeleting systemnodes\n";

        $objSystemCommon->deleteSystemRecord($strThird111);
        $objSystemCommon->deleteSystemRecord($strThird112);
        $objSystemCommon->deleteSystemRecord($strThird121);
        $objSystemCommon->deleteSystemRecord($strThird122);
        $objSystemCommon->deleteSystemRecord($strThird211);
        $objSystemCommon->deleteSystemRecord($strThird212);
        $objSystemCommon->deleteSystemRecord($strThird221);
        $objSystemCommon->deleteSystemRecord($strThird222);

        $objSystemCommon->deleteSystemRecord($strThirdOne1);
        $objSystemCommon->deleteSystemRecord($strThirdOne2);
        $objSystemCommon->deleteSystemRecord($strThirdTwo1);
        $objSystemCommon->deleteSystemRecord($strThirdTwo2);

        $objSystemCommon->deleteSystemRecord($strSecOne);
        $objSystemCommon->deleteSystemRecord($strSecTwo);

        $objSystemCommon->deleteSystemRecord($strRootId);

        echo "\tdeleting the test user\n";
        $objUser->deleteObject();
        echo "\tdeleting the test group\n";
        $objGroup->deleteObject();

    }



    private function checkNodeRights($strNodeId,
                                     $bitView = false,
                                     $bitEdit = false,
                                     $bitDelete = false,
                                     $bitRights = false,
                                     $bitRight1 = false,
                                     $bitRight2 = false,
                                     $bitRight3 = false,
                                     $bitRight4 = false,
                                     $bitRight5 = false) {

        $this->assertEquals($bitView, $this->objRights->rightView($strNodeId, $this->strUserId), __FILE__." checkNodeRights View ".$strNodeId);
        $this->assertEquals($bitEdit, $this->objRights->rightEdit($strNodeId, $this->strUserId), __FILE__." checkNodeRights Edit ".$strNodeId);
        $this->assertEquals($bitDelete, $this->objRights->rightDelete($strNodeId, $this->strUserId), __FILE__." checkNodeRights Delete ".$strNodeId);
        $this->assertEquals($bitRights, $this->objRights->rightRight($strNodeId, $this->strUserId), __FILE__." checkNodeRights Rights".$strNodeId);
        $this->assertEquals($bitRight1, $this->objRights->rightRight1($strNodeId, $this->strUserId), __FILE__." checkNodeRights Right1".$strNodeId);
        $this->assertEquals($bitRight2, $this->objRights->rightRight2($strNodeId, $this->strUserId), __FILE__." checkNodeRights Right2".$strNodeId);
        $this->assertEquals($bitRight3, $this->objRights->rightRight3($strNodeId, $this->strUserId), __FILE__." checkNodeRights Right3".$strNodeId);
        $this->assertEquals($bitRight4, $this->objRights->rightRight4($strNodeId, $this->strUserId), __FILE__." checkNodeRights Right4".$strNodeId);
        $this->assertEquals($bitRight5, $this->objRights->rightRight5($strNodeId, $this->strUserId), __FILE__." checkNodeRights Right5".$strNodeId);

    }

    private function printTree($strRootNode, $intLevel) {
        for($i=0; $i<$intLevel; $i++)
            echo "   ";

        $objCommon = new class_module_system_common($strRootNode);
        //var_dump($objCommon->getSystemRecord());
        echo $objCommon->getRecordComment()." / (v: ".$this->objRights->rightView($strRootNode, $this->strUserId)." e: ".$this->objRights->rightEdit($strRootNode, $this->strUserId).") /  ".$objCommon->getSystemid()."\n";

        //var_dump($objCommon->getChildNodesAsIdArray());
        foreach($objCommon->getChildNodesAsIdArray() as $strOneId)
            $this->printTree($strOneId, $intLevel+1);
    }




}

