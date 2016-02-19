<?php

namespace Kajona\System\Tests;

require_once __DIR__ . "../../../core/module_system/system/Testbase.php";

use Kajona\System\System\Date;
use Kajona\System\System\DateHelper;
use Kajona\System\System\DatePeriodEnum;
use Kajona\System\System\Testbase;

class DateHelperTest extends Testbase
{

    public function testIsEasterHoliday()
    {
        $objHelper = new DateHelper();

        //2014
        $this->assertTrue(!$objHelper->isEasterHoliday(new Date(20140417010000)));//
        $this->assertTrue($objHelper->isEasterHoliday(new Date(20140418010000)));//friday
        $this->assertTrue($objHelper->isEasterHoliday(new Date(20140419010000)));
        $this->assertTrue($objHelper->isEasterHoliday(new Date(20140420010000)));
        $this->assertTrue($objHelper->isEasterHoliday(new Date(20140421010000)));
        $this->assertTrue(!$objHelper->isEasterHoliday(new Date(20140422010000)));

        //2015 (leap year)
        $this->assertTrue(!$objHelper->isEasterHoliday(new Date(20160324010000)));
        $this->assertTrue($objHelper->isEasterHoliday(new Date(20160325010000)));//friday
        $this->assertTrue($objHelper->isEasterHoliday(new Date(20160326010000)));
        $this->assertTrue($objHelper->isEasterHoliday(new Date(20160327010000)));
        $this->assertTrue($objHelper->isEasterHoliday(new Date(20160328010000)));
        $this->assertTrue(!$objHelper->isEasterHoliday(new Date(20160329010000)));


        $this->assertTrue($objHelper->isEasterHoliday(new Date(20170414010000)));//friday
        $this->assertTrue($objHelper->isEasterHoliday(new Date(20180330010000)));//friday
        $this->assertTrue($objHelper->isEasterHoliday(new Date(20190419010000)));//friday

        $this->assertTrue($objHelper->isEasterHoliday(new Date(20200410010000)));//leap year
        $this->assertTrue($objHelper->isEasterHoliday(new Date(20240329010000)));//leap year


    }

    public function testIsTarget2Day()
    {
        $objHelper = new DateHelper();

        //1.1.
        $this->assertTrue(!$objHelper->isValidTarget2Day(new Date(20140101010000)));
        //1.5.
        $this->assertTrue(!$objHelper->isValidTarget2Day(new Date(20140501010000)));

        //easter
        $this->assertTrue($objHelper->isValidTarget2Day(new Date(20140417010000)));
        $this->assertTrue(!$objHelper->isValidTarget2Day(new Date(20140418010000)));
        $this->assertTrue(!$objHelper->isValidTarget2Day(new Date(20140419010000)));
        $this->assertTrue(!$objHelper->isValidTarget2Day(new Date(20140420010000)));
        $this->assertTrue(!$objHelper->isValidTarget2Day(new Date(20140421010000)));
        $this->assertTrue($objHelper->isValidTarget2Day(new Date(20140422010000)));

        //xmas
        $this->assertTrue(!$objHelper->isValidTarget2Day(new Date(20141225010000)));
        $this->assertTrue(!$objHelper->isValidTarget2Day(new Date(20141226010000)));

        $this->assertTrue(!$objHelper->isValidTarget2Day(new Date(20141227010000)));
        $this->assertTrue(!$objHelper->isValidTarget2Day(new Date(20141228010000)));
        $this->assertTrue($objHelper->isValidTarget2Day(new Date(20141229010000)));

        //random weekday
        $this->assertTrue($objHelper->isValidTarget2Day(new Date(20141121010000)));
        $this->assertTrue($objHelper->isValidTarget2Day(new Date(20141124010000)));


        //random weekend
        $this->assertTrue(!$objHelper->isValidTarget2Day(new Date(20141122010000)));
        $this->assertTrue(!$objHelper->isValidTarget2Day(new Date(20141123010000)));
    }

    public function testIsLeapYear()
    {
        $objHelper = new DateHelper();

        $this->assertTrue($objHelper->isLeapYear(2016));
        $this->assertTrue(!$objHelper->isLeapYear(2017));
        $this->assertTrue(!$objHelper->isLeapYear(2018));
        $this->assertTrue(!$objHelper->isLeapYear(2019));
        $this->assertTrue($objHelper->isLeapYear(2020));
        $this->assertTrue(!$objHelper->isLeapYear(2021));
        $this->assertTrue(!$objHelper->isLeapYear(2022));
        $this->assertTrue(!$objHelper->isLeapYear(2023));
        $this->assertTrue($objHelper->isLeapYear(2024));
        $this->assertTrue(!$objHelper->isLeapYear(2025));
        $this->assertTrue(!$objHelper->isLeapYear(2026));
        $this->assertTrue(!$objHelper->isLeapYear(2027));
        $this->assertTrue($objHelper->isLeapYear(2028));
    }


