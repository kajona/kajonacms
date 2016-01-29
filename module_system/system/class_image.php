<?php
/*"******************************************************************************************************
*   (c) 2004-2006 by MulchProductions, www.mulchprod.de                                                 *
*   (c) 2007-2015 by Kajona, www.kajona.de                                                              *
*       Published under the GNU LGPL v2.1, see /system/licence_lgpl.txt                                 *
*-------------------------------------------------------------------------------------------------------*
*	$Id$                                              *
********************************************************************************************************/

/**
 * Class to modify and edit images using a nice little caching
 * In addition, new image can be created.
 * To do so, set the height and width and create a new image by calling createBlankImage()
 * To have a proper working caching, mind the following:
 *     1.) Resize the image
 *  2.) Afterwards add text to the image or overlay another image
 * The caching itself works in two-steps:
 * 1.) The picture is being loaded by loadImage()
 *         -> The caching works as expected, but at the time of calling loadImage(), the image-object is created,
 *            using up some time (could be MUCH time!!!)
 * 2.) The picture is loaded by preLoadImage()
 *         -> In this case, the image-object is created the time it is being worked on, before the pointer $this->objImage is null.
 *            Before creating the image-object, $this->bitPreload remains false, afterwards it is set to true
 * A few additional words related to caching and the constructor-param:
 * If you only want to resize the image you don't have to care, everything is handled within the class itself.
 * But, as soon as you'll want to perform different or additional operations, e.g. resizing and cutting, overlaying
 * or embedding text, you have to "tell" the class what operations will come.
 * This can be done by using the constructor-param $strCacheAdd.
 * Example:
 * You want to resize an image an place a text at a certain position within the image.
 * The resizing & resize-caching itself is handled by the class, you don't have to care.
 * BUT: if you don't specify additional caching params the class, when it comes to resizing, the class won't
 * know that there'll be additional operations and creates the cachename based on the resize-params, only - what
 * will lead to a wrong result.
 * So make sure to pass the later operations as an additional cache param when creating an instance of class_image:
 * $objImage = new class_image($strInlayText.$intTextXPos.$intTextYPos).
 * This guarantees a valid cachename based on both operations, the resizing and the text-overlay.
 * A hint regarding overlays: Right now, only PNGs are supported for transparent overlays. GIFs are no longer supported.
 *
 * @package module_system
 * @deprecated Use class_image2 instead.
 * @author sidler@mulchprod.de
 */
class class_image {

    /**
     * @var null|resource
     */
    private $objImage = null;

    private $intWidth = 0;
    private $intHeight = 0;

    private $bitPreload;
    private $strCachepath;
    private $strCacheAdd;
    private $strImagename;
    private $strCachename;
    private $strType = ".jpg";
    private $strImagePathOriginal;

    private $bitNeedToSave;

    /**
     * Constructor
     *
     * @param string $strCacheAdd Additional string to add for the caching
     */
    public function __construct($strCacheAdd = "") {


        $this->strCachepath = _images_cachepath_;
        $this->strCacheAdd = $strCacheAdd;
        $this->bitNeedToSave = true;
        $this->bitPreload = false;

        // Try to overwrite PHP memory-limit so large images can be processed, too
        if(class_carrier::getInstance()->getObjConfig()->getPhpIni("memory_limit") < 64) {
            @ini_set("memory_limit", "64M");
        }
    }

    public function __destruct() {
        $this->releaseResources();
    }


    /**
     * Tries to create a new image using the dimensions set before
     *
     * @param string $strType
     *
     * @return bool
     */
    public function createBlankImage($strType = ".jpg") {
        $this->strType = $strType;
        if($this->intHeight != 0 && $this->intWidth != 0) {
            $this->objImage = $this->createEmptyImage($this->intWidth, $this->intHeight);
            return true;
        }

        return false;
    }


    /**
     * Loads all properties from the image, but doesn't create the image-object yet
     * to save runtime. This is being done by finalLoadImage()
     *
     * @param string $strImage
     *
     * @return bool
     */
    public function preLoadImage($strImage) {
        $bitReturn = false;
        $strImage = removeDirectoryTraversals($strImage);
        $this->bitPreload = true;
        if(is_file(_realpath_ . $strImage)) {
            $strType = uniStrtolower(uniSubstr($strImage, uniStrrpos($strImage, ".")));
            $arrInfo = getimagesize(_realpath_ . $strImage);
            $this->intWidth = $arrInfo[0];
            $this->intHeight = $arrInfo[1];
            $this->strImagename = basename($strImage);
            $this->strImagePathOriginal = dirname($strImage);
            $this->strType = $strType;
            $bitReturn = true;
        }

        return $bitReturn;
    }

