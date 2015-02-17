<?php
/*"******************************************************************************************************
*   (c) 2013-2015 by Kajona, www.kajona.de                                                              *
*       Published under the GNU LGPL v2.1, see /system/licence_lgpl.txt                                 *
*-------------------------------------------------------------------------------------------------------*
*	$Id$	                                            *
********************************************************************************************************/

/**
 */
class class_image_overlay extends class_image_abstract_operation {
    /**
     * @var class_image2
     */
    private $objImage;
    private $intX;
    private $intY;
    private $bitAlphaBlending;

    /**
     * @param class_image2 $objImage
     * @param int $intX
     * @param int $intY
     * @param bool $bitAlphaBlending
     */
    public function __construct(class_image2 $objImage, $intX, $intY, $bitAlphaBlending = true) {
        $this->objImage = $objImage;
        $this->intX = $intX;
        $this->intY = $intY;
        $this->bitAlphaBlending = $bitAlphaBlending;
    }

    /**
     * @param resource &$objResource
     *
     * @return bool
     */
    public function render(&$objResource) {
        $objOverlayResource = $this->objImage->createGdResource();
        $intOverlayWidth = imagesx($objOverlayResource);
        $intOverlayHeight = imagesy($objOverlayResource);

        imagealphablending($objResource, $this->bitAlphaBlending);
        imagealphablending($objOverlayResource, $this->bitAlphaBlending);

        $bitSuccess = imagecopy($objResource, $objOverlayResource, $this->intX, $this->intY, 0, 0, $intOverlayWidth, $intOverlayHeight);

        imagealphablending($objResource, false);
        imagealphablending($objOverlayResource, false);

        return $bitSuccess;
    }

    /**
     * @return array
     */
    public function getCacheIdValues() {
        $arrValues = array(
            $this->objImage->getCacheId(),
            $this->intX,
            $this->intY,
            $this->bitAlphaBlending
        );
        return $arrValues;
    }
}