    public function testCalculateNextIntervals()
    {
        $objHelper = new DateHelper();

        //NextWorking day
        $objDate = new Date(20150201000001);
        $objCalcDate = $objHelper->calcNextWorkingDay($objDate);
        $this->assertEquals($objCalcDate->getLongTimestamp(), 20150202000001);

        $objDate = new Date(20150201000001);
        $objCalcDate = $objHelper->calcNextWorkingDay($objDate, 4);
        $this->assertEquals($objCalcDate->getLongTimestamp(), 20150205000001);

        //NextWorking day
        $objDate = new Date(20150207000001);
        $objCalcDate = $objHelper->calcNextWorkingDay($objDate);
        $this->assertEquals($objCalcDate->getLongTimestamp(), 20150209000001);

        $objDate = new Date(20150207000001);
        $objCalcDate = $objHelper->calcNextWorkingDay($objDate, 4);
        $this->assertEquals($objCalcDate->getLongTimestamp(), 20150212000001);

        //LastWorking day
        $objDate = new Date(20150207000001);
        $objCalcDate = $objHelper->calcLastWorkingDay($objDate);
        $this->assertEquals($objCalcDate->getLongTimestamp(), 20150206000001);

        $objDate = new Date(20150207000001);
        $objCalcDate = $objHelper->calcLastWorkingDay($objDate, 4);
        $this->assertEquals($objCalcDate->getLongTimestamp(), 20150203000001);

        //LastWorking day with weekend
        $objDate = new Date(20150211000001);
        $objCalcDate = $objHelper->calcLastWorkingDay($objDate, 4);
        $this->assertEquals($objCalcDate->getLongTimestamp(), 20150205000001);
    }

    public function test_firstDayOfThis()
    {
        $objHelper = new DateHelper();

        $arrDates = array();
        $arrDates[] = array("period" => DatePeriodEnum::YEAR(), "basedate" => 20150101000001, "expecteddate" => 20150101000001);
        $arrDates[] = array("period" => DatePeriodEnum::YEAR(), "basedate" => 20151001000001, "expecteddate" => 20150101000001);

        $arrDates[] = array("period" => DatePeriodEnum::HALFYEAR(), "basedate" => 20150401000001, "expecteddate" => 20150101000001);
        $arrDates[] = array("period" => DatePeriodEnum::HALFYEAR(), "basedate" => 20150601000001, "expecteddate" => 20150101000001);
        $arrDates[] = array("period" => DatePeriodEnum::HALFYEAR(), "basedate" => 20150701000001, "expecteddate" => 20150701000001);
        $arrDates[] = array("period" => DatePeriodEnum::HALFYEAR(), "basedate" => 20151001000001, "expecteddate" => 20150701000001);

        $arrDates[] = array("period" => DatePeriodEnum::QUARTER(), "basedate" => 20150101000001, "expecteddate" => 20150101000001);//jan
        $arrDates[] = array("period" => DatePeriodEnum::QUARTER(), "basedate" => 20150201000001, "expecteddate" => 20150101000001);//feb
        $arrDates[] = array("period" => DatePeriodEnum::QUARTER(), "basedate" => 20150301000001, "expecteddate" => 20150101000001);//march
        $arrDates[] = array("period" => DatePeriodEnum::QUARTER(), "basedate" => 20150401000001, "expecteddate" => 20150401000001);//apr
        $arrDates[] = array("period" => DatePeriodEnum::QUARTER(), "basedate" => 20150501000001, "expecteddate" => 20150401000001);//may
        $arrDates[] = array("period" => DatePeriodEnum::QUARTER(), "basedate" => 20150601000001, "expecteddate" => 20150401000001);//jun
        $arrDates[] = array("period" => DatePeriodEnum::QUARTER(), "basedate" => 20150701000001, "expecteddate" => 20150701000001);//jul
        $arrDates[] = array("period" => DatePeriodEnum::QUARTER(), "basedate" => 20150801000001, "expecteddate" => 20150701000001);//aug
        $arrDates[] = array("period" => DatePeriodEnum::QUARTER(), "basedate" => 20150901000001, "expecteddate" => 20150701000001);//sep
        $arrDates[] = array("period" => DatePeriodEnum::QUARTER(), "basedate" => 20151001000001, "expecteddate" => 20151001000001);//oct
        $arrDates[] = array("period" => DatePeriodEnum::QUARTER(), "basedate" => 20151101000001, "expecteddate" => 20151001000001);//nov
        $arrDates[] = array("period" => DatePeriodEnum::QUARTER(), "basedate" => 20151201000001, "expecteddate" => 20151001000001);//dec

        $arrDates[] = array("period" => DatePeriodEnum::MONTH(), "basedate" => 20150201000001, "expecteddate" => 20150201000001);
        $arrDates[] = array("period" => DatePeriodEnum::MONTH(), "basedate" => 20151201000001, "expecteddate" => 20151201000001);
        $arrDates[] = array("period" => DatePeriodEnum::MONTH(), "basedate" => 20151223000001, "expecteddate" => 20151201000001);

        $arrDates[] = array("period" => DatePeriodEnum::WEEK(), "basedate" => 20150202000001, "expecteddate" => 20150202000001);//mon
        $arrDates[] = array("period" => DatePeriodEnum::WEEK(), "basedate" => 20150203000001, "expecteddate" => 20150202000001);//tue
        $arrDates[] = array("period" => DatePeriodEnum::WEEK(), "basedate" => 20150204000001, "expecteddate" => 20150202000001);//wed
        $arrDates[] = array("period" => DatePeriodEnum::WEEK(), "basedate" => 20150205000001, "expecteddate" => 20150202000001);//thu
        $arrDates[] = array("period" => DatePeriodEnum::WEEK(), "basedate" => 20150206000001, "expecteddate" => 20150202000001);//fri
        $arrDates[] = array("period" => DatePeriodEnum::WEEK(), "basedate" => 20150207000001, "expecteddate" => 20150202000001);//sat
        $arrDates[] = array("period" => DatePeriodEnum::WEEK(), "basedate" => 20150208000001, "expecteddate" => 20150202000001);//sun


        foreach ($arrDates as $arrDate) {
            $objDate = new Date($arrDate["basedate"]);
            $objCalcDate = $objHelper->firstDayOfThis($arrDate["period"], $objDate);
            $this->assertEquals($arrDate["expecteddate"], $objCalcDate->getLongTimestamp(), $arrDate["period"] . "" . $arrDate["basedate"] . "" . $arrDate["expecteddate"]);
        }
    }


