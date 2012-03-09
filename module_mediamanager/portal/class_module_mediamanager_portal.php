<?php
/*"******************************************************************************************************
*   (c) 2007-2012 by Kajona, www.kajona.de                                                              *
*       Published under the GNU LGPL v2.1, see /system/licence_lgpl.txt                                 *
*-------------------------------------------------------------------------------------------------------*
*	$Id: class_carrier.php 4059 2011-08-09 14:52:41Z sidler $                                            *
********************************************************************************************************/

/**
 * Gallery Portal. Loads the thumbnails or detail-views
 *
 * @package module_mediamanager
 * @author sidler@mulchprod.de
 */
class class_module_mediamanager_portal extends class_portal implements interface_portal {

    public static $INT_MODE_GALLERY = 0;
    public static $INT_MODE_DOWNLOADS = 1;



	/**
	 * Constructor
	 *
	 * @param mixed $arrElementData
	 */
	public function __construct($arrElementData) {
		$this->setArrModuleEntry("moduleId", _mediamanager_module_id_);
		$this->setArrModuleEntry("modul", "mediamanager");

		parent::__construct($arrElementData);

        if($this->getAction() == "mediaFolder" || $this->getAction() == "imageFolder")
            $this->setAction("list");


        if($this->arrElementData["gallery_mode"] == 1)
            $this->setAction("random");

	}

    /**
     * Adds to code to enable to portaleditor
     *
     * @param string $strReturn
     * @return string
     */
	private function addPortaleditorCode($strReturn) {

        $arrPeConfig = array(
                              "pe_module" => "mediamanager",
                              "pe_action_edit" => "openFolder",
                              "pe_action_edit_params" => "&systemid=".$this->arrElementData["repo_id"],
                              "pe_action_new" => "",
                              "pe_action_new_params" => "",
                              "pe_action_delete" => "",
                              "pe_action_delete_params" => ""
                            );

        //open a subfolder?
        if($this->getParam("action") == "imageFolder" && validateSystemid($this->getSystemid()))
            $arrPeConfig["pe_action_edit_params"] = "&systemid=".$this->getSystemid();

        $strReturn = class_element_portal::addPortalEditorCode($strReturn, $this->arrElementData["repo_id"], $arrPeConfig);

		return $strReturn;

	}


