<?php
/*"******************************************************************************************************
*   (c) 2004-2006 by MulchProductions, www.mulchprod.de                                                 *
*   (c) 2007-2012 by Kajona, www.kajona.de                                                              *
*       Published under the GNU LGPL v2.1, see /system/licence_lgpl.txt                                 *
*-------------------------------------------------------------------------------------------------------*
*   $Id$                                  *
********************************************************************************************************/



/**
 * This class handles the admin-sided management of the pages
 * In this case, that are only the pages NOT yet the content
 *
 * @package module_pages
 * @author sidler@mulchprod.de
 */
class class_module_pages_admin extends class_admin_simple implements interface_admin  {

	/**
	 * Constructor
	 *
	 */
	public function __construct() {

        $this->setArrModuleEntry("modul", "pages");
        $this->setArrModuleEntry("moduleId", _pages_modul_id_);

		parent::__construct();
        if($this->getParam("unlockid") != "") {
            $objLockmanager = new class_lockmanager($this->getParam("unlockid"));
            $objLockmanager->unlockRecord();
		}

	}


    public function getOutputModuleNavi() {
	    $arrReturn = array();
		$arrReturn[] = array("right", getLinkAdmin("right", "change", "&changemodule=".$this->arrModule["modul"],  $this->getLang("commons_module_permissions"), "", "", true, "adminnavi"));
		$arrReturn[] = array("", "");
		$arrReturn[] = array("view", getLinkAdmin($this->arrModule["modul"], "list", "", $this->getLang("commons_list"), "", "", true, "adminnavi"));
	    $arrReturn[] = array("view", getLinkAdmin($this->arrModule["modul"], "listAll", "", $this->getLang("modul_liste_alle"), "", "", true, "adminnavi"));
		$arrReturn[] = array("", "");
	    $arrReturn[] = array("edit", getLinkAdmin($this->arrModule["modul"], "newPage", "&systemid=".$this->getSystemid(), $this->getLang("modul_neu"), "", "", true, "adminnavi"));
		$arrReturn[] = array("", "");
		$arrReturn[] = array("right1", getLinkAdmin($this->arrModule["modul"], "listElements", "", $this->getLang("modul_elemente"), "", "", true, "adminnavi"));
	    $arrReturn[] = array("right1", getLinkAdmin($this->arrModule["modul"], "newElement", "", $this->getLang("modul_element_neu"), "", "", true, "adminnavi"));
		$arrReturn[] = array("", "");
	    $arrReturn[] = array("right3", getLinkAdmin($this->arrModule["modul"], "updatePlaceholder", "", $this->getLang("updatePlaceholder"), "", "", true, "adminnavi"));
		return $arrReturn;
	}

    protected function actionSortUp() {
        $this->setPositionAndReload($this->getSystemid(), "upwards");
    }

    protected function actionSortDown() {
        $this->setPositionAndReload($this->getSystemid(), "downwards");
    }

    protected function actionShowHistory() {
        $strReturn = "";
        $objCommon = new class_module_system_common($this->getSystemid());
        if($objCommon->rightEdit()) {
            $objSystemAdmin = class_module_system_module::getModuleByName("system")->getAdminInstanceOfConcreteModule();
            $strReturn .= $objSystemAdmin->actionGenericChangelog($this->getSystemid(), $this->arrModule["modul"], "showHistory");
        }
        else
            $strReturn = $this->getLang("commons_error_permissions");

        return $strReturn;
    }

    /**
     * Renders the form to create a new entry
     * @return string
     * @permissions edit
     */
    protected function actionNew() {
        // TODO: Implement actionNew() method.
    }

    /**
     * Renders the form to edit an existing entry
     * @return string
     * @permissions edit
     */
    protected function actionEdit() {
        $objEntry = class_objectfactory::getInstance()->getObject($this->getSystemid());
        if($objEntry instanceof class_module_pages_page) {
            if($objEntry->getIntType() == class_module_pages_page::$INT_TYPE_ALIAS)
                $this->adminReload(getLinkAdminHref($this->getArrModule("modul"), "editAlias", "&systemid=".$objEntry->getSystemid()));
            else
                $this->adminReload(getLinkAdminHref($this->getArrModule("modul"), "editPage", "&systemid=".$objEntry->getSystemid()));
        }
        else if($objEntry instanceof class_module_pages_folder) {
            $this->adminReload(getLinkAdminHref($this->getArrModule("modul"), "editFolder", "&systemid=".$objEntry->getSystemid()));
        }
    }


    /**
	 * Creates a list of sites in the current folder
	 *
	 * @return string
     * @autoTestable
     * @permissions view
	 */
	protected function actionList() {

        $strFolder = "";
        $arrFolder = class_module_pages_folder::getFolderList($this->getSystemid());
        if(count($arrFolder) > 0) {
            $objArraySectionIterator = new class_array_section_iterator(count($arrFolder));
            $objArraySectionIterator->setPageNumber(1);
            $objArraySectionIterator->setIntElementsPerPage($objArraySectionIterator->getNumberOfElements());
            $objArraySectionIterator->setArraySection($arrFolder);
            $strFolder = $this->renderList($objArraySectionIterator, false, "folderList");

            $strFolder .= $this->objToolkit->divider();
            $strFolder = $this->objToolkit->getLayoutFolderPic($strFolder, $this->getLang("klapper"));
        }

        //Collect the pages belonging to the current parent
        $arrPages = class_module_pages_folder::getPagesInFolder($this->getSystemid());
        $objArraySectionIterator = new class_array_section_iterator(count($arrPages));
        $objArraySectionIterator->setPageNumber(1);
        $objArraySectionIterator->setIntElementsPerPage($objArraySectionIterator->getNumberOfElements());
        $objArraySectionIterator->setArraySection($arrPages);
        $strPages = $this->renderList($objArraySectionIterator, true, "pagesList");

        $strPathNavi = "";

        if(count(class_module_languages_language::getAllLanguages(true)) > 1) {
            $arrToolbarEntries = array();
            $objLanguages = new class_module_languages_admin();
            $arrToolbarEntries[] = $objLanguages->getLanguageSwitch();
            $strPathNavi .= $this->objToolkit->getContentToolbar($arrToolbarEntries);
        }
        else
            $strPathNavi .= $this->objToolkit->divider();

        return $strPathNavi.$this->generateTreeView($strFolder.$strPages);
	}

    protected function renderLevelUpAction($strListIdentifier) {
        if($strListIdentifier == "pagesList") {
            if(validateSystemid($this->getSystemid()) && $this->getSystemid() != $this->getObjModule()->getSystemid()  ) {
                $objPrevFolder = new class_module_pages_folder($this->getSystemid());
                return $this->objToolkit->listButton(getLinkAdmin("pages", "list", "&systemid=".$objPrevFolder->getPrevId(), $this->getLang("commons_one_level_up"), $this->getLang("commons_one_level_up"), "icon_folderActionLevelup.gif"));
            }
        }
        return parent::renderLevelUpAction($strListIdentifier);
    }

