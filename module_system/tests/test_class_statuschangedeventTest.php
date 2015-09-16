<?php

require_once (__DIR__."/../../module_system/system/class_testbase.php");

class class_test_statuschangedeventTest extends class_testbase  {

    public static $bitHandled = false;

    public function testEventHandler() {
        self::$bitHandled = false;


        $objAspect = new class_module_system_aspect();
        $objAspect->setStrName("test");
        $objAspect->updateObjectToDb();

        class_core_eventdispatcher::getInstance()->addListener(class_system_eventidentifier::EVENT_SYSTEM_STATUSCHANGED, new class_test_statuchangedhandler($objAspect->getSystemid(), $this));

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
        $this->assertTrue($objObject instanceof class_module_system_aspect);
    }


}

class class_test_statuchangedhandler implements interface_genericevent_listener {

    private $strSystemid;
    /** @var  class_test_statuschangedeventTest */
    private $objSourceTest;

    function __construct($strSystemid, class_test_statuschangedeventTest $objTest) {
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
            class_test_statuschangedeventTest::$bitHandled = true;
            $this->objSourceTest->assertData($intOldStatus, $intNewStatus, $objObject);


        }
    }

}

