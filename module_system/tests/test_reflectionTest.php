<?php

require_once (__DIR__."/../../module_system/system/class_testbase.php");

class class_test_reflection extends class_testbase  {

    public function testAnnotationsValueFromClass() {
        $objAnnotations = new class_reflection(new B());

        $arrClassAnnotations = $objAnnotations->getAnnotationValuesFromClass("@noval");
        $this->assertEquals(0, count($arrClassAnnotations));

        $arrClassAnnotations = $objAnnotations->getAnnotationValuesFromClass("@classTest");
        $this->assertEquals(3, count($arrClassAnnotations));
        $this->assertTrue(in_array("val1", $arrClassAnnotations));
        $this->assertTrue(in_array("val2", $arrClassAnnotations));
        $this->assertTrue(in_array("val3", $arrClassAnnotations));
        
        $objAnnotations = new class_reflection(new A());

        $arrClassAnnotations = $objAnnotations->getAnnotationValuesFromClass("@noval");
        $this->assertEquals(0, count($arrClassAnnotations));

        $arrClassAnnotations = $objAnnotations->getAnnotationValuesFromClass("@classTest");
        $this->assertEquals(1, count($arrClassAnnotations));
        $this->assertTrue(in_array("val1", $arrClassAnnotations));
        
        $arrClassAnnotations = $objAnnotations->getAnnotationValuesFromClass("@emptyAnnotation");
        $this->assertEquals(1, count($arrClassAnnotations));
        $this->assertTrue(in_array("", $arrClassAnnotations));
    }
    
    public function testGetAnnotationsWithValueFromClass() {
        $objAnnotations = new class_reflection(new B());

        $arrClassAnnotations = $objAnnotations->getAnnotationsWithValueFromClass("val2");
        $this->assertEquals(2, count($arrClassAnnotations));
        $this->assertTrue(in_array("@classTest", $arrClassAnnotations));
        $this->assertTrue(in_array("@classTest2", $arrClassAnnotations));
    }

    public function testHasMethodAnnotation() {

        $objAnnotations = new class_reflection(new B());

        $this->assertTrue($objAnnotations->hasMethodAnnotation("testMethod", "@methodTest"));
        $this->assertTrue(!$objAnnotations->hasMethodAnnotation("testMethod", "@method2Test"));

        $this->assertTrue(!$objAnnotations->hasMethodAnnotation("test2Method", "@method2Test"));
    }

    public function testHasPropertyAnnotation() {

        $objAnnotations = new class_reflection(new B());

        $this->assertTrue($objAnnotations->hasPropertyAnnotation("propertyB1", "@propertyTest"));
        $this->assertTrue(!$objAnnotations->hasPropertyAnnotation("propertyB1", "@property2Test"));

        $objAnnotations = new class_reflection(new A());
        $this->assertTrue($objAnnotations->hasPropertyAnnotation("propertyA1", "@propertyTest"));
    }

    public function testGetMethodAnnotationValue() {

        $objAnnotations = new class_reflection(new B());

        $this->assertEquals("val1", $objAnnotations->getMethodAnnotationValue("testMethod", "@methodTest"));
        $this->assertTrue(!$objAnnotations->getMethodAnnotationValue("testMethod", "@method2Test"));
    }

