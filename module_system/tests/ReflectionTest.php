<?php

namespace Kajona\System\System;

use Kajona\System\Tests\Testbase;

class ReflectionTest extends Testbase  {

    /**
     * Dummy dataprovider which is being used to call each test case twice.
     *
     * @return array
     */
    public function additionProvider()
    {
        return array(
            array(0),
            array(0)
        );
    }

    /**
     * @dataProvider additionProvider
     */
    public function testAnnotationsValueFromClass($a) {
        $objAnnotations = new Reflection(new B());

        $arrClassAnnotations = $objAnnotations->getAnnotationValuesFromClass("@noval");
        $this->assertEquals(0, count($arrClassAnnotations));

        $arrClassAnnotations = $objAnnotations->getAnnotationValuesFromClass("@classTest");
        $this->assertEquals(3, count($arrClassAnnotations));
        $this->assertTrue(in_array("val1", $arrClassAnnotations));
        $this->assertTrue(in_array("val2", $arrClassAnnotations));
        $this->assertTrue(in_array("val3", $arrClassAnnotations));
        
        $objAnnotations = new Reflection(new A());

        $arrClassAnnotations = $objAnnotations->getAnnotationValuesFromClass("@noval");
        $this->assertEquals(0, count($arrClassAnnotations));

        $arrClassAnnotations = $objAnnotations->getAnnotationValuesFromClass("@classTest");
        $this->assertEquals(1, count($arrClassAnnotations));
        $this->assertTrue(in_array("val1", $arrClassAnnotations));
        
        $arrClassAnnotations = $objAnnotations->getAnnotationValuesFromClass("@emptyAnnotation");
        $this->assertEquals(1, count($arrClassAnnotations));
        $this->assertTrue(in_array("", $arrClassAnnotations));
    }

    /**
     * @dataProvider additionProvider
     */
    public function testGetAnnotationsWithValueFromClass($a) {
        $objAnnotations = new Reflection(new B());

        $arrClassAnnotations = $objAnnotations->getAnnotationsWithValueFromClass("val2");
        $this->assertEquals(2, count($arrClassAnnotations));
        $this->assertTrue(in_array("@classTest", $arrClassAnnotations));
        $this->assertTrue(in_array("@classTest2", $arrClassAnnotations));
    }

    /**
     * @dataProvider additionProvider
     */
    public function testHasMethodAnnotation($a) {

        $objAnnotations = new Reflection(new B());

        $this->assertTrue($objAnnotations->hasMethodAnnotation("testMethod", "@methodTest"));
        $this->assertTrue(!$objAnnotations->hasMethodAnnotation("testMethod", "@method2Test"));

        $this->assertTrue(!$objAnnotations->hasMethodAnnotation("test2Method", "@method2Test"));
    }

    /**
     * @dataProvider additionProvider
     */
    public function testHasPropertyAnnotation($a) {

        $objAnnotations = new Reflection(new B());

        $this->assertTrue($objAnnotations->hasPropertyAnnotation("propertyB1", "@propertyTest"));
        $this->assertTrue(!$objAnnotations->hasPropertyAnnotation("propertyB1", "@property2Test"));

        $objAnnotations = new Reflection(new A());
        $this->assertTrue($objAnnotations->hasPropertyAnnotation("propertyA1", "@propertyTest"));
    }

    /**
     * @dataProvider additionProvider
     */
    public function testGetMethodAnnotationValue($a) {

        $objAnnotations = new Reflection(new B());

        $this->assertEquals("val1", $objAnnotations->getMethodAnnotationValue("testMethod", "@methodTest"));
        $this->assertTrue(!$objAnnotations->getMethodAnnotationValue("testMethod", "@method2Test"));
    }

    /**
     * @dataProvider additionProvider
     */
    public function testGetPropertiesWithAnnotation($a) {

        $objAnnotations = new Reflection(new B());

        $this->assertEquals(3, count($objAnnotations->getPropertiesWithAnnotation("@propertyTest")));

        $arrProps = $objAnnotations->getPropertiesWithAnnotation("@propertyTest");
        
        $arrKeys = array_keys($arrProps);
        $arrValues = array_values($arrProps);

        $this->assertEquals("valA1", $arrValues[0]);
        $this->assertEquals("propertyA1", $arrKeys[0]);

        $this->assertEquals("valB1", $arrValues[1]);
        $this->assertEquals("propertyB1", $arrKeys[1]);

        $this->assertEquals("valB2", $arrValues[2]);
        $this->assertEquals("propertyB2", $arrKeys[2]);



        $this->assertEquals("valB1", $objAnnotations->getAnnotationValueForProperty("propertyB1", "@propertyTest"));
        $this->assertEquals("valA1", $objAnnotations->getAnnotationValueForProperty("propertyA1", "@propertyTest"));
        $this->assertNull($objAnnotations->getAnnotationValueForProperty("propertyA1", "@notAPropertyTest"));

    }

