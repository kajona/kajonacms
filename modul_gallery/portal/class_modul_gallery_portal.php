<?php
/*"******************************************************************************************************
*   (c) 2004-2006 by MulchProductions, www.mulchprod.de                                                 *
*   (c) 2007-2009 by Kajona, www.kajona.de                                                              *
*       Published under the GNU LGPL v2.1, see /system/licence_lgpl.txt                                 *
*-------------------------------------------------------------------------------------------------------*
*	$Id$                               *
********************************************************************************************************/

//Include der Mutter-Klasse
include_once(_portalpath_."/class_portal.php");
include_once(_portalpath_."/interface_portal.php");
//needed models
include_once(_systempath_."/class_modul_gallery_gallery.php");
include_once(_systempath_."/class_modul_gallery_pic.php");

/**
 * Gallery Portal. Loads the thumbnails or detail-views
 *
 * @package modul_gallery
 */
class class_modul_gallery_portal extends class_portal implements interface_portal {
	/**
	 * Constructor
	 *
	 * @param mixed $arrElementData
	 */
	public function __construct($arrElementData) {
        $arrModul = array();
		$arrModul["name"] 				= "modul_gallery";
		$arrModul["author"] 			= "sidler@mulchprod.de";
		$arrModul["table"]  			= _dbprefix_."gallery_gallery";
		$arrModul["table2"] 			= _dbprefix_."gallery_pic";
		$arrModul["moduleId"] 			= _bildergalerie_modul_id_;
		$arrModul["modul"]  			= "gallery";

		parent::__construct($arrModul, $arrElementData);

	}

	/**
	 * Action-block, controling the behaviour of the class
	 *
	 * @return string
	 */
	public function action() {
	    $strReturn = "";
		$strAction = "";

		if($this->getParam("action") != "")
		    $strAction = $this->getParam("action");

		if($strAction == "detailImage") {
			if($this->checkIfRequestedIdIsInElementsTree())
			    $strReturn = $this->actionDetailImage();
			else
			    $strReturn = $this->actionList();
		}
		elseif($strAction == "imageFolder")
		    $strReturn = $this->actionList();
		elseif($this->arrElementData["gallery_mode"] == 1)
		    $strReturn .= $this->actionRandom();
		else
		    $strReturn .= $this->actionList();

		return $strReturn;

	}

//---Listenfunktionen------------------------------------------------------------------------------------

