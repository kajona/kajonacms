<?php
/*"******************************************************************************************************
*   (c) 2004-2006 by MulchProductions, www.mulchprod.de                                                 *
*   (c) 2007-2015 by Kajona, www.kajona.de                                                              *
*       Published under the GNU LGPL v2.1, see /system/licence_lgpl.txt                                 *
*-------------------------------------------------------------------------------------------------------*
*	$Id$                          *
********************************************************************************************************/


/**
 * Admin class of the postacomment-module. Responsible for listing posts and organizing them
 *
 * @package module_postacomment
 * @author sidler@mulchprod.de
 *
 * @objectList class_module_postacomment_post
 * @objectEdit class_module_postacomment_post
 *
 * @module postacomment
 * @moduleId _postacomment_modul_id_
 */
class class_module_postacomment_admin extends class_admin_evensimpler implements interface_admin {

    public function getOutputModuleNavi() {
        $arrReturn = array();
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
        foreach(class_module_pages_page::getAllPages() as $objOnePage) {
            $arrPages[$objOnePage->getSystemid()] = $objOnePage->getStrName();
        }

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




}