    public function test_lastDayOfThis()
    {
        $objHelper = new DateHelper();

        $arrDates = array();
        $arrDates[] = array("period" => DatePeriodEnum::YEAR(), "basedate" => 20150101000001, "expecteddate" => 20151231000001);
        $arrDates[] = array("period" => DatePeriodEnum::YEAR(), "basedate" => 20151001000001, "expecteddate" => 20151231000001);

        $arrDates[] = array("period" => DatePeriodEnum::HALFYEAR(), "basedate" => 20150401000001, "expecteddate" => 20150630000001);
        $arrDates[] = array("period" => DatePeriodEnum::HALFYEAR(), "basedate" => 20150601000001, "expecteddate" => 20150630000001);
        $arrDates[] = array("period" => DatePeriodEnum::HALFYEAR(), "basedate" => 20150701000001, "expecteddate" => 20151231000001);
        $arrDates[] = array("period" => DatePeriodEnum::HALFYEAR(), "basedate" => 20151001000001, "expecteddate" => 20151231000001);

        $arrDates[] = array("period" => DatePeriodEnum::QUARTER(), "basedate" => 20150101000001, "expecteddate" => 20150331000001);//jan
        $arrDates[] = array("period" => DatePeriodEnum::QUARTER(), "basedate" => 20150201000001, "expecteddate" => 20150331000001);//feb
        $arrDates[] = array("period" => DatePeriodEnum::QUARTER(), "basedate" => 20150301000001, "expecteddate" => 20150331000001);//march
        $arrDates[] = array("period" => DatePeriodEnum::QUARTER(), "basedate" => 20150401000001, "expecteddate" => 20150630000001);//apr
        $arrDates[] = array("period" => DatePeriodEnum::QUARTER(), "basedate" => 20150501000001, "expecteddate" => 20150630000001);//may
        $arrDates[] = array("period" => DatePeriodEnum::QUARTER(), "basedate" => 20150601000001, "expecteddate" => 20150630000001);//jun
        $arrDates[] = array("period" => DatePeriodEnum::QUARTER(), "basedate" => 20150701000001, "expecteddate" => 20150930000001);//jul
        $arrDates[] = array("period" => DatePeriodEnum::QUARTER(), "basedate" => 20150801000001, "expecteddate" => 20150930000001);//aug
        $arrDates[] = array("period" => DatePeriodEnum::QUARTER(), "basedate" => 20150901000001, "expecteddate" => 20150930000001);//sep
        $arrDates[] = array("period" => DatePeriodEnum::QUARTER(), "basedate" => 20151001000001, "expecteddate" => 20151231000001);//oct
        $arrDates[] = array("period" => DatePeriodEnum::QUARTER(), "basedate" => 20151101000001, "expecteddate" => 20151231000001);//nov
        $arrDates[] = array("period" => DatePeriodEnum::QUARTER(), "basedate" => 20151201000001, "expecteddate" => 20151231000001);//dec

        $arrDates[] = array("period" => DatePeriodEnum::MONTH(), "basedate" => 20150201000001, "expecteddate" => 20150228000001);
        $arrDates[] = array("period" => DatePeriodEnum::MONTH(), "basedate" => 20151201000001, "expecteddate" => 20151231000001);
        $arrDates[] = array("period" => DatePeriodEnum::MONTH(), "basedate" => 20151223000001, "expecteddate" => 20151231000001);

        $arrDates[] = array("period" => DatePeriodEnum::WEEK(), "basedate" => 20150202000001, "expecteddate" => 20150208000001);//mon
        $arrDates[] = array("period" => DatePeriodEnum::WEEK(), "basedate" => 20150203000001, "expecteddate" => 20150208000001);//tue
        $arrDates[] = array("period" => DatePeriodEnum::WEEK(), "basedate" => 20150204000001, "expecteddate" => 20150208000001);//wed
        $arrDates[] = array("period" => DatePeriodEnum::WEEK(), "basedate" => 20150205000001, "expecteddate" => 20150208000001);//thu
        $arrDates[] = array("period" => DatePeriodEnum::WEEK(), "basedate" => 20150206000001, "expecteddate" => 20150208000001);//fri
        $arrDates[] = array("period" => DatePeriodEnum::WEEK(), "basedate" => 20150207000001, "expecteddate" => 20150208000001);//sat
        $arrDates[] = array("period" => DatePeriodEnum::WEEK(), "basedate" => 20150208000001, "expecteddate" => 20150208000001);//sun


        foreach ($arrDates as $arrDate) {
            $objDate = new Date($arrDate["basedate"]);
            $objCalcDate = $objHelper->lastDayOfThis($arrDate["period"], $objDate);
            $this->assertEquals($arrDate["expecteddate"], $objCalcDate->getLongTimestamp(), $arrDate["period"] . " " . $arrDate["basedate"] . " " . $arrDate["expecteddate"]);
        }
    }