    /**
     * @dataProvider additionProvider
     */
    public function testGetAnnotationValueForProperty($a) {
        $objAnnotations = new Reflection(new C());

        $strValues = $objAnnotations->getAnnotationValueForProperty("propertyB1", "@propertyTest");
        $this->assertEquals("valB1", $strValues);

        $strValues = $objAnnotations->getAnnotationValueForProperty("propertyB1", "@propertyParamTest1");
        $this->assertEquals("valB1", $strValues);

        $strValues = $objAnnotations->getAnnotationValueForProperty("propertyB1", "@propertyParamTest2");
        $this->assertEquals("valB1", $strValues);

        $strValues = $objAnnotations->getAnnotationValueForProperty("propertyB1", "@propertyParamTest3");
        $this->assertEquals("", $strValues);

        $strValues = $objAnnotations->getAnnotationValueForProperty("propertyB1", "@propertyParamTest4");
        $this->assertEquals("", $strValues);
    }

    /**
     * @dataProvider additionProvider
     */
    public function testGetGetters($a) {
        $objReflection = new Reflection(new A());
        $this->assertEquals(strtolower("getLongPropertyA1"), strtolower($objReflection->getGetter("propertyA1")));

        $objReflection = new Reflection(new B());
        $this->assertEquals(strtolower("getLongPropertyA1"), strtolower($objReflection->getGetter("propertyA1")));
        $this->assertEquals(strtolower("getBitPropertyB1"), strtolower($objReflection->getGetter("propertyB1")));
    }

    /**
     * @dataProvider additionProvider
     */
    public function testGetSetters($a) {
        $objReflection = new Reflection(new A());
        $this->assertEquals(strtolower("setStrPropertyA1"), strtolower($objReflection->getSetter("propertyA1")));

        $objReflection = new Reflection(new B());
        $this->assertEquals(strtolower("setStrPropertyA1"), strtolower($objReflection->getSetter("propertyA1")));
        $this->assertEquals(strtolower("setIntPropertyB1"), strtolower($objReflection->getSetter("propertyB1")));
    }

    /**
     * @dataProvider additionProvider
     */
    public function testPropertyAnnotationInheritance($a) {
        $objReflection = new Reflection(new A());
        $this->assertEquals("val CA", $objReflection->getAnnotationValueForProperty("propertyC", "@propertyTestInheritance"));

        $objReflection = new Reflection(new B());
        $this->assertEquals("val CB", $objReflection->getAnnotationValueForProperty("propertyC", "@propertyTestInheritance"));
    }


