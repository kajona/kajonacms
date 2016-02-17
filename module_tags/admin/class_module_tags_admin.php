<?php
/*"******************************************************************************************************
*   (c) 2004-2006 by MulchProductions, www.mulchprod.de                                                 *
*   (c) 2007-2015 by Kajona, www.kajona.de                                                              *
*       Published under the GNU LGPL v2.1, see /system/licence_lgpl.txt                                 *
*-------------------------------------------------------------------------------------------------------*
*	$Id$                                  *
********************************************************************************************************/
use Kajona\System\System\ModelInterface;

/**
 * Admin-Part of the tags.
 * No classical functionality, rather a list of helper-methods, e.g. in order to
 * create the form to tag content.
 *
 * @package module_tags
 * @author sidler@mulchprod.de
 *
 * @objectList class_module_tags_tag
 * @objectEdit class_module_tags_tag
 *
 * @autoTestable list
 *
 * @module tags
 * @moduleId _tags_modul_id_
 */
class class_module_tags_admin extends class_admin_evensimpler implements interface_admin {

    public function getOutputModuleNavi() {
        $arrReturn = array();
        $arrReturn[] = array("view", class_link::getLinkAdmin($this->getArrModule("modul"), "list", "", $this->getLang("commons_list"), "", "", true, "adminnavi"));
        $arrReturn[] = array("right1", class_link::getLinkAdmin($this->getArrModule("modul"), "listFavorites", "", $this->getLang("action_list_favorites"), "", "", true, "adminnavi"));

        return $arrReturn;
    }



    protected function getNewEntryAction($strListIdentifier, $bitDialog = false) {
        return "";
    }

    protected function renderAdditionalActions(\Kajona\System\System\Model $objListEntry) {
        if($objListEntry instanceof class_module_tags_tag) {
            $arrButtons = array();
            $arrButtons[] = $this->objToolkit->listButton(
                class_link::getLinkAdmin(
                    $this->getArrModule("modul"),
                    "showAssignedRecords",
                    "&systemid=" . $objListEntry->getSystemid(),
                    $this->getLang("action_show_assigned_records"),
                    $this->getLang("action_show_assigned_records"),
                    "icon_folderActionOpen"
                )
            );

            if($objListEntry->rightRight1()) {

                $strJs = "<script type='text/javascript'>KAJONA.admin.loader.loadFile('".class_resourceloader::getInstance()->getCorePathForModule("module_tags")."/module_tags/admin/scripts/tags.js', function() {
                    KAJONA.admin.tags.createFavoriteEnabledIcon = '".addslashes(class_adminskin_helper::getAdminImage("icon_favorite", $this->getLang("tag_favorite_remove")))."';
                    KAJONA.admin.tags.createFavoriteDisabledIcon = '".addslashes(class_adminskin_helper::getAdminImage("icon_favoriteDisabled", $this->getLang("tag_favorite_add")))."';
                });</script>";

                $strImage = class_module_tags_favorite::getAllFavoritesForUserAndTag($this->objSession->getUserID(), $objListEntry->getSystemid()) != null ?
                    class_adminskin_helper::getAdminImage("icon_favorite", $this->getLang("tag_favorite_remove")) :
                    class_adminskin_helper::getAdminImage("icon_favoriteDisabled", $this->getLang("tag_favorite_add"));

                $arrButtons[] = $strJs.$this->objToolkit->listButton("<a href=\"#\" onclick=\"KAJONA.admin.tags.createFavorite('".$objListEntry->getSystemid()."', this); return false;\">".$strImage."</a>");
            }

