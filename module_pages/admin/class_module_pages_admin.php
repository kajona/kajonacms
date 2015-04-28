<?php
/*"******************************************************************************************************
*   (c) 2004-2006 by MulchProductions, www.mulchprod.de                                                 *
*   (c) 2007-2015 by Kajona, www.kajona.de                                                              *
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
 * @module pages
 * @moduleId _pages_modul_id_
 */
class class_module_pages_admin extends class_admin_simple implements interface_admin {

    const STR_LIST_ALLPAGES = "STR_LIST_ALLPAGES";
    const STR_LIST_PAGES = "STR_LIST_PAGES";
    const STR_LIST_ELEMENTS = "STR_LIST_ELEMENTS";

    /**
     * @return array
     */
    public function getOutputModuleNavi() {
        $arrReturn = array();
        $arrReturn[] = array("view", class_link::getLinkAdmin($this->getArrModule("modul"), "list", "", $this->getLang("commons_list"), "", "", true, "adminnavi"));
        $arrReturn[] = array("", "");
        $arrReturn[] = array("right1", class_link::getLinkAdmin($this->getArrModule("modul"), "listElements", "", $this->getLang("modul_elemente"), "", "", true, "adminnavi"));
        $arrReturn[] = array("", "");
        $arrReturn[] = array("right3", class_link::getLinkAdmin($this->getArrModule("modul"), "updatePlaceholder", "", $this->getLang("action_update_placeholder"), "", "", true, "adminnavi"));
        return $arrReturn;
    }

    /**
     * Renders the form to create a new entry
     *
     * @return string
     * @permissions edit
     */
    protected function actionNew() {
        //in nearly every case, a new page should be created
        $this->adminReload(class_link::getLinkAdminHref($this->getArrModule("modul"), "newPage"));
        return "";
    }

    /**
     * Renders the form to edit an existing entry
     *
     * @return string
     * @permissions edit
     */
    protected function actionEdit() {
        /** @var $objEntry class_module_pages_page */
        $objEntry = class_objectfactory::getInstance()->getObject($this->getSystemid());
        if($objEntry instanceof class_module_pages_page) {
            if($objEntry->getIntType() == class_module_pages_page::$INT_TYPE_ALIAS) {
                $this->adminReload(class_link::getLinkAdminHref($this->getArrModule("modul"), "editAlias", "&systemid=".$objEntry->getSystemid()));
            }
            else {
                $this->adminReload(class_link::getLinkAdminHref($this->getArrModule("modul"), "editPage", "&systemid=".$objEntry->getSystemid()));
            }
        }
        else if($objEntry instanceof class_module_pages_folder) {
            $this->adminReload(class_link::getLinkAdminHref($this->getArrModule("modul"), "editFolder", "&systemid=".$objEntry->getSystemid()));
        }
        else if($objEntry instanceof class_module_pages_element) {
            $this->adminReload(class_link::getLinkAdminHref($this->getArrModule("modul"), "editElement", "&systemid=".$objEntry->getSystemid()));
        }
    }


    /**
     * @param string $strListIdentifier
     *
     * @return array
     */
    protected function getBatchActionHandlers($strListIdentifier) {
        if($strListIdentifier == class_module_pages_admin::STR_LIST_PAGES || $strListIdentifier == class_module_pages_admin::STR_LIST_ALLPAGES)
            return $this->getDefaultActionHandlers();

        return array();
    }

    /**
     * Creates a list of sites in the current folder
     *
     * @return string
     * @autoTestable
     * @permissions view
     */
    protected function actionList() {

        class_module_languages_admin::enableLanguageSwitch();

        $bitPeMode = $this->getParam("pe") != "";

        //Collect the pages belonging to the current parent
        $objArraySectionIterator = new class_array_section_iterator(class_module_pages_folder::getPagesAndFolderListCount($this->getSystemid()));
        $objArraySectionIterator->setPageNumber($this->getParam("pv"));
        $objArraySectionIterator->setArraySection(class_module_pages_folder::getPagesAndFolderList($this->getSystemid(), false, $objArraySectionIterator->calculateStartPos(), $objArraySectionIterator->calculateEndPos()));
        $strPages = $this->renderList($objArraySectionIterator, true, class_module_pages_admin::STR_LIST_PAGES, true);

        if($bitPeMode)
            $strReturn = $strPages;
        else
            $strReturn = $this->generateTreeView($strPages);

        return $strReturn;
    }

    /**
     * @param string $strListIdentifier
     *
     * @return string
     */
    protected function renderLevelUpAction($strListIdentifier) {
        if($strListIdentifier == class_module_pages_admin::STR_LIST_PAGES) {
            if(validateSystemid($this->getSystemid()) && $this->getSystemid() != $this->getObjModule()->getSystemid()) {
                $objPrevFolder = new class_module_pages_folder($this->getSystemid());
                return $this->objToolkit->listButton(
                    class_link::getLinkAdmin(
                        "pages",
                        "list",
                        "&systemid=".$objPrevFolder->getPrevId()."&pe=".$this->getParam("pe"),
                        $this->getLang("commons_one_level_up"),
                        $this->getLang("commons_one_level_up"),
                        "icon_folderActionLevelup"
                    )
                );
            }
        }
        return parent::renderLevelUpAction($strListIdentifier);
    }

