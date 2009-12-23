<?php
/*"******************************************************************************************************
*   (c) 2004-2006 by MulchProductions, www.mulchprod.de                                                 *
*   (c) 2007-2009 by Kajona, www.kajona.de                                                              *
*       Published under the GNU LGPL v2.1, see /system/licence_lgpl.txt                                 *
*-------------------------------------------------------------------------------------------------------*
*	$Id$                             *
********************************************************************************************************/

/**
 * Downloads Portal. Generates a list of available downloads
 *
 * @package modul_downloads
 */
class class_modul_downloads_portal extends class_portal implements interface_portal {
	/**
	 * Constructor
	 *
	 * @param mixed $arrElementData
	 */
	public function __construct($arrElementData) {
        $arrModul = array();
		$arrModul["name"] 				= "modul_downloads";
		$arrModul["author"] 			= "sidler@mulchprod.de";
		$arrModul["table2"] 			= _dbprefix_."downloads_file";
		$arrModul["table"] 			    = _dbprefix_."downloads_archive";
		$arrModul["table3"] 			= _dbprefix_."downloads_logs";
		$arrModul["moduleId"] 			= _downloads_modul_id_;
		$arrModul["modul"] 			    = "downloads";
        
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

        if($strAction == "detailDownload")
            $strReturn = $this->actionDetailDownload();
        else
            $strReturn = $this->actionList();

		return $strReturn;

	}

//---Aktionsfunktionen-----------------------------------------------------------------------------------