    protected function renderEditAction(class_model $objListEntry) {
        if($objListEntry instanceof class_module_pages_element) {
            return $this->objToolkit->listButton(getLinkAdmin("pages", "editElement", "&systemid=".$objListEntry->getSystemid(), $this->getLang("element_bearbeiten"), $this->getLang("element_bearbeiten"), "icon_pencil.gif"));
        }
        else
            return parent::renderEditAction($objListEntry);
    }


    protected function renderDeleteAction(interface_model $objListEntry) {
        if($objListEntry instanceof class_module_pages_page && $objListEntry->rightDelete()) {
            return $this->objToolkit->listDeleteButton($objListEntry->getStrDisplayName(), $this->getLang("seite_loeschen_frage"), getLinkAdminHref($this->arrModule["modul"], "deletePageFinal", "&systemid=".$objListEntry->getSystemid()));
        }
        else if($objListEntry instanceof class_module_pages_folder) {
            return $this->objToolkit->listDeleteButton($objListEntry->getStrDisplayName(), $this->getLang("pages_ordner_loeschen_frage"), getLinkAdminHref($this->arrModule["modul"], "deleteFolderFinal", "&systemid=".$objListEntry->getSystemid()));
        }
        else if($objListEntry instanceof class_module_pages_element) {
            return $this->objToolkit->listDeleteButton($objListEntry->getStrDisplayName(), $this->getLang("element_loeschen_frage"), getLinkAdminHref($this->arrModule["modul"], "deleteElement", "&elementid=".$objListEntry->getSystemid()));
        }
        else
            return parent::renderDeleteAction($objListEntry);
    }

    protected function renderStatusAction(class_model $objListEntry) {
        if($objListEntry instanceof class_module_pages_element) {
            return "";
        }
        else
            return parent::renderStatusAction($objListEntry);
    }


    protected function renderAdditionalActions(class_model $objListEntry) {

        if($objListEntry instanceof class_module_pages_page) {
            $arrReturn = array();
            if($objListEntry->getIntType() == class_module_pages_page::$INT_TYPE_ALIAS) {
                $objTargetPage = class_module_pages_page::getPageByName($objListEntry->getStrAlias());
                if($objTargetPage->rightEdit())
                    $arrReturn[] =  $this->objToolkit->listButton(getLinkAdmin("pages_content", "list", "&systemid=".$objTargetPage->getStrSystemid(), "", $this->getLang("seite_inhalte"), "icon_page.gif"));

                $arrReturn[] = $this->objToolkit->listButton(getLinkAdmin($this->getArrModule("modul"), "list", "&systemid=".$objListEntry->getSystemid(), "", $this->getLang("page_sublist"), "icon_treeBranchOpen.gif"));
            }
            else if($objListEntry->rightEdit()) {
                $arrReturn[] =  $this->objToolkit->listButton(getLinkAdmin("pages_content", "list", "&systemid=".$objListEntry->getSystemid(), "", $this->getLang("seite_inhalte"), "icon_page.gif"));
                $arrReturn[] = $this->objToolkit->listButton(getLinkAdmin($this->getArrModule("modul"), "copyPage", "&systemid=".$objListEntry->getSystemid(), "", $this->getLang("seite_copy"), "icon_copy.gif"));

                $arrReturn[] = $this->objToolkit->listButton(getLinkAdmin($this->getArrModule("modul"), "list", "&systemid=".$objListEntry->getSystemid(), "", $this->getLang("page_sublist"), "icon_treeBranchOpen.gif"));
            }

            return $arrReturn;
        }
        else if($objListEntry instanceof class_module_pages_folder) {
            $arrReturn[] = $this->objToolkit->listButton(getLinkAdmin("pages", "list", "&systemid=".$objListEntry->getSystemid(), $this->getLang("pages_ordner_oeffnen"), $this->getLang("pages_ordner_oeffnen"), "icon_folderActionOpen.gif"));

            if(_system_changehistory_enabled_ != "false")
                $arrReturn[] = $this->objToolkit->listButton(getLinkAdmin("pages", "showHistory", "&systemid=".$objListEntry->getSystemid(), "", $this->getLang("show_history"), "icon_history.gif"));

            return $arrReturn;
        }
        else
            return parent::renderAdditionalActions($objListEntry);
    }

    protected function getNewEntryAction($strListIdentifier) {
        if($strListIdentifier != "folderList" && $strListIdentifier != "elementList" && $this->getObjModule()->rightEdit()) {
            $arrReturn = array();
            $arrReturn[] = $this->objToolkit->listButton(getLinkAdmin($this->getArrModule("modul"), "newPage", "&systemid=".$this->getSystemid(), $this->getLang("modul_neu"), $this->getLang("modul_neu"), "icon_new.gif"));
            $arrReturn[] = $this->objToolkit->listButton(getLinkAdmin($this->getArrModule("modul"), "newAlias", "&systemid=".$this->getSystemid(), $this->getLang("modul_neu_alias"), $this->getLang("modul_neu_alias"), "icon_new_alias.gif"));

            return $arrReturn;
        }
        else if($strListIdentifier == "folderList" && $this->getObjModule()->rightRight2()) {
            if((!validateSystemid($this->getSystemid()) || $this->getSystemid() == $this->getObjModule()->getSystemid()))
                return $this->objToolkit->listButton(getLinkAdmin($this->getArrModule("modul"), "newFolder", "&systemid=".$this->getSystemid(), $this->getLang("commons_create_folder"), $this->getLang("commons_create_folder"), "icon_new.gif"));

        }
        else if($strListIdentifier == "elementList" && $this->getObjModule()->rightRight1()) {
            return $this->objToolkit->listButton(getLinkAdmin($this->getArrModule("modul"), "newElement", "", $this->getLang("modul_element_neu"), $this->getLang("modul_element_neu"), "icon_new.gif"));
        }
        else
            return "";

    }