    /**
     * @dataProvider additionProvider
     */
    public function testGetAnnotationValuesFromClassParameter($a) {
        $objReflection = new Reflection(new C());

        $this->assertTrue($objReflection->hasClassAnnotation("@classTest"));
        $this->assertTrue($objReflection->hasClassAnnotation("@classParamTest1"));
        $this->assertTrue($objReflection->hasClassAnnotation("@classParamTest2"));
        $this->assertTrue($objReflection->hasClassAnnotation("@classParamTest3"));
        $this->assertTrue($objReflection->hasClassAnnotation("@classParamTest4"));
        $this->assertTrue($objReflection->hasClassAnnotation("@fieldDDValues"));


        $arrClassAnnotations = $objReflection->getAnnotationValuesFromClass("@classTest");
        $this->assertEquals(5, count($arrClassAnnotations));
        $this->assertTrue(in_array("val1", $arrClassAnnotations));
        $this->assertTrue(in_array("val2", $arrClassAnnotations));
        $this->assertTrue(in_array("val3", $arrClassAnnotations));
        $this->assertTrue(in_array("val4", $arrClassAnnotations));
        $this->assertTrue(in_array("val5", $arrClassAnnotations));

        //Values
        $arrValues = $objReflection->getAnnotationValuesFromClass("@classParamTest1");
        $this->assertCount(1, $arrValues);
        $this->assertEquals("val1", $arrValues[0]);
        Reflection::flushCache();

        $arrValues = $objReflection->getAnnotationValuesFromClass("@classParamTest2");
        $this->assertCount(2, $arrValues);
        $this->assertEquals("", $arrValues[0]);
        $this->assertEquals("", $arrValues[1]);
        Reflection::flushCache();

        $arrValues = $objReflection->getAnnotationValuesFromClass("@classParamTest3");
        $this->assertCount(1, $arrValues);
        $this->assertEquals("val3", $arrValues[0]);
        Reflection::flushCache();

        $arrValues = $objReflection->getAnnotationValuesFromClass("@classParamTest4");
        $this->assertCount(1, $arrValues);
        $this->assertEquals("", $arrValues[0]);
        Reflection::flushCache();

        //Params
        $arrParams = $objReflection->getAnnotationValuesFromClass("@classParamTest1", ReflectionEnum::PARAMS);
        $this->assertCount(1, $arrParams);
        $this->assertCount(0, $arrParams[0]);
        Reflection::flushCache();

        $arrParamsAll = $objReflection->getAnnotationValuesFromClass("@classParamTest2", ReflectionEnum::PARAMS);
        $this->assertCount(2, $arrParamsAll);//param from tow classes

        //Class C
        $arrParams = $arrParamsAll[0];
        $this->assertCount(5, $arrParams);
        $this->assertArrayHasKey("param1", $arrParams);
        $this->assertArrayHasKey("param2", $arrParams);
        $this->assertArrayHasKey("param3", $arrParams);
        $this->assertArrayHasKey("param4", $arrParams);
        $this->assertArrayHasKey("param5", $arrParams);
        $this->assertEquals(0, $arrParams["param1"]);
        $this->assertEquals("abc", $arrParams["param2"]);
        $this->assertTrue(is_array($arrParams["param3"]));
        $this->assertEquals("0", $arrParams["param3"][0]);
        $this->assertEquals("123", $arrParams["param3"][1]);
        $this->assertEquals("456", $arrParams["param3"][2]);
        $this->assertEquals(999, $arrParams["param4"]);
        $this->assertEquals("hans im glück", $arrParams["param5"]);

        //Class B
        $arrParams = $arrParamsAll[1];
        $this->assertCount(2, $arrParams);
        $this->assertArrayHasKey("param1", $arrParams);
        $this->assertArrayHasKey("param2", $arrParams);
        $this->assertEquals(54, $arrParams["param1"]);
        $this->assertEquals(12334, $arrParams["param2"]);

        //Class C
        $arrParams = $objReflection->getAnnotationValuesFromClass("@classParamTest3", ReflectionEnum::PARAMS);
        $arrParams = $arrParams[0];
        $this->assertCount(3, $arrParams);
        $this->assertArrayHasKey("param1", $arrParams);
        $this->assertArrayHasKey("param2", $arrParams);
        $this->assertArrayHasKey("param3", $arrParams);
        $this->assertEquals(0, $arrParams["param1"]);
        $this->assertEquals("abc", $arrParams["param2"]);
        $this->assertTrue(is_array($arrParams["param3"]));
        $this->assertEquals("0", $arrParams["param3"][0]);
        $this->assertEquals("123", $arrParams["param3"][1]);
        $this->assertEquals("456", $arrParams["param3"][2]);

        //Class C
        $arrParams = $objReflection->getAnnotationValuesFromClass("@classParamTest4", ReflectionEnum::PARAMS);
        $arrParams = $arrParams[0];
        $this->assertCount(0, $arrParams);
    }

    /**
     * @dataProvider additionProvider
     */
    public function testGetAnnotationsWithValueFromClassParameter($a) {
        $objAnnotations = new Reflection(new C());

        $arrClassAnnotations = $objAnnotations->getAnnotationsWithValueFromClass(54, ReflectionEnum::PARAMS);
        $this->assertEquals(1, count($arrClassAnnotations));

        $arrClassAnnotations = $objAnnotations->getAnnotationsWithValueFromClass("0", ReflectionEnum::PARAMS);
        $this->assertEquals(2, count($arrClassAnnotations));
        $this->assertTrue(in_array("@classParamTest2", $arrClassAnnotations));
        $this->assertTrue(in_array("@classParamTest3", $arrClassAnnotations));

        $arrClassAnnotations = $objAnnotations->getAnnotationsWithValueFromClass(0, ReflectionEnum::PARAMS);
        $this->assertEquals(2, count($arrClassAnnotations));
        $this->assertTrue(in_array("@classParamTest2", $arrClassAnnotations));
        $this->assertTrue(in_array("@classParamTest3", $arrClassAnnotations));

        $arrClassAnnotations = $objAnnotations->getAnnotationsWithValueFromClass("hans im glück", ReflectionEnum::PARAMS);
        $this->assertEquals(1, count($arrClassAnnotations));
        $this->assertTrue(in_array("@classParamTest2", $arrClassAnnotations));

        $arrClassAnnotations = $objAnnotations->getAnnotationsWithValueFromClass("", ReflectionEnum::PARAMS);
        $this->assertEquals(0, count($arrClassAnnotations));

    }


    /**
     * @dataProvider additionProvider
     */
    public function testGetMethodAnnotationValueParameter($a) {

        $objAnnotations = new Reflection(new C());

        $arrParams = $objAnnotations->getMethodAnnotationValue("testMethod", "@methodTest", ReflectionEnum::PARAMS);
        $this->assertCount(2, $arrParams);
        $this->assertTrue(array_key_exists("param1", $arrParams));
        $this->assertTrue(array_key_exists("param2", $arrParams));


        $this->assertTrue(!$objAnnotations->getMethodAnnotationValue("testMethod", "@method2Test", ReflectionEnum::PARAMS));
    }



