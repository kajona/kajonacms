<?php
/*"******************************************************************************************************
*   (c) 2013-2015 by Kajona, www.kajona.de                                                              *
*       Published under the GNU LGPL v2.1, see /system/licence_lgpl.txt                                 *
*-------------------------------------------------------------------------------------------------------*
*	$Id$	                                            *
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
        $this->arrColor = class_image2::parseColorRgb($strColor);
        $this->strFont = $strFont;
        $this->floatAngle = $floatAngle;
    }

    /**
     * @param resource &$objResource
     *
     * @return bool
     */
    public function render(&$objResource) {
        $strFontPath = class_resourceloader::getInstance()->getPathForFile("/system/fonts/" . $this->strFont);

        //if within a phar, we need to move on to the extract-folder
        if(uniStrpos($strFontPath, ".phar") !== false) {
            $arrMatches = array();
            if(preg_match("#/core(.*)/(([a-zA-Z_]*)\.phar)#i", $strFontPath, $arrMatches)) {
                $strFontPath = _realpath_.class_resourceloader::getInstance()->getWebPathForModule($arrMatches[3])."/system/fonts/" . $this->strFont;
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