<?php
/*"******************************************************************************************************
*   (c) 2004-2006 by MulchProductions, www.mulchprod.de                                                 *
*   (c) 2007-2012 by Kajona, www.kajona.de                                                              *
*       Published under the GNU LGPL v2.1, see /system/licence_lgpl.txt                                 *
*-------------------------------------------------------------------------------------------------------*
*	$Id$                                *
********************************************************************************************************/

/**
 * Portal-Class of the search module. Does all the searching in the database
 *
 * @package module_search
 * @author sidler@mulchprod.de
 */
class class_module_search_portal extends class_portal implements interface_portal {
	private $strSearchterm = "";

	/**
	 * Constructor
	 *
	 * @param mixed $arrElementData
	 */
	public function __construct($arrElementData) {
        parent::__construct($arrElementData);

        $this->setArrModuleEntry("modul", "search");
        $this->setArrModuleEntry("moduleId", _search_module_id_);

		if($this->getParam("searchterm") != "") {
			$this->strSearchterm = htmlToString(urldecode($this->getParam("searchterm")), true);
		}
	}
	


	/**
	 * Creates a search form using the template specified in the admin
	 *
	 * @return string
     * @permissions view
	 */
	protected function actionList() {

		$strTemplateID = $this->objTemplate->readTemplate("/module_search/".$this->arrElementData["search_template"], "search_form");

		$arrTemplate = array();
		if($this->strSearchterm != "")
			$arrTemplate["suche_term"] = $this->strSearchterm;

		$strPage = $this->arrElementData["search_page"]	;
		if($strPage == "")
		  $strPage = $this->getPagename();

		$arrTemplate["action"] = getLinkPortalHref($strPage, "", "search");
        return $this->fillTemplate($arrTemplate, $strTemplateID);
	}


	/**
	 * Calls the single search-functions, sorts the results an creates the output
	 *
	 * @return string
     * @permissions view
	 */
	protected function actionSearch() {
		$strReturn = "";
		//Read the config
        $arrTemplate = array();
		$arrTemplate["hitlist"] = "";
		$strReturn .= $this->actionList();
        $objSearchCommons = new class_module_search_commons();

        /** @var $arrHitsSorted class_search_result */
        $arrHitsSorted = $objSearchCommons->doPortalSearch($this->strSearchterm);

		//Resize Array to wanted size
		$arrHitsFilter = $this->objToolkit->pager($this->arrElementData["search_amount"], ($this->getParam("pv") != "" ? (int)$this->getParam("pv") : 1), $this->getLang("commons_next"), $this->getLang("commons_back"), "search", ($this->arrElementData["search_page"] != "" ? $this->arrElementData["search_page"] : $this->getPagename()), $arrHitsSorted, "&searchterm=".urlencode(html_entity_decode($this->strSearchterm, ENT_COMPAT, "UTF-8")));

        $strRowTemplateID = $this->objTemplate->readTemplate("/module_search/".$this->arrElementData["search_template"], "search_hitlist_hit");

        /** @var $objHit class_search_result */
		foreach($arrHitsFilter["arrData"] as $objHit) {

            if($objHit->getStrPagename() == "master")
                continue;

            $objPage = class_module_pages_page::getPageByName($objHit->getStrPagename());
            if(!$objPage->rightView() || $objPage->getIntRecordStatus() != 1)
                continue;

            $arrRow = array();
			if(($objHit->getStrPagelink() == ""))
				$arrRow["page_link"] = getLinkPortal($objHit->getStrPagename(), "", "_self", $objHit->getStrPagename(), "", "&highlight=".urlencode(html_entity_decode($this->strSearchterm, ENT_QUOTES, "UTF-8"))."#".uniStrtolower(urlencode(html_entity_decode($this->strSearchterm, ENT_QUOTES, "UTF-8"))));
			else
				$arrRow["page_link"] = $objHit->getStrPagelink();
			$arrRow["page_description"] = uniStrTrim($objHit->getStrDescription(), 200);
			$arrTemplate["hitlist"] .= $this->objTemplate->fillTemplate($arrRow, $strRowTemplateID, false);
		}

		//Collect global data
		$arrTemplate["search_term"] = $this->strSearchterm;
		$arrTemplate["search_nrresults"] = count($arrHitsSorted);
		$arrTemplate["link_forward"] = $arrHitsFilter["strForward"];
		$arrTemplate["link_back"] = $arrHitsFilter["strBack"];
		$arrTemplate["link_overview"] = $arrHitsFilter["strPages"];

		$strTemplateID = $this->objTemplate->readTemplate("/module_search/".$this->arrElementData["search_template"], "search_hitlist");

		return $strReturn . $this->fillTemplate($arrTemplate, $strTemplateID);
	}

}
