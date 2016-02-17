<?php

namespace Kajona\System\Tests;
use class_module_news_news;
use Kajona\System\Admin\AdminFormgenerator;
use Kajona\System\System\SystemModule;
use Kajona\System\System\Testbase;

class ObjectValidatorTest extends Testbase  {

    protected function setUp() {
        parent::setUp();
    }


    /**
     * Checks if the references of the source object is correctly set after validation
     */
    public function testObjectValidator() {
        $objModule = SystemModule::getModuleByName("news");
        if($objModule == null) {
            return;
        }

        $objNews = new class_module_news_news();
        $objForm = new AdminFormgenerator("news", $objNews);
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


    protected function tearDown() {
        parent::tearDown();
    }
}

