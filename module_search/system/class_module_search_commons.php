<?php
/*"******************************************************************************************************
*   (c) 2004-2006 by MulchProductions, www.mulchprod.de                                                 *
*   (c) 2007-2012 by Kajona, www.kajona.de                                                              *
*       Published under the GNU LGPL v2.1, see /system/licence_lgpl.txt                                 *
*-------------------------------------------------------------------------------------------------------*
*	$Id: class_modul_search_commons.php 4049 2011-08-03 14:59:29Z sidler $                               *
********************************************************************************************************/

/**
 * This class contains a few methods used by the search as little helpers
 *
 * @package module_search
 * @author sidler@mulchprod.de
 */
class class_module_search_commons extends class_model implements interface_model  {
    

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
     * @return string
     */
    public function getStrDisplayName() {
        return "";
    }

    /**
     * Checks, if a page is valid to be listed in the seach-resultset
     *
     * @param string $strPagename
     * @return bool
     */
    private function isPageValid($strPagename) {
        $objPage = class_module_pages_page::getPageByName($strPagename);

        return (validateSystemid($objPage->getSystemid()) && $objPage->getIntRecordStatus() == 1 && $objPage->rightView());
    }


    /**
     * Calls the single search-functions, sorts the results and creates the output.
     * Method for portal-searches.
     *
     * @param $strSearchterm
     *
     * @return array
     */
	public function doPortalSearch($strSearchterm) {

        $strSearchterm = trim(uniStrReplace("%", "", $strSearchterm));

	    if(uniStrlen($strSearchterm) == 0)
	       return array();
	       
	    //log the query
	    class_module_search_log::generateLogEntry($strSearchterm);

	    $arrSearchtermPlugin = array();
	    $arrSearchtermPlugin[] = $strSearchterm;
        $arrHits = array();

		//Search for search-plugins
        $arrSearchPlugins = class_resourceloader::getInstance()->getFolderContent("/portal/searchplugins", array(".php"));
		foreach($arrSearchPlugins as $strOnePlugin) {
		    //Check, if not the interface
		    if($strOnePlugin != "interface_search_plugin_portal.php" && uniStrpos($strOnePlugin, "searchdef_pages_" ) === false) {
		        $strClassname = str_replace(".php", "", $strOnePlugin);
                /** @var $objPlugin interface_search_plugin_portal */
		        $objPlugin = new $strClassname($strSearchterm);
		        if($objPlugin instanceof interface_search_plugin_portal) {
                    $arrTempResults = $objPlugin->doSearch();

                    //merge found hits with current hits
                    foreach($arrTempResults as $strKey => $arrOneResult) {
                        if(isset($arrHits[$strKey])) {
                            //ok, merge in
                            $arrHits[$strKey]["hits"]++;
                        }
                        else {
                            $arrHits[$strKey] = $arrOneResult;
                        }
                    }
		        }
		    }
		}

		//Sort the hits
		$arrHitsSorted = array();
		foreach($arrHits as $arrOneModule) {
		    //Before returning the page, check if its disabled & the rights are correct
            if(!$this->isPageValid($arrOneModule["pagename"]))
		        continue;

			if(!isset($arrHitsSorted[(string)$arrOneModule["hits"]]))
				$arrHitsSorted[(string)$arrOneModule["hits"]] = $arrOneModule;
			else {
				$intTemp = $arrOneModule["hits"]+0.001;
				while(isset($arrHitsSorted[(string)$intTemp]))
					$intTemp += 0.001;
				$arrHitsSorted[(string)$intTemp] = $arrOneModule;
			}
		}
		//Sort by relevance
		krsort($arrHitsSorted);

		return $arrHitsSorted;

	}

}
