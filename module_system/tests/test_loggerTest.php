<?php

require_once (__DIR__."/../../module_system/system/class_testbase.php");

class class_test_loggerTest extends class_testbase  {





    public function testLogger() {

        echo "test logger...\n";

        $objLogger = class_logger::getInstance("test.log");

        $objLogger->setIntLogLevel(class_logger::$levelError);
        $objLogger->addLogRow("test log row 1", class_logger::$levelInfo);

        $this->assertEquals($objLogger->getLogFileContent(), "");

        $objLogger->setIntLogLevel(class_logger::$levelInfo);
        $objLogger->addLogRow("test log row 1", class_logger::$levelInfo);

        $this->assertFileExists(_realpath_._projectpath_."/log/test.log");

        $this->assertTrue(uniStripos($objLogger->getLogFileContent(), "test log row 1") !== false);


        $objFilesystem = new class_filesystem();
        $objFilesystem->fileDelete(_projectpath_."/log/test.log");
        $this->assertFileNotExists(_realpath_._projectpath_."/log/test.log");

    }




}

