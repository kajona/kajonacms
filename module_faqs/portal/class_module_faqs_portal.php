<?php
/*"******************************************************************************************************
*   (c) 2004-2006 by MulchProductions, www.mulchprod.de                                                 *
*   (c) 2007-2013 by Kajona, www.kajona.de                                                              *
*       Published under the GNU LGPL v2.1, see /system/licence_lgpl.txt                                 *
*-------------------------------------------------------------------------------------------------------*
*	$Id$									*
********************************************************************************************************/

/**
 * Portal-class of the faqs. Handles the printing of faqs lists / detail
 *
 * @package module_faqs
 * @author sidler@mulchprod.de
 * @module faqs
 * @moduleId _faqs_module_id_
 */
class class_module_faqs_portal extends class_portal implements interface_portal {


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

            $strFaqTemplateID = $this->objTemplate->readTemplate("/module_faqs/".$this->arrElementData["faqs_template"], "faq_faq");
            $strFaqs = "";
            //Check rights
            foreach($arrFaqs as $objOneFaq) {
                if($objOneFaq->rightView()) {
                    $strOneFaq = "";
                    $arrOneFaq = array();
                    $arrOneFaq["faq_question"] = $objOneFaq->getStrQuestion();
                    $arrOneFaq["faq_answer"] = $objOneFaq->getStrAnswer();
                    $arrOneFaq["faq_systemid"] = $objOneFaq->getSystemid();


                    //ratings available?
                    if($objOneFaq->getFloatRating() !== null && class_module_system_module::getModuleByName("rating") != null) {
                        /** @var $objRating class_module_rating_portal */
                        $objRating = class_module_system_module::getModuleByName("rating")->getPortalInstanceOfConcreteModule();
                        $arrOneFaq["faq_rating"] = $objRating->buildRatingBar($objOneFaq->getFloatRating(), $objOneFaq->getIntRatingHits(), $objOneFaq->getSystemid(), $objOneFaq->isRateableByUser(), $objOneFaq->rightRight1());

                    }

                    $strOneFaq .= $this->objTemplate->fillTemplate($arrOneFaq, $strFaqTemplateID, false);

                    //Add pe code
                    $arrPeConfig = array(
                        "pe_module"               => "faqs",
                        "pe_action_edit"          => "editFaq",
                        "pe_action_edit_params"   => "&systemid=".$objOneFaq->getSystemid(),
                        "pe_action_new"           => "newFaq",
                        "pe_action_new_params"    => "",
                        "pe_action_delete"        => "deleteFaq",
                        "pe_action_delete_params" => "&systemid=".$objOneFaq->getSystemid()
                    );
                    $strFaqs .= class_element_portal::addPortalEditorCode($strOneFaq, $objOneFaq->getSystemid(), $arrPeConfig);
                }
            }

            //wrap category around
            $strCatTemplateID = $this->objTemplate->readTemplate("/module_faqs/".$this->arrElementData["faqs_template"], "faq_category");
            $arrTemplate = array();
            $arrTemplate["faq_cat_title"] = $objCategory->getStrTitle();
            $arrTemplate["faq_faqs"] = $strFaqs;
            $arrTemplate["faq_cat_systemid"] = $objCategory->getSystemid();

            $strCats .= $this->fillTemplate($arrTemplate, $strCatTemplateID);
        }

        //wrap list container around
        //wrap category around
        $strListTemplateID = $this->objTemplate->readTemplate("/module_faqs/".$this->arrElementData["faqs_template"], "faqs_list");
        $arrTemplate = array();
        $arrTemplate["faq_categories"] = $strCats;

        $strReturn .= $this->fillTemplate($arrTemplate, $strListTemplateID);

        return $strReturn;
    }

}
