<?php
/*"******************************************************************************************************
*   (c) 2004-2006 by MulchProductions, www.mulchprod.de                                                 *
*   (c) 2007-2009 by Kajona, www.kajona.de                                                              *
*       Published under the GNU LGPL v2.1, see /system/licence_lgpl.txt                                 *
*-------------------------------------------------------------------------------------------------------*
*	$Id$                             *
********************************************************************************************************/


//Base class & interface
include_once(_adminpath_."/class_admin.php");
include_once(_adminpath_."/interface_admin.php");

//needed classes
include_once(_systempath_."/class_modul_downloads_archive.php");
include_once(_systempath_."/class_modul_downloads_file.php");
include_once(_systempath_."/class_modul_downloads_logbook.php");



/**
 * Admin-Class of the downloads-module. Used to sync the archives with the filesystem and to define file-properties
 *
 * @package modul_downloads
 */
class class_modul_downloads_admin extends class_admin implements interface_admin {

	/**
	 * Construcut
	 *
	 */
	public function __construct() {
        $arrModule = array();
		$arrModule["name"] 				= "modul_downloads";
		$arrModule["author"] 			= "sidler@mulchprod.de";
		$arrModule["moduleId"] 			= _downloads_modul_id_;
		$arrModule["table"] 			= _dbprefix_."downloads_archive";
		$arrModule["table2"] 			= _dbprefix_."downloads_file";
		$arrModule["table3"]			= _dbprefix_."downloads_log";
		$arrModule["modul"]				= "downloads";

		//Base class
		parent::__construct($arrModule);
	}

