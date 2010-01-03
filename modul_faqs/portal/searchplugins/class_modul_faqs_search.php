<?php
/*"******************************************************************************************************
*   (c) 2004-2006 by MulchProductions, www.mulchprod.de                                                 *
*   (c) 2007-2010 by Kajona, www.kajona.de                                                              *
*       Published under the GNU LGPL v2.1, see /system/licence_lgpl.txt                                 *
*-------------------------------------------------------------------------------------------------------*
*	$Id$                                *
********************************************************************************************************/

/**
 * Search plugin of the faqs-module.
 *
 * @package modul_faqs
 */
class class_modul_faqs_search extends class_portal implements interface_search_plugin  {

    private $arrTableConfig = array();
    private $arrSearchterm;
    private $strSearchtermRaw = "";
    private $arrHits = array();

    public function __construct($arrSearchterm, $strSearchtermRaw) {
        parent::__construct();

        $this->arrSearchterm = $arrSearchterm;
        $this->strSearchtermRaw = $strSearchtermRaw;

        $arrSearch = array();
        $arrSearch["faqs"] = array();
		$arrSearch["faqs"][_dbprefix_."faqs"][] = "faqs_question";
		$arrSearch["faqs"][_dbprefix_."faqs"][] = "faqs_answer";

		$this->arrTableConfig = $arrSearch;
    }


    public function doSearch() {
        $this->searchFaqs();
        return $this->arrHits;
    }


    /**
	 * searches the faqs for the given string
	 *
	 */
	private function searchFaqs() {
        foreach($this->arrTableConfig["faqs"] as $strTable => $arrColumnConfig) {
			$arrWhere = array();
			//Build an or-statemement out of the columns
			foreach($arrColumnConfig as $strColumn) {
				foreach ($this->arrSearchterm as $strOneSeachterm)
                    $arrWhere[] = $strColumn.$strOneSeachterm;
			}
			$strWhere = "( ".implode(" OR ", $arrWhere). " ) ";

			//Query bauen
			$strQuery =
			"SELECT *
			 FROM ".$strTable.",
			 		"._dbprefix_."system
			 WHERE ".$strWhere."
			 	AND system_id = faqs_id
			 	AND system_status = 1";

			$arrFaqs = $this->objDB->getArray($strQuery);

			//Register found faqs
			if(count($arrFaqs) > 0) {
				foreach($arrFaqs as $arrOneFaq) {

				    if(!$this->checkLanguage($arrOneFaq) || !$this->objRights->rightView($arrOneFaq["system_id"]))
				        continue;

					//generate links
					if(isset($this->arrHits[$arrOneFaq["system_id"]]["hits"]))
						$this->arrHits[$arrOneFaq["system_id"]]["hits"]++;
					else {
    					$this->arrHits[$arrOneFaq["system_id"]]["hits"] = 1;
    					$this->arrHits[$arrOneFaq["system_id"]]["pagelink"] = getLinkPortal(_faqs_search_resultpage_, "", "_self", _faqs_search_resultpage_, "", "&highlight=".$this->strSearchtermRaw);
    					$this->arrHits[$arrOneFaq["system_id"]]["pagename"] = _faqs_search_resultpage_;
    					$this->arrHits[$arrOneFaq["system_id"]]["description"] = $arrOneFaq["faqs_question"];
					}
				}
			}
		}
	}


	/**
	 * Checks, if the hit is available on page using the current language
	 *
	 * @param array $arrOneFaq
	 * @return bool true, if the post is visible
	 */
	private function checkLanguage($arrOneFaq) {
        $bitReturn = true;

        //if theres an element showing all cats, everything is donw
        $strQuery = "SELECT COUNT(*)
                       FROM "._dbprefix_."element_faqs,
                            "._dbprefix_."page_element,
                            "._dbprefix_."system
                      WHERE faqs_category = '0'
                        AND content_id = page_element_id
                        AND content_id = system_id
                        AND system_status = 1
                        AND page_element_placeholder_language = '".dbsafeString($this->getPortalLanguage())."' ";

        $arrRow = $this->objDB->getRow($strQuery);
        if(isset($arrRow["COUNT(*)"]) && (int)$arrRow["COUNT(*)"] >= 1)
            return true;


        $strQuery = "SELECT COUNT(*)
                       FROM "._dbprefix_."element_faqs,
                            "._dbprefix_."faqs_member,
                            "._dbprefix_."page_element,
                            "._dbprefix_."system
                      WHERE faqsmem_faq = '".dbsafeString($arrOneFaq["system_id"])."'
                        AND faqs_category = faqsmem_category
                        AND content_id = page_element_id
                        AND content_id = system_id
                        AND system_status = 1
                        AND page_element_placeholder_language = '".dbsafeString($this->getPortalLanguage())."' " ;

        $arrRow = $this->objDB->getRow($strQuery);


        if(isset($arrRow["COUNT(*)"]) && (int)$arrRow["COUNT(*)"] >= 1)
            return true;

        return false;
	}
}
?>