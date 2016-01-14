<?php

require_once (__DIR__."/../../module_system/system/class_testbase.php");

class class_test_configReaderTest extends class_testbase  {





    public function testPlainConfigReader() {
        $strSimpleConfigFile = <<<TXT
<?php
        \$config["testkey1"] = "testval1";
        \$config["testkey2"] = "testval2";

TXT;

        file_put_contents(class_resourceloader::getInstance()->getCorePathForModule("module_system", true)."/module_system/system/config/test1.php", $strSimpleConfigFile);
        class_classloader::getInstance()->flushCache();

        $this->assertFileExists(class_resourceloader::getInstance()->getCorePathForModule("module_system", true)."/module_system/system/config/test1.php");

        $objConfig = class_config::getInstance("test1.php");

        $this->assertEquals("testval1", $objConfig->getConfig("testkey1"));
        $this->assertEquals("testval2", $objConfig->getConfig("testkey2"));

        $objFilesystem = new class_filesystem();
        $objFilesystem->fileDelete(class_resourceloader::getInstance()->getCorePathForModule("module_system")."/module_system/system/config/test1.php");

        class_classloader::getInstance()->flushCache();
        $this->assertFileNotExists(class_resourceloader::getInstance()->getCorePathForModule("module_system", true)."/module_system/system/config/test1.php");

    }




    public function testMergedConfigReader() {
        $strSimpleConfigFile = <<<TXT
<?php
        \$config["testkey1"] = "testval1";
        \$config["testkey2"] = "testval2";

TXT;

        $strMergingConfigFile = <<<TXT
<?php

if(is_dir(__DIR__."/../../../core/module_system/")) {
  include __DIR__.'/../../../core/module_system/system/config/test2.php';
}
else {
  require_once 'phar://'.__DIR__.'/../../../core/module_system.phar/system/config/test2.php';
}

        \$config["testkey2"] = "otherval";

TXT;


        file_put_contents(class_resourceloader::getInstance()->getCorePathForModule("module_system", true)."/module_system/system/config/test2.php", $strSimpleConfigFile);
        file_put_contents(_realpath_._projectpath_."/system/config/test2.php", $strMergingConfigFile);
        class_classloader::getInstance()->flushCache();

        $this->assertFileExists(class_resourceloader::getInstance()->getCorePathForModule("module_system", true)."/module_system/system/config/test2.php");
        $this->assertFileExists(_realpath_._projectpath_."/system/config/test2.php");

        $objConfig = class_config::getInstance("test2.php");

        $this->assertEquals("testval1", $objConfig->getConfig("testkey1"));
        $this->assertEquals("otherval", $objConfig->getConfig("testkey2"));

        $objFilesystem = new class_filesystem();
        $objFilesystem->fileDelete(class_resourceloader::getInstance()->getCorePathForModule("module_system")."/module_system/system/config/test2.php");
        $objFilesystem->fileDelete(_projectpath_."/system/config/test2.php");
        class_classloader::getInstance()->flushCache();

        $this->assertFileNotExists(class_resourceloader::getInstance()->getCorePathForModule("module_system", true)."/module_system/system/config/test2.php");
        $this->assertFileNotExists(_realpath_._projectpath_."/system/config/test2.php");
    }



    public function testStaticReader() {

        $this->assertEquals(class_config::readPlainConfigsFromFilesystem("https_header"), "HTTPS");
    }

}

