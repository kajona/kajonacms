<?php

namespace Kajona\System\Tests;

require_once __DIR__ . "../../../core/module_system/system/Testbase.php";

use Kajona\System\System\Classloader;
use Kajona\System\System\Pluginmanager;
use Kajona\System\System\Resourceloader;
use Kajona\System\System\Testbase;

class PluginmanagerTest extends Testbase
{


    protected function setUp()
    {

        $strClass = <<<PHP
<?php
            namespace Kajona\System\System;
            class PluginmanagerTestModel implements GenericPluginInterface  {

                public static function getExtensionName() {
                    return "core.pluginmanager.test";
                }

                public function __construct() {

                }
            }

PHP;
        $strClass2 = <<<PHP
<?php
            namespace Kajona\System\System;
            class PluginmanagerTestModel2 implements GenericPluginInterface  {

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
            namespace Kajona\System\System;
            class PluginmanagerTestModel3 implements GenericPluginInterface  {

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

        echo "Saving testfiles to " . Resourceloader::getInstance()->getCorePathForModule("module_system", true) . "/module_system/system/PluginmanagerTestModel.php\n";
        echo "Saving testfiles to " . Resourceloader::getInstance()->getCorePathForModule("module_system", true) . "/module_system/system/PluginmanagerTestModel2.php\n";
        echo "Saving testfiles to " . Resourceloader::getInstance()->getCorePathForModule("module_system", true) . "/module_system/system/PluginmanagerTestModel3.php\n";
        file_put_contents(Resourceloader::getInstance()->getCorePathForModule("module_system", true) . "/module_system/system/PluginmanagerTestModel.php", $strClass);
        file_put_contents(Resourceloader::getInstance()->getCorePathForModule("module_system", true) . "/module_system/system/PluginmanagerTestModel2.php", $strClass2);
        file_put_contents(Resourceloader::getInstance()->getCorePathForModule("module_system", true) . "/module_system/system/PluginmanagerTestModel3.php", $strClass3);

        Classloader::getInstance()->flushCache();

        parent::setUp();


    }


    public function testSearching()
    {
        $objManager = new Pluginmanager("core.pluginmanager.test");

        $arrInstances = $objManager->getPlugins();

        $this->assertEquals(count($arrInstances), 1);
        $this->assertTrue($arrInstances[0] instanceof \Kajona\System\System\PluginmanagerTestModel);

        $objManager = new Pluginmanager("core.pluginmanager2.test");

        $arrInstances = $objManager->getPlugins(array(1, 2));

        $this->assertEquals(count($arrInstances), 2);
        $this->assertTrue($arrInstances[0] instanceof \Kajona\System\System\PluginmanagerTestModel2);
        $objInstance = $arrInstances[0];
        $this->assertEquals($objInstance->arg1, 1);
        $this->assertEquals($objInstance->arg2, 2);

        $this->assertTrue($arrInstances[1] instanceof \Kajona\System\System\PluginmanagerTestModel3);
        $objInstance = $arrInstances[1];
        $this->assertEquals($objInstance->arg1, 1);
        $this->assertEquals($objInstance->arg2, 2);
    }

    public function testNegativeSearch()
    {
        $objManager = new Pluginmanager("core.pluginmanager.nonexisting");

        $arrInstances = $objManager->getPlugins();

        $this->assertEquals(count($arrInstances), 0);
    }


    protected function tearDown()
    {

        unlink(Resourceloader::getInstance()->getCorePathForModule("module_system", true) . "/module_system/system/PluginmanagerTestModel.php");
        unlink(Resourceloader::getInstance()->getCorePathForModule("module_system", true) . "/module_system/system/PluginmanagerTestModel2.php");
        unlink(Resourceloader::getInstance()->getCorePathForModule("module_system", true) . "/module_system/system/PluginmanagerTestModel3.php");

        Classloader::getInstance()->flushCache();

        parent::tearDown();
    }

}

