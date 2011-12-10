<?php

require_once (dirname(__FILE__)."/../../module_system/system/class_testbase.php");

class class_test_generalModelTest extends class_testbase  {



    public function testModuleModels() {

        class_carrier::getInstance()->getObjRights()->setBitTestMode(true);

        $arrFiles = class_resourceloader::getInstance()->getFolderContent("/system", array(".php"));

        /**
         * @var class_model[]
         */
        $arrClassInstances = array();
        $arrSystemids = array();


        foreach($arrFiles as $strOneFile) {
            if(uniStripos($strOneFile, "class_module_") !== false) {
                $objClass = new ReflectionClass(uniSubstr($strOneFile, 0, -4));
                if(!$objClass->isAbstract() && $objClass->isSubclassOf("class_model")) {

                    if(!in_array($objClass->getName(), array(
                        "class_module_pages_element",
                        "class_module_languages_languageset",
                        "class_module_system_session",
                        "class_module_system_setting",
                        "class_module_user_group",
                        "class_module_user_user",
                        "class_module_pages_pageelement")) ) {

                        $arrClassInstances[] = $objClass->newInstance();
                    }
                }
            }
        }


        /** @var $objOneInstance class_model */
        foreach($arrClassInstances as $objOneInstance) {

            $this->assertTrue($objOneInstance->updateObjectToDb(), "saving object ".get_class($objOneInstance));
            $arrSystemids[$objOneInstance->getSystemid()] = get_class($objOneInstance);

            echo "saved object of type ".get_class($objOneInstance)."@".$objOneInstance->getSystemid()."\n";
        }

        $objObjectfactory = class_objectfactory::getInstance();
        foreach($arrSystemids as $strSystemid => $strClass) {

            echo "instantiating ".$strSystemid."@".$strClass."\n";

            $objInstance = $objObjectfactory->getObject($strSystemid);

            $this->assertTrue($objInstance != null);

            $this->assertEquals(get_class($objInstance), $strClass);


            echo "deleting ".$strSystemid."@".$strClass."\n";
            $objInstance->deleteObject();
        }


        class_carrier::getInstance()->getObjRights()->setBitTestMode(false);

    }



}

