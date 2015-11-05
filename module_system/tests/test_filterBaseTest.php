<?php

require_once (__DIR__."/../../module_system/system/class_testbase.php");

/**
 * Class class_test_functions
 */
class test_filterBaseTest extends class_testbase  {

    public function testFilterBaseNoValues() {
        $objFilter = new FilterBaseA();

        $arrRestrictions = $objFilter->getOrmRestrictions();
        $this->assertCount(0, $arrRestrictions);
    }

    public function testFilterBaseValues() {
        $strSystemid = generateSystemid();

        $objFilter = new FilterBaseA();
        $objFilter->setStrFilter1("1");
        $objFilter->setIntFilter2(1);
        $objFilter->setFloatFilter3(1.0);
        $objFilter->setArrFilter4(array(1,2,3,4));
        $objFilter->setObjFilter5(new class_date(20150101000001));
        $objFilter->setIntFilter6(12);
        $objFilter->setObjFilter7(new class_date(20150101000001));
        $objFilter->setObjFilter8(new class_date(20150101000001));
        $objFilter->setStrFilter9($strSystemid);

        $arrRestrictions = $objFilter->getOrmRestrictions();
        $this->assertCount(9, $arrRestrictions);

        //Without annotation @filterCompareOperator
        $this->assertTrue($arrRestrictions[0] instanceof class_orm_objectlist_restriction);
        $this->assertEquals(" AND filter.filter1 LIKE ? ", $arrRestrictions[0]->getStrWhere());
        $this->assertCount(1, $arrRestrictions[0]->getArrParams());
        $this->assertEquals("%1%", $arrRestrictions[0]->getArrParams()[0]);

        $this->assertTrue($arrRestrictions[1] instanceof class_orm_objectlist_restriction);
        $this->assertEquals(" AND filter.filter2 = ? ", $arrRestrictions[1]->getStrWhere());
        $this->assertCount(1, $arrRestrictions[1]->getArrParams());
        $this->assertEquals(1, $arrRestrictions[1]->getArrParams()[0]);

        $this->assertTrue($arrRestrictions[2] instanceof class_orm_objectlist_restriction);
        $this->assertEquals(" AND filter.filter3 = ? ", $arrRestrictions[2]->getStrWhere());
        $this->assertCount(1, $arrRestrictions[2]->getArrParams());
        $this->assertEquals(1.0, $arrRestrictions[1]->getArrParams()[0]);

        $this->assertTrue($arrRestrictions[3] instanceof class_orm_objectlist_in_restriction);
        $this->assertEquals(" AND filter.filter4 IN (?,?,?,?) ", $arrRestrictions[3]->getStrWhere());
        $this->assertCount(4, $arrRestrictions[3]->getArrParams());

        $this->assertTrue($arrRestrictions[4] instanceof class_orm_objectlist_restriction);
        $this->assertEquals(" AND filter.filter5 = ? ", $arrRestrictions[4]->getStrWhere());
        $this->assertCount(1, $arrRestrictions[4]->getArrParams());
        $this->assertEquals(20150101000001, $arrRestrictions[4]->getArrParams()[0]);

        $this->assertTrue($arrRestrictions[5] instanceof class_orm_objectlist_restriction);
        $this->assertEquals(" AND filter.filter6 <= ? ", $arrRestrictions[5]->getStrWhere());
        $this->assertEquals(12, $arrRestrictions[5]->getArrParams()[0]);


        //With annotation @filterCompareOperator
        $this->assertTrue($arrRestrictions[6] instanceof class_orm_objectlist_restriction);
        $this->assertEquals(" AND filter.filter7 >= ? ", $arrRestrictions[6]->getStrWhere());
        $this->assertEquals(20150101000000, $arrRestrictions[6]->getArrParams()[0]);

        $this->assertTrue($arrRestrictions[7] instanceof class_orm_objectlist_restriction);
        $this->assertEquals(" AND filter.filter8 <= ? ", $arrRestrictions[7]->getStrWhere());
        $this->assertEquals(20150101235959, $arrRestrictions[7]->getArrParams()[0]);

        //Filter by system id
        $this->assertTrue($arrRestrictions[8] instanceof class_orm_objectlist_restriction);
        $this->assertEquals(" AND filter.filter9 LIKE ? ", $arrRestrictions[8]->getStrWhere());
        $this->assertCount(1, $arrRestrictions[8]->getArrParams());
        $this->assertEquals($strSystemid, $arrRestrictions[8]->getArrParams()[0]);
    }
}


