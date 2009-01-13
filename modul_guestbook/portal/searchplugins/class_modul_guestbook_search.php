<?php
/*"******************************************************************************************************
*   (c) 2004-2006 by MulchProductions, www.mulchprod.de                                                 *
*   (c) 2007-2009 by Kajona, www.kajona.de                                                              *
*       Published under the GNU LGPL v2.1, see /system/licence_lgpl.txt                                 *
*-------------------------------------------------------------------------------------------------------*
*	$Id$                             *
********************************************************************************************************/

include_once(_portalpath_."/class_portal.php");
include_once(_portalpath_."/searchplugins/interface_search_plugin.php");

/**
 * Search plugin of the guestbook-module.
 *
 * @package modul_guestbook
 */
class class_modul_guestbook_search extends class_portal implements interface_search_plugin  {

    private $arrTableConfig = array();
    private $arrSearchterm;
    private $strSearchtermRaw = "";
    private $arrHits = array();

    public function  __construct($arrSearchterm, $strSearchtermRaw) {
        parent::__construct();

        $this->arrSearchterm = $arrSearchterm;
        $this->strSearchtermRaw = $strSearchtermRaw;

        $arrSearch = array();

        //Downloads
        $arrSearch["guestbook"] = array();
		$arrSearch["guestbook"][_dbprefix_."guestbook_post"][] = "guestbook_post_name";
		$arrSearch["guestbook"][_dbprefix_."guestbook_post"][] = "guestbook_post_text";

		$this->arrTableConfig = $arrSearch;
    }


    public function doSearch() {
        $this->searchGuestbook();
        return $this->arrHits;
    }


    /**
     * Searches the guestbook-posts
     *
     */
	private function searchGuestbook() {
		foreach($this->arrTableConfig["guestbook"] as $strTable => $arrColumnConfig) {
			$arrWhere = array();
			//Build an or-statemement out of the columns
			foreach($arrColumnConfig as $strColumn) {
				foreach ($this->arrSearchterm as $strOneSeachterm)
                    $arrWhere[] = $strColumn.$strOneSeachterm;
			}
			$strWhere = "( ".implode(" OR ", $arrWhere). " ) ";

			//Query bauen
			$strQuery =
			"SELECT guestbook_post_text, system_id, system_prev_id
			 FROM ".$strTable.",
			 		"._dbprefix_."system
			 WHERE ".$strWhere."
			 	AND system_id  = guestbook_post_id
			 		AND system_status = 1";

			$arrPosts = $this->objDB->getArray($strQuery);

			//Register found posts
			if(count($arrPosts) > 0) {
				foreach($arrPosts as $arrOnePost) {

				    //check, if the post is available on a page using the current language
                    if(!$this->checkLanguage($arrOnePost))
                        continue;

					if(isset($this->arrHits[$arrOnePost["system_id"]]["hits"]))
						$this->arrHits[$arrOnePost["system_id"]]["hits"]++;
					else {
					    //search pv position
					    //number of posts per page
					    $strQuery = "SELECT guestbook_amount
					                   FROM "._dbprefix_."element_guestbook,
					                        "._dbprefix_."system
					                  WHERE guestbook_id = system_prev_id
					                    AND system_id = '".dbsafeString($arrOnePost["system_id"])."'";
					    $arrRow = $this->objDB->getRow($strQuery);
					    $intAmount = $arrRow["guestbook_amount"];
					    include_once(_systempath_."/class_modul_guestbook_post.php");
					    $arrPostsInGB = class_modul_guestbook_post::getPosts($arrOnePost["system_prev_id"], true);
					    $intCounter = 0;
					    foreach($arrPostsInGB as $objOnePostInGb) {
					        $intCounter++;
					        if($objOnePostInGb->getSystemid() == $arrOnePost["system_id"])
					           break;
					    }
					    //calculate pv
					    $intPvPos = ceil($intCounter/$intAmount);
				    	$this->arrHits[$arrOnePost["system_id"]]["hits"] = 1;
					    $this->arrHits[$arrOnePost["system_id"]]["pagelink"] = getLinkPortal(_guestbook_search_resultpage_, "", "_self", _guestbook_search_resultpage_, "", "&highlight=".$this->strSearchtermRaw."&pv=".$intPvPos);
					    $this->arrHits[$arrOnePost["system_id"]]["pagename"] = _guestbook_search_resultpage_;
					    $this->arrHits[$arrOnePost["system_id"]]["description"] = (uniStrlen($arrOnePost["guestbook_post_text"]) < 100 ? $arrOnePost["guestbook_post_text"] : uniSubstr($arrOnePost["guestbook_post_text"], 0, 100)."...") ;
					}
				}
			}
		}
	}



	/**
	 * Checks, if the post is available on page using the current language
	 *
	 * @param array $arrPost
	 * @return bool true, if the post is visible
	 */
	private function checkLanguage($arrPost) {
        $bitReturn = true;


        $strQuery = "SELECT COUNT(*)
                       FROM "._dbprefix_."element_guestbook,
                            "._dbprefix_."page_element,
                            "._dbprefix_."system
                      WHERE guestbook_id = '".dbsafeString($arrPost["system_prev_id"])."'
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