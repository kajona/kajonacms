<?php
class class_image2 {

    const FORMAT_PNG = "png";
    const FORMAT_JPG = "jpg";
    const FORMAT_GIF = "gif";

    private $strCachePath = _images_cachepath_;

    private $objResource;
    private $strOriginalPath;
    private $intWidth;
    private $intHeight;
    private $arrOperations = array();
    private $strCacheId;

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
        $this->strOriginalPath = null;
        $this->intWidth = $intWidth;
        $this->intHeight = $intHeight;
        $this->arrOperations = array();
    }

    public function load($strPath) {
        $bitReturn = false;
        $strPath = removeDirectoryTraversals($strPath);
        if (is_file(_realpath_ . $strPath)) {
            list($intWidth, $intHeight) = getimagesize(_realpath_ . $strPath);
            $this->strOriginalPath = $strPath;
            $this->intWidth = $intWidth;
            $this->intHeight = $intHeight;
            $this->arrOperations = array();
            $bitReturn = true;
        }

        return $bitReturn;
    }

    public function save($strPath, $strFormat = null) {
        $strPath = removeDirectoryTraversals($strPath);

        if ($strFormat == null) {
            $strFormat = self::getFormatFromFilename($strPath);
        }

        if (!$this->isCached($strFormat)) {
            return $this->processImage($strPath, $strFormat);
        }
        else {
            $strCacheFile = $this->getCachePath($strFormat);
            if (!file_exists(_realpath_ . $strPath)
                || filemtime(_realpath_ . $strCacheFile) > filemtime(_realpath_ . $strPath)) {
                return copy(_realpath_ . $strCacheFile, _realpath_ . $strPath);
            }

            return true;
        }
    }

    public function sendToBrowser($strFormat = null) {
        if ($strFormat == null && $this->strOriginalPath != null) {
            $strFormat = self::getFormatFromFilename($this->strOriginalPath);
        }
        
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

        if (!$this->isCached($strFormat)) {
            return $this->processImage(null, $strFormat);
        }
        else {
            $strCacheFile = $this->getCachePath($strFormat);
            $ptrFile = @fopen(_realpath_ . $strCacheFile, 'rb');
            fpassthru($ptrFile);
            return fclose($ptrFile);
        }
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

    private function processImage($strPath, $strFormat) {
        $bitResult = $this->finalLoadOrCreate();

        if (!$bitResult || !$this->applyOperations()) {
            return false;
        }

        $this->saveCache($strFormat);
        return $this->outputImage($strPath, $strFormat);
    }

    private function finalLoadOrCreate() {
        $bitReturn = false;

        // Load existing file
        if ($this->strOriginalPath != null) {
            $strFormat = self::getFormatFromFilename($this->strOriginalPath);
            $strAbsolutePath = _realpath_ . $this->strOriginalPath;

            switch ($strFormat) {
                case self::FORMAT_PNG:
                    $this->objResource = imagecreatefrompng($strAbsolutePath);
                    $bitReturn = true;
                    break;

                case self::FORMAT_JPG:
                    $this->objResource = imagecreatefromjpeg($strAbsolutePath);
                    $bitReturn = true;
                    break;

                case self::FORMAT_GIF:
                    $this->objResource = imagecreatefromgif($strAbsolutePath);
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

        return $bitReturn;
    }

    private function applyOperations() {
        $bitReturn = true;

        foreach ($this->arrOperations as $objOperation) {
            $oldResource = $this->objResource;
            $bitReturn &= $objOperation->render($this->objResource);

            if ($oldResource != $this->objResource) {
                imagedestroy($oldResource);
                imagealphablending($this->objResource, false);
                imagesavealpha($this->objResource, true);
                $this->intWidth = imagesx($this->objResource);
                $this->intHeight = imagesy($this->objResource);
            }
        }

        return true;
    }

    private function outputImage($strPath, $strFormat) {
        if ($strPath != null) {
            $strPath = _realpath_ . $strPath;
        }

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

    private function isCached($strFormat) {
        $this->initCacheId($strFormat);
        $strCachePath = $this->getCachePath($strFormat);

        if (file_exists(_realpath_ . $strCachePath)) {
            //echo "DEBUG: Cache hit!\n";
            return true;
        }

        //echo "DEBUG: Cache miss!\n";
        return false;
    }

    private function saveCache($strFormat) {
        $strCachePath = $this->getCachePath($strFormat);
        //echo "DEBUG: Saving cache file: " . $strCachePath . "\n";
        $this->outputImage($strCachePath, $strFormat);
    }

    private function getCachePath($strFormat) {
        return $this->strCachePath . $this->strCacheId . "." . $strFormat;;
    }

    private function initCacheId($strFormat) {
        $arrayValues = array($this->intWidth, $this->intHeight, $strFormat);

        if ($this->strOriginalPath != null) {
            $arrayValues[] = $this->strOriginalPath;
            $arrayValues[] = filemtime(_realpath_ . $this->strOriginalPath);
        }

        $strCacheId = self::buildCacheId("init", $arrayValues);

        foreach ($this->arrOperations as $objOperation) {
            $strOpCacheName = "_".uniSubstr(get_class($objOperation), 12);
            $strOpCacheValues = $objOperation->getCacheIdValues();
            $strCacheId .= self::buildCacheId($strOpCacheName, $strOpCacheValues);
        }

        echo "DEBUG: Cache Id: " . $strCacheId . "\n";
        $this->strCacheId = md5($strCacheId);
    }

    private static function buildCacheId($strName, $arrValues) {
        $strValues = implode(",", $arrValues);
        return $strName . "(" . $strValues  . ")";
    }

    private static function getFormatFromFilename($strPath) {
        $strExtension = getFileExtension($strPath);

        if ($strExtension == ".jpeg") {
            $strExtension = ".jpg";
        }

        if ($strExtension != "") {
            return strtolower(substr($strExtension, 1));
        }
    }
}