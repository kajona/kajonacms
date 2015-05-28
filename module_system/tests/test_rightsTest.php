<?php

require_once (__DIR__."/../../module_system/system/class_testbase.php");

class class_test_rights extends class_testbase {

    /**
     * @var class_rights
     */
    private $objRights ;
    private $strUserId;


    public function testInheritance() {
        echo "\tRIGHTS INHERITANCE...\n";
        $objRights = class_carrier::getInstance()->getObjRights();
        $this->objRights = class_carrier::getInstance()->getObjRights();


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
        $objAspect = new class_module_system_aspect(); $objAspect->setStrName("autotest 0");
        $objAspect->updateObjectToDb();
        $strRootId = $objAspect->getSystemid();
        echo "\tid of root-node: ".$strRootId."\n";
        echo "\tcreating child nodes...\n";
        $objAspect = new class_module_system_aspect(); $objAspect->setStrName("autotest 01");
        $objAspect->updateObjectToDb($strRootId);
        $strSecOne = $objAspect->getSystemid();
        $objAspect = new class_module_system_aspect(); $objAspect->setStrName("autotest 02");
        $objAspect->updateObjectToDb($strRootId);
        $strSecTwo = $objAspect->getSystemid();

        $objAspect = new class_module_system_aspect(); $objAspect->setStrName("autotest 011");
        $objAspect->updateObjectToDb($strSecOne);
        $strThirdOne1 = $objAspect->getSystemid();
        $objAspect = new class_module_system_aspect(); $objAspect->setStrName("autotest 012");
         $objAspect->updateObjectToDb($strSecOne);
        $strThirdOne2 = $objAspect->getSystemid();
        $objAspect = new class_module_system_aspect(); $objAspect->setStrName("autotest 021");
        $objAspect->updateObjectToDb($strSecTwo);
        $strThirdTwo1 = $objAspect->getSystemid();
        $objAspect = new class_module_system_aspect(); $objAspect->setStrName("autotest 022");
        $objAspect->updateObjectToDb($strSecTwo);
        $strThirdTwo2 = $objAspect->getSystemid();

        $objAspect = new class_module_system_aspect(); $objAspect->setStrName("autotest 0111");
        $objAspect->updateObjectToDb($strThirdOne1);
        $strThird111 = $objAspect->getSystemid();
        $objAspect = new class_module_system_aspect(); $objAspect->setStrName("autotest 0112");
        $objAspect->updateObjectToDb($strThirdOne1);
        $strThird112 = $objAspect->getSystemid();
        $objAspect = new class_module_system_aspect(); $objAspect->setStrName("autotest 0121");
        $objAspect->updateObjectToDb($strThirdOne2);
        $strThird121 = $objAspect->getSystemid();
        $objAspect = new class_module_system_aspect(); $objAspect->setStrName("autotest 0122");
        $objAspect->updateObjectToDb($strThirdOne2);
        $strThird122 = $objAspect->getSystemid();
        $objAspect = new class_module_system_aspect(); $objAspect->setStrName("autotest 0211");
        $objAspect->updateObjectToDb($strThirdTwo1);
        $strThird211 = $objAspect->getSystemid();
        $objAspect = new class_module_system_aspect(); $objAspect->setStrName("autotest 0212");
        $objAspect->updateObjectToDb($strThirdTwo1);
        $strThird212 = $objAspect->getSystemid();
        $objAspect = new class_module_system_aspect(); $objAspect->setStrName("autotest 0221");
        $objAspect->updateObjectToDb($strThirdTwo2);
        $strThird221 = $objAspect->getSystemid();
        $objAspect = new class_module_system_aspect(); $objAspect->setStrName("autotest 0222");
        $objAspect->updateObjectToDb($strThirdTwo2);
        $strThird222 = $objAspect->getSystemid();
        $arrThirdLevelNodes = array($strThird111, $strThird112, $strThird121, $strThird122, $strThird211, $strThird212, $strThird221, $strThird222);


        echo "\tchecking leaf nodes for initial rights\n";
//        class_carrier::getInstance()->flushCache(class_carrier::INT_CACHE_TYPE_DBQUERIES | class_carrier::INT_CACHE_TYPE_ORMCACHE);
        foreach($arrThirdLevelNodes as $strOneRootNode) {
            $this->checkNodeRights($strOneRootNode, false, false);
        }

        echo "\tadding group with right view & edit\n";
//        class_carrier::getInstance()->flushCache(class_carrier::INT_CACHE_TYPE_DBQUERIES | class_carrier::INT_CACHE_TYPE_ORMCACHE);
        $objRights->addGroupToRight($objGroup->getSystemid(), $strRootId, "view");
        $objRights->addGroupToRight($objGroup->getSystemid(), $strRootId, "edit");


        echo "\tchecking leaf nodes for inherited rights\n";
//        class_carrier::getInstance()->flushCache(class_carrier::INT_CACHE_TYPE_DBQUERIES | class_carrier::INT_CACHE_TYPE_ORMCACHE);
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
        $objTempCommons = new class_module_system_aspect($strSecOne);
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
        $objTempCommons = new class_module_system_aspect($strThirdOne1);
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
        $objTempCommons = new class_module_system_aspect($strSecOne);
        $objTempCommons->setStrPrevId($strRootId);
        $objTempCommons->updateObjectToDb();
        //$objSystemCommon->setPrevId($strRootId, $strSecOne); //SecOne still inheriting
        $objTempCommons = new class_module_system_aspect($strThirdOne1);
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

        $objAspect->deleteObjectFromDatabase($strThird111);
        $objAspect->deleteObjectFromDatabase($strThird112);
        $objAspect->deleteObjectFromDatabase($strThird121);
        $objAspect->deleteObjectFromDatabase($strThird122);
        $objAspect->deleteObjectFromDatabase($strThird211);
        $objAspect->deleteObjectFromDatabase($strThird212);
        $objAspect->deleteObjectFromDatabase($strThird221);
        $objAspect->deleteObjectFromDatabase($strThird222);

        $objAspect->deleteObjectFromDatabase($strThirdOne1);
        $objAspect->deleteObjectFromDatabase($strThirdOne2);
        $objAspect->deleteObjectFromDatabase($strThirdTwo1);
        $objAspect->deleteObjectFromDatabase($strThirdTwo2);

        $objAspect->deleteObjectFromDatabase($strSecOne);
        $objAspect->deleteObjectFromDatabase($strSecTwo);

        $objAspect->deleteObjectFromDatabase($strRootId);

        echo "\tdeleting the test user\n";
        $objUser->deleteObjectFromDatabase();
        echo "\tdeleting the test group\n";
        $objGroup->deleteObjectFromDatabase();

    }




