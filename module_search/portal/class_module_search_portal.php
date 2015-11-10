<?php
/*"******************************************************************************************************
*   (c) 2004-2006 by MulchProductions, www.mulchprod.de                                                 *
*   (c) 2007-2015 by Kajona, www.kajona.de                                                              *
*       Published under the GNU LGPL v2.1, see /system/licence_lgpl.txt                                 *
*-------------------------------------------------------------------------------------------------------*
*	$Id$                                *
********************************************************************************************************/

/**
 * Portal-Class of the search module. Does all the searching in the database
 *
 * @package module_search
 * @author sidler@mulchprod.de
 *
 * @module search
 * @moduleId _search_module_id_
 */
class class_module_search_portal extends class_portal_controller implements interface_portal {

    private $objSearchSearch;

    /**
     * Constructor
     *
     * @param mixed $arrElementData
     */
    public function __construct($arrElementData = array(), $strSystemid = "") {
        parent::__construct($arrElementData, $strSystemid);

        if(isset($arrElementData["search_query_id"]) && $arrElementData["search_query_id"] != "")
            $this->setAction("search");

        $this->objSearchSearch = new class_module_search_search();


        if($this->getParam("searchterm") != "") {
            $this->objSearchSearch->setStrQuery(htmlToString(urldecode($this->getParam("searchterm")), true));
        }
    }


    /**
     * Creates a search form using the template specified in the admin
     *
     * @return string
     * @permissions view
     */
    protected function actionList()
    {
        $strTemplateID = $this->objTemplate->readTemplate("/module_search/" . $this->arrElementData["search_template"], "search_form");

        $arrTemplate = array();

        if ($this->arrElementData["search_query_id"] != "") {
            $this->objSearchSearch = new class_module_search_search($this->arrElementData["search_query_id"]);
        }

        $strPage = $this->arrElementData["search_page"];
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

        $this->objSearchSearch->setBitPortalObjectFilter(true);
        $this->objSearchSearch->setStrPortalLangFilter($this->getStrPortalLanguage());

        /** @var $arrHitsSorted class_search_result[] */
        $arrHitsSorted = $objSearchCommons->doPortalSearch($this->objSearchSearch);

        //Resize Array to wanted size
        $arrHitsFilter = $this->objToolkit->pager(
            $this->arrElementData["search_amount"],
            ($this->getParam("pv") != "" ? (int)$this->getParam("pv") : 1),
            $this->getLang("commons_next"),
            $this->getLang("commons_back"),
            "search",
            ($this->arrElementData["search_page"] != "" ? $this->arrElementData["search_page"] : $this->getPagename()),
            $arrHitsSorted,
            "&searchterm=".urlencode(html_entity_decode($this->objSearchSearch->getStrQuery(), ENT_COMPAT, "UTF-8"))
        );

        $strRowTemplateID = $this->objTemplate->readTemplate("/module_search/".$this->arrElementData["search_template"], "search_hitlist_hit");

        /** @var $objHit class_search_result */
        foreach($arrHitsFilter["arrData"] as $objHit) {

            if($objHit->getStrPagename() == "master")
                continue;

            $objPage = class_module_pages_page::getPageByName($objHit->getStrPagename());
            if($objPage === null || !$objPage->rightView() || $objPage->getIntRecordStatus() != 1)
                continue;

            $arrRow = array();
            if(($objHit->getStrPagelink() == ""))
                $arrRow["page_link"] = getLinkPortal(
                    $objHit->getStrPagename(),
                    "",
                    "_self",
                    $objHit->getStrPagename(),
                    "",
                    "&highlight=".urlencode(html_entity_decode($this->objSearchSearch->getStrQuery(), ENT_QUOTES, "UTF-8"))."#".uniStrtolower(urlencode(html_entity_decode($this->objSearchSearch->getStrQuery(), ENT_QUOTES, "UTF-8")))
                );
            else
                $arrRow["page_link"] = $objHit->getStrPagelink();
            $arrRow["page_description"] = uniStrTrim($objHit->getStrDescription(), 200);
            $arrTemplate["hitlist"] .= $this->objTemplate->fillTemplate($arrRow, $strRowTemplateID, false);
        }

        //Collect global data
        $arrTemplate["search_term"] = $this->objSearchSearch->getStrQuery();
        $arrTemplate["search_nrresults"] = count($arrHitsSorted);
        $arrTemplate["link_forward"] = $arrHitsFilter["strForward"];
        $arrTemplate["link_back"] = $arrHitsFilter["strBack"];
        $arrTemplate["link_overview"] = $arrHitsFilter["strPages"];

        $strTemplateID = $this->objTemplate->readTemplate("/module_search/".$this->arrElementData["search_template"], "search_hitlist");

        return $strReturn.$this->fillTemplate($arrTemplate, $strTemplateID);
    }

}
