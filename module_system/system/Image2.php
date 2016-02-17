<?php
/*"******************************************************************************************************
*   (c) 2013-2015 by Kajona, www.kajona.de                                                              *
*       Published under the GNU LGPL v2.1, see /system/licence_lgpl.txt                                 *
*-------------------------------------------------------------------------------------------------------*
*	$Id$	                                            *
********************************************************************************************************/

namespace Kajona\System\System;

use Kajona\System\System\Imageplugins\ImageOperationInterface;


/**
 * Class to manipulate and output images.
 *
 * This class can be used to load or create an image, apply multiple operations, such as scaling and rotation,
 * and save the resulting image. By default the processed image will be cached and no processing will be
 * performed when a cached version is available.
 *
 * Example:
 * $objImage = new Image2();
 * $objImage->load("/files/images/samples/PA252134.JPG");
 *
 * // Scale and crop the image so it is exactly 800 * 600 pixels large.
 * $objImage->addOperation(new class_image_scale_and_crop(800, 600));
 *
 * // Render a text with 80% opacity.
 * $objImage->addOperation(new class_image_text("Kajona", 300, 300, 40, "rgb(0,0,0,0.8)")
 *
 * // Apply the operations and send the image to the browser.
 * if (!$objImage->sendToBrowser()) {
 *     echo "Error processing image.";
 * }
 *
 * Custom operations can be added by implementing ImageOperationInterface. Most operations
 * should inherit from class_image_abstract_operation, which implements ImageOperationInterface
 * and provides common functionality.
 *
 * @package module_system
 */
class Image2 {

    const FORMAT_PNG = "png";
    const FORMAT_JPG = "jpg";
    const FORMAT_GIF = "gif";

    private $strCachePath = _images_cachepath_;
    private $bitUseCache = true;
    private $bitImageIsUpToDate = false;

    private $objResource;
    private $strOriginalPath;
    private $intWidth;
    private $intHeight;
    private $intJpegQuality = 100;
    private $arrOperations = array();
    private $strCacheId;

    /**
     * Default constructor
     */
    public function __construct() {
        // Try to overwrite PHP memory-limit so large images can be processed, too
        if(Carrier::getInstance()->getObjConfig()->getPhpIni("memory_limit") < 128) {
            @ini_set("memory_limit", "128M");
        }
    }

    /**
     * Default destructor
     */
    public function __destruct() {
        if ($this->objResource != null) {
            imagedestroy($this->objResource);
        }
    }

    /**
     * Parses a color string into an RGB or RGBA array.
     *
     * Allowed color strings:
     * * Hexadecimal RGB string: #rrggbb
     * * Hexadecimal RGBA string: #rrggbbaa
     * * Decimal RGB color (color values between 0 and 255): rgb(255, 0, 16)
     * * Decimal RGBA color (as above with alpha between 0.0 and 1.0): rgba(255,0,16,0.9)
     *
     * @param string $strColor Color string.
     * @return array RGB or RGBA values.
     */
    public static function parseColorRgb($strColor) {

        // Hex RGB(A) value, e.g. #FF0000 or #FF000022
        if (preg_match("/#([0-9a-f]{2})([0-9a-f]{2})([0-9a-f]{2})([0-9a-f]{2})?/i", $strColor, $arrMatches)) {
            $intRed = hexdec($arrMatches[1]);
            $intGreen = hexdec($arrMatches[2]);
            $intBlue = hexdec($arrMatches[3]);
            $arrColor = array($intRed, $intGreen, $intBlue);

            if(isset($arrMatches[4])) {
                // alpha is a value between 0 and 127
                $intAlpha = (int)(hexdec($arrMatches[4]) / 2);
                $arrColor[] = $intAlpha;
            }

            return $arrColor;
        }
        // Decimal RGB, e.g. rgb(255, 0, 16)
        elseif (preg_match("/rgb\\(\\s*(\\d{1,3})\\s*,\\s*(\\d{1,3})\\s*,\\s*(\\d{1,3})\\s*\\)/i", $strColor, $arrMatches)) {
            $intRed = min((int)$arrMatches[1], 255);
            $intGreen = min((int)$arrMatches[2], 255);
            $intBlue = min((int)$arrMatches[3], 255);
            $arrColor = array($intRed, $intGreen, $intBlue);
            return $arrColor;
        }
        // Decimal RGBA, e.g. rgba(255, 0, 16, 0.8)
        elseif (preg_match("/rgba\\(\\s*(\\d{1,3})\\s*,\\s*(\\d{1,3})\\s*,\\s*(\\d{1,3})\\s*,\\s*(\\d+(\\.\\d+)?)\\s*\\)/i", $strColor, $arrMatches)) {
            $intRed = min((int)$arrMatches[1], 255);
            $intGreen = min((int)$arrMatches[2], 255);
            $intBlue = min((int)$arrMatches[3], 255);

            // alpha is a value between 0 and 127
            $intAlpha = (int)(min((float)$arrMatches[4], 1.0) * 127.0);

            $arrColor = array($intRed, $intGreen, $intBlue, $intAlpha);
            return $arrColor;
        }

        return false;
    }

