<?php
/*"******************************************************************************************************
*   (c) 2004-2006 by MulchProductions, www.mulchprod.de                                                 *
*   (c) 2007-2015 by Kajona, www.kajona.de                                                              *
*       Published under the GNU LGPL v2.1, see /system/licence_lgpl.txt                                 *
********************************************************************************************************/
namespace Kajona\System;

use Kajona\Mediamanager\System\MediamanagerFile;
use Kajona\Pages\System\PagesPageelement;
use Kajona\System\System\Carrier;
use Kajona\System\System\CoreEventdispatcher;
use Kajona\System\System\HttpStatuscodes;
use Kajona\System\System\Image2;
use Kajona\System\System\Imageplugins\ImageLine;
use Kajona\System\System\Imageplugins\ImageOverlay;
use Kajona\System\System\Imageplugins\ImageRectangle;
use Kajona\System\System\Imageplugins\ImageScale;
use Kajona\System\System\Imageplugins\ImageScaleAndCrop;
use Kajona\System\System\Imageplugins\ImageText;
use Kajona\System\System\RequestEntrypointEnum;
use Kajona\System\System\ResponseObject;
use Kajona\System\System\Session;
use Kajona\System\System\SystemEventidentifier;
use Kajona\System\System\SystemModule;

/**
 * This class can be used to create fast "on-the-fly" resizes of images
 * or to create a captcha image
 * To resize an image, you can call this class like
 * _webpath_/image.php?image=path/to/image.jpg&maxWidth=200&maxHeight=200
 * To resize and crop an image to a fixed size, you can call this class like
 * _webpath_/image.php?image=path/to/image.jpg&fixedWidth=200&fixedHeight=200
 * To create a captcha image, you can call this class like
 * _webpath_/image.php?image=kajonaCaptcha
 * If used with jpeg-pictures, the param quality=[1-95] can be used, default value is 90.
 * The images are directly sent to the browser, so include the calling in img-tags.
 * ATTENTION:
 * Make sure to use urlencoded image-paths!
 * The params maxWidth, maxHeight and quality are optional. If fixedWidth and fixedHeight
 * are set, maxWidth and maxHeight won't be used.
 *
 */
class Flyimage
{

    private $strFilename;
    private $intMaxWidth = 0;
    private $intMaxHeight = 0;
    private $intFixedWidth = 0;
    private $intFixedHeight = 0;

    private $intQuality;

    private $strElementId;
    private $strSystemid;

    /**
     * constructor, init the parent-class
     */
    public function __construct()
    {
        //find the params to use
        $this->strFilename = urldecode(getGet("image"));
        //avoid directory traversing
        $this->strFilename = str_replace("../", "", $this->strFilename);

        $this->intMaxHeight = (int)getGet("maxHeight");
        if ($this->intMaxHeight < 0) {
            $this->intMaxHeight = 0;
        }

        $this->intMaxWidth = (int)getGet("maxWidth");
        if ($this->intMaxWidth < 0) {
            $this->intMaxWidth = 0;
        }

        $this->intFixedHeight = (int)getGet("fixedHeight");
        if ($this->intFixedHeight < 0 || $this->intFixedHeight > 2000) {
            $this->intFixedHeight = 0;
        }

        $this->intFixedWidth = (int)getGet("fixedWidth");
        if ($this->intFixedWidth < 0 || $this->intFixedWidth > 2000) {
            $this->intFixedWidth = 0;
        }

        $this->intQuality = (int)getGet("quality");
        if ($this->intQuality <= 0 || $this->intQuality > 100) {
            $this->intQuality = 90;
        }


        $this->strSystemid = getGet("systemid");
        $this->strElementId = getGet("elementid");

    }

    /**
     * Here happens the magic: creating the image and sending it to the browser
     *
     * @return void
     */
    public function generateImage()
    {

        //switch the different modes - may be want to generate a detailed image-view
        if (validateSystemid($this->strSystemid) && validateSystemid($this->strElementId)) {
            Carrier::getInstance()->getObjConfig()->loadConfigsDatabase(Carrier::getInstance()->getObjDB());
            $this->generateMediamanagerImage();
        }
        else {
            Carrier::getInstance()->getObjSession()->sessionClose();
            $this->resizeImage();
        }
    }

