<?php
/*"******************************************************************************************************
*   (c) 2004-2006 by MulchProductions, www.mulchprod.de                                                 *
*   (c) 2007-2009 by Kajona, www.kajona.de                                                              *
*       Published under the GNU LGPL v2.1, see /system/licence_lgpl.txt                                 *
*-------------------------------------------------------------------------------------------------------*
*   $Id$                            *
********************************************************************************************************/

//Base class
include_once(_adminpath_."/class_admin.php");
include_once(_adminpath_."/interface_admin.php");

include_once(_systempath_."/class_modul_filemanager_repo.php");

/**
 * Admin-Parts of the filemanager
 *
 * @package modul_filemanager
 */
class class_modul_filemanager_admin extends class_admin implements  interface_admin {
	private $strFolder;
	private $strFolderOld;
	private $strAction;

	/**
	 * Constructor
	 *
	 */
	public function __construct() {
		$arrModule["name"] 				= "modul_filemanager";
		$arrModule["author"] 			= "sidler@mulchprod.de";
		$arrModule["moduleId"] 			= _filemanager_modul_id_;
		$arrModule["modul"]				= "filemanager";
		$arrModule["table"]		     	= _dbprefix_."filemanager";
		//base class
		parent::__construct($arrModule);
		$this->strAction = $this->getAction();
		$this->getCurrentFolder();
	}

	/**
	 * Decides which method is to be loaded
	 *
	 * @param string $strAction
	 */
	public function action($strAction = "") {
	    $strReturn = "";
        if($strAction == "")
            $strAction = "list";

        try {

    		if($strAction == "list")
    			$strReturn = $this->actionList();

    		if($strAction == "openFolder" || $strAction == "renameFile" || $strAction == "deleteFile"
    		   || $strAction == "deleteFolder" || $strAction == "newFolder" || $strAction == "uploadFile"
    		   || $strAction == "imageDetail" )
    			$strReturn = $this->actionFolderContent();

    		if($strAction == "newRepo") {
    			$strReturn = $this->actionNewRepo();
    			if($strReturn == "")
    			    $this->adminReload(_indexpath_."?admin=1&module=".$this->arrModule["modul"]);
    		}

    		if($strAction == "deleteRepo") {
    			$strReturn = $this->actionDeleteRepo();
    			if($strReturn == "")
    			    $this->adminReload(_indexpath_."?admin=1&module=".$this->arrModule["modul"]);
    		}

    		if($strAction == "editRepo") {
    			$strReturn = $this->actionEditRepo();
    			if($strReturn == "")
    			    $this->adminReload(_indexpath_."?admin=1&module=".$this->arrModule["modul"]);
    		}
            
            if($strAction == "imageDetails") {
            	$strReturn = $this->actionImageDetails();
            }
        }
        catch (class_exception $objException) {
		    $objException->processException();
		    $strReturn = "An internal error occured: ".$objException->getMessage();
		}
		$this->strOutput = $strReturn;
	}


	public function getOutputContent() {
		return $this->strOutput;
	}

	public function getOutputModuleNavi() {
	    $arrReturn = array();
        $arrReturn[] = array("right", getLinkAdmin("right", "change", "&changemodule=".$this->arrModule["modul"],  $this->getText("modul_rechte"), "", "", true, "adminnavi"));
        $arrReturn[] = array("", "");
		$arrReturn[] = array("view", getLinkAdmin($this->arrModule["modul"], "list", "", $this->getText("modul_liste"), "", "", true, "adminnavi"));
		$arrReturn[] = array("right2", getLinkAdmin($this->arrModule["modul"], "newRepo", "", $this->getText("modul_neu"), "", "", true, "adminnavi"));
		return $arrReturn;
	}

	protected function getRequiredFields() {
        $strAction = $this->getAction();
        $arrReturn = array();
        if($strAction == "newRepo") {
            $arrReturn["filemanager_name"] = "string";
            $arrReturn["filemanager_path"] = "folder";
        }
        if($strAction == "editRepo") {
            $arrReturn["filemanager_name"] = "string";
            $arrReturn["filemanager_path"] = "folder";
        }

        return $arrReturn;
    }


// --- ListenFunktionen ---------------------------------------------------------------------------------


	/**
	 * Returns a list of all repos known to the system
	 *
	 * @return string
	 */
	private function actionList() {
		$strReturn = "";
		//Check rights
		if($this->objRights->rightView($this->getModuleSystemid($this->arrModule["modul"]))) {
			//Load the repos
			$arrObjRepos = class_modul_filemanager_repo::getAllRepos(_filemanager_show_foreign_);
			$intI = 0;
			//Print every repo
			foreach($arrObjRepos as $objOneRepo) {
				//check rights
				if($this->objRights->rightView($objOneRepo->getSystemid())) {
                    $strActions = "";
			   		if($this->objRights->rightView($objOneRepo->getSystemid()))
			   			$strActions .= $this->objToolkit->listButton(getLinkAdmin($this->arrModule["modul"], "openFolder", "&systemid=".$objOneRepo->getSystemid(), "", $this->getText("repo_oeffnen"), "icon_folderActionOpen.gif"));
			   		if($this->objRights->rightRight2($objOneRepo->getSystemid()))
			   			$strActions .= $this->objToolkit->listButton(getLinkAdmin($this->arrModule["modul"], "editRepo", "&systemid=".$objOneRepo->getSystemid(), "", $this->getText("repo_bearbeiten"), "icon_folderProperties.gif"));
			   		if($this->objRights->rightRight2($objOneRepo->getSystemid()))
			   		    $strActions .= $this->objToolkit->listDeleteButton($objOneRepo->getStrName(), $this->getText("repo_loeschen_frage"), getLinkAdminHref($this->arrModule["modul"], "deleteRepo", "&systemid=".$objOneRepo->getSystemid()));
		   			if($this->objRights->rightRight2($objOneRepo->getSystemid()))
			   			$strActions .= $this->objToolkit->listButton(getLinkAdmin("right", "change", "&systemid=".$objOneRepo->getSystemid(), "", $this->getText("repo_rechte"), getRightsImageAdminName($objOneRepo->getSystemid())));

			   		$strReturn .= $this->objToolkit->listRow2Image(getImageAdmin("icon_folderOpen.gif"), $objOneRepo->getStrName(), $strActions, $intI++);
				}
			}
			if($this->objRights->rightRight2($this->getModuleSystemid($this->arrModule["modul"])))
			    $strReturn .= $this->objToolkit->listRow2Image("", "", getLinkAdmin($this->arrModule["modul"], "newRepo", "", $this->getText("modul_neu"), $this->getText("modul_neu"), "icon_blank.gif"), $intI++);


			if(uniStrlen($strReturn) != 0)
			    $strReturn = $this->objToolkit->listHeader().$strReturn.$this->objToolkit->listFooter();

			if(count($arrObjRepos) == 0)
				$strReturn .= $this->getText("liste_leer");
		}
		else
			$strReturn = $this->getText("fehler_recht");

		return $strReturn;
	}


// --- Filefunktionen------------------------------------------------------------------------------------