	/**
	 * Creates a list of thumbnails
	 *
	 * @return string
     * @permissions view
	 */
	protected function actionList() {
		$strReturn = "";

		//Determin the prev_id to load
		if(!validateSystemid($this->getSystemid()) || $this->getParam("action") != "imageFolder" || !$this->checkIfRequestedIdIsInElementsTree()) {
		    $this->setSystemid($this->arrElementData["repo_id"]);
		}

		$bitPageview = false;
		//load using the pageview?
        $arrTempImages = array();
        $arrImages = array();
		if($this->arrElementData["gallery_imagesperpage"] > 0) {
		    $bitPageview = true;
            $objArraySectionIterator = new class_array_section_iterator(class_module_mediamanager_file::getFileCount($this->getSystemid(), false, true));
            $objArraySectionIterator->setIntElementsPerPage($this->arrElementData["gallery_imagesperpage"]);
            $objArraySectionIterator->setPageNumber((int)($this->getParam("pv") != "" ? $this->getParam("pv") : 1));
            $objArraySectionIterator->setArraySection(class_module_mediamanager_file::loadFilesDB($this->getSystemid(), false, true, $objArraySectionIterator->calculateStartPos(), $objArraySectionIterator->calculateEndPos()));

		    $arrTempImages = $this->objToolkit->simplePager($objArraySectionIterator,
		                                              $this->getLang("commons_next"),
		                                              $this->getLang("commons_back"),
		                                              $this->getParam("action"),
		                                              $this->getPagename(),
		                                              "&systemid=".$this->getSystemid());
		    $arrImages = $arrTempImages["arrData"];
		}
		else {
		    //Load all Images & Folder
            $arrImages = class_module_mediamanager_file::loadFilesDB($this->getSystemid(), false, true);
		}

		//Loop over every item and collect them
		$arrTemplate = array();
		$arrTemplate["systemid"] = $this->arrElementData["content_id"];
		$arrTemplate["folderlist"] = "";
		$arrTemplate["piclist"] = "";

		if(count($arrImages) > 0) {
		    $intImageCounter = 0;

            $arrRemainingImages = array();

            //calc number of images outside the loop
            $intNrOfPicsPerRow = $this->getImagesPerRow($this->arrElementData["gallery_template"]);

			foreach ($arrImages as $objOneImage) {
				//Check rights and the existance of placeholders
				if($intNrOfPicsPerRow > 0 && $objOneImage->rightView()) {
					//Folder or image?
					if($objOneImage->getIntType() == 0) {
                        $arrTemplateImage = array();

                        //create the template-entries for the single image
                        $arrTemplateImage["pic"] = $this->generateImage($objOneImage->getStrFilename(), $this->arrElementData["gallery_maxh_p"], $this->arrElementData["gallery_maxw_p"]);
                        $arrTemplateImage["pic_href"] = getLinkPortalHref($this->getPagename(), "", "detailImage", "", $objOneImage->getSystemid(), "", $objOneImage->getStrName());
                        $arrTemplateImage["name"] = $objOneImage->getStrName();
                        $arrTemplateImage["subtitle"] = $objOneImage->getStrSubtitle();
                        $arrTemplateImage["pic_detail"]  = $this->generateImage($objOneImage->getStrFilename(), $this->arrElementData["gallery_maxh_d"], $this->arrElementData["gallery_maxw_d"], $this->arrElementData["gallery_text"], "10", $this->arrElementData["gallery_text_x"], $this->arrElementData["gallery_text_y"], "dejavusans.ttf", "255,255,255", $this->arrElementData["gallery_overlay"]);
                        $arrTemplateImage["pic_description"] = $objOneImage->getStrDescription();
				        $arrTemplateImage["pic_size"] = $objOneImage->getIntFileSize();
				        $arrTemplateImage["pic_hits"] = $objOneImage->getIntHits();

                        //render the single image
                        $strTemplateID = $this->objTemplate->readTemplate("/module_mediamanager/".$this->arrElementData["gallery_template"], "piclist_pic");
                        $strCurrentImage = $this->objTemplate->fillTemplate($arrTemplateImage, $strTemplateID);

                        $arrRemainingImages["pic_".$intImageCounter % $intNrOfPicsPerRow] = $strCurrentImage;

                        //already rendered enough images?
                        if(count($arrRemainingImages) == $intNrOfPicsPerRow) {
                            $strTemplateID = $this->objTemplate->readTemplate("/module_mediamanager/".$this->arrElementData["gallery_template"], "piclist");
                            $arrTemplate["piclist"] .= $this->objTemplate->fillTemplate($arrRemainingImages, $strTemplateID);
                            $arrRemainingImages = array();
                        }

                        $intImageCounter++;

					}

					if($objOneImage->getIntType() == 1) {
						//Folder
						$arrFolder = array();
						$arrFolder["folder_name"] = $objOneImage->getStrName();
						$arrFolder["folder_description"] = $objOneImage->getStrDescription();
						$arrFolder["folder_subtitle"] = $objOneImage->getStrSubtitle();
						$arrFolder["folder_link"] = getLinkPortal($this->getPagename(), "", "_self",  $this->getLang("galerie_ordner_link"), "imageFolder", "", $objOneImage->getSystemid(), "", "", $objOneImage->getStrName());
						$arrFolder["folder_href"] = getLinkPortalHref($this->getPagename(), "", "imageFolder", "", $objOneImage->getSystemid(), "", $objOneImage->getStrName());

                        $objFirstImage = $this->getFirstImageInFolder($objOneImage->getSystemid());
                        if($objFirstImage != null) {
                            $arrFolder["folder_preview"] = $this->generateImage($objFirstImage->getStrFilename(), $this->arrElementData["gallery_maxh_p"], $this->arrElementData["gallery_maxw_p"]);
                        }

						$strTemplateID = $this->objTemplate->readTemplate("/module_mediamanager/".$this->arrElementData["gallery_template"], "folderlist");
						$arrTemplate["folderlist"] .= $this->objTemplate->fillTemplate($arrFolder, $strTemplateID, false);

					}
				}
			}
			//Print remaining images
			if(count($arrRemainingImages) > 0) {
				$strTemplateID = $this->objTemplate->readTemplate("/module_mediamanager/".$this->arrElementData["gallery_template"], "piclist");
				$arrTemplate["piclist"] .= $this->objTemplate->fillTemplate($arrRemainingImages, $strTemplateID, false);
				$arrRemainingImages= array();
			}
		}
		else
			$strReturn = $this->getLang("liste_leer");

		//and load the sourrounding template
		if($bitPageview) {
		    $arrTemplate["link_forward"] = $arrTempImages["strForward"];
            $arrTemplate["link_pages"] = $arrTempImages["strPages"];
            $arrTemplate["link_back"] = $arrTempImages["strBack"];
		}
		$strTemplateID = $this->objTemplate->readTemplate("/module_mediamanager/".$this->arrElementData["gallery_template"], "list");
		$arrTemplate["pathnavigation"] = $this->generatePathnavi();
		$strReturn .= $this->fillTemplate($arrTemplate, $strTemplateID);

        $strReturn = $this->addPortaleditorCode($strReturn);
		return $strReturn;
	}

