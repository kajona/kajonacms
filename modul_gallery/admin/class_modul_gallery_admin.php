<?php
/*"******************************************************************************************************
*   (c) 2004-2006 by MulchProductions, www.mulchprod.de                                                 *
*   (c) 2007-2011 by Kajona, www.kajona.de                                                              *
*       Published under the GNU LGPL v2.1, see /system/licence_lgpl.txt                                 *
*-------------------------------------------------------------------------------------------------------*
*	$Id$                               *
********************************************************************************************************/

/**
 * Admin class of the gallery-module. Used to sync the galleries with the filesystem and to define picture-properties
 *
 * @package modul_gallery
 * @author sidler@mulchprod.de
 */
class class_modul_gallery_admin extends class_admin implements interface_admin  {

    private $strPeAddon = "";

	/**
	 * Construcut
	 *
	 */
	public function __construct() {
        $arrModule = array();
		$arrModule["name"] 				= "modul_gallery";
		$arrModule["author"] 			= "sidler@mulchprod.de";
		$arrModule["moduleId"] 			= _gallery_modul_id_;
		$arrModule["table"] 			= _dbprefix_."gallery_gallery";
		$arrModule["table2"]			= _dbprefix_."gallery_pic";
		$arrModule["modul"]				= "gallery";
		//Base class
		parent::__construct($arrModule);

        if($this->getParam("pe") == "1")
            $this->strPeAddon = "&pe=1";
	}

	/**
	 * Action-block. Controlles the further behaviour of the class
	 *
	 * @param string $strAction
	 */
	public function action($strAction = "") {
		$strReturn = "";

        //sync?
        if($this->getParam("resync") == "true") {
            $this->actionSyncInternal();
            $this->adminReload(getLinkAdminHref($this->arrModule["modul"], "showGallery", "&systemid=".$this->getSystemid()));
        }

        $strReturn = parent::action($strAction);


		$this->strOutput = $strReturn;
	}

	protected function getOutputModuleNavi() {
	    $arrReturn = array();
        $arrReturn[] = array("right", getLinkAdmin("right", "change", "&changemodule=".$this->arrModule["modul"],  $this->getText("modul_rechte"), "", "", true, "adminnavi"));
        $arrReturn[] = array("", "");
	    $arrReturn[] = array("view", getLinkAdmin($this->arrModule["modul"], "list", "", $this->getText("commons_list"), "", "", true, "adminnavi"));
     	$arrReturn[] = array("edit", getLinkAdmin($this->arrModule["modul"], "newGallery", "", $this->getText("galerie_neu"), "", "", true, "adminnavi"));
        $arrReturn[] = array("", "");
     	$arrReturn[] = array("right1", getLinkAdmin($this->arrModule["modul"], "massSync", "", $this->getText("gallery_masssync"), "", "", true, "adminnavi"));
     	return $arrReturn;
    }


	public function getRequiredFields() {
        $strAction = $this->getAction();
        $arrReturn = array();
        if($strAction == "saveGallery") {
            $arrReturn["gallery_title"] = "string";
            $arrReturn["gallery_path"] = "folder";
        }
        if($strAction == "editImage") {
            $arrReturn["pic_name"] = "string";
        }

        return $arrReturn;
    }


    protected function actionSortUp() {
        $this->setPositionAndReload($this->getSystemid(), "upwards");
    }

    protected function actionSortDown() {
        $this->setPositionAndReload($this->getSystemid(), "downwards");
    }

// --- ListenFunktionen ---------------------------------------------------------------------------------


