<?php
/*"******************************************************************************************************
*   (c) 2007-2015 by Kajona, www.kajona.de                                                              *
*       Published under the GNU LGPL v2.1, see /system/licence_lgpl.txt                                 *
********************************************************************************************************/


/**
 * This class provides a wrapper to the qrcode-library.
 * It may be used to generate qr-code images
 * 
 * @package module_qrcode
 * @author mr.bashshell (aka STB)
 * 
 */
class class_qrcode {

    private $intSize = 2;
    private $strCorrectionLevel = "Q";
    private $intPadding = 4;



    /**
     * Generates an image based on the string passed.
     *
     * @param $strContent
     * @return string the filename relative to the document root. If you want to use the image in the web, add _webpath_ to the filename.
     */
    public function getImageForString($strContent) {

        $strFilename = "qr".md5($strContent.$this->strCorrectionLevel.$this->intSize.$this->intPadding).".png";

        //caching based on the current filename
        if(is_file(_realpath_._images_cachepath_."/".$strFilename))
            return _images_cachepath_."/".$strFilename;


        require_once __DIR__."/phpqrcode/vendor/autoload.php";

        \PHPQRCode\QRcode::png($strContent, _realpath_._images_cachepath_."/".$strFilename, $this->strCorrectionLevel, $this->intSize, $this->intPadding);

        return _images_cachepath_."/".$strFilename;
    }


    /**
     * Generates a QR Code for a given URL
     *
     * @param string $strQrURL
     * @return string
     * @deprecated use class_qrcode::getImageForString() instead
     */
    public function getQrCode4URL($strQrURL) {
        return $this->getImageForString($strQrURL);
    }


    public function setStrCorrectionLevel($strCorrectionLevel) {
        $this->strCorrectionLevel = $strCorrectionLevel;
    }

    public function getStrCorrectionLevel() {
        return $this->strCorrectionLevel;
    }

    public function setIntPadding($strPadding) {
        $this->intPadding = $strPadding;
    }

    public function getIntPadding() {
        return $this->intPadding;
    }

    public function setIntSize($strSize) {
        $this->intSize = $strSize;
    }

    public function getIntSize() {
        return $this->intSize;
    }

}
