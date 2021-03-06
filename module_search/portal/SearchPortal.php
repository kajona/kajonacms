<?php
/*"******************************************************************************************************
*   (c) 2004-2006 by MulchProductions, www.mulchprod.de                                                 *
*   (c) 2007-2016 by Kajona, www.kajona.de                                                              *
*       Published under the GNU LGPL v2.1, see /system/licence_lgpl.txt                                 *
*-------------------------------------------------------------------------------------------------------*
*	$Id$                                *
********************************************************************************************************/

namespace Kajona\Search\Portal;

use Kajona\Pages\System\PagesPage;
use Kajona\Search\System\SearchCommons;
use Kajona\Search\System\SearchResult;
use Kajona\Search\System\SearchSearch;
use Kajona\System\Portal\PortalController;
use Kajona\System\Portal\PortalInterface;
use Kajona\System\System\ArraySectionIterator;
use Kajona\System\System\Link;
use Kajona\System\System\StringUtil;


/**
 * Portal-Class of the search module. Does all the searching in the database
 *
 * @package module_search
 * @author sidler@mulchprod.de
 *
 * @module search
 * @moduleId _search_module_id_
 */
class SearchPortal extends PortalController implements PortalInterface
{
    private static $INT_MAX_NR_OF_RESULTS = 30;

    private $objSearchSearch;