    public function test_firstDayOfLast()
    {
        $objHelper = new DateHelper();

        $arrDates = array();
        $arrDates[] = array("period" => DatePeriodEnum::YEAR(), "basedate" => 20150101000001, "expecteddate" => 20140101000001);
        $arrDates[] = array("period" => DatePeriodEnum::YEAR(), "basedate" => 20151001000001, "expecteddate" => 20140101000001);

        $arrDates[] = array("period" => DatePeriodEnum::HALFYEAR(), "basedate" => 20150401000001, "expecteddate" => 20140701000001);
        $arrDates[] = array("period" => DatePeriodEnum::HALFYEAR(), "basedate" => 20150601000001, "expecteddate" => 20140701000001);
        $arrDates[] = array("period" => DatePeriodEnum::HALFYEAR(), "basedate" => 20150701000001, "expecteddate" => 20150101000001);
        $arrDates[] = array("period" => DatePeriodEnum::HALFYEAR(), "basedate" => 20151001000001, "expecteddate" => 20150101000001);

        $arrDates[] = array("period" => DatePeriodEnum::QUARTER(), "basedate" => 20150101000001, "expecteddate" => 20141001000001);//jan
        $arrDates[] = array("period" => DatePeriodEnum::QUARTER(), "basedate" => 20150201000001, "expecteddate" => 20141001000001);//feb
        $arrDates[] = array("period" => DatePeriodEnum::QUARTER(), "basedate" => 20150301000001, "expecteddate" => 20141001000001);//march
        $arrDates[] = array("period" => DatePeriodEnum::QUARTER(), "basedate" => 20150401000001, "expecteddate" => 20150101000001);//apr
        $arrDates[] = array("period" => DatePeriodEnum::QUARTER(), "basedate" => 20150501000001, "expecteddate" => 20150101000001);//may
        $arrDates[] = array("period" => DatePeriodEnum::QUARTER(), "basedate" => 20150601000001, "expecteddate" => 20150101000001);//jun
        $arrDates[] = array("period" => DatePeriodEnum::QUARTER(), "basedate" => 20150701000001, "expecteddate" => 20150401000001);//jul
        $arrDates[] = array("period" => DatePeriodEnum::QUARTER(), "basedate" => 20150801000001, "expecteddate" => 20150401000001);//aug
        $arrDates[] = array("period" => DatePeriodEnum::QUARTER(), "basedate" => 20150901000001, "expecteddate" => 20150401000001);//sep
        $arrDates[] = array("period" => DatePeriodEnum::QUARTER(), "basedate" => 20151001000001, "expecteddate" => 20150701000001);//oct
        $arrDates[] = array("period" => DatePeriodEnum::QUARTER(), "basedate" => 20151101000001, "expecteddate" => 20150701000001);//nov
        $arrDates[] = array("period" => DatePeriodEnum::QUARTER(), "basedate" => 20151201000001, "expecteddate" => 20150701000001);//dec

        $arrDates[] = array("period" => DatePeriodEnum::MONTH(), "basedate" => 20150201000001, "expecteddate" => 20150101000001);
        $arrDates[] = array("period" => DatePeriodEnum::MONTH(), "basedate" => 20151201000001, "expecteddate" => 20151101000001);
        $arrDates[] = array("period" => DatePeriodEnum::MONTH(), "basedate" => 20151223000001, "expecteddate" => 20151101000001);

        $arrDates[] = array("period" => DatePeriodEnum::WEEK(), "basedate" => 20150202000001, "expecteddate" => 20150126000001);//mon
        $arrDates[] = array("period" => DatePeriodEnum::WEEK(), "basedate" => 20150203000001, "expecteddate" => 20150126000001);//tue
        $arrDates[] = array("period" => DatePeriodEnum::WEEK(), "basedate" => 20150204000001, "expecteddate" => 20150126000001);//wed
        $arrDates[] = array("period" => DatePeriodEnum::WEEK(), "basedate" => 20150205000001, "expecteddate" => 20150126000001);//thu
        $arrDates[] = array("period" => DatePeriodEnum::WEEK(), "basedate" => 20150206000001, "expecteddate" => 20150126000001);//fri
        $arrDates[] = array("period" => DatePeriodEnum::WEEK(), "basedate" => 20150207000001, "expecteddate" => 20150126000001);//sat
        $arrDates[] = array("period" => DatePeriodEnum::WEEK(), "basedate" => 20150208000001, "expecteddate" => 20150126000001);//sun
        $arrDates[] = array("period" => DatePeriodEnum::WEEK(), "basedate" => 20150527000001, "expecteddate" => 20150518000001);//sun


        foreach ($arrDates as $arrDate) {
            $objDate = new Date($arrDate["basedate"]);
            $objCalcDate = $objHelper->firstDayOfLast($arrDate["period"], $objDate);
            $this->assertEquals($arrDate["expecteddate"], $objCalcDate->getLongTimestamp(), $arrDate["period"] . " " . $arrDate["basedate"] . " " . $arrDate["expecteddate"]);;
        }
    }

