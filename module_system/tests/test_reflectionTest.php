<?php

require_once (__DIR__."/../../module_system/system/class_testbase.php");

class class_test_reflection extends class_testbase  {

    public function testAnnotationsValueFromClass() {
        $objAnnotations = new class_reflection(new B());

        $arrClassAnnotations = $objAnnotations->getAnnotationValuesFromClass("@noval");
        $this->assertEquals(0, count($arrClassAnnotations));

        $arrClassAnnotations = $objAnnotations->getAnnotationValuesFromClass("@classTest");
        $this->assertEquals(2, count($arrClassAnnotations));
        $this->assertEquals("val2", $arrClassAnnotations[0]);
        $this->assertEquals("val1", $arrClassAnnotations[1]);

        $objAnnotations = new class_reflection(new A());

        $arrClassAnnotations = $objAnnotations->getAnnotationValuesFromClass("@noval");
        $this->assertEquals(0, count($arrClassAnnotations));

        $arrClassAnnotations = $objAnnotations->getAnnotationValuesFromClass("@classTest");
        $this->assertEquals(1, count($arrClassAnnotations));
        $this->assertEquals("val1", $arrClassAnnotations[0]);
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
        $this->assertEquals("valB1", $arrValues[0]);
        $this->assertEquals("propertyB1", $arrKeys[0]);

        $this->assertEquals("valB2", $arrValues[1]);
        $this->assertEquals("propertyB2", $arrKeys[1]);

        $this->assertEquals("valA1", $arrValues[2]);
        $this->assertEquals("propertyA1", $arrKeys[2]);


        $this->assertEquals("valB1", $objAnnotations->getAnnotationValueForProperty("propertyB1", "@propertyTest"));
        $this->assertEquals("valA1", $objAnnotations->getAnnotationValueForProperty("propertyA1", "@propertyTest"));

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

}

//set up test-structures

/**
 *
 * @classTest val1
 */
class A {

    /**
     * @propertyTest valA1
     */
    private $propertyA1;

    private $propertyA2;

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

