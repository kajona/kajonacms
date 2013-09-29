<?php
/**
 * Implements an image rotation operation.
 */
class class_image_rotate extends class_image_abstract_operation {
    private $floatAngle;
    private $arrColor;

    public function __construct($floatAngle, $strColor = "#000000") {
        $this->floatAngle = $floatAngle;
        $this->arrColor = class_image2::parseColorRgb($strColor);
    }

    public function render(&$objResource) {
        $intColor = $this->allocateColor($objResource, $this->arrColor);

        if ($intColor === null) {
            return false;
        }

        imagealphablending($objResource, true);
        $objResource = imagerotate($objResource, $this->floatAngle, $intColor);
        imagealphablending($objResource, false);
        imagesavealpha($objResource, true);
    }

    public function getCacheIdValues() {
        $arrValues = array($this->floatAngle);
        $arrValues = array_merge($arrValues, $this->arrColor);
        return $arrValues;
    }
}