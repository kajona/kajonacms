<?php

require_once (__DIR__."/../../module_system/system/class_testbase.php");

class class_test_pluginmanager extends class_testbase  {


    protected function setUp() {

        $strClass = <<<PHP
<?php
            class class_module_pluginmanager_test implements interface_generic_plugin  {

                public static function getExtensionName() {
                    return "core.pluginmanager.test";
                }

                public function __construct() {

                }
            }

PHP;
        $strClass2 = <<<PHP
<?php
            class class_module_pluginmanager2_test implements interface_generic_plugin  {

                public \$arg1;
                public \$arg2;

                public static function getExtensionName() {
                    return "core.pluginmanager2.test";
                }

                public function __construct(\$arg1, \$arg2) {
                    \$this->arg1 = \$arg1;
                    \$this->arg2 = \$arg2;
                }
            }

PHP;

        $strClass3 = <<<PHP
<?php
            class class_module_pluginmanager3_test implements interface_generic_plugin  {

                public \$arg1;
                public \$arg2;

                public static function getExtensionName() {
                    return "core.pluginmanager2.test";
                }

                public function __construct(\$arg1, \$arg2) {
                    \$this->arg1 = \$arg1;
                    \$this->arg2 = \$arg2;
                }
            }

PHP;

        echo "Saving testfiles to ".class_resourceloader::getInstance()->getCorePathForModule("module_system", true)."/module_system/system/class_module_pluginmanager_test.php\n";
        echo "Saving testfiles to ".class_resourceloader::getInstance()->getCorePathForModule("module_system", true)."/module_system/system/class_module_pluginmanager2_test.php\n";
        echo "Saving testfiles to ".class_resourceloader::getInstance()->getCorePathForModule("module_system", true)."/module_system/system/class_module_pluginmanager3_test.php\n";
        file_put_contents(class_resourceloader::getInstance()->getCorePathForModule("module_system", true)."/module_system/system/class_module_pluginmanager_test.php", $strClass);
        file_put_contents(class_resourceloader::getInstance()->getCorePathForModule("module_system", true)."/module_system/system/class_module_pluginmanager2_test.php", $strClass2);
        file_put_contents(class_resourceloader::getInstance()->getCorePathForModule("module_system", true)."/module_system/system/class_module_pluginmanager3_test.php", $strClass3);

        class_classloader::getInstance()->flushCache();

        parent::setUp();


    }


    public function testSearching() {
        $objManager = new class_pluginmanager("core.pluginmanager.test");

        $arrInstances = $objManager->getPlugins();

        $this->assertEquals(count($arrInstances), 1);
        $this->assertTrue($arrInstances[0] instanceof class_module_pluginmanager_test);

        $objManager = new class_pluginmanager("core.pluginmanager2.test");

        $arrInstances = $objManager->getPlugins(array(1, 2));

        $this->assertEquals(count($arrInstances), 2);
        $this->assertTrue($arrInstances[0] instanceof class_module_pluginmanager2_test);
        $objInstance = $arrInstances[0];
        $this->assertEquals($objInstance->arg1, 1);
        $this->assertEquals($objInstance->arg2, 2);

        $this->assertTrue($arrInstances[1] instanceof class_module_pluginmanager3_test);
        $objInstance = $arrInstances[1];
        $this->assertEquals($objInstance->arg1, 1);
        $this->assertEquals($objInstance->arg2, 2);
    }

    public function testNegativeSearch() {
        $objManager = new class_pluginmanager("core.pluginmanager.nonexisting");

        $arrInstances = $objManager->getPlugins();

        $this->assertEquals(count($arrInstances), 0);
    }


    protected function tearDown() {

        unlink(class_resourceloader::getInstance()->getCorePathForModule("module_system", true)."/module_system/system/class_module_pluginmanager_test.php");
        unlink(class_resourceloader::getInstance()->getCorePathForModule("module_system", true)."/module_system/system/class_module_pluginmanager2_test.php");
        unlink(class_resourceloader::getInstance()->getCorePathForModule("module_system", true)."/module_system/system/class_module_pluginmanager3_test.php");

        class_classloader::getInstance()->flushCache();

        parent::tearDown();
    }

}

