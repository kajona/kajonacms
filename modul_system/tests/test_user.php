<?php

class class_test_user implements interface_testable {



    public function test() {
        $objDB = class_carrier::getInstance()->getObjDB();

        echo "\tmodul_user...\n";

        //blank system - one user should have been created

        echo "\tcheck number of users installed...\n";
        $arrUserInstalled = class_modul_user_user::getAllUsers();
        $intStartUsers = count($arrUserInstalled);
        echo "\t ...found ".$intStartUsers." users.\n";

        echo "\tcheck number of groups installed...\n";
        $arrGroupsInstalled = class_modul_user_group::getAllGroups();
        $intStartGroups = count($arrGroupsInstalled);
        echo "\t ...found ".$intStartUsers." users.\n";


        echo "\tcreate 100 users using the model...\n";
        $arrUsersCreated = array();
        for($intI =0; $intI < 100; $intI++) {
            $objUser = new class_modul_user_user();
            $objUser->setStrEmail(generateSystemid()."@".generateSystemid()."de");
            $strUsername = "user_".generateSystemid();
            $objUser->setStrUsername($strUsername);
            $objUser->updateObjectToDb();
            $arrUsersCreated[] = $objUser->getSystemid();
            $strID = $objUser->getSystemid();
            $objDB->flushQueryCache();
            $objUser = new class_modul_user_user($strID);
            class_assertions::assertEqual($objUser->getStrUsername(), $strUsername, __FILE__." checkNameOfUserCreated");
        }
        $arrUserInstalled = class_modul_user_user::getAllUsers();
        class_assertions::assertEqual(count($arrUserInstalled), (100+$intStartUsers), __FILE__." checkNrOfUsersCreatedByModel");



        echo "\tcreate 100 groups using the model...\n";
        $arrGroupsCreated = array();
        for($intI =0; $intI < 100; $intI++) {
            $objGroup = new class_modul_user_group();
            $strName = "name_".generateSystemid();
            $objGroup->setStrName($strName);
            $objGroup->updateObjectToDb();
            $strID = $objGroup->getSystemid();
            $arrGroupsCreated[] = $objGroup->getSystemid();
            $objDB->flushQueryCache();
            $objGroup = new class_modul_user_group($strID);
            class_assertions::assertEqual($objGroup->getStrName(), $strName, __FILE__." checkNameOfGroupCreated");
        }
        $arrGroupsInstalled = class_modul_user_group::getAllGroups();
        class_assertions::assertEqual(count($arrGroupsInstalled), (100+$intStartGroups), __FILE__." checkNrOfGroupsByModel");



        echo "\tdeleting users created...\n";
        foreach($arrUsersCreated as $strOneUser) {
                $objUser = new class_modul_user_user($strOneUser);
            $objUser->deleteUser();
            $objDB->flushQueryCache();
        }


        echo "\tcheck number of users installed...\n";
        $arrUserInstalled = class_modul_user_user::getAllUsers();
        class_assertions::assertEqual(count($arrUserInstalled), $intStartUsers, __FILE__." checkNrOfUsers");



        echo "\tdeleting groups created...\n";
        foreach($arrGroupsCreated as $strOneGroup) {
            class_modul_user_group::deleteGroup($strOneGroup);
            $objDB->flushQueryCache();
        }

        echo "\tcheck number of groups installed...\n";
        $arrGroupsInstalled = class_modul_user_group::getAllGroups();
        class_assertions::assertEqual(count($arrGroupsInstalled), $intStartGroups, __FILE__." checkNrOfGroups");

        echo "\ttest group membership handling...\n";
        $objGroup = new class_modul_user_group();
        $objGroup->setStrName("AUTOTESTGROUP");
        $objGroup->updateObjectToDb();

        echo "\tadding 100 members to group...\n";
        for ($intI = 0; $intI <= 100; $intI++) {
            $objUser = new class_modul_user_user();
            $objUser->setStrUsername("AUTOTESTUSER_".$intI);
            $objUser->setStrEmail("autotest_".$intI."@kajona.de");
            $objUser->updateObjectToDb();
            //add user to group
            class_modul_user_group::addUserToGroups($objUser, array($objGroup->getSystemid()));
            $arrUsersInGroup = class_modul_user_group::getGroupMembers($objGroup->getSystemid());
            class_assertions::assertTrue($objGroup->isUserMemberInGroup($objUser), __FILE__." checkUserInGroup");
            class_assertions::assertEqual(count($arrUsersInGroup), 1+$intI, __FILE__." checkNrOfUsersInGroup");
            $objDB->flushQueryCache();
        }

        echo "\tdeleting groups & users\n";
        foreach(class_modul_user_group::getGroupMembers($objGroup->getSystemid()) as $objOneUser)
            $objOneUser->deleteUser();
        class_modul_user_group::deleteGroup($objGroup->getSystemid());


        $objDB->flushQueryCache();
        echo "\tcheck number of users installed is same as at beginning...\n";
        $arrUserInstalled = class_modul_user_user::getAllUsers();
        class_assertions::assertEqual(count($arrUserInstalled), $intStartUsers, __FILE__." checkNrOfUsersAtEnd");

        echo "\tcheck number of groups installed is same as at beginning...\n";
        $arrGroupsInstalled = class_modul_user_group::getAllGroups();
        class_assertions::assertEqual(count($arrGroupsInstalled), $intStartGroups, __FILE__." checkNrOfGrpupsAtEnd");

    }

}

?>