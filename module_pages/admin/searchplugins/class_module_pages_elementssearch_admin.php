<?php
/*"******************************************************************************************************
*   (c) 2004-2006 by MulchProductions, www.mulchprod.de                                                 *
*   (c) 2007-2012 by Kajona, www.kajona.de                                                              *
*       Published under the GNU LGPL v2.1, see /system/licence_lgpl.txt                                 *
*-------------------------------------------------------------------------------------------------------*
*	$Id$                                 *
********************************************************************************************************/


/**
 * Generic search-plugin to search for content based on the elements' table-definitions.
 *
 * @package module_pages
 * @author sidler@mulchprod.de
 */
class class_module_pages_elementssearch_admin implements interface_search_plugin  {

    private $strSearchterm = "";

    /**
     * @var class_db
     */
    private $objDB;

    /**
     * @var class_search_result
     */
    private $arrHits = array();

    private $arrTables = array();

    public function  __construct(class_module_search_search $objSearch) {

        $this->strSearchterm = $objSearch->getStrQuery();


        $this->objDB = class_carrier::getInstance()->getObjDB();
    }


    public function doSearch() {

        $this->scanElements();

        //build the queries
        foreach($this->arrTables as $strOneTable => $arrColumns) {
            $arrWhere = array();
            $arrParams = array();
            foreach($arrColumns as $strOneColumn) {
                $arrWhere[] = $this->objDB->encloseColumnName($strOneColumn)." LIKE ? ";
                $arrParams[] = "%".$this->strSearchterm."%";
            }

            $strWhere = "( ".implode(" OR ", $arrWhere). " ) ";

            //Build the query
            $strQuery = "SELECT content_id
						 FROM ".$strOneTable.",
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
                $objResult->setStrPagelink(getLinkAdminHref("pages_content", "edit", "&systemid=".$arrOneEntry["content_id"]."&source=search"));
                $this->arrHits[] = $objResult;
            }

        }


        return $this->arrHits;
    }


    public function scanElements() {
        $arrFiles = class_resourceloader::getInstance()->getFolderContent("/admin/elements");

        foreach($arrFiles as $strOneFile) {
            if(uniStripos($strOneFile, "class_element_") !== false) {
                $strClassname = uniSubstr($strOneFile, 0, -4);
                /** @var $objInstance class_element_admin */
                $objInstance = new $strClassname();

                $strTable = $objInstance->getArrModule("table");
                $strColumns = $objInstance->getArrModule("tableColumns");

                if($strTable != "" && $strColumns != "") {
                    $arrColumns = explode(",", $strColumns);

                    if(!isset($this->arrTables[$strTable]))
                        $this->arrTables[$strTable] = array();

                    foreach($arrColumns as $strOneColumn) {
                        if(!in_array($strOneColumn, $this->arrTables[$strTable]))
                            $this->arrTables[$strTable][] = $strOneColumn;
                    }

                }
            }
        }

    }
}
