<?php
/*"******************************************************************************************************
*   (c) 2004-2006 by MulchProductions, www.mulchprod.de                                                 *
*   (c) 2007-2012 by Kajona, www.kajona.de                                                              *
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
class class_module_pages_search_portal extends class_portal implements interface_search_plugin_portal  {

    private $arrTableConfig = array();
    private $strSearchterm = "";

    /**
     * @var class_search_result
     */
    private $arrHits = array();

    public function  __construct($strSearchterm) {
        parent::__construct();

        $this->strSearchterm = $strSearchterm;

        $arrSearch = array();

        //include the list of tables and rows to search
        $arrSearch["pages_elements"] = array();
        $arrSearch["page"] = array();

        $arrFiles = class_resourceloader::getInstance()->getFolderContent("/portal/searchplugins", array(".php"));

        foreach($arrFiles as $strPath => $strOneFile) {
        	if(uniStrpos($strOneFile, "searchdef_pages_" ) !== false) {
        		include_once(_realpath_.$strPath);
        	}
        }

		$this->arrTableConfig = $arrSearch;
    }


    public function doSearch() {

        $this->searchPagesElements();
        $this->searchPages();
        $this->searchPageTags();

        return array_values($this->arrHits);
    }


    /**
     * searches the pages-elements for the specified term
     *
     * @return void
     * @internal param mixed $arrTableConfig
     */
	private function searchPagesElements() {

        $objLanguages = new class_module_languages_language();

		foreach($this->arrTableConfig["pages_elements"] as $strTable => $arrColumnConfig) {

            if(!in_array($strTable, $this->objDB->getTables()))
                continue;

			$arrWhere = array();
            $arrParams = array(
                $objLanguages->getStrPortalLanguage(),
                $objLanguages->getStrPortalLanguage()
            );
			//Build an or-statemement out of the columns
			foreach($arrColumnConfig as $strColumn) {
                $arrWhere[] = $strColumn;
                $arrParams[] = "%".$this->strSearchterm."%";
			}
			$strWhere = "( ".implode(" OR ", $arrWhere). " ) ";

			//Build the query
            $strQuery = "SELECT page_name, pageproperties_browsername, pageproperties_description, page_id
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
						   AND page_element_ph_language = ?
						   AND pageproperties_language = ?
						   AND content_id = page_element_id
						   AND system_status = 1
						   AND   ".$strWhere."
						 ORDER BY page_element_ph_placeholder ASC,
						 		system_sort ASC";

			$arrPages = $this->objDB->getPArray($strQuery, $arrParams);

			//register the found pages
			if(count($arrPages) > 0) {
				foreach($arrPages as $arrOnePage) {
					if(isset($this->arrHits[$arrOnePage["page_name"]])) {
                        $objResult = $this->arrHits[$arrOnePage["page_name"]];
                        $objResult->setIntHits($objResult->getIntHits()+1);
					}
					else {

                        $strText = $arrOnePage["pageproperties_browsername"] != "" ? $arrOnePage["pageproperties_browsername"] : $arrOnePage["page_name"];
                        $objResult = new class_search_result();
                        $objResult->setStrResultId($arrOnePage["page_id"]);
                        $objResult->setStrSystemid($arrOnePage["page_id"]);
                        $objResult->setStrPagelink(getLinkPortal($arrOnePage["page_name"], "", "_self", $strText, "", "&highlight=".urlencode(html_entity_decode($this->strSearchterm, ENT_QUOTES, "UTF-8"))));
                        $objResult->setStrPagename($arrOnePage["page_name"]);
                        $objResult->setStrDescription($arrOnePage["pageproperties_description"]);

                        $this->arrHits[$arrOnePage["page_name"]] = $objResult;
					}
				}
			}
		}
	}


    /**
     * searches the pages for the given term
     *
     * @return void
     * @internal param mixed $arrTableConfig
     */
	private function searchPages() {
        $objLanguages = new class_module_languages_language();

	    foreach($this->arrTableConfig["page"] as $strTable => $arrColumnConfig) {
			$arrWhere = array();
            $arrParams = array(
                $objLanguages->getStrPortalLanguage()
            );
			//Build an or-statemement out of the columns
			foreach($arrColumnConfig as $strColumn) {
				$arrWhere[] = $strColumn;
                $arrParams[] = "%".$this->strSearchterm."%";
			}
			$strWhere = "( ".implode(" OR ", $arrWhere). " ) ";

			//build query
			$strQuery = "SELECT page_name, pageproperties_browsername, pageproperties_description, page_id
						 FROM ".$strTable.",
						      "._dbprefix_."page_properties,
						      "._dbprefix_."system
						 WHERE pageproperties_language = ?
						   AND pageproperties_id = page_id
						   AND system_id = page_id
						   AND system_status = 1
						   AND   ".$strWhere."
						 ORDER BY system_sort ASC";

			$arrPages = $this->objDB->getPArray($strQuery, $arrParams);

			//register the found pages
			if(count($arrPages) > 0) {
				foreach($arrPages as $arrOnePage) {
					//Dont find the master-page!!!
					if($arrOnePage["page_name"] != "master") {
						if(isset($this->arrHits[$arrOnePage["page_name"]])) {
                            $objResult = $this->arrHits[$arrOnePage["page_name"]];
                            $objResult->setIntHits($objResult->getIntHits()+1);
    					}
    					else {
                            $strText = $arrOnePage["pageproperties_browsername"] != "" ? $arrOnePage["pageproperties_browsername"] : $arrOnePage["page_name"];
                            $objResult = new class_search_result();
                            $objResult->setStrResultId($arrOnePage["page_id"]);
                            $objResult->setStrSystemid($arrOnePage["page_id"]);
                            $objResult->setStrPagelink(getLinkPortal($arrOnePage["page_name"], "", "_self", $strText, "", "&highlight=".urlencode(html_entity_decode($this->strSearchterm, ENT_QUOTES, "UTF-8"))));
                            $objResult->setStrPagename($arrOnePage["page_name"]);
                            $objResult->setStrDescription($arrOnePage["pageproperties_description"]);

                            $this->arrHits[$arrOnePage["page_name"]] = $objResult;
    					}
					}
				}
			}
		}
	}

    private function searchPageTags() {
        if(class_module_system_module::getModuleByName("tags") != null) {

            $objLanguages = new class_module_languages_language();

            $arrParams = array(
                $objLanguages->getStrPortalLanguage(),
                $this->strSearchterm
            );

            $strQuery = "SELECT page_name, pageproperties_browsername, pageproperties_description, page_id
                          FROM "._dbprefix_."system,
                               "._dbprefix_."tags_member,
                               "._dbprefix_."tags_tag,
                               "._dbprefix_."page_properties,
                               "._dbprefix_."page
                         WHERE system_module_nr = "._pages_modul_id_."
                           AND pageproperties_language = ?
						   AND pageproperties_id = page_id
						   AND system_id = page_id
                           AND system_id = tags_systemid
                           AND tags_tagid = tags_tag_id
                           AND system_status = 1
                           AND tags_tag_name LIKE ? ";


            $arrPages = $this->objDB->getPArray($strQuery, $arrParams);

            //register the found pages
			if(count($arrPages) > 0) {
				foreach($arrPages as $arrOnePage) {
					//Dont find the master-page!!!
					if($arrOnePage["page_name"] != "master") {
						if(isset($this->arrHits[$arrOnePage["page_name"]])) {
                            $objResult = $this->arrHits[$arrOnePage["page_name"]];
                            $objResult->setIntHits($objResult->getIntHits()+1);
    					}
    					else {
                            $strText = $arrOnePage["pageproperties_browsername"] != "" ? $arrOnePage["pageproperties_browsername"] : $arrOnePage["page_name"];
                            $objResult = new class_search_result();
                            $objResult->setStrResultId($arrOnePage["page_id"]);
                            $objResult->setStrSystemid($arrOnePage["page_id"]);
                            $objResult->setStrPagelink(getLinkPortal($arrOnePage["page_name"], "", "_self", $strText, "", "&highlight=".urlencode(html_entity_decode($this->strSearchterm, ENT_QUOTES, "UTF-8"))));
                            $objResult->setStrPagename($arrOnePage["page_name"]);
                            $objResult->setStrDescription($arrOnePage["pageproperties_description"]);

                            $this->arrHits[$arrOnePage["page_name"]] = $objResult;
    					}
					}
				}
			}

       }
    }

}