    /**
     * Set whether caching is enabled (default) or disabled.
     *
     * @param bool $bitUseCache
     * @return void
     */
    public function setUseCache($bitUseCache) {
        $this->bitUseCache = $bitUseCache;
    }

    /**
     * Set the quality for JPEG pictures.
     *
     * This parameter applies only when saving JPEG images
     * and does not affect all other image processing.
     *
     * @param int $intJpegQuality
     * @return void
     */
    public function setJpegQuality($intJpegQuality) {
        $this->intJpegQuality = $intJpegQuality;
    }

    /**
     * Create a new image with the given width and height.
     *
     * @param int $intWidth
     * @param int $intHeight
     * @return void
     */
    public function create($intWidth, $intHeight) {
        $this->strOriginalPath = null;
        $this->bitImageIsUpToDate = false;
        $this->intWidth = $intWidth;
        $this->intHeight = $intHeight;
        $this->arrOperations = array();
    }

    /**
     * Use an existing image file.
     *
     * Returns false if the file does not exist.
     *
     * @param string $strPath
     * @return bool
     */
    public function load($strPath) {
        $bitReturn = false;
        $strPath = removeDirectoryTraversals($strPath);
        if (is_file(_realpath_ . $strPath)) {
            $this->bitImageIsUpToDate = false;
            $this->strOriginalPath = $strPath;
            $this->arrOperations = array();
            $bitReturn = true;
        }

        return $bitReturn;
    }

    /**
     * Add an image operation.
     *
     * Image operations must implement ImageOperationInterface.
     *
     * @param ImageOperationInterface $objOperation
     * @return void
     */
    public function addOperation(ImageOperationInterface $objOperation) {
        $this->arrOperations[] = $objOperation;
        $this->bitImageIsUpToDate = false;
    }

    /**
     * Save the image to a file.
     *
     * Calling this method will actually start the image processing,
     * if no cached image is available.
     *
     * @param string $strPath
     * @param string $strFormat
     * @return bool
     */
    public function save($strPath, $strFormat = null) {
        $strPath = removeDirectoryTraversals($strPath);

        if ($strFormat == null) {
            $strFormat = self::getFormatFromFilename($strPath);
        }

        if (!$this->isCached($strFormat)) {
            if ($this->processImage($strFormat)) {
                return $this->outputImage($strFormat, $strPath);
            }
            else {
                return false;
            }
        }
        else {
            $strCacheFile = $this->getCachePath($strFormat);
            if (!file_exists(_realpath_ . $strPath) || filemtime(_realpath_ . $strCacheFile) > filemtime(_realpath_ . $strPath)) {
                return copy(_realpath_ . $strCacheFile, _realpath_ . $strPath);
            }

            return true;
        }
    }

    /**
     * Create the image and send it directly to the browser.
     *
     * Calling this method will actually start the image processing,
     * if no cached image is available.
     *
     * @param null $strFormat
     * @return bool
     */
    public function sendToBrowser($strFormat = null) {
        if ($strFormat == null && $this->strOriginalPath != null) {
            $strFormat = self::getFormatFromFilename($this->strOriginalPath);
        }
        
        $strResponseType = null;
        switch($strFormat) {
            case self::FORMAT_PNG:
                $strResponseType = HttpResponsetypes::STR_TYPE_JPEG;
                break;
            case self::FORMAT_JPG:
                $strResponseType = HttpResponsetypes::STR_TYPE_PNG;
                break;
            case self::FORMAT_GIF:
                $strResponseType = HttpResponsetypes::STR_TYPE_GIF;
                break;
            default:
                return false;
        }

        ResponseObject::getInstance()->setStrResponseType($strResponseType);
        ResponseObject::getInstance()->sendHeaders();

        if (!$this->isCached($strFormat)) {
            if ($this->processImage($strFormat)) {
                return $this->outputImage($strFormat);
            }
            else {
                return false;
            }
        }
        else {
            $strCacheFile = $this->getCachePath($strFormat);
            $ptrFile = fopen(_realpath_ . $strCacheFile, 'rb');
            fpassthru($ptrFile);
            return fclose($ptrFile);
        }
    }