    public function test_lastDayOfLast()
    {
        $objHelper = new DateHelper();

        $arrDates = array();
        $arrDates[] = array("period" => DatePeriodEnum::YEAR(), "basedate" => 20150101000001, "expecteddate" => 20141231000001);
        $arrDates[] = array("period" => DatePeriodEnum::YEAR(), "basedate" => 20151001000001, "expecteddate" => 20141231000001);

        $arrDates[] = array("period" => DatePeriodEnum::HALFYEAR(), "basedate" => 20150401000001, "expecteddate" => 20141231000001);
        $arrDates[] = array("period" => DatePeriodEnum::HALFYEAR(), "basedate" => 20150601000001, "expecteddate" => 20141231000001);
        $arrDates[] = array("period" => DatePeriodEnum::HALFYEAR(), "basedate" => 20150701000001, "expecteddate" => 20150630000001);
        $arrDates[] = array("period" => DatePeriodEnum::HALFYEAR(), "basedate" => 20151001000001, "expecteddate" => 20150630000001);

        $arrDates[] = array("period" => DatePeriodEnum::QUARTER(), "basedate" => 20150101000001, "expecteddate" => 20141231000001);//jan
        $arrDates[] = array("period" => DatePeriodEnum::QUARTER(), "basedate" => 20150201000001, "expecteddate" => 20141231000001);//feb
        $arrDates[] = array("period" => DatePeriodEnum::QUARTER(), "basedate" => 20150301000001, "expecteddate" => 20141231000001);//march
        $arrDates[] = array("period" => DatePeriodEnum::QUARTER(), "basedate" => 20150401000001, "expecteddate" => 20150331000001);//apr
        $arrDates[] = array("period" => DatePeriodEnum::QUARTER(), "basedate" => 20150501000001, "expecteddate" => 20150331000001);//may
        $arrDates[] = array("period" => DatePeriodEnum::QUARTER(), "basedate" => 20150601000001, "expecteddate" => 20150331000001);//jun
        $arrDates[] = array("period" => DatePeriodEnum::QUARTER(), "basedate" => 20150701000001, "expecteddate" => 20150630000001);//jul
        $arrDates[] = array("period" => DatePeriodEnum::QUARTER(), "basedate" => 20150801000001, "expecteddate" => 20150630000001);//aug
        $arrDates[] = array("period" => DatePeriodEnum::QUARTER(), "basedate" => 20150901000001, "expecteddate" => 20150630000001);//sep
        $arrDates[] = array("period" => DatePeriodEnum::QUARTER(), "basedate" => 20151001000001, "expecteddate" => 20150930000001);//oct
        $arrDates[] = array("period" => DatePeriodEnum::QUARTER(), "basedate" => 20151101000001, "expecteddate" => 20150930000001);//nov
        $arrDates[] = array("period" => DatePeriodEnum::QUARTER(), "basedate" => 20151201000001, "expecteddate" => 20150930000001);//dec

        $arrDates[] = array("period" => DatePeriodEnum::MONTH(), "basedate" => 20150201000001, "expecteddate" => 20150131000001);
        $arrDates[] = array("period" => DatePeriodEnum::MONTH(), "basedate" => 20151201000001, "expecteddate" => 20151130000001);
        $arrDates[] = array("period" => DatePeriodEnum::MONTH(), "basedate" => 20151223000001, "expecteddate" => 20151130000001);

        $arrDates[] = array("period" => DatePeriodEnum::WEEK(), "basedate" => 20150202000001, "expecteddate" => 20150201000001);//mon
        $arrDates[] = array("period" => DatePeriodEnum::WEEK(), "basedate" => 20150203000001, "expecteddate" => 20150201000001);//tue
        $arrDates[] = array("period" => DatePeriodEnum::WEEK(), "basedate" => 20150204000001, "expecteddate" => 20150201000001);//wed
        $arrDates[] = array("period" => DatePeriodEnum::WEEK(), "basedate" => 20150205000001, "expecteddate" => 20150201000001);//thu
        $arrDates[] = array("period" => DatePeriodEnum::WEEK(), "basedate" => 20150206000001, "expecteddate" => 20150201000001);//fri
        $arrDates[] = array("period" => DatePeriodEnum::WEEK(), "basedate" => 20150207000001, "expecteddate" => 20150201000001);//sat
        $arrDates[] = array("period" => DatePeriodEnum::WEEK(), "basedate" => 20150208000001, "expecteddate" => 20150201000001);//sun


        foreach ($arrDates as $arrDate) {
            $objDate = new Date($arrDate["basedate"]);
            $objCalcDate = $objHelper->lastDayOfLast($arrDate["period"], $objDate);
            $this->assertEquals($arrDate["expecteddate"], $objCalcDate->getLongTimestamp(), $arrDate["period"] . " " . $arrDate["basedate"] . " " . $arrDate["expecteddate"]);;
        }
    }