	/**
	 * Action-block. Controlles the further behaviour of the class
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

    		if($strAction == "newArchive")
    			$strReturn = $this->actionNewArchive("new");

    		if($strAction == "editArchive")
    			$strReturn = $this->actionNewArchive("edit");

    		if($strAction == "saveArchive") {
    		    if($this->validateForm()) {
    			    $strReturn = $this->actionSaveArchive();
    			    if($strReturn == "")
                        $this->adminReload(getLinkAdminHref($this->arrModule["modul"]));
    		    }
    		    else {
    		        if($this->getParam("mode") == "new")
    		            $strReturn .= $this->actionNewArchive("new");
    		        else
    		            $strReturn .= $this->actionNewArchive("edit");

    		    }
    		}

    		if($strAction == "massSync")
    			$strReturn .= $this->actionMassSync();

    		if($strAction == "showArchive")
    			$strReturn = $this->actionShowArchive();

    		if($strAction == "deleteArchive") {
    			$strReturn = $this->actionDeleteArchive();
    			if($strReturn == "")
                    $this->adminReload(getLinkAdminHref($this->arrModule["modul"]));
    		}

    		if($strAction == "editFile") {
    			$strReturn = $this->actionEditDetails();
    			if($strReturn == "")
    			    $this->adminReload(getLinkAdminHref($this->arrModule["modul"], "showArchive", "systemid=".$this->getPrevId()));
    		}

    		if($strAction == "logbook")
    			$strReturn = $this->actionViewLogbook();

    		if($strAction == "deleteLogbook") {
    			$strReturn = $this->actionDeleteLogbook();
    			if($strReturn == "")
    			    $this->adminReload(getLinkAdminHref($this->arrModule["modul"], "logbook"));
    		}

    		if($strAction == "sortUp") {
    			$strReturn = $this->actionSort("up");
    			$this->adminReload(getLinkAdminHref($this->arrModule["modul"], "showArchive", "systemid=".$this->getPrevId()));
    		}

    		if($strAction == "sortDown") {
    			$strReturn = $this->actionSort("down");
    		    $this->adminReload(getLinkAdminHref($this->arrModule["modul"], "showArchive", "systemid=".$this->getPrevId()));
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
	    $arrReturn[] = array("edit", getLinkAdmin($this->arrModule["modul"], "newArchive", "", $this->getText("archiv_neu"), "", "", true, "adminnavi"));
	    $arrReturn[] = array("", "");
     	$arrReturn[] = array("right1", getLinkAdmin($this->arrModule["modul"], "massSync", "", $this->getText("archive_masssync"), "", "", true, "adminnavi"));
	    $arrReturn[] = array("", "");
  	    $arrReturn[] = array("right3", getLinkAdmin($this->arrModule["modul"], "logbook", "", $this->getText("logbuch"), "", "", true, "adminnavi"));
		return $arrReturn;
    }


    protected function getRequiredFields() {
        $strAction = $this->getAction();
        $arrReturn = array();
        if($strAction == "saveArchive") {
            $arrReturn["archive_title"] = "string";
            $arrReturn["archive_path"] = "folder";
        }
        if($strAction == "editFile") {
            $arrReturn["downloads_name"] = "string";
            $arrReturn["downloads_max_kb"] = "number";
        }

        return $arrReturn;
    }



// --- ListenFunktionen ---------------------------------------------------------------------------------


	/**
	 * Returns a list of all archives
	 *
	 * @return string
	 */
	private function actionList() {
		$strReturn = "";
        $strJsSyncCode = "";
		//Check rights
		if($this->objRights->rightView($this->getModuleSystemid($this->arrModule["modul"]))) {
			//Load archves
			$arrObjArchives = class_modul_downloads_archive::getAllArchives();
			$intI = 0;

			//initial js-code needed for common tasks
			$strJsSyncCode .= $this->objToolkit->jsDialog(3);
			$strJsSyncCode .= $this->objToolkit->jsDialog(0);
			$strJsSyncCode .= "<script type=\"text/javascript\">
                function archive_init_screenlock_dialog() { jsDialog_3.init(); }
                function archive_hide_screenlock_dialog() { jsDialog_3.hide(); }

                function syncArchive(strSystemid) {
                    archive_init_screenlock_dialog();

                    kajonaAdminAjax.genericAjaxCall('downloads', 'syncArchive', strSystemid, {
						    success : function(o) {
						        archive_hide_screenlock_dialog();
						        jsDialog_0.setTitle('".$this->getText("syncDialogHeader")."');
						        jsDialog_0.setContentRaw(o.responseText+'<br /><br /><input type=\"submit\" name=\"closeButton\" value=\"".$this->getText("hideSyncDialog")."\" class=\"inputSubmitShort\" onclick=\"jsDialog_0.hide(); return false;\" /><br />');
						        jsDialog_0.init();
						        kajonaStatusDisplay.displayXMLMessage(o.responseText);
						    },
						    failure : function(o) {
						        archive_hide_screenlock_dialog();
						        kajonaStatusDisplay.messageError(\"<b>request failed!!!</b>\"
						                + o.responseText);
						    }
						}
					);
                }
            </script>";


			foreach($arrObjArchives as $arrOneObjArchive) {
				if($this->objRights->rightView($arrOneObjArchive->getSystemid())) {
					$strAction = "";
					if($this->objRights->rightView($arrOneObjArchive->getSystemid()))
			   		    $strAction .= $this->objToolkit->listButton(getLinkAdmin($this->arrModule["modul"], "showArchive", "&systemid=".$arrOneObjArchive->getSystemid(), "", $this->getText("archiv_anzeigen"), "icon_folderActionOpen.gif"));
			   		if($this->objRights->rightRight1($arrOneObjArchive->getSystemid())) {
                        $strAction .= $this->objToolkit->listButton(getLinkAdminManual("href=\"javascript:syncArchive('".$arrOneObjArchive->getSystemid()."');\"",  "", $this->getText("archiv_syncro"), "icon_sync.gif"));
                    }
			   		if($this->objRights->rightEdit($arrOneObjArchive->getSystemid()))
			   		    $strAction .= $this->objToolkit->listButton(getLinkAdmin($this->arrModule["modul"], "editArchive", "&systemid=".$arrOneObjArchive->getSystemid(), "", $this->getText("archiv_bearbeiten"), "icon_pencil.gif"));
			   		if($this->objRights->rightDelete($arrOneObjArchive->getSystemid()))
			   		    $strAction .= $this->objToolkit->listDeleteButton($arrOneObjArchive->getTitle(), $this->getText("archiv_loeschen_frage"), getLinkAdminHref($this->arrModule["modul"], "deleteArchive", "&systemid=".$arrOneObjArchive->getSystemid()));
			   		if($this->objRights->rightRight($arrOneObjArchive->getSystemid()))
		   			    $strAction .= $this->objToolkit->listButton(getLinkAdmin("right", "change", "&systemid=".$arrOneObjArchive->getSystemid(), "", $this->getText("archiv_rechte"), getRightsImageAdminName($arrOneObjArchive->getSystemid())));
			   		$strReturn .= $this->objToolkit->listRow2Image(getImageAdmin("icon_downloads.gif"), $arrOneObjArchive->getTitle(), $strAction, $intI++);
				}
			}
			if($this->objRights->rightEdit($this->getModuleSystemid($this->arrModule["modul"])))
			    $strReturn .= $this->objToolkit->listRow2Image("", "", getLinkAdmin($this->arrModule["modul"], "newArchive", "", $this->getText("archiv_neu"), $this->getText("archiv_neu"), "icon_blank.gif"), $intI++);

			if(uniStrlen($strReturn) != 0)
			    $strReturn = $this->objToolkit->listHeader().$strReturn.$this->objToolkit->listFooter();

		    if(count($arrObjArchives) == 0)
				$strReturn .= $this->objToolkit->getTextRow($this->getText("liste_leer_archive"));
		}
		else
			$strReturn = $this->getText("fehler_recht");

		return $strReturn.$strJsSyncCode;
	}

