<?php
class class_image2 {

    const FORMAT_PNG = "png";
    const FORMAT_JPG = "jpg";
    const FORMAT_GIF = "gif";

    private $objResource;
    private $originalPath;
    private $intWidth;
    private $intHeight;

    public function __construct() {
    }

    public function __destruct() {
        if ($this->objResource != null) {
            imagedestroy($this->objResource);
        }
    }

    public function create($intWidth, $intHeight) {
        $this->originalPath = null;
        $this->intWidth = $intWidth;
        $this->intHeight = $intHeight;
    }

    public function load($strPath) {
        $bitReturn = false;
        $strPath = removeDirectoryTraversals($strPath);
        if (is_file(_realpath_ . $strPath)) {
            list($intWidth, $intHeight) = getimagesize(_realpath_ . $strPath);
            $this->originalPath = $strPath;
            $this->intWidth = $intWidth;
            $this->intHeight = $intHeight;
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

    public function addOperation(class_image_operation $operation) {
        //TODO
    }

    public function getWidth() {
        return $this->intWidth;
    }

    public function getHeight() {
        return $this->intHeight;
    }

    private function finalLoadOrCreate() {
        if ($this->originalPath != null) {
            $strFormat = self::getFormatFromFilename($this->originalPath);
            switch ($strFormat) {
                case self::FORMAT_PNG:
                    $this->objResource = imagecreatefrompng(_realpath_ . $this->originalPath);
                    return true;

                case self::FORMAT_JPG:
                    $this->objResource = imagecreatefromjpeg(_realpath_ . $this->originalPath);
                    return true;

                case self::FORMAT_GIF:
                    $this->objResource = imagecreatefromgif(_realpath_ . $this->originalPath);
                    return true;

                default:
                    return false;
            }
        }
        else {
            $this->objResource = imagecreatetruecolor($this->intWidth, $this->intHeight);
            imagealphablending($this->objResource, false);
            imagesavealpha($this->objResource, true);
        }
    }

    private function applyOperations() {
        //TODO
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