	/**
	 * Creates a list of all available galleries
	 *
	 * @return string
	 */
	protected function actionList() {
		$strReturn = "";
        $strJsSyncCode = "";
		//Pruefen der Modul-Rechte
		if($this->objRights->rightView($this->getModuleSystemid($this->arrModule["modul"]))) {
			//Load galleries
			$arrObjGalleries = class_modul_gallery_gallery::getGalleries();
			$intI = 0;

			//initial js-code needed for common tasks
			$strJsSyncCode .= $this->objToolkit->jsDialog(3);
			$strJsSyncCode .= $this->objToolkit->jsDialog(0);
			$strJsSyncCode .= "<script type=\"text/javascript\">
                function gallery_init_screenlock_dialog() { jsDialog_3.init(); }
                function gallery_hide_screenlock_dialog() { jsDialog_3.hide(); }

                function syncGallery(strSystemid) {
                    gallery_init_screenlock_dialog();

                    KAJONA.admin.ajax.genericAjaxCall('gallery', 'syncGallery', strSystemid, {
						    success : function(o) {
						        gallery_hide_screenlock_dialog();
						        jsDialog_0.setTitle('".$this->getText("syncDialogHeader")."');
						        jsDialog_0.setContentRaw(o.responseText+'<br /><br /><input type=\"submit\" name=\"closeButton\" value=\"".$this->getText("hideSyncDialog")."\" class=\"inputSubmitShort\" onclick=\"jsDialog_0.hide(); return false;\" /><br />');
						        jsDialog_0.init();
						        KAJONA.admin.statusDisplay.displayXMLMessage(o.responseText);
						    },
						    failure : function(o) {
						        gallery_hide_screenlock_dialog();
						        KAJONA.admin.statusDisplay.messageError(\"<b>Request failed!</b>\"
						                + o.responseText);
						    }
						}
					);
                }
            </script>";


			//Iterate over all galleries
			foreach($arrObjGalleries as $objOneGallery) {
				//Check specific rights
				if($objOneGallery->rightView()) {
				    $strAction = "";
				    if($objOneGallery->rightView())
			   		    $strAction .= $this->objToolkit->listButton(getLinkAdmin($this->arrModule["modul"], "showGallery", "&systemid=".$objOneGallery->getSystemid(), "", $this->getText("galerie_anzeigen"), "icon_folderActionOpen.gif"));
			   		if($objOneGallery->rightRight1()) {
			   			//snyc is allowed. create js-code for ajax-syncing
			   			$strAction .= $this->objToolkit->listButton(getLinkAdminManual("href=\"javascript:syncGallery('".$objOneGallery->getSystemid()."');\"",  "", $this->getText("galerie_syncro"), "icon_sync.gif"));
			   		}
			   		if($objOneGallery->rightRight3())
			   		    $strAction .= $this->objToolkit->listButton(getLinkAdmin($this->arrModule["modul"],  "editGallery", "&systemid=".$objOneGallery->getSystemid(), "", $this->getText("galerie_bearbeiten"), "icon_pencil.gif"));
			   		if($objOneGallery->rightRight3())
			   		    $strAction .= $this->objToolkit->listDeleteButton($objOneGallery->getStrTitle(), $this->getText("galerie_loeschen_frage"), getLinkAdminHref($this->arrModule["modul"], "deleteGallery", "&systemid=".$objOneGallery->getSystemid()));
			   		if($objOneGallery->rightRight3())
		   			    $strAction .= $this->objToolkit->listButton(getLinkAdmin("right", "change", "&systemid=".$objOneGallery->getSystemid(), "", $this->getText("commons_edit_permissions"), getRightsImageAdminName($objOneGallery->getSystemid())));
			   		$strReturn .= $this->objToolkit->listRow2Image(getImageAdmin("icon_gallery.gif"), $objOneGallery->getStrTitle(), $strAction, $intI++);
				}
			}



			if($this->objRights->rightRight3($this->getModuleSystemid($this->arrModule["modul"])))
			    $strReturn .= $this->objToolkit->listRow2Image("", "", getLinkAdmin($this->arrModule["modul"], "newGallery", "", $this->getText("galerie_neu"), $this->getText("galerie_neu"), "icon_new.gif"), $intI++);

			if(uniStrlen($strReturn) != 0)
			    $strReturn = $this->objToolkit->listHeader().$strReturn.$this->objToolkit->listFooter();

			if(count($arrObjGalleries) == 0)
				$strReturn .= $this->getText("galerie_liste_leer");
		}
		else
			$strReturn = $this->getText("fehler_recht");

		return $strReturn.$strJsSyncCode;
	}


// --- Galeriefunktionen --------------------------------------------------------------------------------

    protected function actionEditGallery() {
        return $this->actionNewGallery("edit");
    }

