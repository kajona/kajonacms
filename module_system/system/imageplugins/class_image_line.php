<?php
/*"******************************************************************************************************
*   (c) 2013-2015 by Kajona, www.kajona.de                                                              *
*       Published under the GNU LGPL v2.1, see /system/licence_lgpl.txt                                 *
*-------------------------------------------------------------------------------------------------------*
*	$Id$	                                            *
********************************************************************************************************/

/**
 * Implements an operation to draw a line
 */
class class_image_line extends class_image_abstract_operation {
    private $intStartX;
    private $intStartY;
    private $intEndX;
    private $intEndY;
    private $arrColor;

    //$intStartX, $intStartY, $intEndX, $intEndY, $intColor
    /**
     * @param int $intStartX
     * @param int $intStartY
     * @param int $intEndX
     * @param int $intEndY
     * @param string $strColor
     */
    public function __construct($intStartX, $intStartY, $intEndX, $intEndY, $strColor = "#FFFFFF") {
        $this->intStartX = $intStartX;
        $this->intStartY = $intStartY;
        $this->arrColor = class_image2::parseColorRgb($strColor);
        $this->intEndX = $intEndX;
        $this->intEndY = $intEndY;
    }

    /**
     * @param resource &$objResource
     *
     * @return bool
     */
    public function render(&$objResource) {
        $intColor = $this->allocateColor($objResource, $this->arrColor);
        return imageline($objResource, $this->intStartX, $this->intStartY, $this->intEndX, $this->intEndY, $intColor);
    }

    /**
     * @return array
     */
    public function getCacheIdValues() {
        $arrValues = array(
            $this->intStartX,
            $this->intStartY,
            $this->intEndX,
            $this->intEndY,
        );
        $arrValues = array_merge($arrValues, $this->arrColor);
        return $arrValues;
    }
}