    /**
     * Creates the image-object, if the image was loaded using preLoadImage()
     *
     * @return bool
     */
    private function finalLoadImage() {
        if($this->bitPreload) {
            switch($this->strType) {
            case ".jpg":
                $this->objImage = imagecreatefromjpeg(_realpath_ . $this->strImagePathOriginal . "/" . $this->strImagename);
                break;
            case ".png":
                $this->objImage = imagecreatefrompng(_realpath_ . $this->strImagePathOriginal . "/" . $this->strImagename);
                imagealphablending($this->objImage, false);
                imagesavealpha($this->objImage, true);
                break;
            case ".gif":
                $this->objImage = imagecreatefromgif(_realpath_ . $this->strImagePathOriginal . "/" . $this->strImagename);
                break;
            }

            class_logger::getInstance()->addLogRow("loaded image " . $this->strImagePathOriginal . "/" . $this->strImagename, class_logger::$levelInfo);
            $bitReturn = true;
        }
        else {
            $bitReturn = false;
        }

        return $bitReturn;
    }

    /**
     * DEPRECATED!!! USE preLoadImage() INSTEAD!!!
     * Loads an image from filesystem
     *
     * @param string $strImage
     *
     * @return bool
     * @deprecated use preLoadImage() instead
     * @see class_image::preLoadImage()
     */
    public function loadImage($strImage) {
        $bitReturn = false;
        $strImage = removeDirectoryTraversals($strImage);

        //file existing?
        if(is_file(_realpath_ . $strImage)) {
            //get file type
            $strType = uniStrtolower(uniSubstr($strImage, uniStrrpos($strImage, ".")));
            switch($strType) {
            case ".jpg":
                $this->objImage = imagecreatefromjpeg(_realpath_ . $strImage);
                break;
            case ".png":
                $this->objImage = imagecreatefrompng(_realpath_ . $strImage);
                imagealphablending($this->objImage, false);
                imagesavealpha($this->objImage, true);
                break;
            case ".gif":
                $this->objImage = imagecreatefromgif(_realpath_ . $strImage);
                break;
            }
            if($this->objImage != null) {
                $arrInfo = getimagesize(_realpath_ . $strImage);
                $this->intWidth = $arrInfo[0];
                $this->intHeight = $arrInfo[1];
                $this->strImagename = basename($strImage);
                $this->strImagePathOriginal = dirname($strImage);
                $this->strType = $strType;
                $bitReturn = true;
            }
        }
        return $bitReturn;
    }

    /**
     * Saves an image to the filesystem
     *
     * @param string $strTarget
     * @param bool $bitCache
     * @param int $intJpegQuality
     *
     * @return bool
     */
    public function saveImage($strTarget = "", $bitCache = false, $intJpegQuality = 90) {
        $bitReturn = false;
        if($this->bitNeedToSave) {
            if(!is_numeric($intJpegQuality) || $intJpegQuality < 0) {
                $intJpegQuality = 90;
            }

            if($strTarget != "") {
                $strTarget = removeDirectoryTraversals($strTarget);
            }

            if($bitCache) {

                $this->generateCachename();
                $strTarget = $this->strCachepath . $this->strCachename;
            }

            //get file type
            if($strTarget != "") {
                $strType = uniSubstr($strTarget, uniStrrpos($strTarget, "."));
            }
            else {
                $strType = $this->strType;
            }

            if($strTarget == "") {
                $strTarget = "/" . $this->strCachepath . $this->generateCachename(0, 0, $intJpegQuality);
            }

            switch(uniStrtolower($strType)) {
            case ".jpg":
                $bitReturn = imagejpeg($this->objImage, _realpath_ . $strTarget, $intJpegQuality);
                break;
            case ".png":
                $bitReturn = imagepng($this->objImage, _realpath_ . $strTarget);
                break;
            case ".gif":
                $bitReturn = imagegif($this->objImage, _realpath_ . $strTarget);
                break;
            }
        }
        else {
            $bitReturn = true;
        }

        if(!$bitReturn) {
            class_logger::getInstance()->addLogRow("error saving file to " . $strTarget, class_logger::$levelWarning);
        }

        return $bitReturn;
    }

