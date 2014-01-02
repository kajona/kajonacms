<?php
/*"******************************************************************************************************
*   (c) 2013-2014 by Kajona, www.kajona.de                                                              *
*       Published under the GNU LGPL v2.1, see /system/licence_lgpl.txt                                 *
*-------------------------------------------------------------------------------------------------------*
*	$Id$	                                            *
********************************************************************************************************/

/**
 * Implements an image scaling operation.
 * The scaling retains the aspect ration.
 */
class class_image_crop extends class_image_abstract_operation {
    private $intX;
    private $intY;
    private $intWidth;
    private $intHeight;

    public function __construct($intX, $intY, $intWidth, $intHeight) {
        $this->intX = $intX < 0 ? 0 : (int)$intX;
        $this->intY = $intY < 0 ? 0 : (int)$intY;
        $this->intWidth = (int)$intWidth;
        $this->intHeight = (int)$intHeight;
    }

    public function render(&$objResource) {
        // Crop the image
        $objCroppedResource = $this->createImageResource($this->intWidth, $this->intHeight);
        $bitSuccess = imagecopy($objCroppedResource, $objResource,
            0, 0, // Destination X, Y
            $this->intX, $this->intY, // Source X, Y
            $this->intWidth, $this->intHeight);

        if (!$bitSuccess) {
            imagedestroy($objCroppedResource);
            return false;
        }

        $objResource = $objCroppedResource;
        return true;
    }

    public function getCacheIdValues() {
        return array($this->intX, $this->intY, $this->intWidth, $this->intHeight);
    }
}