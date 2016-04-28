<?php
/*"******************************************************************************************************
*   (c) 2004-2006 by MulchProductions, www.mulchprod.de                                                 *
*   (c) 2007-2016 by Kajona, www.kajona.de                                                              *
*       Published under the GNU LGPL v2.1, see /system/licence_lgpl.txt                                 *
*-------------------------------------------------------------------------------------------------------*
*   $Id$                                  *
********************************************************************************************************/

namespace Kajona\Pages\Admin;


use Kajona\Pages\System\PagesElement;
use Kajona\Pages\System\PagesFolder;
use Kajona\Pages\System\PagesJstreeNodeLoader;
use Kajona\Pages\System\PagesPage;
use Kajona\Pages\System\PagesPageelement;
use Kajona\System\Admin\AdminFormgenerator;
use Kajona\System\Admin\AdminInterface;
use Kajona\System\Admin\AdminSimple;
use Kajona\System\Admin\Formentries\FormentryBase;
use Kajona\System\Admin\Formentries\FormentryHidden;
use Kajona\System\Admin\Formentries\FormentryText;
use Kajona\System\Admin\LanguagesAdmin;
use Kajona\System\System\AdminskinHelper;
use Kajona\System\System\ArraySectionIterator;
use Kajona\System\System\Exception;
use Kajona\System\System\HttpResponsetypes;
use Kajona\System\System\Link;
use Kajona\System\System\Model;
use Kajona\System\System\Objectfactory;
use Kajona\System\System\Resourceloader;
use Kajona\System\System\ResponseObject;
use Kajona\System\System\SystemJSTreeBuilder;
use Kajona\System\System\SystemJSTreeConfig;
use Kajona\System\System\SystemModule;
use Kajona\System\System\SystemSetting;


/**
 * This class handles the admin-sided management of the pages
 * In this case, that are only the pages NOT yet the content
 *
 * @author sidler@mulchprod.de
 * @module pages
 * @moduleId _pages_modul_id_
 */
class PagesAdminController extends AdminSimple implements AdminInterface
{

    const STR_LIST_ALLPAGES = "STR_LIST_ALLPAGES";
    const STR_LIST_PAGES = "STR_LIST_PAGES";
    const STR_LIST_ELEMENTS = "STR_LIST_ELEMENTS";

    /**
     * @return array
     */
    public function getOutputModuleNavi()
    {
        $arrReturn = array();
        $arrReturn[] = array("view", Link::getLinkAdmin($this->getArrModule("modul"), "list", "", $this->getLang("commons_list"), "", "", true, "adminnavi"));
        $arrReturn[] = array("", "");
        $arrReturn[] = array("right1", Link::getLinkAdmin($this->getArrModule("modul"), "listElements", "", $this->getLang("modul_elemente"), "", "", true, "adminnavi"));
        $arrReturn[] = array("", "");
        $arrReturn[] = array("right3", Link::getLinkAdmin($this->getArrModule("modul"), "updatePlaceholder", "", $this->getLang("action_update_placeholder"), "", "", true, "adminnavi"));
        return $arrReturn;
    }

    /**
     * Renders the form to create a new entry
     *
     * @return string
     * @permissions edit
     */
    protected function actionNew()
    {
        //in nearly every case, a new page should be created
        $this->adminReload(Link::getLinkAdminHref($this->getArrModule("modul"), "newPage"));
        return "";
    }

    /**
     * Renders the form to edit an existing entry
     *
     * @return string
     * @permissions edit
     */
    protected function actionEdit()
    {
        /** @var $objEntry PagesPage */
        $objEntry = Objectfactory::getInstance()->getObject($this->getSystemid());
        if ($objEntry instanceof PagesPage) {
            if ($objEntry->getIntType() == PagesPage::$INT_TYPE_ALIAS) {
                $this->adminReload(Link::getLinkAdminHref($this->getArrModule("modul"), "editAlias", "&systemid=".$objEntry->getSystemid()));
            }
            else {
                $this->adminReload(Link::getLinkAdminHref($this->getArrModule("modul"), "editPage", "&systemid=".$objEntry->getSystemid()));
            }
        }
        elseif ($objEntry instanceof PagesFolder) {
            $this->adminReload(Link::getLinkAdminHref($this->getArrModule("modul"), "editFolder", "&systemid=".$objEntry->getSystemid()));
        }
        elseif ($objEntry instanceof PagesElement) {
            $this->adminReload(Link::getLinkAdminHref($this->getArrModule("modul"), "editElement", "&systemid=".$objEntry->getSystemid()));
        }
    }


    /**
     * @param string $strListIdentifier
     *
     * @return array
     */
    protected function getBatchActionHandlers($strListIdentifier)
    {
        if ($strListIdentifier == PagesAdminController::STR_LIST_PAGES || $strListIdentifier == PagesAdminController::STR_LIST_ALLPAGES) {
            return $this->getDefaultActionHandlers();
        }

        return array();
    }

    /**
     * Creates a list of sites in the current folder
     *
     * @return string
     * @autoTestable
     * @permissions view
     */
    protected function actionList()
    {

        LanguagesAdmin::enableLanguageSwitch();

        $bitPeMode = $this->getParam("pe") != "";

        //Collect the pages belonging to the current parent
        $objArraySectionIterator = new ArraySectionIterator(PagesFolder::getPagesAndFolderListCount($this->getSystemid()));
        $objArraySectionIterator->setPageNumber($this->getParam("pv"));
        $objArraySectionIterator->setArraySection(PagesFolder::getPagesAndFolderList($this->getSystemid(), false, $objArraySectionIterator->calculateStartPos(), $objArraySectionIterator->calculateEndPos()));
        $strPages = $this->renderList($objArraySectionIterator, true, PagesAdminController::STR_LIST_PAGES, true);

        if ($bitPeMode) {
            $strReturn = $strPages;
        }
        else {
            $strReturn = $this->generateTreeView($strPages);
        }

        return $strReturn;
    }

