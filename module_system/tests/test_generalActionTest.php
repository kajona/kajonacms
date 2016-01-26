<?php

require_once (__DIR__."/../../module_system/system/class_testbase.php");

class class_test_generalActionTest extends class_testbase  {



    public function testAdminModules() {

        class_adminskin_helper::defineSkinWebpath();
        class_carrier::getInstance()->getObjRights()->setBitTestMode(true);

        //load all admin-classes
        $arrFiles = class_resourceloader::getInstance()->getFolderContent("/admin", array(".php"), false, null,
        function(&$strOneFile, $strPath) {
            $strOneFile = class_classloader::getInstance()->getInstanceFromFilename($strPath, "class_admin_controller", null, array(), true);
        });

        foreach($arrFiles as $objAdminInstance) {
            if($objAdminInstance !== null)
                $this->runSingleFile($objAdminInstance);
        }

        class_carrier::getInstance()->getObjRights()->setBitTestMode(false);

    }



    public function testPortalModules() {


        class_carrier::getInstance()->getObjRights()->setBitTestMode(true);

        //load all admin-classes
        $arrFiles = class_resourceloader::getInstance()->getFolderContent("/portal", array(".php"), false, function($strOneFile) {
            if(preg_match("/class_module_(.*)_portal.php/i", $strOneFile)) {
                $strClassname = uniSubstr($strOneFile, 0, -4);
                $objReflection = new ReflectionClass($strClassname);
                if(!$objReflection->isAbstract()) {
                    return true;
                }
            }
            return false;
        },
        function(&$strOneFile, $strPath) {
            $strOneFile = class_classloader::getInstance()->getInstanceFromFilename($strPath, "class_portal_controller", null, array(), true);
        });

        foreach($arrFiles as $objPortalInstance) {
            if($objPortalInstance !== null)
                $this->runSingleFile($objPortalInstance);
        }

        class_carrier::getInstance()->getObjRights()->setBitTestMode(false);

    }


    /**
     * @param interface_admin|interface_portal $objViewInstance
     */
    private function runSingleFile($objViewInstance) {

        $objReflection = new ReflectionClass($objViewInstance);
        $arrMethods = $objReflection->getMethods();

        $objAnnotations = new class_reflection(get_class($objViewInstance));

        //collect the autotestable annotations located on class-level
        foreach($objAnnotations->getAnnotationValuesFromClass("@autoTestable") as $strValue) {
            foreach(explode(",", $strValue) as $strOneMethod) {
                echo "found method ".get_class($objViewInstance)."@".$strOneMethod." marked as class-based @autoTestable, preparing call\n";
                echo "   calling via action() method\n";
                $objViewInstance->action($strOneMethod);
            }
        }


        /** @var ReflectionMethod $objOneMethod */
        foreach($arrMethods as $objOneMethod) {

            if($objAnnotations->hasMethodAnnotation($objOneMethod->getName(), "@autoTestable")) {
                echo "found method ".get_class($objViewInstance)."@".$objOneMethod->getName()." marked as @autoTestable, preparing call\n";

                if(uniSubstr($objOneMethod->getName(), 0, 6) == "action" && $objReflection->hasMethod("action")) {
                    echo "   calling via action() method\n";
                    $objViewInstance->action(uniSubstr($objOneMethod->getName(), 6));
                }
                else {
                    echo "   direct call";
                    $objOneMethod->invoke($objViewInstance);
                }
            }
        }

    }

}