    public function test_firstDayOfNext()
    {
        $objHelper = new DateHelper();

        $arrDates = array();
        $arrDates[] = array("period" => DatePeriodEnum::YEAR(), "basedate" => 20150101000001, "expecteddate" => 20160101000001);
        $arrDates[] = array("period" => DatePeriodEnum::YEAR(), "basedate" => 20151001000001, "expecteddate" => 20160101000001);

        $arrDates[] = array("period" => DatePeriodEnum::HALFYEAR(), "basedate" => 20150401000001, "expecteddate" => 20150701000001);
        $arrDates[] = array("period" => DatePeriodEnum::HALFYEAR(), "basedate" => 20150601000001, "expecteddate" => 20150701000001);
        $arrDates[] = array("period" => DatePeriodEnum::HALFYEAR(), "basedate" => 20150701000001, "expecteddate" => 20160101000001);
        $arrDates[] = array("period" => DatePeriodEnum::HALFYEAR(), "basedate" => 20151001000001, "expecteddate" => 20160101000001);

        $arrDates[] = array("period" => DatePeriodEnum::QUARTER(), "basedate" => 20150101000001, "expecteddate" => 20150401000001);//jan
        $arrDates[] = array("period" => DatePeriodEnum::QUARTER(), "basedate" => 20150201000001, "expecteddate" => 20150401000001);//feb
        $arrDates[] = array("period" => DatePeriodEnum::QUARTER(), "basedate" => 20150301000001, "expecteddate" => 20150401000001);//march
        $arrDates[] = array("period" => DatePeriodEnum::QUARTER(), "basedate" => 20150401000001, "expecteddate" => 20150701000001);//apr
        $arrDates[] = array("period" => DatePeriodEnum::QUARTER(), "basedate" => 20150501000001, "expecteddate" => 20150701000001);//may
        $arrDates[] = array("period" => DatePeriodEnum::QUARTER(), "basedate" => 20150601000001, "expecteddate" => 20150701000001);//jun
        $arrDates[] = array("period" => DatePeriodEnum::QUARTER(), "basedate" => 20150701000001, "expecteddate" => 20151001000001);//jul
        $arrDates[] = array("period" => DatePeriodEnum::QUARTER(), "basedate" => 20150801000001, "expecteddate" => 20151001000001);//aug
        $arrDates[] = array("period" => DatePeriodEnum::QUARTER(), "basedate" => 20150901000001, "expecteddate" => 20151001000001);//sep
        $arrDates[] = array("period" => DatePeriodEnum::QUARTER(), "basedate" => 20151001000001, "expecteddate" => 20160101000001);//oct
        $arrDates[] = array("period" => DatePeriodEnum::QUARTER(), "basedate" => 20151101000001, "expecteddate" => 20160101000001);//nov
        $arrDates[] = array("period" => DatePeriodEnum::QUARTER(), "basedate" => 20151201000001, "expecteddate" => 20160101000001);//dec

        $arrDates[] = array("period" => DatePeriodEnum::MONTH(), "basedate" => 20150201000001, "expecteddate" => 20150301000001);
        $arrDates[] = array("period" => DatePeriodEnum::MONTH(), "basedate" => 20151201000001, "expecteddate" => 20160101000001);
        $arrDates[] = array("period" => DatePeriodEnum::MONTH(), "basedate" => 20151223000001, "expecteddate" => 20160101000001);

        $arrDates[] = array("period" => DatePeriodEnum::WEEK(), "basedate" => 20150202000001, "expecteddate" => 20150209000001);//mon
        $arrDates[] = array("period" => DatePeriodEnum::WEEK(), "basedate" => 20150203000001, "expecteddate" => 20150209000001);//tue
        $arrDates[] = array("period" => DatePeriodEnum::WEEK(), "basedate" => 20150204000001, "expecteddate" => 20150209000001);//wed
        $arrDates[] = array("period" => DatePeriodEnum::WEEK(), "basedate" => 20150205000001, "expecteddate" => 20150209000001);//thu
        $arrDates[] = array("period" => DatePeriodEnum::WEEK(), "basedate" => 20150206000001, "expecteddate" => 20150209000001);//fri
        $arrDates[] = array("period" => DatePeriodEnum::WEEK(), "basedate" => 20150207000001, "expecteddate" => 20150209000001);//sat
        $arrDates[] = array("period" => DatePeriodEnum::WEEK(), "basedate" => 20150208000001, "expecteddate" => 20150209000001);//sun


        foreach ($arrDates as $arrDate) {
            $objDate = new Date($arrDate["basedate"]);
            $objCalcDate = $objHelper->firstDayOfNext($arrDate["period"], $objDate);
            $this->assertEquals($arrDate["expecteddate"], $objCalcDate->getLongTimestamp(), $arrDate["period"] . " " . $arrDate["basedate"] . " " . $arrDate["expecteddate"]);;
        }
    }

