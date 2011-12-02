<?php
/*"******************************************************************************************************
*   (c) 2004-2006 by MulchProductions, www.mulchprod.de                                                 *
*   (c) 2007-2011 by Kajona, www.kajona.de                                                              *
*       Published under the GNU LGPL v2.1, see /system/licence_lgpl.txt                                 *
*-------------------------------------------------------------------------------------------------------*
*	$Id$                                 *
********************************************************************************************************/


/**
 * Search plugin of the pages-module. Searches the configured page-elements and the pages-data.
 * To add page-elements written on your own, create the appropriate array-entries.
 * In detail: Create a row for each table-row, you want to search
 *
 * e.g: $arrSearch["pages_elements"]["table_to_search"][] = "row_to_search"
 *
 * @package module_pages
 */
class class_modul_pages_search extends class_portal implements interface_search_plugin  {

    private $arrTableConfig = array();
    private $arrSearchterm;
    private $strSearchtermRaw = "";
    private $arrHits = array();

    public function  __construct($arrSearchterm, $strSearchtermRaw) {
        parent::__construct();

        $this->arrSearchterm = $arrSearchterm;
        $this->strSearchtermRaw = $strSearchtermRaw;

        $arrSearch = array();

        //include the list of tables and rows to search
        $arrSearch["pages_elements"] = array();
        $arrSearch["page"] = array();

        $objFilesystem = new class_filesystem();
        $arrFiles = $objFilesystem->getFilelist(_portalpath_."/searchplugins/", array(".php"));

        foreach($arrFiles as $strOneFile) {
        	if(uniStrpos($strOneFile, "searchdef_pages_" ) !== false) {
        		include_once(_portalpath_."/searchplugins/".$strOneFile);
        	}
        }


		$this->arrTableConfig = $arrSearch;
    }


    public function doSearch() {
        $this->searchPagesElements();
        $this->searchPages();
        $this->searchPageTags();

        return $this->arrHits;
    }



    /**
	 * searches the pages-elements for the specified term
	 *
	 * @param mixed $arrTableConfig
	 */
	private function searchPagesElements() {

		foreach($this->arrTableConfig["pages_elements"] as $strTable => $arrColumnConfig) {

            if(!in_array($strTable, $this->objDB->getTables()))
                continue;

			$arrWhere = array();
			//Build an or-statemement out of the columns
			foreach($arrColumnConfig as $strColumn) {
			    foreach ($this->arrSearchterm as $strOneSeachterm)
				    $arrWhere[] = $strColumn.$strOneSeachterm;
			}
			$strWhere = "( ".implode(" OR ", $arrWhere). " ) ";

			//Build the query
            $strQuery = "SELECT page_name, pageproperties_browsername, pageproperties_description
						 FROM ".$strTable.",
						      "._dbprefix_."page_element,
						      "._dbprefix_."page,
						      "._dbprefix_."page_properties,
						      "._dbprefix_."element,
						      "._dbprefix_."system
						 WHERE system_prev_id = page_id
						   AND pageproperties_id = page_id
						   AND page_element_ph_element = element_name
						   AND system_id = page_element_id
						   AND page_element_ph_language = '".dbsafeString($this->getPortalLanguage())."'
						   AND pageproperties_language = '".dbsafeString($this->getPortalLanguage())."'
						   AND content_id = page_element_id
						   AND system_status = 1
						   AND   ".$strWhere."
						 ORDER BY page_element_ph_placeholder ASC,
						 		system_sort ASC";

			$arrPages = $this->objDB->getArray($strQuery);

			//register the found pages
			if(count($arrPages) > 0) {
				foreach($arrPages as $arrOnePage) {
					if(isset($this->arrHits[$arrOnePage["page_name"]])) {
						$this->arrHits[$arrOnePage["page_name"]]["hits"]++;
					}
					else {
						$this->arrHits[$arrOnePage["page_name"]]["hits"] = 1;
						$strText = $arrOnePage["pageproperties_browsername"] != "" ? $arrOnePage["pageproperties_browsername"] : $arrOnePage["page_name"];
						$this->arrHits[$arrOnePage["page_name"]]["pagelink"] = getLinkPortal($arrOnePage["page_name"], "", "_self", $strText, "", "&highlight=".html_entity_decode($this->strSearchtermRaw, ENT_QUOTES, "UTF-8"));
						$this->arrHits[$arrOnePage["page_name"]]["pagename"] = $arrOnePage["page_name"];
						$this->arrHits[$arrOnePage["page_name"]]["description"] = $arrOnePage["pageproperties_description"];
					}
				}
			}
		}
	}



