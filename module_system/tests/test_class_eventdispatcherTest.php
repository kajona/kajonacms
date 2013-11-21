<?php

require_once (__DIR__."/../../module_system/system/class_testbase.php");

class class_test_eventdispatcher extends class_testbase  {


    protected function setUp() {

        $strClass = <<<PHP
<?php
            class class_module_eventdispatcher_test implements interface_eventtest_listener  {

                public function handleDemoEvent1(\$strArg1, \$strArg2) {
                    return true;
                }

                public function handleDemoEvent2(\$strArg1, \$strArg2, \$strArg3) {
                    return false;
                }

                public function handleDemoEvent3(\$strArg1) {
                    return \$strArg1 == true;
                }

                public function handleDemoException(\$strArg1) {
                    throw new class_exception(\$strArg1, class_exception::\$level_ERROR);
                }
            }

PHP;

        $strInterface = <<<PHP
<?php
            interface interface_eventtest_listener {
                public function handleDemoEvent1(\$strArg1, \$strArg2);
                public function handleDemoEvent2(\$strArg1, \$strArg2, \$strArg3);
                public function handleDemoEvent3(\$strArg1);

                public function handleDemoException(\$strMessage1);
            }

PHP;


        file_put_contents(_realpath_."/core/module_system/system/class_module_eventdispatcher_test.php", $strClass);
        file_put_contents(_realpath_."/core/module_system/system/interface_eventtest_listener.php", $strInterface);

        class_resourceloader::getInstance()->flushCache();
        class_classloader::getInstance()->flushCache();

        parent::setUp();


    }

    public function testInstantiations() {

        $objInstance = new class_module_eventdispatcher_test();

        $this->assertTrue(is_object($objInstance));

    }


    public function testInterfaceLoader() {
        $this->assertEquals(count(class_core_eventdispatcher::getEventListeners("interface_eventtest_listener")), 1);
    }


    public function testDemoEvents() {

        $this->assertTrue(class_core_eventdispatcher::notifyListeners("interface_eventtest_listener", "handleDemoEvent1", array("a1", "a2")));
        $this->assertTrue(!class_core_eventdispatcher::notifyListeners("interface_eventtest_listener", "handleDemoEvent2", array("a1", "a2", "a3")));

        $this->assertTrue(class_core_eventdispatcher::notifyListeners("interface_eventtest_listener", "handleDemoEvent3", array(true)));
        $this->assertTrue(!class_core_eventdispatcher::notifyListeners("interface_eventtest_listener", "handleDemoEvent3", array(false)));


        try {
            class_core_eventdispatcher::notifyListeners("interface_eventtest_listener", "handleDemoException", array("string 1"));
        }
        catch(class_exception $objEx) {
            $this->assertEquals($objEx->getMessage(), "string 1");
        }

    }

    protected function tearDown() {


        $objFilesystem = new class_filesystem();
        $objFilesystem->fileDelete("/core/module_system/system/class_module_eventdispatcher_test.php");
        $objFilesystem->fileDelete("/core/module_system/system/interface_eventtest_listener.php");


        class_resourceloader::getInstance()->flushCache();
        class_classloader::getInstance()->flushCache();

        parent::tearDown();
    }

}