	/**
	 * Deltes a repo or shows the warning box
	 *
	 * @return string "" in case of success
	 */
	private function actionDeleteRepo() {
		$strReturn = "";

		if($this->objRights->rightDelete($this->getSystemid())) {
            $objRepo = new class_modul_filemanager_repo($this->getSystemid());
            $objRepo->deleteRepo();
		}
		else
			$strReturn = $this->getText("fehler_recht");

		return $strReturn;
	}

	/**
	 * returns the form for a new repo
	 *
	 * @return string
	 */
	private function actionNewRepo() {
		$strReturn = "";
		if($this->objRights->rightRight2($this->getModuleSystemid($this->arrModule["modul"]))) {
		    //validate Form, if passed
		    $bitValidated = true;
		    if($this->getParam("repoSaveNew") != "") {
                if(!$this->validateForm()) {
                    $bitValidated = false;
                    $this->setParam("repoSaveNew", "");
                }
		    }

			//save or new?
			if($this->getParam("repoSaveNew") == ""){
				//create the form
				if(!$bitValidated)
				    $strReturn .= $this->objToolkit->getValidationErrors($this);
    			$strReturn .= $this->objToolkit->formHeader(_indexpath_."?admin=1&module=filemanager&action=newRepo&repoSaveNew=1");
    			$strReturn .= $this->objToolkit->formInputText("filemanager_name", $this->getText("filemanager_name"), $this->getParam("filemanager_name"));
    			$strReturn .= $this->objToolkit->formInputText("filemanager_path", $this->getText("filemanager_path"), $this->getParam("filemanager_path"), "inputText", getLinkAdminPopup("folderview", "folderList", "&form_element=filemanager_path&folder=/portal", $this->getText("browser"), $this->getText("browser"), "icon_externalBrowser.gif", 500, 500, "ordneransicht"));
    			$strReturn .= $this->objToolkit->formTextRow($this->getText("filemanager_upload_filter_h"));
    			$strReturn .= $this->objToolkit->formInputText("filemanager_upload_filter", $this->getText("filemanager_upload_filter"), $this->getParam("filemanager_upload_filter"));
    			$strReturn .= $this->objToolkit->formTextRow($this->getText("filemanager_view_filter_h"));
    			$strReturn .= $this->objToolkit->formInputText("filemanager_view_filter", $this->getText("filemanager_view_filter"), $this->getParam("filemanager_view_filter"));
    			$strReturn .= $this->objToolkit->formInputSubmit($this->getText("submit"));
				$strReturn .= $this->objToolkit->formClose();
			}
			else {
				//save the passed data to database
				$objRepo = new class_modul_filemanager_repo();
				$objRepo->setStrName($this->getParam("filemanager_name"));
				$objRepo->setStrPath($this->getParam("filemanager_path"));
				$objRepo->setStrUploadFilter($this->getParam("filemanager_upload_filter"));
				$objRepo->setStrViewFilter($this->getParam("filemanager_view_filter"));
                if(!$objRepo->saveObjectToDb())
                    throw new class_exception($this->getText("fehler_repo"), class_exception::$level_ERROR);
			}
		}
		else
		  $strReturn = $this->getText("fehler_recht");

		return $strReturn;
	}



	/**
	 * Returns the form to edit the repo and saves the data
	 *
	 * @return string "" in case of success
	 */
	private function actionEditRepo() {
		$strReturn = "";
		if($this->objRights->rightRight2($this->getSystemid())) {
		    $bitValidated = true;
	        if($this->getParam("repoSaveEdit") != "") {
                if(!$this->validateForm()) {
                    $bitValidated = false;
                    $this->setParam("repoSaveEdit", "");
                }
	        }
			//Form or update?
			if($this->getParam("repoSaveEdit") == "") {
				$objRepo = new class_modul_filemanager_repo($this->getSystemid());

		        if(!$bitValidated)
		            $strReturn .= $this->objToolkit->getValidationErrors($this);
				//create the form
    			$strReturn .= $this->objToolkit->formHeader(_indexpath_."?admin=1&module=filemanager&action=editRepo&repoSaveEdit=1");
    			$strReturn .= $this->objToolkit->formInputText("filemanager_name", $this->getText("filemanager_name"), $objRepo->getStrName());
    			$strReturn .= $this->objToolkit->formInputText("filemanager_path", $this->getText("filemanager_path"), $objRepo->getStrPath(), "inputText", getLinkAdminPopup("folderview", "folderList", "&form_element=filemanager_path&folder=/portal", $this->getText("browser"), $this->getText("browser"), "icon_externalBrowser.gif", 500, 500, "ordneransicht"));
    			$strReturn .= $this->objToolkit->formTextRow($this->getText("filemanager_upload_filter_h"));
    			$strReturn .= $this->objToolkit->formInputText("filemanager_upload_filter", $this->getText("filemanager_upload_filter"), $objRepo->getStrUploadFilter());
    			$strReturn .= $this->objToolkit->formTextRow($this->getText("filemanager_view_filter_h"));
    			$strReturn .= $this->objToolkit->formInputText("filemanager_view_filter", $this->getText("filemanager_view_filter"), $objRepo->getStrViewFilter());
    			$strReturn .= $this->objToolkit->formInputHidden("systemid", $this->getSystemid());
    			$strReturn .= $this->objToolkit->formInputSubmit($this->getText("submit"));
				$strReturn .= $this->objToolkit->formClose();
			}
			else {
				//Update the databse
				$objRepo = new class_modul_filemanager_repo($this->getSystemid());
				$objRepo->setStrName($this->getParam("filemanager_name"));
				$objRepo->setStrPath($this->getParam("filemanager_path"));
				$objRepo->setStrUploadFilter($this->getParam("filemanager_upload_filter"));
				$objRepo->setStrViewFilter($this->getParam("filemanager_view_filter"));


				if($objRepo->updateObjectToDb())
					$strReturn = "";
				else
				    throw new class_exception($this->getText("repo_bearbeiten_fehler"), class_exception::$level_ERROR);
			}
		}
		else
			$strReturn = $this->getText("fehler_recht");

		return $strReturn;
	}


