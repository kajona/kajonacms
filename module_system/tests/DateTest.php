<?php

namespace Kajona\System\Tests;
use Kajona\System\System\Date;
use Kajona\System\System\Testbase;

class DateTest extends Testbase  {

    public function testTimezoneShifts() {
        $objDate = new Date("20141026000000");

        $objDate->setNextDay();
        $this->assertEquals($objDate->getLongTimestamp(), "20141027000000");

        $objDate = new Date("20141027000000");
        $objDate->setPreviousDay();

        $this->assertEquals($objDate->getLongTimestamp(), "20141026000000");
    }



    public function testSameDay() {
        $objDate = new Date();

        $this->assertTrue($objDate->isSameDay(new Date()));

        $objDate->setNextDay();
        $this->assertTrue(!$objDate->isSameDay(new Date()));
    }

    public function testDateParams() {
        $objDate = new Date(0);
        $this->assertEquals($objDate->getLongTimestamp(), 00000000000000);

        $objDate = new Date("0");
        $this->assertEquals($objDate->getLongTimestamp(), 00000000000000);

        $objDate = new Date("");
        $this->assertTrue($objDate->getLongTimestamp() > 0);

        $objDate = new Date(null);
        $this->assertTrue($objDate->getLongTimestamp() > 0);


        $objDate = new Date(20140310123627);
        $this->assertEquals($objDate->getLongTimestamp(), 20140310123627);

        $objDate = new Date("20140310123627");
        $this->assertEquals($objDate->getLongTimestamp(), 20140310123627);

        $objDate = new Date("");
        $objDate2 = new Date($objDate);
        $this->assertEquals($objDate2->getLongTimestamp(), $objDate->getLongTimestamp());

        $objDate = new Date(12345678);
        $this->assertEquals($objDate->getLongTimestamp(), 19700523222118);

        $objDate = new Date("12345678");
        $this->assertEquals($objDate->getLongTimestamp(), 19700523222118);

        $objDate = new Date("12345678");
        $objDate2 = new Date($objDate);
        $this->assertEquals($objDate2->getLongTimestamp(), $objDate->getLongTimestamp());
    }


    public function testNextMonth() {
        $objDate = new Date(20130101000000);
        $objDate->setNextMonth();
        $this->assertEquals($objDate->getLongTimestamp(), 20130201000000);

        $objDate = new Date(20130115120000);
        $objDate->setNextMonth();
        $this->assertEquals($objDate->getLongTimestamp(), 20130215120000);

        $objDate = new Date(20130131120000);
        $objDate->setNextMonth();
        $this->assertEquals($objDate->getLongTimestamp(), 20130228120000);

        $objDate = new Date(20130228120000);
        $objDate->setNextMonth();
        $this->assertEquals($objDate->getLongTimestamp(), 20130328120000);

        $objDate = new Date(20130331120000);
        $objDate->setNextMonth();
        $this->assertEquals($objDate->getLongTimestamp(), 20130430120000);
    }


    public function testPreviousMonth() {
        $objDate = new Date(20130101120000);
        $objDate->setPreviousMonth();
        $this->assertEquals($objDate->getLongTimestamp(), 20121201120000);

        $objDate = new Date(20130430120000);
        $objDate->setPreviousMonth();
        $this->assertEquals($objDate->getLongTimestamp(), 20130330120000);

        $objDate = new Date(20130331120000);
        $objDate->setPreviousMonth();
        $this->assertEquals($objDate->getLongTimestamp(), 20130228120000);

        $objDate = new Date(20130831120000);
        $objDate->setPreviousMonth();
        $this->assertEquals($objDate->getLongTimestamp(), 20130731120000);
    }


    public function testNextWeek() {
        $objDate = new Date(20130115120000);
        $objDate->setNextWeek();
        $this->assertEquals($objDate->getLongTimestamp(), 20130122120000);
    }

    public function testPreviousWeek() {
        $objDate = new Date(20130122120000);
        $objDate->setPreviousWeek();
        $this->assertEquals($objDate->getLongTimestamp(), 20130115120000);
    }


    public function testNextYear() {
        $objDate = new Date(20130115120000);
        $objDate->setNextYear();
        $this->assertEquals($objDate->getLongTimestamp(), 20140115120000);

        $objDate = new Date(20150531120000);
        $objDate->setNextYear();
        $this->assertEquals($objDate->getLongTimestamp(), 20160531120000);
    }

    public function testPreviousYear() {
        $objDate = new Date(20130122120000);
        $objDate->setPreviousYear();
        $this->assertEquals($objDate->getLongTimestamp(), 20120122120000);

        $objDate = new Date(20150531120000);
        $objDate->setPreviousYear();
        $this->assertEquals($objDate->getLongTimestamp(), 20140531120000);
    }

    public function testSetEndOfDay() {
        $objDate = new Date(20150901133737);
        $objDate->setEndOfDay();
        $this->assertEquals($objDate->getLongTimestamp(), 20150901235959);
    }

    public function testSetBeginningOfDay() {
        $objDate = new Date(20150901133737);
        $objDate->setBeginningOfDay();
        $this->assertEquals($objDate->getLongTimestamp(), 20150901000000);
    }
}