    /**
     * Saves the current image to the filesystem and sends it to the browser
     *
     * @param int $intJpegQuality

     */
    public function sendImageToBrowser($intJpegQuality = 90) {
        //Check, if we already got an image
        if($this->objImage == null && $this->bitPreload) {
            if(is_file(_realpath_ . $this->strCachepath . $this->strCachename)) {
                $this->preLoadImage($this->strCachepath . $this->strCachename);
            }
            $this->finalLoadImage();
        }

        if(!is_numeric($intJpegQuality) || $intJpegQuality < 0) {
            $intJpegQuality = 90;
        }

        $this->saveImage("", true, $intJpegQuality);

        //and send it to the browser
        if($this->strCachename != null && $this->strCachename != "") {
            $strType = uniSubstr($this->strCachename, uniStrrpos($this->strCachename, "."));
        }
        else {
            $strType = $this->strType;
        }


        switch($strType) {
        case ".jpg":
            class_response_object::getInstance()->setStrResponseType(class_http_responsetypes::STR_TYPE_JPEG);
            break;
        case ".png":
            class_response_object::getInstance()->setStrResponseType(class_http_responsetypes::STR_TYPE_PNG);
            break;
        case ".gif":
            class_response_object::getInstance()->setStrResponseType(class_http_responsetypes::STR_TYPE_GIF);
            break;
        }


        //send all mandatory headers
        class_response_object::getInstance()->sendHeaders();

        //stream image directly from the filesystem if available
        if(is_file(_realpath_ . $this->strCachepath . $this->strCachename)) {
            $ptrFile = @fopen(_realpath_ . $this->strCachepath . $this->strCachename, 'rb');
            fpassthru($ptrFile);
            @fclose($ptrFile);
        }
        else {
            switch($strType) {
            case ".jpg":
                imagejpeg($this->objImage, null, $intJpegQuality);
                break;
            case ".png":
                imagepng($this->objImage);
                break;
            case ".gif":
                imagegif($this->objImage);
                break;
            }
        }
    }


    /**
     * Resize the image to the given params
     *
     * @param int $intWidth
     * @param int $intHeight
     * @param int $intFactor
     * @param bool $bitCache
     *
     * @return bool
     */
    public function resizeImage($intWidth = 0, $intHeight = 0, $intFactor = 0, $bitCache = false) {

        //param validation
        if(!is_numeric($intWidth) || $intWidth < 0) {
            $intWidth = 0;
        }

        if(!is_numeric($intHeight) || $intHeight < 0) {
            $intHeight = 0;
        }

        if(!is_numeric($intFactor) || $intFactor < 0) {
            $intFactor = 0;
        }


        $floatFaktor = $this->intWidth / $this->intHeight;
        $intWidthNew = $this->intWidth;
        $intHeightNew = $this->intHeight;

        //Fall 1: Breite, keine Hoehe: Verh. beibehalten
        if($intWidth != 0 && $intHeight == 0) {
            //Neue Hoehe festlegen:
            $intWidthNew = $intWidth;
            $intHeightNew = (int)($intWidthNew / $floatFaktor);
        }

        //Fall 2: Hoehe, keine Breite: Verh. beibehalten
        elseif($intWidth == 0 && $intHeight != 0) {
            $intHeightNew = $intHeight;
            $intWidthNew = (int)($intHeightNew * $floatFaktor);
        }

        //Fall 3: Um Faktor skalieren
        elseif($intFactor != 0) {
            $intWidthNew = (int)($this->intWidth * $intFactor);
            $intHeightNew = (int)($this->intHeight * $intFactor);
        }

        //Fall 4: Hoehe und Breite gegeben
        elseif($intWidth != 0 && $intHeight != 0) {
            $intWidthNew = $intWidth;
            $intHeightNew = $intHeight;
        }

        //Wenn Cache aktiviert, dann nun den Dateinamen erzeugen
        if($bitCache) {
            //Aus den ermittelten Daten einen Namen erzeugen
            $this->strCachename = $this->generateCachename($intHeightNew, $intWidthNew);

            if(is_file(_realpath_ . $this->strCachepath . $this->strCachename)) {
                $this->bitNeedToSave = false;
                $this->intWidth = $intWidthNew;
                $this->intHeight = $intHeightNew;
                return true;
            }
        }
        $this->bitNeedToSave = true;

        //Hier bei Bedarf das Bild nachladen
        if($this->objImage == null && $this->bitPreload) {
            $this->finalLoadImage();
        }

        //Abmessungen bestimmt, nun damit ein neues Bild anlegen
        $objImageResized = $this->createEmptyImage($intWidthNew, $intHeightNew);

        //Nun den Inhalt des alten Bildes skaliert in das neue Bild legen
        imagecopyresampled($objImageResized, $this->objImage, 0, 0, 0, 0, $intWidthNew, $intHeightNew, $this->intWidth, $this->intHeight);

        //Dieses Bild als neues verwenden
        $this->intWidth = $intWidthNew;
        $this->intHeight = $intHeightNew;
        imagedestroy($this->objImage);
        $this->objImage = $objImageResized;

        return true;
    }