	/**
     * searches the pages for the given term
     *
     * @param mixed $arrTableConfig
     */
	private function searchPages() {
	    foreach($this->arrTableConfig["page"] as $strTable => $arrColumnConfig) {
			$arrWhere = array();
			//Build an or-statemement out of the columns
			foreach($arrColumnConfig as $strColumn) {
				$arrWhere[] = $strColumn.$this->arrSearchterm[0];
				$arrWhere[] = $strColumn.$this->arrSearchterm[1];
			}
			$strWhere = "( ".implode(" OR ", $arrWhere). " ) ";

			//build query
			$strQuery = "SELECT page_name, pageproperties_browsername, pageproperties_description
						 FROM ".$strTable.",
						      "._dbprefix_."page_properties,
						      "._dbprefix_."system
						 WHERE pageproperties_language = '".$this->objDB->dbsafeString($this->getPortalLanguage())."'
						   AND pageproperties_id = page_id
						   AND system_id = page_id
						   AND system_status = 1
						   AND   ".$strWhere."
						 ORDER BY system_sort ASC";

			$arrPages = $this->objDB->getArray($strQuery);

			//register the found pages
			if(count($arrPages) > 0) {
				foreach($arrPages as $arrOnePage) {
					//Dont find the master-page!!!
					if($arrOnePage["page_name"] != "master") {
						if(isset($this->arrHits[$arrOnePage["page_name"]])) {
    						$this->arrHits[$arrOnePage["page_name"]]["hits"]++;
    					}
    					else {
    						$this->arrHits[$arrOnePage["page_name"]]["hits"] = 1;
    						$strText = $arrOnePage["pageproperties_browsername"] != "" ? $arrOnePage["pageproperties_browsername"] : $arrOnePage["page_name"];
                            $this->arrHits[$arrOnePage["page_name"]]["pagelink"] = getLinkPortal($arrOnePage["page_name"], "", "_self", $strText, "", "&highlight=".$this->strSearchtermRaw);
    						$this->arrHits[$arrOnePage["page_name"]]["pagename"] = $arrOnePage["page_name"];
    						$this->arrHits[$arrOnePage["page_name"]]["description"] = $arrOnePage["pageproperties_description"];
    					}
					}
				}
			}
		}
	}

    private function searchPageTags() {
        if(class_module_system_module::getModuleByName("tags") != null) {

            $arrWhere = array();
		    //Build an or-statemement out of the columns
            foreach ($this->arrSearchterm as $strOneSeachterm)
                $arrWhere[] = "tags_tag_name ".$strOneSeachterm;

		    $strWhere = "( ".implode(" OR ", $arrWhere). " ) ";

            $strQuery = "SELECT page_name, pageproperties_browsername, pageproperties_description
                          FROM "._dbprefix_."system,
                               "._dbprefix_."tags_member,
                               "._dbprefix_."tags_tag,
                               "._dbprefix_."page_properties,
                               "._dbprefix_."page
                         WHERE system_module_nr = "._pages_modul_id_."
                           AND pageproperties_language = '".$this->objDB->dbsafeString($this->getPortalLanguage())."'
						   AND pageproperties_id = page_id
						   AND system_id = page_id
                           AND system_id = tags_systemid
                           AND tags_tagid = tags_tag_id
                           AND system_status = 1
                           AND ".$strWhere."
                           AND tags_attribute = '".  dbsafeString($this->getPortalLanguage())."'  ";


            $arrPages = $this->objDB->getArray($strQuery);

            //register the found pages
			if(count($arrPages) > 0) {
				foreach($arrPages as $arrOnePage) {
					//Dont find the master-page!!!
					if($arrOnePage["page_name"] != "master") {
						if(isset($this->arrHits[$arrOnePage["page_name"]])) {
    						$this->arrHits[$arrOnePage["page_name"]]["hits"]++;
    					}
    					else {
    						$this->arrHits[$arrOnePage["page_name"]]["hits"] = 1;
    						$strText = $arrOnePage["pageproperties_browsername"] != "" ? $arrOnePage["pageproperties_browsername"] : $arrOnePage["page_name"];
                            $this->arrHits[$arrOnePage["page_name"]]["pagelink"] = getLinkPortal($arrOnePage["page_name"], "", "_self", $strText, "", "&highlight=".$this->strSearchtermRaw);
    						$this->arrHits[$arrOnePage["page_name"]]["pagename"] = $arrOnePage["page_name"];
    						$this->arrHits[$arrOnePage["page_name"]]["description"] = $arrOnePage["pageproperties_description"];
    					}
					}
				}
			}

       }
    }

}