    public function test_lastDayOfNext()
    {
        $objHelper = new DateHelper();

        $arrDates = array();
        $arrDates[] = array("period" => DatePeriodEnum::YEAR(), "basedate" => 20150101000001, "expecteddate" => 20161231000001);
        $arrDates[] = array("period" => DatePeriodEnum::YEAR(), "basedate" => 20151001000001, "expecteddate" => 20161231000001);

        $arrDates[] = array("period" => DatePeriodEnum::HALFYEAR(), "basedate" => 20150401000001, "expecteddate" => 20151231000001);
        $arrDates[] = array("period" => DatePeriodEnum::HALFYEAR(), "basedate" => 20150601000001, "expecteddate" => 20151231000001);
        $arrDates[] = array("period" => DatePeriodEnum::HALFYEAR(), "basedate" => 20150701000001, "expecteddate" => 20160630000001);
        $arrDates[] = array("period" => DatePeriodEnum::HALFYEAR(), "basedate" => 20151001000001, "expecteddate" => 20160630000001);

        $arrDates[] = array("period" => DatePeriodEnum::QUARTER(), "basedate" => 20150101000001, "expecteddate" => 20150630000001);//jan
        $arrDates[] = array("period" => DatePeriodEnum::QUARTER(), "basedate" => 20150201000001, "expecteddate" => 20150630000001);//feb
        $arrDates[] = array("period" => DatePeriodEnum::QUARTER(), "basedate" => 20150301000001, "expecteddate" => 20150630000001);//march
        $arrDates[] = array("period" => DatePeriodEnum::QUARTER(), "basedate" => 20150401000001, "expecteddate" => 20150930000001);//apr
        $arrDates[] = array("period" => DatePeriodEnum::QUARTER(), "basedate" => 20150501000001, "expecteddate" => 20150930000001);//may
        $arrDates[] = array("period" => DatePeriodEnum::QUARTER(), "basedate" => 20150601000001, "expecteddate" => 20150930000001);//jun
        $arrDates[] = array("period" => DatePeriodEnum::QUARTER(), "basedate" => 20150701000001, "expecteddate" => 20151231000001);//jul
        $arrDates[] = array("period" => DatePeriodEnum::QUARTER(), "basedate" => 20150801000001, "expecteddate" => 20151231000001);//aug
        $arrDates[] = array("period" => DatePeriodEnum::QUARTER(), "basedate" => 20150901000001, "expecteddate" => 20151231000001);//sep
        $arrDates[] = array("period" => DatePeriodEnum::QUARTER(), "basedate" => 20151001000001, "expecteddate" => 20160331000001);//oct
        $arrDates[] = array("period" => DatePeriodEnum::QUARTER(), "basedate" => 20151101000001, "expecteddate" => 20160331000001);//nov
        $arrDates[] = array("period" => DatePeriodEnum::QUARTER(), "basedate" => 20151201000001, "expecteddate" => 20160331000001);//dec

        $arrDates[] = array("period" => DatePeriodEnum::MONTH(), "basedate" => 20150201000001, "expecteddate" => 20150331000001);
        $arrDates[] = array("period" => DatePeriodEnum::MONTH(), "basedate" => 20151201000001, "expecteddate" => 20160131000001);
        $arrDates[] = array("period" => DatePeriodEnum::MONTH(), "basedate" => 20151223000001, "expecteddate" => 20160131000001);

        $arrDates[] = array("period" => DatePeriodEnum::WEEK(), "basedate" => 20150202000001, "expecteddate" => 20150215000001);//mon
        $arrDates[] = array("period" => DatePeriodEnum::WEEK(), "basedate" => 20150203000001, "expecteddate" => 20150215000001);//tue
        $arrDates[] = array("period" => DatePeriodEnum::WEEK(), "basedate" => 20150204000001, "expecteddate" => 20150215000001);//wed
        $arrDates[] = array("period" => DatePeriodEnum::WEEK(), "basedate" => 20150205000001, "expecteddate" => 20150215000001);//thu
        $arrDates[] = array("period" => DatePeriodEnum::WEEK(), "basedate" => 20150206000001, "expecteddate" => 20150215000001);//fri
        $arrDates[] = array("period" => DatePeriodEnum::WEEK(), "basedate" => 20150207000001, "expecteddate" => 20150215000001);//sat
        $arrDates[] = array("period" => DatePeriodEnum::WEEK(), "basedate" => 20150208000001, "expecteddate" => 20150215000001);//sun


        foreach ($arrDates as $arrDate) {
            $objDate = new Date($arrDate["basedate"]);
            $objCalcDate = $objHelper->lastDayOfNext($arrDate["period"], $objDate);
            $this->assertEquals($arrDate["expecteddate"], $objCalcDate->getLongTimestamp(), $arrDate["period"] . " " . $arrDate["basedate"] . " " . $arrDate["expecteddate"]);;
        }
    }