	/**
	 * Creates a list of thumbnails
	 *
	 * @return string
	 */
	private function actionList() {
		$strReturn = "";

		//Determin the prev_id to load
		if($this->getSystemid() == "0" || $this->getSystemid() == "" || $this->getParam("action") != "imageFolder" || !$this->checkIfRequestedIdIsInElementsTree()) {
		    $this->setSystemid($this->arrElementData["gallery_id"]);
		}

		$bitPageview = false;
		//load using the pageview?
        $arrTempImages = array();
        $arrImages = array();
		if($this->arrElementData["gallery_imagesperpage"] > 0) {
		    $bitPageview = true;
		    include_once(_systempath_."/class_array_section_iterator.php");
            $objArraySectionIterator = new class_array_section_iterator(class_modul_gallery_pic::getFileCount($this->getSystemid(), false, true));
            $objArraySectionIterator->setIntElementsPerPage($this->arrElementData["gallery_imagesperpage"]);
            $objArraySectionIterator->setPageNumber((int)($this->getParam("pv") != "" ? $this->getParam("pv") : 1));
            $objArraySectionIterator->setArraySection(class_modul_gallery_pic::loadFilesDBSection($this->getSystemid(), false, true, $objArraySectionIterator->calculateStartPos(), $objArraySectionIterator->calculateEndPos()));

		    $arrImages = $objArraySectionIterator->getArrayExtended();
		    $arrTempImages = $this->objToolkit->pager($this->arrElementData["gallery_imagesperpage"],
		                                              ($this->getParam("pv") != "" ? $this->getParam("pv") : 1),
		                                              $this->getText("forwardlink"),
		                                              $this->getText("backlink"),
		                                              $this->getParam("action"),
		                                              ($this->getParam("page") != "" ? $this->getParam("page") : ""),
		                                              $arrImages,
		                                              "&systemid=".$this->getSystemid());
		    $arrImages = $arrTempImages["arrData"];
		}
		else {
		    //Load all Images & Folder
            $arrImages = class_modul_gallery_pic::loadFilesDB($this->getSystemid(), false, true);
		}

		//Loop over every item and collect them
		$arrTemplate  = array();
		$arrTemplate["folderlist"] = "";
		$arrTemplate["piclist"] = "";

        $arrTemplateImage = array();
		if(count($arrImages) > 0) {
		    $intImageCounter = 0;
			foreach ($arrImages as $objOneImage) {
				//Check rights
				if($this->objRights->rightView($objOneImage->getSystemid())) {
					//Folder or image?
					if($objOneImage->getIntType() == 0) {
                       //defined nr of images oer row?
					    if($this->arrElementData["gallery_nrow"] > 0) {
    						//Image
    						$arrTemplateImage["pic_".$intImageCounter % $this->arrElementData["gallery_nrow"]] = getLinkPortal($this->getPagename(), "", "_self", $this->generateImage($objOneImage->getStrFilename(), $this->arrElementData["gallery_maxh_p"], $this->arrElementData["gallery_maxw_p"]), "detailImage", "", $objOneImage->getSystemid(), "", "", $objOneImage->getStrName());
    						$arrTemplateImage["name_".$intImageCounter % $this->arrElementData["gallery_nrow"]] = $objOneImage->getStrName();
    						$arrTemplateImage["subtitle_".$intImageCounter % $this->arrElementData["gallery_nrow"]] = $objOneImage->getStrSubtitle();
    						$intImageCounter++;

    						if($intImageCounter % $this->arrElementData["gallery_nrow"] == 0) {
    							$strTemplateID = $this->objTemplate->readTemplate("/modul_gallery/".$this->arrElementData["gallery_template"], "piclist");
    							$arrTemplate["piclist"] .= $this->fillTemplate($arrTemplateImage, $strTemplateID);
    							$arrTemplateImage = array();
    						}
					    }
					    //unlimited nr of images per row, no linebreaks
					    else {
                            //Image
    						$arrTemplateImage["pic"] = getLinkPortal($this->getPagename(), "", "_self", $this->generateImage($objOneImage->getStrFilename(), $this->arrElementData["gallery_maxh_p"], $this->arrElementData["gallery_maxw_p"]), "detailImage", "", $objOneImage->getSystemid(), "", "", $objOneImage->getStrName());
    						$arrTemplateImage["name"] = $objOneImage->getStrName();
    						$arrTemplateImage["subtitle"] = $objOneImage->getStrSubtitle();

							$strTemplateID = $this->objTemplate->readTemplate("/modul_gallery/".$this->arrElementData["gallery_template"], "piclist_unlimited");
							$arrTemplate["piclist"] .= $this->fillTemplate($arrTemplateImage, $strTemplateID);
							$arrTemplateImage = array();
					    }
					}

					if($objOneImage->getIntType() == 1) {
						//Folder
						$arrFolder = array();
						$arrFolder["folder_name"] = $objOneImage->getStrName();
						$arrFolder["folder_description"] = $objOneImage->getStrDescription();
						$arrFolder["folder_subtitle"] = $objOneImage->getStrSubtitle();
						$arrFolder["folder_link"] = getLinkPortal($this->getPagename(), "", "_self",  $this->getText("galerie_ordner_link"), "imageFolder", "", $objOneImage->getSystemid(), "", "", $objOneImage->getStrName());
						$arrFolder["folder_href"] = getLinkPortalHref($this->getPagename(), "", "imageFolder", "", $objOneImage->getSystemid(), "", $objOneImage->getStrName());
						$strTemplateID = $this->objTemplate->readTemplate("/modul_gallery/".$this->arrElementData["gallery_template"], "folderlist");
						$arrTemplate["folderlist"] .= $this->fillTemplate($arrFolder, $strTemplateID);

					}
				}
			}
			//Print remaining images
			if(count($arrTemplateImage) > 0) {
				$strTemplateID = $this->objTemplate->readTemplate("/modul_gallery/".$this->arrElementData["gallery_template"], "piclist");
				$arrTemplate["piclist"] .= $this->fillTemplate($arrTemplateImage, $strTemplateID);
				$arrTemplateImage= array();
			}
		}
		else
			$strReturn = $this->getText("liste_leer");

		//and load the sourrounding template
		if($bitPageview) {
		    $arrTemplate["link_forward"] = $arrTempImages["strForward"];
            $arrTemplate["link_pages"] = $arrTempImages["strPages"];
            $arrTemplate["link_back"] = $arrTempImages["strBack"];
		}
		$strTemplateID = $this->objTemplate->readTemplate("/modul_gallery/".$this->arrElementData["gallery_template"], "list");
		$arrTemplate["pathnavigation"] = $this->generatePathnavi();
		$strReturn .= $this->fillTemplate($arrTemplate, $strTemplateID);
		return $strReturn;
	}

//---Detailfunktionen------------------------------------------------------------------------------------