    public function testGetPropertiesWithAnnotation() {

        $objAnnotations = new class_reflection(new B());

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

    public function testGetGetters() {
        $objReflection = new class_reflection(new A());
        $this->assertEquals(strtolower("getLongPropertyA1"), strtolower($objReflection->getGetter("propertyA1")));

        $objReflection = new class_reflection(new B());
        $this->assertEquals(strtolower("getLongPropertyA1"), strtolower($objReflection->getGetter("propertyA1")));
        $this->assertEquals(strtolower("getBitPropertyB1"), strtolower($objReflection->getGetter("propertyB1")));
    }


    public function testGetSetters() {
        $objReflection = new class_reflection(new A());
        $this->assertEquals(strtolower("setStrPropertyA1"), strtolower($objReflection->getSetter("propertyA1")));

        $objReflection = new class_reflection(new B());
        $this->assertEquals(strtolower("setStrPropertyA1"), strtolower($objReflection->getSetter("propertyA1")));
        $this->assertEquals(strtolower("setIntPropertyB1"), strtolower($objReflection->getSetter("propertyB1")));
    }


    public function testPropertyAnnotationInheritance() {
        $objReflection = new class_reflection(new A());
        $this->assertEquals("val CA", $objReflection->getAnnotationValueForProperty("propertyC", "@propertyTestInheritance"));

        $objReflection = new class_reflection(new B());
        $this->assertEquals("val CB", $objReflection->getAnnotationValueForProperty("propertyC", "@propertyTestInheritance"));
    }



    public function testAnnotationsParameter() {
        $objReflection = new class_reflection(new C());

        /*
         *
         * @classParamTest1 val1
         * @classParamTest2 (param1=0, param2="abc", param3={"0", 123, 456}, 999)
         * @classParamTest3 val3 (param1=0, param2="abc", param3={"0", 123, 456})
         * @classParamTest4
         *
        */


        //^(.*)(\(.*\))
        $this->assertTrue($objReflection->hasClassAnnotation("@classParamTest1"));
        $this->assertTrue($objReflection->hasClassAnnotation("@classParamTest2"));
        $this->assertTrue($objReflection->hasClassAnnotation("@classParamTest3"));
        $this->assertTrue($objReflection->hasClassAnnotation("@classParamTest4"));

        //Values
        $arrValues = $objReflection->getAnnotationValuesFromClass("@classParamTest1");
        $this->assertCount(1, $arrValues);
        $this->assertEquals("val1", $arrValues[0]);

        $arrValues = $objReflection->getAnnotationValuesFromClass("@classParamTest2");
        $this->assertCount(0, $arrValues);

        $arrValues = $objReflection->getAnnotationValuesFromClass("@classParamTest3");
        $this->assertCount(1, $arrValues);
        $this->assertEquals("val3", $arrValues[0]);

        $arrValues = $objReflection->getAnnotationValuesFromClass("@classParamTest4");
        $this->assertCount(0, $arrValues);

        //Params
        $arrParams = $objReflection->getAnnotationValuesFromClass("@classParamTest1");
        $this->assertCount(0, $arrParams);

        $arrParams = $objReflection->getAnnotationParamsFromClass("@classParamTest2");
        $this->assertCount(4, $arrParams);
        $this->assertArrayHasKey("param1", $arrParams);
        $this->assertArrayHasKey("param2", $arrParams);
        $this->assertArrayHasKey("param3", $arrParams);
        $this->assertArrayHasKey("param4", $arrParams);
        $this->assertEquals(0, $arrParams["param1"]);
        $this->assertEquals("abc", $arrParams["param2"]);
        $this->assertTrue(is_array($arrParams["param3"]));
        $this->assertEquals("0", $arrParams["param3"][0]);
        $this->assertEquals("123", $arrParams["param3"][1]);
        $this->assertEquals("456", $arrParams["param3"][2]);
        $this->assertEquals(999, $arrParams["param4"]);

        $arrParams = $objReflection->getAnnotationParamsFromClass("@classParamTest3");
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

        $arrParams = $objReflection->getAnnotationValuesFromClass("@classParamTest4");
        $this->assertCount(0, $arrParams);
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
 * @classTest val2
 * @classTest val3
 *
 * @classParamTest1 val1
 * @classParamTest2 (param1=0, param2="abc", param3={"0", 123, 456}, 999)
 * @classParamTest3 val3 (param1=0, param2="abc", param3={"0", 123, 456})
 * @classParamTest4
 * *
 */
class C extends A {

    /**
     * @propertyTest valB1
     * @propertyParamTest1 valB1(param1=0)
     * @propertyParamTest2 valB1
     * @propertyParamTest3(param1=0)
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

