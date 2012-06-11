<?php
/*"******************************************************************************************************
*   (c) 2004-2006 by MulchProductions, www.mulchprod.de                                                 *
*   (c) 2007-2012 by Kajona, www.kajona.de                                                              *
*       Published under the GNU LGPL v2.1, see /system/licence_lgpl.txt                                 *
*-------------------------------------------------------------------------------------------------------*
*	$Id: class_modul_postacomment_admin.php 3962 2011-07-03 12:10:54Z sidler $                          *
********************************************************************************************************/


/**
 * Admin class of the postacomment-module. Responsible for listing posts and organizing them
 *
 * @package module_postacomment
 * @author sidler@mulchprod.de
 */
class class_module_postacomment_admin extends class_admin_simple implements interface_admin {

	/**
	 * Constructor
	 *
	 */
	public function __construct() {
        $this->setArrModuleEntry("modul", "postacomment");
        $this->setArrModuleEntry("moduleId", _postacomment_modul_id_);
        parent::__construct();
	}

    public function getOutputModuleNavi() {
	    $arrReturn = array();
        $arrReturn[] = array("right", getLinkAdmin("right", "change", "&changemodule=".$this->arrModule["modul"],  $this->getLang("commons_module_permissions"), "", "", true, "adminnavi"));
        $arrReturn[] = array("", "");
    	$arrReturn[] = array("view", getLinkAdmin($this->arrModule["modul"], "list", "", $this->getLang("commons_list"), "", "", true, "adminnavi"));
		return $arrReturn;
	}





	/**
	 * Returns a list of all categories and all postacomment
	 * The list could be filtered by categories
	 *
	 * @return string
     * @permissions view
     * @autoTestable
	 */
	protected function actionList() {

        //a small filter would be nice...
        $strReturn = $this->objToolkit->formHeader(getLinkAdminHref($this->arrModule["modul"], "list"));

        $arrPages = array();
        $arrPages[""] = "---";
        foreach(class_module_pages_page::getAllPages() as $objOnePage)
            $arrPages[$objOnePage->getSystemid()] = $objOnePage->getStrName();

        $strReturn .= $this->objToolkit->formInputDropdown("filterId", $arrPages, $this->getLang("postacomment_filter"), $this->getParam("filterId"));
        $strReturn .= $this->objToolkit->formInputSubmit($this->getLang("postacomment_dofilter"));
        $strReturn .= $this->objToolkit->formClose();

        $strReturn .= $this->objToolkit->divider();

        $objArraySectionIterator = new class_array_section_iterator(class_module_postacomment_post::getNumberOfPostsAvailable(false, $this->getParam("filterId")));
        $objArraySectionIterator->setPageNumber((int)($this->getParam("pv") != "" ? $this->getParam("pv") : 1));
        $objArraySectionIterator->setArraySection(class_module_postacomment_post::loadPostList(false, $this->getParam("filterId"), false, "", $objArraySectionIterator->calculateStartPos(), $objArraySectionIterator->calculateEndPos()));

        $strReturn .= $this->renderList($objArraySectionIterator);
        return $strReturn;

	}

    protected function getNewEntryAction($strListIdentifier, $bitDialog = false) {
        return "";
    }


    /**
     * Renders the form to create a new entry
     *
     * @throws class_exception
     * @return string
     * @permissions edit
     */
    protected function actionNew() {
        throw new class_exception("actioNew not supported by module postacomment", class_exception::$level_ERROR);
    }

    /**
     * Renders the form to edit an existing entry
     *
     * @param class_admin_formgenerator|null $objForm
     *
     * @return string
     * @permissions edit
     */
    protected function actionEdit(class_admin_formgenerator $objForm = null) {
        $objComment = new class_module_postacomment_post($this->getSystemid());
        if($objComment->rightEdit()) {

            if($objForm == null)
                $objForm = $this->getAdminForm($objComment);

            return $objForm->renderForm(getLinkAdminHref($this->arrModule["modul"], "saveComment"));
        }
        else
            return $this->getLang("commons_error_permissions");
    }

    private function getAdminForm(class_module_postacomment_post $objComment) {
        $objForm = new class_admin_formgenerator("comment", $objComment);
        $objForm->generateFieldsFromObject();

        $objForm->getField("title")->setStrLabel($this->getLang("form_comment_title"));
        $objForm->getField("comment")->setStrLabel($this->getLang("postacomment_comment"));
        return $objForm;
    }

    /**
     * Saves the passed comment-data back to the database.
     * @return string "" in case of success
     * @permissions edit
     */
    protected function actionSaveComment() {

        $objComment = new class_module_postacomment_post($this->getSystemid());
        $objForm = $this->getAdminForm($objComment);

        if(!$objForm->validateForm())
            return $this->actionEdit($objForm);

        if($objComment->rightEdit()) {
            $objForm->updateSourceObject();
            $objComment->updateObjectToDb();
            $this->adminReload(getLinkAdminHref($this->arrModule["modul"]));
            return "";
        }
        else
            return $this->getLang("commons_error_permissions");
    }

}

