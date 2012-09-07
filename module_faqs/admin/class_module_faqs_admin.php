<?php
/*"******************************************************************************************************
*   (c) 2004-2006 by MulchProductions, www.mulchprod.de                                                 *
*   (c) 2007-2012 by Kajona, www.kajona.de                                                              *
*       Published under the GNU LGPL v2.1, see /system/licence_lgpl.txt                                 *
*-------------------------------------------------------------------------------------------------------*
*	$Id: class_modul_faqs_admin.php 3961 2011-07-02 20:04:05Z sidler $                              *
********************************************************************************************************/


/**
 * Admin class of the faqs-module. Responsible for editing faqs and organizing them in categories
 *
 * @package module_faqs
 * @author sidler@mulchprod.de
 */
class class_module_faqs_admin extends class_admin_simple implements interface_admin {

    const STR_CAT_LIST = "STR_CAT_LIST";
    const STR_FAQ_LIST = "STR_FAQ_LIST";

	/**
	 * Constructor
	 *
	 */
	public function __construct() {
        $this->setArrModuleEntry("moduleId", _faqs_module_id_);
        $this->setArrModuleEntry("modul", "faqs");
        parent::__construct();
	}


    public function getOutputModuleNavi() {
	    $arrReturn = array();
        $arrReturn[] = array("view", getLinkAdmin($this->arrModule["modul"], "list", "", $this->getLang("commons_list"), "", "", true, "adminnavi"));
        $arrReturn[] = array("", "");
        $arrReturn[] = array("edit", getLinkAdmin($this->arrModule["modul"], "newFaq", "", $this->getLang("modul_anlegen"), "", "", true, "adminnavi"));
        $arrReturn[] = array("edit", getLinkAdmin($this->arrModule["modul"], "newCat", "", $this->getLang("commons_create_category"), "", "", true, "adminnavi"));
        $arrReturn[] = array("", "");
        $arrReturn[] = array("right", getLinkAdmin("right", "change", "&changemodule=".$this->arrModule["modul"],  $this->getLang("commons_module_permissions"), "", "", true, "adminnavi"));
        return $arrReturn;
	}

    /**
     * Renders the form to create a new entry
     *
     * @return string
     * @permissions edit
     */
    protected function actionNew() {
        return $this->actionNewFaq();
    }

    /**
     * Renders the form to edit an existing entry
     *
     * @return string
     * @permissions edit
     */
    protected function actionEdit() {
        $objObject = class_objectfactory::getInstance()->getObject($this->getSystemid());
        if($objObject instanceof class_module_faqs_category && $objObject->rightEdit())
            $this->adminReload(getLinkAdminHref($this->getArrModule("modul"), "editCat", "&systemid=".$objObject->getSystemid()));

        if($objObject instanceof class_module_faqs_faq && $objObject->rightEdit())
            $this->adminReload(getLinkAdminHref($this->getArrModule("modul"), "editFaq", "&systemid=".$objObject->getSystemid()));

        return "";
    }

    protected function getNewEntryAction($strListIdentifier, $bitDialog = false) {
        if($strListIdentifier == class_module_faqs_admin::STR_CAT_LIST)
            return $this->objToolkit->listButton(getLinkAdmin($this->getArrModule("modul"), "newCat", "", $this->getLang("commons_create_category"), $this->getLang("commons_create_category"), "icon_new.png"));


        return parent::getNewEntryAction($strListIdentifier, $bitDialog);
    }

    protected function renderDeleteAction(interface_model $objListEntry) {

        if($objListEntry instanceof class_module_faqs_category && $objListEntry->rightDelete()) {
            return $this->objToolkit->listDeleteButton($objListEntry->getStrDisplayName(), $this->getLang("commons_delete_category_question"), getLinkAdminHref($objListEntry->getArrModule("modul"), "delete", "&systemid=".$objListEntry->getSystemid()));
        }

        return parent::renderDeleteAction($objListEntry);
    }

    protected function renderAdditionalActions(class_model $objListEntry) {
        if($objListEntry instanceof class_module_faqs_category)
            return array(
                $this->objToolkit->listButton(getLinkAdmin($this->arrModule["modul"], "list", "&filterId=".$objListEntry->getSystemid(), "", $this->getLang("kat_anzeigen"), "icon_lens.png"))
            );


        return array();
    }


    /**
	 * Returns a list of all categories and all faqs
	 * The list can be filtered by categories
	 *
	 * @return string
     * @autoTestable
     * @permissions view
	 */
	protected function actionList() {

        $objIterator = new class_array_section_iterator(class_module_faqs_category::getCategoriesCount());
        $objIterator->setIntElementsPerPage(class_module_faqs_category::getCategoriesCount());
        $objIterator->setPageNumber(1);
        $objIterator->setArraySection(class_module_faqs_category::getCategories($objIterator->calculateStartPos(), $objIterator->calculateEndPos()));

        $strReturn = $this->renderList($objIterator, false, class_module_faqs_admin::STR_CAT_LIST);


        $strReturn .= $this->objToolkit->divider();

        $objIterator = new class_array_section_iterator(class_module_faqs_faq::getFaqsCount($this->getParam("filterId")));
        $objIterator->setPageNumber($this->getParam("pv"));
        $objIterator->setArraySection(class_module_faqs_faq::getFaqsList($this->getParam("filterId"), $objIterator->calculateStartPos(), $objIterator->calculateEndPos()));

        $strReturn .= $this->renderList($objIterator, false, class_module_faqs_admin::STR_FAQ_LIST);

        return $strReturn;

	}


    
    protected function actionEditCat() {
        return $this->actionNewCat("edit");
    }