    //---Detailfunktionen------------------------------------------------------------------------------------

	/**
	 * Prints a image as a detailed-view
	 * and generates forward / backward links
	 *
     * @param bool $bitRegisterAdditionalTitle
	 * @return string
	 */
	protected function actionDetailImage($bitRegisterAdditionalTitle = true) {

        if(!$this->checkIfRequestedIdIsInElementsTree())
            return $this->actionList();


		$strReturn = "";
		//Load record
		$objImage = new class_module_mediamanager_file($this->getSystemid());
		$arrImage = $this->getNextPrevIds();

		//Load template
		$strTemplateID = $this->objTemplate->readTemplate("/module_mediamanager/".$this->arrElementData["gallery_template"], "picdetail");
		//Collect Data
		$arrImage["pic_url"] = $this->generateImage($objImage->getStrFilename(), $this->arrElementData["gallery_maxh_d"], $this->arrElementData["gallery_maxw_d"], $this->arrElementData["gallery_text"], "10", $this->arrElementData["gallery_text_x"], $this->arrElementData["gallery_text_y"], "dejavusans.ttf", "255,255,255", $this->arrElementData["gallery_overlay"]);

		//previous 3 images
		$arrImage["backlink"] = ($arrImage["backward_1"] != "" ? getLinkPortal($this->getPagename(), "", "",  $this->getLang("commons_back"), "detailImage", "", $arrImage["backward_1"] ) : "" );
        for($intI = 1; $intI <= 3; $intI++) {
    		if($arrImage["backward_".$intI] != "") {
                $objImageBack = new class_module_mediamanager_file($arrImage["backward_".$intI]);
                $arrImage["backlink_image_".$intI] = getLinkPortal($this->getPagename(), "", "", "<img src=\"image.php?image=".$objImageBack->getStrFilename()."&amp;maxWidth=".$this->arrElementData["gallery_maxw_m"]."&amp;maxHeight=".$this->arrElementData["gallery_maxh_m"]."\" border=\"0\"/>", "detailImage", "", $objImageBack->getSystemid(), "", "", $objImageBack->getStrName());
                $arrImage["backlink_image_filename_".$intI] = urlencode($objImageBack->getStrFilename());
                $arrImage["backlink_image_systemid_".$intI] = $objImageBack->getSystemid();
            }
        }

        //next 3 images
        $arrImage["forwardlink"] = ($arrImage["forward_1"] != "" ? getLinkPortal($this->getPagename(), "", "",  $this->getLang("commons_next"), "detailImage", "", $arrImage["forward_1"] ) : "" );
        for($intI = 1; $intI <= 3; $intI++) {
    		if($arrImage["forward_".$intI] != "") {
                $objImageFwd = new class_module_mediamanager_file($arrImage["forward_".$intI]);
                $arrImage["forwardlink_image_".$intI] = getLinkPortal($this->getPagename(), "", "", "<img src=\"image.php?image=".$objImageFwd->getStrFilename()."&amp;maxWidth=".$this->arrElementData["gallery_maxw_m"]."&amp;maxHeight=".$this->arrElementData["gallery_maxh_m"]."\" border=\"0\"/>", "detailImage", "", $objImageFwd->getSystemid(), "", "", $objImageFwd->getStrName());
                $arrImage["forwardlink_image_filename_".$intI] = urlencode($objImageFwd->getStrFilename());
                $arrImage["forwardlink_image_systemid_".$intI] = $objImageFwd->getSystemid();
            }
        }

        //current image
        $arrImage["pic_small"] = getLinkPortal($this->getPagename(), "", "", "<img src=\"image.php?image=".$objImage->getStrFilename()."&amp;maxWidth=".$this->arrElementData["gallery_maxw_m"]."&amp;maxHeight=".$this->arrElementData["gallery_maxh_m"]."\" border=\"0\"/>", "detailImage", "", $objImage->getSystemid(), "currentPic");

		$arrImage["overview"] = getLinkPortal($this->getPagename(), "", "",  $this->getLang("overview"), "imageFolder", "", $objImage->getPrevId());
		$arrImage["pathnavigation"] = $this->generatePathnavi(true);
		$arrImage["systemid"] = $this->getSystemid();
		$arrImage["pic_name"] = $objImage->getStrName();
		$arrImage["pic_description"] = $objImage->getStrDescription();
        $arrImage["pic_subtitle"] = $objImage->getStrSubtitle();
		$arrImage["pic_filename"] = $objImage->getStrFilename();
		$arrImage["pic_size"] = bytesToString($objImage->getIntFileSize());
		$arrImage["pic_hits"] = $objImage->getIntHits();

		//ratings available?
		if($objImage->getFloatRating() !== null) {
		    $arrImage["pic_rating"] = $this->buildRatingBar($objImage->getFloatRating(), $objImage->getIntRatingHits(), $objImage->getSystemid(), $objImage->isRateableByUser(), $objImage->rightRight2());
		}

		$strReturn = $this->fillTemplate($arrImage, $strTemplateID);

        //Add pe code
        $arrPeConfig = array(
			"pe_module" => "gallery",
			"pe_action_edit" => "editImage",
			"pe_action_edit_params" => "&systemid=".$objImage->getSystemid()
		);
        $strReturn = class_element_portal::addPortalEditorCode($strReturn, $objImage->getSystemid(), $arrPeConfig);

        //set the name of the current image to the page title via class_pages
        if($bitRegisterAdditionalTitle)
            class_module_pages_portal::registerAdditionalTitle($objImage->getStrName());

		//Update view counter
		$objImage->setIntHits($objImage->getIntHits()+1);
		$objImage->updateObjectToDb(false);

        $strReturn = $this->addPortaleditorCode($strReturn);
		return $strReturn;
	}

