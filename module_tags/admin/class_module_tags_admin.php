<?php
/*"******************************************************************************************************
*   (c) 2004-2006 by MulchProductions, www.mulchprod.de                                                 *
*   (c) 2007-2012 by Kajona, www.kajona.de                                                              *
*       Published under the GNU LGPL v2.1, see /system/licence_lgpl.txt                                 *
*-------------------------------------------------------------------------------------------------------*
*	$Id$                                  *
********************************************************************************************************/

/**
 * Admin-Part of the tags.
 * No classical functionality, rather a list of helper-methods, e.g. in order to
 * create the form to tag content.
 *
 * @package module_tags
 * @author sidler@mulchprod.de
 */
class class_module_tags_admin extends class_admin_simple implements interface_admin {

	/**
	 * Constructor
	 */
	public function __construct() {
        $this->setArrModuleEntry("modul", "tags");
        $this->setArrModuleEntry("moduleId", _tags_modul_id_);
		parent::__construct();

	}

    public function getOutputModuleNavi() {
	    $arrReturn = array();
        $arrReturn[] = array("right", getLinkAdmin("right", "change", "&changemodule=".$this->arrModule["modul"],  $this->getLang("commons_module_permissions"), "", "", true, "adminnavi"));
        $arrReturn[] = array("", "");
		$arrReturn[] = array("view", getLinkAdmin($this->arrModule["modul"], "list", "", $this->getLang("commons_list"), "", "", true, "adminnavi"));
		$arrReturn[] = array("view", getLinkAdmin($this->arrModule["modul"], "listFavorites", "", $this->getLang("actionListFavorites"), "", "", true, "adminnavi"));

        return $arrReturn;
	}

    protected function actionNew() {
    }

    /**
     * Renders the list of tags available
     * @return string
     * @autoTestable
     * @permissions view
     */
    protected function actionList() {

        $objArraySectionIterator = new class_array_section_iterator(class_module_tags_tag::getNumberOfTags());
        $objArraySectionIterator->setPageNumber((int)($this->getParam("pv") != "" ? $this->getParam("pv") : 1));
        $objArraySectionIterator->setArraySection(class_module_tags_tag::getAllTags($objArraySectionIterator->calculateStartPos(), $objArraySectionIterator->calculateEndPos()));

        return $this->renderList($objArraySectionIterator);
    }

    protected function getNewEntryAction($strListIdentifier, $bitDialog = false) {
        return "";
    }

    protected function renderAdditionalActions(class_model $objListEntry) {
        if($objListEntry instanceof class_module_tags_tag) {
            return array(
                $this->objToolkit->listButton(getLinkAdmin($this->getArrModule("modul"), "showAssignedRecords", "&systemid=".$objListEntry->getSystemid(), $this->getLang("actionShowAssignedRecords"), $this->getLang("actionShowAssignedRecords"), "icon_folderActionOpen.gif")),
                $this->objToolkit->listButton(getLinkAdmin($this->getArrModule("modul"), "addToFavorites", "&systemid=".$objListEntry->getSystemid(), $this->getLang("actionAddToFavorites"), $this->getLang("actionAddToFavorites"), "icon_favorite.gif"))
            );
        }
        else
            return array();
    }

    protected function renderEditAction(class_model $objListEntry, $bitDialog = false) {
        if($objListEntry instanceof class_module_tags_tag)
            return parent::renderEditAction($objListEntry);
        else if($objListEntry->rightEdit())
            return $this->objToolkit->listButton(getLinkAdmin($objListEntry->getArrModule("modul"), "edit", "&systemid=".$objListEntry->getSystemid(), $this->getLang("commons_list_edit"), $this->getLang("commons_list_edit"), "icon_pencil.gif"));
    }

    protected function renderDeleteAction(interface_model $objListEntry) {
        if($objListEntry instanceof class_module_tags_tag) {
            if($objListEntry->rightDelete()) {
                return $this->objToolkit->listDeleteButton($objListEntry->getStrDisplayName(), $this->getLang("delete_question_fav", $objListEntry->getArrModule("modul")), getLinkAdminHref($objListEntry->getArrModule("modul"), "delete", "&systemid=".$objListEntry->getSystemid()));
            }
        }
        else if($objListEntry instanceof class_module_tags_favorite)
            return parent::renderDeleteAction($objListEntry);
    }


