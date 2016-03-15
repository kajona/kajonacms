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
 * Implements an image rotation operation.
 */
class ImageRotate extends ImageAbstractOperation {
    private $floatAngle;
    private $arrColor;

    /**
     * @param double $floatAngle
     * @param string $strColor
     */
    public function __construct($floatAngle, $strColor = "#000000") {
        $this->floatAngle = $floatAngle;
        $this->arrColor = Image2::parseColorRgb($strColor);
    }

    /**
     * @param resource &$objResource
     *
     * @return bool
     */
    public function render(&$objResource) {
        $intColor = $this->allocateColor($objResource, $this->arrColor);

        if ($intColor === null) {
            return false;
        }

        imagealphablending($objResource, true);
        $objResource = imagerotate($objResource, $this->floatAngle, $intColor);
        return true;
    }

    /**
     * @return array
     */
    public function getCacheIdValues() {
        $arrValues = array($this->floatAngle);
        $arrValues = array_merge($arrValues, $this->arrColor);
        return $arrValues;
    }
}