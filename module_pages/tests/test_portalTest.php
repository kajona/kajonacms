<?php

require_once __DIR__."../../../core/module_system/system/Testbase.php";
class class_test_portalTest extends class_testbase  {



    public function testPortal() {

        echo "generating all pages existing to find notices...\n";

        $arrPages = \Kajona\Pages\System\PagesPage::getAllPages();
        $objModule = class_module_system_module::getModuleByName("pages");

        /** @var $objModuleRequested \Kajona\Pages\Portal\PagesPortalController */
        $objModuleRequested = $objModule->getPortalInstanceOfConcreteModule();

        /** @var \Kajona\Pages\System\PagesPage $objOnePage */
        foreach($arrPages as $objOnePage) {
            echo "generating page ".$objOnePage->getStrName()."\n";

            $objModuleRequested->setParam("page", $objOnePage->getStrName());
            $objModuleRequested->action("");

        }

    }

}