    //---Random----------------------------------------------------------------------------------------------

    /**
     * Selects a random image out of the selected gallery and creates a detail-view
     *
     * @return string
     */
    protected function actionRandom() {
        //Fetch all images of the selected category
        $arrRandom = array();
        $arrRandom = $this->loadImagesRecursive($this->arrElementData["repo_id"]);

        //Count images
        $intNumber = count($arrRandom)-1;
        //and a random number
        srand ((double)microtime()*1000000);
        $intRand = rand(0, $intNumber);
        //set the systemid as current
        if(isset($arrRandom[$intRand]))
            $this->setSystemid($arrRandom[$intRand]);
        //and load all
        $strReturn = $this->actionDetailImage(false);
        $strReturn = $this->addPortaleditorCode($strReturn);
        return $strReturn;
    }

    /**
     * Loads all images to find one randomly
     *
     * @param string $strStartID
     * @return mixed
     */
    private function loadImagesRecursive($strStartID) {
        $arrRandom = array();
        $arrCurrLevel = class_module_mediamanager_file::loadFilesDB($strStartID, false, true);
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
     * @param string $strOverlayImage
	 * @return string the complete html-img tag
	 */
	private function generateImage($strImage, $intHeight, $intWidth, $strText = "", $intTextSize = "20", $intTextX = 20, $intTextY= 20, $strFont = "dejavusans.ttf", $strFontColor= "255,255,255", $strOverlayImage = "") {
	    $strReturn = "No image defined!!";
		$intWidthNew = 0;
		$intHeightNew = 0;
		if(is_file(_realpath_.$strImage)) {
			//If theres text to put over the image, manipulate image "inline",
			//otherwise let the work do image.php -> kinda multithreading ;)
			if($strText == "") {
				$strReturn = "image.php?image=".urlencode($strImage)."&amp;maxWidth=".$intWidth."&amp;maxHeight=".$intHeight;
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

				$objImage = new class_image($strText.$strOverlayImage);
				//Edit Picture
				if($objImage->preLoadImage($strImage)) {
					//resize the image
					if($bitResize)
					    $objImage->resizeImage($intWidthNew, $intHeightNew, 0, true);
					//Inlay text
					if($strText != "")
						$objImage->imageText($strText, $intTextX, $intTextY, $intTextSize, $strFontColor, $strFont, true);
                    //overlay image
                    if($strOverlayImage != "")
                        $objImage->overlayImage($strOverlayImage, $intTextX, $intTextY, true);

					$objImage->saveImage("", true);
					$strImageName = $objImage->getCachename();
					$strReturn = "_webpath_"._images_cachepath_.$strImageName;
					//and release memory
					$objImage->releaseResources();
				}
			    else
				    $strReturn = "Error manipulating image!";
			}
		}
		else 	//Nichts zu tun, Bild so ausgeben
			$strReturn = "_webpath_".$strImage;

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
		$objData = class_objectfactory::getInstance()->getObject($this->getSystemid());
		$objGallery = new class_module_mediamanager_repo($this->arrElementData["repo_id"]);

        if($objGallery->rightView()) {

            $arrTemplate = array();
            //Name and link
            if($bitCurrentViewIsDetail)
                $arrTemplate["pathnavigation_point"] = getLinkPortal($this->getPagename(), "", "_self", $objData->getStrDisplayName(), "detailImage", "", $objData->getSystemid(), "", "", $objData->getStrDisplayName());
            else
                $arrTemplate["pathnavigation_point"] = getLinkPortal($this->getPagename(), "", "_self", $objData->getStrDisplayName(), "imageFolder", "", $objData->getSystemid(), "", "", $objData->getStrDisplayName());

            $strTemplateID = $this->objTemplate->readTemplate("/module_mediamanager/".$this->arrElementData["gallery_template"], "pathnavigation_level");
            $strReturn .= $this->fillTemplate($arrTemplate, $strTemplateID);

            while(!$objData instanceof class_module_mediamanager_repo) {
                $objData = class_objectfactory::getInstance()->getObject($objData->getPrevId());

                $arrTemplate["pathnavigation_point"] = getLinkPortal($this->getPagename(), "", "_self", $objData->getStrDisplayName(), "imageFolder", "", $objData->getSystemid());
                $strTemplateID = $this->objTemplate->readTemplate("/module_mediamanager/".$this->arrElementData["gallery_template"], "pathnavigation_level");
                $strReturn = $this->fillTemplate($arrTemplate, $strTemplateID). $strReturn;
            }

        }

		return $strReturn;
	}



    /**
     * Tries to load the fist image under the passed systemid.
     * If available, the instance is returned, otherwise null
     *
     * @param string $strFolderId
     * @return class_module_mediamanager_file
     */
    private function getFirstImageInFolder($strFolderId) {
        //load the files in the passed folder
        $arrSubLevel = class_module_mediamanager_file::loadFilesDB($strFolderId, false, true);
        if(count($arrSubLevel) > 0) {
            foreach($arrSubLevel as $objOneImage) {
                if($objOneImage->getIntType() == class_module_mediamanager_file::$INT_TYPE_FILE && $objOneImage->rightView()) {
                    return $objOneImage;
                }
            }
        }

        return null;
    }

	/**
	 * Determins the systemids of the previous / next image
	 *
	 * @return mixed
	 */
	private function getNextPrevIds() {
		$arrReturn = array();

		//Load all images on the current level
        $objCur = class_objectfactory::getInstance()->getObject($this->getSystemid());
		$arrImagesLevel = class_module_mediamanager_file::loadFilesDB($objCur->getPrevId(), class_module_mediamanager_file::$INT_TYPE_FILE, true);
		//Sort out the unallowed ones
		foreach($arrImagesLevel as $intKey => $objOneImage) {
			if(!$objOneImage->rightView())
				unset($arrImagesLevel[$intKey]);
		}

		//make array-keys numeric
		$arrTemp = $arrImagesLevel;
		$arrImagesLevel = array();
		foreach ($arrTemp as $objOneElement)
		    $arrImagesLevel[] = $objOneElement;
		//Search the previous, current and next image
		$bitHit = false;
		$intKeyHit = 0;
		foreach ($arrImagesLevel as $intKey => $objOneImage) {
			if(!$bitHit) {
				if($objOneImage->getSystemid() == $this->getSystemid()) {
					$bitHit = true;
					$intKeyHit = $intKey;
				}
			}
			else {
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
        $objData = class_objectfactory::getInstance()->getObject($this->getSystemid());
        $objGallery = new class_module_mediamanager_repo($this->arrElementData["repo_id"]);

        while(!$objData instanceof class_module_mediamanager_repo)
            $objData = class_objectfactory::getInstance()->getObject($objData->getPrevId());

        //if the requested systemid belong to the tree set in the pageelement, the systemids should match.
        if($objData->getSystemid() != $this->arrElementData["repo_id"])
            $bitReturn = false;

		return $bitReturn;
	}

	/**
	 * Builds the rating bar available for every image-detailview.
	 * Creates the needed js-links and image-tags as defined by the template.
	 *
	 * @param float $floatRating
     * @param int $intRatings
	 * @param string $strSystemid
	 * @param bool $bitRatingAllowed
	 * @return string
	 */
	private function buildRatingBar($floatRating, $intRatings, $strSystemid, $bitRatingAllowed = true, $bitPermissions = true) {
		$strIcons = "";
		$strRatingBarTitle = "";

		$intNumberOfIcons = class_module_rating_rate::$intMaxRatingValue;

		//read the templates
		$strTemplateBarId = $this->objTemplate->readTemplate("/module_mediamanager/".$this->arrElementData["gallery_template"], "rating_bar");

		if($bitRatingAllowed && $bitPermissions) {
			$strTemplateIconId = $this->objTemplate->readTemplate("/module_mediamanager/".$this->arrElementData["gallery_template"], "rating_icon");

			for($intI = 1; $intI <= $intNumberOfIcons; $intI++) {
				$arrTemplate = array();
				$arrTemplate["rating_icon_number"] = $intI;

			    $arrTemplate["rating_icon_onclick"] = "KAJONA.portal.rating.rate('".$strSystemid."', '".$intI.".0', ".$intNumberOfIcons."); return false;";
       		    $arrTemplate["rating_icon_title"] = $this->getLang("gallery_rating_rate1").$intI.$this->getLang("gallery_rating_rate2");

				$strIcons .= $this->fillTemplate($arrTemplate, $strTemplateIconId);
			}
		} else {
		    if(!$bitRatingAllowed)
			    $strRatingBarTitle = $this->getLang("gallery_rating_voted");
			else
			    $strRatingBarTitle = $this->getLang("commons_error_permissions");
		}

		return $this->fillTemplate(array("rating_icons" => $strIcons, "rating_bar_title" => $strRatingBarTitle,
                                         "rating_rating" => $floatRating, "rating_hits" => $intRatings,
                                         "rating_ratingPercent" => ($floatRating/$intNumberOfIcons*100),
                                         "system_id" => $strSystemid, 2), $strTemplateBarId);
	}

    /**
     * Calculates the number of images per row as defined in the template.
     *
     * @param string $strTemplate
     * @return int
     */
    private function getImagesPerRow($strTemplate) {

        $strTemplateID = $this->objTemplate->readTemplate("/module_mediamanager/".$strTemplate, "piclist");
        $arrElements = $this->objTemplate->getElements($strTemplateID);
        return count($arrElements);

    }
}