	/**
	 * Creates a form to edit / create a gallery
	 *
	 * @param string $strMode
	 * @return string
	 */
	protected function actionNewGallery($strMode = "new") {
		$strReturn = "";
		if($strMode == "new") {
			//right
			if($this->objRights->rightRight3($this->getModuleSystemid($this->arrModule["modul"]))) {
			    //Build a form
			    $strReturn .= $this->objToolkit->getValidationErrors($this, "saveGallery");
			    $strReturn .= $this->objToolkit->formHeader(getLinkAdminHref($this->arrModule["modul"], "saveGallery"));
                $strReturn .= $this->objToolkit->formInputText("gallery_title", $this->getText("gallery_title"), $this->getParam("gallery_title"));
                $strReturn .= $this->objToolkit->formInputText("gallery_path", $this->getText("commons_path"), $this->getParam("gallery_path"), "inputText", getLinkAdminDialog("folderview", "folderList", "&form_element=gallery_path&folder=/portal/pics", $this->getText("browser"), $this->getText("browser"), "icon_externalBrowser.gif", $this->getText("browser")));
			    $strReturn .= $this->objToolkit->formInputHidden("mode", "new");
			    $strReturn .= $this->objToolkit->formInputHidden("systemid", "0");
			    $strReturn .= $this->objToolkit->formInputSubmit($this->getText("commons_save"));
			    $strReturn .= $this->objToolkit->formClose();

			    $strReturn .= $this->objToolkit->setBrowserFocus("gallery_title");
			}
			else
				$strReturn = $this->getText("fehler_recht");
		}
		elseif ($strMode == "edit") {
			if($this->objRights->rightRight3($this->getSystemid())) 	{
			    //Load the gallery
			    $objGallery = new class_modul_gallery_gallery($this->getSystemid());
			    //Build a form
			    $strReturn .= $this->objToolkit->getValidationErrors($this, "saveGallery");
			    $strReturn .= $this->objToolkit->formHeader(getLinkAdminHref($this->arrModule["modul"], "saveGallery"));
                $strReturn .= $this->objToolkit->formInputText("gallery_title", $this->getText("gallery_title"), $objGallery->getStrTitle());
                $strReturn .= $this->objToolkit->formInputText("gallery_path", $this->getText("commons_path"), $objGallery->getStrPath(), "inputText", getLinkAdminDialog("folderview", "folderList", "&form_element=gallery_path&folder=/portal/pics", $this->getText("browser"), $this->getText("browser"), "icon_externalBrowser.gif", $this->getText("browser")));
			    $strReturn .= $this->objToolkit->formInputHidden("mode", "edit");
			    $strReturn .= $this->objToolkit->formInputHidden("systemid", $this->getSystemid());
			    $strReturn .= $this->objToolkit->formInputSubmit($this->getText("commons_save"));
			    $strReturn .= $this->objToolkit->formClose();

			    $strReturn .= $this->objToolkit->setBrowserFocus("gallery_title");
			}
			else
				$strReturn = $this->getText("fehler_recht");
		}
		return $strReturn;
	}

	/**
	 * Saves a new or modified gallery
	 *
	 * @return string "" in case of success
	 */
	protected function actionSaveGallery() {
		$strReturn = "";

        if(!$this->validateForm())
            return $this->actionNewGallery($this->getParam("mode"));

		//Modus checken
		if($this->getParam("mode") == "new") {
			//Rechte checken
			if($this->objRights->rightRight3($this->getModuleSystemid($this->arrModule["modul"]))) {
			    $objGallery = new class_modul_gallery_gallery();
			    $objGallery->setStrPath($this->getParam("gallery_path"));
			    $objGallery->setStrTitle($this->getParam("gallery_title"));

			    if(!$objGallery->updateObjectToDb())
			        throw new class_exception("Error saving object to db", class_exception::$level_ERROR);

                $this->adminReload(getLinkAdminHref($this->arrModule["modul"]));

			}
			else
				$strReturn = $this->getText("fehler_recht");
		}
		elseif ($this->getParam("mode") == "edit") {
			//Right-Check
			if($this->objRights->rightRight3($this->getSystemid())) {
			    $objGallery = new class_modul_gallery_gallery($this->getSystemid());
			    $objGallery->setStrPath($this->getParam("gallery_path"));
			    $objGallery->setStrTitle($this->getParam("gallery_title"));

				if(!$objGallery->updateObjectToDb())
					throw new class_exception("Error updating object to db", class_exception::$level_ERROR);

                $this->adminReload(getLinkAdminHref($this->arrModule["modul"]));
			}
			else
				$strReturn = $this->getText("fehler_recht");
		}
		return $strReturn;
	}

