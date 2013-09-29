<?php

$objImage = new class_image2();
if (!$objImage->load("/files/images/samples/PA252134.JPG")) {
    echo "Could not load file.\n";
}

if (!$objImage->save("/files/cache/PA252134.PNG", class_image2::FORMAT_PNG)) {
    echo "File not saved.\n";
}