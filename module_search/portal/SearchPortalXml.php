<?php
/*"******************************************************************************************************
*   (c) 2004-2006 by MulchProductions, www.mulchprod.de                                                 *
*   (c) 2007-2016 by Kajona, www.kajona.de                                                              *
*       Published under the GNU LGPL v2.1, see /system/licence_lgpl.txt                                 *
*-------------------------------------------------------------------------------------------------------*
*	$Id$						    *
********************************************************************************************************/

namespace Kajona\Search\Portal;

use Kajona\Pages\System\PagesPage;
use Kajona\Search\System\SearchCommons;
use Kajona\Search\System\SearchResult;
use Kajona\Search\System\SearchSearch;
use Kajona\System\Portal\PortalController;
use Kajona\System\Portal\XmlPortalInterface;


/**
 * Portal-class of the search.
 * Serves xml-requests, e.g. generates search results
 *
 * @package module_search
 * @author sidler@mulchprod.de
 *
 * @module search
 * @moduleId _search_module_id_
 */
class SearchPortalXml extends PortalController implements XmlPortalInterface
{

    private static $INT_MAX_NR_OF_RESULTS = 30;

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

        $arrResult = array();
        $objSearchCommons = new SearchCommons();
        if ($objSearch->getStrQuery() != "") {
            $arrResult = $objSearchCommons->doPortalSearch($objSearch);
        }

        $strReturn .= $this->createSearchXML($objSearch->getStrQuery(), $arrResult);

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
                $objOneResult->setStrPagelink(getLinkPortal($objOneResult->getStrPagename(), "", "_self", $objOneResult->getStrPagename(), "", "&highlight=".$strSearchterm."#".$strSearchterm));
            }

            $strReturn .=
                "        <item>\n"
                ."            <pagename>".$objOneResult->getStrPagename()."</pagename>\n"
                ."            <pagelink>".$objOneResult->getStrPagelink()."</pagelink>\n"
                ."            <score>".$objOneResult->getIntHits()."</score>\n"
                ."            <description>".xmlSafeString(uniStrTrim($objOneResult->getStrDescription(), 200))."</description>\n"
                ."        </item>\n";
        }

        $strReturn .= "    </resultset>\n";
        $strReturn .= "</search>";
        return $strReturn;
    }
}
