<?php
/*"******************************************************************************************************
*   (c) 2004-2006 by MulchProductions, www.mulchprod.de                                                 *
*   (c) 2007-2012 by Kajona, www.kajona.de                                                              *
*       Published under the GNU LGPL v2.1, see /system/licence_lgpl.txt                                 *
*-------------------------------------------------------------------------------------------------------*
*	$Id$                    *
********************************************************************************************************/

/**
 * Search plugin of the postacomment-module.
 *
 * @package module_postacomment
 * @author sidler@mulchprod.de
 */
class class_module_postacomment_search_portal implements interface_search_plugin  {

    private $strSearchterm;

    /**
     * @var class_search_result
     */
    private $arrHits = array();

    private $objDB;

    public function  __construct($strSearchterm) {

        $this->strSearchterm = $strSearchterm;

        $this->objDB = class_carrier::getInstance()->getObjDB();
    }


    public function doSearch() {
        if(class_module_system_module::getModuleByName("postacomment") != null)
            $this->searchPostacomment();

        return array_values($this->arrHits);
    }


    /**
     * Searches the guestbook-posts
     *
     */
    private function searchPostacomment() {

        $objLanguages = new class_module_languages_language();

        $arrWhere = array(
            "postacomment_title LIKE ?",
            "postacomment_comment LIKE ?"
        );
        $arrParams = array(
            "%".$this->strSearchterm."%",
            "%".$this->strSearchterm."%"
        );

        $strWhere = "( ".implode(" OR ", $arrWhere). " ) ";
        $arrParams[] = $objLanguages->getStrPortalLanguage();

        //Query bauen
        $strQuery ="SELECT pacsys.system_id, page_name, page_id, ".$this->objDB->encloseColumnName("int1")."
                      FROM "._dbprefix_."postacomment,
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

                if(isset($this->arrHits[$objComment->getSystemid().$arrOnePost["page_id"]])) {
                    $objResult = $this->arrHits[$objComment->getSystemid().$arrOnePost["page_id"]];
                    $objResult->setIntHits($objResult->getIntHits()+1);
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

                    $objResult = new class_search_result();
                    $objResult->setStrResultId($objComment->getSystemid().$arrOnePost["page_id"]);
                    $objResult->setStrSystemid($objComment->getSystemid());
                    $objResult->setStrPagelink(getLinkPortal($arrOnePost["page_name"], "", "_self", $arrOnePost["page_name"], "", "&highlight=".urlencode(html_entity_decode($this->strSearchterm, ENT_QUOTES, "UTF-8"))."&pvPAC=".$intPvPos));
                    $objResult->setStrPagename($arrOnePost["page_name"]);
                    $objResult->setStrDescription($objComment->getStrComment());

                    $this->arrHits[$objComment->getSystemid().$arrOnePost["page_id"]] = $objResult;
                }
            }
        }
    }


}

