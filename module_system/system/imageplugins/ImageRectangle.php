<?php
/*"******************************************************************************************************
*   (c) 2013-2016 by Kajona, www.kajona.de                                                              *
*       Published under the GNU LGPL v2.1, see /system/licence_lgpl.txt                                 *
*-------------------------------------------------------------------------------------------------------*
*	$Id$	                                            *
********************************************************************************************************/

namespace Kajona\System\System\Imageplugins;

use Kajona\System\System\Image2;


/**
 * Implements an operation to draw a rectangle
 */
class ImageRectangle extends ImageAbstractOperation {
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
        $this->arrColor = Image2::parseColorRgb($strColor);
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