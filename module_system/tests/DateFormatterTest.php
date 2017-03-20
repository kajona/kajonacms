<?php

namespace Kajona\System\Tests;

use Kajona\System\System\Date;
use Kajona\System\System\DateFormatter;
use Kajona\System\System\Lang;

class DateFormatterTest extends Testbase
{
    protected $strCurrentLang;

    public function setUp()
    {
        parent::setUp();

        $this->strCurrentLang = Lang::getInstance()->getStrTextLanguage();
    }

    public function tearDown()
    {
        parent::tearDown();

        Lang::getInstance()->setStrTextLanguage($this->strCurrentLang);
    }

    public function testLongFormatDe()
    {
        Lang::getInstance()->setStrTextLanguage("de");

        $objDate = new Date(20170320000000);

        $this->assertEquals("20.03.2017 00:00:00", DateFormatter::toLongFormat($objDate));
    }

    public function testLongFormatEn()
    {
        Lang::getInstance()->setStrTextLanguage("en");

        $objDate = new Date(20170320000000);

        $this->assertEquals("03/20/2017 00:00:00", DateFormatter::toLongFormat($objDate));
    }

    public function testShortFormatDe()
    {
        Lang::getInstance()->setStrTextLanguage("de");

        $objDate = new Date(20170320000000);

        $this->assertEquals("20.03.2017", DateFormatter::toShortFormat($objDate));
    }

    public function testShortFormatEn()
    {
        Lang::getInstance()->setStrTextLanguage("en");

        $objDate = new Date(20170320000000);

        $this->assertEquals("03/20/2017", DateFormatter::toShortFormat($objDate));
    }

    public function testFormat()
    {
        $objDate = new Date(20170320000000);

        $this->assertEquals("2017-03-20T00:00:00+0100", DateFormatter::format(\DateTime::ISO8601, $objDate));
        $this->assertEquals("Mon, 20 Mar 2017 00:00:00 +0100", DateFormatter::format(\DateTime::RFC2822, $objDate));
    }

    public function testGetMonthNameDe()
    {
        Lang::getInstance()->setStrTextLanguage("de");

        $objDate = new Date(20170301000000);

        $this->assertEquals("März", DateFormatter::getMonthName($objDate));
    }

    public function testGetMonthNameEn()
    {
        Lang::getInstance()->setStrTextLanguage("en");

        $objDate = new Date(20170301000000);

        $this->assertEquals("March", DateFormatter::getMonthName($objDate));
    }

    public function testGetMonthShortNameDe()
    {
        Lang::getInstance()->setStrTextLanguage("de");

        $objDate = new Date(20170320000000);

        $this->assertEquals("Mär", DateFormatter::getMonthShortName($objDate));
    }

    public function testGetMonthShortNameEn()
    {
        Lang::getInstance()->setStrTextLanguage("en");

        $objDate = new Date(20170320000000);

        $this->assertEquals("Mar", DateFormatter::getMonthShortName($objDate));
    }

    public function testGetWeekdayNameDe()
    {
        Lang::getInstance()->setStrTextLanguage("de");

        $objDate = new Date(20170320000000);

        $this->assertEquals("Mo", DateFormatter::getWeekdayName($objDate));
    }

    public function testGetWeekdayNameEn()
    {
        Lang::getInstance()->setStrTextLanguage("en");

        $objDate = new Date(20170320000000);

        $this->assertEquals("Mu", DateFormatter::getWeekdayName($objDate));
    }

    public function testGetWeekdayShortNameDe()
    {
        Lang::getInstance()->setStrTextLanguage("de");

        $objDate = new Date(20170320000000);

        $this->assertEquals("M", DateFormatter::getWeekdayShortName($objDate));
    }

    public function testGetWeekdayShortNameEn()
    {
        Lang::getInstance()->setStrTextLanguage("en");

        $objDate = new Date(20170320000000);

        $this->assertEquals("M", DateFormatter::getWeekdayShortName($objDate));
    }
}