    /**
     * Crops the current image. Therefore you are able to set the start x/y-coordinates and the
     * desired height and width of the section to keep.
     *
     * @param int $intXStart
     * @param int $intYStart
     * @param int $intWidth
     * @param int $intHeight
     * @param bool $bitCache
     *
     * @return bool
     */
    public function cropImage($intXStart, $intYStart, $intWidth, $intHeight, $bitCache = false) {

        //param-validation
        if(!is_numeric($intXStart) || $intXStart < 0) {
            $intXStart = 0;
        }
        if(!is_numeric($intYStart) || $intYStart < 0) {
            $intYStart = 0;
        }
        if(!is_numeric($intWidth) || $intWidth < 0) {
            $intWidth = 0;
        }
        if(!is_numeric($intHeight) || $intHeight < 0) {
            $intHeight = 0;
        }


        //Wenn Cache aktiviert, dann nun den Dateinamen erzeugen
        if($bitCache) {
            //Aus den ermittelten Daten einen Namen erzeugen
            $this->strCachename = $this->generateCachename();

            if(is_file(_realpath_ . $this->strCachepath . $this->strCachename)) {
                $this->bitNeedToSave = false;
                return true;
            }
        }
        $this->bitNeedToSave = true;

        //load the original image
        //Hier bei Bedarf das Bild nachladen
        if($this->objImage == null && $this->bitPreload) {
            $this->finalLoadImage();
        }

        //create a new image using the desired size
        $objImageCropped = $this->createEmptyImage($intWidth, $intHeight);

        //copy the selected region
        imagecopy($objImageCropped, $this->objImage, 0, 0, $intXStart, $intYStart, $intWidth, $intHeight);
        $this->intWidth = $intWidth;
        $this->intHeight = $intHeight;
        imagedestroy($this->objImage);
        $this->objImage = $objImageCropped;

        return true;
    }