    /**
     * Show the form to create or edit a faqs cat
     *
     * @param string $strMode
     * @param class_admin_formgenerator $objForm
     *
     * @return string
     * @permissions edit
     * @autoTestable
     */
	protected function actionNewCat($strMode = "new", class_admin_formgenerator $objForm = null) {

        $objCategory = new class_module_faqs_category();
        if($strMode == "edit") {
            $objCategory = new class_module_faqs_category($this->getSystemid());

            if(!$objCategory->rightEdit())
                return $this->getLang("commons_error_permissions");
        }

        if($objForm == null)
            $objForm = $this->getCatAdminForm($objCategory);

        $objForm->addField(new class_formentry_hidden("", "mode"))->setStrValue($strMode);
        return $objForm->renderForm(getLinkAdminHref($this->getArrModule("modul"), "saveCat"));
	}


    private function getCatAdminForm(class_module_faqs_category $objCat) {
        $objForm = new class_admin_formgenerator("cat", $objCat);
        $objForm->generateFieldsFromObject();
        return $objForm;
    }

	/**
	 * Saves the passed values as a new category to the db
	 *
	 * @return string "" in case of success
     * @permissions edit
	 */
	protected function actionSaveCat() {
        $objCat = null;

        if($this->getParam("mode") == "new")
            $objCat = new class_module_faqs_category();

        else if($this->getParam("mode") == "edit")
            $objCat = new class_module_faqs_category($this->getSystemid());

        if($objCat != null) {

            $objForm = $this->getCatAdminForm($objCat);
            if(!$objForm->validateForm())
                return $this->actionNewCat($this->getParam("mode"), $objForm);

            $objForm->updateSourceObject();
            $objCat->updateObjectToDb();

            $this->adminReload(getLinkAdminHref($this->arrModule["modul"], "", ($this->getParam("pe") != "" ? "&peClose=1" : "")));
            return "";
        }

        return $this->getLang("commons_error_permissions");
	}




    protected function actionEditFaq() {
        return $this->actionNewFaq("edit");
    }


    protected function actionNewFaq($strMode = "new", class_admin_formgenerator $objForm = null) {

        $objFaq = new class_module_faqs_faq();
        if($strMode == "edit") {
            $objFaq = new class_module_faqs_faq($this->getSystemid());

            if(!$objFaq->rightEdit())
                return $this->getLang("commons_error_permissions");
        }

        if($objForm == null)
            $objForm = $this->getFaqAdminForm($objFaq);

        $objForm->addField(new class_formentry_hidden("", "mode"))->setStrValue($strMode);
        return $objForm->renderForm(getLinkAdminHref($this->getArrModule("modul"), "saveFaq"));
    }


    private function getFaqAdminForm(class_module_faqs_faq $objFaq) {
        $objForm = new class_admin_formgenerator("faq", $objFaq);
        $objForm->generateFieldsFromObject();

        $arrCats = class_module_faqs_category::getCategories();
        if (count($arrCats) > 0)
            $objForm->addField(new class_formentry_headline())->setStrValue($this->getLang("commons_categories"));

        $arrFaqsMember = class_module_faqs_category::getFaqsMember($this->getSystemid());

        foreach ($arrCats as $objOneCat) {
            $bitChecked = false;
            foreach ($arrFaqsMember as $objOneMember)
                if($objOneMember->getSystemid() == $objOneCat->getSystemid())
                    $bitChecked = true;

            $objForm->addField(new class_formentry_checkbox("faq", "cat[".$objOneCat->getSystemid()."]"))->setStrLabel($objOneCat->getStrTitle())->setStrValue($bitChecked);

        }


        return $objForm;
    }

    /**
     * Saves the passed values as a new category to the db
     *
     * @return string "" in case of success
     * @permissions edit
     */
    protected function actionSaveFaq() {
        $objFaq = null;

        if($this->getParam("mode") == "new")
            $objFaq = new class_module_faqs_faq();

        else if($this->getParam("mode") == "edit")
            $objFaq = new class_module_faqs_faq($this->getSystemid());

        if($objFaq != null) {

            $objForm = $this->getFaqAdminForm($objFaq);
            if(!$objForm->validateForm())
                return $this->actionNewFaq($this->getParam("mode"), $objForm);

            $objForm->updateSourceObject();

            $arrParams = $this->getAllParams();
            $arrCats = array();
            if(isset($arrParams["faq_cat"])) {
                foreach($arrParams["faq_cat"] as $strCatID => $strValue) {
                    $arrCats[$strCatID] = $strValue;
                }
            }
            $objFaq->setArrCats($arrCats);

            $objFaq->setUpdateBitMemberships(true);
            $objFaq->updateObjectToDb();

            $this->adminReload(getLinkAdminHref($this->arrModule["modul"], "", ($this->getParam("pe") != "" ? "&peClose=1" : "")));
            return "";
        }

        return $this->getLang("commons_error_permissions");
    }



}

