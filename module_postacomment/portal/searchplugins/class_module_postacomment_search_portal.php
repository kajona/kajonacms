<?php
/*"******************************************************************************************************
*   (c) 2004-2006 by MulchProductions, www.mulchprod.de                                                 *
*   (c) 2007-2012 by Kajona, www.kajona.de                                                              *
*       Published under the GNU LGPL v2.1, see /system/licence_lgpl.txt                                 *
*-------------------------------------------------------------------------------------------------------*
*	$Id: class_module_guestbook_search_portal.php 4647 2012-05-11 14:37:22Z sidler $                    *
********************************************************************************************************/

/**
 * Search plugin of the postacomment-module.
 *
 * @package module_postacomment
 */
class class_module_postacomment_search_portal implements interface_search_plugin_portal  {

    private $arrTableConfig = array();
    private $strSearchterm;
    private $arrHits = array();

    private $objDB;

    public function  __construct($strSearchterm) {

        $this->strSearchterm = $strSearchterm;
        $arrSearch = array();

        //Downloads
        $arrSearch["postacomment"] = array();
		$arrSearch["postacomment"][_dbprefix_."postacomment"][] = "postacomment_title LIKE ?";
		$arrSearch["postacomment"][_dbprefix_."postacomment"][] = "postacomment_comment LIKE ?";

		$this->arrTableConfig = $arrSearch;

        $this->objDB = class_carrier::getInstance()->getObjDB();
    }


    public function doSearch() {
        if(class_module_system_module::getModuleByName("postacomment") != null)
            $this->searchPostacomment();

        return $this->arrHits;
    }


    /**
     * Searches the guestbook-posts
     *
     */
	private function searchPostacomment() {

        $objLanguages = new class_module_languages_language();

		foreach($this->arrTableConfig["postacomment"] as $strTable => $arrColumnConfig) {
			$arrWhere = array();
            $arrParams = array();

			//Build an or-statemement out of the columns
			foreach($arrColumnConfig as $strColumn) {
                $arrWhere[] = $strColumn;
                $arrParams[] = "%".$this->strSearchterm."%";
			}
			$strWhere = "( ".implode(" OR ", $arrWhere). " ) ";
            $arrParams[] = $objLanguages->getStrPortalLanguage();

			//Query bauen
			$strQuery ="SELECT pacsys.system_id, page_name, page_id, ".$this->objDB->encloseColumnName("int1")."
			              FROM ".$strTable.",
			 		           "._dbprefix_."system AS pacsys,
			 		           "._dbprefix_."system AS pagesys,
			 		           "._dbprefix_."system AS elementsys,
			 		           "._dbprefix_."page_element,
			 		           "._dbprefix_."element_universal,
                               "._dbprefix_."page
			             WHERE ".$strWhere."
			 	           AND pacsys.system_id = postacomment_id
			 		       AND pacsys.system_status = 1
			 		       AND postacomment_page = page_id
			 		       AND pagesys.system_id = page_id
			 		       AND pagesys.system_status = 1
			 		       AND elementsys.system_prev_id = page_id
			 		       AND elementsys.system_id = content_id
			 		       AND page_element_id = content_id
                           AND postacomment_language = ? ";

			$arrPosts = $this->objDB->getPArray($strQuery, $arrParams);

			//Register found posts
			if(count($arrPosts) > 0) {

				foreach($arrPosts as $arrOnePost) {

                    $objComment = new class_module_postacomment_post($arrOnePost["system_id"]);

                    //check, if the post is available on a page using the current language
                    if(!isset($arrOnePost["page_name"]) || $arrOnePost["page_name"] == "" || !$objComment->rightView())
                        continue;

                    if(isset($this->arrHits[$objComment->getSystemid().$arrOnePost["page_id"]]["hits"])) {
                        $this->arrHits[$objComment->getSystemid().$arrOnePost["page_id"]]["hits"]++;
                    }
                    else {

                        //search pv position
                        $intPvPos = 1;
                        $intAmount = $arrOnePost["int1"];
                        if($intAmount != "" && $intAmount > 0) {
                            $arrPostsForPage = class_module_postacomment_post::loadPostList(true, $objComment->getStrAssignedPage(), $objComment->getStrAssignedSystemid(), $objComment->getStrAssignedLanguage());
                            $intCounter = 0;
                            foreach($arrPostsForPage as $objOnePostForPage) {
                                $intCounter++;
                                if($objOnePostForPage->getSystemid() == $objComment->getSystemid())
                                   break;
                            }
                        //calculate pv
                        $intPvPos = ceil($intCounter/$intAmount);

                        }
                        $this->arrHits[$objComment->getSystemid().$arrOnePost["page_id"]]["hits"] = 1;
                        $this->arrHits[$objComment->getSystemid().$arrOnePost["page_id"]]["pagelink"] = getLinkPortal($arrOnePost["page_name"], "", "_self", $arrOnePost["page_name"], "", "&highlight=".urlencode(html_entity_decode($this->strSearchterm, ENT_QUOTES, "UTF-8"))."&pvPAC=".$intPvPos);
                        $this->arrHits[$objComment->getSystemid().$arrOnePost["page_id"]]["pagename"] = $arrOnePost["page_name"];
                        $this->arrHits[$objComment->getSystemid().$arrOnePost["page_id"]]["description"] = uniStrTrim($objComment->getStrComment(), 100);
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