            return $arrButtons;

        }
        else {
            return array();
        }
    }


    /**
     * @param \Kajona\System\System\ModelInterface|\Kajona\System\System\Model $objListEntry
     *
     * @return string
     */
    protected function renderDeleteAction(\Kajona\System\System\ModelInterface $objListEntry) {
        if($objListEntry instanceof class_module_tags_favorite) {
            if($objListEntry->rightDelete()) {
                return $this->objToolkit->listDeleteButton(
                    $objListEntry->getStrDisplayName(),
                    $this->getLang("delete_question_fav", $objListEntry->getArrModule("modul")),
                    class_link::getLinkAdminHref($objListEntry->getArrModule("modul"), "delete", "&systemid=" . $objListEntry->getSystemid())
                );
            }
        }
        else
            return parent::renderDeleteAction($objListEntry);


        return "";
    }


    /**
     * @permissions view
     * @return string
     */
    protected function actionShowAssignedRecords() {
        //load tag
        $objTag = new class_module_tags_tag($this->getSystemid());
        //get assigned record-ids

        $objArraySectionIterator = new class_array_section_iterator($objTag->getIntAssignments());
        $objArraySectionIterator->setPageNumber((int)($this->getParam("pv") != "" ? $this->getParam("pv") : 1));
        $objArraySectionIterator->setArraySection($objTag->getArrAssignedRecords($objArraySectionIterator->calculateStartPos(), $objArraySectionIterator->calculateEndPos()));

        return $this->renderList($objArraySectionIterator, false, "assignedTagList");
    }

    public function getActionIcons($objOneIterable, $strListIdentifier = "") {
        if($strListIdentifier == "assignedTagList") {
            //call the original module to render the action-icons
            $objAdminInstance = class_module_system_module::getModuleByName($objOneIterable->getArrModule("modul"))->getAdminInstanceOfConcreteModule();
            if($objAdminInstance != null && $objAdminInstance instanceof class_admin_simple) {
                return $objAdminInstance->getActionIcons($objOneIterable);
            }
        }

        return parent::getActionIcons($objOneIterable, $strListIdentifier);
    }

    /**
     * Renders the generic tag-form, in case to be embedded from external.
     * Therefore, two params are evaluated:
     *  - the param "systemid"
     *  - the param "attribute"
     *
     * @return string
     * @permissions view
     */
    protected function actionGenericTagForm() {
        $this->setArrModuleEntry("template", "/folderview.tpl");
        return $this->getTagForm($this->getSystemid(), $this->getParam("attribute"));
    }

    /**
     * Generates a form to add tags to the passed systemid.
     * Since all functionality is performed using ajax, there's no page-reload when adding or removing tags.
     * Therefore the form-handling of existing forms can remain as is
     *
     * @param string $strTargetSystemid the systemid to tag
     * @param string $strAttribute additional info used to differ between tag-sets for a single systemid
     *
     * @return string
     * @permissions view
     */
    public function getTagForm($strTargetSystemid, $strAttribute = null) {
        $strTagContent = "";

        $strTagsWrapperId = generateSystemid();

        $strTagContent .= $this->objToolkit->formHeader(
            class_link::getLinkAdminHref($this->getArrModule("modul"), "saveTags"), "", "", "KAJONA.admin.tags.saveTag(document.getElementById('tagname').value+'', '" . $strTargetSystemid . "', '" . $strAttribute . "');return false;"
        );
        $strTagContent .= $this->objToolkit->formTextRow($this->getLang("tag_name_hint"));
        $strTagContent .= $this->objToolkit->formInputTagSelector("tagname", $this->getLang("form_tags_name"));
        $strTagContent .= $this->objToolkit->formInputSubmit($this->getLang("button_add"), $this->getLang("button_add"), "");
        $strTagContent .= $this->objToolkit->formClose();
        $strTagContent .= $this->objToolkit->setBrowserFocus("tagname");

        $strTagContent .= $this->objToolkit->getTaglistWrapper($strTagsWrapperId, $strTargetSystemid, $strAttribute);

        return $strTagContent;
    }

    protected function getOutputNaviEntry(ModelInterface $objInstance) {
        if($objInstance instanceof class_module_tags_tag)
            return class_link::getLinkAdmin($this->getArrModule("modul"), "showAssignedRecords", "&systemid=" . $objInstance->getSystemid(), $objInstance->getStrName());

        return null;
    }


    /**
     * Renders the list of favorites created by the current user
     *
     * @return string
     * @autoTestable
     * @permissions right1
     */
    protected function actionListFavorites() {

        $objArraySectionIterator = new class_array_section_iterator(class_module_tags_favorite::getNumberOfFavoritesForUser($this->objSession->getUserID()));
        $objArraySectionIterator->setPageNumber((int)($this->getParam("pv") != "" ? $this->getParam("pv") : 1));
        $objArraySectionIterator->setArraySection(class_module_tags_favorite::getAllFavoritesForUser($this->objSession->getUserID(), $objArraySectionIterator->calculateStartPos(), $objArraySectionIterator->calculateEndPos()));

        return $this->renderList($objArraySectionIterator);
    }

}
