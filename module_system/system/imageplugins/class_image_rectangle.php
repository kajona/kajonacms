<?php
/*"******************************************************************************************************
*   (c) 2013-2014 by Kajona, www.kajona.de                                                              *
*       Published under the GNU LGPL v2.1, see /system/licence_lgpl.txt                                 *
*-------------------------------------------------------------------------------------------------------*
*	$Id$	                                            *
********************************************************************************************************/

/**
 * Implements an operation to draw a rectangle
 */
class class_image_rectangle extends class_image_abstract_operation {
    private $intX;
    private $intY;
    private $intWidth;
    private $intHeight;
    private $arrColor;

    /**
     * @param int $intX
     * @param int $intY
     * @param int $intWidth
     * @param int $intHeight
     * @param string $strColor
     */
    public function __construct($intX, $intY, $intWidth, $intHeight, $strColor = "#FFFFFF") {
        $this->intX = $intX;
        $this->intY = $intY;
        $this->arrColor = class_image2::parseColorRgb($strColor);
        $this->intWidth = $intWidth;
        $this->intHeight = $intHeight;
    }

    /**
     * @param resource &$objResource
     *
     * @return bool
     */
    public function render(&$objResource) {
        $intColor = $this->allocateColor($objResource, $this->arrColor);
        return imagefilledrectangle($objResource, $this->intX, $this->intY, ($this->intX + $this->intWidth), ($this->intY + $this->intHeight), $intColor);
    }

    /**
     * @return array
     */
    public function getCacheIdValues() {
        $arrValues = array(
            $this->intX,
            $this->intY,
            $this->intHeight,
            $this->intWidth,
        );
        $arrValues = array_merge($arrValues, $this->arrColor);
        return $arrValues;
    }
}