<?php

include_once(_systempath_."/class_modul_system_common.php");
include_once(_systempath_."/class_modul_user_user.php");
include_once(_systempath_."/class_modul_user_group.php");

class class_test_rights implements interface_testable {



    public function test() {

        $this->testInheritance();
    }



    private function testInheritance() {
        echo "\tRIGHTS INHERITANCE...\n";
        $objDB = class_carrier::getInstance()->getObjDB();
        $objRights = class_carrier::getInstance()->getObjRights();
        $objSystemCommon = new class_modul_system_common();



        //create a new user & group to be used during testing
        echo "\tcreating a test user\n";
        $objUser = new class_modul_user_user();
        $objUser->setStrEmail(generateSystemid()."@".generateSystemid()."de");
        $strUsername = "user_".generateSystemid();
        $objUser->setStrUsername($strUsername);
        $objUser->saveObjectToDb();
        echo "\tid of user: ".$objUser->getSystemid()."\n";

        echo "\tcreating a test group\n";
        $objGroup = new class_modul_user_group();
        $strName = "name_".generateSystemid();
        $objGroup->setStrName($strName);
        $objGroup->saveObjectToDb();
        echo "\tid of group: ".$objGroup->getSystemid()."\n";

        echo "\tadding user to group\n";
        class_modul_user_group::addUserToGroups($objUser, array($objGroup->getSystemid()));

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
            class_assertions::assertTrue($objRights->rightView($strOneRootNode, $objUser->getSystemid()), __FILE__." checkLeafNodesInheritInitial");
            class_assertions::assertTrue($objRights->rightEdit($strOneRootNode, $objUser->getSystemid()), __FILE__." checkLeafNodesInheritInitial");
            class_assertions::assertFalse($objRights->rightRight1($strOneRootNode, $objUser->getSystemid()), __FILE__." checkLeafNodesInheritInitial");
            class_assertions::assertFalse($objRights->rightRight2($strOneRootNode, $objUser->getSystemid()), __FILE__." checkLeafNodesInheritInitial");
            class_assertions::assertFalse($objRights->rightRight3($strOneRootNode, $objUser->getSystemid()), __FILE__." checkLeafNodesInheritInitial");
            class_assertions::assertFalse($objRights->rightRight4($strOneRootNode, $objUser->getSystemid()), __FILE__." checkLeafNodesInheritInitial");
            class_assertions::assertFalse($objRights->rightRight5($strOneRootNode, $objUser->getSystemid()), __FILE__." checkLeafNodesInheritInitial");
        }




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
        $objUser->deleteUser();
        echo "\tdeleting the test group\n";
        class_modul_user_group::deleteGroup($objGroup->getSystemid());
        
    }


}

?>