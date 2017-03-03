<?php
/*"******************************************************************************************************
*   (c) 2007-2016 by Kajona, www.kajona.de                                                              *
*       Published under the GNU LGPL v2.1, see /system/licence_lgpl.txt                                 *
*-------------------------------------------------------------------------------------------------------*
*	$Id$	                                            *
********************************************************************************************************/

namespace Kajona\System\Admin;

use Closure;
use Kajona\System\System\AdminGridableInterface;
use Kajona\System\System\AdminListableInterface;
use Kajona\System\System\AdminskinHelper;
use Kajona\System\System\ArraySectionIterator;
use Kajona\System\System\Exception;
use Kajona\System\System\Link;
use Kajona\System\System\Lockmanager;
use Kajona\System\System\Model;
use Kajona\System\System\ModelInterface;
use Kajona\System\System\StringUtil;
use Kajona\System\System\SystemSetting;
use Kajona\System\System\VersionableInterface;


/**
 * Class holding common methods for extended and simplified admin-guis.
 *
 * @package module_system
 * @since 4.0
 */
abstract class AdminSimple extends AdminController
{

    private $strPeAddon = "";

    /**
     * @param string $strSystemid
     */
    public function __construct($strSystemid = "")
    {
        parent::__construct($strSystemid);

        if ($this->getParam("pe") == "1") {
            $this->strPeAddon = "&pe=1";
        }

        if ($this->getParam("unlockid") != "") {
            $objLockmanager = new Lockmanager($this->getParam("unlockid"));
            $objLockmanager->unlockRecord(true);
        }
    }

    /**
     * Overwritten in order to inject a toolbar per record. may be useful for certain actions
     *
     * @param array &$arrContent
     *
     * @return void
     */
    protected function onRenderOutput(&$arrContent)
    {
        $arrContent["actiontoolbar"] = $this->objToolkit->getContentActionToolbar($this->getContentActionToolbar());
    }

    /**
     * Default-implementation to render an action toolbar
     *
     * @return string
     */
    protected function getContentActionToolbar()
    {
        if (StringUtil::indexOf($this->getAction(), "list") !== false || StringUtil::indexOf($this->getAction(), "new") !== false || StringUtil::indexOf($this->getAction(), "save") !== false) {
            return "";
        }

        if (validateSystemid($this->getSystemid())) {
            $objRecord = $this->objFactory->getObject($this->getSystemid());

            if ($objRecord instanceof AdminListableInterface) {
                return $this->getActionIcons($objRecord);
            }
        }

        return "";
    }


    /**
     * Renders the form to create a new entry
     *
     * @abstract
     * @return string
     * @permissions edit
     */
    protected abstract function actionNew();

    /**
     * Renders the form to edit an existing entry
     *
     * @abstract
     * @return string
     * @permissions edit
     */
    protected abstract function actionEdit();

    /**
     * Renders the general list of records
     *
     * @abstract
     * @return string
     * @permissions view
     */
    protected abstract function actionList();


    /**
     * A general action to delete a record.
     * This method may be overwritten by subclasses.
     *
     * @permissions delete
     * @throws Exception
     * @return void
     */
    protected function actionDelete()
    {
        $objRecord = $this->objFactory->getObject($this->getSystemid());
        if ($objRecord != null && $objRecord->rightDelete()) {
            if (!$objRecord->deleteObject()) {
                throw new Exception("error deleting object ".strip_tags($objRecord->getStrDisplayName()), Exception::$level_ERROR);
            }

            $strTargetUrl = urldecode($this->getParam("reloadUrl"));

            if ($strTargetUrl == "" || StringUtil::indexOf($strTargetUrl, $this->getSystemid()) !== false) {
                 $strTargetUrl = "admin=1&module=".$this->getArrModule("modul");

                $intI = 1;
                while ($this->getHistory($intI) !== null) {
                    $strTargetUrl = $this->getHistory($intI++);

                    if (StringUtil::indexOf($strTargetUrl, $this->getSystemid()) === false) {
                        if (StringUtil::indexOf($strTargetUrl, "admin=1") === false) {
                            $strTargetUrl = "admin=1&module=".$this->getArrModule("modul");
                        }

                        break;
                    }
                }

                $strTargetUrl = Link::plainUrlToHashUrl($strTargetUrl);
                $strTargetUrl = StringUtil::replace(array(_indexpath_."?admin=1", ""), "", $strTargetUrl);
            }
            return "<script type='text/javascript'>require('router').loadUrl('{$strTargetUrl}')</script>";
        }
        else {
            throw new Exception("error loading object ".$this->getSystemid(), Exception::$level_ERROR);
        }
    }