    /**
     * @param class_model $objListEntry
     * @param bool $bitDialog
     *
     * @return string
     */
    protected function renderEditAction(class_model $objListEntry, $bitDialog = false) {
        if($objListEntry instanceof class_module_pages_element) {
            if($objListEntry->rightEdit())
                return $this->objToolkit->listButton(
                    getLinkAdmin(
                        "pages",
                        "editElement",
                        "&systemid=".$objListEntry->getSystemid(),
                        $this->getLang("element_bearbeiten"),
                        $this->getLang("element_bearbeiten"),
                        "icon_edit"
                    )
                );
        }
        else if($objListEntry instanceof class_module_pages_folder) {
            return parent::renderEditAction($objListEntry, true);
        }
        else
            return parent::renderEditAction($objListEntry);

        return "";
    }


    /**
     * @param interface_model|class_module_pages_page $objListEntry
     *
     * @return string
     */
    protected function renderDeleteAction(interface_model $objListEntry) {

        if($this->getParam("pe") != "")
            return "";

        $objLockmanager = $objListEntry->getLockManager();
        if(!$objLockmanager->isAccessibleForCurrentUser()) {
            return $this->objToolkit->listButton(class_adminskin_helper::getAdminImage("icon_deleteLocked", $this->getLang("commons_locked")));
        }
        else if($objListEntry instanceof class_module_pages_page && $objListEntry->rightDelete()) {

            return $this->objToolkit->listDeleteButton(
                $objListEntry->getStrDisplayName(), $this->getLang("seite_loeschen_frage"), class_link::getLinkAdminHref($this->getArrModule("modul"), "deletePageFinal", "&systemid=".$objListEntry->getSystemid())
            );
        }
        else if($objListEntry instanceof class_module_pages_folder && $objListEntry->rightDelete()) {
            return $this->objToolkit->listDeleteButton(
                $objListEntry->getStrDisplayName(), $this->getLang("pages_ordner_loeschen_frage"), class_link::getLinkAdminHref($this->getArrModule("modul"), "deleteFolderFinal", "&systemid=".$objListEntry->getSystemid())
            );
        }
        else if($objListEntry instanceof class_module_pages_element && $objListEntry->rightDelete()) {
            return $this->objToolkit->listDeleteButton(
                $objListEntry->getStrDisplayName(), $this->getLang("element_loeschen_frage"), class_link::getLinkAdminHref($this->getArrModule("modul"), "deleteElement", "&elementid=".$objListEntry->getSystemid())
            );
        }
        else
            return parent::renderDeleteAction($objListEntry);
    }

    /**
     * @param class_model $objListEntry
     * @param string $strAltActive tooltip text for the icon if record is active
     * @param string $strAltInactive tooltip text for the icon if record is inactive
     *
     * @return string
     */
    protected function renderStatusAction(class_model $objListEntry, $strAltActive = "", $strAltInactive = "") {
        if($objListEntry instanceof class_module_pages_element) {
            return "";
        }
        else
            return parent::renderStatusAction($objListEntry, $strAltActive, $strAltInactive);
    }

    /**
     * @param class_model|class_module_pages_page $objListEntry
     *
     * @return array
     */
    protected function renderAdditionalActions(class_model $objListEntry) {

        $bitPeMode = $this->getParam("pe") != "";

        if($objListEntry instanceof class_module_pages_page) {
            $arrReturn = array();
            if($objListEntry->getIntType() == class_module_pages_page::$INT_TYPE_ALIAS) {
                $objTargetPage = class_module_pages_page::getPageByName($objListEntry->getStrAlias());
                if(!$bitPeMode && $objTargetPage != null && $objTargetPage->rightEdit())
                    $arrReturn[] = $this->objToolkit->listButton(
                        class_link::getLinkAdmin("pages_content", "list", "&systemid=".$objTargetPage->getStrSystemid()."&pe=".$this->getParam("pe"), "", $this->getLang("seite_inhalte_alias"), "icon_page_alias")
                    );

                $arrReturn[] = $this->objToolkit->listButton(
                    class_link::getLinkAdmin($this->getArrModule("modul"), "list", "&systemid=".$objListEntry->getSystemid()."&pe=".$this->getParam("pe"), "", $this->getLang("page_sublist"), "icon_folderActionOpen")
                );
            }
            else if($objListEntry->rightView()) {

                if(!$bitPeMode && $objListEntry->rightEdit()) {
                    $arrReturn[] = $this->objToolkit->listButton(
                        class_link::getLinkAdmin("pages_content", "list", "&systemid=".$objListEntry->getSystemid()."&pe=".$this->getParam("pe"), "", $this->getLang("seite_inhalte"), "icon_page")
                    );
                }

                $arrReturn[] = $this->objToolkit->listButton(
                    class_link::getLinkAdmin($this->getArrModule("modul"), "list", "&systemid=".$objListEntry->getSystemid()."&pe=".$this->getParam("pe"), "", $this->getLang("page_sublist"), "icon_folderActionOpen")
                );
            }

            return $arrReturn;
        }
        else if($objListEntry instanceof class_module_pages_folder) {
            $arrReturn[] = $this->objToolkit->listButton(
                class_link::getLinkAdmin("pages", "list", "&systemid=".$objListEntry->getSystemid()."&pe=".$this->getParam("pe"), $this->getLang("pages_ordner_oeffnen"), $this->getLang("pages_ordner_oeffnen"), "icon_folderActionOpen")
            );
            return $arrReturn;
        }
        else
            return parent::renderAdditionalActions($objListEntry);
    }

    /**
     * @param class_model $objListEntry
     *
     * @return string
     */
    protected function renderCopyAction(class_model $objListEntry) {

        $bitPeMode = $this->getParam("pe") != "";
        if($bitPeMode)
            return "";

        if($objListEntry instanceof class_module_pages_element)
            return "";

        if($objListEntry instanceof class_module_pages_folder)
            return "";

        if($objListEntry instanceof class_module_pages_page && $objListEntry->getIntType() == class_module_pages_page::$INT_TYPE_ALIAS)
            return "";

        return parent::renderCopyAction($objListEntry);
    }


