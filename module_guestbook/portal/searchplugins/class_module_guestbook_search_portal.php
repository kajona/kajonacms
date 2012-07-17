<?php
/*"******************************************************************************************************
*   (c) 2004-2006 by MulchProductions, www.mulchprod.de                                                 *
*   (c) 2007-2012 by Kajona, www.kajona.de                                                              *
*       Published under the GNU LGPL v2.1, see /system/licence_lgpl.txt                                 *
*-------------------------------------------------------------------------------------------------------*
*	$Id$                    *
********************************************************************************************************/

/**
 * Search plugin of the guestbook-module.
 *
 * @package module_guestbook
 */
class class_module_guestbook_search_portal implements interface_search_plugin_portal  {

    private $arrTableConfig = array();
    private $strSearchterm;
    private $arrHits = array();

    private $objDB;

    public function  __construct($strSearchterm) {

        $this->strSearchterm = $strSearchterm;
        $arrSearch = array();

        //Downloads
        $arrSearch["guestbook"] = array();
		$arrSearch["guestbook"][_dbprefix_."guestbook_post"][] = "guestbook_post_name LIKE ?";
		$arrSearch["guestbook"][_dbprefix_."guestbook_post"][] = "guestbook_post_text LIKE ?";

		$this->arrTableConfig = $arrSearch;

        $this->objDB = class_carrier::getInstance()->getObjDB();
    }


    public function doSearch() {
        if(class_module_system_module::getModuleByName("guestbook") != null)
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
            $arrParams = array();

			//Build an or-statemement out of the columns
			foreach($arrColumnConfig as $strColumn) {
                $arrWhere[] = $strColumn;
                $arrParams[] = "%".$this->strSearchterm."%";
			}
			$strWhere = "( ".implode(" OR ", $arrWhere). " ) ";

			//Query bauen
			$strQuery ="SELECT system_id
			              FROM ".$strTable.",
			 		           "._dbprefix_."system
			             WHERE ".$strWhere."
			 	           AND system_id  = guestbook_post_id
			 		       AND system_status = 1";

			$arrPosts = $this->objDB->getPArray($strQuery, $arrParams);

			//Register found posts
			if(count($arrPosts) > 0) {

				foreach($arrPosts as $arrOnePost) {

                    $objPost = new class_module_guestbook_post($arrOnePost["system_id"]);
                    $arrDetails = $this->getElementData($objPost);

                    foreach($arrDetails as $arrOnePage) {

                        //check, if the post is available on a page using the current language
                        if(!isset($arrOnePage["page_name"]) || $arrOnePage["page_name"] == "" || !$objPost->rightView())
                            continue;

                        if(isset($this->arrHits[$objPost->getSystemid().$arrOnePage["page_id"]]["hits"])) {
                            $this->arrHits[$objPost->getSystemid().$arrOnePage["page_id"]]["hits"]++;
                        }
                        else {

                            //search pv position
                            $intAmount = $arrOnePage["guestbook_amount"];
                            $arrPostsInGB = class_module_guestbook_post::getPosts($objPost->getPrevId(), true);
                            $intCounter = 0;
                            foreach($arrPostsInGB as $objOnePostInGb) {
                                $intCounter++;
                                if($objOnePostInGb->getSystemid() == $objPost->getSystemid())
                                   break;
                            }
                            //calculate pv
                            $intPvPos = ceil($intCounter/$intAmount);
                            $this->arrHits[$objPost->getSystemid().$arrOnePage["page_id"]]["hits"] = 1;
                            $this->arrHits[$objPost->getSystemid().$arrOnePage["page_id"]]["pagelink"] = getLinkPortal($arrOnePage["page_name"], "", "_self", $arrOnePage["page_name"], "", "&highlight=".html_entity_decode($this->strSearchterm, ENT_QUOTES, "UTF-8")."&pv=".$intPvPos);
                            $this->arrHits[$objPost->getSystemid().$arrOnePage["page_id"]]["pagename"] = $arrOnePage["page_name"];
                            $this->arrHits[$objPost->getSystemid().$arrOnePage["page_id"]]["description"] = uniStrTrim($objPost->getStrGuestbookPostText(), 100);
                        }
                    }
                }
            }
		}
	}

    private function getElementData(class_module_guestbook_post $objPost) {
        $strQuery =  "SELECT page_name, guestbook_amount, page_id
                       FROM "._dbprefix_."element_guestbook,
                            "._dbprefix_."page_element,
                            "._dbprefix_."page,
                            "._dbprefix_."system
                      WHERE guestbook_id = ?
                        AND content_id = page_element_id
                        AND content_id = system_id
                        AND system_prev_id = page_id
                        AND system_status = 1
                        AND page_element_ph_language = ? " ;

        $objLanguages = new class_module_languages_language();

        $arrRows = $this->objDB->getPArray($strQuery, array($objPost->getPrevId(), $objLanguages->getStrPortalLanguage()));

        return $arrRows;
    }

}

