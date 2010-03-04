<?php

class class_test_class_image implements interface_testable {



    public function test() {

        echo "\ttesting class_image...\n";


        echo "\tloading an image from the samplecontent\n";

        $strImage = "/portal/pics/upload/samples/IMG_3000.JPG";

        //$strImage = "<img src=\""._webpath_."/portal/pics/upload/samples/IMG_3000.JPG\"/>";

        
        echo "\timage: ".$strImage."\n";
        $objImage = new class_image();
        $objImage->preLoadImage($strImage);

        //resize the image
        echo "\tresizing the image to 150 x 150\n";
        $objImage->resizeImage(150, 150, 0, true);

        $objImage->saveImage("", true);
        $strResizeCacheName1 = $objImage->getCachename();

        class_assertions::assertNotEqual($strResizeCacheName1 , "", __FILE__." getCachenameAfterResize");

        echo "\tcachename: ".$strResizeCacheName1."\n";
        echo "<img src=\""._webpath_._images_cachepath_.$strResizeCacheName1."\"/>";

        echo "\treplay test...\n";
        echo "\timage: ".$strImage."\n";
        $objImage = new class_image();
        $objImage->preLoadImage($strImage);

        //resize the image
        echo "\tresizing the image to 150 x 150\n";
        $objImage->resizeImage(150, 150, 0, true);

        $objImage->saveImage("", true);
        $strResizeCacheName2 = $objImage->getCachename();

        class_assertions::assertEqual($strResizeCacheName2, $strResizeCacheName2, __FILE__." getCachenameAfterResize");

        echo "\tcachename: ".$strResizeCacheName2."\n";
        //echo "<img src=\""._webpath_._images_cachepath_.$strResizeCacheName2."\"/>";



        echo "\tresize & text...\n";
        echo "\timage: ".$strImage."\n";
        $objImage = new class_image("test1020");
        $objImage->preLoadImage($strImage);

        //resize the image
        $objImage->resizeImage(150, 150, 0, true);
        $objImage->imageText("test", 10, 20);

        $objImage->saveImage("", true);
        $strResizeCacheName3 = $objImage->getCachename();

        class_assertions::assertTrue($strResizeCacheName3 != "", __FILE__." getCachenameAfterResize&Text");

        echo "\tcachename: ".$strResizeCacheName3."\n";
        echo "<img src=\""._webpath_._images_cachepath_.$strResizeCacheName3."\"/>";

        echo "\tresize & text...\n";
        echo "\timage: ".$strImage."\n";
        $objImage = new class_image("test2030");
        $objImage->preLoadImage($strImage);

        //resize the image
        $objImage->resizeImage(150, 150, 0, true);
        $objImage->imageText("test", 20, 30);

        $objImage->saveImage("", true);
        $strResizeCacheName4 = $objImage->getCachename();

        class_assertions::assertNotEqual($strResizeCacheName3, $strResizeCacheName4, __FILE__." getCachenameAfterResize&Text");

        echo "\tcachename: ".$strResizeCacheName3."\n";
        echo "<img src=\""._webpath_._images_cachepath_.$strResizeCacheName4."\"/>";

        echo "\ttest image overlay.\n";

        $objImage = new class_image("overlay".$strResizeCacheName4);
        $objImage->preLoadImage($strImage);
        $objImage->resizeAndCropImage(300, 300, 300, 300);

        $objImage->overlayImage(_images_cachepath_.$strResizeCacheName4, 10, 10, true);
        $objImage->saveImage("", true);
        $strResizeCacheName5 = $objImage->getCachename();

        echo "\tcachename: ".$strResizeCacheName5."\n";
        echo "<img src=\""._webpath_._images_cachepath_.$strResizeCacheName5."\"/>";



    }

}

?>