	/**
	 * Deletes a gallery and all images or shows the warning box
	 *
	 * @return string "" in case of success
	 */
	protected function actionDeleteGallery() {
		$strReturn = "";
		//Rechte-Check
		if($this->objRights->rightRight3($this->getSystemid())) {
            $objGallery = new class_modul_gallery_gallery($this->getSystemid());

			if($objGallery->deleteGalleryRecursive()) {
			    if(!$objGallery->deleteGallery())
			        throw new class_exception($this->getText("galerie_loeschen_fehler"), class_exception::$level_ERROR);
			}
			else
				throw new class_exception($this->getText("galerie_loeschen_fehler"), class_exception::$level_ERROR);

            $this->adminReload(getLinkAdminHref($this->arrModule["modul"]));
		}
		else
			$strReturn .= $this->getText("fehler_recht");

		return $strReturn;
	}

// --- Synchronisierungsfunktionen ----------------------------------------------------------------------


	/**
	 * Synchronizes all galleries available, if rights given
	 *
	 * @return string
	 */
	protected function actionMassSync() {
        $strReturn = "";
		//rights
		if($this->objRights->rightRight1($this->getModuleSystemid($this->arrModule["modul"]))) {

		    //load all galleries
		    $arrGalleries = class_modul_gallery_gallery::getGalleries();
		    $arrSyncs = array( "insert" => 0, "delete" => 0, "update" => 0);
		    foreach($arrGalleries as $objOneGallery) {
		        if($objOneGallery->rightRight1()) {
                    $arrTemp = class_modul_gallery_pic::syncRecursive($objOneGallery->getSystemid(), $objOneGallery->getStrPath());
                    $arrSyncs["insert"] += $arrTemp["insert"];
                    $arrSyncs["delete"] += $arrTemp["delete"];
                    $arrSyncs["update"] += $arrTemp["update"];
		        }
		    }
		    $strReturn = $this->getText("syncro_ende");
			$strReturn .= $this->objToolkit->getTextRow($this->getText("sync_add").$arrSyncs["insert"].$this->getText("sync_del").$arrSyncs["delete"].$this->getText("sync_upd").$arrSyncs["update"]);

			//Flush cache
			$this->flushCompletePagesCache();
		}
		else
			$strReturn = $this->getText("fehler_recht");

		return $strReturn;
	}



// --- Dateifunktionen ----------------------------------------------------------------------------------

	/**
	 * Creates a form to edit an image oder saves the passed data
	 *
	 * @return string "" in case of success
	 */
	protected function actionEditImage() {
		$strReturn = "";
		//Rights?
		if($this->objRights->rightEdit($this->getSystemid())) {
		    if($this->getParam("save") != "" && !$this->validateForm()) {
		        $this->setParam("save", "");
		    }

			//mode? Show form or save image
			if($this->getParam("save") == "") {
			    $strImage = "";
				$objImage = new class_modul_gallery_pic($this->getSystemid());

				//path-navigation
                if($this->strPeAddon == "")
                    $strReturn .= $this->generatePathNavi().basename($objImage->getStrFilename());
                $strReturn .= $this->objToolkit->divider();

				//Build the form
                $strReturn .= $this->objToolkit->getValidationErrors($this, "editImage");
				$strReturn .= $this->objToolkit->formHeader(getLinkAdminHref($this->arrModule["modul"], "editImage"));
                $strReturn .= $this->objToolkit->formInputText("pic_name", $this->getText("pic_name"), $objImage->getStrName());
                $strReturn .= $this->objToolkit->formInputTextArea("pic_subtitle", $this->getText("pic_subtitle"), $objImage->getStrSubtitle());
                $strReturn .= $this->objToolkit->formWysiwygEditor("pic_description", $this->getText("pic_description"), $objImage->getStrDescription(), "minimal");
				$strReturn .= $this->objToolkit->formInputHidden("save", "1");
				$strReturn .= $this->objToolkit->formInputHidden("systemid", $this->getSystemid());
				$strReturn .= $this->objToolkit->formInputSubmit($this->getText("commons_save"));

			    //additional infos and preview of the image
                if(is_file(_realpath_.$objImage->getStrFilename())) {
                    $arrImagesize = getimagesize(_realpath_.$objImage->getStrFilename());
                    $strImage = "<img src=\""._webpath_."/image.php?image=".$objImage->getStrFilename()."&amp;maxWidth=300&amp;maxHeight=300\" />";

                    $strReturn .= $this->objToolkit->divider();
                    $strReturn .= $this->objToolkit->formTextRow($strImage);
                    $strReturn .= $this->objToolkit->formTextRow($this->getText("pic_size").$arrImagesize[0]."x".$arrImagesize[1].$this->getText("pic_size_pixel"));
                    $strReturn .= $this->objToolkit->formTextRow($this->getText("pic_filename").basename($objImage->getStrFilename()));
                    $strReturn .= $this->objToolkit->formTextRow($this->getText("pic_folder").dirname($objImage->getStrFilename()));
                }

                $strReturn .= $this->objToolkit->formInputHidden("peClose", $this->getParam("pe"));
				$strReturn .= $this->objToolkit->formClose();

				$strReturn .= $this->objToolkit->setBrowserFocus("pic_name");
			}
			elseif ($this->getParam("save") == "1") {
				//Update the opbject
				$objImage = new class_modul_gallery_pic($this->getSystemid());
				$objImage->setStrName($this->getParam("pic_name"));
				$objImage->setStrDescription(processWysiwygHtmlContent($this->getParam("pic_description")));
				$objImage->setStrSubtitle($this->getParam("pic_subtitle"));
				if(!$objImage->updateObjectToDb())
				    throw new class_exception($this->getText("bild_speichern_fehler"), class_exception::$level_ERROR);

				//flush cache
				$this->flushCompletePagesCache();

                $this->adminReload(getLinkAdminHref($this->arrModule["modul"], "showGallery", "systemid=".$this->getPrevId() .($this->getParam("pe") == "" ? "" : "&peClose=".$this->getParam("pe")) ));
			}
		}
		else
			$strReturn = $this->getText("fehler_recht");
		return $strReturn;
	}


