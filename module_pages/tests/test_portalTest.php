<?php

require_once (__DIR__."/../../module_system/system/class_testbase.php");

class class_test_portalTest extends class_testbase  {



    public function testPortal() {

        echo "generating all pages existing to find notices...\n";

        $arrPages = class_module_pages_page::getAllPages();
        $objDispatcher = new class_request_dispatcher();

        $objModule = class_module_system_module::getModuleByName("pages");

        /** @var $objModuleRequested class_module_pages_portal */
        $objModuleRequested = $objModule->getPortalInstanceOfConcreteModule();

        /** @var class_module_pages_page $objOnePage */
        foreach($arrPages as $objOnePage) {
            echo "generating page ".$objOnePage->getStrName()."\n";

            $objModuleRequested->setParam("page", $objOnePage->getStrName());
            $objModuleRequested->action("");

        }

    }

}



