<?php

$floatAngle = 90.0;

$objImage = new class_image2();
if (!$objImage->load("/files/images/samples/PA252134.JPG")) {
    echo "Could not load file.\n";
}

$objImage->addOperation(new class_image_rotate($floatAngle, "#ffffffff"));

if (!$objImage->save("/files/cache/PA252134.PNG", class_image2::FORMAT_PNG)) {
    echo "File not saved.\n";
}