	/**
	 * Shows all files & folders under the current systemid
	 *
	 * @return string
	 */
	protected function actionShowGallery() {
		$strReturn = "";
		//rights
		if($this->objRights->rightView($this->getSystemid())) {
		    //path navi
            if($this->strPeAddon == "")
                $strReturn .= $this->generatePathnavi();

            //Since we can crossreference the filemanager, provide an upload-form
            $arrPath = $this->getPathArray();
            $objTempPic = new class_modul_gallery_pic($this->getSystemid());
            $objFmRepo = class_modul_filemanager_repo::getRepoForForeignId($arrPath[0]);
            $strFmFolder = substr($objTempPic->getStrFilename(), strpos($objTempPic->getStrFilename(), $objFmRepo->getStrPath()) + strlen($objFmRepo->getStrPath()));

            //Build the upload form
            if($objFmRepo->rightRight1()) {

                $strDialog = $this->objToolkit->formInputText("folderName", $this->getText("ordner_name", "filemanager"));
                $strReturn .= "<script type=\"text/javascript\">\n
                                function init_fm_newfolder_dialog() {
                                    jsDialog_1.setTitle('".$this->getText("ordner_anlegen_dialogHeader", "filemanager")."');
                                    jsDialog_1.setContent('".uniStrReplace(array("\r\n", "\n"), "", addslashes($strDialog))."',
                                                          '".$this->getText("ordner_anlegen_dialogButton", "filemanager")."',
                                                          'javascript:KAJONA.admin.filemanager.createFolder(\'folderName\', \'".$objFmRepo->getSystemid()."\', \'".$strFmFolder."\', \'gallery\', \'partialSyncGallery\', \'".$this->getSystemid()."\' ); jsDialog_1.hide();');
                                            jsDialog_1.init(); }\n
                              ";

                $strReturn .= "</script>";
                $strReturn .= $this->objToolkit->jsDialog(1);
                $strReturn .= getLinkAdminManual("href=\"javascript:init_fm_newfolder_dialog();\"", $this->getText("ordner_anlegen", "filemanager"), "", "", "", "", "", "inputSubmit");

				$strReturn .= $this->objToolkit->formInputHidden("flashuploadSystemid", $objFmRepo->getSystemid());
				$strReturn .= $this->objToolkit->formInputHidden("flashuploadFolder", $strFmFolder);

	            $strReturn .= $this->objToolkit->formInputUploadFlash("filemanager_upload", $this->getText("filemanager_upload", "filemanager", "admin"), $objFmRepo->getStrUploadFilter(), true);

				$strReturn .= "<script type=\"text/javascript\">
					function kajonaUploaderCallback() {
						/* KAJONA.admin.ajax.genericAjaxCall('gallery', 'massSyncGallery', '', { */
						KAJONA.admin.ajax.genericAjaxCall('gallery', 'partialSyncGallery', '".$this->getSystemid()."', {
							success : function(o) {
								location.reload();
							},
							failure : function(o) {
								KAJONA.admin.statusDisplay.messageError(\"<b>Request failed!</b><br />\" + o.responseText);
							}
						}
						);
					}
                </script>";

				$strReturn .= "<br />";
            }


			//Load all files
			$arrFiles = class_modul_gallery_pic::loadFilesDB($this->getSystemid());

			$strListID = generateSystemid();
			$strReturn .= $this->objToolkit->dragableListHeader($strListID);
			//maybe, a link one level up is neede
			$strTemp = $this->getPrevId($this->getSystemid());
			$intI = 0;
			if($strTemp != "0" && $strTemp != $this->getModuleSystemid($this->arrModule["modul"])) {
				$strReturn .= $this->objToolkit->listRow3("..", "", $this->objToolkit->listButton(getLinkAdmin($this->arrModule["modul"], "showGallery", "&systemid=".$strTemp.$this->strPeAddon, "", $this->getText("commons_one_level_up"), "icon_folderActionLevelup.gif")), getImageAdmin("icon_folderClosed.gif"), $intI++);
			}

			if(count($arrFiles) > 0) {
				foreach($arrFiles as $objOneFile) {
					//create a icon
				 	if($objOneFile->getIntType() == 0) 	{
				 		$arrTemp = $this->objToolkit->mimeType(basename($objOneFile->getStrFilename()));
				 		$strPic = $arrTemp[2];
				 		$strText = $arrTemp[0];
				 	}
				 	else {
				 		$strPic = "icon_folderClosed.gif";
				 		$strText = "Ordner";
				 	}

				 	//And build the row itself
				 	$strName = uniStrTrim($objOneFile->getStrName(), 30)." (".uniStrTrim(basename($objOneFile->getStrFilename()), 25).")";
				 	$strCenter = ($objOneFile->getIntType() == 0 ? bytesToString($objOneFile->getIntSize()) ." - ": "") ;
				 	$strCenter .= ($objOneFile->getIntType() == 0 ? $objOneFile->getIntHits()." Hits": "");

				    //ratings available?
                    if($objOneFile->getIntType() != 1) {
                        $floatRating = $objOneFile->getFloatRating();
                        if ($floatRating !== null) {
                            $strCenter .= " - ".$floatRating;
                        }
                    }

			   		//If folder, a link to open
			   		$strAction = "";
			   		if($objOneFile->getIntType() == 1 && $objOneFile->rightView())
			   			$strAction .= $this->objToolkit->listButton(getLinkAdmin($this->arrModule["modul"], "showGallery", "&systemid=".$objOneFile->getSystemid().$this->strPeAddon, "", $this->getText("ordner_oeffnen"), "icon_folderActionOpen.gif"));

			   		if($this->objRights->rightEdit($objOneFile->getSystemid())) {
			   		    if($objOneFile->getIntType() == 1) {
			   			    $strAction .= $this->objToolkit->listButton(getLinkAdmin($this->arrModule["modul"], "editImage", "&systemid=".$objOneFile->getSystemid().$this->strPeAddon, "", $this->getText("ordner_bearbeiten"), "icon_pencil.gif"));
                        }
			   			else {
                            //the filemanager edit action
                            if($objFmRepo != null) {
                                $strAction .= $this->objToolkit->listButton(getLinkAdminDialog("filemanager", "imageDetails", "&systemid=".$objFmRepo->getSystemid()."&folder=".$strFmFolder."&file=".basename($objOneFile->getStrFilename())."&galleryId=".$this->getSystemid()."&direct=true", "", $this->getText("bild_bearbeiten"), "icon_crop.gif"));
                            }
			   			    $strAction .= $this->objToolkit->listButton(getLinkAdmin($this->arrModule["modul"], "editImage", "&systemid=".$objOneFile->getSystemid().$this->strPeAddon, "", $this->getText("image_properties"), "icon_pencil.gif"));
                        }
				   		//$strAction .= $this->objToolkit->listButton(getLinkAdmin($this->arrModule["modul"], "sortUp", "&systemid=".$objOneFile->getSystemid().$this->strPeAddon, "", $this->getText("sortierung_hoch"), "icon_arrowUp.gif"));
				   		//$strAction .= $this->objToolkit->listButton(getLinkAdmin($this->arrModule["modul"], "sortDown", "&systemid=".$objOneFile->getSystemid().$this->strPeAddon, "", $this->getText("sortierung_runter"), "icon_arrowDown.gif"));
			   		}
                    if($this->objRights->rightDelete($objOneFile->getSystemid())) {
                        if($objOneFile->getIntType() == 0)
                            $strAction .= $this->objToolkit->listDeleteButton($strName, $this->getText("datei_loeschen_frage"), "javascript:KAJONA.admin.ajax.deleteFile(\'".$objFmRepo->getSystemid()."\', \'".$strFmFolder."\', \'".basename($objOneFile->getStrFilename())."\', \'gallery\', \'partialSyncGallery\', \'".$this->getSystemid()."\')");
                        else
                            $strAction .= $this->objToolkit->listDeleteButton($strName, $this->getText("datei_loeschen_frage"), "javascript:KAJONA.admin.ajax.deleteFolder(\'".$objFmRepo->getSystemid()."\', \'".$strFmFolder."/".basename($objOneFile->getStrFilename())."\', \'gallery\', \'massSyncGallery\')");
                    }

                    if($this->objRights->rightEdit($objOneFile->getSystemid()) && $this->strPeAddon == "")
                        $strAction .= $this->objToolkit->listStatusButton($objOneFile->getSystemid());

			   		if($this->objRights->rightRight($objOneFile->getSystemid()) && $this->strPeAddon == "")
			   			$strAction .= $this->objToolkit->listButton(getLinkAdmin("right", "change", "&systemid=".$objOneFile->getSystemid(), "", $this->getText("commons_edit_permissions"), getRightsImageAdminName($objOneFile->getSystemid())));

                    // if no folder, attach a thumbnail-tooltip
                    if ($objOneFile->getIntType() == 1) {
                        $strReturn .= $this->objToolkit->listRow3($strName, $strCenter, $strAction, getImageAdmin($strPic), $intI++, $objOneFile->getSystemid());
                    } else {
    			   		$strImage = "<div class=\'loadingContainer\'><img src=\\'"._webpath_."/image.php?image=".$objOneFile->getStrFilename()."&amp;maxWidth=100&amp;maxHeight=100\\' /></div>";

    					$strReturn .= $this->objToolkit->listRow3($strName, $strCenter, $strAction, getImageAdmin($strPic, $strImage, true), $intI++, $objOneFile->getSystemid());
                    }
				}
			}
			else
				$strReturn .= $this->objToolkit->listRow2($this->getText("liste_bilder_leer"), "", $intI++);

			$strReturn .= $this->objToolkit->dragableListFooter($strListID);
		}
		else
			$strReturn = $this->getText("fehler_recht");

		return $strReturn;
	}



    private function actionSyncInternal() {
        if($this->objRights->rightRight1($this->getSystemid()) ) {
            $arrPathIds = $this->getPathArray();
            $objGallery = new class_modul_gallery_gallery(array_shift($arrPathIds));
            $arrTemp = class_modul_gallery_pic::syncRecursive($objGallery->getSystemid(), $objGallery->getStrPath());
        }
    }


	/**
	 * Generates a pathnavigation
	 *
	 * @return string
	 */
	private function generatePathNavi() {
	    $arrEntries = array();
	    $arrPathIds = $this->getPathArray();
	    $arrEntries[] = getLinkAdmin($this->arrModule["modul"], "", "", "&nbsp;/&nbsp;");
	    //the first one is the repo itself
	    $objGallery = new class_modul_gallery_gallery(array_shift($arrPathIds));
	    $arrEntries[] = getLinkAdmin($this->arrModule["modul"], "showGallery", "&systemid=".$objGallery->getSystemid(), $objGallery->getStrTitle());
	    foreach ($arrPathIds as $strOneId) {
	        $objPic = new class_modul_gallery_pic($strOneId);
	    	$arrEntries[] = getLinkAdmin($this->arrModule["modul"], "showGallery", "&systemid=".$strOneId, $objPic->getStrName());
	    }
	    //if editing a folder / image, remove the last one
	    if($this->getAction() == "editImage")
	       array_pop($arrEntries);

	    return $this->objToolkit->getPathNavigation($arrEntries);
	}


}

?>