    /**
     * A general action to copy a record.
     * This method may be overwritten by subclasses.
     *
     * @permissions edit
     * @throws Exception
     * @return void
     */
    protected function actionCopyObject()
    {
        $objRecord = $this->objFactory->getObject($this->getSystemid());
        if ($objRecord != null && $objRecord->rightEdit()) {
            if (!$objRecord->copyObject()) {
                throw new Exception("error creating a copy of object ".strip_tags($objRecord->getStrDisplayName()), Exception::$level_ERROR);
            }

            $this->adminReload(Link::getLinkAdminHref($this->getArrModule("modul"), $this->getActionNameForClass("list", $objRecord), "&systemid=".$objRecord->getPrevId()));
        }
        else {
            throw new Exception("error loading object ".$this->getSystemid(), Exception::$level_ERROR);
        }
    }


    /**
     * Returns the action name for a given class name.
     *
     * @param string $strAction
     * @param ModelInterface $objInstance
     *
     * @return string specific action name
     */
    protected function getActionNameForClass($strAction, $objInstance)
    {
        return $strAction;
    }


    /**
     * Renders a list of items in a floatable "thumbnail" view, so a grid.
     * Please be aware, that the combination of paging and grids may result in unpredictable ordering.
     * As soon as the list is sortable, the page-size should be at least the same as the number of elements
     *
     * @param ArraySectionIterator $objArraySectionIterator
     * @param string $strListIdentifier an internal identifier to check the current parent-list
     * @param string $strPagerAddon
     * @param bool $bitSortable
     *
     * @throws Exception
     * @return string
     */
    protected final function renderFloatingGrid(ArraySectionIterator $objArraySectionIterator, $strListIdentifier = "", $strPagerAddon = "", $bitSortable = true)
    {
        $strReturn = "";

        $strListActions = "";
        if ($this->renderLevelUpAction($strListIdentifier) != "") {
            $strListActions .= $this->objToolkit->listButton($this->renderLevelUpAction($strListIdentifier));
        }

        $strListActions .= $this->mergeNewEntryActions($this->getNewEntryAction($strListIdentifier));

        if ($strListActions != "") {
            $strReturn .= $this->objToolkit->listHeader();
            $strReturn .= $this->objToolkit->genericAdminList("", "", "", $strListActions);
            $strReturn .= $this->objToolkit->listFooter();
        }


        if (!$objArraySectionIterator->valid()) {
            $strReturn .= $this->objToolkit->getTextRow($this->getLang("commons_list_empty"));
        }


        if ($objArraySectionIterator->valid()) {

            $strReturn .= $this->objToolkit->gridHeader($bitSortable, $objArraySectionIterator->getIntElementsPerPage(), $objArraySectionIterator->getPageNumber());

            /** @var $objOneIterable Model|ModelInterface|AdminGridableInterface */
            foreach ($objArraySectionIterator as $objOneIterable) {

                if (!$objOneIterable->rightView() || !$objOneIterable instanceof AdminGridableInterface) {
                    continue;
                }

                $strActions = $this->getActionIcons($objOneIterable, $strListIdentifier);
                $strReturn .= $this->objToolkit->gridEntry($objOneIterable, $strActions, $this->renderGridEntryClickAction($objOneIterable, $strListIdentifier));
            }

            $strReturn .= $this->objToolkit->gridFooter();
        }

        $strReturn .= $this->objToolkit->getPageview($objArraySectionIterator, $this->getArrModule("modul"), $this->getAction(), "&systemid=".$this->getSystemid().$this->strPeAddon.$strPagerAddon);

        return $strReturn;
    }


