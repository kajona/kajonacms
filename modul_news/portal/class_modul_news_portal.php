<?php
/*"******************************************************************************************************
*   (c) 2004-2006 by MulchProductions, www.mulchprod.de                                                 *
*   (c) 2007-2009 by Kajona, www.kajona.de                                                              *
*       Published under the GNU LGPL v2.1, see /system/licence_lgpl.txt                                 *
*-------------------------------------------------------------------------------------------------------*
*	$Id$									*
********************************************************************************************************/

/**
 * Portal-class of the news. Handles thd printing of news lists / detail
 *
 * @package modul_news
 */
class class_modul_news_portal extends class_portal implements interface_portal {
	/**
	 * Constructor
	 *
	 * @param mixed $arrElementData
	 */
	public function __construct($arrElementData) {
        $arrModule = array();
		$arrModule["name"] 				= "modul_news";
		$arrModule["author"] 			= "sidler@mulchprod.de";
		$arrModule["table"] 			= _dbprefix_."news";
		$arrModule["table2"]			= _dbprefix_."news_category";
		$arrModule["table3"]			= _dbprefix_."news_member";
		$arrModule["moduleId"] 			= _news_modul_id_;
		$arrModule["modul"]				= "news";

		parent::__construct($arrModule, $arrElementData);
	}

	/**
	 * Action-Block, decides what to do
	 *
	 * @return string
	 */
	public function action() {
		$strReturn = "";
		$strAction = "";

		if($this->getParam("action") != "")
		    $strAction = $this->getParam("action");

		if ($strAction == "newsDetail" && $this->arrElementData["news_view"] == 1)
			$strReturn = $this->actionNewsdetail();
		elseif($this->arrElementData["news_view"] == 0 || $strAction == "newsList")
		    $strReturn = $this->actionList();

		return $strReturn;

	}

//---Aktionsfunktionen-----------------------------------------------------------------------------------