    /**
     * Constructor
     *
     * @param mixed $arrElementData
     */
    public function __construct($arrElementData = array(), $strSystemid = "")
    {
        parent::__construct($arrElementData, $strSystemid);

        if (isset($arrElementData["search_query_id"]) && $arrElementData["search_query_id"] != "") {
            $this->setAction("search");
        }

        $this->objSearchSearch = new SearchSearch();


        if ($this->getParam("searchterm") != "") {
            $this->objSearchSearch->setStrQuery(htmlToString(urldecode($this->getParam("searchterm")), true));
        }

        if ($this->getParam("searchmodule") != "") {
            $this->objSearchSearch->setFilterModules(array(urldecode($this->getParam("searchmodule"))));
        }

        if (isset($this->arrElementData["search_query_append"]) && $this->arrElementData["search_query_append"] != "") {
            $this->objSearchSearch->setStrQuery($this->arrElementData["search_query_append"]." ".$this->objSearchSearch->getStrQuery());
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
        $arrTemplate = array();

        if ($this->arrElementData["search_query_id"] != "") {
            $this->objSearchSearch = new SearchSearch($this->arrElementData["search_query_id"]);
        }

        $strPage = $this->arrElementData["search_page"];
        if ($strPage == "") {
            $strPage = $this->getPagename();
        }

        $arrTemplate["action"] = Link::getLinkPortalHref($strPage, "", "search");
        $arrTemplate["search_term"] = htmlToString(urldecode($this->getParam("searchterm")), true);
        return $this->objTemplate->fillTemplateFile($arrTemplate, "/module_search/".$this->arrElementData["search_template"], "search_form");
    }


    /**
     * Calls the single search-functions, sorts the results an creates the output
     *
     * @return string
     * @permissions view
     */
    protected function actionSearch()
    {
        $strReturn = "";
        //Read the config
        $arrTemplate = array();
        $arrTemplate["hitlist"] = "";
        $strReturn .= $this->actionList();
        $objSearchCommons = new SearchCommons();

        $this->objSearchSearch->setBitPortalObjectFilter(true);
        $this->objSearchSearch->setStrPortalLangFilter($this->getStrPortalLanguage());

        /** @var $arrHitsSorted SearchResult[] */
        $arrHitsSorted = array_values($objSearchCommons->doPortalSearch($this->objSearchSearch));

        $objArraySectionIterator = new ArraySectionIterator(count($arrHitsSorted));
        $objArraySectionIterator->setIntElementsPerPage($this->arrElementData["search_amount"]);
        $objArraySectionIterator->setPageNumber($this->getParam("pv"));
        $objArraySectionIterator->setArraySection(array_slice($arrHitsSorted, $objArraySectionIterator->calculateStartPos(), $this->arrElementData["search_amount"]));


        //Resize Array to wanted size
        $arrHitsFilter = $this->objToolkit->simplePager(
            $objArraySectionIterator,
            $this->getLang("commons_next"),
            $this->getLang("commons_back"),
            "search",
            ($this->arrElementData["search_page"] != "" ? $this->arrElementData["search_page"] : $this->getPagename()),
            "&searchterm=".urlencode(html_entity_decode(htmlToString(urldecode($this->getParam("searchterm")), true), ENT_COMPAT, "UTF-8")),
            "pv",
            "/module_search/".$this->arrElementData["search_template"]

        );

        /** @var $objHit SearchResult */
        foreach ($objArraySectionIterator as $objHit) {
            if ($objHit->getStrPagename() == "master") {
                continue;
            }

            $objPage = PagesPage::getPageByName($objHit->getStrPagename());
            if ($objPage === null || !$objPage->rightView() || $objPage->getIntRecordStatus() != 1) {
                continue;
            }

            $arrRow = array();
            if (($objHit->getStrPagelink() == "")) {
                $arrRow["page_link"] = Link::getLinkPortal(
                    $objHit->getStrPagename(),
                    "",
                    "_self",
                    $objHit->getStrPagename(),
                    "",
                    "&highlight=".urlencode(html_entity_decode(htmlToString(urldecode($this->getParam("searchterm")), true), ENT_QUOTES, "UTF-8"))."#".StringUtil::toLowerCase(urlencode(html_entity_decode(htmlToString(urldecode($this->getParam("searchterm")), true), ENT_QUOTES, "UTF-8")))
                );
            } else {
                $arrRow["page_link"] = $objHit->getStrPagelink();
            }
            $arrRow["page_description"] = StringUtil::truncate($objHit->getStrDescription(), 200);
            $arrRow["additionaltitle"] = $objHit->getStrAdditionalTitle();
            $arrRow["systemid"] = $objHit->getStrSystemid();
            $arrTemplate["hitlist"] .= $this->objTemplate->fillTemplateFile($arrRow, "/module_search/".$this->arrElementData["search_template"], "search_hitlist_hit", false);
        }

        //Collect global data
        $arrTemplate["search_term"] = htmlToString(urldecode($this->getParam("searchterm")), true);
        $arrTemplate["search_nrresults"] = count($arrHitsSorted);
        $arrTemplate["link_forward"] = $arrHitsFilter["strForward"];
        $arrTemplate["link_back"] = $arrHitsFilter["strBack"];
        $arrTemplate["link_overview"] = $arrHitsFilter["strPages"];

        return $strReturn.$this->objTemplate->fillTemplateFile($arrTemplate, "/module_search/".$this->arrElementData["search_template"], "search_hitlist");
    }



    /**
     * Searches for a passed string
     *
     * @return string
     * @permissions view
     */
    protected function actionDoSearch()
    {
        $strReturn = "";

        $objSearch = new SearchSearch();
        $objSearch->setStrPortalLangFilter($this->getStrPortalLanguage());

        if ($this->getParam("searchterm") != "") {
            $objSearch->setStrQuery(htmlToString(urldecode($this->getParam("searchterm")), true));
        }

        if ($this->getParam("searchmodule") != "") {
            $objSearch->setFilterModules(array(urldecode($this->getParam("searchmodule")), true));
        }

        if ($this->getParam("additionalfilters") != "") {
            $objSearch->setStrQuery(htmlToString(urldecode($this->getParam("additionalfilters")), true)." ".$objSearch->getStrQuery());
        }

        $arrResult = array();
        $objSearchCommons = new SearchCommons();
        if ($objSearch->getStrQuery() != "") {
            $arrResult = $objSearchCommons->doPortalSearch($objSearch);
        }

        $strReturn .= $this->createSearchXML(htmlToString(urldecode($this->getParam("searchterm")), true), $arrResult);

        return $strReturn;
    }


    /**
     * @param $strSearchterm
     * @param SearchResult[] $arrResults
     *
     * @return string
     */
    private function createSearchXML($strSearchterm, $arrResults)
    {
        $strReturn = "";

        $strReturn .=
            "<search>\n"
            ."    <searchterm>".xmlSafeString($strSearchterm)."</searchterm>\n"
            ."    <nrofresults>".count($arrResults)."</nrofresults>\n";


        //And now all results
        $intI = 0;
        $strReturn .= "    <resultset>\n";
        foreach ($arrResults as $objOneResult) {
            $objPage = PagesPage::getPageByName($objOneResult->getStrPagename());
            if ($objPage === null || !$objPage->rightView() || $objPage->getIntRecordStatus() != 1) {
                continue;
            }


            if (++$intI > self::$INT_MAX_NR_OF_RESULTS) {
                break;
            }

            //create a correct link
            if ($objOneResult->getStrPagelink() == "") {
                $objOneResult->setStrPagelink(Link::getLinkPortal($objOneResult->getStrPagename(), "", "_self", $objOneResult->getStrPagename(), "", "&highlight=".$strSearchterm."#".$strSearchterm));
            }

            $strReturn .=
                "        <item>\n"
                ."            <pagename>".$objOneResult->getStrPagename()."</pagename>\n"
                ."            <pagelink>".$objOneResult->getStrPagelink()."</pagelink>\n"
                ."            <score>".$objOneResult->getIntHits()."</score>\n"
                ."            <description>".xmlSafeString(StringUtil::truncate($objOneResult->getStrDescription(), 200))."</description>\n"
                ."        </item>\n";
        }

        $strReturn .= "    </resultset>\n";
        $strReturn .= "</search>";
        return $strReturn;
    }

}