    /**
     * Rotates an image. Note: Only a degree of 90, 180 or 270 is allowed!
     *
     * @param int $intAngle
     *
     * @return bool
     */
    public function rotateImage($intAngle) {
        $bitReturn = false;

        if(!is_numeric($intAngle)) {
            return false;
        }

        //load the original image
        if($this->objImage == null && $this->bitPreload) {
            $this->finalLoadImage();
        }

        //workaround: if the sum of width & height %2 is not 0, one has to be cut by one pixel
        $bitImageResized = false;
        if(($this->intWidth + $this->intHeight) % 2 != 0 && $intAngle != 180) {

            //create a new image and copy the new one into
            $bitImageResized = true;

            $objTempImage = $this->createEmptyImage($this->intWidth + 1, $this->intHeight);

            imagecopy($objTempImage, $this->objImage, 0, 0, 0, 0, $this->intWidth, $this->intHeight);
            imagedestroy($this->objImage);
            $this->objImage = $objTempImage;
            $this->intWidth++;
            //$this->resizeImage($this->intWidth+1, $this->intHeight);
        }


        //different cases. 180 is easy ;)
        if($intAngle == 180) {
            $this->objImage = imagerotate($this->objImage, 180, 0);
            $bitReturn = true;
        }
        elseif($intAngle == 90) {

            //Set up the temp image
            $intSquareSize = $this->intWidth > $this->intHeight ? $this->intWidth : $this->intHeight;
            $objSquareImage = $this->createEmptyImage($intSquareSize, $intSquareSize);
            //copy the existing image into the new image
            if($this->intWidth > $this->intHeight) {
                imagecopy($objSquareImage, $this->objImage, 0, ceil(($this->intWidth - $this->intHeight) / 2), 0, 0, $this->intWidth, $this->intHeight);
            }
            else {
                imagecopy($objSquareImage, $this->objImage, ceil(($this->intHeight - $this->intWidth) / 2), 0, 0, 0, $this->intWidth, $this->intHeight);
            }

            //rotate the image
            $objSquareImage = imagerotate($objSquareImage, 90, 0, -1);
            //crop to the final sizes
            imagedestroy($this->objImage);

            if($this->intWidth > $this->intHeight) {
                $this->objImage = $this->createEmptyImage($this->intHeight, $this->intWidth);
                imagecopy($this->objImage, $objSquareImage, 0, 0, ceil(($this->intWidth - $this->intHeight) / 2), 0, $this->intHeight, $this->intWidth);
            }
            else {
                $this->objImage = $this->createEmptyImage($this->intHeight, $this->intWidth);
                imagecopy($this->objImage, $objSquareImage, 0, 0, 0, ceil(($this->intHeight - $this->intWidth) / 2), $this->intHeight, $this->intWidth);
            }


            $intWidthTemp = $this->intWidth;
            $this->intWidth = $this->intHeight;
            $this->intHeight = $intWidthTemp;
            $bitReturn = true;
        }
        elseif($intAngle == 270) {

            //Set up the temp image
            $intSquareSize = $this->intWidth > $this->intHeight ? $this->intWidth : $this->intHeight;
            $objSquareImage = $this->createEmptyImage($intSquareSize, $intSquareSize);
            //copy the existing image into the new image
            if($this->intWidth > $this->intHeight) {
                imagecopy($objSquareImage, $this->objImage, 0, ceil(($this->intWidth - $this->intHeight) / 2), 0, 0, $this->intWidth, $this->intHeight);
            }
            else {
                imagecopy($objSquareImage, $this->objImage, ceil(($this->intHeight - $this->intWidth) / 2), 0, 0, 0, $this->intWidth, $this->intHeight);
            }

            //rotate the image
            $objSquareImage = imagerotate($objSquareImage, 270, 0, -1);
            //crop to the final sizes
            imagedestroy($this->objImage);

            if($this->intWidth > $this->intHeight) {
                $this->objImage = $this->createEmptyImage($this->intHeight, $this->intWidth);
                imagecopy($this->objImage, $objSquareImage, 0, 0, ceil(($this->intWidth - $this->intHeight) / 2), 0, $this->intHeight, $this->intWidth);
            }
            else {
                $this->objImage = $this->createEmptyImage($this->intHeight, $this->intWidth);
                imagecopy($this->objImage, $objSquareImage, 0, 0, 0, ceil(($this->intHeight - $this->intWidth) / 2), $this->intHeight, $this->intWidth);
            }

            $intWidthTemp = $this->intWidth;
            $this->intWidth = $this->intHeight;
            $this->intHeight = $intWidthTemp;
            $bitReturn = true;
        }

        //recrop to old dimensions?
        if($bitImageResized) {
            if($intAngle == 90) {
                $this->cropImage(0, 1, $this->intWidth, $this->intHeight - 1);
            }
            elseif($intAngle == 270) {
                $this->cropImage(0, 0, $this->intWidth, $this->intHeight - 1);
            }
        }

        return $bitReturn;
    }


    /**
     * Writes text into the image
     *
     * @param string $strText
     * @param int $intX
     * @param int $intY
     * @param int|string $intSize
     * @param string $strColor
     * @param string $strFont
     * @param bool $bitCache
     * @param int $intAngle
     *
     * @return bool
     */
    public function imageText($strText, $intX, $intY, $intSize = "20", $strColor = "255,255,255", $strFont = "dejavusans.ttf", $bitCache = false, $intAngle = 0) {
        $bitReturn = false;

        //Cache?
        if($bitCache) {
            //create cache name
            $this->strCachename = $this->generateCachename();
            if(is_file(_realpath_ . $this->strCachepath . $this->strCachename)) {
                $this->bitNeedToSave = false;
                return true;
            }
        }
        $this->bitNeedToSave = true;

        //load image
        if($this->objImage == null && $this->bitPreload) {
            $this->finalLoadImage();
        }

        $intColor = 0;
        //set color
        if(is_int($strColor)) {
            $intColor = $strColor;
        }
        else {
            $arrayColors = explode(",", $strColor);
            $intColor = imagecolorallocate($this->objImage, $arrayColors[0], $arrayColors[1], $arrayColors[2]);
        }

        //load font
        $strPath = class_resourceloader::getInstance()->getPathForFile("/system/fonts/" . $strFont);
        if($strPath !== false && is_file(_realpath_.$strPath) && function_exists("imagefttext")) {
            $strText = html_entity_decode($strText, ENT_COMPAT, "UTF-8");
            imagealphablending($this->objImage, true);
            @imagefttext($this->objImage, $intSize, $intAngle, $intX, $intY, $intColor, _realpath_ . $strPath, $strText);
            imagealphablending($this->objImage, false);
        }

        return $bitReturn;
    }