	/**
	 * Creates a list of files & folders
	 *
	 * @return string
	 */
	public function actionList() {
		$strReturn = "";

		//systemid passed?
		if( !validateSystemid($this->getSystemid() ) || $this->getAction() != "openDlFolder" || ! $this->checkSystemidBelongsToCurrentTree() ) {
            if(isset($this->arrElementData["download_id"]))
                $this->setSystemid($this->arrElementData["download_id"]);
		}

        if(!isset($this->arrElementData["download_amount"]) || $this->arrElementData["download_amount"] == "" || (int)$this->arrElementData["download_amount"] < 1)
            $this->arrElementData["download_amount"] = 99999;

        //Load all files
	    $objArraySectionIterator = new class_array_section_iterator($this->getNumberOfEntriesOnLevel());
	    $objArraySectionIterator->setIntElementsPerPage($this->arrElementData["download_amount"]);
	    $objArraySectionIterator->setPageNumber((int)($this->getParam("pv") != "" ? $this->getParam("pv") : 1));
	    $objArraySectionIterator->setArraySection($this->getArrFiles($objArraySectionIterator->calculateStartPos(), $objArraySectionIterator->calculateEndPos()));

	    $arrObjects = $objArraySectionIterator->getArrayExtended();

		$arrObjects = $this->objToolkit->pager($this->arrElementData["download_amount"], ($this->getParam("pv") != "" ? $this->getParam("pv") : 1), $this->getText("weiter"), $this->getText("zurueck"), "", ($this->getParam("page") != "" ? $this->getParam("page") : ""), $arrObjects);




       // $arrObjects = $this->getArrFiles();

		if(count($arrObjects["arrData"]) > 0) {
			$strFileList = "";
			$strFolderList = "";
			foreach($arrObjects["arrData"] as $objOneFile) {
				//check rights
				if($this->objRights->rightView($objOneFile->getSystemid())) {
					$arrTemplate = array();
					//Folder or file?
					if($objOneFile->getType() == 0) {
                        //File
						$strTemplateID = $this->objTemplate->readTemplate("/modul_downloads/".$this->arrElementData["download_template"], "file");
						$arrTemplate["file_name"] = $objOneFile->getName();
						$arrTemplate["file_description"] = $objOneFile->getDescription()."";
						$arrTemplate["file_hits"] = $objOneFile->getHits();
						$arrTemplate["file_size"] = bytesToString($objOneFile->getSize());
                        $arrTemplate["file_detail_href"] = getLinkPortalHref($this->getPagename(), "", "detailDownload", "", $objOneFile->getSystemid(), "", $objOneFile->getName());
						//ratings available?
						if($objOneFile->getFloatRating() !== null) {
						    $arrTemplate["file_rating"] = $this->buildRatingBar($objOneFile->getFloatRating(), $objOneFile->getSystemid(), $objOneFile->isRateableByUser(), $objOneFile->rightRight4());
						}

						//could we get a preview (e.g. if its an image)?
						$strSuffix = uniSubstr($objOneFile->getFilename(), uniStrrpos($objOneFile->getFilename(), "."));
						if($strSuffix == ".jpg" || $strSuffix == ".gif" || $strSuffix == ".png") {
                            $strPreviewTemplateID = $this->objTemplate->readTemplate("/modul_downloads/".$this->arrElementData["download_template"], "img_preview");
                            $arrTemplate["file_preview"] = $this->objTemplate->fillTemplate(array("img_filename" => urlencode($objOneFile->getFilename()) ), $strPreviewTemplateID);
                        }

                        //screenshot 1 given? -> provide a preview for the list
                        if($objOneFile->getStrScreen1() != "") {
                            $strPreviewTemplateID = $this->objTemplate->readTemplate("/modul_downloads/".$this->arrElementData["download_template"], "img_preview");
                            $arrTemplate["file_screen_1"] = $this->objTemplate->fillTemplate(array("img_filename" => urlencode($objOneFile->getStrScreen1()) ), $strPreviewTemplateID);
                        }


						//Right to download?
						if($this->objRights->rightRight2($objOneFile->getSystemid())) {
							$arrTemplate["file_link"] = "<a href=\""._webpath_."/download.php?systemid=".$objOneFile->getSystemid()."\">".$this->getText("download_datei_link")."</a>";
							$arrTemplate["file_href"] = ""._webpath_."/download.php?systemid=".$objOneFile->getSystemid()."";
						}
						else {
							$arrTemplate["file_link"] = $this->getText("download_datei_link");
							$arrTemplate["file_href"] = "";
						}

						$strFileList .= $this->objTemplate->fillTemplate($arrTemplate, $strTemplateID, false);
					}
					elseif ($objOneFile->getType() == 1) {
					    //Folder
						$strTemplateID = $this->objTemplate->readTemplate("/modul_downloads/".$this->arrElementData["download_template"], "folder");
						$arrTemplate["folder_name"] = $objOneFile->getName();
						$arrTemplate["folder_description"] = $objOneFile->getDescription()."";
						$arrTemplate["folder_link"] = getLinkPortal($this->getPagename(),  "", "_self", $this->getText("download_ordner_link"), "openDlFolder", "", $objOneFile->getSystemid(), "", "", $objOneFile->getName());
						$arrTemplate["folder_href"] = getLinkPortalHref($this->getPagename(), "","openDlFolder", "", $objOneFile->getSystemid(), "", $objOneFile->getName());
						$strFolderList .= $this->objTemplate->fillTemplate($arrTemplate, $strTemplateID, false);
					}
				}
			}

			//the sourrounding template
			$strTemplateID = $this->objTemplate->readTemplate("/modul_downloads/".$this->arrElementData["download_template"], "list");
			$arrTemplate = array();
			$arrTemplate["folderlist"] = $strFolderList;
			$arrTemplate["filelist"] = $strFileList;
			$arrTemplate["pathnavigation"] = $this->generatePathnavi();
            $arrTemplate["link_forward"] = $arrObjects["strForward"];
            $arrTemplate["link_pages"] = $arrObjects["strPages"];
            $arrTemplate["link_back"] = $arrObjects["strBack"];

			$strReturn .= $this->fillTemplate($arrTemplate, $strTemplateID);
		}
		else {
			$strTemplateID = $this->objTemplate->readTemplate("/modul_downloads/".$this->arrElementData["download_template"], "list");
			$arrTemplate = array();
			$arrTemplate["filelist"] = $this->getText("liste_leer");
			$arrTemplate["pathnavigation"] = $this->generatePathnavi();
			$strReturn .= $this->fillTemplate($arrTemplate, $strTemplateID);
		}

		return $strReturn;
	}