	/**
	 * Prints a image as a detailed-view
	 * and generates forward / backward links
	 *
	 * @return string
	 */
	private function actionDetailImage() {
		$strReturn = "";
		//Load record
		$objImage = new class_modul_gallery_pic($this->getSystemid());
		$arrImage = $this->getNextPrevIds();

		//Load template
		$strTemplateID = $this->objTemplate->readTemplate("/modul_gallery/".$this->arrElementData["gallery_template"], "picdetail");
		//Collect Data
		$arrImage["pic_url"] = $this->generateImage($objImage->getStrFilename(), $this->arrElementData["gallery_maxh_d"], $this->arrElementData["gallery_maxw_d"], $this->arrElementData["gallery_text"], "12", $this->arrElementData["gallery_text_x"], $this->arrElementData["gallery_text_y"]);

		//previous 3 images
		$arrImage["backlink"] = ($arrImage["backward_1"] != "" ? getLinkPortal($this->getPagename(), "", "",  $this->getText("backlink"), "detailImage", "", $arrImage["backward_1"] ) : "" );
        for($intI = 1; $intI <= 3; $intI++) {
    		if($arrImage["backward_".$intI] != "") {
                $objImageBack = new class_modul_gallery_pic($arrImage["backward_".$intI]);
                $arrImage["backlink_image_".$intI] = getLinkPortal($this->getPagename(), "", "", "<img src=\"image.php?image=".$objImageBack->getStrFilename()."&maxWidth=".$this->arrElementData["gallery_maxw_m"]."&maxHeight=".$this->arrElementData["gallery_maxh_m"]."\" border=\"0\"/>", "detailImage", "", $objImageBack->getSystemid(), "", "", $objImageBack->getStrName());
                $arrImage["backlink_image_filename_".$intI] = urlencode($objImageBack->getStrFilename());
                $arrImage["backlink_image_systemid_".$intI] = $objImageBack->getSystemid();
            }
        }

        //next 3 images
        $arrImage["forwardlink"] = ($arrImage["forward_1"] != "" ? getLinkPortal($this->getPagename(), "", "",  $this->getText("forwardlink"), "detailImage", "", $arrImage["forward_1"] ) : "" );
        for($intI = 1; $intI <= 3; $intI++) {
    		if($arrImage["forward_".$intI] != "") {
                $objImageFwd = new class_modul_gallery_pic($arrImage["forward_".$intI]);
                $arrImage["forwardlink_image_".$intI] = getLinkPortal($this->getPagename(), "", "", "<img src=\"image.php?image=".$objImageFwd->getStrFilename()."&maxWidth=".$this->arrElementData["gallery_maxw_m"]."&maxHeight=".$this->arrElementData["gallery_maxh_m"]."\" border=\"0\"/>", "detailImage", "", $objImageFwd->getSystemid(), "", "", $objImageFwd->getStrName());
                $arrImage["forwardlink_image_filename_".$intI] = urlencode($objImageFwd->getStrFilename());
                $arrImage["forwardlink_image_systemid_".$intI] = $objImageFwd->getSystemid();
            }
        }

        //current image
        $arrImage["pic_small"] = getLinkPortal($this->getPagename(), "", "", "<img src=\"image.php?image=".$objImage->getStrFilename()."&maxWidth=".$this->arrElementData["gallery_maxw_m"]."&maxHeight=".$this->arrElementData["gallery_maxh_m"]."\" border=\"0\"/>", "detailImage", "", $objImage->getSystemid(), "currentPic");

		$arrImage["overview"] = ($objImage->getPrevId() != "0" ? getLinkPortal($this->getPagename(), "", "",  $this->getText("uebersicht"), "imageFolder", "", $objImage->getPrevId()) : "" );
		$arrImage["pathnavigation"] = $this->generatePathnavi(true);
		$arrImage["systemid"] = $this->getSystemid();
		$arrImage["pic_name"] = $objImage->getStrName();
		$arrImage["pic_description"] = $objImage->getStrDescription();
        $arrImage["pic_subtitle"] = $objImage->getStrSubtitle();
		$arrImage["pic_filename"] = $objImage->getStrFilename();
		$arrImage["pic_size"] = $objImage->getIntSize();
		$arrImage["pic_hits"] = $objImage->getIntHits();

		//ratings available?
		if($objImage->getFloatRating() !== null) {
		    $arrImage["pic_rating"] = $this->buildRatingBar($objImage->getFloatRating(), $objImage->getSystemid(), $objImage->isRateableByUser(), $objImage->rightRight2());
		}

		$strReturn = $this->fillTemplate($arrImage, $strTemplateID);

		//Update view counter
		$objImage->setIntHits($objImage->getIntHits()+1);
		$objImage->updateObjectToDb(false);
		return $strReturn;
	}

//---Random----------------------------------------------------------------------------------------------