    /**
     * @dataProvider additionProvider
     */
    public function testGetAnnotationValueForPropertyParameter($a) {
        $objAnnotations = new Reflection(new C());

        $arrParams = $objAnnotations->getAnnotationValueForProperty("propertyB1", "@propertyTest", ReflectionEnum::PARAMS);
        $this->assertCount(0, $arrParams);


        $arrParams = $objAnnotations->getAnnotationValueForProperty("propertyB1", "@propertyParamTest1", ReflectionEnum::PARAMS);
        $this->assertCount(3, $arrParams);
        $this->assertArrayHasKey("param1", $arrParams);

        $arrParams = $objAnnotations->getAnnotationValueForProperty("propertyB1", "@propertyParamTest2", ReflectionEnum::PARAMS);
        $this->assertCount(0, $arrParams);


        $arrParams = $objAnnotations->getAnnotationValueForProperty("propertyB1", "@propertyParamTest3", ReflectionEnum::PARAMS);
        $this->assertCount(1, $arrParams);
        $this->assertArrayHasKey("param1", $arrParams);

        $arrParams = $objAnnotations->getAnnotationValueForProperty("propertyB1", "@propertyParamTest4", ReflectionEnum::PARAMS);
        $this->assertCount(0, $arrParams);
    }


    public function testGetParamValue()
    {
        $objAnnotations = new Reflection(new C());

        $strParamValue = $objAnnotations->getParamValueForPropertyAndAnnotation("propertyB1", "@propertyParamTest1", "param1");
        $this->assertEquals(0, $strParamValue);

        $strParamValue = $objAnnotations->getParamValueForPropertyAndAnnotation("propertyB1", "@propertyParamTest1", "param2");
        $this->assertEquals(array("0", 123, 456), $strParamValue);

        $strParamValue = $objAnnotations->getParamValueForPropertyAndAnnotation("propertyB1", "@propertyParamTest1", "param3");
        $this->assertEquals("astring", $strParamValue);

        $strParamValue = $objAnnotations->getParamValueForPropertyAndAnnotation("propertyB1", "@propertyParamTest1", "paramXYZ");
        $this->assertNull($strParamValue);
    }
}

//set up test-structures

/**
 *
 * @emptyAnnotation
 * @classTest val1
 * @classTest2 val2
 *
 */
class A {

    /**
     * @propertyTest valA1
     */
    private $propertyA1;

    private $propertyA2;

    /**
     * @propertyTestInheritance val CA
     */
    private $propertyC;

    public function setStrPropertyA1($propertyA1) {
        $this->propertyA1 = $propertyA1;
    }

    public function getLongPropertyA1() {
        return $this->propertyA1;
    }
}

/**
 *
 * @classTest val2
 * @classTest val3
 *
 * @classParamTest2 (param1=54, param2=12334)
 *
 */
class B extends A {

    /**
     * @propertyTest valB1
     */
    private $propertyB1;

    /**
     * @propertyTest valB2
     */
    private $propertyB2;


    /**
     * @propertyTestInheritance val CB
     */
    private $propertyC;

    /**
     * @methodTest val1
     * @methodTest val2
     */
    public function testMethod() {

    }

    public function setIntPropertyB1($propertyB1) {
        $this->propertyB1 = $propertyB1;
    }

    public function getBitPropertyB1() {
        return $this->propertyB1;
    }

}


/**
 *
 * @classTest val4
 * @classTest val5
 *
 *
 * @classParamTest1 val1
 * @classParamTest2 (param1=0, param2="abc", param3={"0", 123, 456}, param4=999, param5="hans im glück")
 * @classParamTest3 val3 (param1=0, param2="abc", param3={"0", 123, 456})
 * @classParamTest4
 * @fieldDDValues [1 => event_status_1],[2 => event_status_2],[3 => event_status_3],[4 => event_status_4]
 *
 * *
 */
class C extends B {

    /**
     * @propertyTest valB1
     * @propertyParamTest1 valB1 (param1=0, param2={"0", 123, 456}, param3="astring")
     * @propertyParamTest2 valB1
     * @propertyParamTest3 (param1=0)
     * @propertyParamTest4
     * @
     */
    private $propertyB1;

    /**
     * @propertyTest valB2
     *
     */
    private $propertyB2;


    /**
     * @propertyTestInheritance val CB
     */
    private $propertyC;

    /**
     * @methodTest val1 (param1=1, param2="2")
     * @methodTest val2 (param3=3, param4="4")
     */
    public function testMethod() {

    }

    public function setIntPropertyB1($propertyB1) {
        $this->propertyB1 = $propertyB1;
    }

    public function getBitPropertyB1() {
        return $this->propertyB1;
    }




}