    /**
	 * Returns a list of all pages in the system, not worrying about the folders -> Flat List
	 *
	 * @return string The complete List
     * @autoTestable
     * @permissions view
	 */
	protected function actionListAll() {
		$strReturn = "";

        $objArraySectionIterator = new class_array_section_iterator(class_module_pages_page::getNumberOfPagesAvailable());
        $objArraySectionIterator->setPageNumber((int)($this->getParam("pv") != "" ? $this->getParam("pv") : 1));
        $objArraySectionIterator->setArraySection(class_module_pages_page::getAllPages($objArraySectionIterator->calculateStartPos(), $objArraySectionIterator->calculateEndPos()));
        $strReturn .= $this->renderList($objArraySectionIterator);

        $strPathNavi = "";
        if(count(class_module_languages_language::getAllLanguages(true)) > 1) {
            $arrToolbarEntries = array();
            $objLanguages = new class_module_languages_admin();
            $arrToolbarEntries[] = $objLanguages->getLanguageSwitch();
            $strPathNavi .= $this->objToolkit->getContentToolbar($arrToolbarEntries);
        }

        return $strPathNavi.$strReturn;
	}


    protected function actionEditPage() {
        return $this->actionNewPage("edit");
    }

    protected function actionNewAlias() {
        return $this->actionNewPage("new", true);
    }

    protected function actionEditAlias() {
        return $this->actionNewPage("edit", true);
    }

    /**
     * Shows the form to create a new Site
     *
     * @param string $strMode
     * @param bool $bitAlias
     * @param class_admin_formgenerator|null $objForm
     * @return string The form
     * @autoTestable
     * @permissions edit
     */
	protected function actionNewPage($strMode = "new", $bitAlias = false, class_admin_formgenerator $objForm = null) {
		$strReturn = "";

        $objPage = new class_module_pages_page();
        if($strMode == "edit") {
            $objPage = new class_module_pages_page($this->getSystemid());
            if(!$objPage->rightEdit($this->getSystemid()))
                return $this->getLang("commons_error_permissions");
        }

        $arrToolbarEntries = array();
        if(!$bitAlias) {
            if($strMode == "edit") {
                $arrToolbarEntries[] = "<a href=\"".getLinkAdminHref("pages", "editPage", "&systemid=".$this->getSystemid())."\" style=\"background-image:url("._skinwebpath_."/pics/icon_page.gif);\">".$this->getLang("contentToolbar_pageproperties")."</a>";
                $arrToolbarEntries[] = "<a href=\"".getLinkAdminHref("pages_content", "list", "&systemid=".$this->getSystemid())."\" style=\"background-image:url("._skinwebpath_."/pics/icon_pencil.gif);\">".$this->getLang("contentToolbar_content")."</a>";
                $arrToolbarEntries[] = "<a href=\"".getLinkPortalHref($objPage->getStrName(), "", "", "&preview=1", "", $this->getLanguageToWorkOn())."\" target=\"_blank\" style=\"background-image:url("._skinwebpath_."/pics/icon_lens.gif);\">".$this->getLang("contentToolbar_preview")."</a>";
            }
            $objLanguages = new class_module_languages_admin();
            $arrToolbarEntries[3] = $objLanguages->getLanguageSwitch();
            if($this->getParam("pe") != 1)
                $strReturn .= $this->objToolkit->getContentToolbar($arrToolbarEntries, 0)."<br />";
        }

        if($objForm == null)
            $objForm = $this->getPageForm($bitAlias, $objPage, $strMode);


        if($bitAlias)
            $strReturn .= $objForm->renderForm(getLinkAdminHref($this->getArrModule("modul"), "saveAlias"));
        else
            $strReturn .= $objForm->renderForm(getLinkAdminHref($this->getArrModule("modul"), "savePage"));

        //sir: removed, no longer included directly. now included by overlay.
//        if($strMode == "edit") {
//            //include the tags, if present
//            $objTags = class_module_system_module::getModuleByName("tags");
//            if($objTags != null) {
//                /**
//                 * @var class_module_tags_admin
//                 */
//                $objTagsInstance = $objTags->getAdminInstanceOfConcreteModule();
//                $strReturn .= $objTagsInstance->getTagForm($objPage->getSystemid(), $objPage->getStrLanguage());
//            }
//        }

		return $strReturn;
	}


    private function getPageForm($bitAlias, class_module_pages_page $objPage, $strMode) {

        //Load all the Templates available
        $arrTemplates = class_resourceloader::getInstance()->getTemplatesInFolder("/module_pages");

        $arrTemplatesDD = array();
        if(count($arrTemplates) > 0)
            foreach($arrTemplates as $strTemplate)
                $arrTemplatesDD[$strTemplate] = $strTemplate;

        //remove template of master-page when editing a regular page
        $objMasterPage = class_module_pages_page::getPageByName("master");
        if($objPage->getSystemid() == "" || ($objMasterPage->getSystemid() != $objPage->getSystemid() ) ) {
            unset($arrTemplatesDD[$objMasterPage->getStrTemplate()]);
        }

        $strPagesBrowser = getLinkAdminDialog(
            "pages",
            "pagesFolderBrowser",
            "&form_element=page_folder_name&pages=1&elements=false&folder=1&pagealiases=1",
            $this->getLang("commons_open_browser"),
            $this->getLang("commons_open_browser"),
            "icon_externalBrowser.gif",
            $this->getLang("commons_open_browser")
        );


        $objForm = new class_admin_formgenerator("page", $objPage);
        if($bitAlias)
            $objForm->addField(new class_formentry_hidden("page", "name"))->setStrValue(generateSystemid())->setStrLabel($this->getLang("name"));
        else
            $objForm->addDynamicField("name")->setStrLabel($this->getLang("name"));

        $objForm->addDynamicField("browsername")->setStrLabel($this->getLang("browsername"));

        if(!$bitAlias) {
            $objForm->addDynamicField("seostring")->setStrLabel($this->getLang("seostring"));
            $objForm->addDynamicField("desc")->setStrLabel($this->getLang("commons_description"));
            $objForm->addDynamicField("keywords")->setStrLabel($this->getLang("keywords"));
        }

        $strParentId = $objPage->getPrevId();
        if(!validateSystemid($strParentId) && $strMode == "new")
            $strParentId = $this->getSystemid();

        $strFolderId = $this->getParam("page_folder_name_id");
        $strFolderName = $this->getParam("page_folder_name");
        if(!validateSystemid($strFolderId) && validateSystemid($strParentId)) {
            $objParent = class_objectfactory::getInstance()->getObject($strParentId);
            $strFolderId = $objParent->getSystemid();
            $strFolderName = $objParent->getStrDisplayName();
        }
        $objForm->addField(new class_formentry_text("page", "folder_name"))->setStrValue($strFolderName)->setBitReadonly(true)->setStrOpener($strPagesBrowser)->setStrLabel($this->getLang("page_folder_name"));
        $objForm->addField(new class_formentry_hidden("page", "folder_name_id"))->setStrValue($strFolderId);

        if(!$bitAlias) {

            /** @var $objField class_formentry_base */
            $objField = $objForm->addDynamicField("template")->setArrKeyValues($arrTemplatesDD)->setStrLabel($this->getLang("template"));
            if($strMode == "edit" && $objPage->getStrTemplate() == "")
                $objField->setStrHint( $this->getLang("templateNotSelectedBefore"));

            $bitReadonly = false;
            if(_pages_templatechange_ == "false") {
                if($objPage->getNumberOfElementsOnPage() != 0)
                    $bitReadonly = true;
            }
            $objField->setBitReadonly($bitReadonly);

            if($strMode == "new" && $this->getParam("page_template") == "")
                $objField->setStrValue(_pages_defaulttemplate_);

        }
        else {
            $objForm->addDynamicField("alias")->setStrHint($this->getLang("page_alias_hint"))->setBitMandatory(true)->setStrLabel($this->getLang("page_alias"));
        }

        $objForm->addField(new class_formentry_hidden("", "mode"))->setStrValue($strMode);
        return $objForm;
    }


