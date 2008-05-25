<?php
/*"******************************************************************************************************
*   (c) 2004-2006 by MulchProductions, www.mulchprod.de                                                 *
*   (c) 2007-2008 by Kajona, www.kajona.de                                                              *
*       Published under the GNU LGPL v2.1, see /system/licence_lgpl.txt                                 *
*-------------------------------------------------------------------------------------------------------*
* 																										*
* 	class_modul_downloads_portal.php																	*
* 	Portal-class of the downloads module                                                                *
*-------------------------------------------------------------------------------------------------------*
*	$Id$                             *
********************************************************************************************************/

//Include der Mutter-Klasse
include_once(_portalpath_."/class_portal.php");
include_once(_portalpath_."/interface_portal.php");
//needed classes
include_once(_systempath_."/class_modul_downloads_archive.php");
include_once(_systempath_."/class_modul_downloads_file.php");

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
		if($this->getSystemid() == "0" || $this->getSystemid() == "" || $this->getAction() != "openDlFolder" || ! $this->checkSystemidBelongsToCurrentTree()) {
		    $this->setSystemid($this->arrElementData["download_id"]);
		} 
		
		$arrObjects = class_modul_downloads_file::getFilesDB($this->getSystemid(), false, true);

		if(count($arrObjects) > 0) {
			$strFileList = "";
			$strFolderList = "";
			foreach($arrObjects as $objOneFile) {
				//check rights
				if($this->objRights->rightView($objOneFile->getSystemid())) {
					$arrTemplate = array();
					//Folder or file?
					if($objOneFile->getType() == 0) {
                        //File
						$strTememplateID = $this->objTemplate->readTemplate("/modul_downloads/".$this->arrElementData["download_template"], "file");
						$arrTemplate["file_name"] = $objOneFile->getName();
						$arrTemplate["file_description"] = $objOneFile->getDescription()."";
						$arrTemplate["file_hits"] = $objOneFile->getHits();
						$arrTemplate["file_size"] = bytesToString($objOneFile->getSize());
						$arrTemplate["file_rating"] = $this->buildRatingBar($objOneFile->getRating(), $objOneFile->getSystemid());

						//could we get a preview (e.g. if its an image)?
						$strSuffix = uniSubstr($objOneFile->getFilename(), uniStrrpos($objOneFile->getFilename(), "."));
						if($strSuffix == ".jpg" || $strSuffix == ".gif" || $strSuffix == ".png")
						    $arrTemplate["file_preview"] = "<img src=\""._webpath_."/image.php?image=".$objOneFile->getFilename()."&amp;maxWidth=150&amp;maxHeight=100\" />";
						//Right to download?
						if($this->objRights->rightRight2($objOneFile->getSystemid())) {
							$arrTemplate["file_link"] = "<a href=\""._webpath_."/download.php?systemid=".$objOneFile->getSystemid()."\">".$this->getText("download_datei_link")."</a>";
							$arrTemplate["file_href"] = ""._webpath_."/download.php?systemid=".$objOneFile->getSystemid()."";
						}
						else {
							$arrTemplate["file_link"] = $this->getText("download_datei_link");
							$arrTemplate["file_href"] = "";
						}

						$strFileList .= $this->objTemplate->fillTemplate($arrTemplate, $strTememplateID);
					}
					elseif ($objOneFile->getType() == 1) {
					    //Folder
						$strTememplateID = $this->objTemplate->readTemplate("/modul_downloads/".$this->arrElementData["download_template"], "folder");
						$arrTemplate["folder_name"] = $objOneFile->getName();
						$arrTemplate["folder_description"] = $objOneFile->getDescription()."";
						$arrTemplate["folder_link"] = getLinkPortal($this->getPagename(),  "", "_self", $this->getText("download_ordner_link"), "openDlFolder", "", $objOneFile->getSystemid());
						$arrTemplate["folder_href"] = getLinkPortalRaw($this->getPagename(), "","openDlFolder", "", $objOneFile->getSystemid());
						$strFolderList .= $this->objTemplate->fillTemplate($arrTemplate, $strTememplateID);
					}
				}
			}

			//the sourrounding template
			$strTememplateID = $this->objTemplate->readTemplate("/modul_downloads/".$this->arrElementData["download_template"], "list");
			$arrTempalte = array();
			$arrTempalte["folderlist"] = $strFolderList;
			$arrTempalte["filelist"] = $strFileList;
			$arrTempalte["pathnavigation"] = $this->generatePathnavi();
			$strReturn .= $this->objTemplate->fillTemplate($arrTempalte, $strTememplateID);
		}
		else {
			$strTememplateID = $this->objTemplate->readTemplate("/modul_downloads/".$this->arrElementData["download_template"], "list");
			$arrTempalte = array();
			$arrTempalte["filelist"] = $this->getText("liste_leer");
			$arrTempalte["pathnavigation"] = $this->generatePathnavi();
			$strReturn .= $this->objTemplate->fillTemplate($arrTempalte, $strTememplateID);
		}

		return $strReturn;
	}