    /**
     * Selects a random image out of the selected gallery and creates a detail-view
     *
     * @return string
     */
    private function actionRandom() {
        //Fetch all images of the selected category
        $arrRandom = array();
        $arrRandom = $this->loadImagesRecursive($this->arrElementData["gallery_id"]);

        //Count images
        $intNumber = count($arrRandom)-1;
        //and a random number
        srand ((double)microtime()*1000000);
        $intRand = rand(0, $intNumber);
        //set the systemid as current
        if(isset($arrRandom[$intRand]))
            $this->setSystemid($arrRandom[$intRand]);
        //and load all
        return $this->actionDetailImage();
    }

    /**
     * Loads all images to find one randomly
     *
     * @param string $strStartID
     * @return mixed
     */
    private function loadImagesRecursive($strStartID) {
        $arrRandom = array();
        $arrCurrLevel = class_modul_gallery_pic::loadFilesDB($strStartID, false, true);
        if(count($arrCurrLevel) > 0) {
            foreach($arrCurrLevel as $objOneImage) {
                if($objOneImage->getIntType() == 0 && $objOneImage->rightView())
                    $arrRandom[] = $objOneImage->getSystemid();

                //Load all childs
                $arrTemp = $this->loadImagesRecursive($objOneImage->getSystemid());
                foreach ($arrTemp as $strOneTemp) {
                	$arrRandom[] = $strOneTemp;
                }
            }
        }
        return $arrRandom;
    }

//---Bildfunktionen--------------------------------------------------------------------------------------

