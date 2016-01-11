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

        // cross month border
        $arrRanges = class_date_range::getDateRange(new class_date(20160129140000), new class_date(20160202120000), class_date_period_enum::DAY());

        $this->assertEquals(3, count($arrRanges));
        $this->assertEquals(20160129140000, $arrRanges[0][0]->getLongTimestamp());
        $this->assertEquals(20160130135959, $arrRanges[0][1]->getLongTimestamp());
        $this->assertEquals(20160130140000, $arrRanges[1][0]->getLongTimestamp());
        $this->assertEquals(20160131135959, $arrRanges[1][1]->getLongTimestamp());
        $this->assertEquals(20160131140000, $arrRanges[2][0]->getLongTimestamp());
        $this->assertEquals(20160201135959, $arrRanges[2][1]->getLongTimestamp());
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


        $arrRanges = class_date_range::getDateRange(new class_date(20160101000000), new class_date(20160215000000), class_date_period_enum::WEEK());

        $this->assertEquals(6, count($arrRanges));
        $this->assertEquals(20160101000000, $arrRanges[0][0]->getLongTimestamp());
        $this->assertEquals(20160107235959, $arrRanges[0][1]->getLongTimestamp());
        $this->assertEquals(20160108000000, $arrRanges[1][0]->getLongTimestamp());
        $this->assertEquals(20160114235959, $arrRanges[1][1]->getLongTimestamp());
        $this->assertEquals(20160115000000, $arrRanges[2][0]->getLongTimestamp());
        $this->assertEquals(20160121235959, $arrRanges[2][1]->getLongTimestamp());
        $this->assertEquals(20160122000000, $arrRanges[3][0]->getLongTimestamp());
        $this->assertEquals(20160128235959, $arrRanges[3][1]->getLongTimestamp());
        $this->assertEquals(20160129000000, $arrRanges[4][0]->getLongTimestamp());
        $this->assertEquals(20160204235959, $arrRanges[4][1]->getLongTimestamp());
        $this->assertEquals(20160205000000, $arrRanges[5][0]->getLongTimestamp());
        $this->assertEquals(20160211235959, $arrRanges[5][1]->getLongTimestamp());
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

        $arrRanges = class_date_range::getDateRange(new class_date(20151201000000), new class_date(20161101000000), class_date_period_enum::MONTH());

        $this->assertEquals(11, count($arrRanges));
        $this->assertEquals(20151201000000, $arrRanges[0][0]->getLongTimestamp());
        $this->assertEquals(20151231235959, $arrRanges[0][1]->getLongTimestamp());
        $this->assertEquals(20160101000000, $arrRanges[1][0]->getLongTimestamp());
        $this->assertEquals(20160131235959, $arrRanges[1][1]->getLongTimestamp());
        $this->assertEquals(20160201000000, $arrRanges[2][0]->getLongTimestamp());
        $this->assertEquals(20160229235959, $arrRanges[2][1]->getLongTimestamp());
        $this->assertEquals(20160301000000, $arrRanges[3][0]->getLongTimestamp());
        $this->assertEquals(20160331235959, $arrRanges[3][1]->getLongTimestamp());
        $this->assertEquals(20160401000000, $arrRanges[4][0]->getLongTimestamp());
        $this->assertEquals(20160430235959, $arrRanges[4][1]->getLongTimestamp());
        $this->assertEquals(20160501000000, $arrRanges[5][0]->getLongTimestamp());
        $this->assertEquals(20160531235959, $arrRanges[5][1]->getLongTimestamp());
        $this->assertEquals(20160601000000, $arrRanges[6][0]->getLongTimestamp());
        $this->assertEquals(20160630235959, $arrRanges[6][1]->getLongTimestamp());
        $this->assertEquals(20160701000000, $arrRanges[7][0]->getLongTimestamp());
        $this->assertEquals(20160731235959, $arrRanges[7][1]->getLongTimestamp());
        $this->assertEquals(20160801000000, $arrRanges[8][0]->getLongTimestamp());
        $this->assertEquals(20160831235959, $arrRanges[8][1]->getLongTimestamp());
        $this->assertEquals(20160901000000, $arrRanges[9][0]->getLongTimestamp());
        $this->assertEquals(20160930235959, $arrRanges[9][1]->getLongTimestamp());
        $this->assertEquals(20161001000000, $arrRanges[10][0]->getLongTimestamp());
        $this->assertEquals(20161031235959, $arrRanges[10][1]->getLongTimestamp());

        $arrRanges = class_date_range::getDateRange(new class_date(20150201000000), new class_date(20150227000000), class_date_period_enum::MONTH());

        $this->assertEquals(0, count($arrRanges));

        $arrRanges = class_date_range::getDateRange(new class_date(20150201000000), new class_date(20150228000000), class_date_period_enum::MONTH());

        $this->assertEquals(0, count($arrRanges));

        $arrRanges = class_date_range::getDateRange(new class_date(20150201000000), new class_date(20150301000000), class_date_period_enum::MONTH());

        $this->assertEquals(1, count($arrRanges));
        $this->assertEquals(20150201000000, $arrRanges[0][0]->getLongTimestamp());
        $this->assertEquals(20150228235959, $arrRanges[0][1]->getLongTimestamp());

        $arrRanges = class_date_range::getDateRange(new class_date(20160201000000), new class_date(20160227000000), class_date_period_enum::MONTH());

        $this->assertEquals(0, count($arrRanges));

        $arrRanges = class_date_range::getDateRange(new class_date(20160201000000), new class_date(20160228000000), class_date_period_enum::MONTH());

        $this->assertEquals(0, count($arrRanges));

        $arrRanges = class_date_range::getDateRange(new class_date(20160201000000), new class_date(20160229000000), class_date_period_enum::MONTH());

        $this->assertEquals(0, count($arrRanges));

        $arrRanges = class_date_range::getDateRange(new class_date(20160201000000), new class_date(20160301000000), class_date_period_enum::MONTH());

        $this->assertEquals(1, count($arrRanges));
        $this->assertEquals(20160201000000, $arrRanges[0][0]->getLongTimestamp());
        $this->assertEquals(20160229235959, $arrRanges[0][1]->getLongTimestamp());
    }

    public function testTransformToOldFormat()
    {
        $arrNewFormat = array(
            array(new class_date(20150101000000), new class_date(20150101235959)),
            array(new class_date(20150201000000), new class_date(20150201235959)),
        );

        $arrFormat = class_date_range::transformToOldFormat($arrNewFormat);
        $arrExpectFormat = array(
            'start_dates' => array('01.01.2015 00:00:00', '01.02.2015 00:00:00'),
            'end_dates' => array('01.01.2015 23:59:59', '01.02.2015 23:59:59'),
        );

        $this->assertEquals($arrExpectFormat, $arrFormat);
    }

    public function testGetIntervalByString()
    {
        $arrValues = array("DAY", "WEEK", "MONTH", "QUARTER", "HALFYEAR", "YEAR");

        foreach ($arrValues as $strValue) {
            $this->assertInstanceOf("class_date_period_enum", class_date_range::getIntervalByString($strValue));
            $this->assertInstanceOf("class_date_period_enum", class_date_range::getIntervalByString(strtolower($strValue)));
        }
    }

    /**
     * @expectedException class_exception
     */
    public function testGetIntervalByStringInvalid()
    {
        class_date_range::getIntervalByString("foo");
    }
}