    /**
     * Places an image on top of another image
     * In case you want to overlay transparent images, use PNGs only
     *
     * @param string $strImage The image to place into the loaded image
     * @param int $intX
     * @param int $intY
     * @param bool $bitCache
     *
     * @return bool
     */
    public function overlayImage($strImage, $intX = 0, $intY = 0, $bitCache = false) {
        $bitReturn = false;
        //register for using the caching
        $this->strCacheAdd .= $strImage;

        //Cache?
        if($bitCache) {
            //create cachename
            $this->strCachename = $this->generateCachename();
            if(is_file(_realpath_ . $this->strCachepath . $this->strCachename)) {
                $this->bitNeedToSave = false;
                return true;
            }
        }
        $this->bitNeedToSave = true;

        //load image
        if($this->objImage == null && $this->bitPreload) {
            $this->finalLoadImage();
        }

        //load the other image into the system using another instance
        $objOverlayImage = new class_image();
        if($objOverlayImage->loadImage($strImage)) {

            imagealphablending($this->objImage, true);
            //merge pics
            $objOverlayResource = $objOverlayImage->getImageResource();
            imagealphablending($objOverlayResource, true);
            $bitReturn = imagecopy($this->objImage, $objOverlayResource, $intX, $intY, 0, 0, $objOverlayImage->getIntWidth(), $objOverlayImage->getIntHeight());
            //$bitReturn = imagecopymerge($this->objImage, $objOverlayResource, $intX, $intY, 0, 0, $objOverlayImage->getIntWidth(), $objOverlayImage->getIntHeight(), 100);
        }

        return $bitReturn;
    }

    /**
     * Wrapper function to resizeImage() and cropImage(). Ensures a proper working of the internal caching.
     * Please note that cropping is done from within the images center, so there's not that flexibility
     * as provided by cropImage() being used directly.
     * This method is called from image.php and should not be used in other contexts.
     *
     * @param int $intMaxSizeWidth
     * @param int $intMaxSizeHeight
     * @param int $intCropWidth
     * @param int $intCropHeight
     */
    public function resizeAndCropImage($intMaxSizeWidth, $intMaxSizeHeight, $intCropWidth, $intCropHeight) {

        //Load the image-dimensions
        $intWidthNew = 0;
        $intHeightNew = 0;

        $bitResize = false;
        $bitCropToFixedSize = false;

        //check, if cropping is needed
        if($intCropWidth > 0 && $intCropHeight > 0 && ($this->intWidth != $intCropWidth || $this->intHeight != $intCropHeight)) {
            $bitResize = true;

            //TODO: Also it would be nice to enable the use of only one "fixed"-param.
            $floatRelation = $this->intWidth / $this->intHeight; //0 = width, 1 = height
            $floatNewRelation = $intCropWidth / $intCropHeight;

            if($floatRelation > $floatNewRelation) {
                //original image is wider
                $bitCropToFixedSize = true;
                $intHeightNew = $intCropHeight;
                $intWidthNew = (int)($intCropHeight * $floatRelation);
            }
            else if($floatRelation == $floatNewRelation) {
                //original image has same relation, no cropping needed
                $intWidthNew = $intCropWidth;
                $intHeightNew = $intCropHeight;
            }
            else {
                //original image is taller
                $bitCropToFixedSize = true;
                $intWidthNew = $intCropWidth;
                $intHeightNew = (int)($intCropWidth / $floatRelation);
            }
        }
        //check, if resizing is needed
        else if(($intMaxSizeWidth > 0 || $intMaxSizeHeight > 0) && ($this->intWidth > $intMaxSizeWidth || $this->intHeight > $intMaxSizeHeight)) {
            $bitResize = true;
            $floatRelation = $this->intWidth / $this->intHeight; //0 = width, 1 = height

            //choose more restrictive values
            $intHeightNew = $intMaxSizeHeight;
            $intWidthNew = $intMaxSizeHeight * $floatRelation;

            if($intMaxSizeHeight == 0) {
                if($intMaxSizeWidth < $this->intWidth) {
                    $intWidthNew = $intMaxSizeWidth;
                    $intHeightNew = $intWidthNew / $floatRelation;
                }
                else {
                    $bitResize = false;
                }
            }
            elseif($intMaxSizeWidth == 0) {
                if($intMaxSizeHeight < $this->intHeight) {
                    $intHeightNew = $intMaxSizeHeight;
                    $intWidthNew = $intHeightNew * $floatRelation;
                }
                else {
                    $bitResize = false;
                }
            }
            elseif($intHeightNew && $intHeightNew > $intMaxSizeHeight || $intWidthNew > $intMaxSizeWidth) {
                $intHeightNew = $intMaxSizeWidth / $floatRelation;
                $intWidthNew = $intMaxSizeWidth;
            }
            //round to integers
            $intHeightNew = (int)$intHeightNew;
            $intWidthNew = (int)$intWidthNew;
            //avoid 0-sizes
            if($intHeightNew < 1) {
                $intHeightNew = 1;
            }
            if($intWidthNew < 1) {
                $intWidthNew = 1;
            }
        }
        else {
            $this->setBitNeedToSave(false);
        }

        class_logger::getInstance()->addLogRow(
            "resize to(" . $bitResize . "): width: " . $intWidthNew . " height: " . $intHeightNew . " crop to(" . $bitCropToFixedSize . "): width: " . $intCropWidth . " height: " . $intCropHeight . " ", class_logger::$levelInfo
        );


        //set up the cache name for later operations
        $this->strCacheAdd .= $intCropWidth . $intCropHeight;

        //look up the image in the cache
        if($bitCropToFixedSize) {
            $this->strCachename = $this->generateCachename($intCropHeight, $intCropWidth);
            if(is_file(_realpath_ . $this->strCachepath . $this->strCachename)) {
                $this->bitNeedToSave = false;
                $this->intWidth = $intCropWidth;
                $this->intHeight = $intCropHeight;
                return true;
            }

        }
        else {
            $this->strCachename = $this->generateCachename($intHeightNew, $intWidthNew);
            if(is_file(_realpath_ . $this->strCachepath . $this->strCachename)) {
                $this->bitNeedToSave = false;
                $this->intWidth = $intWidthNew;
                $this->intHeight = $intHeightNew;
                return true;
            }
        }


        if($bitResize) {
            $this->resizeImage($intWidthNew, $intHeightNew, 0, true);
        }

        if($bitCropToFixedSize) {
            //positioning the image
            $intXStart = (int)(($intWidthNew - $intCropWidth) / 2);
            $intYStart = (int)(($intHeightNew - $intCropHeight) / 2);

            $this->cropImage($intXStart, $intYStart, $intCropWidth, $intCropHeight, true);
        }

    }