    /**
     * @param string $strListIdentifier
     * @param bool $bitDialog
     *
     * @return array|string
     */
    protected function getNewEntryAction($strListIdentifier, $bitDialog = false) {

        if($this->getParam("pe") != "")
            return "";

        $arrReturn = array();

        $objCurInstance = null;
        if(validateSystemid($this->getSystemid()))
            $objCurInstance = class_objectfactory::getInstance()->getObject($this->getSystemid());
        else
            $objCurInstance = $this->getObjModule();

        if($strListIdentifier != class_module_pages_admin::STR_LIST_ELEMENTS && $objCurInstance->rightEdit()) {
            $arrReturn[] = $this->objToolkit->listButton(
                class_link::getLinkAdmin($this->getArrModule("modul"), "newPage", "&systemid=".$this->getSystemid(), $this->getLang("action_new_page"), $this->getLang("action_new_page"), "icon_new")
            );
            $arrReturn[] = $this->objToolkit->listButton(
                class_link::getLinkAdmin($this->getArrModule("modul"), "newAlias", "&systemid=".$this->getSystemid(), $this->getLang("action_new_alias"), $this->getLang("action_new_alias"), "icon_new_alias")
            );

        }
        if($strListIdentifier != class_module_pages_admin::STR_LIST_ELEMENTS && $objCurInstance->rightRight2()) {
            if((!validateSystemid($this->getSystemid()) || $this->getSystemid() == $this->getObjModule()->getSystemid()))
                $arrReturn[] = $this->objToolkit->listButton(
                    class_link::getLinkAdminDialog($this->getArrModule("modul"), "newFolder", "&systemid=".$this->getSystemid(), $this->getLang("commons_create_folder"), $this->getLang("commons_create_folder"), "icon_new")
                );

        }
        if($strListIdentifier == class_module_pages_admin::STR_LIST_ELEMENTS && $this->getObjModule()->rightRight1()) {
            $arrReturn[] = $this->objToolkit->listButton(
                class_link::getLinkAdmin($this->getArrModule("modul"), "newElement", "", $this->getLang("action_new_element"), $this->getLang("action_new_element"), "icon_new")
            );
        }

        return $arrReturn;
    }

    /**
     * @return string
     */
    protected function actionEditPage() {
        return $this->actionNewPage("edit");
    }

    /**
     * @return string
     * @autoTestable
     */
    protected function actionNewAlias() {
        return $this->actionNewPage("new", true);
    }

    /**
     * @return string
     */
    protected function actionEditAlias() {
        return $this->actionNewPage("edit", true);
    }

