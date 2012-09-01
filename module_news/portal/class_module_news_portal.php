<?php
/*"******************************************************************************************************
*   (c) 2004-2006 by MulchProductions, www.mulchprod.de                                                 *
*   (c) 2007-2012 by Kajona, www.kajona.de                                                              *
*       Published under the GNU LGPL v2.1, see /system/licence_lgpl.txt                                 *
*-------------------------------------------------------------------------------------------------------*
*	$Id: class_modul_news_portal.php 4520 2012-03-03 23:27:56Z sidler $									*
********************************************************************************************************/

/**
 * Portal-class of the news. Handles thd printing of news lists / detail
 *
 * @package module_news
 * @author sidler@mulchprod.de
 */
class class_module_news_portal extends class_portal implements interface_portal {

	/**
	 * Constructor
	 *
	 * @param mixed $arrElementData
	 */
	public function __construct($arrElementData) {
        $this->setArrModuleEntry("moduleId", _news_module_id_);
        $this->setArrModuleEntry("modul", "news");
        parent::__construct($arrElementData);

        $strAction = $this->getParam("action");
        if ($strAction == "newsDetail" && $this->arrElementData["news_view"] == 1)
            $this->setAction("newsDetail");
		elseif($this->arrElementData["news_view"] == 0 || $strAction == "newsList")
		    $this->setAction("newsList");
	}

    /**
     * Default implementation to avoid mail-spamming.
     */
    protected function actionList() {

    }

	/**
	 * Returns a list of news.
	 * As defined in the element, this could be an archive or a normal list
	 *
	 * @return string
	 */
	protected function actionNewsList() {
		$strReturn = "";
		//Load news using the correct filter
		if($this->getParam("filterid") != "")
		    $strFilterId = $this->getParam("filterid");
		else
		    $strFilterId = $this->arrElementData["news_category"];

        //Load all posts
        $objArraySectionIterator = new class_array_section_iterator(class_module_news_news::getNewsCountPortal($this->arrElementData["news_mode"], $strFilterId));
	    $objArraySectionIterator->setIntElementsPerPage($this->arrElementData["news_amount"]);
	    $objArraySectionIterator->setPageNumber((int)($this->getParam("pv") != "" ? $this->getParam("pv") : 1));
        $objArraySectionIterator->setArraySection(class_module_news_news::loadListNewsPortal($this->arrElementData["news_mode"], $strFilterId, $this->arrElementData["news_order"], $objArraySectionIterator->calculateStartPos(), $objArraySectionIterator->calculateEndPos()));

		$arrNews = $this->objToolkit->simplePager($objArraySectionIterator,$this->getLang("commons_next"), $this->getLang("backward"), "", $this->getPagename());

        $strTemplateID = $this->objTemplate->readTemplate("/module_news/".$this->arrElementData["news_template"], "news_list");
        $strTemplateImageID = $this->objTemplate->readTemplate("/module_news/".$this->arrElementData["news_template"], "news_list_image");
        $strWrapperTemplateID = $this->objTemplate->readTemplate("/module_news/".$this->arrElementData["news_template"], "news_list_wrapper");
		//Check rights
		if(count($arrNews["arrData"]) > 0) {
			foreach($arrNews["arrData"] as $objOneNews) {
                /** @var $objOneNews class_module_news_news  */
				if($objOneNews instanceof class_module_news_news && $objOneNews->rightView()) {
				    $strOneNews = "";
                    $arrOneNews = array();
					//generate a link to the details
					$arrOneNews["news_more_link"] = getLinkPortal($this->arrElementData["news_detailspage"], "", "", $this->getLang("news_mehr"), "newsDetail", "", $objOneNews->getSystemid(), "", "", $objOneNews->getStrTitle());
					$arrOneNews["news_more_link_href"] = getLinkPortalHref($this->arrElementData["news_detailspage"], "", "newsDetail", "", $objOneNews->getSystemid(), "", $objOneNews->getStrTitle());
					$arrOneNews["news_start_date"] = dateToString(new class_date($objOneNews->getIntDateStart()), false);
					$arrOneNews["news_id"] = $objOneNews->getSystemid();
					$arrOneNews["news_title"] = $objOneNews->getStrTitle();
					$arrOneNews["news_intro"] = $objOneNews->getStrIntro();
					$arrOneNews["news_text"] = $objOneNews->getStrText();

					//reset more link?
                    if(uniStrlen(htmlStripTags($arrOneNews["news_text"])) == 0)
                        $arrOneNews["news_more_link"] = "";


                    $arrPAC = $this->loadPostacomments($objOneNews->getSystemid());
                    if($arrPAC != null) {
                        $arrOneNews["news_nrofcomments"] = $arrPAC["nrOfComments"];
                        $arrOneNews["news_commentlist"] = $arrPAC["commentList"];
                    }

                    //load template section with or without image?
				    if($objOneNews->getStrImage() != "") {
                        $arrOneNews["news_image"] = urlencode($objOneNews->getStrImage());
                        $strOneNews .= $this->objTemplate->fillTemplate($arrOneNews, $strTemplateImageID);
                    } else {
                        $strOneNews .= $this->objTemplate->fillTemplate($arrOneNews, $strTemplateID);
                    }

					//Add pe code
				    $arrPeConfig = array(
                          "pe_module" => "news",
                          "pe_action_edit" => "editNews",
                          "pe_action_edit_params" => "&systemid=".$objOneNews->getSystemid(),
                          "pe_action_new" => "newNews",
                          "pe_action_new_params" => "",
                          "pe_action_delete" => "deleteNews",
                          "pe_action_delete_params" => "&systemid=".$objOneNews->getSystemid()
                    );
				    $strReturn .= class_element_portal::addPortalEditorCode($strOneNews, $objOneNews->getSystemid(), $arrPeConfig);
				}
			}
            $arrWrapperTemplate = array();
            $arrWrapperTemplate["news"] = $strReturn;
            $arrWrapperTemplate["link_forward"] = $arrNews["strForward"];
            $arrWrapperTemplate["link_pages"] = $arrNews["strPages"];
            $arrWrapperTemplate["link_back"] = $arrNews["strBack"];
            $strReturn = $this->fillTemplate($arrWrapperTemplate, $strWrapperTemplateID);
		}
		else {
			$strReturn .= $this->getLang("news_list_empty");
		}
		return $strReturn;
	}