    /**
     * Tries to add a color to the current image object
     *
     * @param int $intRed
     * @param int $intGreen
     * @param int $intBlue
     *
     * @return int the id of the color created, false in case of errors
     */
    public function registerColor($intRed, $intGreen, $intBlue) {
        $intColorId = false;
        if($this->objImage != null) {
            $intColorId = imagecolorallocate($this->objImage, $intRed, $intGreen, $intBlue);
        }

        return $intColorId;
    }


    /**
     * Tries to set a color transparent to the current image object
     *
     * @param int int the id of the color
     *
     * @return boolean true or false in case of errors
     */
    public function setColorTransparent($intColorId = 0) {
        $bitReturn = false;
        if($this->objImage != null && $intColorId > 0 && $this->strType != ".jpg") {
            imagecolortransparent($this->objImage, $intColorId);
            $bitReturn = true;
        }

        return $bitReturn;
    }


    /**
     * Draws a rectangle into the current image, filled with the passed color
     *
     * @param int $intStartX left top
     * @param int $intStartY left top
     * @param int $intWidth
     * @param int $intHeight
     * @param int $intColor create by registerColor()
     *
     * @return bool
     */
    public function drawFilledRectangle($intStartX, $intStartY, $intWidth, $intHeight, $intColor) {
        if($this->objImage != null) {
            imagefilledrectangle($this->objImage, $intStartX, $intStartY, ($intStartX + $intWidth), ($intStartY + $intHeight), $intColor);
            return true;
        }
        return false;
    }

    /**
     * Draws a rectangle into the current image, color defines the bordercolor
     *
     * @param int $intStartX left top
     * @param int $intStartY left top
     * @param int $intWidth
     * @param int $intHeight
     * @param int $intColor create by registerColor()
     *
     * @return bool
     */
    public function drawRectangle($intStartX, $intStartY, $intWidth, $intHeight, $intColor) {
        if($this->objImage != null) {
            imagerectangle($this->objImage, $intStartX, $intStartY, ($intStartX + $intWidth), ($intStartY + $intHeight), $intColor);
            return true;
        }
        return false;
    }

