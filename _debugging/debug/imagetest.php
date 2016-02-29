<?php
namespace Kajona\Debugging\Debug;
use Kajona\System\System\Image2;
use Kajona\System\System\Imageplugins\ImageRotate;
use Kajona\System\System\Imageplugins\ImageScaleAndCrop;

$floatAngle = 90.0;

$objImage = new \Kajona\System\System\Image2();
$objImage->setUseCache(false);
if (!$objImage->load("/files/images/samples/P9066809.JPG")) {
    echo "Could not load file.\n";
}

$objImage->addOperation(new ImageRotate($floatAngle, "#ffffffff"));
$objImage->addOperation(new ImageScaleAndCrop(800, 1350));

if (!$objImage->save("/files/cache/P9066809_transformed.PNG", Image2::FORMAT_PNG)) {
    echo "File not saved.\n";
}