    /**
     * Wrapper to load a single element and generate the image
     *
     * @return void
     */
    private function generateMediamanagerImage()
    {
        if (SystemModule::getModuleByName("mediamanager") !== null) {

            $objElement = new PagesPageelement($this->strElementId);
            $objPortalElement = $objElement->getConcretePortalInstance();

            $objFile = new MediamanagerFile($this->strSystemid);

            if (!$objFile->rightView()) {
                ResponseObject::getInstance()->setStrStatusCode(HttpStatuscodes::SC_FORBIDDEN);
                ResponseObject::getInstance()->sendHeaders();
                return;
            }

            $arrElementData = $objPortalElement->getElementContent($objElement->getSystemid());

            Session::getInstance()->sessionClose();
            if (is_file(_realpath_.$objFile->getStrFilename())) {
                $objImage = new Image2();
                $objImage->load($objFile->getStrFilename());
                $objImage->addOperation(new ImageScale($arrElementData["gallery_maxw_d"], $arrElementData["gallery_maxh_d"]));
                $objImage->addOperation(new ImageText($arrElementData["gallery_text"], $arrElementData["gallery_text_x"], $arrElementData["gallery_text_y"], 10, "#ffffff"));

                if (is_file(_realpath_.$arrElementData["gallery_overlay"])) {
                    $objImageOverlay = new Image2();
                    $objImageOverlay->load($arrElementData["gallery_overlay"]);
                    $objImage->addOperation(new ImageOverlay($arrElementData["gallery_overlay"], $arrElementData["gallery_text_x"], $arrElementData["gallery_text_y"]));
                }
                $objImage->setJpegQuality((int)$this->intQuality);
                $objImage->sendToBrowser();
                return;
            }

        }

        ResponseObject::getInstance()->setStrStatusCode(HttpStatuscodes::SC_NOT_FOUND);
        ResponseObject::getInstance()->sendHeaders();
    }

    /**
     * Wrapper to the real, fast resizing
     *
     * @return void
     */
    private function resizeImage()
    {
        //Load the image-dimensions
        if (is_file(_realpath_.$this->strFilename) && (uniStrpos($this->strFilename, "/files") !== false || uniStrpos($this->strFilename, "/templates") !== false)) {

            //check headers, maybe execution could be terminated right here
            if (checkConditionalGetHeaders(md5(md5_file(_realpath_.$this->strFilename).$this->intMaxWidth.$this->intMaxHeight.$this->intFixedWidth.$this->intFixedHeight))) {
                ResponseObject::getInstance()->sendHeaders();
                return;
            }

            $objImage = new Image2();
            $objImage->load($this->strFilename);
            $objImage->addOperation(new ImageScaleAndCrop($this->intFixedWidth, $this->intFixedHeight));
            $objImage->addOperation(new ImageScale($this->intMaxWidth, $this->intMaxHeight));

            //send the headers for conditional gets
            setConditionalGetHeaders(md5(md5_file(_realpath_.$this->strFilename).$this->intMaxWidth.$this->intMaxHeight.$this->intFixedWidth.$this->intFixedHeight));

            //TODO: add expires header for browser caching (optional)
            /*
            $intCacheSeconds = 604800; //default: 1 week (60*60*24*7)
            header("Expires: ".gmdate("D, d M Y H:i:s", time() + $intCacheSeconds)." GMT", true);
            header("Cache-Control: public, max-age=".$intCacheSeconds, true);
            header("Pragma: ", true);
            */

            //and send it to the browser
            $objImage->setJpegQuality((int)$this->intQuality);
            $objImage->sendToBrowser();
            return;
        }


        ResponseObject::getInstance()->setStrStatusCode(HttpStatuscodes::SC_NOT_FOUND);
        ResponseObject::getInstance()->sendHeaders();
    }


