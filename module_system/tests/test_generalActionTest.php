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

                $objAdminInstance = new $strClassname();
                $this->runSingleFile($objAdminInstance);
            }
        }

        class_carrier::getInstance()->getObjRights()->setBitTestMode(false);

    }



    public function testPortalModules() {


        class_carrier::getInstance()->getObjRights()->setBitTestMode(true);

        //load all admin-classes
        $arrFiles = class_resourceloader::getInstance()->getFolderContent("/portal");

        foreach($arrFiles as $strOneFile) {
            if(preg_match("/class_module_(.*)_portal.php/i", $strOneFile)) {
                $strClassname = uniSubstr($strOneFile, 0, -4);

                $objPortalInstance = new $strClassname(array());
                $this->runSingleFile($objPortalInstance);
            }
        }

        class_carrier::getInstance()->getObjRights()->setBitTestMode(false);

    }


    /**
     * @param interface_admin|interface_portal $objViewInstance
     */
    private function runSingleFile($objViewInstance) {

        $objReflection = new ReflectionClass($objViewInstance);
        $arrMethods = $objReflection->getMethods();

        /** @var ReflectionMethod $objOneMethod */
        foreach($arrMethods as $objOneMethod) {

            $objAnnotations = new class_annotations(get_class($objViewInstance));

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


class class_xml {

    /**
     * If set to true, the output will be sent without the mandatory xml-head-element
     * @param bool $bitSuppressXmlHeader
     */
    public static function setBitSuppressXmlHeader($bitSuppressXmlHeader) {
    }

}