    public function testCalcWorkingDaysBetween()
    {
        $objHelper = new DateHelper();
        $this->assertEquals(0, $objHelper->calcNumberOfWorkingDaysBetween(new Date(20150801000000), new Date(20150801000000)));
        $this->assertEquals(23, $objHelper->calcNumberOfWorkingDaysBetween(new Date(20150701000000), new Date(20150801000000)));

        $this->assertEquals(23, $objHelper->calcNumberOfWorkingDaysBetween(new Date(20150701000000), new Date(20150731000000)));
        $this->assertEquals(21, $objHelper->calcNumberOfWorkingDaysBetween(new Date(20150801000000), new Date(20150831000000)));
        $this->assertEquals(22, $objHelper->calcNumberOfWorkingDaysBetween(new Date(20150901000000), new Date(20150930000000)));
        $this->assertEquals(66, $objHelper->calcNumberOfWorkingDaysBetween(new Date(20150701000000), new Date(20150930000000)));

        $this->assertEquals(count($objHelper->getWorkingDays(7, 2015)), $objHelper->calcNumberOfWorkingDaysBetween(new Date(20150701000000), new Date(20150731000000)));
        $this->assertEquals(count($objHelper->getWorkingDays(8, 2015)), $objHelper->calcNumberOfWorkingDaysBetween(new Date(20150801000000), new Date(20150831000000)));
        $this->assertEquals(count($objHelper->getWorkingDays(9, 2015)), $objHelper->calcNumberOfWorkingDaysBetween(new Date(20150901000000), new Date(20150930000000)));


    }


}