//---Pfadfunktionen--------------------------------------------------------------------------------------

	/**
	 * Generates a small pathnavigation
	 *
	 * @return unknown
	 */
	private function generatePathnavi() {
		$strReturn = "";
		//Load the current records
		$objArchive = new class_modul_downloads_archive($this->arrElementData["download_id"]);
		$objFile = new class_modul_downloads_file($this->getSystemid());
		//If the record is empty, try to load the archive
		if($objFile->getFilename() == "") {
		      $objFile = new class_modul_downloads_archive($this->arrElementData["download_id"]);
		}

		$arrTemplate["path_level"] = getLinkPortal($this->getPagename(), "", "_self", $objFile->getTitle(), "openDlFolder", "", $objFile->getSystemid());
		$strTemplateID = $this->objTemplate->readTemplate("/modul_downloads/".$this->arrElementData["download_template"], "pathnavi_entry");
		$strReturn .= $this->objTemplate->fillTemplate($arrTemplate, $strTemplateID);

		while($objFile->getPrevId() != "0" && $objFile->getPrevId() != $objArchive->getPrevId()) {
		    $objFile = new class_modul_downloads_file($objFile->getPrevId());
		    if($objFile->getFilename() == "") {
		        $objFile = new class_modul_downloads_archive($this->arrElementData["download_id"]);
		    }
   		    $arrTemplate["path_level"] = getLinkPortal($this->getPagename(), "", "_self", $objFile->getTitle(), "openDlFolder", "", $objFile->getSystemid());
   	       	$strTemplateID = $this->objTemplate->readTemplate("/modul_downloads/".$this->arrElementData["download_template"], "pathnavi_entry");
       		$strReturn = $this->objTemplate->fillTemplate($arrTemplate, $strTemplateID) . $strReturn;
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
    
        while($objFile->getPrevId() != "0" && $objFile->getPrevId() != $objArchive->getPrevId()) {
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
	 * @return string
	 */
	private function buildRatingBar($floatRating, $strSystemid) {
		$strIcons = "";
		
		//read the templates
		$strTemplateBarId = $this->objTemplate->readTemplate("/modul_downloads/".$this->arrElementData["download_template"], "rating_bar");
		$strTemplateFilledId = $this->objTemplate->readTemplate("/modul_downloads/".$this->arrElementData["download_template"], "rating_icon_filled");
		$strTemplateEmptyId = $this->objTemplate->readTemplate("/modul_downloads/".$this->arrElementData["download_template"], "rating_icon_empty");
		
		//currently, ratings are up to 5. increase here to get other ranges.
		for($intI = 1; $intI < 6; $intI++) {
			$arrTemplate = array();
			$strIconId = "kajona_downloads_rating_icon_".$strSystemid."_".$intI;
			$arrTemplate["rating_icon_id"] = $strIconId;
			$arrTemplate["rating_icon_href"] = "javascript:downloadsRating('".$strSystemid."', '".$intI.".0', 6);";
			$arrTemplate["rating_icon_mouseover"] = "downloadsRatingMOver('".$strIconId."', 6);";
			$arrTemplate["rating_icon_mouseout"] = "downloadsRatingMOut('".$strIconId."', 6, ".round($floatRating).");";
			
			if(round($floatRating) > $intI) 
				$strIcons .= $this->objTemplate->fillTemplate($arrTemplate, $strTemplateFilledId); 
			else
			    $strIcons .= $this->objTemplate->fillTemplate($arrTemplate, $strTemplateEmptyId);
		}
		return $this->objTemplate->fillTemplate(array("rating_icons" => $strIcons), $strTemplateBarId);
	}

}
?>