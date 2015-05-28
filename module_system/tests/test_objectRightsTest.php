<?php

require_once (__DIR__."/../../module_system/system/class_testbase.php");

class class_test_objectRights extends class_testbase {

    /**
     * @var class_rights
     */
    private $objRights ;
    private $strUserId;


    public function testInheritanceForObjects() {


        if(class_module_system_module::getModuleByName("pages") === null)
            return;

        echo "\tRIGHTS INHERITANCE...\n";
        $objRights = class_carrier::getInstance()->getObjRights();
        $this->objRights = class_carrier::getInstance()->getObjRights();


        //create a new user & group to be used during testing
        echo "\tcreating a test user\n";
        $objUser = new class_module_user_user();
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

        $strModuleId = $this->createObject("class_module_system_module", "0")->getSystemid();
        class_carrier::getInstance()->flushCache(class_carrier::INT_CACHE_TYPE_MODULES);
        class_module_system_module::getAllModules();

        echo "\tcreating node-tree\n";
        $strRootId = $this->createObject("class_module_pages_page", $strModuleId)->getSystemid();
        echo "\tid of root-node: ".$strRootId."\n";
        echo "\tcreating child nodes...\n";
        $strSecOne = $this->createObject("class_module_pages_page", $strRootId)->getSystemid();
        $strSecTwo = $this->createObject("class_module_pages_page", $strRootId)->getSystemid();

        $strThirdOne1 = $this->createObject("class_module_pages_page", $strSecOne)->getSystemid();
        $strThirdOne2 = $this->createObject("class_module_pages_page", $strSecOne)->getSystemid();
        $strThirdTwo1 = $this->createObject("class_module_pages_page", $strSecTwo)->getSystemid();
        $strThirdTwo2 = $this->createObject("class_module_pages_page", $strSecTwo)->getSystemid();

        $strThird111 = $this->createObject("class_module_pages_page", $strThirdOne1)->getSystemid();
        $strThird112 = $this->createObject("class_module_pages_page", $strThirdOne1)->getSystemid();
        $strThird121 = $this->createObject("class_module_pages_page", $strThirdOne2)->getSystemid();
        $strThird122 = $this->createObject("class_module_pages_page", $strThirdOne2)->getSystemid();
        $strThird211 = $this->createObject("class_module_pages_page", $strThirdTwo1)->getSystemid();
        $strThird212 = $this->createObject("class_module_pages_page", $strThirdTwo1)->getSystemid();
        $strThird221 = $this->createObject("class_module_pages_page", $strThirdTwo2)->getSystemid();
        $strThird222 = $this->createObject("class_module_pages_page", $strThirdTwo2)->getSystemid();
        $arrThirdLevelNodes = array($strThird111, $strThird112, $strThird121, $strThird122, $strThird211, $strThird212, $strThird221, $strThird222);


        echo "\tchecking leaf nodes for initial rights\n";
        foreach($arrThirdLevelNodes as $strOneRootNode) {
            $this->checkNodeRights($strOneRootNode, false, false);
        }

        echo "\tadding group with right view & edit\n";
        $objRights->addGroupToRight($objGroup->getSystemid(), $strModuleId, "view");
        $objRights->addGroupToRight($objGroup->getSystemid(), $strModuleId, "edit");


        echo "\tchecking leaf nodes for inherited rights\n";
        foreach($arrThirdLevelNodes as $strOneRootNode) {
            $this->checkNodeRights($strOneRootNode, true, true);
        }


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
        $objTempCommons = class_objectfactory::getInstance()->getObject($strSecOne);
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
        $objTempCommons = class_objectfactory::getInstance()->getObject($strThirdOne1);
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


        echo "\trebuilding initial tree structure\n";
        $objTempCommons = class_objectfactory::getInstance()->getObject($strSecOne);
        $objTempCommons->setStrPrevId($strRootId);
        $objTempCommons->updateObjectToDb();
        //$objSystemCommon->setPrevId($strRootId, $strSecOne); //SecOne still inheriting
        $objTempCommons = class_objectfactory::getInstance()->getObject($strThirdOne1);
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



        echo "\tdeleting systemnodes\n";



        class_objectfactory::getInstance()->getObject($strThird111)->deleteObjectFromDatabase();
        class_objectfactory::getInstance()->getObject($strThird112)->deleteObjectFromDatabase();
        class_objectfactory::getInstance()->getObject($strThird121)->deleteObjectFromDatabase();
        class_objectfactory::getInstance()->getObject($strThird122)->deleteObjectFromDatabase();
        class_objectfactory::getInstance()->getObject($strThird211)->deleteObjectFromDatabase();
        class_objectfactory::getInstance()->getObject($strThird212)->deleteObjectFromDatabase();
        class_objectfactory::getInstance()->getObject($strThird221)->deleteObjectFromDatabase();
        class_objectfactory::getInstance()->getObject($strThird222)->deleteObjectFromDatabase();
        class_objectfactory::getInstance()->getObject($strThirdOne1)->deleteObjectFromDatabase();
        class_objectfactory::getInstance()->getObject($strThirdOne2)->deleteObjectFromDatabase();
        class_objectfactory::getInstance()->getObject($strThirdTwo1)->deleteObjectFromDatabase();
        class_objectfactory::getInstance()->getObject($strThirdTwo2)->deleteObjectFromDatabase();
        class_objectfactory::getInstance()->getObject($strSecOne)->deleteObjectFromDatabase();
        class_objectfactory::getInstance()->getObject($strSecTwo)->deleteObjectFromDatabase();
        class_objectfactory::getInstance()->getObject($strRootId)->deleteObjectFromDatabase();

        class_objectfactory::getInstance()->getObject($strModuleId)->deleteObjectFromDatabase();

        echo "\tdeleting the test user\n";
        $objUser->deleteObjectFromDatabase();
        echo "\tdeleting the test group\n";
        $objGroup->deleteObjectFromDatabase();

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

        $objTestObject = class_objectfactory::getInstance()->getObject($strNodeId);

        $this->assertEquals($bitView,   $this->objRights->rightView($strNodeId, $this->strUserId), __FILE__." checkNodeRights View ".$strNodeId);
        $this->assertEquals($bitEdit,   $this->objRights->rightEdit($strNodeId, $this->strUserId), __FILE__." checkNodeRights Edit ".$strNodeId);
        $this->assertEquals($bitDelete, $this->objRights->rightDelete($strNodeId, $this->strUserId), __FILE__." checkNodeRights Delete ".$strNodeId);
        $this->assertEquals($bitRights, $this->objRights->rightRight($strNodeId, $this->strUserId), __FILE__." checkNodeRights Rights".$strNodeId);
        $this->assertEquals($bitRight1, $this->objRights->rightRight1($strNodeId, $this->strUserId), __FILE__." checkNodeRights Right1".$strNodeId);
        $this->assertEquals($bitRight2, $this->objRights->rightRight2($strNodeId, $this->strUserId), __FILE__." checkNodeRights Right2".$strNodeId);
        $this->assertEquals($bitRight3, $this->objRights->rightRight3($strNodeId, $this->strUserId), __FILE__." checkNodeRights Right3".$strNodeId);
        $this->assertEquals($bitRight4, $this->objRights->rightRight4($strNodeId, $this->strUserId), __FILE__." checkNodeRights Right4".$strNodeId);
        $this->assertEquals($bitRight5, $this->objRights->rightRight5($strNodeId, $this->strUserId), __FILE__." checkNodeRights Right5".$strNodeId);

    }





}

