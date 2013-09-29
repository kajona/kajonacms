<?php
class class_image2 {

    const FORMAT_PNG = "png";
    const FORMAT_JPG = "jpg";
    const FORMAT_GIF = "gif";

    private $objResource;
    private $originalPath;
    private $intWidth;
    private $intHeight;
    private $arrOperations = array();

    public function __construct() {
    }

    public function __destruct() {
        if ($this->objResource != null) {
            imagedestroy($this->objResource);
        }
    }

    /**
     * Parses a color string into an RGB array.
     *
     * Allowed strings:
     * * Hexadecimal RGB string: #rrggbb
     * * Hexadecimal RGBA string: #rrggbbaa
     *
     * @param $strColor
     * @return array
     */
    public static function parseColorRgb($strColor) {

        if (preg_match("/#([0-9a-f]{2})([0-9a-f]{2})([0-9a-f]{2})([0-9a-f]{2})?/i", $strColor, $arrMatches)) {
            $intRed = hexdec($arrMatches[1]);
            $intGreen = hexdec($arrMatches[2]);
            $intBlue = hexdec($arrMatches[3]);
            $arrColor = array($intRed, $intGreen, $intBlue);

            if (isset($arrMatches[4]))
            {
                // alpha is a value between 0 and 127
                $intAlpha = (int)(hexdec($arrMatches[4]) / 2);
                $arrColor[] = $intAlpha;
            }

            return $arrColor;
        }

        return false;
    }

    public function create($intWidth, $intHeight) {
        $this->originalPath = null;
        $this->intWidth = $intWidth;
        $this->intHeight = $intHeight;
        $this->arrOperations = array();
    }

    public function load($strPath) {
        $bitReturn = false;
        $strPath = removeDirectoryTraversals($strPath);
        if (is_file(_realpath_ . $strPath)) {
            list($intWidth, $intHeight) = getimagesize(_realpath_ . $strPath);
            $this->originalPath = $strPath;
            $this->intWidth = $intWidth;
            $this->intHeight = $intHeight;
            $this->arrOperations = array();
            $bitReturn = true;
        }

        return $bitReturn;
    }

    public function save($strPath, $strFormat = null) {
        $this->finalLoadOrCreate();

        if (!$this->applyOperations()) {
            return false;
        }

        $strPath = removeDirectoryTraversals($strPath);

        if ($strFormat == null) {
            $strFormat = self::getFormatFromFilename($strPath);
        }

        return $this->outputImage(_realpath_ . $strPath, $strFormat);
    }

    public function sendToBrowser($strFormat) {
        $strResponseType = null;
        switch($strFormat) {
            case self::FORMAT_PNG:
                $strResponseType = class_http_responsetypes::STR_TYPE_JPEG;
                break;
            case self::FORMAT_JPG:
                $strResponseType = class_http_responsetypes::STR_TYPE_PNG;
                break;
            case self::FORMAT_GIF:
                $strResponseType = class_http_responsetypes::STR_TYPE_GIF;
                break;
            default:
                return false;
        }

        class_response_object::getInstance()->setStResponseType($strResponseType);
        return $this->outputImage(null, $strFormat);
    }

    public function addOperation(interface_image_operation $objOperation) {
        $this->arrOperations[] = $objOperation;
    }

    public function getWidth() {
        return $this->intWidth;
    }

    public function getHeight() {
        return $this->intHeight;
    }

    private function finalLoadOrCreate() {
        $bitReturn = false;

        // Load existing file
        if ($this->originalPath != null) {
            $strFormat = self::getFormatFromFilename($this->originalPath);
            switch ($strFormat) {
                case self::FORMAT_PNG:
                    $this->objResource = imagecreatefrompng(_realpath_ . $this->originalPath);
                    $bitReturn = true;
                    break;

                case self::FORMAT_JPG:
                    $this->objResource = imagecreatefromjpeg(_realpath_ . $this->originalPath);
                    $bitReturn = true;
                    break;

                case self::FORMAT_GIF:
                    $this->objResource = imagecreatefromgif(_realpath_ . $this->originalPath);
                    $bitReturn = true;
                    break;
            }
        }
        // Create new file in memory
        else {
            $this->objResource = imagecreatetruecolor($this->intWidth, $this->intHeight);
            $bitReturn = true;
        }

        if ($bitReturn) {
            imagealphablending($this->objResource, false);
            imagesavealpha($this->objResource, true);
        }
    }

    private function applyOperations() {
        $bitReturn = true;

        foreach ($this->arrOperations as $objOperation) {
            $oldResource = $this->objResource;
            $bitReturn &= $objOperation->render($this->objResource);

            if ($oldResource != $this->objResource) {
                imagedestroy($oldResource);
                $this->intWidth = imagesx($this->objResource);
                $this->intHeight = imagesy($this->objResource);
            }
        }

        return true;
    }

    private function outputImage($strPath, $strFormat) {
        switch ($strFormat) {
            case self::FORMAT_PNG:
                return imagepng($this->objResource, $strPath);

            case self::FORMAT_JPG:
                return imagejpeg($this->objResource, $strPath);

            case self::FORMAT_GIF:
                return imagegif($this->objResource, $strPath);

            default:
                return false;
        }
    }

    private static function getFormatFromFilename($strPath) {
        $strExtension = getFileExtension($strPath);

        if ($strExtension != "") {
            return strtolower(substr($strExtension, 1));
        }
    }
}