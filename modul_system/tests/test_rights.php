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
        $strRootId = $objSystemCommon->createSystemRecord(0, "autotest");
        echo "\tcreating child nodes...\n";
        $strSecOne = $objSystemCommon->createSystemRecord($strRootId, "autotest");
        $strSecTwo = $objSystemCommon->createSystemRecord($strRootId, "autotest");

        $strThirdOne1 = $objSystemCommon->createSystemRecord($strSecOne, "autotest");
        $strThirdOne2 = $objSystemCommon->createSystemRecord($strSecOne, "autotest");
        $strThirdTwo1 = $objSystemCommon->createSystemRecord($strSecTwo, "autotest");
        $strThirdTwo2 = $objSystemCommon->createSystemRecord($strSecTwo, "autotest");

        $strThird111 = $objSystemCommon->createSystemRecord($strThirdOne1, "autotest");
        $strThird112 = $objSystemCommon->createSystemRecord($strThirdOne1, "autotest");
        $strThird121 = $objSystemCommon->createSystemRecord($strThirdOne2, "autotest");
        $strThird122 = $objSystemCommon->createSystemRecord($strThirdOne2, "autotest");
        $strThird211 = $objSystemCommon->createSystemRecord($strThirdTwo1, "autotest");
        $strThird212 = $objSystemCommon->createSystemRecord($strThirdTwo1, "autotest");
        $strThird221 = $objSystemCommon->createSystemRecord($strThirdTwo2, "autotest");
        $strThird222 = $objSystemCommon->createSystemRecord($strThirdTwo2, "autotest");
        $arrThirdLevelNodes = array($strThird111, $strThird112, $strThird121, $strThird122, $strThird211, $strThird212, $strThird221, $strThird222);



        echo "\tadding group with right view & edit\n";
        $objRights->addGroupToRight($objGroup->getSystemid(), $strRootId, "view");
        $objRights->addGroupToRight($objGroup->getSystemid(), $strRootId, "edit");

        echo "\tchecking leaf nodes for inherited rights\n";
        foreach($arrThirdLevelNodes as $strOneRootNode) {
            class_assertions::assertEqual($objRights->rightView($strOneRootNode, $objUser->getSystemid()), true , __FILE__." checkLeafNodesInheritInitial");
            class_assertions::assertEqual($objRights->rightEdit($strOneRootNode, $objUser->getSystemid()), true , __FILE__." checkLeafNodesInheritInitial");
            class_assertions::assertEqual($objRights->rightRight1($strOneRootNode, $objUser->getSystemid()), false , __FILE__." checkLeafNodesInheritInitial");
            class_assertions::assertEqual($objRights->rightRight2($strOneRootNode, $objUser->getSystemid()), false , __FILE__." checkLeafNodesInheritInitial");
            class_assertions::assertEqual($objRights->rightRight3($strOneRootNode, $objUser->getSystemid()), false , __FILE__." checkLeafNodesInheritInitial");
            class_assertions::assertEqual($objRights->rightRight4($strOneRootNode, $objUser->getSystemid()), false , __FILE__." checkLeafNodesInheritInitial");
            class_assertions::assertEqual($objRights->rightRight5($strOneRootNode, $objUser->getSystemid()), false , __FILE__." checkLeafNodesInheritInitial");
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