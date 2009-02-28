<?php
/*"******************************************************************************************************
*   (c) 2004-2006 by MulchProductions, www.mulchprod.de                                                 *
*   (c) 2007-2009 by Kajona, www.kajona.de                                                              *
*       Published under the GNU LGPL v2.1, see /system/licence_lgpl.txt                                 *
*-------------------------------------------------------------------------------------------------------*
*	$Id$                                *
********************************************************************************************************/

//include base classes
include_once(_portalpath_."/class_portal.php");
include_once(_portalpath_."/interface_portal.php");
include_once(_systempath_."/class_modul_search_commons.php");

/**
 * Portal-Class of the search module. Does all the searching in the database
 *
 * @package modul_search
 */
class class_modul_search_portal extends class_portal implements interface_portal {
	private $strSearchterm = "";

	/**
	 * Constructor
	 *
	 * @param mixed $arrElementData
	 */
	public function __construct($arrElementData) {
        $arrModule = array();
		$arrModule["name"] 				= "modul_search";
		$arrModule["author"] 			= "sidler@mulchprod.de";
		$arrModule["moduleId"] 			= _suche_modul_id_;
		$arrModule["modul"] 			= "search";

		parent::__construct($arrModule, $arrElementData);

		if($this->getParam("searchterm") != "") {
			$this->strSearchterm = htmlToString(urldecode($this->getParam("searchterm")), true);
		}
	}

	/**
	 * Action-Block to manage the class-behaviour
	 *
	 * @return string
	 */
	public function action() {
		$strReturn = "";
		$strAction= "";

		if($this->getParam("action") != "")
		    $strAction = $this->getParam("action");

		if($strAction == "search")
			$strReturn = $this->actionSearch();
		else
		    $strReturn = $this->actionForm();

		return $strReturn;

	}

// --- Suchformular -------------------------------------------------------------------------------------

	/**
	 * Creates a search form using the template specified in the admin
	 *
	 * @return string
	 */
	private function actionForm() {
		$strReturn = "";

		$strTemplateID = $this->objTemplate->readTemplate("/modul_search/".$this->arrElementData["search_template"], "search_form");

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
	 */
	private function actionSearch() {
		$strReturn = "";
		//Read the config
        $arrTemplate = array();
		$arrTemplate["hitlist"] = "";
		$strReturn .= $this->actionForm();
        $objSearchCommons = new class_modul_search_commons();
        $arrHitsSorted = $objSearchCommons->doSearch($this->strSearchterm);

		//Resize Array to wanted size
		$arrHitsFilter = $this->objToolkit->pager($this->arrElementData["search_amount"], ($this->getParam("pv") != "" ? (int)$this->getParam("pv") : 1), $this->getText("weiter"), $this->getText("zurueck"), "search", ($this->arrElementData["search_page"] != "" ? $this->arrElementData["search_page"] : $this->getPagename()), $arrHitsSorted, "&searchterm=".urlencode(html_entity_decode($this->strSearchterm, ENT_COMPAT, "UTF-8")));

        $strRowTempalteID = $this->objTemplate->readTemplate("/modul_search/".$this->arrElementData["search_template"], "search_hitlist_hit");
		foreach($arrHitsFilter["arrData"] as $strPage => $arrHit) {
            $arrRow = array();
			if(!isset($arrHit["pagelink"]))
				$arrRow["page_link"] = getLinkPortal($arrHit["pagename"], "", "_self", $arrHit["pagename"], "", "&highlight=".urlencode(html_entity_decode($this->strSearchterm, ENT_COMPAT, "UTF-8"))."#".strtolower(urlencode(html_entity_decode($this->strSearchterm, ENT_COMPAT, "UTF-8"))));
			else
				$arrRow["page_link"] = $arrHit["pagelink"];
			$arrRow["page_description"] = $arrHit["description"];
			$arrTemplate["hitlist"] .= $this->objTemplate->fillTemplate($arrRow, $strRowTempalteID);
		}

		//Collect global data
		$arrTemplate["search_term"] = $this->strSearchterm;
		$arrTemplate["search_nrresults"] = count($arrHitsSorted);
		$arrTemplate["link_forward"] = $arrHitsFilter["strForward"];
		$arrTemplate["link_back"] = $arrHitsFilter["strBack"];
		$arrTemplate["link_overview"] = $arrHitsFilter["strPages"];

		$arrTemplate["hitlist_text1"] = $this->getText("hitlist_text1");
		$arrTemplate["hitlist_text2"] = $this->getText("hitlist_text2");
		$arrTemplate["hitlist_text3"] = $this->getText("hitlist_text3");

		$strTemplateID = $this->objTemplate->readTemplate("/modul_search/".$this->arrElementData["search_template"], "search_hitlist");

		return $strReturn . $this->objTemplate->fillTemplate($arrTemplate, $strTemplateID);
	}

}
?>