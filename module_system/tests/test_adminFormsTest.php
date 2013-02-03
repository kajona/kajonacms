<?php

require_once (__DIR__."/../../module_system/system/class_testbase.php");

class class_test_adminFormsTest extends class_testbase  {


    public function testFormManager() {

        $objFormManager = new class_admin_formgenerator("test", new AdminFormB());

        $objFormManager->generateFieldsFromObject();


        $this->assertNotNull($objFormManager->getField("fielda1"));
        $this->assertNotNull($objFormManager->getField("fielda2"));
        $this->assertNotNull($objFormManager->getField("fieldb1"));
        $this->assertNotNull($objFormManager->getField("fieldb2"));

        $arrFields = $objFormManager->getArrFields();
        $arrKey = array_keys($arrFields);

        $this->assertEquals($arrKey[0], "fieldb1");
        $this->assertEquals($arrKey[1], "fieldb2");
        $this->assertEquals($arrKey[2], "fielda1");
        $this->assertEquals($arrKey[3], "fielda2");

        $objFormManager->setFieldToPosition("fielda2", 1);
        $objFormManager->setFieldToPosition("fieldb2", 4);

        $arrFields = $objFormManager->getArrFields();
        $arrKey = array_keys($arrFields);

        $this->assertEquals($arrKey[0], "fielda2");
        $this->assertEquals($arrKey[1], "fieldb1");
        $this->assertEquals($arrKey[2], "fielda1");
        $this->assertEquals($arrKey[3], "fieldb2");
    }

}

//set up test-structures

class AdminFormA extends class_model {

    /**
     * @var
     * @fieldType text
     */
    private $strFieldA1;

    /**
     * @var
     * @fieldType text
     */
    private $strFieldA2;

    /**
     * @param  $strFieldA1
     */
    public function setStrFieldA1($strFieldA1) {
        $this->strFieldA1 = $strFieldA1;
    }

    /**
     * @return
     */
    public function getStrFieldA1() {
        return $this->strFieldA1;
    }

    /**
     * @param  $strFieldA2
     */
    public function setStrFieldA2($strFieldA2) {
        $this->strFieldA2 = $strFieldA2;
    }

    /**
     * @return
     */
    public function getStrFieldA2() {
        return $this->strFieldA2;
    }


}

class AdminFormB extends AdminFormA {

    /**
     * @var
     * @fieldType text
     */
    private $strFieldB1;

    /**
     * @var
     * @fieldType text
     */
    private $strFieldB2;

    /**
     * @param  $strFieldB1
     */
    public function setStrFieldB1($strFieldB1) {
        $this->strFieldB1 = $strFieldB1;
    }

    /**
     * @return
     */
    public function getStrFieldB1() {
        return $this->strFieldB1;
    }

    /**
     * @param  $strFieldB2
     */
    public function setStrFieldB2($strFieldB2) {
        $this->strFieldB2 = $strFieldB2;
    }

    /**
     * @return
     */
    public function getStrFieldB2() {
        return $this->strFieldB2;
    }



}