	/**
	 * Loads the content of a folder
	 * If requested, loads subactions,too
	 *
	 * @return string
	 */
	private function actionFolderContent() {
		$strReturn = "";
		if($this->objRights->rightView($this->getSystemid())) {
			$objRepo = new class_modul_filemanager_repo($this->getSystemid());
			//Load the files
			include_once(_systempath_."/class_filesystem.php");
			$objFilesystem = new class_filesystem();
            //React on request passed. Do this before loading the filelist, cause subactions could modify it
		   	$strExtra = "";
		   	if($this->strAction == "renameFile") {
		   		$strExtra .= $this->actionRenameFile();
		   	}
		   	elseif($this->strAction == "deleteFile") {
		   		$strExtra .= $this->actionDeleteFile();
		   	}
		   	elseif($this->strAction == "deleteFolder") {
		   		$strExtra .= $this->actionDeleteFolder();
		   	}
		   	elseif($this->strAction == "newFolder") {
		   		$strExtra .= $this->actionNewFolder();
		   	}
		   	elseif ($this->strAction == "imageDetail") {
		   	    $strExtra .= $this->actionFileDetailview();
		   	}
		   	else  {
		   		$strExtra .= $this->actionUploadFile();
		   	}
		   	//ok, load the list using the repo-data
		   	$arrViewFilter = array();
		   	if($objRepo->getStrViewFilter() != "")
		   		$arrViewFilter = explode(",", $objRepo->getStrViewFilter());
                
		   	$arrFiles = $objFilesystem->getCompleteList($this->strFolder, $arrViewFilter, array(".svn"), array(".svn", ".", ".."));
            $strActions = "";
		   	$strActions .= $this->objToolkit->listButton(getLinkAdmin($this->arrModule["modul"], "newFolder", "&systemid=".$this->getSystemid()."&folder=".$this->strFolderOld, "", $this->getText("ordner_anlegen"), "icon_folderOpen.gif"));
			$strActions .= $this->objToolkit->listButton(getLinkAdmin($this->arrModule["modul"], "uploadFile", "&systemid=".$this->getSystemid()."&folder=".$this->strFolderOld, "", $this->getText("datei_upload"), "icon_upload.gif"));

		   	//Building a status-bar, using the toolkit
		   	$arrInfobox = array();
		   	$arrInfobox["folder"] = $this->generatePathNavi($this->strFolder);
		   	$arrInfobox["extraactions"] = $strExtra;
		   	$arrInfobox["files"] = $arrFiles["nrFiles"];
		   	$arrInfobox["folders"] = $arrFiles["nrFolders"];
		   	$arrInfobox["actions"] = $strActions;

		   	$arrInfobox["foldertitle"] = $this->getText("foldertitle");
		   	$arrInfobox["nrfilestitle"] = $this->getText("nrfilestitle");
		   	$arrInfobox["nrfoldertitle"] = $this->getText("nrfoldertitle");
		   	$strReturn .= $this->objToolkit->getFilemanagerInfoBox($arrInfobox);
		   	$strReturn .= $this->objToolkit->divider();

			//So, start printing files & folders
			$intI = 0;
			$strReturn .= $this->objToolkit->listHeader();
	  		//Link one folder up?
	  		if($this->strFolderOld != "") {
	  			$strFolderNew = uniSubstr($this->strFolder, 0, uniStrrpos($this->strFolder, "/"));
	  			$strFolderNew = str_replace($objRepo->getStrPath(), "", $strFolderNew);
                $strReturn .= $this->objToolkit->listRow3( "..","", $this->objToolkit->listButton(getLinkAdmin($this->arrModule["modul"], "openFolder", "&systemid=".$this->getSystemid().($strFolderNew != "" ? "&folder=".$strFolderNew : ""), "", $this->getText("ordner_hoch"), "icon_folderActionLevelup.gif")), getImageAdmin("icon_folderOpen.gif"), $intI++);
	  		}
	  		else {
	  		    //Link back to the repos
	  		    $strReturn .= $this->objToolkit->listRow3( "..","", $this->objToolkit->listButton(getLinkAdmin($this->arrModule["modul"], "list", "", "", $this->getText("ordner_hoch"), "icon_folderActionLevelup.gif")), getImageAdmin("icon_folderOpen.gif"), $intI++);
	  		}
			if(count($arrFiles["folders"]) > 0) {
				foreach($arrFiles["folders"] as $strFolder) {
                    $strAction = "";
		   			$strAction .= $this->objToolkit->listButton(getLinkAdmin($this->arrModule["modul"], "openFolder", "&systemid=".$this->getSystemid()."&folder=".$this->strFolderOld."/".$strFolder, "", $this->getText("repo_oeffnen"), "icon_folderActionOpen.gif"));
                    
    				include_once(_systempath_."/class_filesystem.php");
    				$objFilesystem = new class_filesystem();
    				$arrFilesSub = $objFilesystem->getCompleteList($this->strFolder."/".$strFolder, array(), array(), array(".", ".."));
    				if(count($arrFilesSub["files"]) == 0 && count($arrFilesSub["folders"]) == 0) {
    					$strAction .= $this->objToolkit->listDeleteButton($strFolder, $this->getText("ordner_loeschen_frage"), getLinkAdminHref($this->arrModule["modul"], "deleteFolder", "&systemid=".$this->getSystemid()."".($this->strFolderOld!= "" ? "&folder=".$this->strFolderOld: "")."&delFolder=".$strFolder));
    				}
    				else
    					$strAction .= $this->objToolkit->listButton(getImageAdmin("icon_tonDisabled.gif", $this->getText("ordner_loeschen_fehler_l")));
                        
		   			$strReturn .= $this->objToolkit->listRow3($strFolder, (_filemanager_ordner_groesse_ != "false" ? bytesToString($this->folderSize($this->strFolder."/".$strFolder, $arrViewFilter, array(".svn"), array(".svn", ".", ".."))) : ""), $strAction, getImageAdmin("icon_folderOpen.gif"), $intI++);
				}
			}
			$strReturn .= $this->objToolkit->listFooter();
            $strReturn .= $this->objToolkit->divider();
            //For the files, we have to build a data table
            $arrHeader = array();
            $arrHeader[0] = "&nbsp;";
            $arrHeader[1] = "&nbsp;";
            $arrHeader[2] = $this->getText("datei_groesse");
            $arrHeader[3] = $this->getText("datei_erstell");
            $arrHeader[4] = $this->getText("datei_bearbeit");
            $arrHeader[5] = $this->getText("datei_zugriff");
            $arrHeader[6] = "";
            $arrFilesTemplate = array();
	  		if(count($arrFiles["files"]) > 0) {
	  		    $intJ = 0;
				foreach($arrFiles["files"] as $arrOneFile) {
					//Geticon
					$arrMime  = $this->objToolkit->mimeType($arrOneFile["filename"]);
					$strFilename = $arrOneFile["filename"];
					$bitImage = false;
					if($arrMime[1] == "jpg" || $arrMime[1] == "png" || $arrMime[1] == "gif")
					   $bitImage = true;
					//Filename too long?
					if(uniStrlen($strFilename) > 50)
						$strFilename = uniSubstr($strFilename, 0, 50)."...";

					$strActions = "";
					if(!$bitImage)
		   			    $strActions .= $this->objToolkit->listButton(getLinkAdminRaw(_webpath_.$this->strFolder."/".$arrOneFile["filename"], "",$this->getText("datei_oeffnen"), "icon_lens.gif", "_blank" ));
		   			else
		   			    $strActions .= $this->objToolkit->listButton(getLinkAdmin($this->arrModule["modul"], "imageDetails", "&systemid=".$this->getSystemid().($this->strFolderOld != "" ? "&folder=".$this->strFolderOld : "" )."&file=".$arrOneFile["filename"], "", $this->getText("datei_oeffnen"), "icon_lens.gif"));
		   			$strActions .= $this->objToolkit->listButton(getLinkAdmin($this->arrModule["modul"], "renameFile", "&systemid=".$this->getSystemid().($this->strFolderOld != "" ? "&folder=".$this->strFolderOld : "" )."&file=".$arrOneFile["filename"], "", $this->getText("datei_umbenennen"), "icon_pencil.gif"));
		   			$strActions .= $this->objToolkit->listDeleteButton($arrOneFile["filename"], $this->getText("datei_loeschen_frage"), getLinkAdminHref($this->arrModule["modul"], "deleteFile", "&systemid=".$this->getSystemid()."".($this->strFolderOld != "" ? "&folder=".$this->strFolderOld: "")."&file=".$arrOneFile["filename"]));
					
		   			// if an image, attach a thumbnail-tooltip
		   			if ($bitImage) {
		   			    $strImage = "<div class=\'loadingContainer\'><img src=\\'"._webpath_."/image.php?image=".urlencode(str_replace(_realpath_, "", $arrOneFile["filepath"]))."&amp;maxWidth=100&amp;maxHeight=100\\' /></div>";
		   			    $arrFilesTemplate[$intJ][0] = getImageAdmin($arrMime[2], $strImage, true);
		   			} else
		   			    $arrFilesTemplate[$intJ][0] = getImageAdmin($arrMime[2], $arrMime[0]);

					$arrFilesTemplate[$intJ][1] = $strFilename;
					$arrFilesTemplate[$intJ][2] = bytesToString($arrOneFile["filesize"]);
					$arrFilesTemplate[$intJ][3] = timeToString($arrOneFile["filecreation"]);
					$arrFilesTemplate[$intJ][4] = timeToString($arrOneFile["filechange"]);
					$arrFilesTemplate[$intJ][5] = timeToString($arrOneFile["fileaccess"]);
					$arrFilesTemplate[$intJ++][6] = "<div class=\"listActions\">".$strActions."</div>";
				}
	  		}
	  		$strReturn .= $this->objToolkit->dataTable($arrHeader, $arrFilesTemplate);
		}
		else
			$this->getText("fehler_recht");

		return $strReturn;
	}


