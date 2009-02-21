<?php
/*"******************************************************************************************************
*   (c) 2004-2006 by MulchProductions, www.mulchprod.de                                                 *
*   (c) 2007-2009 by Kajona, www.kajona.de                                                              *
*       Published under the GNU LGPL v2.1, see /system/licence_lgpl.txt                                 *
*-------------------------------------------------------------------------------------------------------*
*	$Id$                                                    *
********************************************************************************************************/

if(!require_once("./system/includes.php"))
	die("Error including necessary files");


/**
 * This class can be used to create fast "on-the-fly" resizes of images
 * or to create a captcha image
 *
 * To resize an image, you can call this class like
 * _webpath_/image.php?image=path/to/image.jpg&maxWidth=200&maxHeight=200
 * If used with jpeg-pictures, the param quality=[1-95] can be used, default is 90
 *
 * To create a captcha image, you can call this class like
 * _webpath_/image.php?image=kajonaCaptcha
 *
 * The images are directly sent to the browser, so include the calling in img-tags
 *
 * ATTENTION:
 * Make sure to use urlencoded image-paths!!!
 * The params width, height and quality are optional
 *
 * @package modul_system
 */
class class_flyimage {

    private $strFilename;
    private $intMaxWidth;
    private $intMaxHeight;
    /**
     * Object of class_image
     *
     * @var class_image
     */
    private $objImage;
    private $intQuality;

    /**
     * constructor, inits the parent-class
     *
     */
    public function __construct() {
        //Loading all configs...
        $objCarrier = class_carrier::getInstance();
        //ok, all needed constants are set up...
        include_once(_realpath_."/system/class_image.php");
        $this->objImage = new class_image(_images_cachepath_);
        //find the params to use
        $this->strFilename = urldecode(getGet("image"));
        //avoid directory traversing
        $this->strFilename = str_replace("../", "", $this->strFilename);

        $this->intMaxHeight = (int)getGet("maxHeight");
        if($this->intMaxHeight < 0)
            $this->intMaxHeight = 0;

        $this->intMaxWidth = (int)getGet("maxWidth");
        if($this->intMaxWidth < 0)
            $this->intMaxWidth = 0;

        $this->intQuality = (int)getGet("quality");
        if($this->intQuality <= 0 || $this->intQuality > 100)
            $this->intQuality = 90;

    }

    /**
     * Here happens the magic: creating the image and sending it to the browser
     *
     */
    public function generateImage() {
        //Load the image-dimensions
        $intWidthNew = 0;
		$intHeightNew = 0;
		if(is_file(_realpath_.$this->strFilename) && (uniStrpos($this->strFilename, "/portal/pics") !== false || uniStrpos($this->strFilename, "/portal/downloads") !== false)) {
			$arrImageData = getimagesize(_realpath_.$this->strFilename);
			//check, if resizing is needed
			$bitResize = false;
			if($this->intMaxHeight == 0 && $this->intMaxWidth == 0) {
			    $bitResize = false;
			}
			else if($arrImageData[0] > $this->intMaxWidth || $arrImageData[1] > $this->intMaxHeight)	{
			    $bitResize = true;
				$floatRelation = $arrImageData[0] / $arrImageData[1]; //0 = breite, 1 = hoehe

				//chose more restricitve values
			    $intHeightNew = $this->intMaxHeight;
                $intWidthNew = $this->intMaxHeight * $floatRelation;

                if($this->intMaxHeight == 0) {
                    if($this->intMaxWidth < $arrImageData[0]) {
                        $intWidthNew = $this->intMaxWidth;
                        $intHeightNew = $intWidthNew / $floatRelation;
                    }
                    else
                        $bitResize = false;
                }
                elseif ($this->intMaxWidth == 0) {
                    if($this->intMaxHeight < $arrImageData[1]) {
                        $intHeightNew = $this->intMaxHeight;
                        $intWidthNew = $intHeightNew * $floatRelation;
                    }
                    else
                        $bitResize = false;
                }
                elseif ($intHeightNew && $intHeightNew > $this->intMaxHeight || $intWidthNew > $this->intMaxWidth) {
    				$intHeightNew = $this->intMaxWidth / $floatRelation;
                    $intWidthNew = $this->intMaxWidth;
                }
                //round to integers
                $intHeightNew = (int)$intHeightNew;
                $intWidthNew = (int)$intWidthNew;
                //avoid 0-sizes
                if($intHeightNew < 1)
                    $intHeightNew = 1;
                if($intWidthNew < 1)
                    $intWidthNew = 1;
			}

			//check headers, maybe execution could be terminated right here
			if(checkConditionalGetHeaders(md5(md5_file(_realpath_.$this->strFilename).$this->intMaxWidth.$this->intMaxHeight))) {
			    return;
			}

			//echo "width: ".$intWidthNew." (image: ".$arrImageData[0]." max: ".$this->intMaxWidth.") <br />";
			//echo "height: ".$intHeightNew." (image:".$arrImageData[1]." max: ".$this->intMaxHeight.")";
			//ok, the new dimensions are set up, so start manipulating the image
			$this->objImage->preLoadImage($this->strFilename);
			if($bitResize)
		  	    $this->objImage->resizeImage($intWidthNew, $intHeightNew, 0, true);

			//send the headers for conditional gets
			sendConditionalGetHeaders(md5(md5_file(_realpath_.$this->strFilename).$this->intMaxWidth.$this->intMaxHeight));

			//and send it to the browser
			$this->objImage->sendImageToBrowser((int)$this->intQuality);
			//release memory
			$this->objImage->releaseResources();
		}
    }

