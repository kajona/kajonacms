<?php
/*"******************************************************************************************************
*   (c) 2013-2016 by Kajona, www.kajona.de                                                              *
*       Published under the GNU LGPL v2.1, see /system/licence_lgpl.txt                                 *
*-------------------------------------------------------------------------------------------------------*
*	$Id$	                                            *
********************************************************************************************************/

namespace Kajona\System\System\Imageplugins;

use Kajona\System\System\Image2;
use Kajona\System\System\Resourceloader;


/**
 * Implements a text rendering operation.
 */
class ImageText extends ImageAbstractOperation {
    private $strText;
    private $intX;
    private $intY;
    private $floatSize;
    private $arrColor;
    private $strFont;
    private $floatAngle;

    /**
     * @param string $strText
     * @param int $intX
     * @param int $intY
     * @param double $floatSize
     * @param string $strColor
     * @param string $strFont
     * @param float $floatAngle
     */
    public function __construct($strText, $intX, $intY, $floatSize, $strColor = "#000000", $strFont = "dejavusans.ttf", $floatAngle = 0.0) {
        $this->strText = $strText;
        $this->intX = $intX;
        $this->intY = $intY;
        $this->floatSize = $floatSize;
        $this->arrColor = Image2::parseColorRgb($strColor);
        $this->strFont = $strFont;
        $this->floatAngle = $floatAngle;
    }

    /**
     * @param resource &$objResource
     *
     * @return bool
     */
    public function render(&$objResource) {
        $strFontPath = Resourceloader::getInstance()->getPathForFile("/system/fonts/" . $this->strFont);

        //if within a phar, we need to move on to the extract-folder
        if(uniStrpos($strFontPath, ".phar") !== false) {
            $arrMatches = array();
            if(preg_match("#/core(.*)/(([a-zA-Z_]*)\.phar)#i", $strFontPath, $arrMatches)) {
                $strFontPath = _realpath_.Resourceloader::getInstance()->getWebPathForModule($arrMatches[3])."/system/fonts/" . $this->strFont;
            }
        }

        if ($strFontPath !== false && is_file($strFontPath)) {
            $intColor = $this->allocateColor($objResource, $this->arrColor);
            $strText = html_entity_decode($this->strText, ENT_COMPAT, "UTF-8");
            imagealphablending($objResource, true);
            imagefttext($objResource, $this->floatSize, $this->floatAngle,
                $this->intX, $this->intY, $intColor, $strFontPath, $strText);
            imagealphablending($objResource, false);
            return true;
        }

        return false;
    }

    /**
     * @return array
     */
    public function getCacheIdValues() {
        $arrValues = array(
            md5($this->strText),
            $this->intX,
            $this->intY,
            $this->floatSize,
            $this->strFont,
            $this->floatAngle
        );
        $arrValues = array_merge($arrValues, $this->arrColor);
        return $arrValues;
    }
}