    /**
     * Renders a list of items, target is the common admin-list.
     * Please be aware, that the combination of paging and sortable-lists may result in unpredictable ordering.
     * As soon as the list is sortable, the page-size should be at least the same as the number of elements. Optional
     * it is possible to provide a filter callback which is called for each entry. If the callback returns false the
     * entry gets skipped.
     *
     * @param ArraySectionIterator $objArraySectionIterator
     * @param bool $bitSortable
     * @param string $strListIdentifier an internal identifier to check the current parent-list
     * @param bool $bitAllowTreeDrop
     * @param string $strPagerAddon
     * @param Closure $objFilter
     *
     * @throws Exception
     * @return string
     */
    protected final function renderList(ArraySectionIterator $objArraySectionIterator, $bitSortable = false, $strListIdentifier = "", $bitAllowTreeDrop = false, $strPagerAddon = "", Closure $objFilter = null)
    {
        $strReturn = "";

        $strListId = generateSystemid();

        if (!$objArraySectionIterator->valid()) {
            $strReturn .= $this->objToolkit->getTextRow($this->getLang("commons_list_empty"));
        }

        if ($bitSortable) {
            $strReturn .= $this->objToolkit->dragableListHeader($strListId, false, $bitAllowTreeDrop, $objArraySectionIterator->getIntElementsPerPage(), $objArraySectionIterator->getPageNumber());
        }
        else {
            $strReturn .= $this->objToolkit->listHeader();
        }

        if ($this->renderLevelUpAction($strListIdentifier) != "") {
            $strReturn .= $this->objToolkit->genericAdminList("", "", "", $this->objToolkit->listButton($this->renderLevelUpAction($strListIdentifier)));
        }

        $arrMassActions = $this->getBatchActionHandlers($strListIdentifier);

        $intTotalNrOfElements = $objArraySectionIterator->getNumberOfElements();
        /** @var $objOneIterable Model|ModelInterface|AdminListableInterface|ModelInterface */
        foreach ($objArraySectionIterator as $objOneIterable) {

            // if we have a filter Closure call it else use the standard rightView method
            if ($objFilter !== null) {
                if ($objFilter($objOneIterable) === false) {
                    if ($bitSortable) {
                        //inject hidden dummy row for a proper sorting
                        $strReturn .= $this->objToolkit->genericAdminList($objOneIterable->getSystemid(), "", "", "", "", "", false, "hidden");
                    }
                    $intTotalNrOfElements--;
                    continue;
                }
            }
            elseif (!$objOneIterable->rightView()) {
                if ($bitSortable) {
                    //inject hidden dummy row for a proper sorting
                    $strReturn .= $this->objToolkit->genericAdminList($objOneIterable->getSystemid(), "", "", "", "", "", false, "hidden");
                }
                $intTotalNrOfElements--;
                continue;
            }

            $strActions = $this->getActionIcons($objOneIterable, $strListIdentifier);
            $strReturn .= $this->objToolkit->simpleAdminList($objOneIterable, $strActions, count($arrMassActions) > 0);

        }

        $strNewActions = $this->mergeNewEntryActions($this->getNewEntryAction($strListIdentifier));
        $strBatchActions = "";

        if (count($arrMassActions) > 0) {
            $strBatchActions .= $this->objToolkit->renderBatchActionHandlers($arrMassActions);
        }

        if ($strNewActions != "" || $strBatchActions != "") {
            $strReturn .= $this->objToolkit->genericAdminList("batchActionSwitch", $strBatchActions, "", $strNewActions, "", "", $strBatchActions != "");
        }

        if ($bitSortable) {
            $strReturn .= $this->objToolkit->dragableListFooter($strListId);
        }
        else {
            $strReturn .= $this->objToolkit->listFooter();
        }

        $objArraySectionIterator->setIntTotalElements($intTotalNrOfElements);
        $strReturn .= $this->objToolkit->getPageview($objArraySectionIterator, $this->getArrModule("modul"), $this->getAction(), "&systemid=".$this->getSystemid().$this->strPeAddon.$strPagerAddon);

        return $strReturn;
    }