    /**
     * Generates a captcha image to defend bots.
     * To generate a captcha image, use "kajonaCaptcha" as image-param
     * when calling image.php
     * Up to now, the size-params are ignored during the creation of a
     * captcha image
     *
     * @return void
     */
    public function generateCaptchaImage()
    {
        if ($this->intMaxWidth == 0 || $this->intMaxWidth > 500) {
            $intWidth = 200;
        }
        else {
            $intWidth = $this->intMaxWidth;
        }

        if ($this->intMaxHeight == 0 || $this->intMaxHeight > 500) {
            $intHeight = 50;
        }
        else {
            $intHeight = $this->intMaxHeight;
        }

        $intMinfontSize = 15;
        $intMaxFontSize = 22;
        $intWidthPerChar = 30;
        $strCharsPossible = "abcdefghijklmnpqrstuvwxyz123456789";
        $intHorizontalOffset = 10;
        $intVerticalOffset = 10;
        $intForegroundOffset = 2;

        $strCharactersPlaced = "";

        //init the random-function
        srand((double)microtime() * 1000000);

        //v2 version
        $objImage2 = new Image2();
        $objImage2->create($intWidth, $intHeight);
        $objImage2->addOperation(new ImageRectangle(0, 0, $intWidth, $intHeight, "#FFFFFF"));

        //draw vertical lines
        $intStart = 5;
        while ($intStart < $intWidth - 5) {
            $objImage2->addOperation(new ImageLine($intStart, 0, $intStart, $intWidth, $this->generateGreyLikeColor()));
            $intStart += rand(10, 17);
        }
        //draw horizontal lines
        $intStart = 5;
        while ($intStart < $intHeight - 5) {
            $objImage2->addOperation(new ImageLine(0, $intStart, $intWidth, $intStart, $this->generateGreyLikeColor()));
            $intStart += rand(10, 17);
        }

        //draw floating horizontal lines
        for ($intI = 0; $intI <= 3; $intI++) {
            $intXPrev = 0;
            $intYPrev = rand(0, $intHeight);
            while ($intXPrev <= $intWidth) {
                $intNewX = rand($intXPrev, $intXPrev + 50);
                $intNewY = rand(0, $intHeight);
                $objImage2->addOperation(new ImageLine($intXPrev, $intYPrev, $intNewX, $intNewY, $this->generateGreyLikeColor()));
                $intXPrev = $intNewX;
                $intYPrev = $intNewY;
            }
        }

        //calculate number of characters on the image
        $intNumberOfChars = floor($intWidth / $intWidthPerChar);

        //place characters in the image
        for ($intI = 0; $intI < $intNumberOfChars; $intI++) {
            //character to place
            $strCurrentChar = $strCharsPossible[rand(0, (uniStrlen($strCharsPossible) - 1))];
            $strCharactersPlaced .= $strCurrentChar;
            //color to use
            $intCol1 = rand(0, 200);
            $intCol2 = rand(0, 200);
            $intCol3 = rand(0, 200);
            //fontsize
            $intSize = rand($intMinfontSize, $intMaxFontSize);
            //calculate x and y pos
            $intX = $intHorizontalOffset + ($intI * $intWidthPerChar);
            $intY = $intHeight - rand($intVerticalOffset, $intHeight - $intMaxFontSize);
            //the angle
            $intAngle = rand(-30, 30);
            //place the background character
            $objImage2->addOperation(new ImageText($strCurrentChar, $intX, $intY, $intSize, "rgb(".$intCol1.",".$intCol2.",".$intCol3.")", "dejavusans.ttf", $intAngle));
            //place the foreground charater
            $objImage2->addOperation(new ImageText($strCurrentChar, $intX + $intForegroundOffset, $intY + $intForegroundOffset, $intSize, "rgb(".($intCol1 + 50).",".($intCol2 + 50).",".($intCol3 + 50).")", "dejavusans.ttf", $intAngle));
        }

        //register placed string to session
        Carrier::getInstance()->getObjSession()->setCaptchaCode($strCharactersPlaced);

        //and send it to the browser

        //force no-cache headers
        ResponseObject::getInstance()->addHeader("Expires: Thu, 19 Nov 1981 08:52:00 GMT");
        ResponseObject::getInstance()->addHeader("Cache-Control: no-store, no-cache, must-revalidate, private");
        ResponseObject::getInstance()->addHeader("Pragma: no-cache");

        $objImage2->setUseCache(false);
        $objImage2->sendToBrowser(Image2::FORMAT_JPG);
    }

    /**
     * Generates a grayish color and registers the color to the image
     *
     * @return int color-id in image
     */
    private function generateGreyLikeColor()
    {
        return "rgb(".(rand(150, 230).", ".rand(150, 230).", ".rand(150, 230).")");
    }

    /**
     * Returns the filename of the current image
     *
     * @return string
     */
    public function getImageFilename()
    {
        return $this->strFilename;
    }
}

define("_autotesting_", false);

$objImage = new Flyimage();
if ($objImage->getImageFilename() == "kajonaCaptcha") {
    $objImage->generateCaptchaImage();
}
else {
    $objImage->generateImage();
}

CoreEventdispatcher::getInstance()->notifyGenericListeners(SystemEventidentifier::EVENT_SYSTEM_REQUEST_AFTERCONTENTSEND, array(RequestEntrypointEnum::IMAGE()));

