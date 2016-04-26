<?php

namespace Kajona\System\Tests;

use Kajona\System\System\Classloader;
use Kajona\System\System\Config;
use Kajona\System\System\Filesystem;
use Kajona\System\System\Resourceloader;

class ConfigReaderTest extends Testbase
{


    public function testPlainConfigReader()
    {
        $strSimpleConfigFile = <<<TXT
<?php
        \$config["testkey1"] = "testval1";
        \$config["testkey2"] = "testval2";

TXT;

        file_put_contents(Resourceloader::getInstance()->getCorePathForModule("module_system", true) . "/module_system/system/config/test1.php", $strSimpleConfigFile);
        Classloader::getInstance()->flushCache();

        $this->assertFileExists(Resourceloader::getInstance()->getCorePathForModule("module_system", true) . "/module_system/system/config/test1.php");

        $objConfig = Config::getInstance("module_system", "test1.php");

        $this->assertEquals("testval1", $objConfig->getConfig("testkey1"));
        $this->assertEquals("testval2", $objConfig->getConfig("testkey2"));

        $objFilesystem = new Filesystem();
        $objFilesystem->fileDelete(Resourceloader::getInstance()->getCorePathForModule("module_system") . "/module_system/system/config/test1.php");

        Classloader::getInstance()->flushCache();
        $this->assertFileNotExists(Resourceloader::getInstance()->getCorePathForModule("module_system", true) . "/module_system/system/config/test1.php");

    }


    public function testMergedConfigReader()
    {
        $strSimpleConfigFile = <<<TXT
<?php
        \$config["testkey1"] = "testval1";
        \$config["testkey2"] = "testval2";

TXT;

        $strMergingConfigFile = <<<TXT
<?php


        \$config["testkey2"] = "otherval";

TXT;


        file_put_contents(Resourceloader::getInstance()->getCorePathForModule("module_system", true) . "/module_system/system/config/test2.php", $strSimpleConfigFile);
        file_put_contents(_realpath_ . _projectpath_ . "/module_system/system/config/test2.php", $strMergingConfigFile);
        Classloader::getInstance()->flushCache();

        $this->assertFileExists(Resourceloader::getInstance()->getCorePathForModule("module_system", true) . "/module_system/system/config/test2.php");
        $this->assertFileExists(_realpath_ . _projectpath_ . "/module_system/system/config/test2.php");

        $objConfig = Config::getInstance("module_system", "test2.php");

        $this->assertEquals("testval1", $objConfig->getConfig("testkey1"));
        $this->assertEquals("otherval", $objConfig->getConfig("testkey2"));

        $objFilesystem = new Filesystem();
        $objFilesystem->fileDelete(Resourceloader::getInstance()->getCorePathForModule("module_system") . "/module_system/system/config/test2.php");
        $objFilesystem->fileDelete(_projectpath_ . "/module_system/system/config/test2.php");
        Classloader::getInstance()->flushCache();

        $this->assertFileNotExists(Resourceloader::getInstance()->getCorePathForModule("module_system", true) . "/module_system/system/config/test2.php");
        $this->assertFileNotExists(_realpath_ . _projectpath_ . "/module_system/system/config/test2.php");
    }


    public function testStaticReader()
    {

        $this->assertEquals(Config::readPlainConfigsFromFilesystem("https_header"), "HTTPS");
    }

}

