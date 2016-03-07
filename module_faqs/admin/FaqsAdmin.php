<?php
/*"******************************************************************************************************
*   (c) 2004-2006 by MulchProductions, www.mulchprod.de                                                 *
*   (c) 2007-2015 by Kajona, www.kajona.de                                                              *
*       Published under the GNU LGPL v2.1, see /system/licence_lgpl.txt                                 *
********************************************************************************************************/

namespace Kajona\Faqs\Admin;

use Kajona\Faqs\System\FaqsCategory;
use Kajona\Faqs\System\FaqsFaq;
use Kajona\System\Admin\AdminEvensimpler;
use Kajona\System\Admin\AdminInterface;
use Kajona\System\System\ArraySectionIterator;
use Kajona\System\System\Link;
use Kajona\System\System\ModelInterface;


/**
 * Admin class of the faqs-module. Responsible for editing faqs and organizing them in categories
 *
 * @package module_faqs
 * @author sidler@mulchprod.de
 *
 *
 * @objectListFaq Kajona\Faqs\System\FaqsFaq
 * @objectEditFaq Kajona\Faqs\System\FaqsFaq
 * @objectNewFaq Kajona\Faqs\System\FaqsFaq
 *
 * @objectListCat Kajona\Faqs\System\FaqsCategory
 * @objectEditCat Kajona\Faqs\System\FaqsCategory
 * @objectNewCat Kajona\Faqs\System\FaqsCategory
 *
 * @autoTestable listFaq,newFaq,listCat,newCat
 *
 * @module faqs
 * @moduleId _faqs_module_id_
 */
class FaqsAdmin extends AdminEvensimpler implements AdminInterface
{

    const STR_CAT_LIST = "STR_CAT_LIST";
    const STR_FAQ_LIST = "STR_FAQ_LIST";

    public function getOutputModuleNavi()
    {
        return array(
            array("view", Link::getLinkAdmin($this->getArrModule("modul"), "list", "", $this->getLang("commons_list"), "", "", true, "adminnavi"))
        );
    }


    protected function renderDeleteAction(ModelInterface $objListEntry)
    {
        if ($objListEntry instanceof FaqsCategory && $objListEntry->rightDelete()) {
            return $this->objToolkit->listDeleteButton(
                $objListEntry->getStrDisplayName(), $this->getLang("commons_delete_category_question"), Link::getLinkAdminHref($objListEntry->getArrModule("modul"), "delete", "&systemid=" . $objListEntry->getSystemid())
            );
        }
        return parent::renderDeleteAction($objListEntry);
    }

    protected function renderAdditionalActions(\Kajona\System\System\Model $objListEntry)
    {
        if ($objListEntry instanceof FaqsCategory) {
            return array(
                $this->objToolkit->listButton(Link::getLinkAdmin($this->getArrModule("modul"), "list", "&filterId=" . $objListEntry->getSystemid(), "", $this->getLang("kat_anzeigen"), "icon_lens"))
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
    protected function actionList()
    {

        $this->setStrCurObjectTypeName("Cat");
        $this->setCurObjectClassName("Kajona\\Faqs\\System\\FaqsCategory");
        $objIterator = new ArraySectionIterator(FaqsCategory::getObjectCount());
        $objIterator->setIntElementsPerPage(FaqsCategory::getObjectCount());
        $objIterator->setPageNumber(1);
        $objIterator->setArraySection(FaqsCategory::getObjectList("", $objIterator->calculateStartPos(), $objIterator->calculateEndPos()));

        $strReturn = $this->renderList($objIterator, false, FaqsAdmin::STR_CAT_LIST);

        $this->setStrCurObjectTypeName("Faq");
        $this->setCurObjectClassName("Kajona\\Faqs\\System\\FaqsFaq");
        $objIterator = new ArraySectionIterator(FaqsFaq::getObjectCount($this->getParam("filterId")));
        $objIterator->setPageNumber($this->getParam("pv"));
        $objIterator->setArraySection(FaqsFaq::getObjectList($this->getParam("filterId"), $objIterator->calculateStartPos(), $objIterator->calculateEndPos()));

        $strReturn .= $this->renderList($objIterator, false, FaqsAdmin::STR_FAQ_LIST);

        return $strReturn;

    }

    protected function getBatchActionHandlers($strListIdentifier)
    {
        if ($strListIdentifier == FaqsAdmin::STR_FAQ_LIST) {
            return $this->getDefaultActionHandlers();
        }
        return parent::getBatchActionHandlers($strListIdentifier);
    }


    protected function getOutputNaviEntry(ModelInterface $objInstance)
    {
        return Link::getLinkAdmin($this->getArrModule("modul"), "edit", "&systemid=" . $objInstance->getSystemid(), $objInstance->getStrDisplayName());
    }

}