    /**
     * Shows the form to create a new Site
     *
     * @param string $strMode
     * @param bool $bitAlias
     * @param class_admin_formgenerator|null $objForm
     *
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
        else if($strMode == "new") {
            $objPage->setSystemid($this->getSystemid());
        }

        $arrToolbarEntries = array();
        if(!$bitAlias) {
            if($strMode == "edit") {
                $arrToolbarEntries[] = "<a href=\"".class_link::getLinkAdminHref("pages", "editPage", "&systemid=".$this->getSystemid())."\">".class_adminskin_helper::getAdminImage("icon_edit").$this->getLang("contentToolbar_pageproperties")."</a>";
                $arrToolbarEntries[] = "<a href=\"".class_link::getLinkAdminHref("pages_content", "list", "&systemid=".$this->getSystemid())."\" >".class_adminskin_helper::getAdminImage("icon_page").$this->getLang("contentToolbar_content")."</a>";
                $arrToolbarEntries[] = "<a href=\"".class_link::getLinkPortalHref(
                    $objPage->getStrName(), "", "", "&preview=1", "", $this->getLanguageToWorkOn())."\" target=\"_blank\">".class_adminskin_helper::getAdminImage("icon_lens").$this->getLang("contentToolbar_preview"
                )."</a>";
            }
            if($this->getParam("pe") != 1)
                $strReturn .= $this->objToolkit->getContentToolbar($arrToolbarEntries, 0)."<br />";
        }
        class_module_languages_admin::enableLanguageSwitch();

        if($objForm == null)
            $objForm = $this->getPageForm($bitAlias, $objPage, $strMode);


        if($bitAlias)
            $strReturn .= $objForm->renderForm(class_link::getLinkAdminHref($this->getArrModule("modul"), "saveAlias"));
        else
            $strReturn .= $objForm->renderForm(class_link::getLinkAdminHref($this->getArrModule("modul"), "savePage"));

        return $strReturn;
    }


    /**
     * @param $bitAlias
     * @param class_module_pages_page $objPage
     * @param $strMode
     *
     * @return class_admin_formgenerator
     */
    private function getPageForm($bitAlias, class_module_pages_page $objPage, $strMode) {

        //Load all the Templates available
        $arrTemplates = class_resourceloader::getInstance()->getTemplatesInFolder("/module_pages");

        $arrTemplatesDD = array();
        if(count($arrTemplates) > 0)
            foreach($arrTemplates as $strTemplate)
                $arrTemplatesDD[$strTemplate] = $strTemplate;

        //remove template of master-page when editing a regular page
        $objMasterPage = class_module_pages_page::getPageByName("master");
        if($objMasterPage != null && ($objPage->getSystemid() == "" || ($objMasterPage->getSystemid() != $objPage->getSystemid()))) {
            unset($arrTemplatesDD[$objMasterPage->getStrTemplate()]);
        }

        $strPagesBrowser = class_link::getLinkAdminDialog(
            "pages",
            "pagesFolderBrowser",
            "&form_element=page_folder_name&pages=1&elements=false&folder=1&pagealiases=1",
            $this->getLang("commons_open_browser"),
            $this->getLang("commons_open_browser"),
            "icon_externalBrowser",
            $this->getLang("commons_open_browser")
        );


        $objForm = new class_admin_formgenerator("page", $objPage);
        if($bitAlias)
            $objForm->addField(new class_formentry_hidden("page", "name"))->setStrValue(generateSystemid())->setStrLabel($this->getLang("name"));
        else
            $objForm->addDynamicField("strName")->setStrLabel($this->getLang("name"));

        $objForm->addDynamicField("browsername")->setStrLabel($this->getLang("browsername"));

        if(!$bitAlias) {
            $objForm->addDynamicField("strSeostring")->setStrLabel($this->getLang("seostring"));
            $objForm->addDynamicField("strDescription")->setStrLabel($this->getLang("commons_description"));
            $objForm->addDynamicField("strKeywords")->setStrLabel($this->getLang("keywords"));
        }

        $strParentId = $objPage->getPrevId();
        if(!validateSystemid($strParentId) && $strMode == "new")
            $strParentId = $this->getSystemid();

        $strFolderId = $this->getParam("page_folder_name_id");
        $strFolderName = $this->getParam("page_folder_name");
        if(!validateSystemid($strFolderId) && validateSystemid($strParentId)) {
            $objParent = class_objectfactory::getInstance()->getObject($strParentId);
            $strFolderId = $objParent->getSystemid();
            if($objParent->getSystemid() != $this->getObjModule()->getSystemid())
                $strFolderName = $objParent->getStrDisplayName();
        }
        $objForm->addField(new class_formentry_text("page", "folder_name"))->setStrValue($strFolderName)->setBitReadonly(true)->setStrOpener($strPagesBrowser)->setStrLabel($this->getLang("page_folder_name"));
        $objForm->addField(new class_formentry_hidden("page", "folder_name_id"))->setStrValue($strFolderId);

        if(!$bitAlias) {

            /** @var $objField class_formentry_base */
            $objField = $objForm->addDynamicField("strTemplate")->setArrKeyValues($arrTemplatesDD)->setStrLabel($this->getLang("template"));
            if($strMode == "edit" && $objPage->getStrTemplate() == "")
                $objField->setStrHint($this->getLang("templateNotSelectedBefore"));

            $bitReadonly = false;
            if(class_module_system_setting::getConfigValue("_pages_templatechange_") == "false") {
                if($this->getAction() == "newPage" || $this->getParam("mode") == "new")
                    $bitReadonly = false;
                else if($objPage->getNumberOfElementsOnPage() != 0)
                    $bitReadonly = true;
            }
            $objField->setBitReadonly($bitReadonly);

            if($strMode == "new" && $this->getParam("page_template") == "")
                $objField->setStrValue(class_module_system_setting::getConfigValue("_pages_defaulttemplate_"));

        }
        else {
            $objForm->addDynamicField("strAlias")->setStrHint($this->getLang("page_alias_hint"))->setBitMandatory(true)->setStrLabel($this->getLang("page_alias"));

            $objForm->addDynamicField("strTarget")->setStrLabel($this->getLang("page_target"));
        }

        $objForm->addField(new class_formentry_hidden("", "mode"))->setStrValue($strMode);
        return $objForm;
    }


    /**
     * @return String
     */
    protected function actionSaveAlias() {
        return $this->actionSavePage(true);
    }

    /**
     * Saves a submitted page in the database (new Page!)
     *
     * @param bool $bitAlias
     *
     * @throws class_exception
     * @return String, "" if successful
     * @permissions edit
     */
    protected function actionSavePage($bitAlias = false) {

        $objPage = new class_module_pages_page();
        if($this->getParam("mode") == "edit")
            $objPage = new class_module_pages_page($this->getSystemid());

        $objForm = $this->getPageForm($bitAlias, $objPage, $this->getParam("mode"));


        if(!$objForm->validateForm())
            return $this->actionNewPage($this->getParam("mode"), $bitAlias, $objForm);

        $objForm->updateSourceObject();

        if($bitAlias)
            $objPage->setIntType(class_module_pages_page::$INT_TYPE_ALIAS);

        if(!$objPage->updateObjectToDb($this->getParam("page_folder_name_id")))
            throw new class_exception("Error saving new page to db", class_exception::$level_ERROR);

        if($this->getParam("pe") != "")
            $this->adminReload(class_link::getLinkAdminHref($this->getArrModule("modul"), "list", "&peClose=1&blockAction=1&peRefreshPage=".urlencode(class_link::getLinkPortalHref($objPage->getStrName()))));
        else
            $this->adminReload(class_link::getLinkAdminHref($this->getArrModule("modul"), "list", "systemid=".$objPage->getPrevId()));

        return "";
    }


    /**
     * @return string
     */
    protected function actionChangeAlias() {
        return $this->actionNewPage("edit", true);
    }

