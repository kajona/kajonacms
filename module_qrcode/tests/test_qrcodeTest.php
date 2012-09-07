<?php
require_once (__DIR__ . "/../../module_system/system/class_testbase.php");

class class_test_qrcode extends class_testbase  {

    public function testQrcode() {

        echo "test qrcode...\n";

        $objQrCode = new class_qrcode();

        $strImage1 = $objQrCode->getImageForString("Kajona Test Image");
        $this->assertFileExists(_realpath_.$strImage1);

        $strImage2 = $objQrCode->getImageForString(_webpath_);
        $this->assertFileExists(_realpath_.$strImage2);


        echo "\t <img src=\""._webpath_.$strImage1."\" />\n";
        echo "\t <img src=\""._webpath_.$strImage2."\" />\n";

    }


}
