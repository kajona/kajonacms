<?php

namespace Kajona\System\Tests;

require_once __DIR__ . "../../../core/module_system/system/Testbase.php";

use Kajona\System\System\Carrier;
use Kajona\System\System\Exception;
use Kajona\System\System\SystemAspect;
use Kajona\System\System\SystemSetting;
use Kajona\System\System\Testbase;
use Kajona\System\System\UserGroup;
use Kajona\System\System\UserUser;

class LockmanagerTest extends Testbase
{


    public function testLocking()
    {
        $objAspect = new SystemAspect();
        $objAspect->setStrName("test");
        $objAspect->updateObjectToDb();
        $strAspectId = $objAspect->getSystemid();


        $this->assertTrue($objAspect->getLockManager()->isAccessibleForCurrentUser());
        $this->assertTrue(!$objAspect->getLockManager()->isLocked());

        $objUser = new UserUser();
        $objUser->setStrUsername(generateSystemid());
        $objUser->setIntActive(1);
        $objUser->updateObjectToDb();

        $this->assertTrue(Carrier::getInstance()->getObjSession()->loginUser($objUser));

        $objAspect->getLockManager()->lockRecord();

        $this->assertEquals($objUser->getSystemid(), $objAspect->getLockManager()->getLockId());

        $this->assertTrue($objAspect->getLockManager()->isAccessibleForCurrentUser());
        $this->assertTrue($objAspect->getLockManager()->isLocked());
        $this->assertTrue($objAspect->getLockManager()->isLockedByCurrentUser());

        //updates should release the lock
        $objAspect->updateObjectToDb();

        $this->assertTrue($objAspect->getLockManager()->isAccessibleForCurrentUser());
        $this->assertTrue(!$objAspect->getLockManager()->isLocked());
        $this->assertTrue(!$objAspect->getLockManager()->isLockedByCurrentUser());


        Carrier::getInstance()->getObjSession()->logout();
        $objAspect = new SystemAspect($strAspectId);
        $objAspect->deleteObjectFromDatabase();
        $objUser->deleteObjectFromDatabase();
    }


    public function testLockBetweenUsers()
    {
        $objAspect = new SystemAspect();
        $objAspect->setStrName("test");
        $objAspect->updateObjectToDb();
        $strAspectId = $objAspect->getSystemid();


        $this->assertTrue($objAspect->getLockManager()->isAccessibleForCurrentUser());
        $this->assertTrue(!$objAspect->getLockManager()->isLocked());

        $objUser1 = new UserUser();
        $objUser1->setStrUsername(generateSystemid());
        $objUser1->setIntActive(1);
        $objUser1->updateObjectToDb();

        $this->assertTrue(Carrier::getInstance()->getObjSession()->loginUser($objUser1));
        $objAspect->getLockManager()->lockRecord();

        $this->assertEquals($objUser1->getSystemid(), $objAspect->getLockManager()->getLockId());

        $this->assertTrue($objAspect->getLockManager()->isAccessibleForCurrentUser());
        $this->assertTrue($objAspect->getLockManager()->isLocked());
        $this->assertTrue($objAspect->getLockManager()->isLockedByCurrentUser());

        $objUser2 = new UserUser();
        $objUser2->setStrUsername(generateSystemid());
        $objUser2->setIntActive(1);
        $objUser2->updateObjectToDb();


        $this->assertTrue(Carrier::getInstance()->getObjSession()->loginUser($objUser2));

        $this->assertTrue(!$objAspect->getLockManager()->isAccessibleForCurrentUser());
        $this->assertTrue($objAspect->getLockManager()->isLocked());
        $this->assertTrue(!$objAspect->getLockManager()->isLockedByCurrentUser());


        //updates should release the lock
        $objException = null;
        try {
            $objAspect->updateObjectToDb();
        } catch (Exception $objEx) {
            $objException = $objEx;
        }

        $this->assertNotNull($objException);

        //lock should remain
        $this->assertTrue(!$objAspect->getLockManager()->isAccessibleForCurrentUser());
        $this->assertTrue($objAspect->getLockManager()->isLocked());
        $this->assertTrue(!$objAspect->getLockManager()->isLockedByCurrentUser());

        $this->assertEquals($objUser1->getSystemid(), $objAspect->getLockManager()->getLockId());

        //unlocking is not allowed for user 2
        $this->assertTrue(!$objAspect->getLockManager()->unlockRecord());

        //force unlock not allowed since user is not in admin group
        $this->assertTrue($objAspect->getLockManager()->unlockRecord(true));

        //lock should remain
        $this->assertTrue($objAspect->getLockManager()->isAccessibleForCurrentUser());
        $this->assertTrue(!$objAspect->getLockManager()->isLocked());
        $this->assertTrue(!$objAspect->getLockManager()->isLockedByCurrentUser());


        //add user 2 to admin group
        $objGroup = new UserGroup(SystemSetting::getConfigValue("_admins_group_id_"));
        $this->assertTrue($objGroup->getObjSourceGroup()->addMember($objUser2->getObjSourceUser()));

        //relogin
        $this->flushDBCache();
        $objUser2 = new UserUser($objUser2->getSystemid());
        $this->assertTrue(Carrier::getInstance()->getObjSession()->loginUser($objUser2));

        //force unlock now allowed since user is not in admin group
        $this->assertTrue($objAspect->getLockManager()->unlockRecord(true));

        //lock should be gone
        $this->assertTrue($objAspect->getLockManager()->isAccessibleForCurrentUser());
        $this->assertTrue(!$objAspect->getLockManager()->isLocked());
        $this->assertTrue(!$objAspect->getLockManager()->isLockedByCurrentUser());


        Carrier::getInstance()->getObjSession()->logout();
        $objAspect = new SystemAspect($strAspectId);
        $objAspect->deleteObjectFromDatabase();
        $objUser1->deleteObjectFromDatabase();
        $objUser2->deleteObjectFromDatabase();
    }


    public function testLockExceptionOnSort()
    {
        $objAspect = new SystemAspect();
        $objAspect->setStrName("test");
        $objAspect->updateObjectToDb();
        $strAspectId = $objAspect->getSystemid();

        $objUser1 = new UserUser();
        $objUser1->setStrUsername(generateSystemid());
        $objUser1->setIntActive(1);
        $objUser1->updateObjectToDb();

        $this->assertTrue(Carrier::getInstance()->getObjSession()->loginUser($objUser1));

        $objAspect->getLockManager()->lockRecord();
        $this->assertTrue($objAspect->getLockManager()->isLockedByCurrentUser());

        $objUser2 = new UserUser();
        $objUser2->setStrUsername(generateSystemid());
        $objUser2->setIntActive(1);
        $objUser2->updateObjectToDb();

        $this->assertTrue(Carrier::getInstance()->getObjSession()->loginUser($objUser2));
        $this->assertTrue(!$objAspect->getLockManager()->isLockedByCurrentUser());

        $intSort = $objAspect->getIntSort();
        $objException = null;
        try {
            $objAspect->setAbsolutePosition(4);
        } catch (Exception $objEx) {
            $objException = $objEx;
        }

        $this->assertNotNull($objException);
        $this->assertEquals($intSort, $objAspect->getIntSort());

        Carrier::getInstance()->getObjSession()->logout();
        $objAspect = new SystemAspect($strAspectId);
        $objAspect->deleteObjectFromDatabase();
        $objUser1->deleteObjectFromDatabase();
        $objUser2->deleteObjectFromDatabase();
    }


}

