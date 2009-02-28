<?php
/*"******************************************************************************************************
*   (c) 2004-2006 by MulchProductions, www.mulchprod.de                                                 *
*   (c) 2007-2009 by Kajona, www.kajona.de                                                              *
*       Published under the GNU LGPL v2.1, see /system/licence_lgpl.txt                                 *
*-------------------------------------------------------------------------------------------------------*
*	$Id$                                              *
********************************************************************************************************/

/**
 * Class to modify and edit images using a nice little caching
 * In addition, new image can be created.
 * To do so, set the height and width and create a new image by calling createBlankImage()
 *
 * To have a proper working caching, mind the following:
 * 	1.) Resize the image
 *  2.) Afterwards add text to the image or overlay another image
 *
 * The caching itself works in two-steps:
 *
 * 1.) The picture is being loaded by loadImage()
 * 		-> The caching works as expected, but at the time of calling loadImage(), the image-object is created,
 * 		   using up some time (could be MUCH time!!!)
 * 2.) The picture is loaded by preLoadImage()
 * 		-> In this case, the image-object is created the time it is being worked on, before the pointer $this->objImage is null.
 * 		   Before creating the image-object, $this->bitPreload remains false, afterwards it is set to true
 *
 * @package modul_system
 */
class class_image {
	private $arrModul;
	private $objImage = null;

	private $intWidth = 0;
	private $intHeight = 0;

	private $bitPreload;
	private $strCachepath;
	private $strCacheAdd;
	private $strImagename;
	private $strCachename;
	private $strType;
	private $strImagePathOriginal;

	private $bitNeedToSave;

	/**
	 * Contructor
	 *
	 * @param string $strCachepath Path, where the cached images are saved
	 * @param string $strCacheAdd Additional string to add
	 */
	public function __construct($strCachepath = "", $strCacheAdd = "") {
		$this->arrModul["name"] 		= "class_bild";
		$this->arrModul["author"] 		= "sidler@mulchprod.de";
		$this->arrModul["moduleId"]		= _system_modul_id_;

		$this->strCachepath = $strCachepath;
		$this->strCacheAdd = $strCacheAdd;
		$this->bitNeedToSave = true;
		$this->bitPreload = false;

		// Try to overwrite PHP memory-limit so large images can be processed, too
		if (class_carrier::getInstance()->getObjConfig()->getPhpIni("memory_limit") < 64)
			@ini_set("memory_limit", "64M");
	}

	public function __destruct() {
	    $this->releaseResources();
	}


	/**
	 * Tries to create a new image using the dimensions set before
	 *
	 * @param string $strType
	 * @return bool
	 */
	public function createBlankImage($strType = ".jpg") {
        $this->strType = $strType;
        if($this->intHeight != 0 && $this->intWidth != 0) {
            $this->objImage = imagecreatetruecolor($this->intWidth, $this->intHeight);
        }
        else
            return false;
	}


