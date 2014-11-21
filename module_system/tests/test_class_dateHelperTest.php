<?php

require_once (__DIR__."/../../module_system/system/class_testbase.php");

class test_class_dateHelperTest extends class_testbase  {

    public function testIsEasterHoliday() {
        $objHelper = new class_date_helper();

        $this->assertTrue(!$objHelper->isEasterHoliday(new class_date(20140417010000)));
        $this->assertTrue($objHelper->isEasterHoliday(new class_date(20140418010000)));
        $this->assertTrue($objHelper->isEasterHoliday(new class_date(20140419010000)));
        $this->assertTrue($objHelper->isEasterHoliday(new class_date(20140420010000)));
        $this->assertTrue($objHelper->isEasterHoliday(new class_date(20140421010000)));
        $this->assertTrue(!$objHelper->isEasterHoliday(new class_date(20140422010000)));
    }

    public function testIsTarget2Day() {
        $objHelper = new class_date_helper();

        //1.1.
        $this->assertTrue(!$objHelper->isValidTarget2Day(new class_date(20140101010000)));
        //1.5.
        $this->assertTrue(!$objHelper->isValidTarget2Day(new class_date(20140501010000)));

        //easter
        $this->assertTrue($objHelper->isValidTarget2Day(new class_date(20140417010000)));
        $this->assertTrue(!$objHelper->isValidTarget2Day(new class_date(20140418010000)));
        $this->assertTrue(!$objHelper->isValidTarget2Day(new class_date(20140419010000)));
        $this->assertTrue(!$objHelper->isValidTarget2Day(new class_date(20140420010000)));
        $this->assertTrue(!$objHelper->isValidTarget2Day(new class_date(20140421010000)));
        $this->assertTrue($objHelper->isValidTarget2Day(new class_date(20140422010000)));

        //xmas
        $this->assertTrue(!$objHelper->isValidTarget2Day(new class_date(20141225010000)));
        $this->assertTrue(!$objHelper->isValidTarget2Day(new class_date(20141226010000)));

        $this->assertTrue(!$objHelper->isValidTarget2Day(new class_date(20141227010000)));
        $this->assertTrue(!$objHelper->isValidTarget2Day(new class_date(20141228010000)));
        $this->assertTrue($objHelper->isValidTarget2Day(new class_date(20141229010000)));

        //random weekday
        $this->assertTrue($objHelper->isValidTarget2Day(new class_date(20141121010000)));
        $this->assertTrue($objHelper->isValidTarget2Day(new class_date(20141124010000)));


        //random weekend
        $this->assertTrue(!$objHelper->isValidTarget2Day(new class_date(20141122010000)));
        $this->assertTrue(!$objHelper->isValidTarget2Day(new class_date(20141123010000)));
    }


}

