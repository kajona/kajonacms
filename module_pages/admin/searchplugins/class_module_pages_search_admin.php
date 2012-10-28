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

    private $strSearchterm = "";

    /**
     * @var class_db
     */
    private $objDB;

    /**
     * @var class_search_result
     */
    private $arrHits = array();

    public function  __construct(class_module_search_search $objSearch) {

        $this->strSearchterm = $objSearch->getStrQuery();
        $this->objDB = class_carrier::getInstance()->getObjDB();
    }


    public function doSearch() {
        $this->searchPages();
        return $this->arrHits;
    }



    /**
     * searches the pages for the given term
     *
     * @return void
     * @internal param mixed $arrTableConfig
     */
	private function searchPages() {

        $arrWhere = array(
            "page_name LIKE ?",
            "pageproperties_description LIKE ?",
            "pageproperties_keywords LIKE ?",
            "pageproperties_browsername LIKE ?"
        );
        $arrParams = array(
            "%".$this->strSearchterm."%",
            "%".$this->strSearchterm."%",
            "%".$this->strSearchterm."%",
            "%".$this->strSearchterm."%"
        );

        $strWhere = "( ".implode(" OR ", $arrWhere). " ) ";

        //build query
        $strQuery = "SELECT page_id
                     FROM "._dbprefix_."page,
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
