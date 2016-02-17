<?php

namespace Kajona\System\Tests;

use Kajona\System\System\CoreEventdispatcher;
use Kajona\System\System\GenericeventListenerInterface;
use Kajona\System\System\SystemAspect;
use Kajona\System\System\SystemEventidentifier;
use Kajona\System\System\Testbase;

class StatuschangedeventTest extends Testbase  {

    public static $bitHandled = false;

    public function testEventHandler() {
        self::$bitHandled = false;


        $objAspect = new SystemAspect();
        $objAspect->setStrName("test");
        $objAspect->updateObjectToDb();

        CoreEventdispatcher::getInstance()->addListener(SystemEventidentifier::EVENT_SYSTEM_STATUSCHANGED, new StatuchangedhandlerTestModel($objAspect->getSystemid(), $this));

        $this->assertTrue(!self::$bitHandled);
        $objAspect->setIntRecordStatus(0);
        $this->assertTrue(!self::$bitHandled);
        $objAspect->updateObjectToDb();
        $this->assertTrue(self::$bitHandled);

        $objAspect->deleteObjectFromDatabase();
    }


    public function assertData($intOldStatus, $intNewStatus, $objObject) {
        $this->assertEquals($intOldStatus, 1);
        $this->assertEquals($intNewStatus, 0);
        $this->assertTrue($objObject instanceof SystemAspect);
    }


}

class StatuchangedhandlerTestModel implements GenericeventListenerInterface {

    private $strSystemid;
    /** @var  StatuschangedeventTest */
    private $objSourceTest;

    function __construct($strSystemid, StatuschangedeventTest $objTest) {
        $this->strSystemid = $strSystemid;
        $this->objSourceTest = $objTest;
    }


    /**
     * This generic method is called in case of dispatched events.
     * The first param is the name of the event, the second argument is an array of
     * event-specific arguments.
     * Make sure to return a matching boolean value, indicating if the event-process was successful or not. The event source may
     * depend on a valid return value.
     *
     * @param string $strEventIdentifier
     * @param array $arrArguments
     *
     * @return bool
     */
    public function handleEvent($strEventIdentifier, array $arrArguments) {

        list($strSystemid, $objObject, $intOldStatus, $intNewStatus) = $arrArguments;

        if($strSystemid == $this->strSystemid) {
            StatuschangedeventTest::$bitHandled = true;
            $this->objSourceTest->assertData($intOldStatus, $intNewStatus, $objObject);


        }
    }

}