	/**
	 * Loads the content of a folder
	 * If requested, loads subactions,too
	 *
	 * SPECIAL MODE FOR MODULE FOLDERVIEW
	 *
	 * @param string $strTargetfield
	 * @return string
	 */
	public function actionFolderContentFolderviewMode($strTargetfield) {
		$strReturn = "";

		//list repos or contents?
		if($this->getSystemid() == "") {
            if($this->objRights->rightView($this->getModuleSystemid($this->arrModule["modul"]))) {
    			//Load the repos
    			$arrObjRepos = class_modul_filemanager_repo::getAllRepos(_filemanager_show_foreign_);
    			$intI = 0;
    			//Print every repo
    			foreach($arrObjRepos as $objOneRepo) {
    				//check rights
    				if($this->objRights->rightView($objOneRepo->getSystemid())) {
                        $strActions = "";
    			   		if($this->objRights->rightView($objOneRepo->getSystemid()))
    			   			$strActions .= $this->objToolkit->listButton(getLinkAdmin("folderview", "list", "&form_element=".$strTargetfield."&systemid=".$objOneRepo->getSystemid(), "", $this->getText("repo_oeffnen"), "icon_folderActionOpen.gif"));

    			   		$strReturn .= $this->objToolkit->listRow2Image(getImageAdmin("icon_folderOpen.gif"), $objOneRepo->getStrName(), $strActions, $intI++);
    				}
    			}

    			if(uniStrlen($strReturn) != 0)
    			    $strReturn = $this->objToolkit->listHeader().$strReturn.$this->objToolkit->listFooter();

    			if(count($arrObjRepos) == 0)
    				$strReturn .= $this->getText("liste_leer");
    		}
    		else
    			$strReturn = $this->getText("fehler_recht");
		}
		else {
    		if($this->objRights->rightView($this->getSystemid())) {
    			$objRepo = new class_modul_filemanager_repo($this->getSystemid());
    			//Load the files
    			include_once(_systempath_."/class_filesystem.php");
    			$objFilesystem = new class_filesystem();
    			$strAddonAction = $this->getParam("fmcommand");
                //React on request passed. Do this before loading the filelist, cause subactions could modify it
    		   	$strExtra = "";
    		   	$bitSpecial = false;
    		   	if($strAddonAction == "newFolder") {
    		   	    $bitSpecial = true;
    		   		$strExtra .= $this->actionNewFolder(true, $strTargetfield);
    		   	}
    		   	elseif ($strAddonAction == "uploadFile")  {
    		   	    $bitSpecial = true;
    		   		$strExtra .= $this->actionUploadFile(true, $strTargetfield);
    		   	}
    		   	//ok, load the list using the repo-data
    		   	$arrViewFilter = array();
    		   	if($objRepo->getStrViewFilter() != "")
    		   		$arrViewFilter = explode(",", $objRepo->getStrViewFilter());

    		   	$arrFiles = $objFilesystem->getCompleteList($this->strFolder, $arrViewFilter, array(".svn"), array(".svn", ".", ".."));
                $strActions = "";
                if($strAddonAction == "") {
    		   	    $strActions .= $this->objToolkit->listButton(getLinkAdminPopup("folderview", "list", "&fmcommand=newFolder&systemid=".$this->getSystemid()."&folder=".$this->strFolderOld, $this->getText("ordner_anlegen"), $this->getText("ordner_anlegen"), "icon_folderOpen.gif", "200"));
    			    $strActions .= $this->objToolkit->listButton(getLinkAdminPopup("folderview", "list", "&fmcommand=uploadFile&systemid=".$this->getSystemid()."&folder=".$this->strFolderOld, $this->getText("datei_upload"), $this->getText("datei_upload"), "icon_upload.gif", "300"));
                }

    		   	//Building a status-bar, using the toolkit
    		   	$arrInfobox = array();
    		   	$arrInfobox["folder"] = $this->strFolder;
    		   	$arrInfobox["extraactions"] = $strExtra;
    		   	$arrInfobox["files"] = $arrFiles["nrFiles"];
    		   	$arrInfobox["folders"] = $arrFiles["nrFolders"];
    		   	$arrInfobox["actions"] = $strActions;

    		   	$arrInfobox["foldertitle"] = $this->getText("foldertitle");
    		   	$arrInfobox["nrfilestitle"] = $this->getText("nrfilestitle");
    		   	$arrInfobox["nrfoldertitle"] = $this->getText("nrfoldertitle");
    		   	$strReturn .= $this->objToolkit->getFilemanagerInfoBox($arrInfobox);


    			//So, start printing files & folders
    			$intI = 0;
    			if(!$bitSpecial) {
    			    $strReturn .= $this->objToolkit->divider();
        			$strReturn .= $this->objToolkit->listHeader();
        	  		//Link one folder up?
        	  		if($this->strFolderOld != "") {
        	  			$strFolderNew = uniSubstr($this->strFolder, 0, uniStrrpos($this->strFolder, "/"));
        	  			$strFolderNew = str_replace($objRepo->getStrPath(), "", $strFolderNew);
                        $strReturn .= $this->objToolkit->listRow3( "..","", $this->objToolkit->listButton(getLinkAdmin("folderview", "list", "&form_element=".$strTargetfield."&systemid=".$this->getSystemid().($strFolderNew != "" ? "&folder=".$strFolderNew : ""), "", $this->getText("ordner_hoch"), "icon_folderActionLevelup.gif")), getImageAdmin("icon_folderOpen.gif"), $intI++);
        	  		}
        	  		else {
        	  		    //Link up to repo list
        	  		    $strReturn .= $this->objToolkit->listRow3( "..","", $this->objToolkit->listButton(getLinkAdmin("folderview", "list", "&form_element=".$strTargetfield, "", $this->getText("ordner_hoch"), "icon_folderActionLevelup.gif")), getImageAdmin("icon_folderOpen.gif"), $intI++);
        	  		}
        			if(count($arrFiles["folders"]) > 0) {
        				foreach($arrFiles["folders"] as $strFolder) {
                            $strAction = "";
        		   			$strAction .= $this->objToolkit->listButton(getLinkAdmin("folderview", "list", "&form_element=".$strTargetfield."&systemid=".$this->getSystemid()."&folder=".$this->strFolderOld."/".$strFolder, "", $this->getText("repo_oeffnen"), "icon_folderActionOpen.gif"));
        		   			$strReturn .= $this->objToolkit->listRow3($strFolder, (_filemanager_ordner_groesse_ != "false" ? bytesToString($this->folderSize($this->strFolder."/".$strFolder, $arrViewFilter, array(".svn"), array(".svn", ".", ".."))) : ""), $strAction, getImageAdmin("icon_folderOpen.gif"), $intI++);
        				}
        			}
        			$strReturn .= $this->objToolkit->listFooter();
                    $strReturn .= $this->objToolkit->divider();
                    //For the files, we have to build a data table
                    $arrHeader = array();
                    $arrHeader[0] = "&nbsp;";
                    $arrHeader[1] = "&nbsp;";
                    $arrHeader[2] = "&nbsp;";
                    $arrHeader[6] = "";
                    $arrFilesTemplate = array();
        	  		if(count($arrFiles["files"]) > 0) {
        	  		    $intJ = 0;
        				foreach($arrFiles["files"] as $arrOneFile) {
        					//Get icon
        					$arrMime  = $this->objToolkit->mimeType($arrOneFile["filename"]);
        					
        					$bitImage = false;
					        if($arrMime[1] == "jpg" || $arrMime[1] == "png" || $arrMime[1] == "gif")
					           $bitImage = true;
					   
        					$strFilename = $arrOneFile["filename"];
        					//Filename too long?
        					$strFilename = uniStrTrim($strFilename, 50);

        					$strActions = "";
        		   			$strActions .= $this->objToolkit->listButton(getLinkAdminRaw(_webpath_.$this->strFolder."/".$arrOneFile["filename"], "",$this->getText("datei_oeffnen"), "icon_lens.gif", "_blank" ));

                            $strFolder = $this->strFolder;
        		   			$strValue = _webpath_.$strFolder."/".$arrOneFile["filename"];
        		   			$strActions .= $this->objToolkit->listButton("<a href=\"#\" title=\"".$this->getText("useFile")."\" class=\"showTooltip\" onClick=\"window.opener.document.getElementById('".$strTargetfield."').value='".$strValue."'; self.close(); \">".getImageAdmin("icon_accept.gif"));

				   			// if an image, attach a thumbnail-tooltip
				   			if ($bitImage) {
				   			    $strImage = "<div class=\'loadingContainer\'><img src=\\'"._webpath_."/image.php?image=".urlencode(str_replace(_realpath_, "", $arrOneFile["filepath"]))."&amp;maxWidth=100&amp;maxHeight=100\\' /></div>";
				   			    $arrFilesTemplate[$intJ][0] = getImageAdmin($arrMime[2], $strImage, true);
				   			} else
				   			    $arrFilesTemplate[$intJ][0] = getImageAdmin($arrMime[2], $arrMime[0]);

        					$arrFilesTemplate[$intJ][1] = $strFilename;
        					$arrFilesTemplate[$intJ][2] = bytesToString($arrOneFile["filesize"]);
        					$arrFilesTemplate[$intJ++][3] = "<div class=\"listActions\">".$strActions."</div>";
        				}
        	  		}
        	  		$strReturn .= $this->objToolkit->dataTable($arrHeader, $arrFilesTemplate);
    			}
    		}
    		else
    			$this->getText("fehler_recht");
		}

		return $strReturn;
	}

