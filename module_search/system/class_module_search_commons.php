<?php
/*"******************************************************************************************************
*   (c) 2004-2006 by MulchProductions, www.mulchprod.de                                                 *
*   (c) 2007-2012 by Kajona, www.kajona.de                                                              *
*       Published under the GNU LGPL v2.1, see /system/licence_lgpl.txt                                 *
*-------------------------------------------------------------------------------------------------------*
*	$Id$                               *
********************************************************************************************************/

/**
 * This class contains a few methods used by the search as little helpers
 *
 * @package module_search
 * @author sidler@mulchprod.de
 */
class class_module_search_commons extends class_model implements interface_model {


    /**
     * Constructor to create a valid object
     *
     * @param string $strSystemid (use "" on new objects)
     */
    public function __construct($strSystemid = "") {
        $this->setArrModuleEntry("modul", "search");
        $this->setArrModuleEntry("moduleId", _search_module_id_);

        parent::__construct($strSystemid);
    }

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
     * @param $strSearchterm
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
     * Method for portal-searches.
     *
     * @param $objSearch class_module_search_search
     *
     * @return class_search_result[]
     */
    public function doAdminSearch(class_module_search_search $objSearch) {
        $strSearchterm = trim(uniStrReplace("%", "", $objSearch->getStrQuery()));
        if(uniStrlen($strSearchterm) == 0)
            return array();

        //Search for search-plugins
        $arrSearchPlugins = class_resourceloader::getInstance()->getFolderContent("/admin/searchplugins", array(".php"));

        $objSearchFunc = function(class_search_result $objA, class_search_result $objB) {
            //first by module
            if($objA->getObjObject() instanceof class_model && $objB->getObjObject() instanceof class_model) {
                $intCmp = strcmp($objA->getObjObject()->getArrModule("modul"), $objB->getObjObject()->getArrModule("modul"));

                if($intCmp != 0)
                    return $intCmp;
                else
                    return $objA->getIntHits() < $objB->getIntHits();
            }
            return $objA->getIntHits() < $objB->getIntHits();
        };


        return $this->doSearch($objSearch, $arrSearchPlugins, $objSearchFunc);

    }

    /**
     * Internal wrapper, triggers the final search.
     *
     * @param $objSearch class_module_search_search
     * @param $arrSearchPlugins
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

        if($objSortFunc == null)
            $objSortFunc = function(class_search_result $objA, class_search_result $objB) {
                return $objA->getIntHits() < $objB->getIntHits();
            };

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

}
