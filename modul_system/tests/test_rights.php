<?php

include_once(_systempath_."/class_modul_system_common.php");
include_once(_systempath_."/class_modul_user_user.php");
include_once(_systempath_."/class_modul_user_group.php");

class class_test_rights implements interface_testable {



    public function test() {

        $this->testInheritance();
        $this->testCsv();
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
        echo "\id of user: ".$objUser->getSystemid()."\n";

        echo "\tcreating a test group\n";
        $objGroup = new class_modul_user_group();
        $strName = "name_".generateSystemid();
        $objGroup->setStrName($strName);
        $objGroup->saveObjectToDb();
        echo "\id of group: ".$objGroup->getSystemid()."\n";

        echo "\tadding user to group\n";
        class_modul_user_group::addUserToGroups($objUser, array($objGroup->getSystemid()));

        echo "\creating node-tree\n";
        $intRootId = $objSystemCommon->createSystemRecord(0, "autotest");
        echo "\tcreating child nodes...\n";
        $intSecOneId = $objSystemCommon->createSystemRecord($intRootId, "autotest");
        $intSecTwoId = $objSystemCommon->createSystemRecord($intRootId, "autotest");

        $intThirdOne1 = $objSystemCommon->createSystemRecord($intSecOneId, "autotest");
        $intThirdOne2 = $objSystemCommon->createSystemRecord($intSecOneId, "autotest");
        $intThirdTwo1 = $objSystemCommon->createSystemRecord($intSecTwoId, "autotest");
        $intThirdTwo2 = $objSystemCommon->createSystemRecord($intSecTwoId, "autotest");

        $intThird111 = $objSystemCommon->createSystemRecord($intThirdOne1, "autotest");
        $intThird112 = $objSystemCommon->createSystemRecord($intThirdOne1, "autotest");
        $intThird121 = $objSystemCommon->createSystemRecord($intThirdOne2, "autotest");
        $intThird122 = $objSystemCommon->createSystemRecord($intThirdOne2, "autotest");
        $intThird211 = $objSystemCommon->createSystemRecord($intThirdTwo1, "autotest");
        $intThird212 = $objSystemCommon->createSystemRecord($intThirdTwo1, "autotest");
        $intThird221 = $objSystemCommon->createSystemRecord($intThirdTwo2, "autotest");
        $intThird222 = $objSystemCommon->createSystemRecord($intThirdTwo2, "autotest");



        echo "\tadding group with right view & edit\n";
        $objRights->addGroupToRight(_guests_group_id_, $intRootId, "view");
        $objRights->addGroupToRight(_guests_group_id_, $intRootId, "edit");



        echo "\tdeleting the test user\n";
        $objUser->deleteUser();
        echo "\tdeleting the test group\n";
        class_modul_user_group::deleteGroup($objGroup->getSystemid());

    }

    
}

?>