	/**
	 * Generates an image an returns the complete html-image-tag. Uses caching!
	 *
	 * @param string $strImage
	 * @param int $intHeight
	 * @param int $intWidth
	 * @param string $strText
	 * @param int $intTextSize
	 * @param int $intTextX
	 * @param int $intTextY
	 * @param string $strFont
	 * @param string $strFontColor
	 * @return string the complete html-img tag
	 */
	private function generateImage($strImage, $intHeight, $intWidth, $strText = "", $intTextSize = "20", $intTextX = 20, $intTextY= 20, $strFont = "dejavusans.ttf", $strFontColor= "255,255,255") {
	    $strReturn = "No image defined!!";
		$intWidthNew = 0;
		$intHeightNew = 0;
		if(is_file(_realpath_.$strImage)) {
			//If theres text to put over the image, manipulate image "inline",
			//otherwise let the work do image.php -> kinda multithreading ;)
			if($strText == "") {
				$strReturn = "<img src=\"image.php?image=".urlencode($strImage)."&maxWidth=".$intWidth."&maxHeight=".$intHeight."\" border=\"0\" />";
			}
			else {
			    //do everything right now
			    $arrImageData = getimagesize(_realpath_.$strImage);
			    //check, if resizing is needed
			    $bitResize = false;
			    if($intHeight == 0 && $intWidth == 0) {
			        $bitResize = false;
    			}
    			else if($arrImageData[0] > $intWidth || $arrImageData[1] > $intHeight)	{
    			    $bitResize = true;
    				$floatRelation = $arrImageData[0] / $arrImageData[1]; //0 = breite, 1 = hoehe

    				//chose more restricitve values
    			    $intHeightNew = $intHeight;
                    $intWidthNew = $intHeight * $floatRelation;

                    if($intHeight == 0) {
                        if($intWidth < $arrImageData[0]) {
                            $intWidthNew = $intWidth;
                            $intHeightNew = $intWidthNew / $floatRelation;
                        }
                        else
                            $bitResize = false;
                    }
                    elseif ($intWidth == 0) {
                        if($intHeight < $arrImageData[1]) {
                            $intHeightNew = $intHeight;
                            $intWidthNew = $intHeightNew * $floatRelation;
                        }
                        else
                            $bitResize = false;
                    }
                    elseif ($intHeightNew && $intHeightNew > $intHeight || $intWidthNew > $intWidth) {
        				$intHeightNew = $intWidth / $floatRelation;
                        $intWidthNew = $intWidth;
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

				include_once(_systempath_."/class_image.php");
				$objImage = new class_image(_images_cachepath_, $strText);
				//Edit Picture
				if($objImage->preLoadImage($strImage)) {
					//resize the image
					if($bitResize)
					    $objImage->resizeImage($intWidthNew, $intHeightNew, 0, true);
					//Inlay text
					if($strText != "")
						$objImage->imageText($strText, $intTextX, $intTextY, $intTextSize, $strFontColor, $strFont, true);
					$objImage->saveImage("", true);
					$strImageName = $objImage->getCachename();
					$strReturn = "<img src=\"_webpath_"._images_cachepath_.$strImageName."\" border=\"0\" />";
					//and release memory
					$objImage->releaseResources();
				}
			    else
				    $strReturn = "Error manipulating image!";
			}
		}
		else 	//Nichts zu tun, Bild so ausgeben
			$strReturn = "<img src=\"_webpath_".$strImage."\" />";

		return $strReturn;
	}

//---Pfadfunktionen--------------------------------------------------------------------------------------

	/**
	 * Generates a litte path-navigation across the folders
	 *
	 * @param bool $bitCurrentViewIsDetail
	 * @return string
	 */
	private function generatePathnavi($bitCurrentViewIsDetail = false) {
		$strReturn = "";
		//Load the current record
		$objData = new class_modul_gallery_pic($this->getSystemid());
		$objGallery = new class_modul_gallery_gallery($this->arrElementData["gallery_id"]);
		//If the record is empty, try to load the gallery
		if($objData->getStrName() == "") {
		      $objData = new class_modul_gallery_gallery($this->getSystemid());
		}
        $arrTemplate = array();
		//Name and link
		if($bitCurrentViewIsDetail)
			$arrTemplate["pathnavigation_point"] = getLinkPortal($this->getPagename(), "", "_self", $objData->getStrName(), "detailImage", "", $objData->getSystemid(), "", "", $objData->getStrName());
		else
			$arrTemplate["pathnavigation_point"] = getLinkPortal($this->getPagename(), "", "_self", $objData->getStrName(), "imageFolder", "", $objData->getSystemid(), "", "", $objData->getStrName());

		$strTemplateID = $this->objTemplate->readTemplate("/modul_gallery/".$this->arrElementData["gallery_template"], "pathnavigation_level");
		$strReturn .= $this->fillTemplate($arrTemplate, $strTemplateID);

		while($objData->getPrevId() != "" && $objData->getPrevId() != "0" && $objData->getSystemid() != $objGallery->getSystemid()) {
			$objData = new class_modul_gallery_pic($objData->getPrevId());
			if($objData->getStrName() == "") {
		        $objData = new class_modul_gallery_gallery($this->arrElementData["gallery_id"]);
		        $bitGalStart = true;
		    }

		    $arrTemplate["pathnavigation_point"] = getLinkPortal($this->getPagename(), "", "_self", $objData->getStrName(), "imageFolder", "", $objData->getSystemid());
   	        $strTemplateID = $this->objTemplate->readTemplate("/modul_gallery/".$this->arrElementData["gallery_template"], "pathnavigation_level");
	        $strReturn = $this->fillTemplate($arrTemplate, $strTemplateID). $strReturn;
       }


		return $strReturn;
	}

//---Helferfunktionen------------------------------------------------------------------------------------

	/**
	 * Determins the systemids of the previous / next image
	 *
	 * @return mixed
	 */
	private function getNextPrevIds() {
		$arrReturn = array();

		//Load all images on the current level
		$arrImagesLevel = class_modul_gallery_pic::loadFilesDB($this->getPrevId(), true, true);
		//Sort out the unallowed ones
		foreach($arrImagesLevel as $intKey => $objOneImage) {
			if(!$this->objRights->rightView($objOneImage->getSystemid()))
				unset($arrImagesLevel[$intKey]);
		}

		//make array-keys numeric
		$arrTemp = $arrImagesLevel;
		$arrImagesLevel = array();
		foreach ($arrTemp as $objOneElement)
		    $arrImagesLevel[] = $objOneElement;
		//Search the previous, current and next image
		// TODO: Solve this issue more elegant!!
		$strPrevious = "0";
		$strSuccessor = "0";
		$bitHit = false;
		$intKeyHit = 0;
		foreach ($arrImagesLevel as $intKey => $objOneImage) {
			if(!$bitHit) {
				if($objOneImage->getSystemid() == $this->getSystemid()) {
					$bitHit = true;
					$intKeyHit = $intKey;
				}
				else
					$strPrevious = $objOneImage->getSystemid();
			}
			else {
				$strSuccessor = $objOneImage->getSystemid();
				break;
			}
		}

		$arrReturn["forward_1"] = (isset($arrImagesLevel[$intKeyHit+1]) ? $arrImagesLevel[$intKeyHit+1]->getSystemid() : "");
		$arrReturn["forward_2"] = (isset($arrImagesLevel[$intKeyHit+2]) ? $arrImagesLevel[$intKeyHit+2]->getSystemid() : "");
		$arrReturn["forward_3"] = (isset($arrImagesLevel[$intKeyHit+3]) ? $arrImagesLevel[$intKeyHit+3]->getSystemid() : "");;

		$arrReturn["backward_1"] = (isset($arrImagesLevel[$intKeyHit-1]) ? $arrImagesLevel[$intKeyHit-1]->getSystemid() : "");;
		$arrReturn["backward_2"] = (isset($arrImagesLevel[$intKeyHit-2]) ? $arrImagesLevel[$intKeyHit-2]->getSystemid() : "");;
		$arrReturn["backward_3"] = (isset($arrImagesLevel[$intKeyHit-3]) ? $arrImagesLevel[$intKeyHit-3]->getSystemid() : "");;

		return $arrReturn;
	}


	/**
	 * Validates if the systemid requested is a valid element of the gallery-tree selected via the pageeelement
	 *
	 * @return bool
	 */
	private function checkIfRequestedIdIsInElementsTree() {
		$bitReturn = true;

        //check if requested systemid is part of the elements tree
        $objData = new class_modul_gallery_pic($this->getSystemid());
        $objGallery = new class_modul_gallery_gallery($this->arrElementData["gallery_id"]);

        //If the record is empty, try to load the gallery
        if($objData->getStrName() == "") {
            $objData = new class_modul_gallery_gallery($this->getSystemid());
        }

        while($objData->getPrevId() != "" && $objData->getPrevId() != "0" && $this->validateSystemid($objData->getPrevId())  && $objData->getSystemid() != $objGallery->getSystemid()) {
            $strBackupId = $objData->getPrevId();
            $objData = new class_modul_gallery_pic($objData->getPrevId());
            if($objData->getStrName() == "") {
                $objData = new class_modul_gallery_gallery($strBackupId);
            }

        }

        //if the requested systemid belong to the tree set in the pageelement, the systemids should match.
        if($objData->getSystemid() != $this->arrElementData["gallery_id"])
            $bitReturn = false;

		return $bitReturn;
	}

	/**
	 * Builds the rating bar available for every image-detailview.
	 * Creates the needed js-links and image-tags as defined by the template.
	 *
	 * @param float $floatRating
	 * @param string $strSystemid
	 * @param bool $bitRatingAllowed
	 * @return string
	 */
	private function buildRatingBar($floatRating, $strSystemid, $bitRatingAllowed = true, $bitPermissions = true) {
		$strIcons = "";
		$strRatingBarTitle = "";

		include_once(_systempath_."/class_modul_rating_rate.php");
		$intNumberOfIcons = class_modul_rating_rate::$intMaxRatingValue;

		//read the templates
		$strTemplateBarId = $this->objTemplate->readTemplate("/modul_gallery/".$this->arrElementData["gallery_template"], "rating_bar");

		if($bitRatingAllowed && $bitPermissions) {
			$strTemplateIconId = $this->objTemplate->readTemplate("/modul_gallery/".$this->arrElementData["gallery_template"], "rating_icon");

			for($intI = 1; $intI <= $intNumberOfIcons; $intI++) {
				$arrTemplate = array();
				$arrTemplate["rating_icon_number"] = $intI;

			    $arrTemplate["rating_icon_onclick"] = "kajonaRating('".$strSystemid."', '".$intI.".0', ".$intNumberOfIcons."); hideTooltip(); return false;";
       		    $arrTemplate["rating_icon_title"] = $this->getText("gallery_rating_rate1").$intI.$this->getText("gallery_rating_rate2");

				$strIcons .= $this->fillTemplate($arrTemplate, $strTemplateIconId);
			}
		} else {
		    if(!$bitRatingAllowed)
			    $strRatingBarTitle = $this->getText("gallery_rating_voted");
			else
			    $strRatingBarTitle = $this->getText("gallery_rating_permissions");
		}

		return $this->fillTemplate(array("rating_icons" => $strIcons, "rating_bar_title" => $strRatingBarTitle, "rating_rating" => $floatRating, "rating_ratingPercent" => ($floatRating/$intNumberOfIcons*100), "system_id" => $strSystemid, 2), $strTemplateBarId);
	}

}
?>