	/**
	 * Creates the detailed-view of news
	 *
	 * @return string
	 */
	protected function actionNewsDetail() {
		$strReturn = "";
        /** @var $objNews class_module_news_news */
        $objNews = class_objectfactory::getInstance()->getObject($this->getSystemid());
		if($objNews != null && $objNews instanceof class_module_news_news && $objNews->rightView() && $objNews->getStatus() == "1") {
			//Load record

            $arrNews = array();
            $arrNews["news_back_link"] = "<a href=\"javascript:history.back();\">".$this->getLang("news_zurueck")."</a>";
            $arrNews["news_start_date"] = dateToString(new class_date($objNews->getIntDateStart()), false);
            $arrNews["news_id"] = $objNews->getSystemid();
            $arrNews["news_title"] = $objNews->getStrTitle();
            $arrNews["news_intro"] = $objNews->getStrIntro();
            $arrNews["news_text"] = $objNews->getStrText();

            //load template section with or without image?
            $strTemplateID = "";
            if($objNews->getStrImage() != "") {
                $strTemplateID = $this->objTemplate->readTemplate("/module_news/".$this->arrElementData["news_template"], "news_detail_image");
                $arrNews["news_image"] = urlencode($objNews->getStrImage());
            } else {
                $strTemplateID = $this->objTemplate->readTemplate("/module_news/".$this->arrElementData["news_template"], "news_detail");
            }
            $strReturn .= $this->fillTemplate($arrNews, $strTemplateID);

            //Add pe code
            $arrPeConfig = array(
                "pe_module" => "news",
                "pe_action_edit" => "editNews",
                "pe_action_edit_params" => "&systemid=".$this->getSystemid()
            );
            $strReturn = class_element_portal::addPortalEditorCode($strReturn, $objNews->getSystemid(), $arrPeConfig);

            //and count the hit
            $objNews->increaseHits();

            //set the name of the current news to the page-title via class_pages
            class_module_pages_portal::registerAdditionalTitle($objNews->getStrTitle());
		}
		else
			$strReturn = $this->getLang("commons_error_permissions");
		return $strReturn;
	}

    /**
     * Loads and renders the list of comments provdided by the current news-entry
     */
    private function loadPostacomments($strNewsSystemid) {
        if($this->isPostacommentOnTemplate($this->arrElementData["news_template"])) {

            $objPacModule = class_module_system_module::getModuleByName("postacomment");

            $arrReturn = array();
            if($objPacModule != null) {
                $arrPosts = class_module_postacomment_post::loadPostList(false, "", $strNewsSystemid, $this->getStrPortalLanguage());

                //the rendered list
                $objPacPortal = new class_module_postacomment_portal(array("char1" => "postacomment_ajax.tpl"));
                $objPacPortal->setSystemid($strNewsSystemid);
                $objPacPortal->setStrPagefilter("");
                $strListCode = $objPacPortal->action();

                $arrReturn["nrOfComments"] = count($arrPosts);
                $arrReturn["commentList"] = $strListCode;
            }
            else
                return null;

            return $arrReturn;

        }

        return null;
    }

    /**
     * Checks, if the current template provides placeholders needed to show comments.
     * Ohterwise, the postacomment-module won't be even called.
     *
     * @param string $strTemplate
     * @return bool
     */
    private function isPostacommentOnTemplate($strTemplate) {
        $strTemplateID = $this->objTemplate->readTemplate("/module_news/".$strTemplate, "news_list");

        return $this->objTemplate->containsPlaceholder($strTemplateID, "news_commentlist") || $this->objTemplate->containsPlaceholder($strTemplateID, "news_nrofcomments");
    }
}