    /**
     * Wrapper rendering all action-icons for a given record. In most cases used to render a list-entry.
     *
     * @param Model|ModelInterface|AdminListableInterface $objOneIterable
     * @param string $strListIdentifier
     *
     * @return string
     */
    public function getActionIcons($objOneIterable, $strListIdentifier = "")
    {
        $strActions = "";
        $strActions .= $this->renderUnlockAction($objOneIterable);
        $strActions .= $this->renderEditAction($objOneIterable);
        $arrAddons = $this->renderAdditionalActions($objOneIterable);
        if (is_array($arrAddons)) {
            $strActions .= implode("", $arrAddons);
        }
        $strActions .= $this->renderDeleteAction($objOneIterable);
        $strActions .= $this->renderCopyAction($objOneIterable);
        $strActions .= $this->renderStatusAction($objOneIterable);
        $strActions .= $this->renderTagAction($objOneIterable);
        $strActions .= $this->renderChangeHistoryAction($objOneIterable);
        $strActions .= $this->renderPermissionsAction($objOneIterable);

        return $strActions;
    }


    /**
     * Renders the action to jump a level upwards.
     * Overwrite this method if you want to provide such an action.
     *
     * @param string $strListIdentifier
     *
     * @return string
     */
    protected function renderLevelUpAction($strListIdentifier)
    {
        return "";
    }

    /**
     * For grid elements, an additional action may be defined when clicking the whole object.
     * This action is "in addition" to the action-toolbar at the bottom of the grid entry.
     * Make sure to pass a full eventhandler, e.g. onclick="document.location=''"
     * Overwrite this method if you want to provide such an action.
     *
     * @param AdminListableInterface $objOneIterable
     * @param string $strListIdentifier
     *
     * @return string
     */
    protected function renderGridEntryClickAction($objOneIterable, $strListIdentifier)
    {
        return "";
    }


    /**
     * Renders the edit action button for the current record.
     *
     * @param Model|AdminListableInterface|ModelInterface $objListEntry
     * @param bool $bitDialog opens the linked page in a js-based dialog
     *
     * @return string
     */
    protected function renderEditAction(Model $objListEntry, $bitDialog = false)
    {
        if ($objListEntry->getIntRecordDeleted() == 1) {
            return "";
        }

        if ($objListEntry->rightEdit()) {

            $objLockmanager = $objListEntry->getLockManager();
            if (!$objLockmanager->isAccessibleForCurrentUser()) {
                return $this->objToolkit->listButton(AdminskinHelper::getAdminImage("icon_editLocked", $this->getLang("commons_locked")));
            }

            if ($bitDialog) {
                return $this->objToolkit->listButton(
                    Link::getLinkAdminDialog(
                        $objListEntry->getArrModule("modul"),
                        $this->getActionNameForClass("edit", $objListEntry),
                        "folderview=1&systemid=".$objListEntry->getSystemid().$this->strPeAddon,
                        $this->getLang("commons_list_edit"),
                        $this->getLang("commons_list_edit"),
                        "icon_edit",
                        $objListEntry->getStrDisplayName()
                    )
                );
            }
            else {
                return $this->objToolkit->listButton(
                    Link::getLinkAdmin(
                        $objListEntry->getArrModule("modul"),
                        $this->getActionNameForClass("edit", $objListEntry),
                        "&systemid=".$objListEntry->getSystemid().$this->strPeAddon,
                        $this->getLang("commons_list_edit"),
                        $this->getLang("commons_list_edit"),
                        "icon_edit"
                    )
                );
            }
        }
        return "";
    }


