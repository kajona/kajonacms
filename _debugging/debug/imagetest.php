<?php

$floatAngle = 90.0;

$objImage = new class_image2();
$objImage->setUseCache(false);
if (!$objImage->load("/files/images/samples/P9066809.JPG")) {
    echo "Could not load file.\n";
}

$objImage->addOperation(new class_image_rotate($floatAngle, "#ffffffff"));
$objImage->addOperation(new class_image_scale_and_crop(800, 1350));

if (!$objImage->save("/files/cache/P9066809_transformed.PNG", class_image2::FORMAT_PNG)) {
    echo "File not saved.\n";
}