<?php
/*"******************************************************************************************************
*   (c) 2004-2006 by MulchProductions, www.mulchprod.de                                                 *
*   (c) 2007-2009 by Kajona, www.kajona.de                                                              *
*       Published under the GNU LGPL v2.1, see /system/licence_lgpl.txt                                 *
*-------------------------------------------------------------------------------------------------------*
*	$Id$                                  *
********************************************************************************************************/

/**
 * Search plugin of the news-module.
 *
 * @package modul_news
 */
class class_modul_news_search extends class_portal implements interface_search_plugin  {

    private $arrTableConfig = array();
    private $arrSearchterm;
    private $strSearchtermRaw = "";
    private $arrHits = array();

    public function  __construct($arrSearchterm, $strSearchtermRaw) {
        parent::__construct();

        $this->arrSearchterm = $arrSearchterm;
        $this->strSearchtermRaw = $strSearchtermRaw;

        $arrSearch = array();

        $arrSearch["news"] = array();
		$arrSearch["news"][_dbprefix_."news"][] = "news_title";
		$arrSearch["news"][_dbprefix_."news"][] = "news_intro";
		$arrSearch["news"][_dbprefix_."news"][] = "news_text";

		$this->arrTableConfig = $arrSearch;
    }


    public function doSearch() {
        $this->searchNews();
        return $this->arrHits;
    }


    /**
	 * searches the news for the given string
	 *
	 */
	private function searchNews() {
        foreach($this->arrTableConfig["news"] as $strTable => $arrColumnConfig) {
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
			 	AND system_id = news_id
			 	AND system_status = 1";

			$arrNews = $this->objDB->getArray($strQuery);

			//Register found news
			if(count($arrNews) > 0) {
				foreach($arrNews as $arrOneNews) {

                    if(!$this->checkLanguage($arrOneNews) || !$this->objRights->rightView($arrOneNews["system_id"]))
                        continue;

					//generate links
					if(isset($this->arrHits[$arrOneNews["system_id"]]["hits"]))
						$this->arrHits[$arrOneNews["system_id"]]["hits"]++;
					else {
    					$this->arrHits[$arrOneNews["system_id"]]["hits"] = 1;
    					$this->arrHits[$arrOneNews["system_id"]]["pagelink"] = getLinkPortal(_news_search_resultpage_, "", "_self", $arrOneNews["news_title"], "newsDetail", "&highlight=".$this->strSearchtermRaw, $arrOneNews["system_id"], "", "", $arrOneNews["news_title"]);
    					$this->arrHits[$arrOneNews["system_id"]]["pagename"] = _news_search_resultpage_;
    					$this->arrHits[$arrOneNews["system_id"]]["description"] = $arrOneNews["news_intro"];
					}
				}
			}
		}
	}

	/**
	 * Checks, if the hit is available on page using the current language
	 *
	 * @param array $arrOneNews
	 * @return bool true, if the hit is visible
	 */
	private function checkLanguage($arrOneNews) {
        $bitReturn = true;

        $strQuery = "SELECT COUNT(*)
                       FROM "._dbprefix_."element_news,
                            "._dbprefix_."news_member,
                            "._dbprefix_."page_element,
                            "._dbprefix_."system
                      WHERE newsmem_news = '".dbsafeString($arrOneNews["system_id"])."'
                        AND news_category = newsmem_category
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