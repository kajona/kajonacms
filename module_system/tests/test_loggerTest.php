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

    public function testNormalLogLevel() {

        $objLogger = class_logger::getInstance('test_logger_normal.log');

        $this->assertInstanceOf('class_logger', $objLogger);
        $this->assertEquals(2, $objLogger->getIntLogLevel());

        $objLogger->addLogRow("test log row 3", class_logger::$levelInfo);
        $objLogger->addLogRow("test log row 2", class_logger::$levelWarning);
        $objLogger->addLogRow("test log row 1", class_logger::$levelError);

        $this->assertFileExists(_realpath_._projectpath_."/log/test_logger_normal.log");
        $this->assertTrue(uniStripos($objLogger->getLogFileContent(), 'test log row 3') === false);
        $this->assertTrue(uniStripos($objLogger->getLogFileContent(), 'test log row 2') !== false);
        $this->assertTrue(uniStripos($objLogger->getLogFileContent(), 'test log row 1') !== false);
    }

    public function testCustomLogLevel() {

        class_carrier::getInstance()->getObjConfig()->setDebug('debuglogging_overwrite', array('test_logger_custom.log' => 1));

        $objLogger = class_logger::getInstance('test_logger_custom.log');

        $this->assertInstanceOf('class_logger', $objLogger);
        $this->assertEquals(1, $objLogger->getIntLogLevel());

        $objLogger->addLogRow("test log row 3", class_logger::$levelInfo);
        $objLogger->addLogRow("test log row 2", class_logger::$levelWarning);
        $objLogger->addLogRow("test log row 1", class_logger::$levelError);

        $this->assertFileExists(_realpath_._projectpath_."/log/test_logger_custom.log");
        $this->assertTrue(uniStripos($objLogger->getLogFileContent(), 'test log row 3') === false);
        $this->assertTrue(uniStripos($objLogger->getLogFileContent(), 'test log row 2') === false);
        $this->assertTrue(uniStripos($objLogger->getLogFileContent(), 'test log row 1') !== false);
    }

}