    public function testAddGroupToPermission() {
        $objAspect = new class_module_system_aspect();
        $objAspect->setStrName("democase");
        $objAspect->updateObjectToDb();

        $strGroupId = generateSystemid();

        //fill caches
        class_module_system_aspect::getObjectList();

        $arrRow = class_carrier::getInstance()->getObjDB()->getPRow("SELECT * FROM "._dbprefix_."system_right WHERE right_id = ?", array($objAspect->getSystemid()), 0, false);
        $this->assertTrue(!in_array($strGroupId, explode(",", $arrRow["right_view"])));
        $this->assertTrue(!class_carrier::getInstance()->getObjRights()->checkPermissionForGroup($strGroupId, class_rights::$STR_RIGHT_VIEW, $objAspect->getSystemid()));

        class_carrier::getInstance()->getObjRights()->addGroupToRight($strGroupId, $objAspect->getSystemid(), class_rights::$STR_RIGHT_VIEW);

        $arrRow = class_carrier::getInstance()->getObjDB()->getPRow("SELECT * FROM "._dbprefix_."system_right WHERE right_id = ?", array($objAspect->getSystemid()), 0, false);
        $this->assertTrue(in_array($strGroupId, explode(",", $arrRow["right_view"])));
        $this->assertTrue(class_carrier::getInstance()->getObjRights()->checkPermissionForGroup($strGroupId, class_rights::$STR_RIGHT_VIEW, $objAspect->getSystemid()));

        class_module_system_aspect::getObjectList();

        $arrRow = class_carrier::getInstance()->getObjDB()->getPRow("SELECT * FROM "._dbprefix_."system_right WHERE right_id = ?", array($objAspect->getSystemid()), 0, false);
        $this->assertTrue(in_array($strGroupId, explode(",", $arrRow["right_view"])));
        $this->assertTrue(class_carrier::getInstance()->getObjRights()->checkPermissionForGroup($strGroupId, class_rights::$STR_RIGHT_VIEW, $objAspect->getSystemid()));


        $objAspect->deleteObjectFromDatabase();
    }


    private function checkNodeRights(
        $strNodeId,
        $bitView = false,
        $bitEdit = false,
        $bitDelete = false,
        $bitRights = false,
        $bitRight1 = false,
        $bitRight2 = false,
        $bitRight3 = false,
        $bitRight4 = false,
        $bitRight5 = false
    ) {

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

        $objCommon = new class_module_system_aspect($strRootNode);
        //var_dump($objCommon->getSystemRecord());
        echo $objCommon->getRecordComment()." / (v: ".$this->objRights->rightView($strRootNode, $this->strUserId)." e: ".$this->objRights->rightEdit($strRootNode, $this->strUserId).") /  ".$objCommon->getSystemid()."\n";

        //var_dump($objCommon->getChildNodesAsIdArray());
        foreach($objCommon->getChildNodesAsIdArray() as $strOneId)
            $this->printTree($strOneId, $intLevel+1);
    }




}

