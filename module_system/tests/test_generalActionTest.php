<?php

require_once (__DIR__."/../../module_system/system/class_testbase.php");

class class_test_generalActionTest extends class_testbase  {



    public function testAdminModules() {

        class_carrier::getInstance()->getObjRights()->setBitTestMode(true);

        //load all admin-classes
        $arrFiles = class_resourceloader::getInstance()->getFolderContent("/admin");

        foreach($arrFiles as $strOneFile) {
            if(preg_match("/class_module_(.*)_admin.php/i", $strOneFile)) {
                $strClassname = uniSubstr($strOneFile, 0, -4);

                /** @var interface_admin|class_admin $objAdminInstance */
                $objAdminInstance = new $strClassname();
                $objReflection = new ReflectionClass($objAdminInstance);
                $arrMethods = $objReflection->getMethods();

                /** @var ReflectionMethod $objOneMethod */
                foreach($arrMethods as $objOneMethod) {

                    $objAnnotations = new class_annotations(get_class($objAdminInstance));

                    if($objAnnotations->hasMethodAnnotation($objOneMethod->getName(), "@autoTestable")) {
                        echo "found method ".$strClassname."@".$objOneMethod->getName()." marked as @autoTestable, preparing call\n";


                        if(uniSubstr($objOneMethod->getName(), 0, 6) == "action" && $objReflection->hasMethod("action")) {
                            echo "   calling via action() method\n";
                            $objAdminInstance->action(uniSubstr($objOneMethod->getName(), 6));
                        }
                        else {
                            echo "   direct call";
                            $objOneMethod->invoke($objAdminInstance);
                        }
                    }
                }

            }
        }

        class_carrier::getInstance()->getObjRights()->setBitTestMode(false);

    }



}