    /**
     * @param string $strListIdentifier
     *
     * @return string
     */
    protected function renderLevelUpAction($strListIdentifier)
    {
        if ($strListIdentifier == PagesAdminController::STR_LIST_PAGES) {
            if (validateSystemid($this->getSystemid()) && $this->getSystemid() != $this->getObjModule()->getSystemid()) {
                $objPrevFolder = new PagesFolder($this->getSystemid());
                return $this->objToolkit->listButton(
                    Link::getLinkAdmin(
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
     * @param Model $objListEntry
     * @param bool $bitDialog
     *
     * @return string
     */
    protected function renderEditAction(Model $objListEntry, $bitDialog = false)
    {
        if ($objListEntry instanceof PagesElement) {
            if ($objListEntry->rightEdit()) {
                return $this->objToolkit->listButton(
                    Link::getLinkAdmin(
                        "pages",
                        "editElement",
                        "&systemid=".$objListEntry->getSystemid(),
                        $this->getLang("element_bearbeiten"),
                        $this->getLang("element_bearbeiten"),
                        "icon_edit"
                    )
                );
            }
        }
        elseif ($objListEntry instanceof PagesFolder) {
            return parent::renderEditAction($objListEntry, true);
        }
        else {
            return parent::renderEditAction($objListEntry);
        }

        return "";
    }


    /**
     * @param \Kajona\System\System\ModelInterface|PagesPage $objListEntry
     *
     * @return string
     */
    protected function renderDeleteAction(\Kajona\System\System\ModelInterface $objListEntry)
    {

        if ($this->getParam("pe") != "") {
            return "";
        }

        $objLockmanager = $objListEntry->getLockManager();
        if (!$objLockmanager->isAccessibleForCurrentUser()) {
            return $this->objToolkit->listButton(AdminskinHelper::getAdminImage("icon_deleteLocked", $this->getLang("commons_locked")));
        }
        elseif ($objListEntry instanceof PagesPage && $objListEntry->rightDelete()) {

            return $this->objToolkit->listDeleteButton(
                $objListEntry->getStrDisplayName(), $this->getLang("seite_loeschen_frage"), Link::getLinkAdminHref($this->getArrModule("modul"), "deletePageFinal", "&systemid=".$objListEntry->getSystemid())
            );
        }
        elseif ($objListEntry instanceof PagesFolder && $objListEntry->rightDelete()) {
            return $this->objToolkit->listDeleteButton(
                $objListEntry->getStrDisplayName(), $this->getLang("pages_ordner_loeschen_frage"), Link::getLinkAdminHref($this->getArrModule("modul"), "deleteFolderFinal", "&systemid=".$objListEntry->getSystemid())
            );
        }
        elseif ($objListEntry instanceof PagesElement && $objListEntry->rightDelete()) {
            return $this->objToolkit->listDeleteButton(
                $objListEntry->getStrDisplayName(), $this->getLang("element_loeschen_frage"), Link::getLinkAdminHref($this->getArrModule("modul"), "deleteElement", "&elementid=".$objListEntry->getSystemid())
            );
        }
        else {
            return parent::renderDeleteAction($objListEntry);
        }
    }

    /**
     * @param Model $objListEntry
     * @param string $strAltActive tooltip text for the icon if record is active
     * @param string $strAltInactive tooltip text for the icon if record is inactive
     *
     * @return string
     */
    protected function renderStatusAction(Model $objListEntry, $strAltActive = "", $strAltInactive = "")
    {
        if ($objListEntry instanceof PagesElement) {
            return "";
        }
        else {
            return parent::renderStatusAction($objListEntry, $strAltActive, $strAltInactive);
        }
    }

    /**
     * @param Model|PagesPage $objListEntry
     *
     * @return array
     */
    protected function renderAdditionalActions(Model $objListEntry)
    {

        $bitPeMode = $this->getParam("pe") != "";

        if ($objListEntry instanceof PagesPage) {
            $arrReturn = array();
            if ($objListEntry->getIntType() == PagesPage::$INT_TYPE_ALIAS) {
                $objTargetPage = PagesPage::getPageByName($objListEntry->getStrAlias());
                if (!$bitPeMode && $objTargetPage != null && $objTargetPage->rightEdit()) {
                    $arrReturn[] = $this->objToolkit->listButton(
                        Link::getLinkAdmin("pages_content", "list", "&systemid=".$objTargetPage->getStrSystemid()."&pe=".$this->getParam("pe"), "", $this->getLang("seite_inhalte_alias"), "icon_page_alias")
                    );
                }

                $arrReturn[] = $this->objToolkit->listButton(
                    Link::getLinkAdmin($this->getArrModule("modul"), "list", "&systemid=".$objListEntry->getSystemid()."&pe=".$this->getParam("pe"), "", $this->getLang("page_sublist"), "icon_folderActionOpen")
                );
            }
            elseif ($objListEntry->rightView()) {

                if (!$bitPeMode && $objListEntry->rightEdit()) {
                    $arrReturn[] = $this->objToolkit->listButton(
                        Link::getLinkAdmin("pages_content", "list", "&systemid=".$objListEntry->getSystemid()."&pe=".$this->getParam("pe"), "", $this->getLang("seite_inhalte"), "icon_page")
                    );
                }

                $arrReturn[] = $this->objToolkit->listButton(
                    Link::getLinkAdmin($this->getArrModule("modul"), "list", "&systemid=".$objListEntry->getSystemid()."&pe=".$this->getParam("pe"), "", $this->getLang("page_sublist"), "icon_folderActionOpen")
                );
            }

            return $arrReturn;
        }
        elseif ($objListEntry instanceof PagesFolder) {
            $arrReturn[] = $this->objToolkit->listButton(
                Link::getLinkAdmin("pages", "list", "&systemid=".$objListEntry->getSystemid()."&pe=".$this->getParam("pe"), $this->getLang("pages_ordner_oeffnen"), $this->getLang("pages_ordner_oeffnen"), "icon_folderActionOpen")
            );
            return $arrReturn;
        }
        else {
            return parent::renderAdditionalActions($objListEntry);
        }
    }

    /**
     * @param Model $objListEntry
     *
     * @return string
     */
    protected function renderCopyAction(Model $objListEntry)
    {

        $bitPeMode = $this->getParam("pe") != "";
        if ($bitPeMode) {
            return "";
        }

        if ($objListEntry instanceof PagesElement) {
            return "";
        }

        if ($objListEntry instanceof PagesFolder) {
            return "";
        }

        if ($objListEntry instanceof PagesPage && $objListEntry->getIntType() == PagesPage::$INT_TYPE_ALIAS) {
            return "";
        }

        return parent::renderCopyAction($objListEntry);
    }


    /**
     * @param string $strListIdentifier
     * @param bool $bitDialog
     *
     * @return array|string
     */
    protected function getNewEntryAction($strListIdentifier, $bitDialog = false)
    {

        if ($this->getParam("pe") != "") {
            return "";
        }

        $arrReturn = array();

        $objCurInstance = null;
        if (validateSystemid($this->getSystemid())) {
            $objCurInstance = Objectfactory::getInstance()->getObject($this->getSystemid());
        }
        else {
            $objCurInstance = $this->getObjModule();
        }

        if ($strListIdentifier != PagesAdminController::STR_LIST_ELEMENTS && $objCurInstance->rightEdit()) {
            $arrReturn[] = $this->objToolkit->listButton(
                Link::getLinkAdmin($this->getArrModule("modul"), "newPage", "&systemid=".$this->getSystemid(), $this->getLang("action_new_page"), $this->getLang("action_new_page"), "icon_new")
            );
            $arrReturn[] = $this->objToolkit->listButton(
                Link::getLinkAdmin($this->getArrModule("modul"), "newAlias", "&systemid=".$this->getSystemid(), $this->getLang("action_new_alias"), $this->getLang("action_new_alias"), "icon_new_alias")
            );

        }
        if ($strListIdentifier != PagesAdminController::STR_LIST_ELEMENTS && $objCurInstance->rightRight2()) {
            if ((!validateSystemid($this->getSystemid()) || $this->getSystemid() == $this->getObjModule()->getSystemid())) {
                $arrReturn[] = $this->objToolkit->listButton(
                    Link::getLinkAdminDialog($this->getArrModule("modul"), "newFolder", "&systemid=".$this->getSystemid(), $this->getLang("commons_create_folder"), $this->getLang("commons_create_folder"), "icon_new")
                );
            }

        }
        if ($strListIdentifier == PagesAdminController::STR_LIST_ELEMENTS && $this->getObjModule()->rightRight1()) {
            $arrReturn[] = $this->objToolkit->listButton(
                Link::getLinkAdmin($this->getArrModule("modul"), "newElement", "", $this->getLang("action_new_element"), $this->getLang("action_new_element"), "icon_new")
            );
        }

        return $arrReturn;
    }

    /**
     * @return string
     */
    protected function actionEditPage()
    {
        return $this->actionNewPage("edit");
    }

    /**
     * @return string
     * @autoTestable
     */
    protected function actionNewAlias()
    {
        return $this->actionNewPage("new", true);
    }

    /**
     * @return string
     */
    protected function actionEditAlias()
    {
        return $this->actionNewPage("edit", true);
    }

    /**
     * Shows the form to create a new Site
     *
     * @param string $strMode
     * @param bool $bitAlias
     * @param AdminFormgenerator|null $objForm
     *
     * @return string The form
     * @autoTestable
     * @permissions edit
     */
    protected function actionNewPage($strMode = "new", $bitAlias = false, AdminFormgenerator $objForm = null)
    {
        $strReturn = "";

        $objPage = new PagesPage();
        if ($strMode == "edit") {
            $objPage = new PagesPage($this->getSystemid());
            if (!$objPage->rightEdit($this->getSystemid())) {
                return $this->getLang("commons_error_permissions");
            }
        }
        elseif ($strMode == "new") {
            $objPage->setSystemid($this->getSystemid());
        }

        $arrToolbarEntries = array();
        if (!$bitAlias) {
            if ($strMode == "edit") {
                $arrToolbarEntries[] = "<a href=\"".Link::getLinkAdminHref("pages", "editPage", "&systemid=".$this->getSystemid())."\">".AdminskinHelper::getAdminImage("icon_edit").$this->getLang("contentToolbar_pageproperties")."</a>";
                $arrToolbarEntries[] = "<a href=\"".Link::getLinkAdminHref("pages_content", "list", "&systemid=".$this->getSystemid())."\" >".AdminskinHelper::getAdminImage("icon_page").$this->getLang("contentToolbar_content")."</a>";
                $arrToolbarEntries[] = "<a href=\"".Link::getLinkPortalHref(
                        $objPage->getStrName(), "", "", "&preview=1", "", $this->getLanguageToWorkOn())."\" target=\"_blank\">".AdminskinHelper::getAdminImage("icon_lens").$this->getLang("contentToolbar_preview"
                    )."</a>";
            }
            if ($this->getParam("pe") != 1) {
                $strReturn .= $this->objToolkit->getContentToolbar($arrToolbarEntries, 0)."<br />";
            }
        }
        LanguagesAdmin::enableLanguageSwitch();

        if ($objForm == null) {
            $objForm = $this->getPageForm($bitAlias, $objPage, $strMode);
        }


        if ($bitAlias) {
            $strReturn .= $objForm->renderForm(Link::getLinkAdminHref($this->getArrModule("modul"), "saveAlias"));
        }
        else {
            $strReturn .= $objForm->renderForm(Link::getLinkAdminHref($this->getArrModule("modul"), "savePage"));
        }

        return $strReturn;
    }


    /**
     * @param $bitAlias
     * @param PagesPage $objPage
     * @param $strMode
     *
     * @return AdminFormgenerator
     */
    private function getPageForm($bitAlias, PagesPage $objPage, $strMode)
    {

        //Load all the Templates available
        $arrTemplates = Resourceloader::getInstance()->getTemplatesInFolder("/module_pages");

        $arrTemplatesDD = array();
        if (count($arrTemplates) > 0) {
            foreach ($arrTemplates as $strTemplate) {
                $arrTemplatesDD[$strTemplate] = $strTemplate;
            }
        }

        //remove template of master-page when editing a regular page
        $objMasterPage = PagesPage::getPageByName("master");
        if ($objMasterPage != null && ($objPage->getSystemid() == "" || ($objMasterPage->getSystemid() != $objPage->getSystemid()))) {
            unset($arrTemplatesDD[$objMasterPage->getStrTemplate()]);
        }

        $strPagesBrowser = Link::getLinkAdminDialog(
            "pages",
            "pagesFolderBrowser",
            "&form_element=page_folder_name&pages=1&elements=false&folder=1&pagealiases=1",
            $this->getLang("commons_open_browser"),
            $this->getLang("commons_open_browser"),
            "icon_externalBrowser",
            $this->getLang("commons_open_browser")
        );


        $objForm = new AdminFormgenerator("page", $objPage);
        if ($bitAlias) {
            $objForm->addField(new FormentryHidden("page", "name"))->setStrValue(generateSystemid())->setStrLabel($this->getLang("name"));
        }
        else {
            $objForm->addDynamicField("strName")->setStrLabel($this->getLang("name"));
        }

        $objForm->addDynamicField("browsername")->setStrLabel($this->getLang("browsername"));

        if (!$bitAlias) {
            $objForm->addDynamicField("strSeostring")->setStrLabel($this->getLang("seostring"));
            $objForm->addDynamicField("strDescription")->setStrLabel($this->getLang("commons_description"));
            $objForm->addDynamicField("strKeywords")->setStrLabel($this->getLang("keywords"));
        }

        $strParentId = $objPage->getPrevId();
        if (!validateSystemid($strParentId) && $strMode == "new") {
            $strParentId = $this->getSystemid();
        }

        $strFolderId = $this->getParam("page_folder_name_id");
        $strFolderName = $this->getParam("page_folder_name");
        if (!validateSystemid($strFolderId) && validateSystemid($strParentId)) {
            $objParent = Objectfactory::getInstance()->getObject($strParentId);
            $strFolderId = $objParent->getSystemid();
            if ($objParent->getSystemid() != $this->getObjModule()->getSystemid()) {
                $strFolderName = $objParent->getStrDisplayName();
            }
        }
        $objForm->addField(new FormentryText("page", "folder_name"))->setStrValue($strFolderName)->setBitReadonly(true)->setStrOpener($strPagesBrowser)->setStrLabel($this->getLang("page_folder_name"));
        $objForm->addField(new FormentryHidden("page", "folder_name_id"))->setStrValue($strFolderId);

        if (!$bitAlias) {

            /** @var $objField FormentryBase */
            $objField = $objForm->addDynamicField("strTemplate")->setArrKeyValues($arrTemplatesDD)->setStrLabel($this->getLang("template"));
            if ($strMode == "edit" && $objPage->getStrTemplate() == "") {
                $objField->setStrHint($this->getLang("templateNotSelectedBefore"));
            }

            $bitReadonly = false;
            if (SystemSetting::getConfigValue("_pages_templatechange_") == "false") {
                if ($this->getAction() == "newPage" || $this->getParam("mode") == "new") {
                    $bitReadonly = false;
                }
                elseif ($objPage->getNumberOfElementsOnPage() != 0) {
                    $bitReadonly = true;
                }
            }
            $objField->setBitReadonly($bitReadonly);

            if ($strMode == "new" && $this->getParam("page_template") == "") {
                $objField->setStrValue(SystemSetting::getConfigValue("_pages_defaulttemplate_"));
            }

        }
        else {
            $objForm->addDynamicField("strAlias")->setStrHint($this->getLang("page_alias_hint"))->setBitMandatory(true)->setStrLabel($this->getLang("page_alias"));

            $objForm->addDynamicField("strTarget")->setStrLabel($this->getLang("page_target"));
        }

        $objForm->addField(new FormentryHidden("", "mode"))->setStrValue($strMode);
        return $objForm;
    }


    /**
     * @return String
     */
    protected function actionSaveAlias()
    {
        return $this->actionSavePage(true);
    }

    /**
     * Saves a submitted page in the database (new Page!)
     *
     * @param bool $bitAlias
     *
     * @throws Exception
     * @return String, "" if successful
     * @permissions edit
     */
    protected function actionSavePage($bitAlias = false)
    {

        $objPage = new PagesPage();
        if ($this->getParam("mode") == "edit") {
            $objPage = new PagesPage($this->getSystemid());
        }

        $objForm = $this->getPageForm($bitAlias, $objPage, $this->getParam("mode"));


        if (!$objForm->validateForm()) {
            return $this->actionNewPage($this->getParam("mode"), $bitAlias, $objForm);
        }

        $objForm->updateSourceObject();

        if ($bitAlias) {
            $objPage->setIntType(PagesPage::$INT_TYPE_ALIAS);
        }

        if (!$objPage->updateObjectToDb($this->getParam("page_folder_name_id"))) {
            throw new Exception("Error saving new page to db", Exception::$level_ERROR);
        }

        if ($this->getParam("pe") != "") {
            $this->adminReload(Link::getLinkAdminHref($this->getArrModule("modul"), "list", "&peClose=1&blockAction=1&peRefreshPage=".urlencode(Link::getLinkPortalHref($objPage->getStrName()))));
        }
        else {
            $this->adminReload(Link::getLinkAdminHref($this->getArrModule("modul"), "list", "systemid=".$objPage->getPrevId()));
        }

        return "";
    }


    /**
     * @return string
     */
    protected function actionChangeAlias()
    {
        return $this->actionNewPage("edit", true);
    }

    /**
     * Delete a page and all associated elements
     *
     * @throws Exception
     * @return string, "" in case of success
     */
    protected function actionDeletePageFinal()
    {
        $strReturn = "";
        $objPage = new PagesPage($this->getSystemid());
        if ($objPage->rightDelete()) {
            //Are there any locked records on this page?
            if ($objPage->getNumberOfLockedElementsOnPage() == 0) {
                $strPrevid = $objPage->getPrevId();
                if (!$objPage->deleteObject()) {
                    throw new Exception("Error deleting page from db", Exception::$level_ERROR);
                }

                $this->adminReload(Link::getLinkAdminHref($this->getArrModule("modul"), "list", "systemid=".$strPrevid));
            }
            else {
                //Print a message-box
                $strReturn .= $this->objToolkit->warningBox($this->getLang("ds_seite_gesperrt"));
            }

        }
        else {
            $strReturn = $this->getLang("commons_error_permissions");
        }

        return $strReturn;
    }


    /**
     * Returns a form to create a new folder
     *
     * @param string $strMode
     * @param AdminFormgenerator|null $objForm
     *
     * @return string
     * @permissions right2
     * @autoTestable
     */
    protected function actionNewFolder($strMode = "new", AdminFormgenerator $objForm = null)
    {
        $strReturn = "";
        $this->setArrModuleEntry("template", "/folderview.tpl");

        if ($strMode == "new") {
            $objFolder = new PagesFolder();
        }
        else {
            $objFolder = new PagesFolder($this->getSystemid());
            if (!$objFolder->rightEdit()) {
                return $this->getLang("commons_error_permissions");
            }
        }

        if ($objForm == null) {
            $objForm = $this->getFolderForm($objFolder);
        }
        $objForm->addField(new FormentryHidden("", "mode"))->setStrValue($strMode);

        return $strReturn.$objForm->renderForm(Link::getLinkAdminHref($this->getArrModule("modul"), "folderSave"));
    }

    /**
     * @param PagesFolder $objFolder
     *
     * @return AdminFormgenerator
     */
    private function getFolderForm(PagesFolder $objFolder)
    {
        $objForm = new AdminFormgenerator("folder", $objFolder);
        $objForm->generateFieldsFromObject();
        return $objForm;
    }

    /**
     * Creates a form to edit a folder (rename it)
     *
     * @return string
     * @permissions right2
     */
    protected function actionEditFolder()
    {
        return $this->actionNewFolder("edit");
    }

    /**
     * Saves the posted Folder to database
     *
     * @return String, "" in case of success
     * @permissions right2
     */
    protected function actionFolderSave()
    {

        if ($this->getParam("mode") == "new") {
            $objFolder = new PagesFolder();
        }
        else {
            $objFolder = new PagesFolder($this->getSystemid());
            if (!$objFolder->rightEdit()) {
                return $this->getLang("commons_error_permissions");
            }
        }

        $objForm = $this->getFolderForm($objFolder);
        if (!$objForm->validateForm()) {
            return $this->actionNewFolder($this->getParam("mode"), $objForm);
        }

        $objForm->updateSourceObject();

        $objFolder->updateObjectToDb();
        $this->adminReload(Link::getLinkAdminHref($this->getArrModule("modul"), "list", "&blockAction=1&peClose=1"));

        return "";
    }

    /**
     * Deletes a folder from Database. All subpages & subfolders turn up to top-level
     *
     * @throws Exception
     * @return string, "" in case of success
     */
    protected function actionDeleteFolderFinal()
    {
        $strReturn = "";
        $objFolder = new PagesFolder($this->getSystemid());
        if ($objFolder->rightDelete($this->getSystemid())) {
            $strPrevID = $objFolder->getPrevId();
            if ($objFolder->deleteObject()) {
                $this->adminReload(Link::getLinkAdminHref($this->getArrModule("modul"), "list", "&systemid=".$strPrevID));
            }
            else {
                throw new Exception($this->getLang("ordner_loeschen_fehler"), Exception::$level_ERROR);
            }
        }
        else {
            $strReturn .= $this->getLang("commons_error_permissions");
        }

        return $strReturn;
    }


    /**
     * Creates a pathnavigation through all folders till the current page / folder
     *
     * @return array
     */
    protected function getArrOutputNaviEntries()
    {
        $arrPathLinks = parent::getArrOutputNaviEntries();
        $arrPath = $this->getPathArray($this->getSystemid());
        //Link to root-folder
        foreach ($arrPath as $strOneFolderID) {

            /** @var $objInstance PagesFolder|PagesPage */
            $objInstance = Objectfactory::getInstance()->getObject($strOneFolderID);

            if ($objInstance instanceof PagesFolder) {
                $arrPathLinks[] = Link::getLinkAdmin("pages", "list", "&systemid=".$strOneFolderID."&unlockid=".$this->getSystemid(), $objInstance->getStrName());
            }
            if ($objInstance instanceof PagesPage) {
                $arrPathLinks[] = Link::getLinkAdmin("pages", "list", "&systemid=".$strOneFolderID."&unlockid=".$this->getSystemid(), $objInstance->getStrBrowsername());
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
    private function generateTreeView($strSideContent)
    {
        $strReturn = "";
        $arrNodesToExpand = array_merge(array($this->getObjModule()->getSystemid()), $this->getPathArray($this->getSystemid()));

        //generate the array of ids to expand initially
        $objTreeConfig = new SystemJSTreeConfig( );
        $objTreeConfig->setStrRootNodeId($this->getObjModule()->getSystemid());
        $objTreeConfig->setStrNodeEndpoint(Link::getLinkAdminXml("pages", "getChildNodes"));
        $objTreeConfig->setArrNodesToExpand($arrNodesToExpand);
        $objTreeConfig->addType(PagesJstreeNodeLoader::NODE_TYPE_PAGE_MODULE, array(PagesJstreeNodeLoader::NODE_TYPE_PAGE, PagesJstreeNodeLoader::NODE_TYPE_FOLDER));
        $objTreeConfig->addType(PagesJstreeNodeLoader::NODE_TYPE_FOLDER, array(PagesJstreeNodeLoader::NODE_TYPE_PAGE));
        $objTreeConfig->addType(PagesJstreeNodeLoader::NODE_TYPE_PAGE, array(PagesJstreeNodeLoader::NODE_TYPE_PAGE));

        $strReturn .= $this->objToolkit->getTreeview($objTreeConfig, $strSideContent);

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
    protected function actionListElements()
    {
        $strReturn = "";

        $objArraySectionIterator = new ArraySectionIterator(PagesElement::getObjectCountFiltered());
        $objArraySectionIterator->setPageNumber((int)($this->getParam("pv") != "" ? $this->getParam("pv") : 1));
        $objArraySectionIterator->setArraySection(PagesElement::getObjectList("", $objArraySectionIterator->calculateStartPos(), $objArraySectionIterator->calculateEndPos()));
        $strReturn .= $this->renderList($objArraySectionIterator, false, PagesAdminController::STR_LIST_ELEMENTS);

        return $strReturn;
    }


    /**
     * @return string
     */
    protected function actionEditElement()
    {
        return $this->actionNewElement("edit");
    }

    /**
     * Returns the form to edit / create an element
     *
     * @param string $strMode new || edit
     * @param AdminFormgenerator $objForm
     *
     * @return string
     * @autoTestable
     * @permissions right1
     */
    protected function actionNewElement($strMode = "new", AdminFormgenerator $objForm = null)
    {

        if ($strMode == "new") {
            $objElement = new PagesElement();
        }
        else {
            $objElement = new PagesElement($this->getSystemid());
        }

        if ($objForm == null) {
            $objForm = $this->getElementForm($objElement);
        }
        $objForm->addField(new FormentryHidden("", "mode"))->setStrValue($strMode);

        return $objForm->renderForm(Link::getLinkAdminHref($this->getArrModule("modul"), "saveElement"));
    }

    /**
     * Generates a simple form to edit and create elements' basic data.
     *
     * @param PagesElement $objElement
     *
     * @return AdminFormgenerator
     */
    private function getElementForm(PagesElement $objElement)
    {

        //Fetch Admin classes
        $arrClasses = Resourceloader::getInstance()->getFolderContent("/admin/elements", array(".php"));
        $arrClassesAdmin = array();
        foreach ($arrClasses as $strClass) {
            $arrClassesAdmin[$strClass] = $strClass;
        }

        //Fetch Portal-Classes
        $arrClassesPortal = array();
        $arrClasses = Resourceloader::getInstance()->getFolderContent("/portal/elements", array(".php"));
        foreach ($arrClasses as $strClass) {
            $arrClassesPortal[$strClass] = $strClass;
        }

        $objForm = new AdminFormgenerator("element", $objElement);
        //redefine for proper lang-rendering
        $objElement->setArrModuleEntry("modul", "pages");
        $objForm->generateFieldsFromObject();

        $objForm->getField("cachetime")->setStrHint($this->getLang("element_cachetime_hint"));
        $objForm->getField("classadmin")->setArrKeyValues($arrClassesAdmin);
        $objForm->getField("classportal")->setArrKeyValues($arrClassesPortal);

        //check if the config-vals may be overriden

        /** @var $objAdminInstance ElementAdmin */
        if ($objElement->getSystemid() != "") {
            $objAdminInstance = $objElement->getAdminElementInstance();
            if ($objAdminInstance->getConfigVal1Name() != "") {
                $objForm->addDynamicField("strConfigval1")->setStrLabel($objAdminInstance->getConfigVal1Name());
            }

            if ($objAdminInstance->getConfigVal2Name() != "") {
                $objForm->addDynamicField("strConfigval2")->setStrLabel($objAdminInstance->getConfigVal2Name());
            }

            if ($objAdminInstance->getConfigVal3Name() != "") {
                $objForm->addDynamicField("strConfigval3")->setStrLabel($objAdminInstance->getConfigVal3Name());
            }
        }

        return $objForm;
    }

    /**
     * Saves a passed element
     *
     * @throws Exception
     * @return string, "" in case of success
     * @permissions right1
     */
    protected function actionSaveElement()
    {

        if ($this->getParam("mode") == "new") {
            $objElement = new PagesElement();
        }
        else {
            $objElement = new PagesElement($this->getSystemid());
        }

        $objForm = $this->getElementForm($objElement);

        if (!$objForm->validateForm()) {
            return $this->actionNewElement($this->getParam("mode"), $objForm);
        }

        $objForm->updateSourceObject();

        if (!$objElement->updateObjectToDb()) {
            throw new Exception($this->getLang("element_anlegen_fehler"), Exception::$level_ERROR);
        }

        $this->flushCompletePagesCache();
        $this->adminReload(Link::getLinkAdminHref($this->getArrModule("modul"), "listElements"));
        return "";
    }

    /**
     * Deletes an element from db / displays the warning-box
     *
     * @throws Exception
     * @return string, "" in case of success
     * @permissions right1
     */
    protected function actionDeleteElement()
    {
        $strReturn = "";
        $objElement = new PagesElement($this->getParam("elementid"));
        if (!$objElement->deleteObject()) {
            throw new Exception($this->getLang("element_loeschen_fehler"), Exception::$level_ERROR);
        }

        $this->flushCompletePagesCache();
        $this->adminReload(Link::getLinkAdminHref($this->getArrModule("modul"), "listElements"));

        return $strReturn;
    }


    /**
     * Creates a form to update placeholder in the database
     *
     * @return string
     * @autoTestable
     * @permissions right3
     */
    protected function actionUpdatePlaceholder()
    {
        $strReturn = "";
        $strReturn .= $this->objToolkit->warningBox($this->getLang("quickhelp_update_placeholder"));
        $strReturn .= $this->objToolkit->getTextRow($this->getLang("plUpdateHelp"));
        $strReturn .= $this->objToolkit->divider();

        if ($this->getParam("plToUpdate") == "") {
            $strReturn .= $this->objToolkit->formHeader(Link::getLinkAdminHref($this->getArrModule("modul"), "updatePlaceholder"));
            //Load the available templates
            $arrTemplates = Resourceloader::getInstance()->getTemplatesInFolder("/module_pages");
            $arrTemplatesDD = array();
            $arrTemplatesDD[-1] = $this->getLang("plUpdateAll");
            if (count($arrTemplates) > 0) {
                foreach ($arrTemplates as $strTemplate) {
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
            if (PagesPageelement::updatePlaceholders($this->getParam("template"), $this->getParam("plToUpdate"), $this->getParam("plNew"))) {
                $strReturn .= $this->objToolkit->getTextRow($this->getLang("plUpdateTrue"));
            }
            else {
                $strReturn .= $this->objToolkit->getTextRow($this->getLang("plUpdateFalse"));
            }
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
    protected function actionPagesFolderBrowser()
    {
        $strReturn = "";
        $this->setArrModuleEntry("template", "/folderview.tpl");

        if ($this->getParam("CKEditorFuncNum") != "") {
            $strReturn .= "<script type=\"text/javascript\">window.opener.KAJONA.admin.folderview.selectCallbackCKEditorFuncNum = ".(int)$this->getParam("CKEditorFuncNum").";</script>";
        }

        //param init
        $bitPages = ($this->getParam("pages") != "" ? true : false);
        $bitPageAliases = ($this->getParam("pagealiases") != "" ? true : false);
        $bitPageelements = ($this->getParam("elements") == "false" ? false : true);
        $bitFolder = ($this->getParam("folder") != "" ? true : false);
        $strSystemid = ($this->getSystemid() != "" ? $this->getSystemid() : SystemModule::getModuleByName("pages")->getSystemid());
        $strElement = ($this->getParam("form_element") != "" ? $this->getParam("form_element") : "ordner_name");
        $strPageid = ($this->getParam("pageid") != "" ? $this->getParam("pageid") : "0");

        $strLinkAddon = "".($bitPages ? "&pages=1" : "").($bitFolder ? "&folder=1" : "").($this->getParam("bit_link") != "" ? "&bit_link=1" : "")
            .(!$bitPageelements ? "&elements=false" : "").($bitPageAliases ? "&pagealiases=1" : "");


        $arrFolder = PagesFolder::getFolderList($strSystemid);
        $objFolder = new PagesFolder($strSystemid);
        $strLevelUp = "";

        if (validateSystemid($strSystemid) && $strSystemid != $this->getObjModule()->getSystemid()) {
            $strLevelUp = $objFolder->getPrevId();
        }

        //but: when browsing pages the current level should be kept
        iF ($strPageid != "0") {
            $strLevelUp = $strSystemid;
        }


        $strReturn .= $this->objToolkit->formHeader("");
        $strAction = $this->objToolkit->listButton(
            "<a href=\"#\" title=\"".$this->getLang("select_page")."\" rel=\"tooltip\" onclick=\"KAJONA.admin.folderview.selectCallback([['".$strElement."', '"._indexpath_."?page='+$('#quickselect').val()]]);\">".AdminskinHelper::getAdminImage("icon_accept")."</a>"
        );
        $strReturn .= $this->objToolkit->formInputPageSelector("quickselect", $this->getLang("folderview_quickselect"), "", "", false, false, $strAction);
        $strReturn .= $this->objToolkit->formClose(false);


        $strReturn .= $this->objToolkit->listHeader();
        //Folder to jump one level up
        if (!$bitPages || $strLevelUp != "" || $bitFolder) {
            $strAction = $this->objToolkit->listButton(
                ($strSystemid != "0" && $strLevelUp != "") || $strPageid != "0" ? Link::getLinkAdmin($this->getArrModule("modul"), "pagesFolderBrowser", "&systemid=".$strLevelUp.$strLinkAddon."&form_element=".$strElement.($this->getParam("bit_link") != "" ? "&bit_link=1" : ""), $this->getLang("commons_one_level_up"), $this->getLang("commons_one_level_up"), "icon_folderActionLevelup") : " "
            );
            if ($strSystemid == $this->getObjModule()->getSystemid() && (!$bitPages || $bitFolder)) {
                $strAction .= $this->objToolkit->listButton(
                    "<a href=\"#\" title=\"".$this->getLang("commons_accept")."\" rel=\"tooltip\" onclick=\"KAJONA.admin.folderview.selectCallback([['".$strElement."_id', '".$this->getObjModule()->getSystemid()."'], ['".$strElement."', '']]);\">".AdminskinHelper::getAdminImage("icon_accept")
                );
            }

            $strReturn .= $this->objToolkit->genericAdminList(generateSystemid(), "..", getImageAdmin("icon_folderOpen"), $strAction);
        }

        if (count($arrFolder) > 0 && $strPageid == "0") {
            foreach ($arrFolder as $objSingleFolder) {
                if ($bitPages && !$bitFolder) {
                    $strAction = $this->objToolkit->listButton(
                        Link::getLinkAdmin(
                            $this->getArrModule("modul"),
                            "pagesFolderBrowser",
                            "&systemid=".$objSingleFolder->getSystemid()."&form_element=".$strElement.$strLinkAddon,
                            $this->getLang("pages_ordner_oeffnen"),
                            $this->getLang("pages_ordner_oeffnen"),
                            "icon_folderActionOpen"
                        )
                    );
                    $strReturn .= $this->objToolkit->simpleAdminList($objSingleFolder, $strAction);
                }
                else {
                    $strAction = $this->objToolkit->listButton(
                        Link::getLinkAdmin(
                            $this->getArrModule("modul"),
                            "pagesFolderBrowser",
                            "&systemid=".$objSingleFolder->getSystemid()."&form_element=".$strElement.$strLinkAddon,
                            $this->getLang("pages_ordner_oeffnen"),
                            $this->getLang("pages_ordner_oeffnen"),
                            "icon_folderActionOpen"
                        )
                    );
                    $strAction .= $this->objToolkit->listButton(
                        "<a href=\"#\" title=\"".$this->getLang("commons_accept")."\" rel=\"tooltip\" onclick=\"KAJONA.admin.folderview.selectCallback([['".$strElement."_id', '".$objSingleFolder->getSystemid()."'], ['".$strElement."', '".$objSingleFolder->getStrName()."']]); \">".AdminskinHelper::getAdminImage("icon_accept")
                    );
                    $strReturn .= $this->objToolkit->simpleAdminList($objSingleFolder, $strAction);
                }
            }

        }
        $strReturn .= $this->objToolkit->listFooter();

        //Pages could be sent too
        if ($bitPages && $strPageid == "0") {
            $arrPages = PagesFolder::getPagesInFolder($strSystemid);
            if (count($arrPages) > 0) {
                $strReturn .= $this->objToolkit->listHeader();

                /** @var $objSinglePage PagesPage */
                foreach ($arrPages as $objSinglePage) {
                    $arrSinglePage = array();
                    //Should we generate a link ?
                    if ($this->getParam("bit_link") != "") {
                        $arrSinglePage["name2"] = Link::getLinkPortalHref($objSinglePage->getStrName(), "", "", "", "", $this->getLanguageToWorkOn());
                    }
                    else {
                        $arrSinglePage["name2"] = $objSinglePage->getStrName();
                    }


                    if ($objSinglePage->getIntType() == PagesPage::$INT_TYPE_ALIAS) {
                        if (count(PagesFolder::getPagesInFolder($objSinglePage->getSystemid())) == 0) {
                            $strAction = getImageAdmin("icon_treeBranchOpenDisabled");
                        }
                        else {
                            $strAction = $this->objToolkit->listButton(
                                Link::getLinkAdmin(
                                    $this->getArrModule("modul"),
                                    "pagesFolderBrowser",
                                    "&systemid=".$objSinglePage->getSystemid()."&form_element=".$strElement.$strLinkAddon,
                                    $this->getLang("page_sublist"),
                                    $this->getLang("page_sublist"),
                                    "icon_treeBranchOpen"
                                )
                            );
                        }
                        if ($bitPageAliases) {
                            $strAction .= $this->objToolkit->listButton(
                                "<a href=\"#\" title=\"".$this->getLang("select_page")."\" rel=\"tooltip\" onclick=\"KAJONA.admin.folderview.selectCallback([['".$strElement."_id', '".$objSinglePage->getSystemid()."'],['".$strElement."', '".$arrSinglePage["name2"]."']]);\">".AdminskinHelper::getAdminImage("icon_accept")."</a>"
                            );
                        }

                        $strReturn .= $this->objToolkit->simpleAdminList($objSinglePage, $strAction);
                    }
                    else {
                        if (count(PagesFolder::getPagesInFolder($objSinglePage->getSystemid())) == 0) {
                            $strAction = getImageAdmin("icon_treeBranchOpenDisabled");
                        }
                        else {
                            $strAction = $this->objToolkit->listButton(
                                Link::getLinkAdmin(
                                    $this->getArrModule("modul"),
                                    "pagesFolderBrowser",
                                    "&systemid=".$objSinglePage->getSystemid()."&form_element=".$strElement.$strLinkAddon,
                                    $this->getLang("page_sublist"),
                                    $this->getLang("page_sublist"),
                                    "icon_treeBranchOpen"
                                )
                            );
                        }
                        if ($bitPageelements) {
                            $strAction .= $this->objToolkit->listButton(
                                Link::getLinkAdmin(
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
                            "<a href=\"#\" title=\"".$this->getLang("select_page")."\" rel=\"tooltip\" onclick=\"KAJONA.admin.folderview.selectCallback([['".$strElement."_id', '".$objSinglePage->getSystemid()."'],['".$strElement."', '".$arrSinglePage["name2"]."']]);\">".AdminskinHelper::getAdminImage("icon_accept")."</a>"
                        );
                        $strReturn .= $this->objToolkit->simpleAdminList($objSinglePage, $strAction);

                    }
                }
                $strReturn .= $this->objToolkit->listFooter();
            }
        }

        //Load the list of pagelements available on the page
        if ($strPageid != "0") {
            $strReturn .= $this->objToolkit->divider();
            $arrPageelements = PagesPageelement::getElementsOnPage($strPageid, true, $this->getLanguageToWorkOn());
            $objPage = new PagesPage($strPageid);
            if (count($arrPageelements) > 0) {
                $strReturn .= $this->objToolkit->listHeader();
                /** @var PagesPageelement $objOnePageelement */
                foreach ($arrPageelements as $objOnePageelement) {
                    $arrSinglePage = array();
                    //Should we generate a link ?
                    if ($this->getParam("bit_link") != "") {
                        $arrSinglePage["name2"] = Link::getLinkPortalHref($objPage->getStrName(), "", "", "", "", $this->getLanguageToWorkOn())."#".$objOnePageelement->getSystemid();
                    }
                    else {
                        $arrSinglePage["name2"] = $objPage->getStrName()."#".$objOnePageelement->getSystemid();
                    }

                    $strAction = $this->objToolkit->listButton(
                        "<a href=\"#\" title=\"".$this->getLang("seite_uebernehmen")."\" rel=\"tooltip\" onclick=\"KAJONA.admin.folderview.selectCallback([['".$strElement."', '".$arrSinglePage["name2"]."']]);\">".AdminskinHelper::getAdminImage("icon_accept")."</a>"
                    );
                    $strReturn .= $this->objToolkit->simpleAdminList($objOnePageelement, $strAction);
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
    protected function actionGetPagesByFilter()
    {
        $strFilter = $this->getParam("filter");
        $arrPages = PagesPage::getAllPages(null, null, $strFilter);

        $arrReturn = array();
        foreach ($arrPages as $objOnePage) {
            if ($objOnePage->rightView()) {
                $arrReturn[] = $objOnePage->getStrName();
            }
        }
        ResponseObject::getInstance()->setStrResponseType(HttpResponsetypes::STR_TYPE_JSON);
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
    protected function actionGetChildNodes()
    {
        $objJsTreeLoader = new SystemJSTreeBuilder(
            new PagesJstreeNodeLoader()
        );

        $arrSystemIdPath = $this->getParam(SystemJSTreeBuilder::STR_PARAM_INITIALTOGGLING);
        $bitInitialLoading = is_array($arrSystemIdPath);
        if(!$bitInitialLoading) {
            $arrSystemIdPath = array($this->getSystemid());
        }

        $arrReturn = $objJsTreeLoader->getJson($arrSystemIdPath, $bitInitialLoading, $this->getParam(SystemJSTreeBuilder::STR_PARAM_LOADALLCHILDNOES) === "true");
        ResponseObject::getInstance()->setStrResponseType(HttpResponsetypes::STR_TYPE_JSON);
        return $arrReturn;
    }

}

