<?php
namespace Kajona\Pages\Tests;

use Kajona\System\System\SystemModule;
use Kajona\System\System\Testbase;

require_once __DIR__."../../../core/module_system/system/Testbase.php";

class PortalTest extends Testbase
{


    public function testPortal()
    {

        echo "generating all pages existing to find notices...\n";

        $arrPages = \Kajona\Pages\System\PagesPage::getAllPages();
        $objModule = SystemModule::getModuleByName("pages");

        /** @var $objModuleRequested \Kajona\Pages\Portal\PagesPortalController */
        $objModuleRequested = $objModule->getPortalInstanceOfConcreteModule();

        /** @var \Kajona\Pages\System\PagesPage $objOnePage */
        foreach ($arrPages as $objOnePage) {
            echo "generating page ".$objOnePage->getStrName()."\n";

            $objModuleRequested->setParam("page", $objOnePage->getStrName());
            $objModuleRequested->action("");

        }

    }

}



