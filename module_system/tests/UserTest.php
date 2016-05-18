<?php

namespace Kajona\System\Tests;

use Kajona\System\System\Carrier;
use Kajona\System\System\UserGroup;
use Kajona\System\System\UserUser;

class UserTest extends Testbase
{


    public function test()
    {
        $objDB = Carrier::getInstance()->getObjDB();


        //blank system - one user should have been created

        $arrUserInstalled = UserUser::getObjectListFiltered();
        $intStartUsers = count($arrUserInstalled);

        $arrGroupsInstalled = UserGroup::getObjectListFiltered();
        $intStartGroups = count($arrGroupsInstalled);


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
        $arrUserInstalled = UserUser::getObjectListFiltered();
        $this->assertEquals(count($arrUserInstalled), (10 + $intStartUsers), __FILE__ . " checkNrOfUsersCreatedByModel");


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
        $arrGroupsInstalled = UserGroup::getObjectListFiltered();
        $this->assertEquals(count($arrGroupsInstalled), (10 + $intStartGroups), __FILE__ . " checkNrOfGroupsByModel");


        foreach ($arrUsersCreated as $strOneUser) {
            $objUser = new UserUser($strOneUser);
            $objUser->deleteObjectFromDatabase();
        }
        $objDB->flushQueryCache();


        $arrUserInstalled = UserUser::getObjectListFiltered();
        $this->assertEquals(count($arrUserInstalled), $intStartUsers, __FILE__ . " checkNrOfUsers");


        foreach ($arrGroupsCreated as $strOneGroup) {
            $objOneGroup = new UserGroup($strOneGroup);
            $objOneGroup->deleteObjectFromDatabase();
        }
        $objDB->flushQueryCache();

        $arrGroupsInstalled = UserGroup::getObjectListFiltered();
        $this->assertEquals(count($arrGroupsInstalled), $intStartGroups, __FILE__ . " checkNrOfGroups");

        $objGroup = new UserGroup();
        $objGroup->setStrName("AUTOTESTGROUP");
        $objGroup->updateObjectToDb();

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

        foreach ($objGroup->getObjSourceGroup()->getUserIdsForGroup() as $strOneUser) {
            $objOneUser = new UserUser($strOneUser);
            $objOneUser->deleteObjectFromDatabase();
        }
        $objGroup->deleteObjectFromDatabase();


        $objDB->flushQueryCache();
        $arrUserInstalled = UserUser::getObjectListFiltered();
        $this->assertEquals(count($arrUserInstalled), $intStartUsers, __FILE__ . " checkNrOfUsersAtEnd");

        $arrGroupsInstalled = UserGroup::getObjectListFiltered();
        $this->assertEquals(count($arrGroupsInstalled), $intStartGroups, __FILE__ . " checkNrOfGrpupsAtEnd");

    }

}