    /**
     * Renders the unlock action button for the current record.
     *
     * @param Model|ModelInterface $objListEntry
     *
     * @return string
     */
    protected function renderUnlockAction(ModelInterface $objListEntry)
    {
        if ($objListEntry->getIntRecordDeleted() == 1) {
            return "";
        }

        $objLockmanager = $objListEntry->getLockManager();
        if (!$objLockmanager->isAccessibleForCurrentUser()) {
            if ($objLockmanager->isUnlockableForCurrentUser()) {
                return $this->objToolkit->listButton(
                    Link::getLinkAdmin($objListEntry->getArrModule("modul"), $this->getAction(), "&systemid=".$this->getSystemid()."&unlockid=".$objListEntry->getSystemid(), "", $this->getLang("commons_unlock"), "icon_lockerOpen")
                );
            }
        }
        return "";
    }


    /**
     * Renders the delete action button for the current record.
     *
     * @param Model|ModelInterface $objListEntry
     *
     * @return string
     */
    protected function renderDeleteAction(ModelInterface $objListEntry)
    {
        if ($objListEntry->getIntRecordDeleted() == 1) {
            return "";
        }

        if ($objListEntry->rightDelete()) {

            $objLockmanager = $objListEntry->getLockManager();
            if (!$objLockmanager->isAccessibleForCurrentUser()) {
                return $this->objToolkit->listButton(AdminskinHelper::getAdminImage("icon_deleteLocked", $this->getLang("commons_locked")));
            }

            return $this->objToolkit->listDeleteButton(
                strip_tags($objListEntry->getStrDisplayName()),
                $this->getLang($this->getObjLang()->stringToPlaceholder($this->getActionNameForClass("delete", $objListEntry)."_question"), $objListEntry->getArrModule("modul")),
                Link::getLinkAdminHref($objListEntry->getArrModule("modul"), $this->getActionNameForClass("delete", $objListEntry), "&systemid=".$objListEntry->getSystemid().$this->strPeAddon)
            );
        }
        return "";
    }

    /**
     * Renders the status action button for the current record.
     *
     * @param Model $objListEntry
     * @param string $strAltActive tooltip text for the icon if record is active
     * @param string $strAltInactive tooltip text for the icon if record is inactive
     *
     * @return string
     */
    protected function renderStatusAction(Model $objListEntry, $strAltActive = "", $strAltInactive = "")
    {
        if ($objListEntry->getIntRecordDeleted() == 1) {
            return "";
        }

        if ($objListEntry->rightEdit() && $this->strPeAddon == "") {
            return $this->objToolkit->listStatusButton($objListEntry, false, $strAltActive, $strAltInactive);
        }
        return "";
    }

    /**
     * Renders the permissions action button for the current record.
     *
     * @param Model|ModelInterface $objListEntry
     *
     * @return string
     */
    protected function renderPermissionsAction(Model $objListEntry)
    {
        if ($objListEntry->rightRight() && $this->strPeAddon == "") {
            return $this->objToolkit->listButton(
                Link::getLinkAdminDialog(
                    "right",
                    $this->getActionNameForClass("change", $objListEntry),
                    "&systemid=".$objListEntry->getSystemid().$this->strPeAddon,
                    "",
                    $this->getLang("commons_edit_permissions"),
                    getRightsImageAdminName($objListEntry->getSystemid()),
                    strip_tags($objListEntry->getStrDisplayName()),
                    true,
                    true
                )
            );
        }
        return "";
    }

    /**
     * Renders the icon to edit a records tags
     *
     * @param Model|ModelInterface $objListEntry
     *
     * @return string
     */
    protected function renderTagAction(Model $objListEntry)
    {
        if ($objListEntry->getIntRecordDeleted() == 1) {
            return "";
        }

        if ($objListEntry->rightView()) {

            //sanitize critical chars
            $strDialogTitle = $objListEntry->getStrDisplayName();
            $strDialogTitle = addslashes(StringUtil::replace(array("\n", "\r"), array(), strip_tags(nl2br($strDialogTitle))));


            //the tag list is more complex and wrapped by a js-logic to load the tags by ajax afterwards
            // @codingStandardsIgnoreStart
            $strOnClick = "require('folderview').dialog.setContentIFrame('".Link::getLinkAdminHref("tags", "genericTagForm", "&systemid=".$objListEntry->getSystemid())."'); require('folderview').dialog.setTitle('".$strDialogTitle."'); require('folderview').dialog.init(); return false;";
            $strLink = "<a href=\"#\" onclick=\"".$strOnClick."\" title=\"".$this->getLang("commons_edit_tags")."\" rel=\"tagtooltip\" data-systemid=\"".$objListEntry->getSystemid()."\">".AdminskinHelper::getAdminImage("icon_tag", $this->getLang("commons_edit_tags"), true)."</a>";
            // @codingStandardsIgnoreEnd
            return $this->objToolkit->listButton($strLink);

        }
        return "";
    }


