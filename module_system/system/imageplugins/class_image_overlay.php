<?php
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

    public function __construct(class_image2 $objImage, $intX, $intY, $bitAlphaBlending = true) {
        $this->objImage = $objImage;
        $this->intX = $intX;
        $this->intY = $intY;
        $this->bitAlphaBlending = $bitAlphaBlending;
    }

    public function render(&$objResource) {
        $objOverlayResource = $this->objImage->createGdResource();
        $intOverlayWidth = $this->objImage->getWidth();
        $intOverlayHeight = $this->objImage->getHeight();

        imagealphablending($objResource, $this->bitAlphaBlending);
        imagealphablending($objOverlayResource, $this->bitAlphaBlending);

        $bitSuccess = imagecopy($objResource, $objOverlayResource, $this->intX, $this->intY, 0, 0, $intOverlayWidth, $intOverlayHeight);

        imagealphablending($objResource, false);
        imagealphablending($objOverlayResource, false);

        return $bitSuccess;
    }

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