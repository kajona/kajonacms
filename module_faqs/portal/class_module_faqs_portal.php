<?php
/*"******************************************************************************************************
*   (c) 2004-2006 by MulchProductions, www.mulchprod.de                                                 *
*   (c) 2007-2015 by Kajona, www.kajona.de                                                              *
*       Published under the GNU LGPL v2.1, see /system/licence_lgpl.txt                                 *
*-------------------------------------------------------------------------------------------------------*
*	$Id$									*
********************************************************************************************************/

use Kajona\Pages\Portal\PagesPortaleditor;
use Kajona\Pages\System\PagesPortaleditorActionEnum;
use Kajona\Pages\System\PagesPortaleditorSystemidAction;

/**
 * Portal-class of the faqs. Handles the printing of faqs lists / detail
 *
 * @package module_faqs
 * @author sidler@mulchprod.de
 * @module faqs
 * @moduleId _faqs_module_id_
 */
class class_module_faqs_portal extends class_portal_controller implements interface_portal {


    /**
     * Returns a list of faqs.
     * The category is choosen from the element-data
     *
     * @return string
     */
    protected function actionList() {
        $strReturn = "";

        //load categories
        $arrCategories = array();
        if($this->arrElementData["faqs_category"] == "0") {
            $arrCategories = class_module_faqs_category::getObjectList();
        }
        else {
            $arrCategories[] = new class_module_faqs_category($this->arrElementData["faqs_category"]);
        }

        //if no cat was created by now, use a dummy cat
        if(count($arrCategories) == 0) {
            $arrCategories[] = 1;
        }

        //load every category
        $strCats = "";
        foreach($arrCategories as $objCategory) {

            //Load faqs
            if(!is_object($objCategory) && $objCategory == 1) {
                $arrFaqs = class_module_faqs_faq::loadListFaqsPortal(1);
                $objCategory = new class_module_faqs_category();
            }
            else {
                if($objCategory->getIntRecordStatus() == 0) {
                    continue;
                }

                $arrFaqs = class_module_faqs_faq::loadListFaqsPortal($objCategory->getSystemid());
            }

            $strFaqs = "";
            //Check rights
            foreach($arrFaqs as $objOneFaq) {
                if($objOneFaq->rightView()) {

                    $objMapper = new class_template_mapper($objOneFaq);
                    //legacy support
                    $objMapper->addPlaceholder("faq_question", $objOneFaq->getStrQuestion());
                    $objMapper->addPlaceholder("faq_answer", $objOneFaq->getStrAnswer());
                    $objMapper->addPlaceholder("faq_systemid", $objOneFaq->getSystemid());

                    //ratings available?
                    if($objOneFaq->getFloatRating() !== null && class_module_system_module::getModuleByName("rating") != null) {
                        /** @var $objRating class_module_rating_portal */
                        $objRating = class_module_system_module::getModuleByName("rating")->getPortalInstanceOfConcreteModule();
                        $objMapper->addPlaceholder("faq_rating", $objRating->buildRatingBar($objOneFaq->getFloatRating(), $objOneFaq->getIntRatingHits(), $objOneFaq->getSystemid(), $objOneFaq->isRateableByUser(), $objOneFaq->rightRight1()));
                    }

                    $strOneFaq = $objMapper->writeToTemplate("/module_faqs/".$this->arrElementData["faqs_template"], "faq_faq", false);

                    $strFaqs .= PagesPortaleditor::addPortaleditorContentWrapper($strOneFaq, $objOneFaq->getSystemid());

                    PagesPortaleditor::getInstance()->registerAction(
                        new PagesPortaleditorSystemidAction(PagesPortaleditorActionEnum::EDIT(), class_link::getLinkAdminHref($this->getArrModule("module"), "editFaq", "&systemid={$objOneFaq->getSystemid()}"), $objOneFaq->getSystemid())
                    );

                    PagesPortaleditor::getInstance()->registerAction(
                        new PagesPortaleditorSystemidAction(PagesPortaleditorActionEnum::DELETE(), class_link::getLinkAdminHref($this->getArrModule("module"), "deleteFaq", "&systemid={$objOneFaq->getSystemid()}"), $objOneFaq->getSystemid())
                    );

                    PagesPortaleditor::getInstance()->registerAction(
                        new PagesPortaleditorSystemidAction(PagesPortaleditorActionEnum::CREATE(), class_link::getLinkAdminHref($this->getArrModule("module"), "newFaq"), $objOneFaq->getSystemid())
                    );
                }
            }

            //wrap category around
            $objMapper = new class_template_mapper($objCategory);

            //legacy support
            $objMapper->addPlaceholder("faq_cat_title",  $objCategory->getStrTitle());
            $objMapper->addPlaceholder("faq_faqs", $strFaqs);
            $objMapper->addPlaceholder("faq_cat_systemid", $objCategory->getSystemid());

            $strCats .= $objMapper->writeToTemplate("/module_faqs/".$this->arrElementData["faqs_template"], "faq_category");
        }

        //wrap list container around
        //wrap category around
        $strListTemplateID = $this->objTemplate->readTemplate("/module_faqs/".$this->arrElementData["faqs_template"], "faqs_list");
        $arrTemplate = array();
        $arrTemplate["faq_categories"] = $strCats;

        $strReturn .= $this->objTemplate->fillTemplate($arrTemplate, $strListTemplateID);



        return $strReturn;
    }

}