    /**
     * Generates the form to edit an existing tag
     * @param \class_admin_formgenerator|null $objForm
     * @return string
     * @permissions edit
     */
    protected function actionEdit(class_admin_formgenerator $objForm = null) {
        $objTag = new class_module_tags_tag($this->getSystemid());
		if($objTag->rightEdit()) {

            if($objForm == null)
                $objForm = $this->getAdminForm($objTag);

            return $objForm->renderForm(getLinkAdminHref($this->arrModule["modul"], "saveTag"));
		}
		else
			return $this->getLang("commons_error_permissions");

    }

    private function getAdminForm(class_module_tags_tag $objTag) {
        $objForm = new class_admin_formgenerator("tag", $objTag);
        $objForm->generateFieldsFromObject();

        return $objForm;
    }


    /**
     * Saves the passed tag-data back to the database.
     * @return string "" in case of success
     * @permissions edit
     */
    protected function actionSaveTag() {

        $objTag = new class_module_tags_tag($this->getSystemid());
        $objForm = $this->getAdminForm($objTag);

        if(!$objForm->validateForm())
            return $this->actionEdit($objForm);

		if($objTag->rightEdit()) {
            $objForm->updateSourceObject();
            $objTag->updateObjectToDb();
            $this->adminReload(getLinkAdminHref($this->arrModule["modul"]));
            return "";
		}
		else
			return $this->getLang("commons_error_permissions");
    }

    /**
     * @permissions edit
     * @return string
     */
    protected function actionShowAssignedRecords() {
        //load tag
        $objTag = new class_module_tags_tag($this->getSystemid());
        //get assigned record-ids

        $objArraySectionIterator = new class_array_section_iterator($objTag->getIntAssignments());
        $objArraySectionIterator->setPageNumber((int)($this->getParam("pv") != "" ? $this->getParam("pv") : 1));
        $objArraySectionIterator->setArraySection($objTag->getArrAssignedRecords($objArraySectionIterator->calculateStartPos(), $objArraySectionIterator->calculateEndPos()));

        return $this->renderList($objArraySectionIterator);
    }

    /**
     * Renders the generic tag-form, in case to be embedded from external.
     * Therefore, two params are evaluated:
     *  - the param "systemid"
     *  - the param "attribute"
     * @return string
     * @permissions edit
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
     * @return string
     * @permissions edit
     */
    public function getTagForm($strTargetSystemid, $strAttribute = null) {
        $strReturn = "";
        $strTagContent = "";

        $strTagsWrapperId = generateSystemid();

        $objTarget = class_objectfactory::getInstance()->getObject($strTargetSystemid);

        $strTagContent .= $this->objToolkit->formHeader(getLinkAdminHref($this->arrModule["modul"], "saveTags"), "", "", "KAJONA.admin.tags.saveTag(document.getElementById('tagname').value+'', '".$strTargetSystemid."', '".$strAttribute."');return false;");
        $strTagContent .= $this->objToolkit->formTextRow($this->getLang("tag_name_hint"));
        $strTagContent .= $this->objToolkit->formInputTagSelector("tagname", $this->getLang("form_tag_name"));
        $strTagContent .= $this->objToolkit->formInputSubmit($this->getLang("button_add"), $this->getLang("button_add"), "");
        $strTagContent .= $this->objToolkit->formClose();

        $strTagContent .= $this->objToolkit->getTaglistWrapper($strTagsWrapperId, $strTargetSystemid, $strAttribute);

        $strReturn .= $this->objToolkit->divider();
        $strReturn .= $this->objToolkit->getFieldset($this->getLang("tagsection_header")." ".$objTarget->getStrDisplayName(), $strTagContent);

        return $strReturn;
    }

    protected function getArrOutputNaviEntries() {
        $arrEntries =  parent::getArrOutputNaviEntries();

        if($this->getAction() == "showAssignedRecords") {
            $objListEntry = new class_module_tags_tag($this->getSystemid());
            $arrEntries[] = getLinkAdmin($this->getArrModule("modul"), "showAssignedRecords", "&systemid=".$objListEntry->getSystemid(), $objListEntry->getStrName());
        }

        return $arrEntries;
    }



    /**
     * Renders the list of favorites created by the current user
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

    /**
     * Adds a single tag to a users list of favorites
     * @permissons right1
     */
    protected function actionAddToFavorites() {
        if(count(class_module_tags_favorite::getAllFavoritesForUserAndTag($this->objSession->getUserID(), $this->getSystemid())) == 0) {
            $objFavorite = new class_module_tags_favorite();
            $objFavorite->setStrUserId($this->objSession->getUserID());
            $objFavorite->setStrTagId($this->getSystemid());

            $objFavorite->updateObjectToDb();
        }

        $this->adminReload(getLinkAdminHref($this->getArrModule("modul"), "listFavorites"));
    }
}
