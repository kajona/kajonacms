<?php
/*"******************************************************************************************************
*   (c) 2004-2006 by MulchProductions, www.mulchprod.de                                                 *
*   (c) 2007-2012 by Kajona, www.kajona.de                                                              *
*       Published under the GNU LGPL v2.1, see /system/licence_lgpl.txt                                 *
*-------------------------------------------------------------------------------------------------------*
*	$Id$                    *
********************************************************************************************************/

/**
 * Search plugin of the faqs-module.
 *
 * @package module_faqs
 * @author sidler@mulchprod.de
 */
class class_module_faqs_search_portal implements interface_search_plugin  {

    private $strSearchterm;

    /**
     * @var class_search_result[]
     */
    private $arrHits = array();

    private $objDB;

    public function  __construct($strSearchterm) {

        $this->strSearchterm = $strSearchterm;
        $this->objDB = class_carrier::getInstance()->getObjDB();
    }


    public function doSearch() {
        if(class_module_system_module::getModuleByName("faqs") != null)
            $this->searchfaqs();

        return array_values($this->arrHits);
    }


    /**
     * Searches the faqs-posts
     *
     */
	private function searchfaqs() {

        $arrWhere = array(
            "faqs_question LIKE ?",
            "faqs_answer LIKE ?"
        );
        $arrParams = array(
            "%".$this->strSearchterm."%",
            "%".$this->strSearchterm."%"
        );

        $strWhere = "( ".implode(" OR ", $arrWhere). " ) ";

        //Query bauen
        $strQuery ="SELECT system_id
                      FROM "._dbprefix_."faqs,
                           "._dbprefix_."system
                     WHERE ".$strWhere."
                       AND system_id  = faqs_id
                       AND system_status = 1";

        $arrPosts = $this->objDB->getPArray($strQuery, $arrParams);

        //Register found posts
        if(count($arrPosts) > 0) {

            foreach($arrPosts as $arrOnePost) {

                $objPost = new class_module_faqs_faq($arrOnePost["system_id"]);
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

                        $objResult = new class_search_result();
                        $objResult->setStrResultId($objPost->getSystemid().$arrOnePage["page_id"]);
                        $objResult->setStrSystemid($objPost->getSystemid());
                        $objResult->setStrPagelink(getLinkPortal($arrOnePage["page_name"], "", "_self", $arrOnePage["page_name"], "", "&highlight=".urlencode(html_entity_decode($this->strSearchterm, ENT_QUOTES, "UTF-8"))));
                        $objResult->setStrPagename($arrOnePage["page_name"]);
                        $objResult->setStrDescription($objPost->getStrQuestion());

                        $this->arrHits[$objPost->getSystemid().$arrOnePage["page_id"]] = $objResult;
                    }
                }
            }
        }
	}

    private function getElementData(class_module_faqs_faq $objFaq) {
        $strQuery =  "SELECT page_name,  page_id
                       FROM "._dbprefix_."element_faqs,
                            "._dbprefix_."faqs_member,
                            "._dbprefix_."faqs,
                            "._dbprefix_."page_element,
                            "._dbprefix_."page,
                            "._dbprefix_."system
                      WHERE faqs_id = ?
                        AND content_id = page_element_id
                        AND content_id = system_id
                        AND ( faqs_category = 0 OR (
                                faqs_category = faqsmem_category
                                AND faqsmem_faq = faqs_id
                           )
                        )
                        AND system_prev_id = page_id
                        AND system_status = 1
                        AND page_element_ph_language = ? " ;

        $objLanguages = new class_module_languages_language();

        $arrRows = $this->objDB->getPArray($strQuery, array($objFaq->getSystemid(), $objLanguages->getStrPortalLanguage()));

        return $arrRows;
    }

}