    protected function actionSaveAlias() {
        return $this->actionSavePage(true);
    }

    /**
     * Saves a submitted page in the database (new Page!)
     *
     * @param bool $bitAlias
     * @return String, "" if successful
     * @permissions edit
     */
	protected function actionSavePage($bitAlias = false) {

        $objPage = new class_module_pages_page();
        if($this->getParam("mode") == "edit")
            $objPage = new class_module_pages_page($this->getSystemid());

        $objForm = $this->getPageForm($bitAlias, $objPage, $this->getParam("mode"));


        if(!$objForm->validateForm()) {
            return $this->actionNewPage($this->getParam("mode"), $bitAlias, $objForm);
        }


        $objForm->updateSourceObject();

        if($bitAlias)
            $objPage->setIntType(class_module_pages_page::$INT_TYPE_ALIAS);

        if(!$objPage->updateObjectToDb($this->getParam("page_folder_name_id")))
            throw new class_exception("Error saving new page to db", class_exception::$level_ERROR);

        if($this->getParam("pe") != "")
            $this->adminReload(getLinkAdminHref($this->getArrModule("modul"), "list", "&peClose=1&peRefreshPage=".$objPage->getStrName()));
        else
            $this->adminReload(getLinkAdminHref($this->getArrModule("modul"), "list", "systemid=".$objPage->getPrevId()));


        return "";
	}


    protected function actionChangeAlias() {
        return $this->actionNewPage("edit", true);
    }

	/**
	 * Delete a page and all associated elements
	 *
	 * @return string, "" in case of success
	 */
	protected function actionDeletePageFinal() {
		$strReturn = "";
        $objPage = new class_module_pages_page($this->getSystemid());
		if($objPage->rightDelete()) {
			//Are there any locked records on this page?
			if($objPage->getNumberOfLockedElementsOnPage() == 0) {
                $strPrevid = $objPage->getPrevId();
			    if(!$objPage->deleteObject())
			         throw new class_exception("Error deleting page from db", class_exception::$level_ERROR);

                $this->adminReload(getLinkAdminHref($this->arrModule["modul"], "list", "systemid=".$strPrevid));
			}
			else {
				//Print a message-box
				$strReturn .= $this->objToolkit->warningBox($this->getLang("ds_seite_gesperrt"));
			}

		}
		else
			$strReturn = $this->getLang("commons_error_permissions");

		return $strReturn;
	} //actionDeletePageFinal

	/**
	 * Invokes a deep copy of the current page
	 *
	 * @return string "" in case of success
	 */
	protected function actionCopyPage() {
	    $strReturn = "";
        $objPage = new class_module_pages_page($this->getSystemid());
		if($objPage->rightEdit($this->getSystemid())) {
			if(!$objPage->copyPage())
                throw new class_exception("Error while copying the page!", class_exception::$level_ERROR);

            $this->adminReload(getLinkAdminHref($this->arrModule["modul"], "list", "systemid=".$objPage->getPrevId()));
		}
		else
			$strReturn = $this->getLang("commons_error_permissions");

		return $strReturn;
	}


    /**
     * Returns a form to create a new folder
     *
     * @param string $strMode
     * @param class_admin_formgenerator|null $objForm
     * @return string
     * @permissions right2
     * @autoTestable
     */
	protected function actionNewFolder($strMode = "new", class_admin_formgenerator $objForm = null) {

        //if languages are installed, present a language switch right here
        $objLanguages = new class_module_languages_admin();
        $arrToolbarEntries[0] = $objLanguages->getLanguageSwitch();

        $strReturn = $this->objToolkit->getContentToolbar($arrToolbarEntries, 0)."<br />";


        if($strMode == "new")
            $objFolder = new class_module_pages_folder();
        else {
            $objFolder = new class_module_pages_folder($this->getSystemid());
            if(!$objFolder->rightEdit())
                return $this->getLang("commons_error_permissions");
        }

        if($objForm == null)
            $objForm = $this->getFolderForm($objFolder);
        $objForm->addField(new class_formentry_hidden("", "mode"))->setStrValue($strMode);

        return $strReturn.$objForm->renderForm(getLinkAdminHref($this->getArrModule("modul"), "folderSave"));
	}

    private function getFolderForm(class_module_pages_folder $objFolder) {
        $objForm = new class_admin_formgenerator("folder", $objFolder);
        $objForm->addDynamicField("name")->setStrLabel($this->getLang("ordner_name"));

        return $objForm;
    }

	/**
	 * Creates a form to edit a folder (rename it)
	 *
	 * @return string
     * @permissions right2
	 */
	protected function actionEditFolder() {
        return $this->actionNewFolder("edit");
	}

	/**
	 * Saves the posted Folder to database
	 *
	 * @return String, "" in case of success
     * @permissions right2
	 */
	protected function actionFolderSave() {

        if($this->getParam("mode") == "new")
            $objFolder = new class_module_pages_folder();
        else {
            $objFolder = new class_module_pages_folder($this->getSystemid());
            if(!$objFolder->rightEdit())
                return $this->getLang("commons_error_permissions");
        }

        $objForm = $this->getFolderForm($objFolder);
        if(!$objForm->validateForm())
            return $this->actionNewFolder($this->getParam("mode"), $objForm);

        $objForm->updateSourceObject();

        $objFolder->updateObjectToDb();
        $this->adminReload(getLinkAdminHref($this->getArrModule("modul"), "list"));

        return "";
	}

	/**
	 * Deletes a folder from Database. All subpages & subfolders turn up to top-level
	 *
	 * @return string, "" in case of success
	 */
	protected function actionDeleteFolderFinal() {
		$strReturn = "";
        $objFolder = new class_module_pages_folder($this->getSystemid());
		if($objFolder->rightDelete($this->getSystemid())) 	{
            $strPrevID = $objFolder->getPrevId();
			if($objFolder->deleteObject())
				$this->adminReload(getLinkAdminHref($this->arrModule["modul"], "list", "&systemid=".$strPrevID));
			else
				throw new class_exception($this->getLang("ordner_loeschen_fehler"), class_exception::$level_ERROR);
		}
		else
			$strReturn .= $this->getLang("commons_error_permissions");

		return $strReturn;
	}


