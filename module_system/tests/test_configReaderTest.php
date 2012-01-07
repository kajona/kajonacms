<?php

require_once (__DIR__."/../../module_system/system/class_testbase.php");

class class_test_configReaderTest extends class_testbase  {





    public function testPlainConfigReader() {
        $strSimpleConfigFile = <<<TXT
<?php
        \$config["testkey1"] = "testval1";
        \$config["testkey2"] = "testval2";

TXT;

        file_put_contents(_corepath_."/module_system/system/config/test1.php", $strSimpleConfigFile);

        $this->assertFileExists(_corepath_."/module_system/system/config/test1.php");

        $objConfig = class_config::getInstance("test1.php");

        $this->assertEquals("testval1", $objConfig->getConfig("testkey1"));
        $this->assertEquals("testval2", $objConfig->getConfig("testkey2"));

        $objFilesystem = new class_filesystem();
        $objFilesystem->fileDelete("/core/module_system/system/config/test1.php");

        $this->assertFileNotExists(_corepath_."/module_system/system/config/test1.php");

    }




    public function testMergedConfigReader() {
        $strSimpleConfigFile = <<<TXT
<?php
        \$config["testkey1"] = "testval1";
        \$config["testkey2"] = "testval2";

TXT;

        $strMergingConfigFile = <<<TXT
<?php
        \$config["testkey2"] = "otherval";

TXT;


        file_put_contents(_corepath_."/module_system/system/config/test2.php", $strSimpleConfigFile);
        file_put_contents(_realpath_._projectpath_."/system/config/test2.php", $strMergingConfigFile);

        $this->assertFileExists(_corepath_."/module_system/system/config/test2.php");
        $this->assertFileExists(_realpath_._projectpath_."/system/config/test2.php");

        $objConfig = class_config::getInstance("test2.php");

        $this->assertEquals("testval1", $objConfig->getConfig("testkey1"));
        $this->assertEquals("otherval", $objConfig->getConfig("testkey2"));

        $objFilesystem = new class_filesystem();
        $objFilesystem->fileDelete("/core/module_system/system/config/test2.php");
        $objFilesystem->fileDelete(_projectpath_."/system/config/test2.php");

        $this->assertFileNotExists(_corepath_."/module_system/system/config/test2.php");
        $this->assertFileNotExists(_realpath_._projectpath_."/system/config/test2.php");
    }



    public function testStaticReader() {

        $this->assertEquals(class_config::readPlainConfigsFromFilesystem("https_header"), "HTTPS");
    }

}

