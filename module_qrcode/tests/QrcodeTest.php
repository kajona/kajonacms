<?php

namespace Kajona\Qrcode\Tests;

require_once __DIR__ . "/../../../core/module_system/system/Testbase.php";

use Kajona\Qrcode\System\Qrcode;
use Kajona\System\System\Testbase;

class QrcodeTest extends Testbase
{

    public function testQrcode()
    {

        echo "test qrcode...\n";

        $objQrCode = new Qrcode();

        $strImage1 = $objQrCode->getImageForString("Kajona Test Image");
        $this->assertFileExists(_realpath_ . $strImage1);

        $strImage2 = $objQrCode->getImageForString(_webpath_);
        $this->assertFileExists(_realpath_ . $strImage2);


        echo "\t <img src=\"" . _webpath_ . $strImage1 . "\" />\n";
        echo "\t <img src=\"" . _webpath_ . $strImage2 . "\" />\n";

    }


}
