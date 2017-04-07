<?php

namespace Kajona\System\Tests;

use Kajona\System\System\Carrier;
use Kajona\System\System\Filesystem;
use Kajona\System\System\Logger;
use Kajona\System\System\StringUtil;
use Psr\Log\LoggerInterface;
use Psr\Log\LogLevel;

class LoggerTest extends Testbase
{
    public function testLogger()
    {
        $objLogger = Logger::getInstance("test.log");
        $strFile = _realpath_ . _projectpath_ . "/log/test-" . date('Y-m-d') . ".log";

        file_put_contents($strFile, "");

        $objLogger->debug("test log row debug");
        $objLogger->info("test log row info");
        $objLogger->notice("test log row notice");
        $objLogger->warning("test log row warning");
        $objLogger->error("test log row error");
        $objLogger->critical("test log row critical");
        $objLogger->alert("test log row alert");
        $objLogger->emergency("test log row emergency");
        $objLogger->log(LogLevel::INFO, "test log row emergency");

        $strContent = file_get_contents($strFile);

        var_dump($strContent);

        $this->assertTrue(StringUtil::indexOf($strContent, "test log row 1", false) !== false);
    }
}

