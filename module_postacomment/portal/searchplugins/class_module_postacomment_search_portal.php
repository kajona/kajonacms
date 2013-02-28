<?php
/*"******************************************************************************************************
*   (c) 2004-2006 by MulchProductions, www.mulchprod.de                                                 *
*   (c) 2007-2013 by Kajona, www.kajona.de                                                              *
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

    public function  __construct(class_module_search_search $objSearch) {

        $this->strSearchterm = $objSearch->getStrQuery();

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
        $strQuery ="SELECT pacsys.system_id, page_name, page_id
                      FROM "._dbprefix_."postacomment,
                           "._dbprefix_."system AS pacsys,
                           "._dbprefix_."system AS pagesys,
                           "._dbprefix_."page
                     WHERE ".$strWhere."
                       AND pacsys.system_id = postacomment_id
                       AND postacomment_page = page_id
                       AND pagesys.system_id = page_id
                       AND pagesys.system_status = 1
                       AND pacsys.system_status = 1
                       AND (postacomment_language = ? OR postacomment_language IS NULL OR postacomment_language = '') ";

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

                    $objResult = new class_search_result();
                    $objResult->setStrResultId($objComment->getSystemid().$arrOnePost["page_id"]);
                    $objResult->setStrSystemid($objComment->getSystemid());
                    $objResult->setStrPagelink(getLinkPortal($arrOnePost["page_name"], "", "_self", $arrOnePost["page_name"], "", "&highlight=".urlencode(html_entity_decode($this->strSearchterm, ENT_QUOTES, "UTF-8"))));
                    $objResult->setStrPagename($arrOnePost["page_name"]);
                    $objResult->setStrDescription($objComment->getStrComment());

                    $this->arrHits[$objComment->getSystemid().$arrOnePost["page_id"]] = $objResult;
                }
            }
        }
    }
}

