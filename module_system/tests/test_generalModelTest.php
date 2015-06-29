<?php

require_once (__DIR__."/../../module_system/system/class_testbase.php");

class class_test_generalModelTest extends class_testbase {


    public function testModuleModels() {

        echo "preparing object saves...\n";

        class_carrier::getInstance()->getObjRights()->setBitTestMode(true);

        $arrFiles = class_resourceloader::getInstance()->getFolderContent("/system", array(".php"), false, function($strOneFile) {
            if(uniStripos($strOneFile, "class_module_") !== false) {
                $objClass = new ReflectionClass(uniSubstr($strOneFile, 0, -4));
                if(!$objClass->isAbstract() && $objClass->isSubclassOf("class_model")) {

                    $objAnnotations = new class_reflection(uniSubstr($strOneFile, 0, -4));

                    //block from autotesting?
                    if($objAnnotations->hasClassAnnotation("@blockFromAutosave")) {
                        echo "skipping class ".uniSubstr($strOneFile, 0, -4)." due to @blockFromAutosave annotation"."\n";
                        return false;
                    }

                    return true;
                }
            }

            return false;
        },
        function(&$strOneFile) {
            $strOneFile = uniSubstr($strOneFile, 0, -4);
            $strOneFile = new $strOneFile();
        });

        $arrSystemids = array();

        /** @var $objOneInstance class_model */
        foreach($arrFiles as $objOneInstance) {
            echo "testing object of type ".get_class($objOneInstance)."@".$objOneInstance->getSystemid()."\n";
            $this->assertTrue($objOneInstance->updateObjectToDb(), "saving object ".get_class($objOneInstance));
            $arrSystemids[$objOneInstance->getSystemid()] = get_class($objOneInstance);
            echo " ...saved object of type ".get_class($objOneInstance)."@".$objOneInstance->getSystemid()."\n";
        }

        $objObjectfactory = class_objectfactory::getInstance();
        foreach($arrSystemids as $strSystemid => $strClass) {

            echo "instantiating ".$strSystemid."@".$strClass."\n";

            $objInstance = $objObjectfactory->getObject($strSystemid);

            $this->assertTrue($objInstance != null);

            $this->assertEquals(get_class($objInstance), $strClass);


            echo "deleting ".$strSystemid."@".$strClass."\n";
            $objInstance->deleteObjectFromDatabase();
        }


        class_carrier::getInstance()->getObjRights()->setBitTestMode(false);

    }

}

