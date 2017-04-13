<?php

namespace Kajona\System\Tests;

use Kajona\System\System\Carrier;
use Kajona\System\System\Filesystem;
use Kajona\System\System\Logger;
use Kajona\System\System\StringUtil;
use Psr\Log\LogLevel;

class LoggerTest extends Testbase
{

    public function testLogger()
    {

        $objLogger = Logger::getInstance("test.log");

        $objLogger->setIntLogLevel(Logger::$levelError);
        $objLogger->addLogRow("test log row 1", Logger::$levelInfo);

        $this->assertEquals($objLogger->getLogFileContent(), "");

        $objLogger->setIntLogLevel(Logger::$levelInfo);
        $objLogger->addLogRow("test log row 1", Logger::$levelInfo);

        $this->assertFileExists(_realpath_ . _projectpath_ . "/log/test.log");

        $this->assertTrue(StringUtil::indexOf($objLogger->getLogFileContent(), "test log row 1", false) !== false);


        $objFilesystem = new Filesystem();
        $objFilesystem->fileDelete(_projectpath_ . "/log/test.log");
        $this->assertFileNotExists(_realpath_ . _projectpath_ . "/log/test.log");

    }

    public function testNormalLogLevel()
    {

        $objLogger = Logger::getInstance('test_logger_normal.log');

        $this->assertInstanceOf('Kajona\\System\\System\\Logger', $objLogger);
        $this->assertEquals(2, $objLogger->getIntLogLevel());

        $objLogger->addLogRow("test log row 3", Logger::$levelInfo);
        $objLogger->addLogRow("test log row 2", Logger::$levelWarning);
        $objLogger->addLogRow("test log row 1", Logger::$levelError);

        $this->assertFileExists(_realpath_ . _projectpath_ . "/log/test_logger_normal.log");
        $this->assertTrue(StringUtil::indexOf($objLogger->getLogFileContent(), 'test log row 3', false) === false);
        $this->assertTrue(StringUtil::indexOf($objLogger->getLogFileContent(), 'test log row 2', false) !== false);
        $this->assertTrue(StringUtil::indexOf($objLogger->getLogFileContent(), 'test log row 1', false) !== false);
    }

    public function testCustomLogLevel()
    {
        Carrier::getInstance()->getObjConfig()->setDebug('debuglogging_overwrite', array('test_logger_custom.log' => 1));

        $objLogger = Logger::getInstance('test_logger_custom.log');

        $this->assertInstanceOf('Kajona\\System\\System\\Logger', $objLogger);
        $this->assertEquals(1, $objLogger->getIntLogLevel());

        $objLogger->addLogRow("test log row 3", Logger::$levelInfo);
        $objLogger->addLogRow("test log row 2", Logger::$levelWarning);
        $objLogger->addLogRow("test log row 1", Logger::$levelError);

        $this->assertFileExists(_realpath_ . _projectpath_ . "/log/test_logger_custom.log");
        $this->assertTrue(StringUtil::indexOf($objLogger->getLogFileContent(), 'test log row 3', false) === false);
        $this->assertTrue(StringUtil::indexOf($objLogger->getLogFileContent(), 'test log row 2', false) === false);
        $this->assertTrue(StringUtil::indexOf($objLogger->getLogFileContent(), 'test log row 1', false) !== false);
    }

    public function testPsr()
    {
        $objLogger = Logger::getInstance('test_logger_psr.log');

        $objLogger->emergency('foo');
        $objLogger->alert('foo');
        $objLogger->critical('foo');
        $objLogger->error('foo');
        $objLogger->warning('foo');
        $objLogger->notice('foo');
        $objLogger->info('foo');
        $objLogger->debug('foo');
        $objLogger->log(LogLevel::INFO, 'foo');

        $this->assertFileExists(_realpath_ . _projectpath_ . "/log/test_logger_psr.log");
    }
}

