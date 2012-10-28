<?php
/*"******************************************************************************************************
*   (c) 2004-2006 by MulchProductions, www.mulchprod.de                                                 *
*   (c) 2007-2012 by Kajona, www.kajona.de                                                              *
*       Published under the GNU LGPL v2.1, see /system/licence_lgpl.txt                                 *
*-------------------------------------------------------------------------------------------------------*
*	$Id: class_modul_news_search.php 4026 2011-07-23 18:45:25Z sidler $                                  *
********************************************************************************************************/

/**
 * Search plugin of the news-module.
 *
 * @package module_news
 */
class class_module_news_search_portal implements interface_search_plugin  {

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
        if(class_module_system_module::getModuleByName("news") != null)
            $this->searchNews();

        return array_values($this->arrHits);
    }


    /**
	 * searches the news for the given string
	 *
	 */
	private function searchNews() {

        $arrWhere = array(
            "news_image LIKE ?",
            "news_intro LIKE ?",
            "news_text LIKE ?",
            "news_title LIKE ?"
        );
        $arrParams = array(
            "%".$this->strSearchterm."%",
            "%".$this->strSearchterm."%",
            "%".$this->strSearchterm."%",
            "%".$this->strSearchterm."%"
        );

        $strWhere = "( ".implode(" OR ", $arrWhere). " ) ";

        //Query bauen
        $strQuery ="SELECT system_id
                      FROM "._dbprefix_."news,
                           "._dbprefix_."system
                     WHERE ".$strWhere."
                       AND system_id  = news_id
                       AND system_status = 1";

        $arrNews = $this->objDB->getPArray($strQuery, $arrParams);


        if(count($arrNews) > 0) {

            foreach($arrNews as $arrOneNews) {

                $objNews = new class_module_news_news($arrOneNews["system_id"]);
                $arrDetails = $this->getElementData($objNews);

                foreach($arrDetails as $arrOnePage) {

                    //check, if the post is available on a page using the current language
                    if(!isset($arrOnePage["news_detailspage"]) || $arrOnePage["news_detailspage"] == "" || !$objNews->rightView())
                        continue;

                    $objDetails = class_module_pages_page::getPageByName($arrOnePage["news_detailspage"]);

                    if(isset($this->arrHits[$objNews->getSystemid().$objDetails->getSystemid()])) {
                        $objResult = $this->arrHits[$objNews->getSystemid().$objDetails->getSystemid()];
                        $objResult->setIntHits($objResult->getIntHits()+1);
                    }
                    else {

                        //TODO: PV position

                        $objResult = new class_search_result();
                        $objResult->setStrResultId($objNews->getSystemid().$objDetails->getSystemid());
                        $objResult->setStrSystemid($objNews->getSystemid());
                        $objResult->setStrPagelink(getLinkPortal($arrOnePage["news_detailspage"], "", "_self", $arrOnePage["news_detailspage"], "newsDetail", "&systemid=".$objNews->getSystemid()."&highlight=".urlencode(html_entity_decode($this->strSearchterm, ENT_QUOTES, "UTF-8"))));
                        $objResult->setStrPagename($arrOnePage["news_detailspage"]);
                        $objResult->setStrDescription($objNews->getStrTitle());

                        $this->arrHits[$objNews->getSystemid().$objDetails->getSystemid()] = $objResult;
                    }
                }
            }
        }
	}



    private function getElementData(class_module_news_news $objNews) {
        //search a news-details page

        $strQuery =  "SELECT news_detailspage
                       FROM "._dbprefix_."element_news,
                            "._dbprefix_."news_member,
                            "._dbprefix_."news,
                            "._dbprefix_."page_element,
                            "._dbprefix_."page,
                            "._dbprefix_."system
                      WHERE news_id = ?
                        AND content_id = page_element_id
                        AND content_id = system_id
                        AND ( news_category = 0 OR (
                                news_category = newsmem_category
                                AND newsmem_news = news_id
                           )
                        )
                        AND system_prev_id = page_id
                        AND system_status = 1
                        AND news_view = 0
                        AND page_element_ph_language = ? " ;

        $objLanguages = new class_module_languages_language();

        $arrRows = $this->objDB->getPArray($strQuery, array($objNews->getSystemid(), $objLanguages->getStrPortalLanguage()));

        return $arrRows;
    }


}
