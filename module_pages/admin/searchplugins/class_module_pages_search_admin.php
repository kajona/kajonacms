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
 * @author sidler@mulchprod.de
 */
class class_module_pages_search_admin implements interface_search_plugin  {

    private $arrTableConfig = array();
    private $strSearchterm = "";

    /**
     * @var class_db
     */
    private $objDB;

    /**
     * @var class_search_result
     */
    private $arrHits = array();

    public function  __construct($strSearchterm) {

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
        $this->objDB = class_carrier::getInstance()->getObjDB();
    }


    public function doSearch() {

        $this->searchPagesElements();
        $this->searchPages();

        return array_values($this->arrHits);
    }


    /**
     * searches the pages-elements for the specified term
     *
     * @return void
     * @internal param mixed $arrTableConfig
     */
	private function searchPagesElements() {

		foreach($this->arrTableConfig["pages_elements"] as $strTable => $arrColumnConfig) {

            if(!in_array($strTable, $this->objDB->getTables()))
                continue;

			$arrWhere = array();
            $arrParams = array();
			//Build an or-statemement out of the columns
			foreach($arrColumnConfig as $strColumn) {
                $arrWhere[] = $strColumn;
                $arrParams[] = "%".$this->strSearchterm."%";
			}
			$strWhere = "( ".implode(" OR ", $arrWhere). " ) ";

			//Build the query
            $strQuery = "SELECT content_id
						 FROM ".$strTable.",
						      "._dbprefix_."page_element,
						      "._dbprefix_."system
						 WHERE system_id = page_element_id
						   AND content_id = page_element_id
						   AND   ".$strWhere."";

			$arrElements = $this->objDB->getPArray($strQuery, $arrParams);

            foreach($arrElements as $arrOneEntry) {
                $objPost = class_objectfactory::getInstance()->getObject($arrOneEntry["content_id"]);
                $objResult = new class_search_result();
                $objResult->setObjObject($objPost);
                $objResult->setStrPagelink(getLinkAdminHref("pages_content", "editElement", "&systemid=".$arrOneEntry["content_id"]));
                $this->arrHits[] = $objResult;
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

	    foreach($this->arrTableConfig["page"] as $strTable => $arrColumnConfig) {
			$arrWhere = array();
            $arrParams = array();

			//Build an or-statemement out of the columns
			foreach($arrColumnConfig as $strColumn) {
				$arrWhere[] = $strColumn;
                $arrParams[] = "%".$this->strSearchterm."%";
			}
			$strWhere = "( ".implode(" OR ", $arrWhere). " ) ";

			//build query
			$strQuery = "SELECT page_id
						 FROM ".$strTable.",
						      "._dbprefix_."page_properties,
						      "._dbprefix_."system
						 WHERE pageproperties_id = page_id
						   AND system_id = page_id
						   AND system_status = 1
						   AND   ".$strWhere."";

            $arrElements = $this->objDB->getPArray($strQuery, $arrParams);
            foreach($arrElements as $arrOneEntry) {
                $objPost = class_objectfactory::getInstance()->getObject($arrOneEntry["page_id"]);
                $objResult = new class_search_result();
                $objResult->setObjObject($objPost);
                $this->arrHits[] = $objResult;
            }
		}
	}



}
