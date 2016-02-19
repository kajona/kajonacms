<?php

namespace Kajona\System\Tests;
require_once __DIR__."/../../../core/module_system/system/Testbase.php";
use Kajona\System\System\Carrier;
use Kajona\System\System\Filesystem;
use Kajona\System\System\Logger;
use Kajona\System\System\Testbase;

class LoggerTest extends Testbase  {

    public function testLogger() {

        echo "test logger...\n";

        $objLogger = Logger::getInstance("test.log");

        $objLogger->setIntLogLevel(Logger::$levelError);
        $objLogger->addLogRow("test log row 1", Logger::$levelInfo);

        $this->assertEquals($objLogger->getLogFileContent(), "");

        $objLogger->setIntLogLevel(Logger::$levelInfo);
        $objLogger->addLogRow("test log row 1", Logger::$levelInfo);

        $this->assertFileExists(_realpath_._projectpath_."/log/test.log");

        $this->assertTrue(uniStripos($objLogger->getLogFileContent(), "test log row 1") !== false);


        $objFilesystem = new Filesystem();
        $objFilesystem->fileDelete(_projectpath_."/log/test.log");
        $this->assertFileNotExists(_realpath_._projectpath_."/log/test.log");

    }

    public function testNormalLogLevel() {

        $objLogger = Logger::getInstance('test_logger_normal.log');

        $this->assertInstanceOf('Kajona\\System\\System\\Logger', $objLogger);
        $this->assertEquals(2, $objLogger->getIntLogLevel());

        $objLogger->addLogRow("test log row 3", Logger::$levelInfo);
        $objLogger->addLogRow("test log row 2", Logger::$levelWarning);
        $objLogger->addLogRow("test log row 1", Logger::$levelError);

        $this->assertFileExists(_realpath_._projectpath_."/log/test_logger_normal.log");
        $this->assertTrue(uniStripos($objLogger->getLogFileContent(), 'test log row 3') === false);
        $this->assertTrue(uniStripos($objLogger->getLogFileContent(), 'test log row 2') !== false);
        $this->assertTrue(uniStripos($objLogger->getLogFileContent(), 'test log row 1') !== false);
    }

    public function testCustomLogLevel() {

        Carrier::getInstance()->getObjConfig()->setDebug('debuglogging_overwrite', array('test_logger_custom.log' => 1));

        $objLogger = Logger::getInstance('test_logger_custom.log');

        $this->assertInstanceOf('Kajona\\System\\System\\Logger', $objLogger);
        $this->assertEquals(1, $objLogger->getIntLogLevel());

        $objLogger->addLogRow("test log row 3", Logger::$levelInfo);
        $objLogger->addLogRow("test log row 2", Logger::$levelWarning);
        $objLogger->addLogRow("test log row 1", Logger::$levelError);

        $this->assertFileExists(_realpath_._projectpath_."/log/test_logger_custom.log");
        $this->assertTrue(uniStripos($objLogger->getLogFileContent(), 'test log row 3') === false);
        $this->assertTrue(uniStripos($objLogger->getLogFileContent(), 'test log row 2') === false);
        $this->assertTrue(uniStripos($objLogger->getLogFileContent(), 'test log row 1') !== false);
    }

}

