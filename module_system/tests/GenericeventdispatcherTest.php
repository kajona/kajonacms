<?php

namespace Kajona\System\Tests;

require_once __DIR__."../../../core/module_system/system/Testbase.php";

use Kajona\System\System\Classloader;
use Kajona\System\System\CoreEventdispatcher;
use Kajona\System\System\GenericeventListenerInterface;
use Kajona\System\System\Resourceloader;
use Kajona\System\System\Testbase;

class GenericeventdispatcherTest extends Testbase  {


    protected function setUp() {

        $strClass = <<<PHP
<?php
            namespace Kajona\System\Tests;
            class GenericeventdispatcherTestModel implements \Kajona\System\System\GenericeventListenerInterface  {

                public \$arrArguments = array();

                public \$strHandlerName = "";

                public \$objCallable;

                public function handleEvent(\$strEventName, array \$arrArguments) {
                    \$this->arrArguments = \$arrArguments;

                    if(is_callable(\$this->objCallable)) {
                        call_user_func_array(\$this->objCallable, array(\$this->strHandlerName, \$arrArguments));
                        //\$this->objCallable(\$this->strHandlerName, \$arrArguments);
                    }
                    return true;
                }

                public function getExtensionName() {
                    return "core.system.test.genericevent";
                }
            }

PHP;


        echo "Saving testfiles to ".Resourceloader::getInstance()->getCorePathForModule("module_system", true)."/module_system/system/GenericeventdispatcherTestModel.php\n";
        file_put_contents(Resourceloader::getInstance()->getCorePathForModule("module_system", true)."/module_system/system/GenericeventdispatcherTestModel.php", $strClass);

        Classloader::getInstance()->flushCache();

        parent::setUp();


    }

    public function testInstantiations() {

        $objInstance = new \Kajona\System\Tests\GenericeventdispatcherTestModel();
        $this->assertTrue(is_object($objInstance));
        $this->assertTrue($objInstance instanceof GenericeventListenerInterface);

    }



    public function testEvents() {

        $objListener1 = new \Kajona\System\Tests\GenericeventdispatcherTestModel();
        $objListener1->strHandlerName = "handler 1";
        $objListener1->objCallable = function($strName, $arrArguments) {
            $this->assertEquals($strName, "handler 1");
            $this->assertEquals(count($arrArguments), 2);
            $this->assertEquals($arrArguments[0], "a1");
            $this->assertEquals($arrArguments[1], "a2");
        };

        $objListener2 = new \Kajona\System\Tests\GenericeventdispatcherTestModel();
        $objListener2->strHandlerName = "handler 2";
        $objListener2->objCallable = function($strName, $arrArguments) {
            $this->assertEquals($strName, "handler 2");
            $this->assertEquals(count($arrArguments), 2);
            $this->assertEquals($arrArguments[0], "a1");
            $this->assertEquals($arrArguments[1], "a2");
        };

        $objDispatcher = CoreEventdispatcher::getInstance();
        $objDispatcher->addListener("core.system.test.genericevent", $objListener1);
        $objDispatcher->addListener("core.system.test.genericevent", $objListener2);

        $arrListeners = $objDispatcher->getRegisteredListeners("core.system.test.genericevent");
        $this->assertEquals(count($arrListeners), 2);
        $this->assertEquals($arrListeners[0]->strHandlerName, "handler 1");
        $this->assertEquals($arrListeners[1]->strHandlerName, "handler 2");

        $objDispatcher->notifyGenericListeners("core.system.test.genericevent", array("a1", "a2"));

        $objDispatcher->removeListener("core.system.test.genericevent", $objListener1);
        $arrListeners = $objDispatcher->getRegisteredListeners("core.system.test.genericevent");
        $this->assertEquals(count($arrListeners), 1);
        $this->assertEquals(array_values($arrListeners)[0]->strHandlerName, "handler 2");

        $objDispatcher->removeAllListeners("core.system.test.genericevent");
        $arrListeners = $objDispatcher->getRegisteredListeners("core.system.test.genericevent");
        $this->assertEquals(count($arrListeners), 0);
    }

    public function testRemoveAndAddListener() {
        $objListener1 = new \Kajona\System\Tests\GenericeventdispatcherTestModel();
        $objListener1->strHandlerName = "handler 1";
        $objListener1->objCallable = function($strName, $arrArguments) {
            $this->assertEquals($strName, "handler 1");
        };

        $objListener2 = new \Kajona\System\Tests\GenericeventdispatcherTestModel();
        $objListener2->strHandlerName = "handler 2";
        $objListener2->objCallable = function($strName, $arrArguments) {
            $this->assertEquals($strName, "handler 2");
        };

        $objDispatcher = CoreEventdispatcher::getInstance();
        $objDispatcher->addListener("core.system.test.removeandadd", $objListener1);
        $objDispatcher->addListener("core.system.test.removeandadd", $objListener2);

        $arrListeners = $objDispatcher->getRegisteredListeners("core.system.test.removeandadd");
        $this->assertEquals(count($arrListeners), 2);
        $this->assertEquals(array_values($arrListeners)[0]->strHandlerName, "handler 1");
        $this->assertEquals(array_values($arrListeners)[1]->strHandlerName, "handler 2");

        $objListener3 = new \Kajona\System\Tests\GenericeventdispatcherTestModel();
        $objListener3->strHandlerName = "handler 3";
        $objDispatcher->removeAndAddListener("core.system.test.removeandadd", $objListener3);

        $arrListeners = $objDispatcher->getRegisteredListeners("core.system.test.removeandadd");
        $this->assertEquals(count($arrListeners), 1);
        $this->assertEquals(array_values($arrListeners)[0]->strHandlerName, "handler 3");


    }

    protected function tearDown() {

        unlink(Resourceloader::getInstance()->getCorePathForModule("module_system", true)."/module_system/system/GenericeventdispatcherTestModel.php");

        Classloader::getInstance()->flushCache();

        parent::tearDown();
    }

}