    /**
     * Renders the permissions action button for the current record.
     *
     * @param Model|ModelInterface $objListEntry
     *
     * @return string
     */
    protected function renderCopyAction(Model $objListEntry)
    {
        if ($objListEntry->getIntRecordDeleted() == 1) {
            return "";
        }

        if ($objListEntry->rightEdit() && $this->strPeAddon == "") {
            $strQuestion = $this->getLang("commons_copy_record_question", "system", array(StringUtil::jsSafeString($objListEntry->getStrDisplayName())));
            $strHref = Link::getLinkAdminHref($objListEntry->getArrModule("modul"), $this->getActionNameForClass("copyObject", $objListEntry), "&systemid=".$objListEntry->getSystemid().$this->strPeAddon);

            //create the list-button and the js code to show the dialog
            $strButton = Link::getLinkAdminManual(
                "href=\"#\" onclick=\"javascript:jsDialog_1.setTitle('".$this->getLang("dialog_copyHeader", "system")."'); jsDialog_1.setContent('".$strQuestion."', '".$this->getLang("dialog_copyButton", "system")."',  function() {document.location.href= '{$strHref}';}); jsDialog_1.init(); return false;\"",
                "",
                $this->getLang("commons_edit_copy", "system"),
                "icon_copy"
            );

            return $this->objToolkit->listButton($strButton);
        }
        return "";
    }

    /**
     * Returns an additional set of action-buttons rendered right after the edit-action.
     *
     * @param Model $objListEntry
     *
     * @return array
     */
    protected function renderAdditionalActions(Model $objListEntry)
    {
        return array();
    }

    /**
     * Renders the action to add a new record to the end of the list.
     * Make sure you have the lang-key "module_action_new" in the modules' lang-file.
     * If you overwrite this method, you can either return a string containing the new-link or an array if you want to
     * provide multiple new-action.
     *
     * @param string $strListIdentifier an internal identifier to check the current parent-list
     * @param bool $bitDialog opens the linked pages in a dialog
     *
     * @return string|array
     */
    protected function getNewEntryAction($strListIdentifier, $bitDialog = false)
    {
        $objObject = null;
        if (validateSystemid($this->getSystemid())) {
            $objObject = $this->objFactory->getObject($this->getSystemid());
        }
        if ($objObject == null) {
            $objObject = $this->getObjModule();
        }

        if ($objObject->rightEdit()) {
            if ($bitDialog) {
                return $this->objToolkit->listButton(
                    Link::getLinkAdminDialog(
                        $this->getArrModule("modul"), $this->getActionNameForClass("new", null), "&folderview=1&systemid=".$this->getSystemid().$this->strPeAddon, $this->getLang("commons_list_new"), $this->getLang("commons_list_new"), "icon_new"
                    )
                );
            }
            else {
                return $this->objToolkit->listButton(
                    Link::getLinkAdmin(
                        $this->getArrModule("modul"), $this->getActionNameForClass("new", null), "&systemid=".$this->getSystemid().$this->strPeAddon, $this->getLang("commons_list_new"), $this->getLang("commons_list_new"), "icon_new"
                    )
                );
            }
        }
        return "";
    }

