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

    /**
     * @var class_module_search_search
     */
    private $objSearch = "";

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

        $this->objSearch = $objSearch;


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
                $arrParams[] = "%".$this->objSearch->getStrQuery()."%";
            }

            $strWhere = "( ".implode(" OR ", $arrWhere). " ) ";

            if($this->objSearch->getObjChangeEnddate() != null) {
                $arrParams[] = $this->objSearch->getObjChangeEnddate()->getTimeInOldStyle();
                $strWhere .= "AND system_lm_time <= ? ";
            }

            if($this->objSearch->getObjChangeStartdate() != null) {
                $arrParams[] = $this->objSearch->getObjChangeStartdate()->getTimeInOldStyle();
                $strWhere .= "AND system_lm_time >= ? ";
            }

            //Build the query
            $strQuery = "SELECT content_id
						 FROM ".$strOneTable.",
						      "._dbprefix_."page_element,
						      "._dbprefix_."system
						 WHERE system_id = page_element_id
						   AND content_id = page_element_id
						   AND system_module_nr in (".implode(",", $this->objSearch->getArrFilterModules()).")
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

                    $arrTableInfo = $this->objDB->getColumnsOfTable($strTable);


                    foreach($arrColumns as $strOneColumn) {
                        $bitSkip = false;
                        foreach($arrTableInfo as $arrOneConfig) {
                            if($arrOneConfig["columnName"] == $strOneColumn && uniStrpos("int", $arrOneConfig["columnType"]) !== false) {
                                $bitSkip = true;
                                break;
                            }
                        }

                        if($bitSkip)
                            continue;

                        if(!in_array($strOneColumn, $this->arrTables[$strTable]))
                            $this->arrTables[$strTable][] = $strOneColumn;
                    }

                }
            }
        }

    }
}
