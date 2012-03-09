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

    private $arrImageTypes = array(".png", ".gif", ".jpg", ".jpeg");


	/**
	 * Constructor
	 *
	 * @param mixed $arrElementData
	 */
	public function __construct($arrElementData) {
		$this->setArrModuleEntry("moduleId", _mediamanager_module_id_);
		$this->setArrModuleEntry("modul", "mediamanager");

		parent::__construct($arrElementData);

        if($this->getAction() == "mediaFolder" || $this->getAction() == "imageFolder" || $this->getAction() == "openDlFolder")
            $this->setAction("list");

        if($this->getAction() == "detailImage" || $this->getAction() == "detailDownload")
            $this->setAction("fileDetails");


        if(isset($this->arrElementData["gallery_mode"]) && $this->arrElementData["gallery_mode"] == 1)
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
		if(!validateSystemid($this->getSystemid()) || !$this->checkIfRequestedIdIsInElementsTree()) {
		    $this->setSystemid($this->arrElementData["repo_id"]);
		}

		$bitPageview = false;
		//load using the pageview?
        $arrPagerContent = array();
		if($this->arrElementData["repo_elementsperpage"] > 0) {
		    $bitPageview = true;
            $objArraySectionIterator = new class_array_section_iterator(class_module_mediamanager_file::getFileCount($this->getSystemid(), false, true));
            $objArraySectionIterator->setIntElementsPerPage($this->arrElementData["repo_elementsperpage"]);
            $objArraySectionIterator->setPageNumber($this->getParam("pv"));
            $objArraySectionIterator->setArraySection(class_module_mediamanager_file::loadFilesDB($this->getSystemid(), false, true, $objArraySectionIterator->calculateStartPos(), $objArraySectionIterator->calculateEndPos()));

		    $arrPagerContent = $this->objToolkit->simplePager($objArraySectionIterator,
		                                              $this->getLang("commons_next"),
		                                              $this->getLang("commons_back"),
		                                              $this->getParam("action"),
		                                              $this->getPagename(),
		                                              "&systemid=".$this->getSystemid());
		    $arrFiles = $arrPagerContent["arrData"];
		}
		else {
		    //Load all Images & Folder
            $arrFiles = class_module_mediamanager_file::loadFilesDB($this->getSystemid(), false, true);
		}

		//Loop over every item and collect them
		$arrWrappingTemplate = array();
		$arrWrappingTemplate["systemid"] = $this->arrElementData["content_id"];

		$arrWrappingTemplate["folderlist"] = "";
		$arrWrappingTemplate["filelist"] = "";

		if(count($arrFiles) > 0) {
		    $intFileCounter = 0;

            $arrRemainingFiles = array();

            //calc number of images outside the loop
            $intNrOfFilesPerRow = $this->getFilesPerRow($this->arrElementData["repo_template"]);

			foreach ($arrFiles as $objOneFile) {
				//Check rights and the existance of placeholders
				if($intNrOfFilesPerRow > 0 && $objOneFile->rightView()) {
					//Folder or file?

                    //file
					if($objOneFile->getIntType() == class_module_mediamanager_file::$INT_TYPE_FILE) {
                        $arrFileTemplate = array();

                        //check, if it's an image
                        $strSuffix = uniStrtolower(uniSubstr($objOneFile->getStrFilename(), uniStrrpos($objOneFile->getStrFilename(), ".")));
                        if(in_array($strSuffix, $this->arrImageTypes)) {
                            //provide image placeholders
                            $arrFileTemplate["image_detail_src"]  = $this->generateImage($objOneFile->getStrFilename(), $this->arrElementData["gallery_maxh_d"], $this->arrElementData["gallery_maxw_d"], $this->arrElementData["gallery_text"], "10", $this->arrElementData["gallery_text_x"], $this->arrElementData["gallery_text_y"], "dejavusans.ttf", "255,255,255", $this->arrElementData["gallery_overlay"]);
                        }

                        $arrFileTemplate["file_name"] = $objOneFile->getStrName();
                        $arrFileTemplate["file_filename"] = $objOneFile->getStrFilename();
                        $arrFileTemplate["file_subtitle"] = $objOneFile->getStrSubtitle();
                        $arrFileTemplate["file_description"] = $objOneFile->getStrDescription();
                        $arrFileTemplate["file_size"] = bytesToString($objOneFile->getIntFileSize());
                        $arrFileTemplate["file_hits"] = $objOneFile->getIntHits();
                        $arrFileTemplate["file_lmtime"] = timeToString(filemtime(_realpath_.$objOneFile->getStrFilename()));
                        if(validateSystemid($objOneFile->getOwnerId())) {
                            $objUser = new class_module_user_user($objOneFile->getOwnerId());
                            $arrFileTemplate["file_owner"] = $objUser->getStrUsername();
                        }
                        $arrFileTemplate["file_link_href"] = _webpath_."/download.php?systemid=".$objOneFile->getSystemid();
                        $arrFileTemplate["file_link"] = "<a href=\""._webpath_."/download.php?systemid=".$objOneFile->getSystemid()."\">".$this->getLang("download_link")."</a>";


                        $arrFileTemplate["file_details_href"] = getLinkPortalHref($this->getPagename(), "", "fileDetails", "", $objOneFile->getSystemid(), $this->getPortalLanguage(), $objOneFile->getStrName());

                        //render the single file
                        $strTemplateID = $this->objTemplate->readTemplate("/module_mediamanager/".$this->arrElementData["repo_template"], "filelist_file");
                        $strCurrentImage = $this->objTemplate->fillTemplate($arrFileTemplate, $strTemplateID);
                        $arrRemainingFiles["file_".$intFileCounter % $intNrOfFilesPerRow] = $strCurrentImage;

                        //already rendered enough files?
                        if(count($arrRemainingFiles) == $intNrOfFilesPerRow) {
                            $strTemplateID = $this->objTemplate->readTemplate("/module_mediamanager/".$this->arrElementData["repo_template"], "filelist");
                            $arrWrappingTemplate["filelist"] .= $this->objTemplate->fillTemplate($arrRemainingFiles, $strTemplateID);
                            $arrRemainingFiles = array();
                        }

                        $intFileCounter++;

					}

                    //Folder
					if($objOneFile->getIntType() == class_module_mediamanager_file::$INT_TYPE_FOLDER) {
						$arrFolder = array();
						$arrFolder["folder_name"] = $objOneFile->getStrName();
						$arrFolder["folder_description"] = $objOneFile->getStrDescription();
						$arrFolder["folder_subtitle"] = $objOneFile->getStrSubtitle();
						$arrFolder["folder_href"] = getLinkPortalHref($this->getPagename(), "", "imageFolder", "", $objOneFile->getSystemid(), "", $objOneFile->getStrName());

                        $objFirstFile = $this->getFirstFileInFolder($objOneFile->getSystemid());
                        if($objFirstFile != null) {
                            $strSuffix = uniStrtolower(uniSubstr($objFirstFile->getStrFilename(), uniStrrpos($objFirstFile->getStrFilename(), ".")));
                            if(in_array($strSuffix, array(".jpg", ".jpeg", ".gif", ".png"))) {
                                //provide image placeholders
                                $arrFolder["folder_preview_image_src"] = $objFirstFile->getStrFilename();
                            }
                        }

						$strTemplateID = $this->objTemplate->readTemplate("/module_mediamanager/".$this->arrElementData["repo_template"], "folderlist");
						$arrWrappingTemplate["folderlist"] .= $this->objTemplate->fillTemplate($arrFolder, $strTemplateID, false);

					}
				}
			}
			//Print remaining files
			if(count($arrRemainingFiles) > 0) {
				$strTemplateID = $this->objTemplate->readTemplate("/module_mediamanager/".$this->arrElementData["repo_template"], "piclist");
				$arrWrappingTemplate["filelist"] .= $this->objTemplate->fillTemplate($arrRemainingFiles, $strTemplateID, false);
			}
		}
		else
			$strReturn = $this->getLang("commons_list_empty");

		//and load the sourrounding template
		if($bitPageview) {
		    $arrWrappingTemplate["link_forward"] = $arrPagerContent["strForward"];
            $arrWrappingTemplate["link_pages"] = $arrPagerContent["strPages"];
            $arrWrappingTemplate["link_back"] = $arrPagerContent["strBack"];
		}
		$strTemplateID = $this->objTemplate->readTemplate("/module_mediamanager/".$this->arrElementData["repo_template"], "list");
		$arrWrappingTemplate["pathnavigation"] = $this->generatePathnavi();
		$strReturn .= $this->fillTemplate($arrWrappingTemplate, $strTemplateID);

        $strReturn = $this->addPortaleditorCode($strReturn);
		return $strReturn;
	}




	/**
	 * Prints a file as a detailed-view
	 * and generates forward / backward links + a strip of prev / next files
	 *
     * @param bool $bitRegisterAdditionalTitle
	 * @return string
	 */
	protected function actionFileDetails($bitRegisterAdditionalTitle = true) {

        if(!$this->checkIfRequestedIdIsInElementsTree())
            return $this->actionList();

		//Load record
		$objFile = new class_module_mediamanager_file($this->getSystemid());

        //common fields
        $arrDetailsTemplate = array();
        $arrDetailsTemplate["file_name"] = $objFile->getStrName();
        $arrDetailsTemplate["file_description"] = $objFile->getStrDescription();
        $arrDetailsTemplate["file_subtitle"] = $objFile->getStrSubtitle();
        $arrDetailsTemplate["file_filename"] = $objFile->getStrFilename();
        $arrDetailsTemplate["file_size"] = bytesToString($objFile->getIntFileSize());
        $arrDetailsTemplate["file_hits"] = $objFile->getIntHits();
        $arrDetailsTemplate["file_systemid"] = $objFile->getSystemid();

        $arrDetailsTemplate["file_lmtime"] = timeToString(filemtime(_realpath_.$objFile->getStrFilename()));
        if(validateSystemid($objFile->getOwnerId())) {
            $objUser = new class_module_user_user($objFile->getOwnerId());
            $arrDetailsTemplate["file_owner"] = $objUser->getStrUsername();
        }
        $arrDetailsTemplate["file_link_href"] = _webpath_."/download.php?systemid=".$objFile->getSystemid();
        $arrDetailsTemplate["file_link"] = "<a href=\""._webpath_."/download.php?systemid=".$objFile->getSystemid()."\">".$this->getLang("download_link")."</a>";

        //if its am image, provide additional information
        $strSuffix = uniStrtolower(uniSubstr($objFile->getStrFilename(), uniStrrpos($objFile->getStrFilename(), ".")));
        if(in_array($strSuffix, $this->arrImageTypes)) {
            $arrDetailsTemplate["image_src"] = $this->generateImage($objFile->getStrFilename(), $this->arrElementData["gallery_maxh_d"], $this->arrElementData["gallery_maxw_d"], $this->arrElementData["gallery_text"], "10", $this->arrElementData["gallery_text_x"], $this->arrElementData["gallery_text_y"], "dejavusans.ttf", "255,255,255", $this->arrElementData["gallery_overlay"]);
        }

		$arrStripIds = $this->getNextPrevIds();
        $arrDetailsTemplate["backlink"]    = ($arrStripIds["backward_1"] != "" ? getLinkPortal($this->getPagename(), "", "",  $this->getLang("commons_back"), "fileDetails", "", $arrStripIds["backward_1"] ) : "" );
        $arrDetailsTemplate["forwardlink"] = ($arrStripIds["forward_1"] != "" ? getLinkPortal($this->getPagename(), "", "",  $this->getLang("commons_next"), "fileDetails", "", $arrStripIds["forward_1"] ) : "" );

        //next /prev 3 files
        for($intI = 1; $intI <= 3; $intI++) {
    		if($arrStripIds["forward_".$intI] != "") {
                $objCurFile = new class_module_mediamanager_file($arrStripIds["forward_".$intI]);
                $arrDetailsTemplate["forwardlink_".$intI] = $this->renderFileStripEntry($objCurFile);
            }

            if($arrStripIds["backward_".$intI] != "") {
                $objCurFile = new class_module_mediamanager_file($arrStripIds["backward_".$intI]);
                $arrDetailsTemplate["backlink_".$intI] = $this->renderFileStripEntry($objCurFile);
            }
        }

        //current file
        $arrDetailsTemplate["filestrip_current"] = $this->renderFileStripEntry($objFile);

        $arrDetailsTemplate["overview"] = getLinkPortal($this->getPagename(), "", "",  $this->getLang("overview"), "imageFolder", "", $objFile->getPrevId());
        $arrDetailsTemplate["pathnavigation"] = $this->generatePathnavi(true);

		//ratings available?
		if($objFile->getFloatRating() !== null) {
            $arrDetailsTemplate["file_rating"] = $this->buildRatingBar($objFile->getFloatRating(), $objFile->getIntRatingHits(), $objFile->getSystemid(), $objFile->isRateableByUser(), $objFile->rightRight2());
		}

        $strTemplateID = $this->objTemplate->readTemplate("/module_mediamanager/".$this->arrElementData["repo_template"], "filedetail");
		$strReturn = $this->fillTemplate($arrDetailsTemplate, $strTemplateID);

        //Add pe code
        $arrPeConfig = array(
			"pe_module" => "mediamanager",
			"pe_action_edit" => "editFile",
			"pe_action_edit_params" => "&systemid=".$objFile->getSystemid()
		);
        $strReturn = class_element_portal::addPortalEditorCode($strReturn, $objFile->getSystemid(), $arrPeConfig);

        //set the name of the current image to the page title via class_pages
        if($bitRegisterAdditionalTitle)
            class_module_pages_portal::registerAdditionalTitle($objFile->getStrName());

		//Update view counter
		$objFile->setIntHits($objFile->getIntHits()+1);
		$objFile->updateObjectToDb();

        return $this->addPortaleditorCode($strReturn);
	}


    /**
     * Renders a single elementn of the file-strip
     * @param class_module_mediamanager_file $objCurFile
     * @return string
     */
    private function renderFileStripEntry(class_module_mediamanager_file $objCurFile) {
        $arrTemplate = array(
            "file_detail_href" => getLinkPortalHref($this->getPagename(), "", "fileDetails", "", $objCurFile->getSystemid(), $this->getPortalLanguage(), $objCurFile->getStrName()),
            "file_name" => $objCurFile->getStrName(),
            "file_systemid" => $objCurFile->getStrSystemid(),
            "file_filename" => $objCurFile->getStrFilename()
        );
        $strStripTemplate = $this->objTemplate->readTemplate("/module_mediamanager/".$this->arrElementData["repo_template"], "filedetail_strip");
        return $this->objTemplate->fillTemplate($arrTemplate, $strStripTemplate);
    }


    /**
     * Selects a random file out of the selected repo and creates a detail-view
     *
     * @return string
     */
    protected function actionRandom() {
        //Fetch all images of the selected category
        $arrRandom = $this->loadFilesRecursive($this->arrElementData["repo_id"]);
        //Count files
        $intNumber = count($arrRandom)-1;
        //and a random number
        srand ((double)microtime()*1000000);
        $intRand = rand(0, $intNumber);
        //set the systemid as current
        if(isset($arrRandom[$intRand]))
            $this->setSystemid($arrRandom[$intRand]);
        //and load all
        $strReturn = $this->actionFileDetails(false);
        $strReturn = $this->addPortaleditorCode($strReturn);
        return $strReturn;
    }

    /**
     * Loads all images to find one randomly
     *
     * @param string $strStartID
     * @return mixed
     */
    private function loadFilesRecursive($strStartID) {
        $arrRandom = array();
        $arrCurrLevel = class_module_mediamanager_file::loadFilesDB($strStartID, false, true);
        if(count($arrCurrLevel) > 0) {
            foreach($arrCurrLevel as $objOneImage) {
                if($objOneImage->getIntType() == 0 && $objOneImage->rightView())
                    $arrRandom[] = $objOneImage->getSystemid();

                //Load all childs
                $arrTemp = $this->loadFilesRecursive($objOneImage->getSystemid());
                foreach ($arrTemp as $strOneTemp) {
                	$arrRandom[] = $strOneTemp;
                }
            }
        }
        return $arrRandom;
    }


    /**
     * Generates an image an returns the complete url. Uses caching!
     *
     * @param string $strImage
     * @param int $intHeight
     * @param int $intWidth
     * @param string $strText
     * @param int|string $intTextSize
     * @param int $intTextX
     * @param int $intTextY
     * @param string $strFont
     * @param string $strFontColor
     * @param string $strOverlayImage
     *
     * @return string the url
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
    				$floatRelation = $arrImageData[0] / $arrImageData[1]; //0 = width, 1 = height

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
		else
			$strReturn = "_webpath_".$strImage;

		return $strReturn;
	}


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

            $strTemplateID = $this->objTemplate->readTemplate("/module_mediamanager/".$this->arrElementData["repo_template"], "pathnavigation_level");
            $strReturn .= $this->fillTemplate($arrTemplate, $strTemplateID);

            while(!$objData instanceof class_module_mediamanager_repo) {
                $objData = class_objectfactory::getInstance()->getObject($objData->getPrevId());

                $arrTemplate["pathnavigation_point"] = getLinkPortal($this->getPagename(), "", "_self", $objData->getStrDisplayName(), "imageFolder", "", $objData->getSystemid());
                $strTemplateID = $this->objTemplate->readTemplate("/module_mediamanager/".$this->arrElementData["repo_template"], "pathnavigation_level");
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
    private function getFirstFileInFolder($strFolderId) {
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
	 * Determins the systemids of the previous / next file
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
	 * Validates if the systemid requested is a valid element of the mediamanager-tree selected via the pageeelement.
     * Avoids, that the element reacts on systemids not being present in the current tree.
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
     * @param bool $bitPermissions
     *
     * @return string
     * @todo adopt implementation as soon as ratings are back on
     */
	private function buildRatingBar($floatRating, $intRatings, $strSystemid, $bitRatingAllowed = true, $bitPermissions = true) {
		$strIcons = "";
		$strRatingBarTitle = "";

		$intNumberOfIcons = class_module_rating_rate::$intMaxRatingValue;

		//read the templates
		$strTemplateBarId = $this->objTemplate->readTemplate("/module_mediamanager/".$this->arrElementData["repo_template"], "rating_bar");

		if($bitRatingAllowed && $bitPermissions) {
			$strTemplateIconId = $this->objTemplate->readTemplate("/module_mediamanager/".$this->arrElementData["repo_template"], "rating_icon");

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
    private function getFilesPerRow($strTemplate) {

        $strTemplateID = $this->objTemplate->readTemplate("/module_mediamanager/".$strTemplate, "filelist");
        $arrElements = $this->objTemplate->getElements($strTemplateID);
        return count($arrElements);

    }
}