    /**
	 * Prints a image as a detailed-view
	 * and generates forward / backward links
	 *
	 * @return string
	 */
	private function actionDetailDownload() {
		$strReturn = "";
		//Load record
		$objFile = new class_modul_downloads_file($this->getSystemid());

		//Load template
		$strTemplateID = $this->objTemplate->readTemplate("/modul_downloads/".$this->arrElementData["download_template"], "filedetail");
        $arrFile = array();
		$arrFile["pathnavigation"] = $this->generatePathnavi(true);
		$arrFile["systemid"] = $this->getSystemid();
        $arrFile["file_name"] = $objFile->getName();
        $arrFile["file_description"] = $objFile->getDescription();
        $arrFile["file_filename"] = $objFile->getFilename();
        $arrFile["file_size"] = bytesToString($objFile->getSize());
        $arrFile["file_hits"] = $objFile->getHits();
        $arrFile["file_lmtime"] = timeToString($objFile->getEditDate());

        //Right to download?
        if($this->objRights->rightRight2($objFile->getSystemid())) {
            $arrFile["file_link"] = "<a href=\""._webpath_."/download.php?systemid=".$objFile->getSystemid()."\">".$this->getText("download_datei_link")."</a>";
            $arrFile["file_href"] = ""._webpath_."/download.php?systemid=".$objFile->getSystemid()."";
        }
        else {
            $arrFile["file_link"] = $this->getText("download_datei_link");
            $arrFile["file_href"] = "";
        }

        //could we get a preview (e.g. if its an image)?
        $strSuffix = uniSubstr($objFile->getFilename(), uniStrrpos($objFile->getFilename(), "."));
        if($strSuffix == ".jpg" || $strSuffix == ".gif" || $strSuffix == ".png") {
            $strPreviewTemplateID = $this->objTemplate->readTemplate("/modul_downloads/".$this->arrElementData["download_template"], "img_preview");
            $arrFile["file_preview"] = $this->objTemplate->fillTemplate(array("img_filename" => urlencode($objFile->getFilename()) ), $strPreviewTemplateID);
        }


		//ratings available?
		if($objFile->getFloatRating() !== null) {
		    $arrFile["file_rating"] = $this->buildRatingBar($objFile->getFloatRating(), $objFile->getSystemid(), $objFile->isRateableByUser(), $objFile->rightRight2());
		}

        //screenshots available? undocumented feature!
        if($objFile->getStrScreen1() != "") {
            $strPreviewTemplateID = $this->objTemplate->readTemplate("/modul_downloads/".$this->arrElementData["download_template"], "img_screenshot_list");
            $arrFile["file_screen_1"] = $this->objTemplate->fillTemplate(array("img_title" => "", "img_filename" => urlencode($objFile->getStrScreen1()) ), $strPreviewTemplateID);

            //provide a detailed view, too
            $strPreviewTemplateID = $this->objTemplate->readTemplate("/modul_downloads/".$this->arrElementData["download_template"], "img_screenshot_detail");
            $arrFile["file_screen_detail"] = $this->objTemplate->fillTemplate(array("img_filename" => urlencode($objFile->getStrScreen1()) ), $strPreviewTemplateID);
        }
        if($objFile->getStrScreen2() != "") {
            $strPreviewTemplateID = $this->objTemplate->readTemplate("/modul_downloads/".$this->arrElementData["download_template"], "img_screenshot_list");
            $arrFile["file_screen_2"] = $this->objTemplate->fillTemplate(array("img_title" => "", "img_filename" => urlencode($objFile->getStrScreen2()) ), $strPreviewTemplateID);
        }
        if($objFile->getStrScreen3() != "") {
            $strPreviewTemplateID = $this->objTemplate->readTemplate("/modul_downloads/".$this->arrElementData["download_template"], "img_screenshot_list");
            $arrFile["file_screen_3"] = $this->objTemplate->fillTemplate(array("img_title" => "", "img_filename" => urlencode($objFile->getStrScreen3()) ), $strPreviewTemplateID);
        }

		$strReturn = $this->fillTemplate($arrFile, $strTemplateID);


        //add the filename to the title
        class_modul_pages_portal::registerAdditionalTitle($objFile->getName());

		return $strReturn;
	}

//---Pfadfunktionen--------------------------------------------------------------------------------------

