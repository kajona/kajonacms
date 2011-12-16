<?php

require_once (__DIR__."/../../module_system/system/class_testbase.php");

class class_test_class_image extends class_testbase  {



    public function test() {

        echo "\ttesting class_image...\n";


        echo "\tloading an image from the samplecontent\n";

        $strImage = "/files/images/samples/IMG_3000.JPG";

        //$strImage = "<img src=\""._webpath_."/portal/pics/upload/samples/IMG_3000.JPG\"/>";


        echo "\timage: ".$strImage."\n";
        $objImage = new class_image();
        $objImage->preLoadImage($strImage);

        //resize the image
        echo "\tresizing the image to 150 x 150\n";
        $objImage->resizeImage(150, 150, 0, true);

        $objImage->saveImage("", true);
        $strResizeCacheName1 = $objImage->getCachename();

        $this->assertNotEquals($strResizeCacheName1 , "", __FILE__." getCachenameAfterResize");

        echo "\tcachename: ".$strResizeCacheName1."\n";
        echo "<img src=\""._webpath_._images_cachepath_.$strResizeCacheName1."\"/>";

        $this->assertFileExists(_realpath_._images_cachepath_.$strResizeCacheName1,  __FILE__." getCachenameAfterResize");

        echo "\treplay test...\n";
        echo "\timage: ".$strImage."\n";
        $objImage = new class_image();
        $objImage->preLoadImage($strImage);

        //resize the image
        echo "\tresizing the image to 150 x 150\n";
        $objImage->resizeImage(150, 150, 0, true);

        $objImage->saveImage("", true);
        $strResizeCacheName2 = $objImage->getCachename();

        $this->assertEquals($strResizeCacheName2, $strResizeCacheName2, __FILE__." getCachenameAfterResize");

        echo "\tcachename: ".$strResizeCacheName2."\n";
        //echo "<img src=\""._webpath_._images_cachepath_.$strResizeCacheName2."\"/>";

        $this->assertFileExists(_realpath_._images_cachepath_.$strResizeCacheName2,  __FILE__." getCachenameAfterResize");


        echo "\tresize & text...\n";
        echo "\timage: ".$strImage."\n";
        $objImage = new class_image("test1020");
        $objImage->preLoadImage($strImage);

        //resize the image
        $objImage->resizeImage(150, 150, 0, true);
        $objImage->imageText("test", 10, 20);

        $objImage->saveImage("", true);
        $strResizeCacheName3 = $objImage->getCachename();

        $this->assertTrue($strResizeCacheName3 != "", __FILE__." getCachenameAfterResize&Text");

        echo "\tcachename: ".$strResizeCacheName3."\n";
        echo "<img src=\""._webpath_._images_cachepath_.$strResizeCacheName3."\"/>";

        $this->assertFileExists(_realpath_._images_cachepath_.$strResizeCacheName3,  __FILE__." getCachenameAfterResize");

        echo "\tresize & text...\n";
        echo "\timage: ".$strImage."\n";
        $objImage = new class_image("test2030");
        $objImage->preLoadImage($strImage);

        //resize the image
        $objImage->resizeImage(150, 150, 0, true);
        $objImage->imageText("test", 20, 30);

        $objImage->saveImage("", true);
        $strResizeCacheName4 = $objImage->getCachename();

        $this->assertNotEquals($strResizeCacheName3, $strResizeCacheName4, __FILE__." getCachenameAfterResize&Text");

        echo "\tcachename: ".$strResizeCacheName3."\n";
        echo "<img src=\""._webpath_._images_cachepath_.$strResizeCacheName4."\"/>";

        $this->assertFileExists(_realpath_._images_cachepath_.$strResizeCacheName4,  __FILE__." getCachenameAfterResize");

        echo "\ttest image overlay.\n";

        $objImage = new class_image("overlay".$strResizeCacheName4);
        $objImage->preLoadImage($strImage);
        $objImage->resizeAndCropImage(300, 300, 300, 300);

        $objImage->overlayImage(_images_cachepath_.$strResizeCacheName4, 10, 10, true);
        $objImage->saveImage("", true);
        $strResizeCacheName5 = $objImage->getCachename();

        echo "\tcachename: ".$strResizeCacheName5."\n";
        echo "<img src=\""._webpath_._images_cachepath_.$strResizeCacheName5."\"/>";

        $this->assertFileExists(_realpath_._images_cachepath_.$strResizeCacheName5,  __FILE__." getCachenameAfterResize");



    }

}

