<?php
/*"******************************************************************************************************
*   (c) 2004-2006 by MulchProductions, www.mulchprod.de                                                 *
*   (c) 2007 by Kajona, www.kajona.de                                                                   *
*       Published under the GNU LGPL v2.1, see /system/licence_lgpl.txt                                 *
*-------------------------------------------------------------------------------------------------------*
* 																										*
* 	class_modul_news_portal.php																			*
* 	portalclass of the news																				*
*-------------------------------------------------------------------------------------------------------*
*	$Id$									*
********************************************************************************************************/

//Include der Mutter-Klasse
include_once(_portalpath_."/class_portal.php");
include_once(_portalpath_."/interface_portal.php");
//model
include_once(_systempath_."/class_modul_news_category.php");
include_once(_systempath_."/class_modul_news_feed.php");
include_once(_systempath_."/class_modul_news_news.php");

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
		elseif($this->arrElementData["news_view"] == 0)
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
		//Load news
		$arrNews = class_modul_news_news::loadListNewsPortal($this->arrElementData["news_mode"], $this->arrElementData["news_category"]);
        $strTemplateID = $this->objTemplate->readTemplate("/modul_news/".$this->arrElementData["news_template"], "news_list");
		//Check rights
		if(count($arrNews) > 0) {
			foreach($arrNews as $objOneNews) {
				if($this->objRights->rightView($objOneNews->getSystemid())) {
				    $strOneNews = "";
					//generate a link to the details
					$arrOneNews["news_more_link"] = getLinkPortal($this->arrElementData["news_detailspage"], "", "", $this->getText("news_mehr"),"newsDetail", "", $objOneNews->getSystemid());
					$arrOneNews["news_start_date"] = timeToString($objOneNews->getIntDateStart(), false);
					$arrOneNews["news_id"] = $objOneNews->getSystemid();
					$arrOneNews["news_title"] = $objOneNews->getStrTitle();
					$arrOneNews["news_intro"] = $objOneNews->getStrIntro();
					$arrOneNews["news_text"] = $objOneNews->getStrNewstext();
					$arrOneNews["news_image"] = $objOneNews->getStrImage();
					$strOneNews .= $this->objTemplate->fillTemplate($arrOneNews, $strTemplateID);
	
					//Add pe code
				    include_once(_portalpath_."/class_elemente_portal.php");
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
			$strTemplateID = $this->objTemplate->readTemplate("/modul_news/".$this->arrElementData["news_template"], "news_detail");
			//back link
			$arrNews["news_back_link"] = "<a href=\"javascript:history.back();\">".$this->getText("news_zurueck")."</a>";
			$arrNews["news_start_date"] = timeToString($objNews->getIntDateStart(), false);
			$arrNews["news_id"] = $objNews->getSystemid();
			$arrNews["news_title"] = $objNews->getStrTitle();
			$arrNews["news_intro"] = $objNews->getStrIntro();
			$arrNews["news_text"] = $objNews->getStrNewstext();
			$arrNews["news_image"] = $objNews->getStrImage();
			$strReturn .= $this->objTemplate->fillTemplate($arrNews, $strTemplateID);

			//Add pe code
			$arrPeConfig = array(
		                              "pe_module" => "news",
		                              "pe_action_edit" => "editNewscontent",
		                              "pe_action_edit_params" => "&systemid=".$this->getSystemid()
			                    );
			include_once(_portalpath_."/class_elemente_portal.php");
			$strReturn = class_element_portal::addPortalEditorCode($strReturn, $objNews->getSystemid(), $arrPeConfig, true);
			//and count the hit
			$objNews->increaseHits();
		}
		else
			$strReturn = $this->getText("fehler_recht");
		return $strReturn;
	}

}
?>