<?php
/*"******************************************************************************************************
*   (c) 2004-2006 by MulchProductions, www.mulchprod.de                                                 *
*   (c) 2007-2015 by Kajona, www.kajona.de                                                              *
*       Published under the GNU LGPL v2.1, see /system/licence_lgpl.txt                                 *
********************************************************************************************************/

/**
 * This class contains a few methods used by the search as little helpers
 *
 * @package module_search
 * @author sidler@mulchprod.de
 *
 * @module search
 * @moduleId _search_module_id_
 */
class class_module_search_commons extends class_model implements interface_model {


    /**
     * Returns the name to be used when rendering the current object, e.g. in admin-lists.
     *
     * @return string
     */
    public function getStrDisplayName() {
        return "";
    }

    /**
     * Calls the single search-functions, sorts the results and creates the output.
     * Method for portal-searches.
     *
     * @param class_module_search_search $objSearch
     *
     * @return class_search_result[]
     */
    public function doPortalSearch($objSearch) {
        $objSearch->setStrQuery(trim(uniStrReplace("%", "", $objSearch->getStrQuery())));
        if(uniStrlen($objSearch->getStrQuery()) == 0)
            return array();

        //create a search object
        $objSearch->setBitPortalObjectFilter(true);

        $arrHits = $this->doIndexedSearch($objSearch);

        $arrReturn = array();
        foreach($arrHits as $objOneResult) {
            $objInstance = $objOneResult->getObjObject();

            if($objInstance instanceof class_module_pages_pageelement) {
                $objInstance = $objInstance->getConcreteAdminInstance();

                if($objInstance != null)
                    $objInstance->loadElementData();
                else
                    continue;
            }

            $arrUpdatedResults = $objInstance->updateSearchResult($objOneResult);
            if(is_array($arrUpdatedResults)) {
                $arrReturn = array_merge($arrReturn, $arrUpdatedResults);
            }
            else if($objOneResult != null && $objOneResult instanceof class_search_result)
                $arrReturn[] = $objOneResult;
        }

        //log the query
        class_module_search_log::generateLogEntry($objSearch->getStrQuery());

        $arrReturn = $this->mergeDuplicates($arrReturn);

        return $arrReturn;
    }

    /**
     * Calls the single search-functions, sorts the results and creates the output.
     * Method for backend-searches.
     *
     * @param class_module_search_search $objSearch
     * @param int $intStart
     * @param int $intEnd
     *
     * @return class_search_result[]
     */
    public function doAdminSearch(class_module_search_search $objSearch, $intStart = null, $intEnd = null) {

        $arrHits = $this->doIndexedSearch($objSearch, $intStart, $intEnd);

        //if the object is an instance of interface_search_resultobject, the target-link may be updated
        foreach($arrHits as $objOneResult) {
            if($objOneResult->getObjObject() instanceof interface_search_resultobject)
                $objOneResult->setStrPagelink($objOneResult->getObjObject()->getSearchAdminLinkForObject());
        }

        return $arrHits;
    }


    /**
     * Merges duplicates in the passed array.
     *
     * @param class_search_result[] $arrResults
     *
     * @return class_search_result[]
     */
    private function mergeDuplicates($arrResults) {
        /** @var $arrReturn class_search_result[] */
        $arrReturn = array();

        foreach($arrResults as $objOneResult) {

            if(isset($arrReturn[$objOneResult->getStrSortHash()])) {
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
     * @param class_module_search_search $objSearch
     * @param null $intStart
     * @param null $intEnd
     *
     * @return class_search_result[]
     */
    public function doIndexedSearch($objSearch, $intStart = null, $intEnd = null) {
        $arrHits = array();

        $objParser = new class_module_search_query_parser();
        $objSearchQuery = $objParser->parseText($objSearch->getStrQuery());
        if($objSearchQuery == null)
            return array();

        $objSearchQuery->setMetadataFilter($this->getMetadataFilterFromSearch($objSearch));

        $strQuery = "";
        $arrParameters = array();
        $objSearchQuery->getListQuery($strQuery, $arrParameters);
        $arrSearchResult = $this->objDB->getPArray($strQuery, $arrParameters, $intStart, $intEnd);

        // check view permissions on both, record and matching module
        foreach($arrSearchResult as $arrOneRow) {
            $objInstance = class_objectfactory::getInstance()->getObject($arrOneRow["search_ix_system_id"]);

            $objModule = $objInstance != null ? class_module_system_module::getModuleByName($objInstance->getArrModule("modul")) : null;
            if($objInstance != null && $objModule != null && $objInstance->rightView() && $objModule->rightView()) {
                $objResult = new class_search_result();
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
     * @param class_module_search_search $objSearch
     *
     * @return int
     */
    public function getIndexedSearchCount($objSearch) {
        $objParser = new class_module_search_query_parser();
        $objSearchQuery = $objParser->parseText($objSearch->getStrQuery());

        if($objSearchQuery == null)
            return 0;

        $objSearchQuery->setMetadataFilter($this->getMetadataFilterFromSearch($objSearch));

        $strQuery = "";
        $arrParameters = array();
        $objSearchQuery->getCountQuery($strQuery, $arrParameters);
        $arrSearchResult = $this->objDB->getPRow($strQuery, $arrParameters);
        return $arrSearchResult["COUNT(*)"];
    }

    /**
     * @param class_module_search_search $objSearch
     *
     * @return class_module_search_metadata_filter
     */
    private function getMetadataFilterFromSearch($objSearch) {
        $objMetadataFilter = new class_module_search_metadata_filter();
        $objMetadataFilter->setFilterModules($objSearch->getFilterModules());
        $objMetadataFilter->setFilterUsers($objSearch->getFilterUsers());
        $objMetadataFilter->setFilterChangeStartDate($objSearch->getObjChangeStartdate());
        $objMetadataFilter->setFilterChangeEndDate($objSearch->getObjChangeEnddate());
        $objMetadataFilter->setBitPortalSearch($objSearch->getBitPortalObjectFilter());
        $objMetadataFilter->setStrPortalLang($objSearch->getStrPortalLangFilter());
        return $objMetadataFilter;
    }
}