	/**
	 * Returns a list of news.
	 * As definded in the element, this could be a archive or a normal list
	 *
	 * @return string
	 */
	public function actionList() {
		$strReturn = "";
		//Load news using the correct filter
		$strFilterId = "";
		if($this->getParam("filterid") != "")
		    $strFilterId = $this->getParam("filterid");
		else
		    $strFilterId = $this->arrElementData["news_category"];



        //Load all posts
        $objArraySectionIterator = new class_array_section_iterator(class_modul_news_news::getNewsCountPortal($this->arrElementData["news_mode"], $strFilterId));
	    $objArraySectionIterator->setIntElementsPerPage($this->arrElementData["news_amount"]);
	    $objArraySectionIterator->setPageNumber((int)($this->getParam("pv") != "" ? $this->getParam("pv") : 1));
        $objArraySectionIterator->setArraySection(class_modul_news_news::loadListNewsPortal($this->arrElementData["news_mode"], $strFilterId, $this->arrElementData["news_order"], $objArraySectionIterator->calculateStartPos(), $objArraySectionIterator->calculateEndPos()));

	    $arrNews = $objArraySectionIterator->getArrayExtended();

		$arrNews = $this->objToolkit->pager($this->arrElementData["news_amount"], ($this->getParam("pv") != "" ? $this->getParam("pv") : 1), $this->getText("forward"), $this->getText("backward"), "", ($this->getParam("page") != "" ? $this->getParam("page") : ""), $arrNews);

		//$arrNews = class_modul_news_news::loadListNewsPortal($this->arrElementData["news_mode"], $strFilterId, $this->arrElementData["news_order"]);
        $strTemplateID = $this->objTemplate->readTemplate("/modul_news/".$this->arrElementData["news_template"], "news_list");
        $strTemplateImageID = $this->objTemplate->readTemplate("/modul_news/".$this->arrElementData["news_template"], "news_list_image");
        $strWrapperTemplateID = $this->objTemplate->readTemplate("/modul_news/".$this->arrElementData["news_template"], "news_list_wrapper");
		//Check rights
		if(count($arrNews["arrData"]) > 0) {
			foreach($arrNews["arrData"] as $objOneNews) {
				if($objOneNews instanceof class_modul_news_news && $this->objRights->rightView($objOneNews->getSystemid())) {
				    $strOneNews = "";
                    $arrOneNews = array();
					//generate a link to the details
					$arrOneNews["news_more_link"] = getLinkPortal($this->arrElementData["news_detailspage"], "", "", $this->getText("news_mehr"), "newsDetail", "", $objOneNews->getSystemid(), "", "", $objOneNews->getStrTitle());
					$arrOneNews["news_more_link_href"] = getLinkPortalHref($this->arrElementData["news_detailspage"], "", "newsDetail", "", $objOneNews->getSystemid(), "", $objOneNews->getStrTitle());
					$arrOneNews["news_start_date"] = timeToString($objOneNews->getIntDateStart(), false);
					$arrOneNews["news_id"] = $objOneNews->getSystemid();
					$arrOneNews["news_title"] = $objOneNews->getStrTitle();
					$arrOneNews["news_intro"] = $objOneNews->getStrIntro();
					$arrOneNews["news_text"] = $objOneNews->getStrNewstext();

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
				                              "pe_action_edit" => "editNewscontent",
				                              "pe_action_edit_params" => "&systemid=".$objOneNews->getSystemid(),
				                              "pe_action_new" => "newNews",
				                              "pe_action_new_params" => "",
				                              "pe_action_delete" => "deleteNews",
				                              "pe_action_delete_params" => "&systemid=".$objOneNews->getSystemid()
				                        );
				    $strReturn .= class_element_portal::addPortalEditorCode($strOneNews, $objOneNews->getSystemid(), $arrPeConfig, true);
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
			$strReturn .= $this->getText("news_list_empty");
		}
		return $strReturn;
	}

	/**
	 * Creates the detailed-view of news
	 *
	 * @return string
	 */
	public function actionNewsdetail() {
		$strReturn = "";
		if($this->objRights->rightView($this->getSystemid())) {
			//Load record
			$objNews = new class_modul_news_news($this->getSystemid());
	        if($objNews->getStatus() == "1") {
        	
                $arrNews = array();
				$arrNews["news_back_link"] = "<a href=\"javascript:history.back();\">".$this->getText("news_zurueck")."</a>";
				$arrNews["news_start_date"] = timeToString($objNews->getIntDateStart(), false);
				$arrNews["news_id"] = $objNews->getSystemid();
				$arrNews["news_title"] = $objNews->getStrTitle();
				$arrNews["news_intro"] = $objNews->getStrIntro();
				$arrNews["news_text"] = $objNews->getStrNewstext();
				
	            //load template section with or without image?
                if($objNews->getStrImage() != "") {
                    $strTemplateID = $this->objTemplate->readTemplate("/modul_news/".$this->arrElementData["news_template"], "news_detail_image");
                    $arrNews["news_image"] = urlencode($objNews->getStrImage());
                } else {
                    $strTemplateID = $this->objTemplate->readTemplate("/modul_news/".$this->arrElementData["news_template"], "news_detail");
                }
				$strReturn .= $this->fillTemplate($arrNews, $strTemplateID);

				//Add pe code
				$arrPeConfig = array(
			                              "pe_module" => "news",
			                              "pe_action_edit" => "editNewscontent",
			                              "pe_action_edit_params" => "&systemid=".$this->getSystemid()
				                    );
				$strReturn = class_element_portal::addPortalEditorCode($strReturn, $objNews->getSystemid(), $arrPeConfig, true);
				
				//and count the hit
				$objNews->increaseHits();

				//set the name of the current news to the page-title via class_pages
				class_modul_pages_portal::registerAdditionalTitle($objNews->getStrTitle());
			}
			else
                $strReturn = $this->getText("fehler_recht");
		}
		else
			$strReturn = $this->getText("fehler_recht");
		return $strReturn;
	}


    private function loadPostacomments($strNewsSystemid) {
        $objPacModule = class_modul_system_module::getModuleByName("postacomment");

        $arrReturn = array();
        if($objPacModule != null) {
            $arrPosts = class_modul_postacomment_post::loadPostList(false, class_modul_pages_page::getPageByName($this->getPagename())->getSystemid(), $strNewsSystemid, $this->getPortalLanguage());

            //the rendered list
            $objPacPortal = new class_modul_postacomment_portal(array("char1" => "postacomment_ajax.tpl"));
            $objPacPortal->setSystemid($strNewsSystemid);
            $strListCode = $objPacPortal->action();
            
            //var_dump($strListCode);

            $arrReturn["nrOfComments"] = count($arrPosts);
            $arrReturn["commentList"] = $strListCode;
        }
        else
            return null;

        return $arrReturn;
    }
}
?>