	/**
	 * Returns the form to rename a file
	 *
	 * @return string
	 */
	private function actionRenameFile() {
		$strReturn = "";
		//Check rights
		if($this->objRights->rightEdit($this->getSystemid())) {
			//Rename or form?
			if($this->getParam("datei_umbenennen_final") == "") {
				//Check existance of file
				if(is_file(_realpath_."/".$this->strFolder."/".$this->getParam("file"))) {
					//Form
					$strReturn .= $this->objToolkit->formHeader(_indexpath_."?admin=1&module=filemanager&action=renameFile&datei_umbenennen_final=1");
					$strReturn .= $this->objToolkit->formInputHidden("systemid", $this->getSystemid());
					$strReturn .= $this->objToolkit->formInputHidden("datei_name_alt", $this->getParam("file"));
					$strReturn .= $this->objToolkit->formInputHidden("folder", $this->strFolderOld);
                    $strReturn .= $this->objToolkit->formInputText("datei_name", $this->getText("datei_name"), $this->getParam("file"));
                    $strReturn .= $this->objToolkit->formTextRow($this->getText("datei_umbenennen_hinweis"));
                    $strReturn .= $this->objToolkit->formInputSubmit($this->getText("rename"));
                    $strReturn .= $this->objToolkit->formClose();
				}
			}
			else {
				$strFilename = createFilename(strtolower($this->getParam("datei_name")));
				//Check existance of old  & new file
				if($strFilename != "" && is_file(_realpath_."/".$this->strFolder."/".$this->getParam("datei_name_alt"))) {
					if(!is_file(_realpath_."/".$this->strFolder."/".$strFilename)) {
						//Rename File
						include_once(_systempath_."/class_filesystem.php");
						$objFilesystem = new class_filesystem();
						if($objFilesystem->fileRename($this->strFolder."/".$this->getParam("datei_name_alt"), $this->strFolder."/".$strFilename))
							$strReturn = $this->getText("datei_umbenennen_erfolg");
						else
						 	$strReturn = $this->getText("datei_umbenennen_fehler");
					}
					else
						$strReturn = $this->getText("datei_umbenennen_fehler_z");
				}
				else
					$strReturn = "fehler!";
			}
		}
		else
			$strReturn = $this->getText("fehler_recht");

		return $strReturn;
	}

