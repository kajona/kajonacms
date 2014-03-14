<?php

require_once (__DIR__."/../../module_system/system/class_testbase.php");

class class_test_resourceloader extends class_testbase  {

    public function testResourceloader() {

        $arrContent = class_resourceloader::getInstance()->getFolderContent("/admin", array(".php"), false);

        $this->assertTrue(in_array("class_admin.php", $arrContent));
        $this->assertTrue(in_array("class_admin_batchaction.php", $arrContent));
        $this->assertTrue(!in_array("class_systemtask_base.php", $arrContent));
        $this->assertTrue(!in_array("formentries", $arrContent));



        $arrContent = class_resourceloader::getInstance()->getFolderContent("/admin", array(), true);

        $this->assertTrue(in_array("class_admin.php", $arrContent));
        $this->assertTrue(in_array("formentries", $arrContent));
        $this->assertTrue(in_array("class_admin_batchaction.php", $arrContent));
        $this->assertTrue(!in_array("class_systemtask_base.php", $arrContent));
        $this->assertTrue(!in_array("class_formentry_base.php", $arrContent));

    }



}

