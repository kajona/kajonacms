<?php

namespace Kajona\System\Tests;

require_once __DIR__ . "../../../core/module_system/system/Testbase.php";

use Kajona\System\System\Carrier;
use Kajona\System\System\Testbase;
use Kajona\System\System\UserGroup;
use Kajona\System\System\UserUser;

class UserTest extends Testbase
{


    public function test()
    {
        $objDB = Carrier::getInstance()->getObjDB();

        echo "\tmodul_user...\n";

        //blank system - one user should have been created

        echo "\tcheck number of users installed...\n";
        $arrUserInstalled = UserUser::getObjectList();
        $intStartUsers = count($arrUserInstalled);
        echo "\t ...found " . $intStartUsers . " users.\n";

        echo "\tcheck number of groups installed...\n";
        $arrGroupsInstalled = UserGroup::getObjectList();
        $intStartGroups = count($arrGroupsInstalled);
        echo "\t ...found " . $intStartUsers . " users.\n";


        echo "\tcreate 10 users using the model...\n";
        $arrUsersCreated = array();
        for ($intI = 0; $intI < 10; $intI++) {
            $objUser = new UserUser();
            //$objUser->setStrEmail(generateSystemid()."@".generateSystemid()."de");
            $strUsername = "user_" . generateSystemid();
            $objUser->setStrUsername($strUsername);
            $objUser->updateObjectToDb();
            $arrUsersCreated[] = $objUser->getSystemid();
            $strID = $objUser->getSystemid();
            $objDB->flushQueryCache();
            $objUser = new UserUser($strID);
            $this->assertEquals($objUser->getStrUsername(), $strUsername, __FILE__ . " checkNameOfUserCreated");
        }
        $arrUserInstalled = UserUser::getObjectList();
        $this->assertEquals(count($arrUserInstalled), (10 + $intStartUsers), __FILE__ . " checkNrOfUsersCreatedByModel");


        echo "\tcreate 10 groups using the model...\n";
        $arrGroupsCreated = array();
        for ($intI = 0; $intI < 10; $intI++) {
            $objGroup = new UserGroup();
            $strName = "name_" . generateSystemid();
            $objGroup->setStrName($strName);
            $objGroup->updateObjectToDb();
            $strID = $objGroup->getSystemid();
            $arrGroupsCreated[] = $objGroup->getSystemid();
            $objDB->flushQueryCache();
            $objGroup = new UserGroup($strID);
            $this->assertEquals($objGroup->getStrName(), $strName, __FILE__ . " checkNameOfGroupCreated");
        }
        $arrGroupsInstalled = UserGroup::getObjectList();
        $this->assertEquals(count($arrGroupsInstalled), (10 + $intStartGroups), __FILE__ . " checkNrOfGroupsByModel");


        echo "\tdeleting users created...\n";
        foreach ($arrUsersCreated as $strOneUser) {
            echo "\t\tdeleting user " . $strOneUser . "...\n";
            $objUser = new UserUser($strOneUser);
            $objUser->deleteObjectFromDatabase();
        }
        $objDB->flushQueryCache();


        echo "\tcheck number of users installed...\n";
        $arrUserInstalled = UserUser::getObjectList();
        $this->assertEquals(count($arrUserInstalled), $intStartUsers, __FILE__ . " checkNrOfUsers");


        echo "\tdeleting groups created...\n";
        foreach ($arrGroupsCreated as $strOneGroup) {
            $objOneGroup = new UserGroup($strOneGroup);
            $objOneGroup->deleteObjectFromDatabase();
        }
        $objDB->flushQueryCache();

        echo "\tcheck number of groups installed...\n";
        $arrGroupsInstalled = UserGroup::getObjectList();
        $this->assertEquals(count($arrGroupsInstalled), $intStartGroups, __FILE__ . " checkNrOfGroups");

        echo "\ttest group membership handling...\n";
        $objGroup = new UserGroup();
        $objGroup->setStrName("AUTOTESTGROUP");
        $objGroup->updateObjectToDb();

        echo "\tadding 10 members to group...\n";
        for ($intI = 0; $intI <= 10; $intI++) {
            $objUser = new UserUser();
            $objUser->setStrUsername("AUTOTESTUSER_" . $intI);
            //$objUser->setStrEmail("autotest_".$intI."@kajona.de");
            $objUser->updateObjectToDb();
            //add user to group
            $objGroup->getObjSourceGroup()->addMember($objUser->getObjSourceUser());
            $arrUsersInGroup = $objGroup->getObjSourceGroup()->getUserIdsForGroup();
            $this->assertTrue(in_array($objUser->getSystemid(), $arrUsersInGroup), __FILE__ . " checkUserInGroup");
            $this->assertEquals(count($arrUsersInGroup), 1 + $intI, __FILE__ . " checkNrOfUsersInGroup");
            $objDB->flushQueryCache();
        }

        echo "\tdeleting groups & users\n";
        foreach ($objGroup->getObjSourceGroup()->getUserIdsForGroup() as $strOneUser) {
            $objOneUser = new UserUser($strOneUser);
            $objOneUser->deleteObjectFromDatabase();
        }
        $objGroup->deleteObjectFromDatabase();


        $objDB->flushQueryCache();
        echo "\tcheck number of users installed is same as at beginning...\n";
        $arrUserInstalled = UserUser::getObjectList();
        $this->assertEquals(count($arrUserInstalled), $intStartUsers, __FILE__ . " checkNrOfUsersAtEnd");

        echo "\tcheck number of groups installed is same as at beginning...\n";
        $arrGroupsInstalled = UserGroup::getObjectList();
        $this->assertEquals(count($arrGroupsInstalled), $intStartGroups, __FILE__ . " checkNrOfGrpupsAtEnd");

    }

}