	/**
	 * Shows the form to delete a file / deletes a file
	 *
	 * @return string
	 */
	private function actionDeleteFile() {
		$strReturn = "";
		//Rights
		if($this->objRights->rightDelete($this->getSystemid())) {
			include_once(_systempath_."/class_filesystem.php");
			$objFilesystem = new class_filesystem();
			if($objFilesystem->fileDelete($this->strFolder."/".$this->getParam("file")))
				$strReturn .= $this->getText("datei_loeschen_erfolg");
			else
				$strReturn .= $this->getText("datei_loeschen_fehler");
		}
		else
			$strReturn = $this->getText("fehler_recht");

		return $strReturn;
	}

	/**
	 * Deletes a folder, if empty, shows the warning
	 *
	 * @return string
	 */
	private function actionDeleteFolder() {
		$strReturn = "";
		//Rights
		if($this->objRights->rightDelete($this->getSystemid())) {
			include_once(_systempath_."/class_filesystem.php");
			$objFilesystem = new class_filesystem();

			if($objFilesystem->folderDelete($this->strFolder."/".$this->getParam("delFolder")))
				$strReturn .= $this->getText("ordner_loeschen_erfolg");
			else
				$strReturn .= $this->getText("ordner_loeschen_fehler");
		}
		else
			$strReturn = $this->getText("fehler_recht");

		return $strReturn;
	}


	/**
	 * Creates or shows the form to create a new folder
	 *
	 * @return string
	 */
	private function actionNewFolder($bitActionFromFolderview = false, $strFormElement = "") {
		$strReturn = "";
		//Rights
		if($this->objRights->rightEdit($this->getSystemid())) {
			//Create or form?
			if($this->getParam("createFolderFinal") == "") {
				//Form
				if($bitActionFromFolderview)
				    $strReturn .= $this->objToolkit->formHeader(_indexpath_."?admin=1&module=folderview&action=list&fmcommand=newFolder&createFolderFinal=1&form_element=".$strFormElement);
				else
				    $strReturn .= $this->objToolkit->formHeader(_indexpath_."?admin=1&module=filemanager&action=newFolder&createFolderFinal=1");
				$strReturn .= $this->objToolkit->formInputHidden("systemid", $this->getSystemid());
				$strReturn .= $this->objToolkit->formInputHidden("folder", $this->strFolderOld);
				$strReturn .= $this->objToolkit->formInputText("ordner_name", $this->getText("ordner_name"));
				$strReturn .= $this->objToolkit->formInputSubmit($this->getText("anlegen"));
				$strReturn .= $this->objToolkit->formClose();
			}
			else {
				//Create the folder
				$strFolder = createFilename(strtolower($this->getParam("ordner_name")), true);
				//folder already existing?
				if(!is_dir(_realpath_."/".$this->strFolder."/".$strFolder)) {
					include_once(_systempath_."/class_filesystem.php");
					$objFilesystem = new class_filesystem();
					if($objFilesystem->folderCreate($this->strFolder."/".$strFolder)) {
						$strReturn = $this->getText("ordner_anlegen_erfolg");
						if($bitActionFromFolderview)
						    $strReturn .= "<script type=text/javascript>opener.location.reload();window.close();</script>";
					}
					else
					 	$strReturn = $this->getText("order_anlegen_fehler");
				}
				else
					$strReturn = $this->getText("ordner_anlegen_fehler_l");
			}
		}
		else
			$strReturn = $this->getText("fehler_recht");

		return $strReturn;
	}


