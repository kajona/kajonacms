<?php
/*"******************************************************************************************************
*   (c) 2004-2006 by MulchProductions, www.mulchprod.de                                                 *
*   (c) 2007-2011 by Kajona, www.kajona.de                                                              *
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

    public function getRequiredFields() {
        if($this->getAction() == "saveTag")
            return array("tag_name" => "string");
        else
            return parent::getRequiredFields();
    }

	protected function getOutputModuleNavi() {
	    $arrReturn = array();
        $arrReturn[] = array("right", getLinkAdmin("right", "change", "&changemodule=".$this->arrModule["modul"],  $this->getText("commons_module_permissions"), "", "", true, "adminnavi"));
        $arrReturn[] = array("", "");
		$arrReturn[] = array("view", getLinkAdmin($this->arrModule["modul"], "list", "", $this->getText("commons_list"), "", "", true, "adminnavi"));

        return $arrReturn;
	}

    protected function actionNew() {
    }

    /**
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

    protected function getNewEntryAction($strListIdentifier) {
        return "";
    }


    /**
     * Generates the form to edit an existing tag
     * @return string
     */
    protected function actionEdit() {
        $strReturn = "";
        $objTag = new class_module_tags_tag($this->getSystemid());
		if($objTag->rightEdit()) {

			$strReturn .= $this->objToolkit->getValidationErrors($this, "saveTag");
			$strReturn .= $this->objToolkit->formHeader(getLinkAdminHref($this->arrModule["modul"], "saveTag"));
			$strReturn .= $this->objToolkit->formInputText("tag_name", $this->getText("tag_name"), ($this->getParam("tag_name") != "" ? $this->getParam("tag_name") : $objTag->getStrName()) );
			$strReturn .= $this->objToolkit->formInputHidden("systemid", $objTag->getSystemid());
			$strReturn .= $this->objToolkit->formInputSubmit($this->getText("commons_save"));
			$strReturn .= $this->objToolkit->formClose();

			$strReturn .= $this->objToolkit->setBrowserFocus("tag_name");
		}
		else
			$strReturn = $this->getText("commons_error_permissions");

		return $strReturn;
    }


    /**
     * Saves the passed tag-data back to the database.
     * @return string "" in case of success
     */
    protected function actionSaveTag() {

        if(!$this->validateForm())
            return $this->actionEdit();

        $strReturn = "";
        $objTag = new class_module_tags_tag($this->getSystemid());
		if($objTag->rightEdit()) {
			//Collect data to save to db
			$objTag->setStrName($this->getParam("tag_name"), true);
            $objTag->updateObjectToDb();
            $this->adminReload(getLinkAdminHref($this->arrModule["modul"]));
		}
		else
			$strReturn = $this->getText("commons_error_permissions");

		return $strReturn;
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

        $strTagContent .= $this->objToolkit->formHeader(getLinkAdminHref($this->arrModule["modul"], "saveTags"), "", "", "KAJONA.admin.tags.saveTag(document.getElementById('tagname').value+'', '".$strTargetSystemid."', '".$strAttribute."');return false;");
        $strTagContent .= $this->objToolkit->formTextRow($this->getText("tag_name_hint"));
        $strTagContent .= $this->objToolkit->formInputTagSelector("tagname", $this->getText("tag_name"));
        $strTagContent .= $this->objToolkit->formInputSubmit($this->getText("button_add"), $this->getText("button_add"), "");
        $strTagContent .= $this->objToolkit->formClose();

        $strTagContent .= $this->objToolkit->getTaglistWrapper($strTagsWrapperId, $strTargetSystemid, $strAttribute);

        $strReturn .= $this->objToolkit->divider();
        $strReturn .= $this->objToolkit->getFieldset($this->getText("tagsection_header"), $strTagContent);

        return $strReturn;
    }


}
