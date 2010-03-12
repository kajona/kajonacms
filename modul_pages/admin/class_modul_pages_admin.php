<?php
/*"******************************************************************************************************
*   (c) 2004-2006 by MulchProductions, www.mulchprod.de                                                 *
*   (c) 2007-2010 by Kajona, www.kajona.de                                                              *
*       Published under the GNU LGPL v2.1, see /system/licence_lgpl.txt                                 *
*-------------------------------------------------------------------------------------------------------*
*   $Id$                                  *
********************************************************************************************************/



/**
 * This class handles the admin-sided management of the pages
 * In this case, that are only the pages NOT yet the content
 *
 * @package modul_pages
 */
class class_modul_pages_admin extends class_admin implements interface_admin  {
	private $strFolderlevel = null;				//The current level in the folderhierachy

	/**
	 * Constructor
	 *
	 */
	public function __construct() {
        $arrModule = array();
		$arrModule["name"] 			= "modul_pages";
		$arrModule["author"] 		= "sidler@mulchprod.de";
		$arrModule["moduleId"] 		= _pages_modul_id_;
		$arrModule["modul"]			= "pages";
		$arrModule["table"]			= _dbprefix_."page";
		$arrModule["table2"]		= _dbprefix_."page_elemente";
		$arrModule["table3"]		= _dbprefix_."elemente";

		//base class
		parent::__construct($arrModule);

		//To generate a correct list-view, set a view params
		$this->strFolderlevel= ($this->getParam("folderid") != "" ? $this->getParam("folderid") : $this->getModuleSystemid($this->arrModule["modul"]));

	}