    /**
     * Creates a pathnavigation through all folders till the current page / folder
     *
     * @return array
     */
    protected function getArrOutputNaviEntries() {
        $arrPathLinks = parent::getArrOutputNaviEntries();
        $arrPath = $this->getPathArray($this->getSystemid());
        //Link to root-folder
        foreach($arrPath as $strOneFolderID) {
            $objInstance = class_objectfactory::getInstance()->getObject($strOneFolderID);

            if($objInstance instanceof class_module_pages_folder) {
                $arrPathLinks[] = getLinkAdmin("pages", "list", "&systemid=".$strOneFolderID."&unlockid=".$this->getSystemid(), $objInstance->getStrName());
            }
            if($objInstance instanceof class_module_pages_page) {
                $arrPathLinks[] = getLinkAdmin("pages", "list", "&systemid=".$strOneFolderID."&unlockid=".$this->getSystemid(), $objInstance->getStrBrowsername());
            }
        }

        return $arrPathLinks;
    }

    /**
     * Generates the code needed to render the pages and folder as a tree-view element.
     * The elements themselves are loaded via ajax, so only the root-node and the initial
     * folding-params are generated right here.
     *
     * @param string $strSideContent
     * @return string
     * @permissions view
     */
    private function generateTreeView($strSideContent) {
        $strReturn = "";

        //generate the array of ids to expand initially
        $arrNodes = $this->getPathArray($this->getSystemid());
        array_unshift($arrNodes, $this->getModuleSystemid($this->arrModule["modul"]));
        $strReturn .= $this->objToolkit->getTreeview("KAJONA.admin.ajax.loadPagesTreeViewNodes", $this->getModuleSystemid($this->arrModule["modul"]), $arrNodes, $strSideContent, $this->getOutputModuleTitle(), getLinkAdminHref($this->arrModule["modul"]));
        return $strReturn;
    }