	/**
	 * Generates a small pathnavigation
	 *
	 * @return string
	 */
	private function generatePathnavi() {
		$strReturn = "";
		//Load the current records
		$objArchive = new class_modul_downloads_archive(isset($this->arrElementData["download_id"]) ? $this->arrElementData["download_id"] : "");

        if($objArchive->rightView()) {
            
            $objFile = new class_modul_downloads_file($this->getSystemid());
            //If the record is empty, try to load the archive
            if($objFile->getFilename() == "") {
                  $objFile = new class_modul_downloads_archive(isset($this->arrElementData["download_id"]) ? $this->arrElementData["download_id"] : "");
            }
            $arrTemplate = array();
            //check the action for the first entry
            $strAction = "openDlFolder";
            if($objFile instanceof class_modul_downloads_file && $objFile->getType() == 0)
                $strAction = "detailDownload";

            $arrTemplate["path_level"] = getLinkPortal($this->getPagename(), "", "_self", $objFile->getTitle(), $strAction, "", $objFile->getSystemid(), "", "", $objFile->getTitle());
            $strTemplateID = $this->objTemplate->readTemplate("/modul_downloads/".$this->arrElementData["download_template"], "pathnavi_entry");
            $strReturn .= $this->fillTemplate($arrTemplate, $strTemplateID);

            while(validateSystemid($objFile->getPrevId()) && $objFile->getPrevId() != $objArchive->getPrevId() && $objArchive->rightView()) {
                $objFile = new class_modul_downloads_file($objFile->getPrevId());
                if($objFile->getFilename() == "") {
                    $objFile = new class_modul_downloads_archive(isset($this->arrElementData["download_id"]) ? $this->arrElementData["download_id"] : "");
                }

                $arrTemplate["path_level"] = getLinkPortal($this->getPagename(), "", "_self", $objFile->getTitle(), "openDlFolder", "", $objFile->getSystemid(), "", "", $objFile->getTitle());
                $strTemplateID = $this->objTemplate->readTemplate("/modul_downloads/".$this->arrElementData["download_template"], "pathnavi_entry");
                $strReturn = $this->fillTemplate($arrTemplate, $strTemplateID) . $strReturn;
            }
        }

		return $strReturn;
	}

	/**
	 * Validates if the requested systemid is part of the dl-tree specified in the pageelement
	 *
	 * @return bool
	 */
	private function checkSystemidBelongsToCurrentTree() {
		$bitReturn = true;

		//check if requested systemid is part of the elements tree
        $objArchive = new class_modul_downloads_archive($this->arrElementData["download_id"]);
        $objFile = new class_modul_downloads_file($this->getSystemid());

        //If the record is empty, try to load the archive
        if($objFile->getFilename() == "") {
            $objFile = new class_modul_downloads_archive($this->getSystemid());
        }

        while(validateSystemid($objFile->getPrevId()) && $objFile->getPrevId() != $objArchive->getPrevId()) {
            $strBackupId = $objFile->getPrevId();
            $objFile = new class_modul_downloads_file($objFile->getPrevId());
            if($objFile->getFilename() == "") {
                $objFile = new class_modul_downloads_archive($strBackupId);
            }
        }

        //if the requested systemid belong to the tree set in the pageelement, the systemids should match.
        //otherwise, set the pageelements' systemid as the current id
        if($objFile->getSystemid() != $this->arrElementData["download_id"])
            $bitReturn = false;

		return $bitReturn;
	}

	/**
	 * Builds the rating bar available for every download.
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

		$intNumberOfIcons = class_modul_rating_rate::$intMaxRatingValue;

		//read the templates
		$strTemplateBarId = $this->objTemplate->readTemplate("/modul_downloads/".$this->arrElementData["download_template"], "rating_bar");

		if($bitRatingAllowed && $bitPermissions) {
			$strTemplateIconId = $this->objTemplate->readTemplate("/modul_downloads/".$this->arrElementData["download_template"], "rating_icon");

			for($intI = 1; $intI <= $intNumberOfIcons; $intI++) {
				$arrTemplate = array();
				$arrTemplate["rating_icon_number"] = $intI;

			    $arrTemplate["rating_icon_onclick"] = "kajonaRating('".$strSystemid."', '".$intI.".0', ".$intNumberOfIcons."); kajonaTooltip.hide(); return false;";
       		    $arrTemplate["rating_icon_title"] = $this->getText("download_rating_rate1").$intI.$this->getText("download_rating_rate2");

				$strIcons .= $this->fillTemplate($arrTemplate, $strTemplateIconId);
			}
		} else {
            //disable caching
            class_modul_pages_portal::disablePageCacheForGeneration();
		    if(!$bitRatingAllowed)
			    $strRatingBarTitle = $this->getText("download_rating_voted");
			else
			    $strRatingBarTitle = $this->getText("download_rating_permissions");
		}

		return $this->fillTemplate(array("rating_icons" => $strIcons, "rating_bar_title" => $strRatingBarTitle, "rating_rating" => $floatRating, "rating_ratingPercent" => ($floatRating/$intNumberOfIcons*100), "system_id" => $strSystemid, 2), $strTemplateBarId);
	}
    

    /**
     * Loads the array of files to display.
     *
     * @return array
     */
    protected function getArrFiles($intStart, $intEnd) {
        return class_modul_downloads_file::getFilesDB($this->getSystemid(), false, true, $intStart, $intEnd);
    }

    protected function getNumberOfEntriesOnLevel() {
        return class_modul_downloads_file::getNumberOfFilesDB($this->getSystemid(), false, true);
    }
}
?>