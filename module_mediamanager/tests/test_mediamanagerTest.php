<?php

require_once (dirname(__FILE__)."/../system/class_testbase.php");

class class_test_galleries extends class_testbase  {



    public function testCreateDelete() {
        echo "creating a gallery..\n";

        $objGallery = new class_modul_gallery_gallery();
        $objGallery->setStrPath("/portal/pics/uploads");

        $this->assertTrue($objGallery->updateObjectToDb(),  __FILE__." create gallery");

        class_modul_gallery_pic::syncRecursive($objGallery->getSystemid(), $objGallery->getStrPath());

        $strSystemid = $objGallery->getSystemid();

        $this->flushDBCache();

        echo "deleting gallery...\n";
        $objGallery = new class_modul_gallery_gallery($strSystemid);

        $this->assertTrue($objGallery->deleteGalleryRecursive(true),  __FILE__." delete gallery");
        $this->assertTrue($objGallery->deleteGallery(),  __FILE__." delete gallery");
    }


}



