<?php
/*"******************************************************************************************************
*   (c) 2004-2006 by MulchProductions, www.mulchprod.de                                                 *
*   (c) 2007-2014 by Kajona, www.kajona.de                                                              *
*       Published under the GNU LGPL v2.1, see /system/licence_lgpl.txt                                 *
*-------------------------------------------------------------------------------------------------------*
*	$Id$                               *
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
     * @param string $strSearchterm
     *
     * @return class_search_result[]
     */
    public function doPortalSearch($strSearchterm) {
        $strSearchterm = trim(uniStrReplace("%", "", $strSearchterm));
        if(uniStrlen($strSearchterm) == 0)
            return array();

        $objSearch = new class_module_search_search();
        $objSearch->setStrQuery($strSearchterm);

        //log the query
        class_module_search_log::generateLogEntry($strSearchterm);

        //Search for search-plugins
        $arrSearchPlugins = class_resourceloader::getInstance()->getFolderContent("/portal/searchplugins", array(".php"));
        return $this->doSearch($objSearch, $arrSearchPlugins);

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
     * Internal wrapper, triggers the final search based on search-plugins (currently portal only)
     *
     * @param class_module_search_search $objSearch
     * @param interface_search_plugin[] $arrSearchPlugins
     * @param null|callable $objSortFunc
     *
     * @return array|class_search_result[]
     */
    private function doSearch($objSearch, $arrSearchPlugins, $objSortFunc = null) {
        $arrHits = array();

        foreach($arrSearchPlugins as $strOnePlugin) {
            //Check, if not the interface
            if(uniStrpos($strOnePlugin, "searchdef_pages_") === false) {
                $strClassname = str_replace(".php", "", $strOnePlugin);
                /** @var $objPlugin interface_search_plugin */
                $objPlugin = new $strClassname($objSearch);
                if($objPlugin instanceof interface_search_plugin) {
                    $arrHits = array_merge($arrHits, $objPlugin->doSearch());
                }
            }
        }


        $arrHits = $this->mergeDuplicates($arrHits);

        if($objSortFunc == null) {
            $objSortFunc = function (class_search_result $objA, class_search_result $objB) {
                return $objA->getIntHits() < $objB->getIntHits();
            };
        }

        //sort by hits
        uasort($arrHits, $objSortFunc);


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

            $objModule = class_module_system_module::getModuleByName($objInstance->getArrModule("modul"));
            if($objInstance != null && $objModule != null && $objInstance->rightView() && $objModule->rightView()) {
                $objResult = new class_search_result();
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
        $objMetadataFilter->setFilterChangeStartDate($objSearch->getObjChangeStartdate());
        $objMetadataFilter->setFilterChangeEndDate($objSearch->getObjChangeEnddate());
        return $objMetadataFilter;
    }
}
