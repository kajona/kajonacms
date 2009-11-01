<?php
/*"******************************************************************************************************
*   (c) 2004-2006 by MulchProductions, www.mulchprod.de                                                 *
*   (c) 2007-2009 by Kajona, www.kajona.de                                                              *
*       Published under the GNU LGPL v2.1, see /system/licence_lgpl.txt                                 *
*-------------------------------------------------------------------------------------------------------*
*	$Id$									*
********************************************************************************************************/

/**
 * Portal-class of the faqs. Handles the printing of faqs lists / detail
 *
 * @package modul_faqs
 */
class class_modul_faqs_portal extends class_portal implements interface_portal {
	/**
	 * Constructor
	 *
	 * @param mixed $arrElementData
	 */
	public function __construct($arrElementData) {
        $arrModule = array();
		$arrModule["name"] 				= "modul_faqs";
		$arrModule["author"] 			= "sidler@mulchprod.de";
		$arrModule["table"] 			= _dbprefix_."faqs";
		$arrModule["table2"]			= _dbprefix_."faqs_category";
		$arrModule["table3"]			= _dbprefix_."faqs_member";
		$arrModule["moduleId"] 			= _faqs_modul_id_;
		$arrModule["modul"]				= "faqs";

		parent::__construct($arrModule, $arrElementData);
	}

	/**
	 * Action-Block, decides what to do
	 *
	 * @return string
	 */
	public function action() {
		$strReturn = "";

		$strReturn = $this->actionList();

		return $strReturn;

	}

//--- Lists ---------------------------------------------------------------------------------------------

	/**
	 * Returns a list of faqs.
	 * The category is choosen from the element-data
	 *
	 * @return string
	 */
	public function actionList() {
		$strReturn = "";

		//load categories
		$arrCategories = array();
		if($this->arrElementData["faqs_category"] == "0") {
		    $arrCategories = class_modul_faqs_category::getCategories(true);
		}
		else {
		    $arrCategories[] = new class_modul_faqs_category($this->arrElementData["faqs_category"]);
		}

		//if no cat was created by now, use a dummy cat
		if(count($arrCategories) == 0)
		    $arrCategories[] = 1;

		//load every category
		$strCats = "";
		foreach ($arrCategories as $objCategory) {

    		//Load faqs
    		if(!is_object($objCategory) && $objCategory == 1) {
    		    $arrFaqs = class_modul_faqs_faq::loadListFaqsPortal(1);
    		    $objCategory = new class_modul_faqs_category();
    		}
    		else
    		    $arrFaqs = class_modul_faqs_faq::loadListFaqsPortal($objCategory->getSystemid());

            $strFaqTemplateID = $this->objTemplate->readTemplate("/modul_faqs/".$this->arrElementData["faqs_template"], "faq_faq");
            $strFaqs = "";
    		//Check rights
    		foreach($arrFaqs as $objOneFaq) {
    			if($this->objRights->rightView($objOneFaq->getSystemid())) {
    			    $strOneFaq = "";
    				$arrOneFaq = array();
    				$arrOneFaq["faq_question"] = $objOneFaq->getStrQuestion();
    				$arrOneFaq["faq_answer"] = $objOneFaq->getStrAnswer();
    				$arrOneFaq["faq_systemid"] = $objOneFaq->getSystemid();


    			    //ratings available?
			        if($objOneFaq->getFloatRating() !== null) {
			            $arrOneFaq["faq_rating"] = $this->buildRatingBar($objOneFaq->getFloatRating(), $objOneFaq->getSystemid(), $objOneFaq->isRateableByUser(), $objOneFaq->rightRight1());
			        }

    				$strOneFaq .= $this->objTemplate->fillTemplate($arrOneFaq, $strFaqTemplateID, false);

    				//Add pe code
    			    $arrPeConfig = array(
    			                              "pe_module" => "faqs",
    			                              "pe_action_edit" => "editFaq",
    			                              "pe_action_edit_params" => "&systemid=".$objOneFaq->getSystemid(),
    			                              "pe_action_new" => "newFaq",
    			                              "pe_action_new_params" => "",
    			                              "pe_action_delete" => "deleteFaq",
    			                              "pe_action_delete_params" => "&systemid=".$objOneFaq->getSystemid()
    			                        );
    			    $strFaqs .= class_element_portal::addPortalEditorCode($strOneFaq, $objOneFaq->getSystemid(), $arrPeConfig, true);
    			}
    		}

    		//wrap category around
    		$strCatTemplateID = $this->objTemplate->readTemplate("/modul_faqs/".$this->arrElementData["faqs_template"], "faq_category");
    		$arrTemplate = array();
    		$arrTemplate["faq_cat_title"] = $objCategory->getStrTitle();
    		$arrTemplate["faq_faqs"] = $strFaqs;

    		$strCats .= $this->fillTemplate($arrTemplate, $strCatTemplateID);
		}

		//wrap list container around
		//wrap category around
		$strListTemplateID = $this->objTemplate->readTemplate("/modul_faqs/".$this->arrElementData["faqs_template"], "faqs_list");
		$arrTemplate = array();
		$arrTemplate["faq_categories"] = $strCats;

		$strReturn .= $this->fillTemplate($arrTemplate, $strListTemplateID);

		return $strReturn;
	}



    /**
     * Builds the rating bar available for every faq
     * Creates the needed js-links and image-tags as defined by the template.
     *
     * @param float $floatRating
     * @param string $strSystemid
     * @param bool $bitRatingAllowed
     * @return string
     */
    private function buildRatingBar($floatRating, $strSystemid, $bitRatingAllowed = true, $bitPermissions = true) {
        $strIcons = "";
        $strRatingBarTitle = "";

        $intNumberOfIcons = class_modul_rating_rate::$intMaxRatingValue;

        //read the templates
        $strTemplateBarId = $this->objTemplate->readTemplate("/modul_faqs/".$this->arrElementData["faqs_template"], "rating_bar");

        if($bitRatingAllowed && $bitPermissions) {
            $strTemplateIconId = $this->objTemplate->readTemplate("/modul_faqs/".$this->arrElementData["faqs_template"], "rating_icon");

            for($intI = 1; $intI <= $intNumberOfIcons; $intI++) {
                $arrTemplate = array();
                $arrTemplate["rating_icon_number"] = $intI;

                $arrTemplate["rating_icon_onclick"] = "kajonaRating('".$strSystemid."', '".$intI.".0', ".$intNumberOfIcons."); kajonaTooltip.hide(); return false;";
                $arrTemplate["rating_icon_title"] = $this->getText("faqs_rating_rate1").$intI.$this->getText("faqs_rating_rate2");

                $strIcons .= $this->fillTemplate($arrTemplate, $strTemplateIconId);
            }
        } else {
            //disable caching
            class_modul_pages_portal::disablePageCacheForGeneration();
            if(!$bitRatingAllowed)
                $strRatingBarTitle = $this->getText("faqs_rating_voted");
            else
                $strRatingBarTitle = $this->getText("faqs_rating_permissions");
        }

        return $this->fillTemplate(array("rating_icons" => $strIcons, "rating_bar_title" => $strRatingBarTitle, "rating_rating" => $floatRating, "rating_ratingPercent" => ($floatRating/$intNumberOfIcons*100), "system_id" => $strSystemid, 2), $strTemplateBarId);
    }

}
?>