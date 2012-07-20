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
class class_module_pages_elementsearch_portal implements interface_search_plugin  {

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

    public function  __construct($strSearchterm) {
        $this->strSearchterm = $strSearchterm;
        $this->objDB = class_carrier::getInstance()->getObjDB();
    }


    public function doSearch() {

        $this->scanElements();

        $objLanguages = new class_module_languages_language();

        //build the queries
        foreach($this->arrTables as $strOneTable => $arrColumns) {
            $arrWhere = array();
            $arrParams = array(
                $objLanguages->getStrPortalLanguage(),
                $objLanguages->getStrPortalLanguage()
            );
            foreach($arrColumns as $strOneColumn) {
                $arrWhere[] = $this->objDB->encloseColumnName($strOneColumn)." LIKE ? ";
                $arrParams[] = "%".$this->strSearchterm."%";
            }

            $strWhere = "( ".implode(" OR ", $arrWhere). " ) ";

            //Build the query

            $strQuery = "SELECT page_name, pageproperties_browsername, pageproperties_description, page_id
						 FROM ".$strOneTable.",
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
						   AND   ".$strWhere."";

            $arrElements = $this->objDB->getPArray($strQuery, $arrParams);

            foreach($arrElements as $arrOnePage) {
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
