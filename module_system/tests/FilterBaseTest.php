<?php

namespace Kajona\System\Tests;

use Kajona\System\System\AbstractController;
use Kajona\System\System\Classloader;
use Kajona\System\System\Date;
use Kajona\System\System\FilterBase;
use Kajona\System\System\OrmCondition;
use Kajona\System\System\OrmInCondition;
use Kajona\System\System\OrmInOrEmptyCondition;
use Kajona\System\System\Reflection;
use Kajona\System\System\Resourceloader;

/**
 * Class class_test_functions
 */
class FilterBaseTest extends Testbase
{

    public function testFilterBaseNoValues()
    {
        $objFilter = new FilterBaseA();

        $arrRestrictions = $objFilter->getOrmConditions();
        $this->assertCount(0, $arrRestrictions);
    }

    public function testFilterBaseValues()
    {
        $strSystemid = generateSystemid();

        $objFilter = new FilterBaseA();
        $objFilter->setStrFilter1("1");
        $objFilter->setIntFilter2(1);
        $objFilter->setFloatFilter3(1.0);
        $objFilter->setArrFilter4(array(1, 2, 3, 4));
        $objFilter->setObjFilter5(new Date(20150101000001));
        $objFilter->setIntFilter6(12);
        $objFilter->setObjFilter7(new Date(20150101000001));
        $objFilter->setObjFilter8(new Date(20150101000001));
        $objFilter->setStrFilter9($strSystemid);
        $objFilter->setArrFilter10(array(OrmInOrEmptyCondition::NULL_OR_EMPTY, 1, 2, 3, 4));

        $arrRestrictions = $objFilter->getOrmConditions();
        $this->assertCount(10, $arrRestrictions);

        //Without annotation @filterCompareOperator
        $this->assertTrue($arrRestrictions[0] instanceof OrmCondition);
        $this->assertEquals("filter.filter1 LIKE ?", $arrRestrictions[0]->getStrWhere());
        $this->assertCount(1, $arrRestrictions[0]->getArrParams());
        $this->assertEquals("%1%", $arrRestrictions[0]->getArrParams()[0]);

        $this->assertTrue($arrRestrictions[1] instanceof OrmCondition);
        $this->assertEquals("filter.filter2 = ?", $arrRestrictions[1]->getStrWhere());
        $this->assertCount(1, $arrRestrictions[1]->getArrParams());
        $this->assertEquals(1, $arrRestrictions[1]->getArrParams()[0]);

        $this->assertTrue($arrRestrictions[2] instanceof OrmCondition);
        $this->assertEquals("filter.filter3 = ?", $arrRestrictions[2]->getStrWhere());
        $this->assertCount(1, $arrRestrictions[2]->getArrParams());
        $this->assertEquals(1.0, $arrRestrictions[1]->getArrParams()[0]);

        $this->assertTrue($arrRestrictions[3] instanceof OrmInCondition);
        $this->assertEquals("filter.filter4 IN (?,?,?,?)", $arrRestrictions[3]->getStrWhere());
        $this->assertCount(4, $arrRestrictions[3]->getArrParams());

        $this->assertTrue($arrRestrictions[4] instanceof OrmCondition);
        $this->assertEquals("filter.filter5 = ?", $arrRestrictions[4]->getStrWhere());
        $this->assertCount(1, $arrRestrictions[4]->getArrParams());
        $this->assertEquals(20150101000001, $arrRestrictions[4]->getArrParams()[0]);

        $this->assertTrue($arrRestrictions[5] instanceof OrmCondition);
        $this->assertEquals("filter.filter6 <= ?", $arrRestrictions[5]->getStrWhere());
        $this->assertEquals(12, $arrRestrictions[5]->getArrParams()[0]);


        //With annotation @filterCompareOperator
        $this->assertTrue($arrRestrictions[6] instanceof OrmCondition);
        $this->assertEquals("filter.filter7 >= ?", $arrRestrictions[6]->getStrWhere());
        $this->assertEquals(20150101000000, $arrRestrictions[6]->getArrParams()[0]);

        $this->assertTrue($arrRestrictions[7] instanceof OrmCondition);
        $this->assertEquals("filter.filter8 <= ?", $arrRestrictions[7]->getStrWhere());
        $this->assertEquals(20150101235959, $arrRestrictions[7]->getArrParams()[0]);

        //Filter by system id
        $this->assertTrue($arrRestrictions[8] instanceof OrmCondition);
        $this->assertEquals("filter.filter9 = ?", $arrRestrictions[8]->getStrWhere());
        $this->assertCount(1, $arrRestrictions[8]->getArrParams());
        $this->assertEquals($strSystemid, $arrRestrictions[8]->getArrParams()[0]);

        $this->assertTrue($arrRestrictions[9] instanceof OrmInOrEmptyCondition);
        $this->assertEquals("((filter.filter10 IN (?,?,?,?,?)) OR (filter.filter10 IS NULL) OR (filter.filter10 = ''))", $arrRestrictions[9]->getStrWhere());
        $this->assertCount(5, $arrRestrictions[9]->getArrParams());
    }


