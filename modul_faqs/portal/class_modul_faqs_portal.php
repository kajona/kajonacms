<?php
/*"******************************************************************************************************
*   (c) 2004-2006 by MulchProductions, www.mulchprod.de                                                 *
*   (c) 2007-2008 by Kajona, www.kajona.de                                                              *
*       Published under the GNU LGPL v2.1, see /system/licence_lgpl.txt                                 *
*-------------------------------------------------------------------------------------------------------*
* 																										*
* 	class_modul_faqs_portal.php																			*
* 	portalclass of the faqs																				*
*-------------------------------------------------------------------------------------------------------*
*	$Id$									*
********************************************************************************************************/

//Include der Mutter-Klasse
include_once(_portalpath_."/class_portal.php");
include_once(_portalpath_."/interface_portal.php");
//model
include_once(_systempath_."/class_modul_faqs_category.php");
include_once(_systempath_."/class_modul_faqs_faq.php");

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

    				$strOneFaq .= $this->objTemplate->fillTemplate($arrOneFaq, $strFaqTemplateID);

    				//Add pe code
    			    include_once(_portalpath_."/class_elemente_portal.php");
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

    		$strCats .= $this->objTemplate->fillTemplate($arrTemplate, $strCatTemplateID);
		}

		//wrap list container around
		//wrap category around
		$strListTemplateID = $this->objTemplate->readTemplate("/modul_faqs/".$this->arrElementData["faqs_template"], "faqs_list");
		$arrTemplate = array();
		$arrTemplate["faq_categories"] = $strCats;

		$strReturn .= $this->objTemplate->fillTemplate($arrTemplate, $strListTemplateID);

		return $strReturn;
	}

}
?>