	/**
	 * Action block to decide, which action to perform
	 *
	 * @param string $strAction
	 */
	public function action($strAction = "") {

		//unlock something? do it now!
		if($this->getParam("unlockid") != "") {
            $objLockmanager = new class_lockmanager($this->getParam("unlockid"));
            $objLockmanager->unlockRecord();
		}

		if($strAction == "")
			$strAction = "list";


		$strReturn = "";

		try {
    		// -- Pages ------------------------------

    		if($strAction == "list")
    			$strReturn = $this->actionList();
    		if($strAction == "listAll")
    			$strReturn = $this->actionListAll();
    		if($strAction == "newPage")
    			$strReturn = $this->actionNew();
    		if($strAction == "savePage") {
    		    if($this->validateForm()) {
    			    $strReturn = $this->actionSavePage();
    			    if($strReturn == "")
    				    $this->adminReload(getLinkAdminHref($this->arrModule["modul"], "list", "folderid=".$this->strFolderlevel));
    		    }
    		    else
    		        $strReturn = $this->actionNew();
    		}
    		if($strAction == "changePage") {
    		    if($this->validateForm()) {
    			    $strReturn = $this->actionChangePage();
    			    if($strReturn == "")
    				    $this->adminReload(getLinkAdminHref($this->arrModule["modul"], "list", "folderid=".$this->strFolderlevel));
    		    }
    		    else
    		        $strReturn = $this->actionNew();
    		}
    		if($strAction == "deletePageFinal") {
    			$strReturn = $this->actionDeletePageFinal();
    			if($strReturn == "")
    				$this->adminReload(getLinkAdminHref($this->arrModule["modul"], "list", "folderid=".$this->strFolderlevel));
    		}
    		if($strAction == "copyPage") {
    		    $strReturn = $this->actionCopyPage();
    			if($strReturn == "")
    			    $this->adminReload(getLinkAdminHref($this->arrModule["modul"], "list", "folderid=".$this->strFolderlevel));
    		}

    		// -- Folders ------------------------------
    		if($strAction == "newFolder")
    			$strReturn = $this->actionFolderNew();
    		if($strAction == "folderNewSave") {
    		    if($this->validateForm()) {
    			    $strReturn = $this->actionFolderNewSave();
    			    if($strReturn == "")
    				    $this->adminReload(getLinkAdminHref($this->arrModule["modul"], "list", "folderid=".$this->strFolderlevel));
    		    }
    		    else
    		        $strReturn = $this->actionFolderNew();
    		}

    		if($strAction == "editFolder")
    			$strReturn = $this->actionFolderEdit();
    		if($strAction == "folderEditSave") {
    		    if($this->validateForm()) {
    			    $strReturn = $this->actionFolderEditSave();
    			    if($strReturn == "")
    				    $this->adminReload(getLinkAdminHref($this->arrModule["modul"], "list", "folderid=".$this->getPrevId()));
    		    }
    		    else
    		        $strReturn = $this->actionFolderNew();
    		}

    		if($strAction == "deleteFolderFinal") {
    			$strReturn = $this->actionDeleteFolderFinal();
    			if($strReturn == "")
    				$this->adminReload(getLinkAdminHref($this->arrModule["modul"], "list"));
    		}

    		// -- Misc ----------------------------------
    		if($strAction == "listElements")
    			$strReturn = $this->actionElementsList();
    		if($strAction == "newElement")
    			$strReturn = $this->actionElementNew();
    		if($strAction == "editElement")
    			$strReturn = $this->actionElementNew("edit");
    		if($strAction == "installElement") {
    		    $strReturn = $this->actionInstallElement();
    		    if($strReturn == "")
    				$this->adminReload(getLinkAdminHref($this->arrModule["modul"], "listElements"));
    		}
    		if($strAction == "saveElement") {
    		    if($this->validateForm() & !$this->checkElementExisting()) {
        			$strReturn = $this->actionSaveElement();
        			if($strReturn == "")
        				$this->adminReload(getLinkAdminHref($this->arrModule["modul"], "listElements"));
    		    }
    		    else {
    		        if($this->getParam("modus") == "edit")
    		            $strReturn = $this->actionElementNew("edit");
    		        else
    		            $strReturn = $this->actionElementNew();
    		    }
    		}
    		if($strAction == "deleteElement") {
    			$strReturn = $this->actionDeleteElement();
    			if($strReturn == "")
    				$this->adminReload(getLinkAdminHref($this->arrModule["modul"], "listElements"));
    		}
    		if($strAction == "updatePlaceholder")
                $strReturn = $this->actionUpdatePlaceholder();
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
	    $arrReturn[] = array("view", getLinkAdmin($this->arrModule["modul"], "listAll", "", $this->getText("modul_liste_alle"), "", "", true, "adminnavi"));
		$arrReturn[] = array("", "");
	    $arrReturn[] = array("edit", getLinkAdmin($this->arrModule["modul"], "newPage", ($this->getParam("folderid") != "" ? "&folderid=".$this->getParam("folderid") : "" ), $this->getText("modul_neu"), "", "", true, "adminnavi"));
        $arrReturn[] = array("right", getLinkAdmin($this->arrModule["modul"], "newFolder", ($this->getParam("folderid") != "" ? "&folderid=".$this->getParam("folderid") : "" ), $this->getText("modul_neu_ordner"), "", "", true, "adminnavi"));
		$arrReturn[] = array("", "");
		$arrReturn[] = array("right1", getLinkAdmin($this->arrModule["modul"], "listElements", "", $this->getText("modul_elemente"), "", "", true, "adminnavi"));
	    $arrReturn[] = array("right1", getLinkAdmin($this->arrModule["modul"], "newElement", "", $this->getText("modul_element_neu"), "", "", true, "adminnavi"));
		$arrReturn[] = array("", "");
	    $arrReturn[] = array("right3", getLinkAdmin($this->arrModule["modul"], "updatePlaceholder", "", $this->getText("updatePlaceholder"), "", "", true, "adminnavi"));
		return $arrReturn;
	}


    protected function getRequiredFields() {
        $strAction = $this->getAction();
        $arrReturn = array();
        if($strAction == "folderNewSave" || $strAction == "folderEditSave") {
            $arrReturn["ordner_name"] = "string";
        }
        if($strAction == "savePage" || $strAction == "changePage") {
            $arrReturn["name"] = "string";
        }
        if($strAction == "saveElement") {
            $arrReturn["element_name"] = "string";
            $arrReturn["element_cachetime"] = "number";
        }

        return $arrReturn;
    }



//*"*****************************************************************************************************
//--Pages-Management-------------------------------------------------------------------------------------


	/**
	 * Creates a list of sites in the current folder
	 *
	 * @return string
	 */
	public function actionList() {
		$strReturn = "";
		//Check rights
		if($this->objRights->rightView($this->getModuleSystemid($this->arrModule["modul"]))) {

			//GetFolders
			//if theres a folder-level, load it
			$arrFolder = class_modul_pages_folder::getFolderList($this->strFolderlevel);
			$intI = 0;

            $strFolder = "";
			//Folder-Table
			//A Folder, to get one level up
			if($this->strFolderlevel != $this->getModuleSystemid($this->arrModule["modul"]) && validateSystemid($this->strFolderlevel)) {
				//Get data of folder one level above
				$objPrevFolder = new class_modul_pages_folder($this->strFolderlevel);
				//Output Row
		  		$strFolder .= $this->objToolkit->listRow2Image(getImageAdmin("icon_folderOpen.gif"), "..", $this->objToolkit->listButton(getLinkAdmin("pages", "list", "&folderid=".$objPrevFolder->getPrevId(), $this->getText("pages_hoch"), $this->getText("pages_hoch"), "icon_folderActionLevelup.gif")), $intI++);
			}

			//So, lets loop through the folders
			if(count($arrFolder) > 0) {
				foreach($arrFolder as $objSingleFolder) {
					//Correct Rights?
					if($this->objRights->rightView($objSingleFolder->getSystemid())) {
						$strActions = "";
			    		//Splitting up rights so decide which Buttons to display
			    		if($this->objRights->rightView($objSingleFolder->getSystemid()))
			    			$strActions .= $this->objToolkit->listButton(getLinkAdmin("pages", "list", "&folderid=".$objSingleFolder->getSystemid(), $this->getText("pages_ordner_oeffnen"), $this->getText("pages_ordner_oeffnen"), "icon_folderActionOpen.gif"));
			    		if($this->objRights->rightEdit($objSingleFolder->getSystemid()))
			    			$strActions .= $this->objToolkit->listButton(getLinkAdmin("pages", "editFolder", "&systemid=".$objSingleFolder->getSystemid(), $this->getText("pages_ordner_edit"), $this->getText("pages_ordner_edit"), "icon_pencil.gif"));
			    		if($this->objRights->rightDelete($objSingleFolder->getSystemid())) {
			    		    if(count(class_modul_pages_folder::getFolderList($objSingleFolder->getSystemid())) != 0 || count(class_modul_pages_folder::getPagesInFolder($objSingleFolder->getSystemid())) != 0)
 			    		    	$strActions .= $this->objToolkit->listButton(getImageAdmin("icon_tonDisabled.gif", $this->getText("ordner_loschen_leer")));
                            else
                            	$strActions .= $this->objToolkit->listDeleteButton($objSingleFolder->getStrName(), $this->getText("pages_ordner_loeschen_frage"), getLinkAdminHref($this->arrModule["modul"], "deleteFolderFinal", "&systemid=".$objSingleFolder->getSystemid()));
			    		}
			    		if($this->objRights->rightRight($objSingleFolder->getSystemid()))
			    			$strActions .= $this->objToolkit->listButton(getLinkAdmin("right", "change", "&systemid=".$objSingleFolder->getSystemid(), "", $this->getText("pages_ordner_rechte"), getRightsImageAdminName($objSingleFolder->getSystemid())));
			  			$strFolder .= $this->objToolkit->listRow2Image(getImageAdmin("icon_folderOpen.gif"), $objSingleFolder->getStrName(), $strActions, $intI++);
					}
				}
			}

			if($this->objRights->rightRight2($this->getModuleSystemid($this->arrModule["modul"])))
			    $strFolder .= $this->objToolkit->listRow2Image("", "", getLinkAdmin($this->arrModule["modul"], "newFolder", ($this->getParam("folderid") != "" ? "&folderid=".$this->getParam("folderid") : "" ), $this->getText("modul_neu_ordner"), $this->getText("modul_neu_ordner"), "icon_blank.gif"), $intI++);

			if(uniStrlen($strFolder) != 0)
	  		    $strFolder = $this->objToolkit->listHeader().$strFolder.$this->objToolkit->listFooter();

	  		$strFolder .= $this->objToolkit->divider();

	  		
			$strFolder = $this->objToolkit->getLayoutFolderPic($strFolder, $this->getText("klapper"));


			//Collect the pages belonging to the current folder to display
			$arrPages = class_modul_pages_folder::getPagesInFolder($this->strFolderlevel);
			$intI = 0;
			$strPages = "";

			foreach($arrPages as $objOneRow) {
				$strActions = "";
			 	$strSystemid = $objOneRow->getSystemid();
			 	//As usual: Just display, if the needed rights are given
			 	if($this->objRights->rightView($strSystemid)) {
				 	//Split up rights
		    		if($this->objRights->rightEdit($strSystemid))
    				    $strActions .= $this->objToolkit->listButton(getLinkAdmin("pages", "newPage", "&systemid=".$objOneRow->getSystemid(), "", $this->getText("seite_bearbeiten"), "icon_page.gif"));
		    		if($this->objRights->rightEdit($strSystemid))
	    				$strActions .= $this->objToolkit->listButton(getLinkAdmin("pages_content", "list", "&systemid=".$objOneRow->getSystemid(), "", $this->getText("seite_inhalte"), "icon_pencil.gif"));
	    			if($this->objRights->rightEdit($strSystemid))
		    			$strActions .= $this->objToolkit->listButton(getLinkAdmin("pages", "copyPage", "&systemid=".$objOneRow->getSystemid()."&folderid=".$this->strFolderlevel, "", $this->getText("seite_copy"), "icon_copy.gif"));
		    		if($this->objRights->rightDelete($strSystemid))
		    			$strActions .= $this->objToolkit->listDeleteButton($objOneRow->getStrName(), $this->getText("seite_loeschen_frage"), getLinkAdminHref($this->arrModule["modul"], "deletePageFinal", "&systemid=".$objOneRow->getSystemid()));
		    		if($this->objRights->rightEdit($strSystemid))
		    			$strActions .= $this->objToolkit->listStatusButton($objOneRow->getSystemid());
		    		if($this->objRights->rightRight($strSystemid))
		    			$strActions .= $this->objToolkit->listButton(getLinkAdmin("right", "change", "&systemid=".$objOneRow->getSystemid(), "", $this->getText("seite_rechte"), getRightsImageAdminName($objOneRow->getSystemid())));

		  			$strPages .= $this->objToolkit->listRow2Image(getImageAdmin("icon_page.gif"), $objOneRow->getStrName(), $strActions, $intI++);
			 	}
			}
			if($this->objRights->rightEdit($this->getModuleSystemid($this->arrModule["modul"])))
			    $strPages .= $this->objToolkit->listRow2Image("", "", getLinkAdmin($this->arrModule["modul"], "newPage", ($this->getParam("folderid") != "" ? "&folderid=".$this->getParam("folderid") : "" ), $this->getText("modul_neu"), $this->getText("modul_neu"), "icon_blank.gif"), $intI++);

			if(uniStrlen($strPages) != 0)
			$strPages = $this->objToolkit->listHeader().$strPages.$this->objToolkit->listFooter();

			if(count($arrPages) == 0)
				$strPages .= $this->getText("liste_seiten_leer");

            $strPathNavi = $this->generateFolderNavigation();
			$strReturn .= $strPathNavi."<br /><br />".$this->generateTreeView($strFolder.$strPages);

		}
		else
			$strReturn .= $this->getText("fehler_recht");

		return $strReturn;
	}


	/**
	 * Returns a list of all pages in the system, not worrying about the folders -> Flat List
	 *
	 * @return string The complete List
	 */
	public function actionListAll() {
		$strReturn = "";
		//Check the rights
		if($this->objRights->rightView($this->getModuleSystemid($this->arrModule["modul"]))) {
			$intI = 0;

			//showing a list using the pageview
            $objArraySectionIterator = new class_array_section_iterator(class_modul_pages_page::getNumberOfPagesAvailable());
		    $objArraySectionIterator->setIntElementsPerPage(_admin_nr_of_rows_);
		    $objArraySectionIterator->setPageNumber((int)($this->getParam("pv") != "" ? $this->getParam("pv") : 1));
		    $objArraySectionIterator->setArraySection(class_modul_pages_page::getAllPages($objArraySectionIterator->calculateStartPos(), $objArraySectionIterator->calculateEndPos()));

    		$arrPageViews = $this->objToolkit->getSimplePageview($objArraySectionIterator, "pages", "listAll");
            $arrPages = $arrPageViews["elements"];

			foreach($arrPages as $objPage) {
				$strActions = "";

	    		if($this->objRights->rightEdit($objPage->getSystemid()))
	    			$strActions.= $this->objToolkit->listButton(getLinkAdmin("pages", "newPage", "&systemid=".$objPage->getSystemid(), "", $this->getText("seite_bearbeiten"), "icon_page.gif"));
	    		if($this->objRights->rightEdit($objPage->getSystemid()))
	    			$strActions.= $this->objToolkit->listButton(getLinkAdmin("pages_content", "list", "&systemid=".$objPage->getSystemid(), "", $this->getText("seite_inhalte"), "icon_pencil.gif"));
	    		if($this->objRights->rightEdit($objPage->getSystemid()))
		    			$strActions .= $this->objToolkit->listButton(getLinkAdmin("pages", "copyPage", "&systemid=".$objPage->getSystemid(), "", $this->getText("seite_copy"), "icon_copy.gif"));
	    		if($this->objRights->rightDelete($objPage->getSystemid()))
	    			$strActions.= $this->objToolkit->listDeleteButton($objPage->getStrName(), $this->getText("seite_loeschen_frage"), getLinkAdminHref($this->arrModule["modul"], "deletePageFinal", "&systemid=".$objPage->getSystemid()));
	    		if($this->objRights->rightEdit($objPage->getSystemid()))
	    			$strActions.= $this->objToolkit->listStatusButton($objPage->getSystemid());
	    		if($this->objRights->rightRight($objPage->getSystemid()	))
	    			$strActions.= $this->objToolkit->listButton(getLinkAdmin("rights", "change", "&systemid=".$objPage->getSystemid(), "", $this->getText("seite_rechte"), getRightsImageAdminName($objPage->getSystemid())));

	  			$strReturn .= $this->objToolkit->listRow2Image(getImageAdmin("icon_page.gif"), $objPage->getStrName(), $strActions, $intI++);
			}
			if($this->objRights->rightEdit($this->getModuleSystemid($this->arrModule["modul"])))
			    $strReturn .= $this->objToolkit->listRow2Image("", "", getLinkAdmin($this->arrModule["modul"], "newPage", ($this->getParam("folderid") != "" ? "&folderid=".$this->getParam("folderid") : "" ), $this->getText("modul_neu"), $this->getText("modul_neu"), "icon_blank.gif"), $intI++);

			if(uniStrlen($strReturn) != 0)
			    $strReturn = $this->objToolkit->listHeader().$strReturn.$this->objToolkit->listFooter();

			if(count($arrPages) > 0)
			    $strReturn .= $arrPageViews["pageview"];

			if(count($arrPages) == 0)
				$strReturn .= $this->getText("liste_seiten_leer");

		}
		else
			$strReturn .= $this->getText("fehler_recht");

		return $strReturn;
	}


	/**
	 * Shows the form to create a new Site
	 *
	 * @return string The form
	 */
	public function actionNew() {
		$strReturn = "";

		if($this->getParam("systemid") != "" || $this->getParam("pageid") != "") {
		    if($this->getParam("systemid") == "" && $this->getParam("pageid") != "")
		        $this->setSystemid($this->getParam("pageid"));
		    //edit a page

		    //add a pathnavigation when not in pe mode
            if($this->getParam("pe") != 1) {
                $strReturn = $this->getPathNavigation().$strReturn;
            }

			if($this->objRights->rightEdit($this->getSystemid())) {
                //Load data of the page
                $objPage = new class_modul_pages_page($this->getSystemid());

                $arrToolbarEntries = array();
                $arrToolbarEntries[0] = "<a href=\"".getLinkAdminHref("pages", "newPage", "&systemid=".$this->getSystemid())."\" style=\"background-image:url("._skinwebpath_."/pics/icon_page.gif);\">".$this->getText("contentToolbar_pageproperties")."</a>";
                $arrToolbarEntries[1] = "<a href=\"".getLinkAdminHref("pages_content", "list", "&systemid=".$this->getSystemid())."\" style=\"background-image:url("._skinwebpath_."/pics/icon_pencil.gif);\">".$this->getText("contentToolbar_content")."</a>";
                $arrToolbarEntries[2] = "<a href=\"".getLinkPortalHref($objPage->getStrName(), "", "", "&preview=1", "", $this->getLanguageToWorkOn())."\" target=\"_blank\" style=\"background-image:url("._skinwebpath_."/pics/icon_lens.gif);\">".$this->getText("contentToolbar_preview")."</a>";

                //if languages are installed, present a language switch right here
                $objLanguages = new class_modul_languages_admin();
                $arrToolbarEntries[3] = $objLanguages->getLanguageSwitch();

                $strReturn .= $this->objToolkit->getContentToolbar($arrToolbarEntries, 0)."<br />";

				//Start form
				$strReturn .= $this->objToolkit->getValidationErrors($this);
				$strReturn .= $this->objToolkit->formHeader(getLinkAdminHref($this->arrModule["modul"], "changePage"));

				$strReturn .= $this->objToolkit->formInputText("name", $this->getText("name"), $objPage->getStrName());
				$strReturn .= $this->objToolkit->formInputText("browsername", $this->getText("browsername"), $objPage->getStrBrowsername());
				$strReturn .= $this->objToolkit->formInputText("seostring", $this->getText("seostring"), $objPage->getStrSeostring());
				$strReturn .= $this->objToolkit->formInputTextarea("beschreibung", $this->getText("beschreibung"), $objPage->getStrDesc());
				$strReturn .= $this->objToolkit->formInputHidden("pageid", $this->getSystemid());
				$strReturn .= $this->objToolkit->formInputTextarea("keywords", $this->getText("keywords"), $objPage->getStrKeywords());

				if($objPage->getPrevId() != $this->getModuleSystemid($this->arrModule["modul"]) ) {
				    $objFolder = new class_modul_pages_folder($objPage->getPrevId());
					$strReturn .= $this->objToolkit->formInputHidden("ordnerid", $objFolder->getSystemid());
					$strReturn .= $this->objToolkit->formInputText("ordner_name", $this->getText("ordner_name"), $objFolder->getStrName(), "inputText", getLinkAdminPopup("folderview", "pagesFolderBrowser", "", $this->getText("browser"), $this->getText("browser"), "icon_externalBrowser.gif", 500, 500, "ordneransicht"), true);
				}
				else {
					$strReturn .= $this->objToolkit->formInputHidden("ordnerid", "");
					$strReturn .= $this->objToolkit->formInputText("ordner_name", $this->getText("ordner_name"), "", "inputText", getLinkAdminPopup("folderview", "pagesFolderBrowser", "", $this->getText("browser"), $this->getText("browser"), "icon_externalBrowser.gif", 500, 500, "ordneransicht"));
				}
				//Load the available templates
				$objFilesystem = new class_filesystem();
				$arrTemplates = $objFilesystem->getFilelist("/templates/modul_pages", ".tpl");
				//If set on, the dropdown could be disabled
				$bitEnabled = true;
				if(_pages_templatechange_ == "false") {
					if($objPage->getNumberOfElementsOnPage() != 0)
						$bitEnabled = false;
				}
				$arrTemplatesDD = array();
				if(count($arrTemplates) > 0) {
					foreach($arrTemplates as $strTemplate) {
						$arrTemplatesDD[$strTemplate] = $strTemplate;
					}
				}

				//if no template was selected before, show a warning. can occur when having created new languages
				if($objPage->getStrTemplate() == "")
				    $strReturn .= $this->objToolkit->formTextRow($this->getText("templateNotSelectedBefore"));
				$strReturn .= $this->objToolkit->formInputDropdown("template", $arrTemplatesDD, $this->getText("template"), $objPage->getStrTemplate(), "inputDropdown", $bitEnabled);
				$strReturn .= $this->objToolkit->formInputSubmit($this->getText("submit"));
				$strReturn .= $this->objToolkit->formClose();

				$strReturn .= $this->objToolkit->setBrowserFocus("name");
			}
			else
				$strReturn .= $this->getText("fehler_recht");
		}
		else {
			//Mode: Create a new Page
			if($this->objRights->rightEdit($this->getModuleSystemid($this->arrModule["modul"]))) {
                $arrToolbarEntries = array();

                //if languages are installed, present a language switch right here
                $objLanguages = new class_modul_languages_admin();
                $arrToolbarEntries[0] = $objLanguages->getLanguageSwitch();

                $strReturn .= $this->objToolkit->getContentToolbar($arrToolbarEntries, 0)."<br />";

				//start form
				$strReturn .= $this->objToolkit->getValidationErrors($this);
				$strReturn .= $this->objToolkit->formHeader(getLinkAdminHref($this->arrModule["modul"], "savePage"));
				$strReturn .= $this->objToolkit->formInputText("name", $this->getText("name"), $this->getParam("name"));
				$strReturn .= $this->objToolkit->formInputText("browsername", $this->getText("browsername"), $this->getParam("browsername"));
				$strReturn .= $this->objToolkit->formInputText("seostring", $this->getText("seostring"), $this->getParam("seostring"));
				$strReturn .= $this->objToolkit->formInputTextarea("beschreibung", $this->getText("beschreibung"), $this->getParam("beschreibung"));
				$strReturn .= $this->objToolkit->formInputHidden("pageid", "");
				$strReturn .= $this->objToolkit->formInputTextarea("keywords", $this->getText("keywords"), $this->getParam("keywords"));
				$strReturn .= $this->objToolkit->formInputHidden("ordnerid", $this->getParam("folderid"));
				$strReturn .= $this->objToolkit->formInputHidden("folderid", $this->getParam("folderid"));
				$strFolder = "";
				if($this->getParam("folderid") != "") {
				    $objFolder = new class_modul_pages_folder($this->getParam("folderid"));
				    $strFolder = $objFolder->getStrName();
				}

				$strReturn .= $this->objToolkit->formInputText("ordner_name", $this->getText("ordner_name"), $strFolder, "inputText", getLinkAdminPopup("folderview", "pagesFolderBrowser", "", $this->getText("browser"), $this->getText("browser"), "icon_externalBrowser.gif", 500, 500, "ordneransicht"));

				//Load all the Templates available
				$objFilesystem = new class_filesystem();

				$arrTemplates = $objFilesystem->getFilelist("/templates/modul_pages", ".tpl");

				$arrTemplatesDD = array();
				if(count($arrTemplates) > 0)
					foreach($arrTemplates as $strTemplate)
						$arrTemplatesDD[$strTemplate] = $strTemplate;

				$strReturn .= $this->objToolkit->formInputDropdown("template", $arrTemplatesDD, $this->getText("template"), _pages_defaulttemplate_);
				$strReturn .= $this->objToolkit->formInputSubmit($this->getText("submit"));
				$strReturn .= $this->objToolkit->formClose();

				$strReturn .= $this->objToolkit->setBrowserFocus("name");
			}
			else
				$strReturn .= $this->getText("fehler_recht");
		}
		return $strReturn;
	}

	/**
	 * Saves a sumbitted page in the database (new Page!)
	 *
	 * @return String, "" if successful
	 */
	public function actionSavePage() {
		$strReturn = "";
		if($this->objRights->rightEdit($this->getModuleSystemid($this->arrModule["modul"]))) {
			$strName = uniStrtolower($this->getParam("name"));
			$strDescription = $this->getParam("beschreibung");
			$strTemplate = $this->getParam("template");
			$strKeywords = $this->getParam("keywords");
			$strBrowsername = $this->getParam("browsername");
			$strSeostring = $this->getParam("seostring");

			if($strName != "" && $strName != " ") {
			    $objPage = new class_modul_pages_page("");
			    $objPage->setStrBrowsername($strBrowsername);
			    $objPage->setStrDesc($strDescription);
			    $objPage->setStrName(uniStrtolower($strName));
			    $objPage->setStrTemplate($strTemplate);
			    $objPage->setStrKeywords($strKeywords);
			    $objPage->setStrSeostring($strSeostring);
			    $objPage->setStrLanguage($this->getLanguageToWorkOn());
				$strFolderid = ($this->getParam("ordnerid") != "" ? $this->getParam("ordnerid") : "" );
				//To load the correct list afterwards, save the folder as current folder
				$this->strFolderlevel = $strFolderid;

                if(!validateSystemid($strFolderid))
                    $strFolderid = "";
				if(!$objPage->updateObjectToDb($strFolderid))
				    throw new class_exception("Error saving new page to db", class_exception::$level_ERROR);

			}
			else
				$strReturn .= $this->getText("fehler_name");
		}
		else
			$strReturn .= $this->getText("fehler_recht");

		return $strReturn;
	} //actionSavePage



	/**
	 * Saves the edited, submitted page
	 *
	 * @return string, "" in case of success
	 */
	public function actionChangePage() {
		$strReturn = "";
		$strPageid = $this->getParam("pageid");
		$strName = uniStrtolower($this->getParam("name"));
		$strDescription = $this->getParam("beschreibung");
		$strBrowsername = $this->getParam("browsername");
		$strSeostring = $this->getParam("seostring");

		if($this->getParam("template")!= "")
			$strTemplate = $this->getParam("template");
		else
			$strTemplate = false;

		$strKeywords = $this->getParam("keywords");

		if($this->objRights->rightEdit($strPageid)) {
			if($strName != "" && $strName != " ") {

			    $objPage = new class_modul_pages_page($strPageid);
			    $objPage->setStrBrowsername($strBrowsername);
			    $objPage->setStrDesc($strDescription);
			    $objPage->setStrName(uniStrtolower($strName));
			    $objPage->setStrKeywords($strKeywords);
			    $objPage->setStrSeostring($strSeostring);
			    $objPage->setStrLanguage($this->getLanguageToWorkOn());

			    if($strTemplate !== false)
			        $objPage->setStrTemplate($strTemplate);


				$strFolderid = ($this->getParam("ordnerid") != "" ? $this->getParam("ordnerid") : "" );
				//To load the correct list afterwards, save the folder as current folder
				$this->strFolderlevel = $strFolderid;

                if(!validateSystemid($strFolderid))
                    $strFolderid = "";
				if(!$objPage->updateObjectToDb($strFolderid))
					throw new class_exception("Error updating page to db", class_exception::$level_ERROR);

				//Flush the cache
				$this->flushPageFromPagesCache($strName);
			}
			else
				$strReturn = $this->getText("fehler_name");
		}
		else
			$strReturn = $this->getText("fehler_recht");

		return $strReturn;
	} //actionChangePage()


	/**
	 * Delete a page and all associated elements
	 *
	 * @return string, "" in case of success
	 */
	public function actionDeletePageFinal() {
		$strReturn = "";
		//System-Id zur Rechtepruefung ermitteln
		if($this->objRights->rightDelete($this->getSystemid())) {
		    $objPage = new class_modul_pages_page($this->getSystemid());
			//Are there any locked records on this page?
			if($objPage->getNumberOfLockedElementsOnPage() == 0) {

                //To load the correct list afterwards, save the folder as current folder
                $this->strFolderlevel = $this->getPrevId();

			    if(!class_modul_pages_page::deletePage($this->getSystemid()))
			         throw new class_exception("Error deleting page from db", class_exception::$level_ERROR);

			}
			else {
				//Print a message-box
				$strReturn .= $this->objToolkit->warningBox($this->getText("ds_seite_gesperrt"));
			}

		}
		else
			$strReturn = $this->getText("fehler_recht");

		return $strReturn;
	} //actionDeletePageFinal

	/**
	 * Invokes a deep copy of the current page
	 *
	 * @return string "" in case of success
	 */
	private function actionCopyPage() {
	    $strReturn = "";
		//System-Id zur Rechtepruefung ermitteln
		if($this->objRights->rightEdit($this->getSystemid())) {
		    $objPage = new class_modul_pages_page($this->getSystemid());
			if(!$objPage->copyPage())
                throw new class_exception("Error while copying the page!", class_exception::$level_ERROR);

		}
		else
			$strReturn = $this->getText("fehler_recht");

		return $strReturn;
	}


//*"*****************************************************************************************************
//--Folder-Mgmt------------------------------------------------------------------------------------------


	/**
	 * Returns a form to create a new folder
	 *
	 * @return string
	 */
	private function actionFolderNew() {
		$strReturn = "";
		if($this->objRights->rightRight2($this->getModuleSystemid($this->arrModule["modul"]))) {
			$strPrevid = $this->strFolderlevel;
			//Build the form
			//create an errorlist
			$strReturn .= $this->objToolkit->getValidationErrors($this);
			$strReturn .= $this->objToolkit->formHeader(getLinkAdminHref($this->arrModule["modul"], "folderNewSave"));
			$strReturn .= $this->objToolkit->formInputText("ordner_name", $this->getText("ordner_name"), $this->getParam("ordner_name"));
			$strReturn .= $this->objToolkit->formInputHidden("prev_id", $strPrevid);
			$strReturn .= $this->objToolkit->formInputSubmit($this->getText("submit"));
			$strReturn .= $this->objToolkit->formClose();

			$strReturn .= $this->objToolkit->setBrowserFocus("ordner_name");
		}
		else
			$strReturn = $this->getText("fehler_recht");

		return $strReturn;
	}

	/**
	 * Creates a form to edit a folder (rename it)
	 *
	 * @return unknown
	 */
	private function actionFolderEdit() {
		$strReturn = "";
		if($this->objRights->rightEdit($this->getSystemid())) {
			//Load folder-data
            $objFolder = new class_modul_pages_folder($this->getSystemid());
			$strPrevid = $this->strFolderlevel;
			//Build the form
			//create an errorlist
			$strReturn .= $this->objToolkit->getValidationErrors($this);
			$strReturn .= $this->objToolkit->formHeader(getLinkAdminHref($this->arrModule["modul"], "folderEditSave"));
			$strReturn .= $this->objToolkit->formInputText("ordner_name", $this->getText("ordner_name"), $objFolder->getStrName());

			if($objFolder->getPrevId() != $this->getModuleSystemid("pages")) {
			    $objFolder2 = new class_modul_pages_folder($objFolder->getPrevId());
				$strReturn .= $this->objToolkit->formInputHidden("ordnerid", $objFolder2->getSystemid());
				$strReturn .= $this->objToolkit->formInputText("ordner_parent_name", $this->getText("ordner_name_parent"), $objFolder2->getStrName(), "inputText", getLinkAdminPopup("folderview", "pagesFolderBrowser", "&form_element=ordner_parent_name", $this->getText("browser"), $this->getText("browser"), "icon_externalBrowser.gif", 500, 500, "ordneransicht"), true);
			}
			else {
				$strReturn .= $this->objToolkit->formInputHidden("ordnerid", "");
				$strReturn .= $this->objToolkit->formInputText("ordner_parent_name", $this->getText("ordner_name_parent"), "", "inputText", getLinkAdminPopup("folderview", "pagesFolderBrowser", "&form_element=ordner_parent_name", $this->getText("browser"), $this->getText("browser"), "icon_externalBrowser.gif", 500, 500, "ordneransicht"));
			}


			$strReturn .= $this->objToolkit->formInputHidden("systemid", $this->getSystemid());
			$strReturn .= $this->objToolkit->formInputSubmit($this->getText("submit"));
			$strReturn .= $this->objToolkit->formClose();

			$strReturn .= $this->objToolkit->setBrowserFocus("ordner_name");
		}
		else
			$strReturn = $this->getText("fehler_recht");

		return $strReturn;
	}

	/**
	 * Saves the posted Folder to database
	 *
	 * @return String, "" in case of success
	 */
	private function actionFolderNewSave() {
		$strReturn = "";
		if($this->objRights->rightRight2($this->getModuleSystemid($this->arrModule["modul"]))) {
			//Collect data to save to db
			$objFolder = new class_modul_pages_folder("");
			$objFolder->setStrName($this->getParam("ordner_name"), true);
			$objFolder->updateObjectToDb($this->getParam("prev_id"));
			$this->strFolderlevel = $this->getParam("prev_id");
		}
		else
			$strReturn = $this->getText("fehler_recht");

		return $strReturn;
	}

	/**
	 * Updates the posted Folder to database
	 *
	 * @return String, "" in case of success
	 */
	private function actionFolderEditSave() {
		$strReturn = "";
		if($this->objRights->rightRight2($this->getSystemid())) {
			//Collect data to save to db
			$objFolder = new class_modul_pages_folder($this->getSystemid());
			$objFolder->setStrName($this->getParam("ordner_name"), true);
            $objFolder->updateObjectToDb($this->getParam("ordnerid"));
		}
		else
			$strReturn = $this->getText("fehler_recht");

		return $strReturn;
	}


	/**
	 * Deletes a folder from Database. All subpages & subfolders turn up to top-level
	 *
	 * @return string, "" in case of success
	 */
	public function actionDeleteFolderFinal() {
		$strReturn = "";
		if($this->objRights->rightDelete($this->getSystemid())) 	{
			//Delete the folder
            $objFolder = new class_modul_pages_folder($this->getSystemid());
			if($objFolder->deleteFolder())
				$strReturn = "";
			else
				throw new class_exception($this->getText("ordner_loeschen_fehler"), class_exception::$level_ERROR);
		}
		else
			$strReturn .= $this->getText("fehler_recht");

		return $strReturn;
	}

	/**
	 * Creates a pathnavigation through all folders till the current page / folder
	 *
	 */
	private function generateFolderNavigation() {
		$strReturn = "";
		//Provide a small path-navigation to know where we are...
		$arrPath = $this->getPathArray($this->strFolderlevel);
		$arrPathLinks = array();
		//Link to root-folder
        $arrPathLinks[] = getLinkAdmin("pages", "list", "", "&nbsp;/&nbsp;");
		foreach($arrPath as $strOneFolderID) {
			$objFolder = new class_modul_pages_folder($strOneFolderID);
			$arrPathLinks[] = getLinkAdmin("pages", "list", "&folderid=".$strOneFolderID, $objFolder->getStrName());
		}

		return $this->objToolkit->getPathNavigation($arrPathLinks);
	}

    /**
     * Generates the code needed to render the pages and folder as a tree-view element.
     * The elements themselves are loaded via ajax, so only the root-node and the initial
     * folding-params are generated right here.
     *
     * @param string $strSideContent
     * @return string
     */
    private function generateTreeView($strSideContent) {
        $strReturn = "";

        //generate the array of ids to expand initially
        $arrNodes = $this->getPathArray($this->strFolderlevel);
        array_unshift($arrNodes, $this->getModuleSystemid($this->arrModule["modul"]));
        $strReturn .= $this->objToolkit->getTreeview("kajonaAdminAjax.loadPagesTreeViewNodes", $this->getModuleSystemid($this->arrModule["modul"]), $arrNodes, $strSideContent, $this->getOutputModuleTitle());
        return $strReturn;
    }


//*"*****************************************************************************************************
// --- Elements -----------------------------------------------------------------------------------------

	/**
	 * Returns a list of all installed Elements
	 *
	 * @return string
	 */
	private function actionElementsList() {
		$strReturn = "";
		if($this->objRights->rightRight1($this->getModuleSystemid($this->arrModule["modul"]))) {
			$arrElements = class_modul_pages_element::getAllElements();
			$intI = 0;
			foreach($arrElements as $objOneElement) {

                $objAdminInstance = $objOneElement->getAdminElementInstance();
                $strDescription = $objAdminInstance->getElementDescription();
                $strDescription .= ($strDescription != "" ? "<br /><br />" : "" ).$objOneElement->getStrName();
                $strDescription .= "<br />".$objOneElement->getStrVersion();

	    		$strActions = $this->objToolkit->listButton(getLinkAdmin("pages", "editElement", "&elementid=".$objOneElement->getSystemid(), $this->getText("element_bearbeiten"), $this->getText("element_bearbeiten"), "icon_pencil.gif"));

	    		$strActions .= $this->objToolkit->listDeleteButton($objOneElement->getStrName(), $this->getText("element_loeschen_frage"), getLinkAdminHref($this->arrModule["modul"], "deleteElement", "&elementid=".$objOneElement->getSystemid()));
                $strReturn .= $this->objToolkit->listRow3($objOneElement->getStrName(), " V ".$objOneElement->getStrVersion()." (".$objOneElement->getIntCachetime().")", $strActions, getImageAdmin("icon_dot.gif", $strDescription), $intI++);
			}
			if($this->objRights->rightRight1($this->getModuleSystemid($this->arrModule["modul"])))
			    $strReturn .= $this->objToolkit->listRow3("", "", getLinkAdmin($this->arrModule["modul"], "newElement", "", $this->getText("modul_element_neu"), $this->getText("modul_element_neu"), "icon_blank.gif"), "", $intI++);


			if(uniStrlen($strReturn) != 0)
			    $strReturn = $this->objToolkit->listHeader().$strReturn.$this->objToolkit->listFooter();

			if(count($arrElements) == 0)
		    	$strReturn .= $this->getText("elemente_liste_leer");


		    // ------------------------------------------------------------------------------------------
		    // any element-installers of elements not yet installed?
		    $arrElementsToInstall = array();
    		$objFilesystem = new class_filesystem();
    		//load installers available
    		$arrInstallers = $objFilesystem->getFilelist("/installer");

    		if($arrInstallers !== false) {

	    		foreach($arrInstallers as $intKey => $strFile)
	    			if(strpos($strFile, ".php") === false || strpos($strFile, "installer_element") === false)
	    				unset($arrInstallers[$intKey]);

	    		if(count($arrInstallers) > 0) {
	    		    asort($arrInstallers);
	    		    //Loading each installer
	        		foreach($arrInstallers as $strInstaller) {
	        			//Creating an object....
                        include_once(_realpath_."/installer/".$strInstaller);
	        			$strClass = "class_".str_replace(".php", "", $strInstaller);
	        			$objInstaller = new $strClass();

	        			$objSystem = class_modul_system_module::getModuleByName("system");
	        			if($objInstaller instanceof interface_installer ) {
	                		$bitNeededSysversionInstalled = true;
	                	    //check, if a min version of the system is needed
	                		if($objInstaller->getMinSystemVersion() != "") {
	                		    //the systems version to compare to

	                		    if(version_compare($objInstaller->getMinSystemVersion(), $objSystem->getStrVersion(), ">")) {
	                		        $bitNeededSysversionInstalled = false;
	                		    }
	                		}

                            //all nneded modules installed?
                            $bitRequired = true;
                            $arrModulesNeeded = $objInstaller->getNeededModules();
                            foreach($arrModulesNeeded as $strOneModule) {
                                try {
                                    $objTestModule = class_modul_system_module::getModuleByName($strOneModule, true);
                                }
                                catch (class_exception $objException) {
                                    $objTestModule = null;
                                }
                                if($objTestModule == null) {
                                    $bitRequired = false;
                                }
                            }

	                		if($bitRequired && $bitNeededSysversionInstalled && $objInstaller->hasPostInstalls()) {
	                		    $arrElementsToInstall[str_replace(".php", "", $strInstaller)] = $objInstaller->getArrModule("name_lang");
	                		}
	        			}
	        		}
	    		}

	    		//any installers remaining?
	    		if(count($arrElementsToInstall) > 0 ) {
	    		    $strReturn .= $this->objToolkit->divider();
	                $strReturn .= $this->objToolkit->getTextRow($this->getText("element_installer_hint"));
	    		    $strReturn .= $this->objToolkit->listHeader();
	    		    foreach ($arrElementsToInstall as $strKey => $strInstaller) {
	    		    	$strReturn .= $this->objToolkit->listRow2Image(getImageAdmin("icon_dot.gif"), $strInstaller, $this->objToolkit->listButton(getLinkAdmin("pages", "installElement", "&elementName=".$strKey, $this->getText("element_install"), $this->getText("element_install"), "icon_install.gif")), $intI++);
	    		    }

	    		    $strReturn .= $this->objToolkit->listFooter();
	    		}
    		}


		}
		else
			$strReturn .= $this->getText("fehler_recht");

		return $strReturn;
	}

	/**
	 * Returns the form to edit / create an element
	 *
	 * @param string $strMode new || edit
	 * @return string
	 */
	private function actionElementNew($strMode = "new") {
		$strReturn = "";
		if($this->objRights->rightRight1($this->getModuleSystemid($this->arrModule["modul"]))) {
			//Object to handle the filesystem
			$objFilesystem = new class_filesystem();
			$strTemplateID = $this->objTemplate->readTemplate("/module/modul_pages/elemente.tpl", "element_neu");

			//Which Mode?
			if($strMode == "new") {
				//Build the form
				$strReturn .= $this->objToolkit->getValidationErrors($this);
				$strReturn .= $this->objToolkit->formHeader(getLinkAdminHref($this->arrModule["modul"], "saveElement"));
				$strReturn .= $this->objToolkit->formInputText("element_name", $this->getText("element_name"), $this->getParam("element_name"));
				$strReturn .= $this->objToolkit->formInputText("element_cachetime", $this->getText("element_cachetime"), $this->getParam("element_cachetime"));
				$strReturn .= $this->objToolkit->formTextRow($this->getText("element_cachetime_hint"));
                $strReturn .= $this->objToolkit->divider();

				$strReturn .= $this->objToolkit->formInputHidden("elementid", 0);
				$strReturn .= $this->objToolkit->formInputHidden("modus", "new");
				//Fetch Admin classes
				$arrClasses = $objFilesystem->getFilelist("/admin/elemente", ".php");
				$arrClassesAdmin = array();
				foreach($arrClasses as $strClass)
					$arrClassesAdmin[$strClass] = $strClass;
				$strReturn .= $this->objToolkit->formInputDropdown("element_admin", $arrClassesAdmin, $this->getText("element_admin"), $this->getParam("element_admin"));

				//Fetch Portal-Classes
				$arrClassesPortal = array();
				$arrClasses = $objFilesystem->getFilelist("/portal/elemente", ".php");
				foreach($arrClasses as $strClass)
					$arrClassesPortal[$strClass] = $strClass;
				$strReturn .= $this->objToolkit->formInputDropdown("element_portal", $arrClassesPortal, $this->getText("element_portal"), $this->getParam("element_portal"));

				$strReturn .= $this->objToolkit->divider();

				//Repeatable?
				$arrRepeat = array();
				$arrRepeat[1] = $this->getText("option_ja");
				$arrRepeat[0] = $this->getText("option_nein");
				$strReturn .= $this->objToolkit->formInputDropdown("element_repeat", $arrRepeat, $this->getText("element_repeat"), $this->getParam("element_repeat"));
				$strReturn .= $this->objToolkit->formInputSubmit($this->getText("submit"));
				$strReturn .= $this->objToolkit->formClose();
			}
			elseif ($strMode == "edit") {
				//Load data of the element
				$objData = new class_modul_pages_element($this->getParam("elementid"));

				//Build the form
				$strReturn .= $this->objToolkit->getValidationErrors($this);
				$strReturn .= $this->objToolkit->formHeader(getLinkAdminHref($this->arrModule["modul"], "saveElement"));
				$strReturn .= $this->objToolkit->formInputText("element_name", $this->getText("element_name"), $objData->getStrName());
				$strReturn .= $this->objToolkit->formInputText("element_cachetime", $this->getText("element_cachetime"), $objData->getIntCachetime());
				$strReturn .= $this->objToolkit->formTextRow($this->getText("element_cachetime_hint"));
                $strReturn .= $this->objToolkit->divider();

				$strReturn .= $this->objToolkit->formInputHidden("elementid", $this->getParam("elementid"));
				$strReturn .= $this->objToolkit->formInputHidden("modus", "edit");
				//Fetch Admin classes
				$arrClasses = $objFilesystem->getFilelist("/admin/elemente", ".php");
				$arrClassesAdmin = array();
				foreach($arrClasses as $strClass)
					$arrClassesAdmin[$strClass] = $strClass;
				$strReturn .= $this->objToolkit->formInputDropdown("element_admin", $arrClassesAdmin, $this->getText("element_admin"), $objData->getStrClassAdmin());

				//Fetch Portal-Classes
				$arrClassesPortal = array();
				$arrClasses = $objFilesystem->getFilelist("/portal/elemente", ".php");
				foreach($arrClasses as $strClass)
					$arrClassesPortal[$strClass] = $strClass;
				$strReturn .= $this->objToolkit->formInputDropdown("element_portal", $arrClassesPortal, $this->getText("element_portal"), $objData->getStrClassPortal());

				$strReturn .= $this->objToolkit->divider();

				//Repeatable?
				$arrRepeat = array();
				$arrRepeat[1] = $this->getText("option_ja");
				$arrRepeat[0] = $this->getText("option_nein");
				$strReturn .= $this->objToolkit->formInputDropdown("element_repeat", $arrRepeat, $this->getText("element_repeat"), $objData->getIntRepeat());
				$strReturn .= $this->objToolkit->formInputSubmit($this->getText("submit"));
				$strReturn .= $this->objToolkit->formClose();
			}
		}
		else
			$strReturn .= $this->getText("fehler_recht");

		return $strReturn;
	}

	/**
	 * Tries to install the passed element by using the elements' installer placed in the
	 * /installer-folder
	 *
	 * @return string, "" in case of success
	 */
	private function actionInstallElement() {
        $strReturn = "";
		if($this->objRights->rightRight1($this->getModuleSystemid($this->arrModule["modul"]))) {
            $strElementToInstall = $this->getParam("elementName");

    		$objFilesystem = new class_filesystem();
    		//load installers available
    		$arrInstallers = $objFilesystem->getFilelist("/installer");

    		foreach($arrInstallers as $intKey => $strFile) {
    			if(uniStrReplace(".php", "", $strFile) == $strElementToInstall) {
                    include_once(_realpath_."/installer/".$strFile);
        			//Creating an object....
        			$strClass = "class_".str_replace(".php", "", $strFile);
        			$objInstaller = new $strClass();

        			$strInstallLog = $objInstaller->doPostInstall();
        			$strInstallLog .= "Done.\n";
        			$strReturn .= $this->objToolkit->getPreformatted(array($strInstallLog));
    			    break;
    			}
    		}
		}
		else
			$strReturn .= $this->getText("fehler_recht");

		return $strReturn;
	}

	/**
	 * Saves a passed element
	 *
	 * @return string, "" in case of success
	 */
	private function actionSaveElement() {
		$strReturn = "";
		if($this->objRights->rightRight1($this->getModuleSystemid($this->arrModule["modul"]))) {
			//Mode
			if($this->getParam("modus") == "edit") {
				//Update
				$objElement = new class_modul_pages_element($this->getParam("elementid"));
				$objElement->setStrName($this->getParam("element_name"));
				$objElement->setStrClassAdmin($this->getParam("element_admin"));
				$objElement->setStrClassPortal($this->getParam("element_portal"));
				$objElement->setIntCachetime($this->getParam("element_cachetime"));
				$objElement->setIntRepeat($this->getParam("element_repeat"));

				if(!$objElement->updateObjectToDb())
				    throw new class_exception($this->getText("element_bearbeiten_fehler"), class_exception::$level_ERROR);

				$this->flushCompletePagesCache();
			}
			elseif ($this->getParam("modus") == "new") {
				//Insert
				$objElement = new class_modul_pages_element("");
				$objElement->setStrName($this->getParam("element_name"));
				$objElement->setStrClassAdmin($this->getParam("element_admin"));
				$objElement->setStrClassPortal($this->getParam("element_portal"));
				$objElement->setIntCachetime($this->getParam("element_cachetime"));
				$objElement->setIntRepeat($this->getParam("element_repeat"));

				if(!$objElement->updateObjectToDb())
				    throw new class_exception($this->getText("element_anlegen_fehler"), class_exception::$level_ERROR);

				$this->flushCompletePagesCache();
			}
		}
		else
			$strReturn .= $this->getText("fehler_recht");

		return $strReturn;
	}

	/**
	 * Deletes an element from db / displays the warning-box
	 *
	 * @return string, "" in case of success
	 */
	private function actionDeleteElement() {
		$strReturn = "";
		if($this->objRights->rightRight1($this->getModuleSystemid($this->arrModule["modul"]))) {
			//Delete
            $objElement = new class_modul_pages_element($this->getParam("elementid"));
			if(!$objElement->deleteElement())
			    throw new class_exception($this->getText("element_loeschen_fehler"), class_exception::$level_ERROR);

			$this->flushCompletePagesCache();
		}
		else
			$strReturn .= $this->getText("fehler_recht");

		return $strReturn;
	}


	/**
	 * Creates a form to update placeholder in the database
	 *
	 * @return string
	 */
	private function actionUpdatePlaceholder() {
        $strReturn = "";
        if($this->objRights->rightRight3($this->getModuleSystemid($this->arrModule["modul"]))) {
            if($this->getParam("plToUpdate") == "") {
                $strReturn .= $this->objToolkit->getTextRow($this->getText("plUpdateHelp"));
                $strReturn .= $this->objToolkit->divider();
                $strReturn .= $this->objToolkit->formHeader(getLinkAdminHref($this->arrModule["modul"], "updatePlaceholder"));
                //fetch available templates
                //Load the available templates
				$objFilesystem = new class_filesystem();
				$arrTemplates = $objFilesystem->getFilelist("/templates/modul_pages", ".tpl");
				$arrTemplatesDD = array();
				if(count($arrTemplates) > 0) {
					foreach($arrTemplates as $strTemplate) {
						$arrTemplatesDD[$strTemplate] = $strTemplate;
					}
				}
				$strReturn .= $this->objToolkit->formInputDropdown("template", $arrTemplatesDD, $this->getText("template"));
				$strReturn .= $this->objToolkit->formInputText("plToUpdate", $this->getText("plToUpdate"));
				$strReturn .= $this->objToolkit->formInputText("plNew", $this->getText("plNew"));
				$strReturn .= $this->objToolkit->formInputSubmit($this->getText("plRename"));
                $strReturn .= $this->objToolkit->formClose();
            }
            else {
                $strReturn .= $this->objToolkit->getTextRow($this->getText("plUpdateHelp"));
                $strReturn .= $this->objToolkit->divider();
                if(class_modul_pages_pageelement::updatePlaceholders($this->getParam("template"), $this->getParam("plToUpdate"), $this->getParam("plNew")))
                    $strReturn .= $this->objToolkit->getTextRow($this->getText("plUpdateTrue"));
                else
                    $strReturn .= $this->objToolkit->getTextRow($this->getText("plUpdateFalse"));
            }
        }
        else
			$strReturn .= $this->getText("fehler_recht");

		return $strReturn;
	}


// -- Helferfunktionen ----------------------------------------------------------------------------------

    /**
     * Checks, if a new element already exists
     *
     * @return boo
     */
    private function checkElementExisting() {
        $objElement = class_modul_pages_element::getElement($this->getParam("element_name"));
        if($objElement != null && $objElement->getSystemid() != $this->getParam("elementid")) {
            $this->addValidationError("elementid", $this->getText("required_elementid"));
            return true;
        }
        else
            return false;
    }

    /**
     * Helper to generate a small path-navigation
     *
     * @return string
     */
    private function getPathNavigation() {
        $arrPath = $this->getPathArray();

        $arrPathLinks = array();
        $arrPathLinks[] = getLinkAdmin("pages", "list", "&unlockid=".$this->getSystemid()."&folderid=0", "&nbsp;/&nbsp;", " / ");

        foreach($arrPath as $strOneSystemid) {
            $arrFolder = $this->getSystemRecord($strOneSystemid);
            //Skip Elements: No sense to show in path-navigations
            if($arrFolder["system_module_nr"] == _pages_content_modul_id_)
                continue;

            if($arrFolder["system_module_nr"] == _pages_modul_id_)
                $arrPathLinks[] = getLinkAdmin("pages", "newPage", "&unlockid=".$this->getSystemid()."&systemid=".$strOneSystemid, $arrFolder["system_comment"], $arrFolder["system_comment"]);
            else
                $arrPathLinks[] = getLinkAdmin("pages", "list", "&unlockid=".$this->getSystemid()."&folderid=".$strOneSystemid, $arrFolder["system_comment"], $arrFolder["system_comment"]);
        }
        return $this->objToolkit->getPathNavigation($arrPathLinks);
    }
} //class_modul_pages_admin

?>