	/**
	 * Loads all properties from the image, but doesn't create the image-object yet
	 * to save runtime. This is being done by finalLoadImage()
	 *
	 * @param string $strImage
	 * @return bool
	 */
	public function preLoadImage($strImage) {
		$bitReturn = false;
		$this->bitPreload = true;
		if(is_file(_realpath_.$strImage))  {
			$strType = strtolower(uniSubstr($strImage, uniStrrpos($strImage, ".")));
			$arrInfo = getimagesize(_realpath_.$strImage);
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
	 * Creates the imageobject, if the image was loaded using preLoadImage()
	 *
	 * @return bool
	 */
	private function finalLoadImage() {
		$bitReturn = false;
		if($this->bitPreload) {
			switch ($this->strType) {
				case ".jpg":
					$this->objImage = imagecreatefromjpeg(_realpath_.$this->strImagePathOriginal."/".$this->strImagename);
					break;
				case ".png":
					$this->objImage = imagecreatefrompng(_realpath_.$this->strImagePathOriginal."/".$this->strImagename);
					break;
				case ".gif":
					$this->objImage = imagecreatefromgif(_realpath_.$this->strImagePathOriginal."/".$this->strImagename);
					break;
				}
			$bitReturn = true;
			}
		else
			$bitReturn = false;

		return $bitReturn;
	}

	/**
	 * DEPRECATED!!! USE preLoadImage() INSTEAD!!!
	 * Loads an image from filesystem
	 *
	 * @param string $strImage
	 * @return bool
	 * @deprecated use preLoadImage() instead
	 * @see class_image::preLoadImage()
	 */
	public function loadImage($strImage) {
		$bitReturn = false;
		//Datei Existent?
		if(is_file(_realpath_.$strImage)) {
			//DateiEndung bestimmen
			$strType = strtolower(uniSubstr($strImage, uniStrrpos($strImage, ".")));
			switch ($strType) {
				case ".jpg":
					$this->objImage = imagecreatefromjpeg(_realpath_.$strImage);
					break;
				case ".png":
					$this->objImage = imagecreatefrompng(_realpath_.$strImage);
					break;
				case ".gif":
					$this->objImage = imagecreatefromgif(_realpath_.$strImage);
					break;
			}
			if($this->objImage != null) {
				$arrInfo = getimagesize(_realpath_.$strImage);
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
	 * @return bool
	 */
	public function saveImage($strTarget = "", $bitCache = false, $intJpegQuality = 90) {
		$bitReturn = false;
		if($this->bitNeedToSave) {
			//Wenn Cache Aktiviert, dann unter Cachename speichern
			if($bitCache && $this->strCachename != "")
				$strTarget = $this->strCachepath.$this->strCachename;
			//DateiEndung bestimmen
			if($strTarget != "")
			    $strType = uniSubstr($strTarget, uniStrrpos($strTarget, "."));
			else
			    $strType = $this->strType;

			if($strTarget == "")
			   $strTarget = "/".$this->strCachepath.$this->generateCachename(0, 0, $intJpegQuality);

			switch (strtolower($strType)) {
				case ".jpg":
					imagejpeg($this->objImage, _realpath_.$strTarget, $intJpegQuality);
					$bitReturn = true;
					break;
				case ".png":
					imagepng($this->objImage, _realpath_.$strTarget);
					$bitReturn = true;
					break;
				case ".gif":
					imagegif($this->objImage, _realpath_.$strTarget);
					$bitReturn = true;
					break;
			}
		}
		else
			$bitReturn = true;

        if(!$bitReturn)
            class_logger::getInstance()->addLogRow("error saving file to ".$strTarget, class_logger::$levelWarning);

		return $bitReturn;
	}

	/**
	 * Saves the current image to the filesystem and sends it to the browser
	 *
	 * @param int $intJpegQuality
	 *
	 */
	public function sendImageToBrowser($intJpegQuality = 90) {
	    //Check, if we already got an image
        $bitFileExistsInFilesystem = false;
	    if($this->objImage == null && $this->bitPreload) {
	        if(is_file(_realpath_.$this->strCachepath.$this->strCachename)) {
	            $this->preLoadImage($this->strCachepath.$this->strCachename);
	        }
			$this->finalLoadImage();
	    }


        $this->saveImage("", true, $intJpegQuality);

        //and send it to the browser
        if($this->strCachename != null && $this->strCachename != "")
		    $strType = uniSubstr($this->strCachename, uniStrrpos($this->strCachename, "."));
		else
		    $strType = $this->strType;


		switch ($strType) {
			case ".jpg":
			    header("Content-type: image/jpeg");
				break;
			case ".png":
			    header("Content-type: image/png");
				break;
			case ".gif":
			    header("Content-type: image/gif");
				break;
			}

        //stream image directly from the filesystems is available
        if(is_file(_realpath_.$this->strCachepath.$this->strCachename)) {
            $ptrFile = @fopen(_realpath_.$this->strCachepath.$this->strCachename, 'rb');
            fpassthru($ptrFile);
            @fclose($ptrFile);
        }
        else {
            switch ($strType) {
			case ".jpg":
				imagejpeg($this->objImage, "", $intJpegQuality);
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
	 * Resizes the image to the given params
	 *
	 * @param int $intWidth
	 * @param int $intHeight
	 * @param int $intFactor
	 * @param int $bitCache
	 * @return bool
	 */
	public function resizeImage($intWidth = 0, $intHeight = 0, $intFactor = 0, $bitCache = false) {
		$bitReturn = false;
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

			if(is_file(_realpath_.$this->strCachepath.$this->strCachename)) {
				$this->bitNeedToSave = false;
				$this->intWidth = $intWidthNew;
				$this->intHeight = $intHeightNew;
				return true;
			}
		}


		//Hier bei Bedarf das Bild nachladen
		if($this->objImage == null && $this->bitPreload)
			$this->finalLoadImage();

		//Abmessungen bestimmt, nun damit ein neues Bild anlegen
		$objImageResized = imagecreatetruecolor($intWidthNew, $intHeightNew);

		//Nun den Inhalt des alten Bildes skaliert in das neue Bild legen
		//imagecopyresized($obj_bild_resized, $this->obj_bild, 0, 0, 0, 0, $int_breite_neu, $int_hoehe_neu, $this->int_breite, $this->int_hoehe);
		imagecopyresampled($objImageResized, $this->objImage, 0, 0, 0, 0, $intWidthNew, $intHeightNew, $this->intWidth, $this->intHeight);

		//Dieses Bild als neues verwenden
		$this->intWidth = $intWidthNew;
		$this->intHeight = $intHeightNew;
		imagedestroy($this->objImage);
		$this->objImage = $objImageResized;
		$bitReturn = true;

		return $bitReturn;
	}


    /**
     * Crops the current image. Therefore you are able to set the start x/y-coordinates and the
     * desired height and width of the section to keep.
     *
     * @param int $intXStart
     * @param int $intYStart
     * @param int $intWidth
     * @param int $intHeight
     * @return bool
     */
    public function cropImage($intXStart, $intYStart, $intWidth, $intHeight) {
    	$bitReturn = false;

        //load the original image
        //Hier bei Bedarf das Bild nachladen
        if($this->objImage == null && $this->bitPreload)
            $this->finalLoadImage();

        //create a new image using the desired size
        $objImageCropped = imagecreatetruecolor($intWidth, $intHeight);

        //copy the selected region
        imagecopy($objImageCropped, $this->objImage, 0, 0, $intXStart, $intYStart, $intWidth, $intHeight);
        $this->intWidth = $intWidth;
        $this->intHeight = $intHeight;
        imagedestroy($this->objImage);
        $this->objImage = $objImageCropped;
        $bitReturn = true;

        return $bitReturn;
    }

    /**
     * Rotates an image. Note: Only a degree of 90, 180 or 270 is allowed!
     *
     * @param int $intAngle
     * @return bool
     */
    public function rotateImage($intAngle) {
    	$bitReturn = false;

        //load the original image
        //Hier bei Bedarf das Bild nachladen
        if($this->objImage == null && $this->bitPreload)
            $this->finalLoadImage();

        //different cases. 180 is easy ;)
        if($intAngle == 180) {
        	$this->objImage = imagerotate($this->objImage, 180, 0);
            $bitReturn = true;
        }
        elseif($intAngle == 90) {

            //Set up the temp image
            $intSquareSize = $this->intWidth > $this->intHeight ? $this->intWidth : $this->intHeight;
            $objSquareImage = imagecreatetruecolor($intSquareSize, $intSquareSize);
            //copy the existing image into the new image
            if($this->intWidth > $this->intHeight)
                imagecopy($objSquareImage, $this->objImage, 0, ($this->intWidth-$this->intHeight)/2, 0, 0, $this->intWidth, $this->intHeight);
            else
                imagecopy($objSquareImage, $this->objImage, ($this->intHeight-$this->intWidth)/2, 0, 0, 0, $this->intWidth, $this->intHeight);

            //rotate the image
            $objSquareImage = imagerotate($objSquareImage, 90, 0, -1);
            //crop to the final sizes
            imagedestroy($this->objImage);

            if($this->intWidth > $this->intHeight) {
                $this->objImage = imagecreatetruecolor($this->intHeight, $this->intWidth);
                imagecopy($this->objImage, $objSquareImage ,0,0, ($this->intWidth-$this->intHeight)/2,0, $this->intHeight, $this->intWidth);
            }
            else  {
                $this->objImage = imagecreatetruecolor($this->intHeight, $this->intWidth);
                imagecopy($this->objImage, $objSquareImage, 0,0, 0,($this->intHeight-$this->intWidth)/2, $this->intHeight, $this->intWidth);
            }


            $intWidthTemp = $this->intWidth;
            $this->intWidth = $this->intHeight;
            $this->intHeight = $intWidthTemp;
            $bitReturn = true;
        }
        elseif($intAngle == 270) {

            //Set up the temp image
            $intSquareSize = $this->intWidth > $this->intHeight ? $this->intWidth : $this->intHeight;
            $objSquareImage = imagecreatetruecolor($intSquareSize, $intSquareSize);
            //copy the existing image into the new image
            if($this->intWidth > $this->intHeight)
                imagecopy($objSquareImage, $this->objImage, 0, ($this->intWidth-$this->intHeight)/2, 0, 0, $this->intWidth, $this->intHeight);
            else
                imagecopy($objSquareImage, $this->objImage, ($this->intHeight-$this->intWidth)/2, 0, 0, 0, $this->intWidth, $this->intHeight);

            //rotate the image
            $objSquareImage = imagerotate($objSquareImage, 270, 0, -1);
            //crop to the final sizes
            imagedestroy($this->objImage);

            if($this->intWidth > $this->intHeight) {
                $this->objImage = imagecreatetruecolor($this->intHeight, $this->intWidth);
                imagecopy($this->objImage, $objSquareImage ,0,0, ($this->intWidth-$this->intHeight)/2,0, $this->intHeight, $this->intWidth);
            }
            else  {
                $this->objImage = imagecreatetruecolor($this->intHeight, $this->intWidth);
                imagecopy($this->objImage, $objSquareImage, 0,0, 0,($this->intHeight-$this->intWidth)/2, $this->intHeight, $this->intWidth);
            }

        	$intWidthTemp = $this->intWidth;
            $this->intWidth = $this->intHeight;
            $this->intHeight = $intWidthTemp;
            $bitReturn = true;
        }



        return $bitReturn;
    }


	/**
	 * Writes text into the image
	 *
	 * @param string $strText
	 * @param int $intX
	 * @param int $intY
	 * @param int $intSize
	 * @param string $strColor
	 * @param string $strFont
	 * @param bool $bitCache
	 * @param int $intAngle
	 * @return bool
	 */
	public function imageText($strText, $intX, $intY, $intSize = "20", $strColor = "255,255,255", $strFont = "dejavusans.ttf", $bitCache = false, $intAngle = 0) {
		$bitReturn = false;

		//Cache?
		if($bitCache) 	{
			//Aus den ermittelten Daten einen Namen erzeugen
			$this->strCachename = $this->generateCachename();
			if(is_file(_realpath_.$this->strCachepath.$this->strCachename)) {
				$this->bitNeedToSave = false;
				return true;
			}
		}

		//Hier bei Bedarf das Bild nachladen
		if($this->objImage == null && $this->bitPreload)
			$this->finalLoadImage();

		$intColor = 0;
	    //Farbe bestimmen
        if(is_int($strColor)) {
            $intColor = $strColor;
        }else{
            $arrayColors = explode(",", $strColor);
            $intColor = imagecolorallocate($this->objImage, $arrayColors[0], $arrayColors[1], $arrayColors[2]);
        }

		//Schrift laden
        if(is_file(_systempath_."/fonts/".$strFont) && function_exists("imagefttext")) {
			@imagefttext($this->objImage, $intSize, $intAngle, $intX, $intY, $intColor, _systempath_."/fonts/".$strFont, $strText);
		}

		return $bitReturn;
	}

	/**
	 * Places an image on top of another image
	 * In case you want to overlay transparent images, use gifs instead of pngs
	 *
	 * @param string $strImage The image to place into the loaded image
	 * @param int $intX
	 * @param int $intY
	 * @param bool $bitCache
	 * @return bool
	 */
	public function overlayImage($strImage, $intX = 0, $intY = 0, $bitCache = false) {
	    $bitReturn = false;
	    //register for using the caching
	    $this->strCacheAdd .= $strImage;

	    //Cache?
		if($bitCache) 	{
			//create cachename
			$this->strCachename = $this->generateCachename();
			if(is_file(_realpath_.$this->strCachepath.$this->strCachename)) {
				$this->bitNeedToSave = false;
				return true;
			}
		}

		//load image
		if($this->objImage == null && $this->bitPreload)
			$this->finalLoadImage();

		//load the other image into the system using another instance
		$objOverlayImage = new class_image();
		if($objOverlayImage->loadImage($strImage)) {
		    //merge pics
		    $bitReturn = imagecopymerge($this->objImage, $objOverlayImage->getImageResource(), $intX, $intY, 0, 0, $objOverlayImage->getIntWidth(), $objOverlayImage->getIntHeight(), 100);
		}

		return $bitReturn;
	}

//-- RAW-Functions --------------------------------------------------------------------------------------

    /**
     * Tries to add a color to the current image object
     *
     * @param int $intRed
     * @param int $intGreen
     * @param int $intBlue
     * @return int the id of the color created, false in case of errors
     */
    public function registerColor($intRed, $intGreen, $intBlue) {
        $intColorId = false;
        if($this->objImage != null)
            $intColorId = imagecolorallocate($this->objImage, $intRed, $intGreen, $intBlue);

        return $intColorId;
    }


    /**
     * Tries to set a color transparent to the current image object
     *
     * @param int int the id of the color
     * @return boolean true or false in case of errors
     */
    public function setColorTransparent($intColorId = 0) {
        $bitReturn = false;
        if($this->objImage != null && $intColorId > 0 && $this->strType != ".jpg"){
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
     * @return bool
     */
    public function drawFilledRectangle($intStartX, $intStartY, $intWidth, $intHeight, $intColor) {
        if($this->objImage != null) {
            imagefilledrectangle($this->objImage, $intStartX, $intStartY, ($intStartX+$intWidth), ($intStartY+$intHeight), $intColor);
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
     * @return bool
     */
    public function drawRectangle($intStartX, $intStartY, $intWidth, $intHeight, $intColor) {
        if($this->objImage != null) {
            imagerectangle($this->objImage, $intStartX, $intStartY, ($intStartX+$intWidth), ($intStartY+$intHeight), $intColor);
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
     * @return array or false in case of errors
     */
    public function getBoundingTextbox($intFontSize, $floatAngle, $strFont, $strText){
        if(is_file(_systempath_."/fonts/".$strFont)) {
            return @ImageTTFBBox($intFontSize, $floatAngle, _systempath_."/fonts/".$strFont, $strText);
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
     * @return boolean
     */
    public function drawArc($intCX, $intCY, $intWidth, $intHeight, $intStart, $intEnd, $intColor){
        if($this->objImage != null) {
            imagearc($this->objImage, $intCX, $intCY, $intWidth, $intHeight, $intStart, $intEnd, $intColor);
            return true;
        }
        return false;
    }

//--Helferfunktionen-------------------------------------------------------------------------------------

	/**
	 * Generates a md5 to make the cached image unique
	 *
	 * @param int $intHeight
	 * @param int $intWidth
	 * @return string
	 */
	private function generateCachename($intHeight = 0, $intWidth = 0) {
		if($intWidth == 0)
			$intWidth = $this->intWidth;
		if($intHeight == 0)
			$intHeight = $this->intHeight;

		$intFilesize = @filesize(_realpath_.$this->strImagePathOriginal."/".$this->strImagename);

		$strReturn = md5($this->strImagePathOriginal.$this->strImagename.$intHeight.$intWidth.$this->strCacheAdd.$intFilesize).$this->strType;

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
	 *
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
	 * Why the hell do you need it? Think tiwce!
	 *
	 * @return resource
	 */
	public function getImageResource() {
	    return $this->objImage;
	}

} //class_image

?>