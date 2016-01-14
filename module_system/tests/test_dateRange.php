<?php

require_once (__DIR__."/../../module_system/system/class_testbase.php");

class test_dateRange extends class_testbase
{
    public function testGetDateRangeDay()
    {
        // without hours
        $arrRanges = class_date_range::getDateRange(new class_date(20160101000000), new class_date(20160104000000), class_date_period_enum::DAY());

        $this->assertEquals(3, count($arrRanges));
        $this->assertEquals(20160101000000, $arrRanges[0][0]->getLongTimestamp());
        $this->assertEquals(20160101235959, $arrRanges[0][1]->getLongTimestamp());
        $this->assertEquals(20160102000000, $arrRanges[1][0]->getLongTimestamp());
        $this->assertEquals(20160102235959, $arrRanges[1][1]->getLongTimestamp());
        $this->assertEquals(20160103000000, $arrRanges[2][0]->getLongTimestamp());
        $this->assertEquals(20160103235959, $arrRanges[2][1]->getLongTimestamp());

        // with hours
        $arrRanges = class_date_range::getDateRange(new class_date(20160101140000), new class_date(20160104140000), class_date_period_enum::DAY());

        $this->assertEquals(3, count($arrRanges));
        $this->assertEquals(20160101140000, $arrRanges[0][0]->getLongTimestamp());
        $this->assertEquals(20160102135959, $arrRanges[0][1]->getLongTimestamp());
        $this->assertEquals(20160102140000, $arrRanges[1][0]->getLongTimestamp());
        $this->assertEquals(20160103135959, $arrRanges[1][1]->getLongTimestamp());
        $this->assertEquals(20160103140000, $arrRanges[2][0]->getLongTimestamp());
        $this->assertEquals(20160104135959, $arrRanges[2][1]->getLongTimestamp());

        // with end date hours > start date hours
        $arrRanges = class_date_range::getDateRange(new class_date(20160101140000), new class_date(20160104180000), class_date_period_enum::DAY());

        $this->assertEquals(3, count($arrRanges));
        $this->assertEquals(20160101140000, $arrRanges[0][0]->getLongTimestamp());
        $this->assertEquals(20160102135959, $arrRanges[0][1]->getLongTimestamp());
        $this->assertEquals(20160102140000, $arrRanges[1][0]->getLongTimestamp());
        $this->assertEquals(20160103135959, $arrRanges[1][1]->getLongTimestamp());
        $this->assertEquals(20160103140000, $arrRanges[2][0]->getLongTimestamp());
        $this->assertEquals(20160104135959, $arrRanges[2][1]->getLongTimestamp());

        // with end date hours < start date hours
        $arrRanges = class_date_range::getDateRange(new class_date(20160101140000), new class_date(20160104120000), class_date_period_enum::DAY());

        $this->assertEquals(2, count($arrRanges));
        $this->assertEquals(20160101140000, $arrRanges[0][0]->getLongTimestamp());
        $this->assertEquals(20160102135959, $arrRanges[0][1]->getLongTimestamp());
        $this->assertEquals(20160102140000, $arrRanges[1][0]->getLongTimestamp());
        $this->assertEquals(20160103135959, $arrRanges[1][1]->getLongTimestamp());
    }

    public function testGetDateRangeWeek()
    {
        $arrRanges = class_date_range::getDateRange(new class_date(20160101000000), new class_date(20160106000000), class_date_period_enum::WEEK());

        $this->assertEquals(0, count($arrRanges));

        $arrRanges = class_date_range::getDateRange(new class_date(20160101000000), new class_date(20160107000000), class_date_period_enum::WEEK());

        $this->assertEquals(0, count($arrRanges));

        $arrRanges = class_date_range::getDateRange(new class_date(20160101000000), new class_date(20160108000000), class_date_period_enum::WEEK());

        $this->assertEquals(1, count($arrRanges));
        $this->assertEquals(20160101000000, $arrRanges[0][0]->getLongTimestamp());
        $this->assertEquals(20160107235959, $arrRanges[0][1]->getLongTimestamp());

        $arrRanges = class_date_range::getDateRange(new class_date(20160101000000), new class_date(20160109000000), class_date_period_enum::WEEK());

        $this->assertEquals(1, count($arrRanges));
        $this->assertEquals(20160101000000, $arrRanges[0][0]->getLongTimestamp());
        $this->assertEquals(20160107235959, $arrRanges[0][1]->getLongTimestamp());
    }

    public function testGetDateRangeMonth()
    {
        $arrRanges = class_date_range::getDateRange(new class_date(20160101000000), new class_date(20160130000000), class_date_period_enum::MONTH());

        $this->assertEquals(0, count($arrRanges));

        $arrRanges = class_date_range::getDateRange(new class_date(20160101000000), new class_date(20160131000000), class_date_period_enum::MONTH());

        $this->assertEquals(0, count($arrRanges));

        $arrRanges = class_date_range::getDateRange(new class_date(20160101000000), new class_date(20160201000000), class_date_period_enum::MONTH());

        $this->assertEquals(1, count($arrRanges));
        $this->assertEquals(20160101000000, $arrRanges[0][0]->getLongTimestamp());
        $this->assertEquals(20160131235959, $arrRanges[0][1]->getLongTimestamp());

        $arrRanges = class_date_range::getDateRange(new class_date(20160131000000), new class_date(20160301000000), class_date_period_enum::MONTH());

        $this->assertEquals(1, count($arrRanges));
        $this->assertEquals(20160131000000, $arrRanges[0][0]->getLongTimestamp());
        $this->assertEquals(20160228235959, $arrRanges[0][1]->getLongTimestamp());

        $arrRanges = class_date_range::getDateRange(new class_date(20151201000000), new class_date(20160101000000), class_date_period_enum::MONTH());

        $this->assertEquals(1, count($arrRanges));
        $this->assertEquals(20151201000000, $arrRanges[0][0]->getLongTimestamp());
        $this->assertEquals(20151231235959, $arrRanges[0][1]->getLongTimestamp());
    }
}