	/**
	 * Creates a list if files & folders given under the current Systemid
	 *
	 * @return string
	 */
	private function actionShowArchive() {
		$strReturn = "";
		//Rights
		if($this->objRights->rightView($this->getSystemid())) {
		    $strListId = generateSystemid();
		    //path navi
		    $strReturn .= $this->generatePathnavi();

            //Since we can crossreference the filemanager, provide an upload-form
            $arrPath = $this->getPathArray();
            $objTempFile = new class_modul_downloads_file($this->getSystemid());
            $objFmRepo = class_modul_filemanager_repo::getRepoForForeignId($arrPath[0]);
            $strFmFolder = substr($objTempFile->getFilename(), strpos($objTempFile->getFilename(), $objFmRepo->getStrPath()) + strlen($objFmRepo->getStrPath()));

            //Build the upload form
            if($objFmRepo->rightRight1()) {

                $strDialog = $this->objToolkit->formInputText("folderName", $this->getText("ordner_name", "filemanager"));
                $strReturn .= "<script type=\"text/javascript\">\n
                                function init_fm_newfolder_dialog() {
                                    jsDialog_1.setTitle('".$this->getText("ordner_anlegen_dialogHeader", "filemanager")."');
                                    jsDialog_1.setContent('".uniStrReplace(array("\r\n", "\n"), "", addslashes($strDialog))."',
                                                          '".$this->getText("ordner_anlegen_dialogButton", "filemanager")."',
                                                          'javascript:filemanagerCreateFolder(\'folderName\', \'".$objFmRepo->getSystemid()."\', \'".$strFmFolder."\', \'downloads\', \'massSyncArchive\' ); jsDialog_1.hide();');
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
						kajonaAdminAjax.genericAjaxCall('downloads', 'massSyncArchive', '', {
							success : function(o) {
								location.reload();
							},
							failure : function(o) {
								kajonaStatusDisplay.messageError(\"<b>request failed!!!</b>\" + o.responseText);
							}
						}
						);
					}
                </script>";

				$strReturn .= "<br />";

            }


			//Load files
			$arrFiles = class_modul_downloads_file::getFilesDB($this->getSystemid());
			$strReturn .= $this->objToolkit->dragableListHeader($strListId);
			//linkto jump one level up
			$intI = 0;
			if($this->getPrevId() != "0") {
				$strReturn .= $this->objToolkit->listRow3("..", "", $this->objToolkit->listButton(getLinkAdmin($this->arrModule["modul"], "showArchive", "&systemid=".$this->getPrevId(), "", $this->getText("ordner_hoch"), "icon_folderActionLevelup.gif")), getImageAdmin("icon_folderClosed.gif"), $intI++);
			}

			if(count($arrFiles) > 0) {
				foreach($arrFiles as $objOneFile) {
				 	//get mimes
				 	if($objOneFile->getType() == 0) {
				 		$arrTemp =  $this->objToolkit->mimeType(basename($objOneFile->getFilename()));
				 		$strImage = $arrTemp[2];
				 		$strText = $arrTemp[0];
				 	}
				 	else {
				 		$strImage= "icon_folderClosed.gif";
				 		$strText = "Ordner";
				 	}


					//And build the row itself
				 	$strName = uniStrTrim($objOneFile->getName(), 30)." (".uniStrTrim(basename($objOneFile->getFilename()), 25).")";
				 	$strCenter = ($objOneFile->getType() == 0 ? bytesToString($objOneFile->getSize()) ." - ": "") ;
				 	$strCenter .= ($objOneFile->getType() == 0 ? $objOneFile->getHits()." Hits": "");

				 	//ratings available?
				 	try {
				        $objMdlRating = class_modul_system_module::getModuleByName("rating");
				        if($objMdlRating != null && $objOneFile->getType() != 1) {
				 	        include_once(_systempath_."/class_modul_rating_rate.php");
				 	        $objRating = class_modul_rating_rate::getRating($objOneFile->getSystemid());
				 	        if($objRating != null)
				 	            $strCenter .= " - ".$objRating->getFloatRating();
				 	        else
				 	            $strCenter .= " - 0.0";
				        }

				 	}
				 	catch (class_exception $objException) { }

			   		//If folder, a link to open
			   		$strAction = "";
			   		if($objOneFile->getType() == 1 && $this->objRights->rightView($objOneFile->getSystemid()))
			   			$strAction .= $this->objToolkit->listButton(getLinkAdmin($this->arrModule["modul"], "showArchive", "&systemid=".$objOneFile->getSystemid(), "", $this->getText("ordner_oeffnen"), "icon_folderActionOpen.gif"));

			   		if($this->objRights->rightEdit($objOneFile->getSystemid())) {
			   			$strAction .= $this->objToolkit->listButton(getLinkAdmin($this->arrModule["modul"], "editFile", "&systemid=".$objOneFile->getSystemid(), "", $this->getText("datei_bearbeiten"), "icon_pencil.gif"));
				   		$strAction .= $this->objToolkit->listButton(getLinkAdmin($this->arrModule["modul"], "sortUp", "&systemid=".$objOneFile->getSystemid(), "", $this->getText("sortierung_hoch"), "icon_arrowUp.gif"));
				   		$strAction .= $this->objToolkit->listButton(getLinkAdmin($this->arrModule["modul"], "sortDown", "&systemid=".$objOneFile->getSystemid(), "", $this->getText("sortierung_runter"), "icon_arrowDown.gif"));
				   		$strAction .= $this->objToolkit->listStatusButton($objOneFile->getSystemid());
			   		}
                    if($this->objRights->rightDelete($objOneFile->getSystemid())) {
                        if($objOneFile->getType() == 0)
                            $strAction .= $this->objToolkit->listDeleteButton($strName, $this->getText("datei_loeschen_frage"), "javascript:kajonaAdminAjax.deleteFile(\'".$objFmRepo->getSystemid()."\', \'".$strFmFolder."\', \'".basename($objOneFile->getFilename())."\', \'downloads\', \'massSyncArchive\')");
                        else
                            $strAction .= $this->objToolkit->listDeleteButton($strName, $this->getText("datei_loeschen_frage"), "javascript:kajonaAdminAjax.deleteFolder(\'".$objFmRepo->getSystemid()."\', \'".$strFmFolder."/".basename($objOneFile->getFilename())."\', \'downloads\', \'massSyncArchive\')");
                    }

			   		if($this->objRights->rightRight($objOneFile->getSystemid()))
			   			$strAction .= $this->objToolkit->listButton(getLinkAdmin("right", "change", "&systemid=".$objOneFile->getSystemid(), "", $this->getText("archiv_rechte"), getRightsImageAdminName($objOneFile->getSystemid())));

					$strReturn .= $this->objToolkit->listRow3($strName, $strCenter, $strAction, getImageAdmin($strImage, $strText), $intI++, $objOneFile->getSystemid());
				}
			}
			else
			    $strReturn .= $this->objToolkit->listRow2($this->getText("liste_leer_dl"), "", $intI++);

			$strReturn .= $this->objToolkit->dragableListFooter($strListId);
		}
		else
			$strReturn = $this->getText("fehler_recht");
		return $strReturn;
	}

// --- Archivfunktionen ---------------------------------------------------------------------------------

	/**
	 * Returns a warning or deletes an archive and all its childs
	 *
	 * @return string "" in case of success
	 */
	private function actionDeleteArchive() {
		$strReturn = "";
		//Rights
		if($this->objRights->rightDelete($this->getSystemid())) {
			if(class_modul_downloads_archive::deleteArchiveRecursive($this->getSystemid())) {
				if(!class_modul_downloads_archive::deleteArchive($this->getSystemid())) {
				    throw new class_exception($this->getText("archiv_loeschen_fehler"), class_exception::$level_ERROR);
				}
		    }
		}
		else
			$strReturn .= $this->getText("fehler_recht");

		return $strReturn;
	}

	/**
	 * Creates a form to edit or create a archive
	 *
	 * @param unknown_type $strMode
	 * @return unknown
	 */
	private function actionNewArchive($strMode = "new") {
	    $strReturn = "";
		if($strMode == "new") {
			//right
			if($this->objRights->rightEdit($this->getModuleSystemid($this->arrModule["modul"]))) {
			    //Build a form
			    $strReturn .= $this->objToolkit->getValidationErrors($this);
			    $strReturn .= $this->objToolkit->formHeader(getLinkAdminHref("downloads", "saveArchive"));
                $strReturn .= $this->objToolkit->formInputText("archive_title", $this->getText("archive_title"), $this->getParam("archive_title"));
                $strReturn .= $this->objToolkit->formInputText("archive_path", $this->getText("archive_path"), $this->getParam("archive_path"), "inputText", getLinkAdminPopup("folderview", "folderList", "&form_element=archive_path&folder=/portal/downloads", $this->getText("browser"), $this->getText("browser"), "icon_externalBrowser.gif", 500, 500, "ordneransicht"));
			    $strReturn .= $this->objToolkit->formInputHidden("mode", "new");
			    $strReturn .= $this->objToolkit->formInputHidden("systemid", "0");
			    $strReturn .= $this->objToolkit->formInputSubmit($this->getText("speichern"));
			    $strReturn .= $this->objToolkit->formClose();
			}
			else
				$strReturn = $this->getText("fehler_recht");
		}
		elseif ($strMode == "edit") {
			if($this->objRights->rightEdit($this->getSystemid())) 	{
			    //Load the gallery
			    $objArchive = new class_modul_downloads_archive($this->getSystemid());
			    //Build a form
			    $strReturn .= $this->objToolkit->getValidationErrors($this);
			    $strReturn .= $this->objToolkit->formHeader(getLinkAdminHref("downloads", "saveArchive"));
                $strReturn .= $this->objToolkit->formInputText("archive_title", $this->getText("archive_title"), $objArchive->getTitle());
                $strReturn .= $this->objToolkit->formInputText("archive_path", $this->getText("archive_path"), $objArchive->getPath(), "inputText", getLinkAdminPopup("folderview", "folderList", "&form_element=archive_path&folder=/portal/downloads", $this->getText("browser"), $this->getText("browser"), "icon_externalBrowser.gif", 500, 500, "ordneransicht"));
			    $strReturn .= $this->objToolkit->formInputHidden("mode", "edit");
			    $strReturn .= $this->objToolkit->formInputHidden("systemid", $this->getSystemid());
			    $strReturn .= $this->objToolkit->formInputSubmit($this->getText("speichern"));
			    $strReturn .= $this->objToolkit->formClose();
			}
			else
				$strReturn = $this->getText("fehler_recht");
		}
		return $strReturn;
	}


	/**
	 * Saves a new or modified archive
	 *
	 * @return string "" in case of success
	 */
	private function actionSaveArchive() {
		$strReturn = "";
		//Modus checken
		if($this->getParam("mode") == "new") {
			//Rechte checken
			if($this->objRights->rightView($this->getModuleSystemid($this->arrModule["modul"]))) {
			    $objArchive = new class_modul_downloads_archive("");
			    $objArchive->setPath($this->getParam("archive_path"));
			    $objArchive->setTitle($this->getParam("archive_title"));
			    if(!$objArchive->saveObjectToDb())
			        throw new class_exception("Error saving object to db", class_exception::$level_ERROR);
			}
			else
				$strReturn = $this->getText("fehler_recht");
		}
		elseif ($this->getParam("mode") == "edit") {
			//Right-Check
			if($this->objRights->rightEdit($this->getSystemid())) {

			    $objArchive = new class_modul_downloads_archive($this->getSystemid());
			    $objArchive->setPath($this->getParam("archive_path"));
			    $objArchive->setTitle($this->getParam("archive_title"));

				if(!$objArchive->updateObjectToDB())
					throw new class_exception("Error updating object to db", class_exception::$level_ERROR);
			}
			else
				$strReturn = $this->getText("fehler_recht");
		}
		return $strReturn;
	}

// --- Synchronisierungsfunktionen ----------------------------------------------------------------------


	/**
	 * Synchronizes all archives available, if rights given
	 *
	 * @return string
	 */
	private function actionMassSync() {
        $strReturn = "";
		//rights
		if($this->objRights->rightRight1($this->getModuleSystemid($this->arrModule["modul"]))) {

		    //load all galleries
		    $arrArchives = class_modul_downloads_archive::getAllArchives();
		    $arrSyncs = array( "insert" => 0, "delete" => 0, "update" => 0);
		    foreach($arrArchives as $objOneArchive) {
		        if($objOneArchive->rightRight1()) {
                    $arrTemp = class_modul_downloads_file::syncRecursive($objOneArchive->getSystemid(), $objOneArchive->getPath());
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
	 * Shows a form to edit the details or saves the passed values
	 *
	 * @return string "" in case of success
	 */
	private function actionEditDetails() {
		$strReturn = "";
		//Rights
		if($this->objRights->rightEdit($this->getSystemid())) {
			//form or update
			//validate the form
			$bitErrors = false;
			if($this->getParam("save") == "1" && !$this->validateForm()) {
			     $this->setParam("save", "");
			     $bitErrors = true;
			}

			if($this->getParam("save") == "") {
				//Crete the form
				$strReturn .= $this->generatePathNavi();
				$objFile = new class_modul_downloads_file($this->getSystemid());
				if($bitErrors)
                    $strReturn .= $this->objToolkit->getValidationErrors($this);
				$strReturn .= $this->objToolkit->formHeader(getLinkAdminHref("downloads", "editFile"));
                $strReturn .= $this->objToolkit->formInputText("downloads_name", $this->getText("downloads_name"), $objFile->getName());
                $strReturn .= $this->objToolkit->formWysiwygEditor("downloads_description", $this->getText("downloads_description"), $objFile->getDescription(), "minimal");
                if($objFile->getType() == 0)
                    $strReturn .= $this->objToolkit->formInputText("downloads_max_kb", $this->getText("downloads_max_kb"), $objFile->getMaxKb());
                else
				    $strReturn .= $this->objToolkit->formInputHidden("downloads_max_kb", "0");
				$strReturn .= $this->objToolkit->formInputHidden("save", "1");
				$strReturn .= $this->objToolkit->formInputHidden("systemid", $this->getSystemid());
				$strReturn .= $this->objToolkit->formInputSubmit($this->getText("speichern"));
				$strReturn .= $this->objToolkit->formClose();
			}
			elseif ($this->getParam("save") == "1") {
				//Update the record
				$objFile = new class_modul_downloads_file($this->getSystemid());
				$objFile->setName($this->getParam("downloads_name"));
				$objFile->setDescription($this->getParam("downloads_description"));
				$objFile->setMaxKb($this->getParam("downloads_max_kb"));

				if(!$objFile->updateObjectToDB())
				    throw new class_exception($this->getText("datei_speichern_fehler"), class_exception::$level_ERROR);
			}
		}
		else
			$strReturn = $this->getText("fehler_recht");

		return $strReturn;
	}

// --- Logbuch ------------------------------------------------------------------------------------------

	/**
	 * Show a logbook of all downloads
	 *
	 * @return string
	 */
	private function actionViewLogbook() {
		$strReturn = "";
		if($this->objRights->rightRight3($this->getModuleSystemid($this->arrModule["modul"]))) {

		    $intNrOfRecordsPerPage = 25;

		    $strReturn .= $this->objToolkit->getTextRow(getLinkAdmin("downloads", "deleteLogbook", "", $this->getText("logbuch_loeschen_link"), "")."<br />");

		    include_once(_systempath_."/class_array_section_iterator.php");
		    $objLogbook = new class_modul_downloads_logbook();
		    $objArraySectionIterator = new class_array_section_iterator($objLogbook->getLogbookDataCount());
		    $objArraySectionIterator->setIntElementsPerPage($intNrOfRecordsPerPage);
		    $objArraySectionIterator->setPageNumber((int)($this->getParam("pv") != "" ? $this->getParam("pv") : 1));
		    $objArraySectionIterator->setArraySection($objLogbook->getLogbookSection($objArraySectionIterator->calculateStartPos(), $objArraySectionIterator->calculateEndPos()));

		    $arrLogsRaw = $objArraySectionIterator->getArrayExtended();
		    $arrPageViews = $this->objToolkit->getPageview($arrLogsRaw, (int)($this->getParam("pv") != "" ? $this->getParam("pv") : 1), "downloads", "logbook", "", $intNrOfRecordsPerPage);

            $arrLogsRaw = $arrPageViews["elements"];
		    $arrLogs = array();
		    foreach($arrLogsRaw as $intKey => $arrOneLog) {
		        $arrLogs[$intKey][0] = $arrOneLog["downloads_log_id"];
		        $arrLogs[$intKey][1] = timeToString($arrOneLog["downloads_log_date"]);
		        $arrLogs[$intKey][2] = $arrOneLog["downloads_log_file"];
		        $arrLogs[$intKey][3] = $arrOneLog["downloads_log_user"];
		        $arrLogs[$intKey][4] = $arrOneLog["downloads_log_ip"];
		    }
			//Create a data-table
			$arrHeader = array();
            $arrHeader[0] = $this->getText("header_id");
            $arrHeader[1] = $this->getText("header_date");
            $arrHeader[2] = $this->getText("header_file");
            $arrHeader[3] = $this->getText("header_user");
            $arrHeader[4] = $this->getText("header_ip");
            $strReturn .= $this->objToolkit->dataTable($arrHeader, $arrLogs);
            $strReturn .= $arrPageViews["pageview"];
		}
		else
			$strReturn = $this->getText("fehler_recht");

		return $strReturn;
	}

	/**
	 * Shows a form or deltes a timeintervall from the logs
	 *
	 * @return string "" in case of success
	 */
	private function actionDeleteLogbook() {
		$strReturn = "";
		if($this->getParam("loeschen") == "") {
		    $strReturn .= $this->objToolkit->formHeader(getLinkAdminHref("downloads", "deleteLogbook", "loeschen=1"));
		    $strReturn .= $this->objToolkit->formTextRow($this->getText("hint_datum"));
		    $strReturn .= $this->objToolkit->formDateSimple("datum", "", "", "", $this->getText("datum"));
		    $strReturn .= $this->objToolkit->formInputSubmit($this->getText("speichern"));
		    $strReturn .= $this->objToolkit->formClose();
		}
		elseif ($this->getParam("loeschen") == "1") {
		    //Build the date
			$intDate = strtotime($this->getParam("datum_datum_jahr")."-".$this->getParam("datum_datum_monat")."-".$this->getParam("datum_datum_tag"));

			if(!class_modul_downloads_logbook::deleteFromLogs($intDate))
			    throw new class_exception("Error deleting log-rows", class_exception::$level_ERROR);
		}
		return $strReturn;
	}


// --- Sortierung ---------------------------------------------------------------------------------------

	/**
	 * Sorts a record
	 *
	 * @param string $strDirection up || down
	 */
	private function actionSort($strDirection = "up") {
	    if($strDirection == "up")
	       $this->setPosition($this->getSystemid(), "upwards");
	    else
	       $this->setPosition($this->getSystemid(), "downwards");
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
	    $objRepo = new class_modul_downloads_archive(array_shift($arrPathIds));
	    $arrEntries[] = getLinkAdmin($this->arrModule["modul"], "showArchive", "&systemid=".$objRepo->getSystemid(), $objRepo->getTitle());
	    foreach ($arrPathIds as $strOneId) {
	        $objDownload = new class_modul_downloads_file($strOneId);
	    	$arrEntries[] = getLinkAdmin($this->arrModule["modul"], "showArchive", "&systemid=".$strOneId, $objDownload->getName());
	    }
	    //if in edit mode, pop one element from array
	    if($this->getAction() == "editFile")
	       array_pop($arrEntries);
	    return $this->objToolkit->getPathNavigation($arrEntries);
	}

} //class_modul_downlodas_admin

?>