    /**
     * Generates a captcha image to defend bots.
     * To generate a captcha image, use "kajonaCaptcha" as image-param
     * when calling image.php
     * Up to now, the size-params are ignored during the creation of a
     * captcha image
     *
     */
    public function generateCaptchaImage() {
        if($this->intMaxWidth == 0 || $this->intMaxWidth > 500)
            $intWidth = 200;
        else
            $intWidth = $this->intMaxWidth;

        if($this->intMaxHeight == 0 || $this->intMaxHeight > 500)
            $intHeight = 50;
        else
            $intHeight = $this->intMaxHeight;

        $intMinfontSize = 15;
        $intMaxFontSize = 22;
        $intWidthPerChar = 30;
        $strCharsPossible = "abcdefghijklmnpqrstuvwxyz123456789";
        $intHorizontalOffset = 10;
        $intVerticalOffset = 10;
        $intForegroundOffset = 2;

        $strCharactersPlaced = "";

        //init the random-function
        srand((double)microtime()*1000000);


        //create a blank image
        $this->objImage->setIntHeight($intHeight);
        $this->objImage->setIntWidth($intWidth);
        $this->objImage->createBlankImage();
        //create a white background
        $intWhite = $this->objImage->registerColor(255, 255, 255);
        $intBlack = $this->objImage->registerColor(0, 0, 0);
        $this->objImage->drawFilledRectangle(0, 0, $intWidth, $intHeight, $intWhite);

        //draw vertical lines

        $intStart = 0;
        while($intStart < $intWidth) {
            $this->objImage->drawLine($intStart, 0, $intStart, $intWidth, $this->generateGreyLikeColor());
            $intStart += rand(10, 17);
        }
        //draw horizontal lines
        $intStart = 0;
        while($intStart < $intWidth) {
            $this->objImage->drawLine(0, $intStart, $intWhite, $intStart, $this->generateGreyLikeColor());
            $intStart += rand(10, 17);
        }

        //draw floating horizontal lines
        for($intI = 0; $intI <=3; $intI++) {
            $intXPrev= 0;
            $intYPrev = rand(0, $intHeight);
            while($intXPrev <= $intWidth) {
                $intNewX = rand($intXPrev, $intXPrev+50);
                $intNewY = rand(0, $intHeight);
                $this->objImage->drawLine($intXPrev, $intYPrev, $intNewX, $intNewY, $this->generateGreyLikeColor());
                $intXPrev = $intNewX;
                $intYPrev = $intNewY;
            }
        }

        //calculate number of characters on the image
        $intNumberOfChars = floor($intWidth/$intWidthPerChar);

        //place characters in the image
        for($intI = 0; $intI < $intNumberOfChars; $intI++) {
            //character to place
            $strCurrentChar = $strCharsPossible[rand(0, (uniStrlen($strCharsPossible)-1))];
            $strCharactersPlaced .= $strCurrentChar;
            //color to use
            $intCol1= rand(0, 200);
            $intCol2= rand(0, 200);
            $intCol3= rand(0, 200);
            $strColor = "".$intCol1.",".$intCol2.",".$intCol3;
            $strColorForeground = "".($intCol1+50).",".($intCol2+50).",".($intCol3+50);
            //fontsize
            $intSize = rand($intMinfontSize, $intMaxFontSize);
            //calculate x and y pos
            $intX = $intHorizontalOffset+($intI * $intWidthPerChar);
            $intY = $intHeight-rand($intVerticalOffset, $intHeight-$intMaxFontSize);
            //the angle
            $intAngle = rand(-30, 30);
            //place the background character
            $this->objImage->imageText($strCurrentChar, $intX, $intY, $intSize, $strColor, "dejavusans.ttf", false, $intAngle);
            //place the foreground charater
            $this->objImage->imageText($strCurrentChar, $intX+$intForegroundOffset, $intY+$intForegroundOffset, $intSize, $strColorForeground, "dejavusans.ttf", false, $intAngle);
        }

        //register placed string to session
        class_carrier::getInstance()->getObjSession()->setCaptchaCode($strCharactersPlaced);

        //sourrounding border -> should be generated by css
        //$this->objImage->drawRectangle(0, 0, ($intWidth-1), ($intHeight-1), $intBlack);
        //and send it to the browser
        //$this->objImage->saveImage("/test.jpg");
        //echo "<img src=\""._webpath_."/test.jpg\" />";
        $this->objImage->setBitNeedToSave(false);
        $this->objImage->sendImageToBrowser(60);

    }

    /**
     * Generates a greyish color and registers the color to the image
     *
     * @return int color-id in image
     */
    private function generateGreyLikeColor() {
        return $this->objImage->registerColor(rand(150, 230), rand(150, 230), rand(150, 230));
    }

    /**
     * Returns the filename of the current image
     *
     * @return string
     */
    public function getImageFilename() {
        return $this->strFilename;
    }
}

$objImage = new class_flyimage();
if($objImage->getImageFilename() == "kajonaCaptcha")
    $objImage->generateCaptchaImage();
else
    $objImage->generateImage();
?>