    /**
     * If multiple new actions are given, all buttons are merged into a new, single button.
     * The button itself is rendering the different buttons using a new dropdown-menu.
     *
     * @param array $arrActions
     *
     * @return string
     */
    private function mergeNewEntryActions($arrActions)
    {
        if (!is_array($arrActions)) {
            return $arrActions;
        }

        if (is_array($arrActions) && count($arrActions) == 0) {
            return "";
        }

        if (is_array($arrActions) && count($arrActions) == 1) {
            return $arrActions[0];
        }

        //create a menu and merge all buttons
        $arrActionMenuEntries = array();
        foreach ($arrActions as $strOneAction) {
            $strOneAction = trim($strOneAction);
            //search for a title attribute
            $arrMatches = array();
            if (preg_match('/<a.*?title=(["\'])(.*?)\1.*$/i', $strOneAction, $arrMatches)) {
                if (StringUtil::substring($strOneAction, -11) == "</a></span>") {
                    $strOneAction = StringUtil::substring($strOneAction, 0, -11).$arrMatches[2]."</a></span>";
                }
                else {
                    $strOneAction .= $arrMatches[2];
                }
            }

            $arrActionMenuEntries[] = array("fullentry" => $strOneAction);
        }

        return $this->objToolkit->listButton(
            "<span class='dropdown pull-right'><a href='#' data-toggle='dropdown' role='button'>".AdminskinHelper::getAdminImage("icon_new_multi")."</a>".$this->objToolkit->registerMenu(generateSystemid(), $arrActionMenuEntries)."</span>"
        );

    }

    /**
     * Overwrite this method if you want to provide a handler for a mass-action.
     * If one or more handler(s) are returned, the checkboxes to select a list of records
     * are rendered.
     *
     * @param string $strListIdentifier
     *
     * @return AdminBatchaction[]
     */
    protected function getBatchActionHandlers($strListIdentifier)
    {
        return array();
    }


    /**
     * @return array
     */
    protected function getDefaultActionHandlers()
    {
        $arrReturn = array();
        if ($this->getObjModule()->rightDelete()) {
            $arrReturn[] = new AdminBatchaction(AdminskinHelper::getAdminImage("icon_delete"), Link::getLinkAdminXml("system", "delete", "&systemid=%systemid%"), $this->getLang("commons_batchaction_delete"));
        }

        if ($this->getObjModule()->rightEdit()) {
            $arrReturn[] = new AdminBatchaction(AdminskinHelper::getAdminImage("icon_enabled"), Link::getLinkAdminXml("system", "setStatus", "&systemid=%systemid%&status=1"), $this->getLang("commons_batchaction_enable"));
            $arrReturn[] = new AdminBatchaction(AdminskinHelper::getAdminImage("icon_disabled"), Link::getLinkAdminXml("system", "setStatus", "&systemid=%systemid%&status=0"), $this->getLang("commons_batchaction_disable"));
        }
        return $arrReturn;
    }

    /**
     * Renders the button to open the records' change history. In most cases, this is done in a overlay.
     * To open the change-history, the permission "right3" on the system-module is required.
     *
     * @param Model|ModelInterface $objListEntry
     *
     * @return string
     */
    protected function renderChangeHistoryAction(Model $objListEntry)
    {
        if (SystemSetting::getConfigValue("_system_changehistory_enabled_") == "true" && $objListEntry instanceof VersionableInterface && $objListEntry->rightChangelog()) {
            return $this->objToolkit->listButton(
                Link::getLinkAdminDialog(
                    "system",
                    "genericChangelog",
                    "&systemid=".$objListEntry->getSystemid()."&folderview=1",
                    $this->getLang("commons_edit_history"),
                    $this->getLang("commons_edit_history"),
                    "icon_history",
                    $objListEntry->getStrDisplayName()
                )
            );
        }
        return "";
    }

    /**
     * @param string $strPeAddon
     *
     * @return void
     */
    public function setStrPeAddon($strPeAddon)
    {
        $this->strPeAddon = $strPeAddon;
    }

    /**
     * @return string
     */
    public function getStrPeAddon()
    {
        return $this->strPeAddon;
    }


}

