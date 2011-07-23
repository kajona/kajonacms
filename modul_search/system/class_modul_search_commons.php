<?php
/*"******************************************************************************************************
*   (c) 2004-2006 by MulchProductions, www.mulchprod.de                                                 *
*   (c) 2007-2011 by Kajona, www.kajona.de                                                              *
*       Published under the GNU LGPL v2.1, see /system/licence_lgpl.txt                                 *
*-------------------------------------------------------------------------------------------------------*
*	$Id$                               *
********************************************************************************************************/

/**
 * This class contains a few methods used by the search as little helpers
 *
 * @package modul_search
 */
class class_modul_search_commons extends class_model implements interface_model  {
    

    /**
     * Constructor to create a valid object
     *
     * @param string $strSystemid (use "" on new objects)
     */
    public function __construct($strSystemid = "") {
        $arrModul = array();
        $arrModul["name"] 				= "modul_search";
		$arrModul["author"] 			= "sidler@mulchprod.de";
		$arrModul["moduleId"] 			= _suche_modul_id_;
		$arrModul["table"]       		= "";
		$arrModul["modul"]				= "search";

		//base class
		parent::__construct($arrModul, $strSystemid);

		//init current object
		if($strSystemid != "")
		    $this->initObject();
    }


     /**
     * @see class_model::getObjectTables();
     * @return array
     */
    protected function getObjectTables() {
        return array();
    }

    /**
     * @see class_model::getObjectDescription();
     * @return string
     */
    protected function getObjectDescription() {
        return "";
    }


    /**
     * Initalises the current object, if a systemid was given
     * NOT YET IMPLEMENTED
     *
     */
    public function initObject() {

    }

    /**
     * Updates the current object to the database
     * NOT YET IMPLEMENTED
     *
     */
    protected function updateStateToDb() {
    
    }

    /**
     * Checks, if a page is valid to be listet in the seach-resultset
     *
     * @param string $strPagename
     * @return bool
     */
    private function isPageValid($strPagename) {
        $strQuery = "SELECT * FROM "._dbprefix_."page, "._dbprefix_."system
		                  WHERE page_name = ?
		                  AND system_id = page_id";
		$arrPage = $this->objDB->getPRow($strQuery, array($strPagename) );
		return (count($arrPage) < 1 || $arrPage["system_status"] != 1 || !$this->objRights->rightView($arrPage["system_id"]));
    }


    /**
	 * Calls the single search-functions, sorts the results and creates the output
	 *
	 * @return array
	 */
	public function doSearch($strSearchterm) {

	    if(uniStrlen($strSearchterm) == 0)
	       return array();
	       
	    //log the query
	    class_modul_search_log::generateLogEntry($strSearchterm);

	    $arrSearchtermPlugin = array();
	    $arrSearchtermPlugin[] = " LIKE ('%".dbsafeString($strSearchterm, false)."%')  ";
	    $arrSearchtermPlugin[] = " LIKE ('%".dbsafeString(html_entity_decode($strSearchterm, ENT_COMPAT, "UTF-8"), false)."%')  ";
        $arrHits = array();

		//Search for search-plugins
		$objFilesystem = new class_filesystem();
		$arrSearchPlugins = $objFilesystem->getFilelist(_portalpath_."/searchplugins/", ".php");
		foreach($arrSearchPlugins as $strOnePlugin) {
		    //Check, if not the interface
		    if($strOnePlugin != "interface_search_plugin.php" && uniStrpos($strOnePlugin, "searchdef_pages_" ) === false) {
		        include_once(_portalpath_."/searchplugins/".$strOnePlugin);
		        $strClassname = str_replace(".php", "", $strOnePlugin);
		        $objPlugin = new $strClassname($arrSearchtermPlugin, $strSearchterm);
		        if($objPlugin instanceof interface_search_plugin) {
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
            if($this->isPageValid($arrOneModule["pagename"]))
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
?>