	/**
	 * Uploads or shows the form to upload a file
	 *
	 * @return string
	 */
	private function actionUploadFile($bitActionFromFolderview = false, $strFormElement = "") {
		$strReturn = "";
		if($this->objRights->rightRight1($this->getSystemid())) {
			//Upload-Form
			$objRepo = new class_modul_filemanager_repo($this->getSystemid());
			
			if($bitActionFromFolderview)
			    $strReturn .= $this->objToolkit->formHeader(_indexpath_."?admin=1&amp;module=folderview&amp;action=list&amp;fmcommand=uploadFile&amp;datei_upload_final=1&amp;form_element=".$strFormElement, "formUpload", "multipart/form-data");
			else
			    $strReturn .= $this->objToolkit->formHeader(_indexpath_."?admin=1&amp;module=filemanager&amp;action=uploadFile&amp;datei_upload_final=1", "formUpload", "multipart/form-data");
			$strReturn .= $this->objToolkit->formInputHidden("systemid", $this->getSystemid());
			$strReturn .= $this->objToolkit->formInputHidden("folder", $this->strFolderOld);
			$strReturn .= $this->objToolkit->formTextRow($this->getText("max_size").(bytesToString($this->objConfig->getPhpIni("post_max_size"), true) > bytesToString($this->objConfig->getPhpIni("upload_max_filesize"), true) ? bytesToString($this->objConfig->getPhpIni("upload_max_filesize"), true) : bytesToString($this->objConfig->getphpIni("post_max_size"), true)));
			
			//Fallback code if no or old Flash Player available
			$strFallbackForm = "<div id=\"upload_prototype\" style=\"display: inline;\">";
			$strFallbackForm .= $this->objToolkit->formInputUpload("filemanager_upload[0]", $this->getText("filemanager_upload"));
			$strFallbackForm .= "</div>";
			$strFallbackForm .= $this->objToolkit->formTextRow("<a href=\"javascript:addDownloadInput('upload_prototype', 'filemanager_upload');\" >".$this->getText("add_upload_field")."</a>");
			$strFallbackForm .= $this->objToolkit->formInputSubmit($this->getText("upload_submit"));
			
			$strAllowedFileTypes = uniStrReplace(array(".", ","), array("*.", ";"), $objRepo->getStrUploadFilter());

			$arrTexts = array(
				"upload_fehler_filter" =>  $this->getText("upload_fehler_filter"),
				"upload_multiple_uploadFiles" => $this->getText("upload_multiple_uploadFiles"),
				"upload_multiple_cancel" => $this->getText("upload_multiple_cancel")
			);
			
			$strReturn .= $this->objToolkit->formInputUploadMultipleFlash("filemanager_upload[0]", $strAllowedFileTypes, $strFallbackForm, $arrTexts);
			$strReturn .= $this->objToolkit->formClose();

			if($this->getParam("datei_upload_final") != "") {
				//Handle the fileupload
				$arrSourcesPre = $this->getParam("filemanager_upload");
                foreach ($arrSourcesPre["name"] as $intKey => $strName) {
                    if($strName != "") {
                        $arrSources[$intKey] = array();
                        $arrSources[$intKey]["name"] = $arrSourcesPre["name"][$intKey];
                        $arrSources[$intKey]["tmp_name"] = $arrSourcesPre["tmp_name"][$intKey];
                    }
                }

				$bitSuccess = false;
				foreach ($arrSources as $arrSource) {
    				$strTarget = $this->strFolder."/".createFilename(strtolower($arrSource["name"]));
    				include_once(_systempath_."/class_filesystem.php");
    				$objFilesystem = new class_filesystem();
    				//Check file for correct filters
    				$arrAllowed = explode(",", $objRepo->getStrUploadFilter());
    				$strSuffix = strtolower(uniSubstr($arrSource["name"], uniStrrpos($arrSource["name"], ".")));
    				if($objRepo->getStrUploadFilter() == "" || in_array($strSuffix, $arrAllowed)) {
    					if($objFilesystem->copyUpload($strTarget, $arrSource["tmp_name"])) {
    						$strReturn .= $this->getText("upload_erfolg");
    						$bitSuccess = true;
                            
                            class_logger::getInstance()->addLogRow("uploaded file ".$strTarget, class_logger::$levelInfo);

    					}
    					else
    						$strReturn .= $this->getText("upload_fehler");
    				}
    				else {
    					@unlink($arrSource["tmp_name"]);
    					$strReturn .= $this->getText("upload_fehler_filter");
    				}
				}
				if($bitActionFromFolderview && $bitSuccess)
                    $strReturn .= "<script type=text/javascript>opener.location.reload();window.close();</script>";
			}
		}
		else
			$strReturn = $this->getText("fehler_recht");

		return $strReturn;
	}

	
	/**
	 * Returns details and additional functions handling the current image.
	 *
	 * @param string $strFile
	 * @return string
	 */
	private function actionImageDetails() {
		$strReturn = "";
        
        $arrTemplate = array();
        $arrTemplate["file_pathnavi"] = $this->generatePathNavi($this->strFolder);         
		$strFile = _realpath_.(substr($this->strFolder, 0, 1) == "/" ? "" : "/").$this->strFolder."/".$this->getParam("file");
		if(is_file($strFile)) {
            
			//Details der Datei sammeln
			include_once(_systempath_."/class_filesystem.php");
			$objFilesystem = new class_filesystem();
			$arrDetails = $objFilesystem->getFileDetails($strFile);
            
			$arrTemplate["file_name"] = $arrDetails["filename"];
			$arrTemplate["file_path"] = $arrDetails["filepath"];
			$arrTemplate["file_path_title"] = $this->getText("datei_pfad");

			
			$arrSize = getimagesize($strFile);
			$arrTemplate["file_dimensions"] = $arrSize[0]." x ".$arrSize[1];
            $arrTemplate["file_dimensions_title"] = $this->getText("bild_groesse");
            
            $arrTemplate["file_size"] = bytesToString($arrDetails["filesize"]);
            $arrTemplate["file_size_title"] = $this->getText("datei_groesse");
            
            $arrTemplate["file_lastedit"] = timeToString($arrDetails["filechange"]);
            $arrTemplate["file_lastedit_title"] = $this->getText("datei_bearbeit");
            

			//Generate Dimensions
			$intHeight = $arrSize[1];
			$intWidth = $arrSize[0];
			$strPath = $strFile;
			if(uniStrpos($strPath, _realpath_) !== false)
				$strPath = str_replace(_realpath_, _webpath_, $strPath);

			while($intWidth > 300 || $intHeight > 300) {
				$intWidth *= 0.8;
				$intHeight *= 0.8;
			}
			//Round
			$intWidth = number_format($intWidth, 0);
			$intHeight = number_format($intHeight, 0);
			$arrTemplate["file_image"] = "<img src=\""._webpath_."/image.php?image=".urlencode(str_replace(_realpath_, "", $strFile))."&amp;maxWidth=".$intWidth."&amp;maxHeight=".$intHeight."\" id=\"fm_filemanagerPic\" />";
            
            $arrTemplate["file_actions"] = "";
            $arrTemplate["file_actions"] .= $this->objToolkit->listDeleteButton($arrDetails["filename"], $this->getText("datei_loeschen_frage"), getLinkAdminHref($this->arrModule["modul"], "deleteFile", "&systemid=".$this->getSystemid()."".($this->strFolderOld != "" ? "&folder=".$this->strFolderOld: "")."&file=".$arrDetails["filename"]));
            $arrTemplate["file_actions"] .= $this->objToolkit->listButton(getLinkAdminManual("href=\"javascript:filemanagerShowRealsize();\"", "", $this->getText("showRealsize"), "icon_zoom_in.gif"));
            $arrTemplate["file_actions"] .= $this->objToolkit->listButton(getLinkAdminManual("href=\"javascript:filemanagerShowPreview();\"", "", $this->getText("showPreview"), "icon_zoom_out.gif"));
            $arrTemplate["file_actions"] .= $this->objToolkit->listButton(getLinkAdminManual("href=\"javascript:filemanagerShowCropping();\"", "",  $this->getText("cropImage"), "icon_crop.gif"));
            $arrTemplate["file_actions"] .= $this->objToolkit->listButton(getLinkAdminManual("href=\"javascript:filemanagerSaveCropping();\"", "",  $this->getText("cropImageAccept"), "icon_crop_acceptDisabled.gif", "accept_icon"));
            $arrTemplate["file_actions"] .= $this->objToolkit->listButton(getLinkAdminManual("href=\"javascript:filemanagerRotate(90);\"", "",  $this->getText("rotateImageLeft"), "icon_rotate_left.gif"));
            $arrTemplate["file_actions"] .= $this->objToolkit->listButton(getLinkAdminManual("href=\"javascript:filemanagerRotate(270);\"", "",  $this->getText("rotateImageRight"), "icon_rotate_right.gif"));
             
            $arrTemplate["filemanager_image_js"] = "<script type=\"text/javascript\">
                   var fm_image_rawurl = '"._webpath_.str_replace(_realpath_, "", $strFile)."';
                   var fm_image_scaledurl = '"._webpath_."/image.php?image=".urlencode(str_replace(_realpath_, "", $strFile))."&maxWidth=".$intWidth."&maxHeight=".$intHeight."';
                   var fm_image_isScaled = true;
                   var fm_repo_id = '".$this->getSystemid()."'; 
                   var fm_file = '".$this->getParam("file")."' ;
                   var fm_folder = '".$this->getParam("folder")."'; 
                    kajonaAjaxHelper.loadImagecropperBase();
                    addCss('admin/scripts/yui/resize/assets/resize-core.css');
                    addCss('admin/scripts/yui/imagecropper/assets/imagecropper-core.css'); 
                                   
                   </script>";
			//TODO: fix to new dialog-calls
            $arrTemplate["filemanager_image_js"] .= $this->objToolkit->modalDialog("", $this->getText("cropWarningPreview"), "fm_preview_warning");
            $arrTemplate["filemanager_image_js"] .= $this->objToolkit->modalDialog("", $this->getText("cropWarningSaving")."<a href=\"javascript:filemanagerSaveCroppingToBackend();\">".$this->getText("cropWarningCrop")."</a>", "fm_crop_save_warning");
            $arrTemplate["filemanager_image_js"] .= $this->objToolkit->jsDialog("", "<img src=\""._skinwebpath_."/loading.gif\" />", "fm_crop_screenlock", 3);

            $arrTemplate["filemanager_internal_code"] = "<input type=\"hidden\" name=\"fm_int_realwidth\" id=\"fm_int_realwidth\" value=\"".$arrSize[0]."\" \">";
            $arrTemplate["filemanager_internal_code"] .= "<input type=\"hidden\" name=\"fm_int_realheight\" id=\"fm_int_realheight\" value=\"".$arrSize[1]."\" \">";

		}
		$strReturn .= $this->objToolkit->getFilemanagerImageDetails($arrTemplate);
		return $strReturn;
	}


// --- Helferfunktionen ---------------------------------------------------------------------------------


