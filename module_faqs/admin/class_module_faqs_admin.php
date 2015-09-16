<?php
/*"******************************************************************************************************
*   (c) 2004-2006 by MulchProductions, www.mulchprod.de                                                 *
*   (c) 2007-2015 by Kajona, www.kajona.de                                                              *
*       Published under the GNU LGPL v2.1, see /system/licence_lgpl.txt                                 *
********************************************************************************************************/


/**
 * Admin class of the faqs-module. Responsible for editing faqs and organizing them in categories
 *
 * @package module_faqs
 * @author sidler@mulchprod.de
 *
 *
 * @objectListFaq class_module_faqs_faq
 * @objectEditFaq class_module_faqs_faq
 * @objectNewFaq class_module_faqs_faq
 *
 * @objectListCat class_module_faqs_category
 * @objectEditCat class_module_faqs_category
 * @objectNewCat class_module_faqs_category
 *
 * @autoTestable listFaq,newFaq,listCat,newCat
 *
 * @module faqs
 * @moduleId _faqs_module_id_
 */
class class_module_faqs_admin extends class_admin_evensimpler implements interface_admin {

    const STR_CAT_LIST = "STR_CAT_LIST";
    const STR_FAQ_LIST = "STR_FAQ_LIST";

    public function getOutputModuleNavi() {
        return array(
            array("view", class_link::getLinkAdmin($this->getArrModule("modul"), "list", "", $this->getLang("commons_list"), "", "", true, "adminnavi"))
        );
    }


    protected function renderDeleteAction(interface_model $objListEntry) {
        if($objListEntry instanceof class_module_faqs_category && $objListEntry->rightDelete()) {
            return $this->objToolkit->listDeleteButton(
                $objListEntry->getStrDisplayName(), $this->getLang("commons_delete_category_question"), class_link::getLinkAdminHref($objListEntry->getArrModule("modul"), "delete", "&systemid=" . $objListEntry->getSystemid())
            );
        }
        return parent::renderDeleteAction($objListEntry);
    }

    protected function renderAdditionalActions(class_model $objListEntry) {
        if($objListEntry instanceof class_module_faqs_category) {
            return array(
                $this->objToolkit->listButton(class_link::getLinkAdmin($this->getArrModule("modul"), "list", "&filterId=" . $objListEntry->getSystemid(), "", $this->getLang("kat_anzeigen"), "icon_lens"))
            );
        }
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

        $this->setStrCurObjectTypeName("Cat");
        $this->setCurObjectClassName("class_module_faqs_cat");
        $objIterator = new class_array_section_iterator(class_module_faqs_category::getObjectCount());
        $objIterator->setIntElementsPerPage(class_module_faqs_category::getObjectCount());
        $objIterator->setPageNumber(1);
        $objIterator->setArraySection(class_module_faqs_category::getObjectList("", $objIterator->calculateStartPos(), $objIterator->calculateEndPos()));

        $strReturn = $this->renderList($objIterator, false, class_module_faqs_admin::STR_CAT_LIST);

        $this->setStrCurObjectTypeName("Faq");
        $this->setCurObjectClassName("class_module_faqs_faq");
        $objIterator = new class_array_section_iterator(class_module_faqs_faq::getObjectCount($this->getParam("filterId")));
        $objIterator->setPageNumber($this->getParam("pv"));
        $objIterator->setArraySection(class_module_faqs_faq::getObjectList($this->getParam("filterId"), $objIterator->calculateStartPos(), $objIterator->calculateEndPos()));

        $strReturn .= $this->renderList($objIterator, false, class_module_faqs_admin::STR_FAQ_LIST);

        return $strReturn;

    }

    protected function getBatchActionHandlers($strListIdentifier) {
        if($strListIdentifier == class_module_faqs_admin::STR_FAQ_LIST) {
            return $this->getDefaultActionHandlers();
        }
        return parent::getBatchActionHandlers($strListIdentifier);
    }




    protected function getOutputNaviEntry(interface_model $objInstance) {
        return class_link::getLinkAdmin($this->getArrModule("modul"), "edit", "&systemid=".$objInstance->getSystemid(), $objInstance->getStrDisplayName());
    }

}