    public function testCheckRequiredAnnotations() {

        //Get all classes which extend FilterBase
        $arrFilterClasses = $this->getListOfFilters(true);
        foreach($arrFilterClasses as $strFilterClass) {
            $objReflection = new Reflection($strFilterClass);

            //Check if class has @module annotaion
            $this->assertTrue($objReflection->hasClassAnnotation(AbstractController::STR_MODULE_ANNOTATION), $strFilterClass." has no @module annotation");
        }
    }


    /**
     * Creates a list of objects implementing the FilterBase.
     * The objects are initialized as empty objects
     *
     * @return FilterBase[]|string[]
     */
    public function getListOfFilters($bitAsStringArray = false)
    {
        $arrReturn = array();

        //load classes
        $arrFiles = Resourceloader::getInstance()->getFolderContent("/system", array(".php"), false, null, function (&$strFile, $strPath) {
            $strFile = Classloader::getInstance()->getInstanceFromFilename($strPath, "Kajona\\System\\System\\FilterBase");
        });

        foreach($arrFiles as $objInstance) {

            if($objInstance == null) {
                continue;
            }

            $arrReturn[] = $objInstance;

        }

        if($bitAsStringArray) {
            $arrReturn = array_map(
                function ($strEntry) {
                    return get_class($strEntry);
                },
                $arrReturn
            );
        }


        return $arrReturn;
    }
}


/**
 * Class FilterBaseA
 *
 * @package Kajona\System\Tests
 * @author stefan.meyer1@yahoo.de
 * @module module
 */
class FilterBaseA extends FilterBase
{
    /**
     * @tableColumn filter.filter1
     * @fieldType Kajona\System\Admin\Formentries\FormentryText
     */
    protected $strFilter1;

    /**
     * @tableColumn filter.filter2
     * @fieldType Kajona\System\Admin\Formentries\FormentryText
     */
    protected $intFilter2;

    /**
     * @tableColumn filter.filter3
     * @fieldType Kajona\System\Admin\Formentries\FormentryText
     */
    protected $floatFilter3;

    /**
     * @tableColumn filter.filter4
     * @fieldType Kajona\System\Admin\Formentries\FormentryDropdown
     */
    protected $arrFilter4;

    /**
     * @tableColumn filter.filter5
     * @fieldType Kajona\System\Admin\Formentries\FormentryDate
     */
    protected $objFilter5;


    /**
     * @tableColumn filter.filter6
     * @fieldType Kajona\System\Admin\Formentries\FormentryDate
     * @filterCompareOperator LE
     */
    protected $intFilter6;


    /**
     * @tableColumn filter.filter7
     * @fieldType Kajona\System\Admin\Formentries\FormentryDate
     * @filterCompareOperator GE
     */
    protected $objFilter7;

    /**
     * @tableColumn filter.filter8
     * @fieldType Kajona\System\Admin\Formentries\FormentryDate
     * @filterCompareOperator LE
     */
    protected $objFilter8;


    /**
     * @tableColumn filter.filter9
     * @fieldType Kajona\System\Admin\Formentries\FormentryText
     */
    protected $strFilter9;


    /**
     * @tableColumn filter.filter10
     * @fieldType Kajona\System\Admin\Formentries\FormentryDropdown
     * @filterCompareOperator IN_OR_EMPTY
     */
    protected $arrFilter10;

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

    /**
     * @return mixed
     */
    public function getArrFilter10()
    {
        return $this->arrFilter10;
    }

    /**
     * @param mixed $objFilter10
     */
    public function setArrFilter10($objFilter10)
    {
        $this->arrFilter10 = $objFilter10;
    }
}