	/**
	 * Determins the current folder
	 *
	 */
	private function getCurrentFolder() {
		$objRepo = new class_modul_filemanager_repo($this->getSystemid());
		//Check, which level should be loaded. Remind the evil ones!
		if($this->getParam("folder") != "")
			$strFolder = $this->getParam("folder");
		else
			$strFolder = "";

		//Check
		$strFolder = htmlspecialchars($strFolder);
		//Avoid jumps
		$strFolder = str_replace("../", "", $strFolder);
		//Add to the repo-path
		$this->strFolderOld = $strFolder;
		$this->strFolder = ($objRepo->getStrPath() != "" ? $objRepo->getStrPath() : "").$strFolder;
	}




	/**
	 * Fetches the size of a folder recursiv
	 *
	 * @param string $strStartFolder
	 * @param mixed $arrEnding
	 * @param mixed $arrExclude
	 * @param mixed $arrFolderExclude
	 * @return int
	 */
	private function folderSize($strStartFolder, $arrEnding, $arrExclude, $arrFolderExclude) {
		$intReturn = 0;
		//Filesystemobject
		include_once(_systempath_."/class_filesystem.php");
		$objFilesystem = new class_filesystem();
		$arrFiles = $objFilesystem->getCompleteList($strStartFolder, $arrEnding, $arrExclude, $arrFolderExclude);

		foreach($arrFiles["files"] as $arrFile)
			$intReturn += $arrFile["filesize"];

		//Call it recursive
		if(count($arrFiles["folders"]) > 0) {
			foreach($arrFiles["folders"] as $strFolder)
				$intReturn += $this->folderSize($strStartFolder."/".$strFolder, $arrEnding, $arrExclude, $arrFolderExclude);
		}
		return $intReturn;
	}


	/**
	 * Generates a path-navigation
	 *
	 * @param string $strPath
	 * @return string
	 */
	private function generatePathNavi($strPath) {
        $strReturn = "";
        $arrPaths = array();
        $objRepo = new class_modul_filemanager_repo($this->getSystemid());

        //remove repo-folder
        $strPath = uniStrReplace($objRepo->getStrPath(), "", $strPath);

        //remove first /
        if(isset($strPath[0]) && $strPath[0] == "/") {
            $strPath = uniSubstr($strPath, 1);
        }

        //the first entry is the repo itself
        $arrPaths[] = getLinkAdmin($this->arrModule["modul"], "openFolder", "&systemid=".$this->getSystemid(), $objRepo->getStrPath());

        if(uniStrlen($strPath) > 0 ) {
            $arrTempFolders = explode("/", $strPath);

            $strRealFolder = "";
            foreach ($arrTempFolders as $intKey => $strOneFolder) {
                //get the name of the current folder
                $strFoldername = $strOneFolder;
                $strRealFolder .= "/".$strOneFolder;
                $arrPaths[] = getLinkAdmin($this->arrModule["modul"], "openFolder", "&folder=".$strRealFolder."&systemid=".$this->getSystemid(), $strFoldername);
                //remove the current folder
                $strPath = uniSubstr($strPath, 0, uniStrrpos($strPath, "/"));
            }
        }

        return $this->objToolkit->getPathNavigation($arrPaths);
	}



} //class_modul_filemanager_admin
?>