    /**
     * Delete a page and all associated elements
     *
     * @throws class_exception
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

                $this->adminReload(class_link::getLinkAdminHref($this->getArrModule("modul"), "list", "systemid=".$strPrevid));
            }
            else {
                //Print a message-box
                $strReturn .= $this->objToolkit->warningBox($this->getLang("ds_seite_gesperrt"));
            }

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
     *
     * @return string
     * @permissions right2
     * @autoTestable
     */
    protected function actionNewFolder($strMode = "new", class_admin_formgenerator $objForm = null) {
        $strReturn = "";
        $this->setArrModuleEntry("template", "/folderview.tpl");

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

        return $strReturn.$objForm->renderForm(class_link::getLinkAdminHref($this->getArrModule("modul"), "folderSave"));
    }

    /**
     * @param class_module_pages_folder $objFolder
     *
     * @return class_admin_formgenerator
     */
    private function getFolderForm(class_module_pages_folder $objFolder) {
        $objForm = new class_admin_formgenerator("folder", $objFolder);
        $objForm->generateFieldsFromObject();
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
        $this->adminReload(class_link::getLinkAdminHref($this->getArrModule("modul"), "list", "&blockAction=1&peClose=1"));

        return "";
    }

    /**
     * Deletes a folder from Database. All subpages & subfolders turn up to top-level
     *
     * @throws class_exception
     * @return string, "" in case of success
     */
    protected function actionDeleteFolderFinal() {
        $strReturn = "";
        $objFolder = new class_module_pages_folder($this->getSystemid());
        if($objFolder->rightDelete($this->getSystemid())) {
            $strPrevID = $objFolder->getPrevId();
            if($objFolder->deleteObject())
                $this->adminReload(class_link::getLinkAdminHref($this->getArrModule("modul"), "list", "&systemid=".$strPrevID));
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

            /** @var $objInstance class_module_pages_folder|class_module_pages_page */
            $objInstance = class_objectfactory::getInstance()->getObject($strOneFolderID);

            if($objInstance instanceof class_module_pages_folder) {
                $arrPathLinks[] = class_link::getLinkAdmin("pages", "list", "&systemid=".$strOneFolderID."&unlockid=".$this->getSystemid(), $objInstance->getStrName());
            }
            if($objInstance instanceof class_module_pages_page) {
                $arrPathLinks[] = class_link::getLinkAdmin("pages", "list", "&systemid=".$strOneFolderID."&unlockid=".$this->getSystemid(), $objInstance->getStrBrowsername());
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
     *
     * @return string
     * @permissions view
     */
    private function generateTreeView($strSideContent) {
        $strReturn = "";
        //generate the array of ids to expand initially
        $arrNodes = array_merge(array($this->getObjModule()->getSystemid()), $this->getPathArray($this->getSystemid()));
        $strReturn .= $this->objToolkit->getTreeview(class_link::getLinkAdminXml("pages", "getChildNodes"), "", $arrNodes, $strSideContent);

        //ticket #931: no hierarchical drag n drop for folders
        $strJS = <<<JS
            $(function() {
                $("table.admintable i.fa-folder-o").closest("tr").find("td.treedrag i").remove();
                $("table.admintable i.fa-folder-o").closest("tr").find("td.treedrag").css("cursor", "auto");
                $("table.admintable i.fa-folder-o").closest("tr").find("td.treedrag").removeClass("jstree-draggable");
            });
JS;

        $strJS = "<script type='text/javascript'>".$strJS."</script>";
        return $strReturn.$strJS;
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

        $objArraySectionIterator = new class_array_section_iterator(class_module_pages_element::getObjectCount());
        $objArraySectionIterator->setPageNumber((int)($this->getParam("pv") != "" ? $this->getParam("pv") : 1));
        $objArraySectionIterator->setArraySection(class_module_pages_element::getObjectList("", $objArraySectionIterator->calculateStartPos(), $objArraySectionIterator->calculateEndPos()));
        $strReturn .= $this->renderList($objArraySectionIterator, false, class_module_pages_admin::STR_LIST_ELEMENTS);

        return $strReturn;
    }


    /**
     * @return string
     */
    protected function actionEditElement() {
        return $this->actionNewElement("edit");
    }

    /**
     * Returns the form to edit / create an element
     *
     * @param string $strMode new || edit
     * @param class_admin_formgenerator $objForm
     *
     * @return string
     * @autoTestable
     * @permissions right1

     */
    protected function actionNewElement($strMode = "new", class_admin_formgenerator $objForm = null) {

        if($strMode == "new")
            $objElement = new class_module_pages_element();
        else
            $objElement = new class_module_pages_element($this->getSystemid());

        if($objForm == null)
            $objForm = $this->getElementForm($objElement);
        $objForm->addField(new class_formentry_hidden("", "mode"))->setStrValue($strMode);

        return $objForm->renderForm(class_link::getLinkAdminHref($this->getArrModule("modul"), "saveElement"));
    }

    /**
     * Generates a simple form to edit and create elements' basic data.
     *
     * @param class_module_pages_element $objElement
     *
     * @return class_admin_formgenerator
     */
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
        //redefine for proper lang-rendering
        $objElement->setArrModuleEntry("modul", "pages");
        $objForm->generateFieldsFromObject();

        $objForm->getField("cachetime")->setStrHint($this->getLang("element_cachetime_hint"));
        $objForm->getField("classadmin")->setArrKeyValues($arrClassesAdmin);
        $objForm->getField("classportal")->setArrKeyValues($arrClassesPortal);

        //check if the config-vals may be overriden

        /** @var $objAdminInstance class_element_admin */
        if($objElement->getSystemid() != "") {
            $objAdminInstance = $objElement->getAdminElementInstance();
            if($objAdminInstance->getConfigVal1Name() != "") {
                $objForm->addDynamicField("strConfigval1")->setStrLabel($objAdminInstance->getConfigVal1Name());
            }

            if($objAdminInstance->getConfigVal2Name() != "") {
                $objForm->addDynamicField("strConfigval2")->setStrLabel($objAdminInstance->getConfigVal2Name());
            }

            if($objAdminInstance->getConfigVal3Name() != "") {
                $objForm->addDynamicField("strConfigval3")->setStrLabel($objAdminInstance->getConfigVal3Name());
            }
        }

        return $objForm;
    }

    /**
     * Saves a passed element
     *
     * @throws class_exception
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
        $this->adminReload(class_link::getLinkAdminHref($this->getArrModule("modul"), "listElements"));
        return "";
    }

    /**
     * Deletes an element from db / displays the warning-box
     *
     * @throws class_exception
     * @return string, "" in case of success
     * @permissions right1
     */
    protected function actionDeleteElement() {
        $strReturn = "";
        $objElement = new class_module_pages_element($this->getParam("elementid"));
        if(!$objElement->deleteObject())
            throw new class_exception($this->getLang("element_loeschen_fehler"), class_exception::$level_ERROR);

        $this->flushCompletePagesCache();
        $this->adminReload(class_link::getLinkAdminHref($this->getArrModule("modul"), "listElements"));

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
        $strReturn .= $this->objToolkit->warningBox($this->getLang("quickhelp_update_placeholder"));
        $strReturn .= $this->objToolkit->getTextRow($this->getLang("plUpdateHelp"));
        $strReturn .= $this->objToolkit->divider();

        if($this->getParam("plToUpdate") == "") {
            $strReturn .= $this->objToolkit->formHeader(class_link::getLinkAdminHref($this->getArrModule("modul"), "updatePlaceholder"));
            //Load the available templates
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
            if(class_module_pages_pageelement::updatePlaceholders($this->getParam("template"), $this->getParam("plToUpdate"), $this->getParam("plNew")))
                $strReturn .= $this->objToolkit->getTextRow($this->getLang("plUpdateTrue"));
            else
                $strReturn .= $this->objToolkit->getTextRow($this->getLang("plUpdateFalse"));
        }

        return $strReturn;
    }


    /**
     * Returns a list of folders in the pages-database
     *
     * @return String
     * @permissions view
     * @autoTestable
     */
    protected function actionPagesFolderBrowser() {
        $strReturn = "";
        $intCounter = 1;

        $this->setArrModuleEntry("template", "/folderview.tpl");

        if($this->getParam("CKEditorFuncNum") != "") {
            $strReturn .= "<script type=\"text/javascript\">window.opener.KAJONA.admin.folderview.selectCallbackCKEditorFuncNum = ".(int)$this->getParam("CKEditorFuncNum").";</script>";
        }

        //param init
        $bitPages = ($this->getParam("pages") != "" ? true : false);
        $bitPageAliases = ($this->getParam("pagealiases") != "" ? true : false);
        $bitPageelements = ($this->getParam("elements") == "false" ? false : true);
        $bitFolder = ($this->getParam("folder") != "" ? true : false);
        $strSystemid = ($this->getSystemid() != "" ? $this->getSystemid() : class_module_system_module::getModuleByName("pages")->getSystemid());
        $strElement = ($this->getParam("form_element") != "" ? $this->getParam("form_element") : "ordner_name");
        $strPageid = ($this->getParam("pageid") != "" ? $this->getParam("pageid") : "0");

        $strLinkAddon = "".($bitPages ? "&pages=1" : "").($bitFolder ? "&folder=1" : "").($this->getParam("bit_link") != "" ? "&bit_link=1" : "")
            .(!$bitPageelements ? "&elements=false" : "").($bitPageAliases ? "&pagealiases=1" : "");


        $arrFolder = class_module_pages_folder::getFolderList($strSystemid);
        $objFolder = new class_module_pages_folder($strSystemid);
        $strLevelUp = "";

        if(validateSystemid($strSystemid) && $strSystemid != $this->getObjModule()->getSystemid())
            $strLevelUp = $objFolder->getPrevId();

        //but: when browsing pages the current level should be kept
        iF($strPageid != "0")
            $strLevelUp = $strSystemid;


        $strReturn .= $this->objToolkit->formHeader("");
        $strAction = $this->objToolkit->listButton(
            "<a href=\"#\" title=\"".$this->getLang("select_page")."\" rel=\"tooltip\" onclick=\"KAJONA.admin.folderview.selectCallback([['".$strElement."', '"._indexpath_."?page='+$('#quickselect').val()]]);\">".class_adminskin_helper::getAdminImage("icon_accept")."</a>"
        );
        $strReturn .= $this->objToolkit->formInputPageSelector("quickselect", $this->getLang("folderview_quickselect"), "", "", false, false, $strAction);
        $strReturn .= $this->objToolkit->formClose(false);


        $strReturn .= $this->objToolkit->listHeader();
        //Folder to jump one level up
        if(!$bitPages || $strLevelUp != "" || $bitFolder) {
            $strAction = $this->objToolkit->listButton(
                ($strSystemid != "0" && $strLevelUp != "") || $strPageid != "0" ? class_link::getLinkAdmin($this->getArrModule("modul"), "pagesFolderBrowser", "&systemid=".$strLevelUp.$strLinkAddon."&form_element=".$strElement.($this->getParam("bit_link") != "" ? "&bit_link=1" : ""), $this->getLang("commons_one_level_up"), $this->getLang("commons_one_level_up"), "icon_folderActionLevelup") : " "
            );
            if($strSystemid == $this->getObjModule()->getSystemid() && (!$bitPages || $bitFolder))
                $strAction .= $this->objToolkit->listButton(
                    "<a href=\"#\" title=\"".$this->getLang("commons_accept")."\" rel=\"tooltip\" onclick=\"KAJONA.admin.folderview.selectCallback([['".$strElement."_id', '".$this->getObjModule()->getSystemid()."'], ['".$strElement."', '']]);\">".class_adminskin_helper::getAdminImage("icon_accept")
                );

            $strReturn .= $this->objToolkit->genericAdminList(generateSystemid(), "..", getImageAdmin("icon_folderOpen"), $strAction, $intCounter++);
        }

        if(count($arrFolder) > 0 && $strPageid == "0") {
            foreach($arrFolder as $objSingleFolder) {
                if($bitPages && !$bitFolder) {
                    $strAction = $this->objToolkit->listButton(
                        class_link::getLinkAdmin(
                            $this->getArrModule("modul"),
                            "pagesFolderBrowser",
                            "&systemid=".$objSingleFolder->getSystemid()."&form_element=".$strElement.$strLinkAddon,
                            $this->getLang("pages_ordner_oeffnen"),
                            $this->getLang("pages_ordner_oeffnen"),
                            "icon_folderActionOpen"
                        )
                    );
                    $strReturn .= $this->objToolkit->simpleAdminList($objSingleFolder, $strAction, $intCounter++);
                }
                else {
                    $strAction = $this->objToolkit->listButton(
                        class_link::getLinkAdmin(
                            $this->getArrModule("modul"),
                            "pagesFolderBrowser",
                            "&systemid=".$objSingleFolder->getSystemid()."&form_element=".$strElement.$strLinkAddon,
                            $this->getLang("pages_ordner_oeffnen"),
                            $this->getLang("pages_ordner_oeffnen"),
                            "icon_folderActionOpen"
                        )
                    );
                    $strAction .= $this->objToolkit->listButton(
                        "<a href=\"#\" title=\"".$this->getLang("commons_accept")."\" rel=\"tooltip\" onclick=\"KAJONA.admin.folderview.selectCallback([['".$strElement."_id', '".$objSingleFolder->getSystemid()."'], ['".$strElement."', '".$objSingleFolder->getStrName()."']]); \">".class_adminskin_helper::getAdminImage("icon_accept")
                    );
                    $strReturn .= $this->objToolkit->simpleAdminList($objSingleFolder, $strAction, $intCounter++);
                }
            }

        }
        $strReturn .= $this->objToolkit->listFooter();

        //Pages could be sent too
        if($bitPages && $strPageid == "0") {
            $arrPages = class_module_pages_folder::getPagesInFolder($strSystemid);
            if(count($arrPages) > 0) {
                $strReturn .= $this->objToolkit->listHeader();

                /** @var $objSinglePage class_module_pages_page */
                foreach($arrPages as $objSinglePage) {
                    $arrSinglePage = array();
                    //Should we generate a link ?
                    if($this->getParam("bit_link") != "")
                        $arrSinglePage["name2"] = class_link::getLinkPortalHref($objSinglePage->getStrName(), "", "", "", "", $this->getLanguageToWorkOn());
                    else
                        $arrSinglePage["name2"] = $objSinglePage->getStrName();


                    if($objSinglePage->getIntType() == class_module_pages_page::$INT_TYPE_ALIAS) {
                        if(count(class_module_pages_folder::getPagesInFolder($objSinglePage->getSystemid())) == 0)
                            $strAction = getImageAdmin("icon_treeBranchOpenDisabled");
                        else
                            $strAction = $this->objToolkit->listButton(
                                class_link::getLinkAdmin(
                                    $this->getArrModule("modul"),
                                    "pagesFolderBrowser",
                                    "&systemid=".$objSinglePage->getSystemid()."&form_element=".$strElement.$strLinkAddon,
                                    $this->getLang("page_sublist"),
                                    $this->getLang("page_sublist"),
                                    "icon_treeBranchOpen"
                                )
                            );
                        if($bitPageAliases)
                            $strAction .= $this->objToolkit->listButton(
                                "<a href=\"#\" title=\"".$this->getLang("select_page")."\" rel=\"tooltip\" onclick=\"KAJONA.admin.folderview.selectCallback([['".$strElement."_id', '".$objSinglePage->getSystemid()."'],['".$strElement."', '".$arrSinglePage["name2"]."']]);\">".class_adminskin_helper::getAdminImage("icon_accept")."</a>"
                            );

                        $strReturn .= $this->objToolkit->simpleAdminList($objSinglePage, $strAction, $intCounter++);
                    }
                    else {
                        if(count(class_module_pages_folder::getPagesInFolder($objSinglePage->getSystemid())) == 0)
                            $strAction = getImageAdmin("icon_treeBranchOpenDisabled");
                        else
                            $strAction = $this->objToolkit->listButton(
                                class_link::getLinkAdmin(
                                    $this->getArrModule("modul"),
                                    "pagesFolderBrowser",
                                    "&systemid=".$objSinglePage->getSystemid()."&form_element=".$strElement.$strLinkAddon,
                                    $this->getLang("page_sublist"),
                                    $this->getLang("page_sublist"),
                                    "icon_treeBranchOpen"
                                )
                            );
                        if($bitPageelements) {
                            $strAction .= $this->objToolkit->listButton(
                                class_link::getLinkAdmin(
                                    $this->getArrModule("modul"),
                                    "pagesFolderBrowser",
                                    "&systemid=".$strSystemid."&form_element=".$strElement."&pageid=".$objSinglePage->getSystemid().($this->getParam("bit_link") != "" ? "&bit_link=1" : "").($bitPages ? "&pages=1" : "").($bitPageAliases ? "&pagealiases=1" : ""),
                                    $this->getLang("seite_oeffnen"),
                                    $this->getLang("seite_oeffnen"),
                                    "icon_folderActionOpen"
                                )
                            );
                        }
                        $strAction .= $this->objToolkit->listButton(
                            "<a href=\"#\" title=\"".$this->getLang("select_page")."\" rel=\"tooltip\" onclick=\"KAJONA.admin.folderview.selectCallback([['".$strElement."_id', '".$objSinglePage->getSystemid()."'],['".$strElement."', '".$arrSinglePage["name2"]."']]);\">".class_adminskin_helper::getAdminImage("icon_accept")."</a>"
                        );
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
                        $arrSinglePage["name2"] = class_link::getLinkPortalHref($objPage->getStrName(), "", "", "", "", $this->getLanguageToWorkOn())."#".$objOnePageelement->getSystemid();
                    else
                        $arrSinglePage["name2"] = $objPage->getStrName()."#".$objOnePageelement->getSystemid();

                    $strAction = $this->objToolkit->listButton(
                        "<a href=\"#\" title=\"".$this->getLang("seite_uebernehmen")."\" rel=\"tooltip\" onclick=\"KAJONA.admin.folderview.selectCallback([['".$strElement."', '".$arrSinglePage["name2"]."']]);\">".class_adminskin_helper::getAdminImage("icon_accept")."</a>"
                    );
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
     *
     * @xml
     * @return string
     * @permissions view
     */
    protected function actionGetPagesByFilter() {
        $strFilter = $this->getParam("filter");
        $arrPages = class_module_pages_page::getAllPages(null, null, $strFilter);

        $arrReturn = array();
        foreach($arrPages as $objOnePage) {
            if($objOnePage->rightView()) {
                $arrReturn[] = $objOnePage->getStrName();
            }
        }
        class_response_object::getInstance()->setStrResponseType(class_http_responsetypes::STR_TYPE_JSON);
        return json_encode($arrReturn);
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
        $arrReturn = array();

        $arrPages = class_module_pages_folder::getPagesAndFolderList($this->getSystemid());
        if(count($arrPages) > 0) {
            foreach($arrPages as $objSingleEntry) {
                if($objSingleEntry->rightView()) {

                    /** @var class_module_pages_folder $objSingleEntry */
                    if($objSingleEntry instanceof class_module_pages_folder) {

                        $strLink = "";
                        if($objSingleEntry->rightEdit())
                            $strLink = class_link::getLinkAdminHref("pages", "list", "systemid=".$objSingleEntry->getSystemid(), false);

                        $arrReturn[] = array(
                            "data"  => array(
                                "title" => class_adminskin_helper::getAdminImage($objSingleEntry->getStrIcon())."&nbsp;".$objSingleEntry->getStrDisplayName()
                            ),
                            "state" => (count(class_module_pages_folder::getPagesAndFolderList($objSingleEntry->getSystemid())) == 0 ? "" : "closed"),
                            "attr"  => array(
                                "id"       => $objSingleEntry->getSystemid(),
                                "systemid" => $objSingleEntry->getSystemid(),
                                "link"     => $strLink,
                                "isleaf"   => (count(class_module_pages_folder::getPagesAndFolderList($objSingleEntry->getSystemid())) == 0 ? true : false)
                            )
                        );
                    }


                    /** @var class_module_pages_page $objSingleEntry */
                    if($objSingleEntry instanceof class_module_pages_page) {

                        $strTargetId = $objSingleEntry->getSystemid();
                        if($objSingleEntry->getIntType() == class_module_pages_page::$INT_TYPE_ALIAS && class_module_pages_page::getPageByName($objSingleEntry->getStrAlias()) != null)
                            $strTargetId = class_module_pages_page::getPageByName($objSingleEntry->getStrAlias())->getSystemid();

                        $strLink = "";
                        if($objSingleEntry->getIntType() == class_module_pages_page::$INT_TYPE_ALIAS && class_objectfactory::getInstance()->getObject($strTargetId)->rightEdit())
                            $strLink = class_link::getLinkAdminHref("pages_content", "list", "systemid=".$strTargetId, false);
                        else if($objSingleEntry->getIntType() == class_module_pages_page::$INT_TYPE_PAGE && $objSingleEntry->rightEdit())
                            $strLink = class_link::getLinkAdminHref("pages_content", "list", "systemid=".$objSingleEntry->getSystemid(), false);


                        $arrReturn[] = array(
                            "data"  => array(
                                "title" => class_adminskin_helper::getAdminImage($objSingleEntry->getStrIcon())."&nbsp;".$objSingleEntry->getStrDisplayName()
                            ),
                            "state" => (count(class_module_pages_folder::getPagesAndFolderList($objSingleEntry->getSystemid())) == 0 ? "" : "closed"),
                            "attr"  => array(
                                "id"       => $objSingleEntry->getSystemid(),
                                "systemid" => $objSingleEntry->getSystemid(),
                                "link"     => $strLink,
                                //"link"     => getLinkAdminHref("pages", "list", "systemid=".$objSingleEntry->getSystemid(), false),
                                "type"     => $objSingleEntry->getIntType(),
                                "isleaf"   => (count(class_module_pages_folder::getPagesAndFolderList($objSingleEntry->getSystemid())) == 0 ? true : false)
                            )
                        );
                    }

                }
            }

        }

        class_response_object::getInstance()->setStrResponseType(class_http_responsetypes::STR_TYPE_JSON);
        return json_encode($arrReturn);
    }

}

