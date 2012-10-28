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
 * @author sidler@mulchprod.de
 */
class class_module_guestbook_search_portal implements interface_search_plugin  {

    private $strSearchterm;

    /**
     * @var class_search_result[]
     */
    private $arrHits = array();

    private $objDB;

    public function  __construct(class_module_search_search $objSearch) {

        $this->strSearchterm = $objSearch->getStrQuery();
        $this->objDB = class_carrier::getInstance()->getObjDB();
    }


    public function doSearch() {
        if(class_module_system_module::getModuleByName("guestbook") != null)
            $this->searchGuestbook();

        return array_values($this->arrHits);
    }


    /**
     * Searches the guestbook-posts
     *
     */
	private function searchGuestbook() {

        $arrWhere = array(
            "guestbook_post_name LIKE ?",
            "guestbook_post_text LIKE ?"
        );
        $arrParams = array(
            "%".$this->strSearchterm."%",
            "%".$this->strSearchterm."%"
        );

        $strWhere = "( ".implode(" OR ", $arrWhere). " ) ";

        //Query bauen
        $strQuery ="SELECT system_id
                      FROM "._dbprefix_."guestbook_post,
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

                    if(isset($this->arrHits[$objPost->getSystemid().$arrOnePage["page_id"]])) {
                        $objResult = $this->arrHits[$objPost->getSystemid().$arrOnePage["page_id"]];
                        $objResult->setIntHits($objResult->getIntHits()+1);
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

                        $objResult = new class_search_result();
                        $objResult->setStrResultId($objPost->getSystemid().$arrOnePage["page_id"]);
                        $objResult->setStrSystemid($objPost->getSystemid());
                        $objResult->setStrPagelink(getLinkPortal($arrOnePage["page_name"], "", "_self", $arrOnePage["page_name"], "", "&highlight=".urlencode(html_entity_decode($this->strSearchterm, ENT_QUOTES, "UTF-8"))."&pv=".$intPvPos));
                        $objResult->setStrPagename($arrOnePage["page_name"]);
                        $objResult->setStrDescription($objPost->getStrGuestbookPostText());

                        $this->arrHits[$objPost->getSystemid().$arrOnePage["page_id"]] = $objResult;
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