    /**
     * Draws a line into the current image
     *
     * @param int $intStartX left top
     * @param int $intStartY left top
     * @param int $intEndX
     * @param int $intEndY
     * @param int $intColor create by registerColor()
     *
     * @return bool
     */
    public function drawLine($intStartX, $intStartY, $intEndX, $intEndY, $intColor) {
        if($this->objImage != null) {
            imageline($this->objImage, $intStartX, $intStartY, $intEndX, $intEndY, $intColor);
            return true;
        }
        return false;
    }


    /**
     * Calcs the size of the rendered text.
     *
     * @param int $intFontSize
     * @param int $floatAngle
     * @param string $strFont
     * @param string $strText
     *
     * @return array or false in case of errors
     */
    public function getBoundingTextbox($intFontSize, $floatAngle, $strFont, $strText) {
        if(is_file(class_resourceloader::getInstance()->getAbsolutePathForModule("module_system") . "/system/fonts/" . $strFont)) {
            return @ImageTTFBBox($intFontSize, $floatAngle, class_resourceloader::getInstance()->getAbsolutePathForModule("module_system") . "/system/fonts/" . $strFont, $strText);
        }
        return false;
    }


    /**
     * Draws a circle around the given point
     *
     * @param int $intCX
     * @param int $intCY
     * @param int $intWidth
     * @param int $intHeight
     * @param int $intStart
     * @param int $intEnd
     * @param int $intColor
     *
     * @return boolean
     */
    public function drawArc($intCX, $intCY, $intWidth, $intHeight, $intStart, $intEnd, $intColor) {
        if($this->objImage != null) {
            imagearc($this->objImage, $intCX, $intCY, $intWidth, $intHeight, $intStart, $intEnd, $intColor);
            return true;
        }
        return false;
    }


    /**
     * Creates a blank image with a transparent background
     *
     * @param int $intHeight
     * @param int $intWidth
     *
     * @return object
     */
    private function createEmptyImage($intWidth, $intHeight) {
        $objImage = @imagecreatetruecolor($intWidth, $intHeight);

        imagealphablending($objImage, false); //crashes font-rendering, so set true before rendering fonts
        imagesavealpha($objImage, true);

        return $objImage;
    }

    /**
     * Generates a md5 to make the cached image unique
     *
     * @param int $intHeight
     * @param int $intWidth
     *
     * @return string
     */
    private function generateCachename($intHeight = 0, $intWidth = 0) {
        if($intWidth == 0) {
            $intWidth = $this->intWidth;
        }
        if($intHeight == 0) {
            $intHeight = $this->intHeight;
        }

        $intFilesize = @filesize(_realpath_ . $this->strImagePathOriginal . "/" . $this->strImagename);

        $strReturn = md5($this->strImagePathOriginal . $this->strImagename . $intHeight . $intWidth . $this->strCacheAdd . $intFilesize) . $this->strType;

        $this->strCachename = $strReturn;

        return $strReturn;
    }

    /**
     * Returns the generated cachename
     *
     * @return string
     */
    public function getCachename() {
        return $this->strCachename;
    }

    /**
     * Releases all objects of images to reduce memory-consumption.
     * By calling this method, all images are set back, saving and modifying a former image will fail!

     */
    public function releaseResources() {
        @imagedestroy($this->objImage);
        $this->objImage = null;
    }

    /**
     * Sets the height-param
     *
     * @param int $intHeight
     */
    public function setIntHeight($intHeight) {
        $this->intHeight = $intHeight;
    }

    /**
     * Returns the height of the current image
     *
     * @return int
     */
    public function getIntHeight() {
        return $this->intHeight;
    }

    /**
     * Sets the width-param
     *
     * @param int $intWidth
     */
    public function setIntWidth($intWidth) {
        $this->intWidth = $intWidth;
    }

    /**
     * Returns the width of the current image
     *
     * @return int
     */
    public function getIntWidth() {
        return $this->intWidth;
    }

    /**
     * Sets the bit needToSave
     *
     * @param bool $bitNeedToSave
     */
    public function setBitNeedToSave($bitNeedToSave) {
        $this->bitNeedToSave = $bitNeedToSave;
    }

    /**
     * Returns the current image-resource-object.
     * Why the hell do you need it? Think twice!
     *
     * @return resource
     */
    public function getImageResource() {
        return $this->objImage;
    }

}