class FilterBaseA extends class_filter_base{
    /**
     * @tableColumn filter.filter1
     * @fieldType text
     */
    protected $strFilter1;

    /**
     * @tableColumn filter.filter2
     * @fieldType text
     */
    protected $intFilter2;

    /**
     * @tableColumn filter.filter3
     * @fieldType text
     */
    protected $floatFilter3;

    /**
     * @tableColumn filter.filter4
     * @fieldType dropdown
     */
    protected $arrFilter4;

    /**
     * @tableColumn filter.filter5
     * @fieldType date
     */
    protected $objFilter5;


    /**
     * @tableColumn filter.filter6
     * @fieldType date
     * @filterCompareOperator LE
     */
    protected $intFilter6;


    /**
     * @tableColumn filter.filter7
     * @fieldType date
     * @filterCompareOperator GE
     */
    protected $objFilter7;

    /**
     * @tableColumn filter.filter8
     * @fieldType date
     * @filterCompareOperator LE
     */
    protected $objFilter8;


    /**
     * @tableColumn filter.filter9
     * @fieldType text
     */
    protected $strFilter9;



    public function getFilterId()
    {
        return "filter";
    }

    public function getArrModule()
    {
        return "module";
    }

    /**
     * @return mixed
     */
    public function getStrFilter1()
    {
        return $this->strFilter1;
    }

    /**
     * @param mixed $strFilter1
     */
    public function setStrFilter1($strFilter1)
    {
        $this->strFilter1 = $strFilter1;
    }

    /**
     * @return mixed
     */
    public function getIntFilter2()
    {
        return $this->intFilter2;
    }

    /**
     * @param mixed $intFilter2
     */
    public function setIntFilter2($intFilter2)
    {
        $this->intFilter2 = $intFilter2;
    }

    /**
     * @return mixed
     */
    public function getFloatFilter3()
    {
        return $this->floatFilter3;
    }

    /**
     * @param mixed $floatFilter3
     */
    public function setFloatFilter3($floatFilter3)
    {
        $this->floatFilter3 = $floatFilter3;
    }

    /**
     * @return mixed
     */
    public function getArrFilter4()
    {
        return $this->arrFilter4;
    }

    /**
     * @param mixed $arrFilter4
     */
    public function setArrFilter4($arrFilter4)
    {
        $this->arrFilter4 = $arrFilter4;
    }

    /**
     * @return mixed
     */
    public function getObjFilter5()
    {
        return $this->objFilter5;
    }

    /**
     * @param mixed $objFilter5
     */
    public function setObjFilter5($objFilter5)
    {
        $this->objFilter5 = $objFilter5;
    }

    /**
     * @return mixed
     */
    public function getIntFilter6()
    {
        return $this->intFilter6;
    }

    /**
     * @param mixed $intFilter6
     */
    public function setIntFilter6($intFilter6)
    {
        $this->intFilter6 = $intFilter6;
    }

    /**
     * @return mixed
     */
    public function getObjFilter7()
    {
        return $this->objFilter7;
    }

    /**
     * @param mixed $objFilter7
     */
    public function setObjFilter7($objFilter7)
    {
        $this->objFilter7 = $objFilter7;
    }

    /**
     * @return mixed
     */
    public function getObjFilter8()
    {
        return $this->objFilter8;
    }

    /**
     * @param mixed $objFilter8
     */
    public function setObjFilter8($objFilter8)
    {
        $this->objFilter8 = $objFilter8;
    }

    /**
     * @return mixed
     */
    public function getStrFilter9()
    {
        return $this->strFilter9;
    }

    /**
     * @param mixed $strFilter9
     */
    public function setStrFilter9($strFilter9)
    {
        $this->strFilter9 = $strFilter9;
    }
}

