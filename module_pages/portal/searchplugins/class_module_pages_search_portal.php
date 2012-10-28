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
class class_module_pages_search_portal implements interface_search_plugin  {

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
        $this->searchPageTags();

        return array_values($this->arrHits);
    }



    /**
     * searches the pages for the given term
     *
     * @return void
     * @internal param mixed $arrTableConfig
     */
	private function searchPages() {
        $objLanguages = new class_module_languages_language();

        $arrWhere = array(
            "page_name LIKE ?",
            "pageproperties_description LIKE ?",
            "pageproperties_keywords LIKE ?",
            "pageproperties_browsername LIKE ?"
        );
        $arrParams = array(
            $objLanguages->getStrPortalLanguage(),
            "%".$this->strSearchterm."%",
            "%".$this->strSearchterm."%",
            "%".$this->strSearchterm."%",
            "%".$this->strSearchterm."%"
        );
        $strWhere = "( ".implode(" OR ", $arrWhere). " ) ";

        //build query
        $strQuery = "SELECT page_name, pageproperties_browsername, pageproperties_description, page_id
                     FROM "._dbprefix_."page,
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
