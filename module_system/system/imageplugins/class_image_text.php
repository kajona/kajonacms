<?php
/*"******************************************************************************************************
*   (c) 2013 by Kajona, www.kajona.de                                                              *
*       Published under the GNU LGPL v2.1, see /system/licence_lgpl.txt                                 *
*-------------------------------------------------------------------------------------------------------*
*	$Id:$	                                            *
********************************************************************************************************/

/**
 * Implements a text rendering operation.
 */
class class_image_text extends class_image_abstract_operation {
    private $strText;
    private $intX;
    private $intY;
    private $floatSize;
    private $arrColor;
    private $strFont;
    private $floatAngle;

    public function __construct($strText, $intX, $intY, $floatSize, $strColor = "#000000", $strFont = "dejavusans.ttf", $floatAngle = 0.0) {
        $this->strText = $strText;
        $this->intX = $intX;
        $this->intY = $intY;
        $this->floatSize = $floatSize;
        $this->arrColor = class_image2::parseColorRgb($strColor);
        $this->strFont = $strFont;
        $this->floatAngle = $floatAngle;
    }

    public function render(&$objResource) {
        $strFontPath = class_resourceloader::getInstance()->getPathForFile("/system/fonts/" . $this->strFont);
        if ($strFontPath !== false && is_file(_realpath_.$strFontPath)) {
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