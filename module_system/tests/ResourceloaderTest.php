<?php

namespace Kajona\System\Tests;
require_once __DIR__."../../../core/module_system/system/Testbase.php";
use Kajona\System\System\Resourceloader;
use Kajona\System\System\Testbase;

class ResourceloaderTest extends Testbase  {

    public function testResourceloader() {

        $arrContent = Resourceloader::getInstance()->getFolderContent("/admin", array(".php"), false);

        $this->assertTrue(in_array("AdminController.php", $arrContent));
        $this->assertTrue(in_array("AdminBatchaction.php", $arrContent));
        $this->assertTrue(!in_array("SystemtaskBase.php", $arrContent));
        $this->assertTrue(!in_array("formentries", $arrContent));



        $arrContent = Resourceloader::getInstance()->getFolderContent("/admin", array(), true);

        $this->assertTrue(in_array("AdminController.php", $arrContent));
        $this->assertTrue(in_array("formentries", $arrContent));
        $this->assertTrue(in_array("AdminBatchaction.php", $arrContent));
        $this->assertTrue(!in_array("SystemtaskBase.php", $arrContent));
        $this->assertTrue(!in_array("FormentryBase.php", $arrContent));

    }



}