	/**
	 * Returns a list of all installed Elements
	 *
	 * @return string
     * @autoTestable
     * @permissions right1
	 */
	protected function actionListElements() {
		$strReturn = "";

        $objArraySectionIterator = new class_array_section_iterator(class_module_pages_element::getElementCount());
        $objArraySectionIterator->setPageNumber((int)($this->getParam("pv") != "" ? $this->getParam("pv") : 1));
        $objArraySectionIterator->setArraySection(class_module_pages_element::getAllElements($objArraySectionIterator->calculateStartPos(), $objArraySectionIterator->calculateEndPos()));
        $strReturn .= $this->renderList($objArraySectionIterator, false, "elementList");

        // ------------------------------------------------------------------------------------------
        // any element-installers of elements not yet installed?
        $arrElementsToInstall = array();
        //load installers available
        $arrInstallers = class_resourceloader::getInstance()->getFolderContent("/installer", array(".php"));

        if($arrInstallers !== false) {

            foreach($arrInstallers as $strPath => $strFile)
                if(strpos($strFile, ".php") === false || strpos($strFile, "installer_element") === false)
                    unset($arrInstallers[$strPath]);

            if(count($arrInstallers) > 0) {
                asort($arrInstallers);
                //Loading each installer
                foreach($arrInstallers as $strPath => $strInstaller) {
                    //Creating an object....
                    include_once(_realpath_.$strPath);
                    $strClass = "class_".str_replace(".php", "", $strInstaller);

                    /** @var interface_installer $objInstaller  */
                    $objInstaller = new $strClass();

                    $objSystem = class_module_system_module::getModuleByName("system");
                    if($objInstaller instanceof interface_installer ) {
                        $bitNeededSysversionInstalled = true;
                        //check, if a min version of the system is needed
                        if($objInstaller->getMinSystemVersion() != "") {
                            //the systems version to compare to

                            if(version_compare($objInstaller->getMinSystemVersion(), $objSystem->getStrVersion(), ">")) {
                                $bitNeededSysversionInstalled = false;
                            }
                        }

                        //all needed modules installed?
                        $bitRequired = true;
                        $arrModulesNeeded = $objInstaller->getNeededModules();
                        foreach($arrModulesNeeded as $strOneModule) {
                            $objTestModule = null;
                            try {
                                $objTestModule = class_module_system_module::getModuleByName($strOneModule, true);
                            }
                            catch (class_exception $objException) { }
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
            $intI = 0;
            if(count($arrElementsToInstall) > 0 ) {
                $strReturn .= $this->objToolkit->divider();
                $strReturn .= $this->objToolkit->getTextRow($this->getLang("element_installer_hint"));
                $strReturn .= $this->objToolkit->listHeader();
                foreach ($arrElementsToInstall as $strKey => $strInstaller) {
                    $strReturn .= $this->objToolkit->genericAdminList(generateSystemid(), $strInstaller, getImageAdmin("icon_dot.gif"), $this->objToolkit->listButton(getLinkAdmin("pages", "installElement", "&elementName=".$strKey, $this->getLang("element_install"), $this->getLang("element_install"), "icon_install.gif")), $intI++);
                }

                $strReturn .= $this->objToolkit->listFooter();
            }
        }

		return $strReturn;
	}


    protected function actionEditElement() {
        return $this->actionNewElement("edit");
    }

    /**
     * Returns the form to edit / create an element
     *
     * @param string $strMode new || edit
     * @param class_admin_formgenerator $objForm
     * @return string
     * @autoTestable
     * @permissions right1
     *
     */
	protected function actionNewElement($strMode = "new", class_admin_formgenerator $objForm = null) {

        if($strMode == "new")
            $objElement = new class_module_pages_element();
        else
            $objElement = new class_module_pages_element($this->getSystemid());

        if($objForm == null)
            $objForm = $this->getElementForm($objElement);
        $objForm->addField(new class_formentry_hidden("", "mode"))->setStrValue($strMode);

        return $objForm->renderForm(getLinkAdminHref($this->getArrModule("modul"), "saveElement"));
	}

    private function getElementForm(class_module_pages_element $objElement) {

        //Fetch Admin classes
        $arrClasses = class_resourceloader::getInstance()->getFolderContent("/admin/elements", array(".php"));
        $arrClassesAdmin = array();
        foreach($arrClasses as $strClass)
            $arrClassesAdmin[$strClass] = $strClass;

        //Fetch Portal-Classes
        $arrClassesPortal = array();
        $arrClasses = class_resourceloader::getInstance()->getFolderContent("/portal/elements", array(".php"));
        foreach($arrClasses as $strClass)
            $arrClassesPortal[$strClass] = $strClass;


        $objForm = new class_admin_formgenerator("element", $objElement);
        $objForm->addDynamicField("name")->setStrLabel($this->getLang("commons_name"));
        $objForm->addDynamicField("cachetime")->setStrHint($this->getLang("element_cachetime_hint"));
        $objForm->addField(new class_formentry_divider());
        $objForm->addDynamicField("classadmin")->setArrKeyValues($arrClassesAdmin);
        $objForm->addDynamicField("classportal")->setArrKeyValues($arrClassesPortal);
        $objForm->addField(new class_formentry_divider());
        $objForm->addDynamicField("repeat");

        return $objForm;
    }

	/**
	 * Tries to install the passed element by using the elements' installer placed in the
	 * /installer-folder
	 *
	 * @return string, "" in case of success
     * @permissions right1
	 */
	protected function actionInstallElement() {
        $strReturn = "";
        $strElementToInstall = $this->getParam("elementName");

        $arrInstallers = class_resourceloader::getInstance()->getFolderContent("/installer", array(".php"));

        foreach($arrInstallers as $strPath => $strFile) {
            if(uniStrReplace(".php", "", $strFile) == $strElementToInstall) {
                include_once(_realpath_.$strPath);
                //Creating an object....
                $strClass = "class_".str_replace(".php", "", $strFile);
                $objInstaller = new $strClass();

                $strInstallLog = $objInstaller->doPostInstall();
                $strInstallLog .= "Done.\n";
                $strReturn .= $this->objToolkit->getPreformatted(array($strInstallLog));
                break;
            }
        }

        $this->adminReload(getLinkAdminHref($this->arrModule["modul"], "listElements"));

		return $strReturn;
	}

	/**
	 * Saves a passed element
	 *
	 * @return string, "" in case of success
     * @permissions right1
	 */
	protected function actionSaveElement() {

        if($this->getParam("mode") == "new")
            $objElement = new class_module_pages_element();
        else
            $objElement = new class_module_pages_element($this->getSystemid());

        $objForm = $this->getElementForm($objElement);

        if(!$objForm->validateForm())
            return $this->actionNewElement($this->getParam("mode"), $objForm);

        $objForm->updateSourceObject();

        if(!$objElement->updateObjectToDb())
            throw new class_exception($this->getLang("element_anlegen_fehler"), class_exception::$level_ERROR);

        $this->flushCompletePagesCache();
        $this->adminReload(getLinkAdminHref($this->arrModule["modul"], "listElements"));
        return "";
	}

	/**
	 * Deletes an element from db / displays the warning-box
	 *
	 * @return string, "" in case of success
     * @permissions right1
	 */
	protected function actionDeleteElement() {
		$strReturn = "";
        $objElement = new class_module_pages_element($this->getParam("elementid"));
        if(!$objElement->deleteObject())
            throw new class_exception($this->getLang("element_loeschen_fehler"), class_exception::$level_ERROR);

        $this->flushCompletePagesCache();
        $this->adminReload(getLinkAdminHref($this->arrModule["modul"], "listElements"));

		return $strReturn;
	}


	/**
	 * Creates a form to update placeholder in the database
	 *
	 * @return string
     * @autoTestable
     * @permissions right3
	 */
	protected function actionUpdatePlaceholder() {
        $strReturn = "";
        if($this->getParam("plToUpdate") == "") {
            $strReturn .= $this->objToolkit->getTextRow($this->getLang("plUpdateHelp"));
            $strReturn .= $this->objToolkit->divider();
            $strReturn .= $this->objToolkit->formHeader(getLinkAdminHref($this->arrModule["modul"], "updatePlaceholder"));
            //fetch available templates
            //Load the available templates
            $objFilesystem = new class_filesystem();
            $arrTemplates = class_resourceloader::getInstance()->getTemplatesInFolder("/module_pages");
            $arrTemplatesDD = array();
            $arrTemplatesDD[-1] = $this->getLang("plUpdateAll");
            if(count($arrTemplates) > 0) {
                foreach($arrTemplates as $strTemplate) {
                    $arrTemplatesDD[$strTemplate] = $strTemplate;
                }
            }
            $strReturn .= $this->objToolkit->formInputDropdown("template", $arrTemplatesDD, $this->getLang("template"));
            $strReturn .= $this->objToolkit->formInputText("plToUpdate", $this->getLang("plToUpdate"));
            $strReturn .= $this->objToolkit->formInputText("plNew", $this->getLang("plNew"));
            $strReturn .= $this->objToolkit->formInputSubmit($this->getLang("plRename"));
            $strReturn .= $this->objToolkit->formClose();
        }
        else {
            $strReturn .= $this->objToolkit->getTextRow($this->getLang("plUpdateHelp"));
            $strReturn .= $this->objToolkit->divider();
            if(class_module_pages_pageelement::updatePlaceholders($this->getParam("template"), $this->getParam("plToUpdate"), $this->getParam("plNew")))
                $strReturn .= $this->objToolkit->getTextRow($this->getLang("plUpdateTrue"));
            else
                $strReturn .= $this->objToolkit->getTextRow($this->getLang("plUpdateFalse"));
        }

		return $strReturn;
	}


    /**
     * Checks, if a new element already exists
     *
     * @return bool
     */
    private function checkElementExisting() {
        $objElement = class_module_pages_element::getElement($this->getParam("element_name"));
        if($objElement != null && $objElement->getSystemid() != $this->getParam("elementid")) {
            $this->addValidationError("elementid", $this->getLang("required_elementid"));
            return true;
        }
        else
            return false;
    }


    /**
	 * Returns a list of folders in the pages-database

	 * @return String
     * @permissions view
	 */
	protected function actionPagesFolderBrowser() {
		$strReturn = "";
		$intCounter = 1;

        $this->setArrModuleEntry("template", "/folderview.tpl");

        if ($this->getParam("CKEditorFuncNum") != "") {
            $strReturn .= "<script type=\"text/javascript\">window.opener.KAJONA.admin.folderview.selectCallbackCKEditorFuncNum = ".(int)$this->getParam("CKEditorFuncNum").";</script>";
        }

        //param init
        $bitPages = ($this->getParam("pages") != "" ? true : false);
        $bitPageAliases = ($this->getParam("pagealiases") != "" ? true : false);
        $bitPageelements = ($this->getParam("elements") == "false" ? false : true);
        $bitFolder = ($this->getParam("folder") != "" ? true : false);
        $strSystemid = ($this->getSystemid() != "" ? $this->getSystemid() : $this->getModuleSystemid("pages") );
        $strElement = ($this->getParam("form_element") != "" ? $this->getParam("form_element") : "ordner_name");
        $strPageid = ($this->getParam("pageid") != "" ? $this->getParam("pageid") : "0" );


		$arrFolder = class_module_pages_folder::getFolderList($strSystemid);
        $objFolder = new class_module_pages_folder($strSystemid);
		$strLevelUp = "";

		if(validateSystemid($strSystemid) && $strSystemid != $this->getModuleSystemid($this->arrModule["modul"]))
			$strLevelUp = $objFolder->getPrevId();
		//but: when browsing pages the current level should be kept
		iF($strPageid != "0")
		   $strLevelUp = $strSystemid;

		$strReturn .= $this->objToolkit->listHeader();
		//Folder to jump one level up
		if(!$bitPages || $strLevelUp != "" || $bitFolder) {
			$strAction = $this->objToolkit->listButton(($strSystemid != "0" && $strLevelUp!= "") || $strPageid != "0" ? getLinkAdmin($this->arrModule["modul"], "pagesFolderBrowser", "&systemid=".$strLevelUp.($bitFolder ? "&folder=1" : "").($bitPages ? "&pages=1" : "").(!$bitPageelements ? "&elements=false" : "").($bitPageAliases ? "&pagealiases=1" : "")."&form_element=".$strElement.($this->getParam("bit_link")  != "" ? "&bit_link=1" : ""), $this->getLang("commons_one_level_up"), $this->getLang("commons_one_level_up"), "icon_folderActionLevelup.gif") :  " " );
			if($strSystemid == $this->getModuleSystemid($this->arrModule["modul"]) && (!$bitPages || $bitFolder))
				$strAction .= $this->objToolkit->listButton("<a href=\"#\" title=\"".$this->getLang("ordner_uebernehmen")."\" onmouseover=\"KAJONA.admin.tooltip.add(this);\" onclick=\"KAJONA.admin.folderview.selectCallback([['".$strElement."_id', '".$this->getModuleSystemid($this->arrModule["modul"])."'], ['".$strElement."', '']]);\">".getImageAdmin("icon_accept.gif"));

			$strReturn .= $this->objToolkit->genericAdminList(generateSystemid(), "..", getImageAdmin("icon_folderOpen.gif"), $strAction, $intCounter++);
		}

		if(count($arrFolder) > 0 && $strPageid == "0") {
			foreach($arrFolder as $objSingleFolder) {
				if($bitPages && !$bitFolder) {
					$strAction = $this->objToolkit->listButton(getLinkAdmin($this->arrModule["modul"], "pagesFolderBrowser", "&systemid=".$objSingleFolder->getSystemid().($bitPages ? "&pages=1" : "")."&form_element=".$strElement.($bitFolder ? "&folder=1" : "").(!$bitPageelements? "&elements=false" : "").($bitPageAliases ? "&pagealiases=1" : "").($this->getParam("bit_link")  != "" ? "&bit_link=1" : "")."", $this->getLang("pages_ordner_oeffnen"), $this->getLang("pages_ordner_oeffnen"), "icon_folderActionOpen.gif"));
                    $strReturn .= $this->objToolkit->simpleAdminList($objSingleFolder, $strAction, $intCounter++);
				}
				else {
				    $strAction = $this->objToolkit->listButton(getLinkAdmin($this->arrModule["modul"], "pagesFolderBrowser", "&systemid=".$objSingleFolder->getSystemid()."&form_element=".$strElement.($bitPages ? "&pages=1" : "").($bitFolder ? "&folder=1" : "").($this->getParam("bit_link")  != "" ? "&bit_link=1" : "").(!$bitPageelements? "&elements=false" : "").($bitPageAliases ? "&pagealiases=1" : ""), $this->getLang("pages_ordner_oeffnen"), $this->getLang("pages_ordner_oeffnen"), "icon_folderActionOpen.gif"));
					$strAction .= $this->objToolkit->listButton("<a href=\"#\" title=\"".$this->getLang("ordner_uebernehmen")."\" onmouseover=\"KAJONA.admin.tooltip.add(this);\" onclick=\"KAJONA.admin.folderview.selectCallback([['".$strElement."_id', '".$objSingleFolder->getSystemid()."'], ['".$strElement."', '".$objSingleFolder->getStrName()."']]); \">".getImageAdmin("icon_accept.gif"));
                    $strReturn .= $this->objToolkit->simpleAdminList($objSingleFolder, $strAction, $intCounter++);
				}
			}

		}
		$strReturn .= $this->objToolkit->listFooter();

		//Pages could be sent too
		if($bitPages && $strPageid == "0") {
			$strReturn .= $this->objToolkit->divider();
			$arrPages = class_module_pages_folder::getPagesInFolder($strSystemid);
			if(count($arrPages) > 0) {
				$strReturn .= $this->objToolkit->listHeader();
				foreach($arrPages as $objSinglePage) {
                    $arrSinglePage = array();
					//Should we generate a link ?
					if($this->getParam("bit_link") != "")
						$arrSinglePage["name2"] = getLinkPortalHref($objSinglePage->getStrName(), "", "", "", "", $this->getLanguageToWorkOn());
					else
						$arrSinglePage["name2"] = $objSinglePage->getStrName();

                    if ($objSinglePage->getIntType() == class_module_pages_page::$INT_TYPE_ALIAS) {
	                    $strAction = $this->objToolkit->listButton(getLinkAdmin($this->arrModule["modul"], "pagesFolderBrowser", "&systemid=".$objSinglePage->getSystemid()."&form_element=".$strElement.($bitPages ? "&pages=1" : "").($bitFolder ? "&folder=1" : "").($this->getParam("bit_link")  != "" ? "&bit_link=1" : "").(!$bitPageelements? "&elements=false" : "").($bitPageAliases ? "&pagealiases=1" : ""), $this->getLang("page_sublist"), $this->getLang("page_sublist"), "icon_treeBranchOpen.gif"));
	                    if ($bitPageAliases)
	                    	$strAction .= $this->objToolkit->listButton("<a href=\"#\" title=\"".$this->getLang("select_page")."\" onmouseover=\"KAJONA.admin.tooltip.add(this);\" onclick=\"KAJONA.admin.folderview.selectCallback([['".$strElement."_id', '".$objSinglePage->getSystemid()."'],['".$strElement."', '".$arrSinglePage["name2"]."']]);\">".getImageAdmin("icon_accept.gif")."</a>");

						$strReturn .= $this->objToolkit->simpleAdminList($objSinglePage, $strAction, $intCounter++);
                    }
                    else {
                        $strAction = $this->objToolkit->listButton(getLinkAdmin($this->arrModule["modul"], "pagesFolderBrowser", "&systemid=".$objSinglePage->getSystemid()."&form_element=".$strElement.($bitPages ? "&pages=1" : "").($bitFolder ? "&folder=1" : "").($this->getParam("bit_link")  != "" ? "&bit_link=1" : "").(!$bitPageelements? "&elements=false" : "").($bitPageAliases ? "&pagealiases=1" : ""), $this->getLang("page_sublist"), $this->getLang("page_sublist"), "icon_treeBranchOpen.gif"));
                        if($bitPageelements)
                            $strAction .= $this->objToolkit->listButton(getLinkAdmin($this->arrModule["modul"], "pagesFolderBrowser", "&systemid=".$strSystemid."&form_element=".$strElement."&pageid=".$objSinglePage->getSystemid().($this->getParam("bit_link")  != "" ? "&bit_link=1" : "").($bitPages ? "&pages=1" : "").($bitPageAliases ? "&pagealiases=1" : ""), $this->getLang("seite_oeffnen"), $this->getLang("seite_oeffnen"), "icon_folderActionOpen.gif"));
                        $strAction .= $this->objToolkit->listButton("<a href=\"#\" title=\"".$this->getLang("select_page")."\" onmouseover=\"KAJONA.admin.tooltip.add(this);\" onclick=\"KAJONA.admin.folderview.selectCallback([['".$strElement."_id', '".$objSinglePage->getSystemid()."'],['".$strElement."', '".$arrSinglePage["name2"]."']]);\">".getImageAdmin("icon_accept.gif")."</a>");
                        $strReturn .= $this->objToolkit->simpleAdminList($objSinglePage, $strAction, $intCounter++);

                    }
				}
				$strReturn .= $this->objToolkit->listFooter();
			}
		}

		//Load the list of pagelements available on the page
		if($strPageid != "0") {
		    $strReturn .= $this->objToolkit->divider();
            $arrPageelements = class_module_pages_pageelement::getElementsOnPage($strPageid, true, $this->getLanguageToWorkOn());
            $objPage = new class_module_pages_page($strPageid);
            if(count($arrPageelements) > 0) {
                $strReturn .= $this->objToolkit->listHeader();
                /** @var class_module_pages_pageelement $objOnePageelement */
                foreach($arrPageelements as $objOnePageelement) {
                    $arrSinglePage = array();
                    //Should we generate a link ?
					if($this->getParam("bit_link") != "")
						$arrSinglePage["name2"] = getLinkPortalHref($objPage->getStrName(), "", "", "", "", $this->getLanguageToWorkOn())."#".$objOnePageelement->getSystemid();
					else
						$arrSinglePage["name2"] = $objPage->getStrName()."#".$objOnePageelement->getSystemid();

					$strAction = $this->objToolkit->listButton("<a href=\"#\" title=\"".$this->getLang("seite_uebernehmen")."\" onmouseover=\"KAJONA.admin.tooltip.add(this);\" onclick=\"KAJONA.admin.folderview.selectCallback([['".$strElement."', '".$arrSinglePage["name2"]."']]);\">".getImageAdmin("icon_accept.gif")."</a>");
					$strReturn .= $this->objToolkit->simpleAdminList($objOnePageelement, $strAction, $intCounter++);
                }
                $strReturn .= $this->objToolkit->listFooter();
            }
		}

		return $strReturn;
	}

    /**
	 * Creates a list of sites reduced to match the filter passed.
     * Used e.g. by the page-selector.
     * @xml
	 *
	 * @return string
     * @permissions view
	 */
	protected function actionGetPagesByFilter() {
		$strReturn = "";
        $strFilter = $this->getParam("filter");
        $arrPages = class_module_pages_page::getAllPages(0, 0, $strFilter);

        $strReturn .= "<pages>\n";
        foreach ($arrPages as $objOnePage) {
            if($objOnePage->rightView()) {
                $strReturn .= "  <page>\n";
                $strReturn .= "    <title>".xmlSafeString($objOnePage->getStrName())."</title>\n";
                $strReturn .= "  </page>\n";
            }
        }
        $strReturn .= "</pages>\n";
		return $strReturn;
	}

    /**
     * Fetches all child-nodes (folders and pages) of the passed node.
     * Used by the tree-view in module-pages.
     *
     * @return string
     * @since 3.3.0
     * @xml
     * @permissions view
     */
    protected function actionGetChildNodes() {
        $strReturn = "";

        $strReturn .= "<entries>";

        $arrFolder = class_module_pages_folder::getFolderList($this->getSystemid());
        foreach ($arrFolder as $objSingleEntry) {
                if($objSingleEntry->rightView()) {
                    /** @var class_module_pages_folder $objSingleEntry */
                    if($objSingleEntry instanceof class_module_pages_folder) {
                        $strReturn .= "<folder>";
                        $strReturn .= "<name>".xmlSafeString($objSingleEntry->getStrDisplayName())."</name>";
                        $strReturn .= "<systemid>".$objSingleEntry->getSystemid()."</systemid>";
                        $strReturn .= "<link>".getLinkAdminHref("pages", "list", "systemid=".$objSingleEntry->getSystemid(), false)."</link>";
                        $strReturn .= "<isleaf>".(count(class_module_pages_folder::getPagesAndFolderList($objSingleEntry->getSystemid())) == 0 ? "true" : "false")."</isleaf>";
                        $strReturn .= "</folder>";
                    }
                }
            }


        $arrPages = class_module_pages_folder::getPagesInFolder($this->getSystemid());
        if(count($arrPages) > 0) {
            foreach ($arrPages as $objSingleEntry) {
                if($objSingleEntry->rightView()) {
                    /** @var class_module_pages_page $objSingleEntry */
                    if($objSingleEntry instanceof class_module_pages_page) {
                        $strReturn .= "<page>";
                        $strReturn .= "<name>".xmlSafeString($objSingleEntry->getStrDisplayName())."</name>";
                        $strReturn .= "<systemid>".$objSingleEntry->getSystemid()."</systemid>";
                        if($objSingleEntry->getIntType() == class_module_pages_page::$INT_TYPE_ALIAS)
                            $strReturn .= "<link></link>";
                        else
                            $strReturn .= "<link>".getLinkAdminHref("pages", "list", "&systemid=".$objSingleEntry->getSystemid(), false)."</link>";

                        $strReturn .= "<type>".$objSingleEntry->getIntType()."</type>";
                        $strReturn .= "<isleaf>".(count(class_module_pages_folder::getPagesAndFolderList($objSingleEntry->getSystemid())) == 0 ? "true" : "false")."</isleaf>";
                        $strReturn .= "</page>";
                    }

                }
            }
        }
        $strReturn .= "</entries>";

        return $strReturn;
    }

}

