<?php
/*"******************************************************************************************************
*   (c) 2004-2006 by MulchProductions, www.mulchprod.de                                                 *
*   (c) 2007-2015 by Kajona, www.kajona.de                                                              *
*       Published under the GNU LGPL v2.1, see /system/licence_lgpl.txt                                 *
********************************************************************************************************/

namespace Kajona\Search\System;
use Kajona\Pages\System\PagesPageelement;
use Kajona\System\System\Objectfactory;
use Kajona\System\System\SearchResultobjectInterface;
use Kajona\System\System\SystemModule;


/**
 * This class contains a few methods used by the search as little helpers
 *
 * @package module_search
 * @author sidler@mulchprod.de
 *
 * @module search
 * @moduleId _search_module_id_
 */
class SearchCommons extends \Kajona\System\System\Model implements \Kajona\System\System\ModelInterface
{


    /**
     * Returns the name to be used when rendering the current object, e.g. in admin-lists.
     *
     * @return string
     */
    public function getStrDisplayName()
    {
        return "";
    }

    /**
     * Calls the single search-functions, sorts the results and creates the output.
     * Method for portal-searches.
     *
     * @param SearchSearch $objSearch
     *
     * @return SearchResult[]
     */
    public function doPortalSearch($objSearch)
    {
        $objSearch->setStrQuery(trim(uniStrReplace("%", "", $objSearch->getStrQuery())));
        if (uniStrlen($objSearch->getStrQuery()) == 0) {
            return array();
        }

        //create a search object
        $objSearch->setBitPortalObjectFilter(true);

        $arrHits = $this->doIndexedSearch($objSearch);

        $arrReturn = array();
        foreach ($arrHits as $objOneResult) {
            $objInstance = $objOneResult->getObjObject();

            if ($objInstance instanceof PagesPageelement) {
                $objInstance = $objInstance->getConcreteAdminInstance();

                if ($objInstance != null) {
                    $objInstance->loadElementData();
                }
                else {
                    continue;
                }
            }

            $arrUpdatedResults = $objInstance->updateSearchResult($objOneResult);
            if (is_array($arrUpdatedResults)) {
                $arrReturn = array_merge($arrReturn, $arrUpdatedResults);
            }
            else if ($objOneResult != null && $objOneResult instanceof SearchResult) {
                $arrReturn[] = $objOneResult;
            }
        }

        //log the query
        SearchLog::generateLogEntry($objSearch->getStrQuery());

        $arrReturn = $this->mergeDuplicates($arrReturn);

        return $arrReturn;
    }

    /**
     * Calls the single search-functions, sorts the results and creates the output.
     * Method for backend-searches.
     *
     * @param SearchSearch $objSearch
     * @param int $intStart
     * @param int $intEnd
     *
     * @return SearchResult[]
     */
    public function doAdminSearch(SearchSearch $objSearch, $intStart = null, $intEnd = null)
    {

        $arrHits = $this->doIndexedSearch($objSearch, $intStart, $intEnd);

        //if the object is an instance of interface_search_resultobject, the target-link may be updated
        foreach ($arrHits as $objOneResult) {
            if ($objOneResult->getObjObject() instanceof SearchResultobjectInterface) {
                $objOneResult->setStrPagelink($objOneResult->getObjObject()->getSearchAdminLinkForObject());
            }
        }

        return $arrHits;
    }


    /**
     * Merges duplicates in the passed array.
     *
     * @param SearchResult[] $arrResults
     *
     * @return SearchResult[]
     */
    private function mergeDuplicates($arrResults)
    {
        /** @var $arrReturn SearchResult[] */
        $arrReturn = array();

        foreach ($arrResults as $objOneResult) {

            if (isset($arrReturn[$objOneResult->getStrSortHash()])) {
                $objResult = $arrReturn[$objOneResult->getStrSortHash()];
                $objResult->setIntHits($objResult->getIntHits() + 1);
            }
            else {
                $arrReturn[$objOneResult->getStrSortHash()] = $objOneResult;
            }
        }

        return $arrReturn;

    }

    /**
     * @param SearchSearch $objSearch
     * @param null $intStart
     * @param null $intEnd
     *
     * @return SearchResult[]
     */
    public function doIndexedSearch($objSearch, $intStart = null, $intEnd = null)
    {
        $arrHits = array();

        $objParser = new SearchQueryParser();
        $objSearchQuery = $objParser->parseText($objSearch->getStrQuery());
        if ($objSearchQuery == null) {
            return array();
        }

        $objSearchQuery->setMetadataFilter($this->getMetadataFilterFromSearch($objSearch));

        $strQuery = "";
        $arrParameters = array();
        $objSearchQuery->getListQuery($strQuery, $arrParameters);
        $arrSearchResult = $this->objDB->getPArray($strQuery, $arrParameters, $intStart, $intEnd);

        // check view permissions on both, record and matching module
        foreach ($arrSearchResult as $arrOneRow) {
            $objInstance = Objectfactory::getInstance()->getObject($arrOneRow["search_ix_system_id"]);

            $objModule = $objInstance != null ? SystemModule::getModuleByName($objInstance->getArrModule("modul")) : null;
            if ($objInstance != null && $objModule != null && $objInstance->rightView() && $objModule->rightView()) {
                $objResult = new SearchResult();
                $objResult->setObjSearch($objSearch);
                $objResult->setObjObject($objInstance);
                $objResult->setIntScore($arrOneRow["score"]);
                $arrHits[] = $objResult;
            }
        }

        return $arrHits;
    }


    /**
     * Counts the number of hits
     *
     * @param SearchSearch $objSearch
     *
     * @return int
     */
    public function getIndexedSearchCount($objSearch)
    {
        $objParser = new SearchQueryParser();
        $objSearchQuery = $objParser->parseText($objSearch->getStrQuery());

        if ($objSearchQuery == null) {
            return 0;
        }

        $objSearchQuery->setMetadataFilter($this->getMetadataFilterFromSearch($objSearch));

        $strQuery = "";
        $arrParameters = array();
        $objSearchQuery->getCountQuery($strQuery, $arrParameters);
        $arrSearchResult = $this->objDB->getPRow($strQuery, $arrParameters);
        return $arrSearchResult["COUNT(*)"];
    }

    /**
     * @param SearchSearch $objSearch
     *
     * @return SearchMetadataFilter
     */
    private function getMetadataFilterFromSearch($objSearch)
    {
        $objMetadataFilter = new SearchMetadataFilter();
        $objMetadataFilter->setFilterModules($objSearch->getFilterModules());
        $objMetadataFilter->setFilterUser($objSearch->getFilterUser());
        $objMetadataFilter->setFilterChangeStartDate($objSearch->getObjChangeStartdate());
        $objMetadataFilter->setFilterChangeEndDate($objSearch->getObjChangeEnddate());
        $objMetadataFilter->setBitPortalSearch($objSearch->getBitPortalObjectFilter());
        $objMetadataFilter->setStrPortalLang($objSearch->getStrPortalLangFilter());
        return $objMetadataFilter;
    }
}
