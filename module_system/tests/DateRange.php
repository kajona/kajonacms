<?php

namespace Kajona\System\Tests;

require_once __DIR__ . "/../../../core/module_system/system/Testbase.php";

use Kajona\System\System\Date;
use Kajona\System\System\DatePeriodEnum;
use Kajona\System\System\DateRange;
use Kajona\System\System\Testbase;

class DateRangeTest extends Testbase
{
    public function testGetDateRangeDay()
    {
        // without hours
        $arrRanges = DateRange::getDateRange(new Date(20160101000000), new Date(20160104000000), DatePeriodEnum::DAY());

        $this->assertEquals(3, count($arrRanges));
        $this->assertEquals(20160101000000, $arrRanges[0][0]->getLongTimestamp());
        $this->assertEquals(20160101235959, $arrRanges[0][1]->getLongTimestamp());
        $this->assertEquals(20160102000000, $arrRanges[1][0]->getLongTimestamp());
        $this->assertEquals(20160102235959, $arrRanges[1][1]->getLongTimestamp());
        $this->assertEquals(20160103000000, $arrRanges[2][0]->getLongTimestamp());
        $this->assertEquals(20160103235959, $arrRanges[2][1]->getLongTimestamp());

        // with hours
        $arrRanges = DateRange::getDateRange(new Date(20160101140000), new Date(20160104140000), DatePeriodEnum::DAY());

        $this->assertEquals(3, count($arrRanges));
        $this->assertEquals(20160101140000, $arrRanges[0][0]->getLongTimestamp());
        $this->assertEquals(20160102135959, $arrRanges[0][1]->getLongTimestamp());
        $this->assertEquals(20160102140000, $arrRanges[1][0]->getLongTimestamp());
        $this->assertEquals(20160103135959, $arrRanges[1][1]->getLongTimestamp());
        $this->assertEquals(20160103140000, $arrRanges[2][0]->getLongTimestamp());
        $this->assertEquals(20160104135959, $arrRanges[2][1]->getLongTimestamp());

        // with end date hours > start date hours
        $arrRanges = DateRange::getDateRange(new Date(20160101140000), new Date(20160104180000), DatePeriodEnum::DAY());

        $this->assertEquals(3, count($arrRanges));
        $this->assertEquals(20160101140000, $arrRanges[0][0]->getLongTimestamp());
        $this->assertEquals(20160102135959, $arrRanges[0][1]->getLongTimestamp());
        $this->assertEquals(20160102140000, $arrRanges[1][0]->getLongTimestamp());
        $this->assertEquals(20160103135959, $arrRanges[1][1]->getLongTimestamp());
        $this->assertEquals(20160103140000, $arrRanges[2][0]->getLongTimestamp());
        $this->assertEquals(20160104135959, $arrRanges[2][1]->getLongTimestamp());

        // with end date hours < start date hours
        $arrRanges = DateRange::getDateRange(new Date(20160101140000), new Date(20160104120000), DatePeriodEnum::DAY());

        $this->assertEquals(2, count($arrRanges));
        $this->assertEquals(20160101140000, $arrRanges[0][0]->getLongTimestamp());
        $this->assertEquals(20160102135959, $arrRanges[0][1]->getLongTimestamp());
        $this->assertEquals(20160102140000, $arrRanges[1][0]->getLongTimestamp());
        $this->assertEquals(20160103135959, $arrRanges[1][1]->getLongTimestamp());
    }

    public function testGetDateRangeWeek()
    {
        $arrRanges = DateRange::getDateRange(new Date(20160101000000), new Date(20160106000000), DatePeriodEnum::WEEK());

        $this->assertEquals(0, count($arrRanges));

        $arrRanges = DateRange::getDateRange(new Date(20160101000000), new Date(20160107000000), DatePeriodEnum::WEEK());

        $this->assertEquals(0, count($arrRanges));

        $arrRanges = DateRange::getDateRange(new Date(20160101000000), new Date(20160108000000), DatePeriodEnum::WEEK());

        $this->assertEquals(1, count($arrRanges));
        $this->assertEquals(20160101000000, $arrRanges[0][0]->getLongTimestamp());
        $this->assertEquals(20160107235959, $arrRanges[0][1]->getLongTimestamp());

        $arrRanges = DateRange::getDateRange(new Date(20160101000000), new Date(20160109000000), DatePeriodEnum::WEEK());

        $this->assertEquals(1, count($arrRanges));
        $this->assertEquals(20160101000000, $arrRanges[0][0]->getLongTimestamp());
        $this->assertEquals(20160107235959, $arrRanges[0][1]->getLongTimestamp());
    }

    public function testGetDateRangeMonth()
    {
        $arrRanges = DateRange::getDateRange(new Date(20160101000000), new Date(20160130000000), DatePeriodEnum::MONTH());

        $this->assertEquals(0, count($arrRanges));

        $arrRanges = DateRange::getDateRange(new Date(20160101000000), new Date(20160131000000), DatePeriodEnum::MONTH());

        $this->assertEquals(0, count($arrRanges));

        $arrRanges = DateRange::getDateRange(new Date(20160101000000), new Date(20160201000000), DatePeriodEnum::MONTH());

        $this->assertEquals(1, count($arrRanges));
        $this->assertEquals(20160101000000, $arrRanges[0][0]->getLongTimestamp());
        $this->assertEquals(20160131235959, $arrRanges[0][1]->getLongTimestamp());

        $arrRanges = DateRange::getDateRange(new Date(20160131000000), new Date(20160301000000), DatePeriodEnum::MONTH());

        $this->assertEquals(1, count($arrRanges));
        $this->assertEquals(20160131000000, $arrRanges[0][0]->getLongTimestamp());
        $this->assertEquals(20160228235959, $arrRanges[0][1]->getLongTimestamp());

        $arrRanges = DateRange::getDateRange(new Date(20151201000000), new Date(20160101000000), DatePeriodEnum::MONTH());

        $this->assertEquals(1, count($arrRanges));
        $this->assertEquals(20151201000000, $arrRanges[0][0]->getLongTimestamp());
        $this->assertEquals(20151231235959, $arrRanges[0][1]->getLongTimestamp());
    }
}
