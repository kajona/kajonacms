<?php

require_once (__DIR__."/../../module_system/system/class_testbase.php");

class class_objectValidatorTest extends class_testbase  {

    protected function setUp() {
        parent::setUp();
    }


    /**
     * Checks if the references of the source object is correctly set after validation
     */
    public function testObjectValidator() {
        $objModule = class_module_system_module::getModuleByName("news");
        if($objModule == null) {
            return;
        }

        $objNews = new class_module_news_news();
        $objForm = new class_admin_formgenerator("news", $objNews);
        $objForm->generateFieldsFromObject();

        $objSourceObjectBefore = $objForm->getObjSourceobject();
        $arrFieldsBefore = $objForm->getArrFields();
        $objForm->validateForm();
        $objSourceObjectAfter = $objForm->getObjSourceobject();
        $arrFieldsAfter = $objForm->getArrFields();


        //Now check if the reference to the source object before validation is the same as after
        foreach($objForm->getArrFields() as $intIndex => $objField) {
            if($arrFieldsAfter != null) {
                $this->assertTrue($arrFieldsBefore[$intIndex]->getObjSourceObject() === $arrFieldsAfter[$intIndex]->getObjSourceObject());

                if($arrFieldsBefore[$intIndex]->getObjSourceObject() != null) {
                    $this->assertTrue($arrFieldsBefore[$intIndex]->getObjSourceObject() === $objSourceObjectBefore);
                    $this->assertTrue($arrFieldsBefore[$intIndex]->getObjSourceObject() === $objSourceObjectAfter);
                }

                if($arrFieldsAfter[$intIndex]->getObjSourceObject() != null) {
                    $this->assertTrue($arrFieldsAfter[$intIndex]->getObjSourceObject() === $objSourceObjectBefore);
                    $this->assertTrue($arrFieldsAfter[$intIndex]->getObjSourceObject() === $objSourceObjectAfter);
                }
            }
        }
        $this->assertTrue($objSourceObjectAfter === $objSourceObjectBefore);
    }

    public function testValidator() {
    }


    protected function tearDown() {
        parent::tearDown();
    }
}