    /**
     * Create the image and return the GD image resource.
     *
     * This method is mainly meant to be used internally by image operations
     * working on multiple images.
     *
     * Calling this method will actually start the image processing,
     * if no cached image is available.
     *
     * @return resource
     */
    public function createGdResource() {
        $bitSuccess = false;

        if (!$this->isCached(self::FORMAT_PNG)) {
            $bitSuccess = $this->processImage(self::FORMAT_PNG);
        }
        else {
            $strCacheFile = $this->getCachePath(self::FORMAT_PNG);
            $this->objResource = imagecreatefrompng(_realpath_ . $strCacheFile);
            imagealphablending($this->objResource, false);
            imagesavealpha($this->objResource, true);
        }

        return $this->objResource;
    }

    /**
     * Return the image cache ID.
     *
     * The cache ID is not set until one of the image output method is called.
     *
     * @return mixed
     */
    public function getCacheId() {
        if (!$this->bitImageIsUpToDate) {
            $this->createGdResource();
        }

        return $this->strCacheId;
    }

    /**
     * @param string $strFormat
     *
     * @return bool
     */
    private function processImage($strFormat) {
        if (!$this->bitImageIsUpToDate) {
            $bitSuccess = $this->finalLoadOrCreate();

            if (!$bitSuccess || !$this->applyOperations()) {
                return false;
            }

            $this->saveCache($strFormat);
        }
        
        return true;
    }

    /**
     * @return bool
     */
    private function finalLoadOrCreate() {
        $bitReturn = false;

        if ($this->objResource != null) {
            imagedestroy($this->objResource);
        }

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
            $this->updateImageResource();
        }

        return $bitReturn;
    }

    /**
     * @return bool
     */
    private function applyOperations() {
        $bitReturn = true;

        foreach ($this->arrOperations as $objOperation) {
            $oldResource = $this->objResource;
            $bitReturn &= $objOperation->render($this->objResource);

            if ($oldResource != $this->objResource) {
                imagedestroy($oldResource);
                $this->updateImageResource();
            }
        }

        return $bitReturn;
    }

    /**
     * @param string $strFormat
     * @param null $strPath
     *
     * @return bool
     */
    private function outputImage($strFormat, $strPath = null) {
        if ($strPath != null) {
            $strPath = _realpath_ . $strPath;
        }

        switch ($strFormat) {
            case self::FORMAT_PNG:
                return imagepng($this->objResource, $strPath);

            case self::FORMAT_JPG:
                return imagejpeg($this->objResource, $strPath, $this->intJpegQuality);

            case self::FORMAT_GIF:
                return imagegif($this->objResource, $strPath);

            default:
                return false;
        }
    }

    /**
     * @param string $strFormat
     *
     * @return bool
     */
    private function isCached($strFormat) {
        if (!$this->bitUseCache || $this->bitImageIsUpToDate) {
            return false;
        }

        $this->initCacheId($strFormat);
        $strCachePath = $this->getCachePath($strFormat);

        if (file_exists(_realpath_ . $strCachePath)) {
            //echo "DEBUG: Cache hit!\n";
            return true;
        }

        //echo "DEBUG: Cache miss!\n";
        return false;
    }

    /**
     * @param string $strFormat
     * @return void
     */
    private function saveCache($strFormat) {
        if ($this->bitUseCache) {
            $strCachePath = $this->getCachePath($strFormat);
            //echo "DEBUG: Saving cache file: " . $strCachePath . "\n";
            $this->outputImage($strFormat, $strCachePath);
            $this->bitImageIsUpToDate = true;
        }
    }

    /**
     * @param string $strFormat
     *
     * @return string
     */
    private function getCachePath($strFormat) {
        return $this->strCachePath . "c" . $this->strCacheId . "." . $strFormat;;
    }

    /**
     * @param string $strFormat
     * @return void
     */
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

        //echo "DEBUG: Cache Id: " . $strCacheId . "\n";
        $this->strCacheId = md5($strCacheId);
    }

    /**
     * @return void
     */
    private function updateImageResource() {
        $this->intWidth = imagesx($this->objResource);
        $this->intHeight = imagesy($this->objResource);
        imagealphablending($this->objResource, false);
        imagesavealpha($this->objResource, true);
    }

    /**
     * @param string $strName
     * @param string $arrValues
     *
     * @return string
     */
    private static function buildCacheId($strName, $arrValues) {
        $strValues = implode(",", $arrValues);
        return $strName . "(" . $strValues  . ")";
    }

    /**
     * @param string $strPath
     *
     * @return string
     */
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