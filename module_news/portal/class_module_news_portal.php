<?php
/*"******************************************************************************************************
*   (c) 2004-2006 by MulchProductions, www.mulchprod.de                                                 *
*   (c) 2007-2015 by Kajona, www.kajona.de                                                              *
*       Published under the GNU LGPL v2.1, see /system/licence_lgpl.txt                                 *
*-------------------------------------------------------------------------------------------------------*
*	$Id$									*
********************************************************************************************************/

/**
 * Portal-class of the news. Handles thd printing of news lists / detail
 *
 * @package module_news
 * @author sidler@mulchprod.de
 *
 * @module news
 * @moduleId _news_module_id_
 */
class class_module_news_portal extends class_portal_controller implements interface_portal {

    /**
     * Constructor
     *
     * @param mixed $arrElementData
     */
    public function __construct($arrElementData) {
        parent::__construct($arrElementData);

        $strAction = $this->getParam("action");
        if($strAction == "newsDetail" && $this->arrElementData["news_view"] == 1) {
            $this->setAction("newsDetail");
        }
        elseif(!isset($this->arrElementData["news_view"]) || $this->arrElementData["news_view"] == 0 || $strAction == "newsList") {
            $this->setAction("newsList");
        }
    }

    /**
     * Default implementation to avoid mail-spamming.
     *
     * @return void
     */
    protected function actionList() {

    }

    /**
     * Returns a list of news.
     * As defined in the element, this could be an archive or a normal list
     *
     * @return string
     */
    protected function actionNewsList() {
        $strReturn = "";
        //Load news using the correct filter
        if($this->getParam("filterid") != "") {
            $strFilterId = $this->getParam("filterid");
        }
        else {
            $strFilterId = $this->arrElementData["news_category"];
        }

        $strPageview = 1;
        if($this->getParam("pv") != 1 && $this->getSystemid() == $this->arrElementData["content_id"]) {
            $strPageview = $this->getParam("pv");
        }


        //Load all posts
        $objArraySectionIterator = new class_array_section_iterator(class_module_news_news::getNewsCountPortal($this->arrElementData["news_mode"], $strFilterId));
        $objArraySectionIterator->setIntElementsPerPage($this->arrElementData["news_amount"]);
        $objArraySectionIterator->setPageNumber((int)$strPageview);
        $objArraySectionIterator->setArraySection(
            class_module_news_news::loadListNewsPortal($this->arrElementData["news_mode"], $strFilterId, $this->arrElementData["news_order"], $objArraySectionIterator->calculateStartPos(), $objArraySectionIterator->calculateEndPos())
        );

        $arrNews = $this->objToolkit->simplePager(
            $objArraySectionIterator,
            $this->getLang("commons_next"),
            $this->getLang("commons_back"),
            "",
            $this->getPagename(),
            "&systemid=".$this->arrElementData["content_id"],
            "pv",
            "/module_news/".$this->arrElementData["news_template"]
        );


        //Check rights

        if(!$objArraySectionIterator->valid())
            $strReturn .= $this->getLang("news_list_empty");

        foreach($objArraySectionIterator as $objOneNews) {
            /** @var $objOneNews class_module_news_news */
            if($objOneNews instanceof class_module_news_news && $objOneNews->rightView()) {
                $objMapper = new class_template_mapper($objOneNews);

                //generate a link to the details
                $objMapper->addPlaceholder(
                    "news_more_link", class_link::getLinkPortal($this->arrElementData["news_detailspage"], "", "", $this->getLang("news_mehr"), "newsDetail", "", $objOneNews->getSystemid(), "", "", $objOneNews->getStrTitle())
                );
                $objMapper->addPlaceholder("news_more_link_href", class_link::getLinkPortalHref($this->arrElementData["news_detailspage"], "", "newsDetail", "", $objOneNews->getSystemid(), "", $objOneNews->getStrTitle()));
                $objMapper->addPlaceholder("news_start_date", dateToString($objOneNews->getObjStartDate(), false));
                $objMapper->addPlaceholder("news_id", $objOneNews->getSystemid());
                $objMapper->addPlaceholder("news_title", $objOneNews->getStrTitle());
                $objMapper->addPlaceholder("news_intro", $objOneNews->getStrIntro());
                $objMapper->addPlaceholder("news_text", $objOneNews->getStrText());

                //reset more link? -> no text, no image and no redirect page
                if(uniStrlen(htmlStripTags($objOneNews->getStrText())) == 0 && uniStrlen($objOneNews->getStrImage()) == 0 && ($objOneNews->getIntRedirectEnabled() == "0" || $objOneNews->getStrRedirectPage() == "")) {
                    $objMapper->addPlaceholder("news_more_link", "");
                }

                //postacomment
                $arrPAC = $this->loadPostacomments($objOneNews->getSystemid(), ($objOneNews->getStrImage() != "" ? "news_list_image" : "news_list"));
                if($arrPAC != null) {
                    $objMapper->addPlaceholder("news_nrofcomments", $arrPAC["nrOfComments"]);
                    $objMapper->addPlaceholder("news_commentlist", $arrPAC["commentList"]);
                }

                //ratings
                if($objOneNews->getFloatRating() !== null) {
                    /** @var $objRating class_module_rating_portal */
                    $objRating = class_module_system_module::getModuleByName("rating")->getPortalInstanceOfConcreteModule();
                    $objMapper->addPlaceholder(
                        "news_rating",
                        $objRating->buildRatingBar(
                            $objOneNews->getFloatRating(),
                            $objOneNews->getIntRatingHits(),
                            $objOneNews->getSystemid(),
                            $objOneNews->isRateableByUser(),
                            $objOneNews->rightRight3()
                        )
                    );
                }

                //categories
                $objMapper->addPlaceholder("news_categories", $this->renderCategoryTitles($objOneNews));

                //load template section with or without image?
                if($objOneNews->getStrImage() != "") {
                    $objMapper->addPlaceholder("news_image", urlencode($objOneNews->getStrImage()));
                    $strOneNews = $objMapper->writeToTemplate("/module_news/".$this->arrElementData["news_template"], "news_list_image");
                }
                else {
                    $strOneNews = $objMapper->writeToTemplate("/module_news/".$this->arrElementData["news_template"], "news_list");
                }

                //Add pe code
                $arrPeConfig = array(
                    "pe_module"               => "news",
                    "pe_action_edit"          => "editNews",
                    "pe_action_edit_params"   => "&systemid=" . $objOneNews->getSystemid(),
                    "pe_action_new"           => "newNews",
                    "pe_action_new_params"    => "",
                    "pe_action_delete"        => "delete",
                    "pe_action_delete_params" => "&systemid=" . $objOneNews->getSystemid()
                );
                $strReturn .= class_element_portal::addPortalEditorCode($strOneNews, $objOneNews->getSystemid(), $arrPeConfig);
            }
        }
        $arrWrapperTemplate = array();
        $arrWrapperTemplate["news"] = $strReturn;
        $arrWrapperTemplate["link_forward"] = $arrNews["strForward"];
        $arrWrapperTemplate["link_pages"] = $arrNews["strPages"];
        $arrWrapperTemplate["link_back"] = $arrNews["strBack"];
        $strReturn = $this->fillTemplate($arrWrapperTemplate, $this->objTemplate->readTemplate("/module_news/" . $this->arrElementData["news_template"], "news_list_wrapper"));

        return $strReturn;
    }

    /**
     * Creates the detailed-view of news
     *
     * @return string
     */
    protected function actionNewsDetail() {
        $strReturn = "";
        /** @var $objNews class_module_news_news */
        $objNews = class_objectfactory::getInstance()->getObject($this->getSystemid());
        if($objNews != null && $objNews instanceof class_module_news_news && $objNews->rightView() && $objNews->getIntRecordStatus() == "1") {

            //see if we should generate a redirect instead
            if($objNews->getIntRedirectEnabled() == "1" && $objNews->getStrRedirectPage() != "") {
                $this->portalReload(class_link::getLinkPortalHref($objNews->getStrRedirectPage()));
                return "<script type='text/javascript'>window.location.replace('".class_link::getLinkPortalHref($objNews->getStrRedirectPage())."');</script>";
            }

            //Load record
            $objMapper = new class_template_mapper($objNews);

            $objMapper->addPlaceholder("news_back_link", "<a href=\"javascript:history.back();\">" . $this->getLang("news_zurueck") . "</a>");
            $objMapper->addPlaceholder("news_start_date", dateToString($objNews->getObjStartDate(), false));
            $objMapper->addPlaceholder("news_id", $objNews->getSystemid());
            $objMapper->addPlaceholder("news_title", $objNews->getStrTitle());
            $objMapper->addPlaceholder("news_intro", $objNews->getStrIntro());
            $objMapper->addPlaceholder("news_text", $objNews->getStrText());

            //postacomment
            $arrPAC = $this->loadPostacomments($objNews->getSystemid(), ($objNews->getStrImage() != "" ? "news_detail_image" : "news_detail"));
            if($arrPAC != null) {
                $objMapper->addPlaceholder("news_nrofcomments", $arrPAC["nrOfComments"]);
                $objMapper->addPlaceholder("news_commentlist", $arrPAC["commentList"]);
            }

            //ratings
            if($objNews->getFloatRating() !== null) {
                /** @var $objRating class_module_rating_portal */
                $objRating = class_module_system_module::getModuleByName("rating")->getPortalInstanceOfConcreteModule();
                $objMapper->addPlaceholder(
                    "news_rating",
                    $objRating->buildRatingBar(
                        $objNews->getFloatRating(),
                        $objNews->getIntRatingHits(),
                        $objNews->getSystemid(),
                        $objNews->isRateableByUser(),
                        $objNews->rightRight3()
                    )
                );
            }

            //categories
            $objMapper->addPlaceholder("news_categories", $this->renderCategoryTitles($objNews));

            //load template section with or without image?
            if($objNews->getStrImage() != "") {
                $objMapper->addPlaceholder("news_image", urlencode($objNews->getStrImage()));
                $strReturn .= $objMapper->writeToTemplate("/module_news/".$this->arrElementData["news_template"], "news_detail_image");
            }
            else {
                $strReturn .= $objMapper->writeToTemplate("/module_news/".$this->arrElementData["news_template"], "news_detail");
            }

            //Add pe code
            $arrPeConfig = array(
                "pe_module"             => "news",
                "pe_action_edit"        => "editNews",
                "pe_action_edit_params" => "&systemid=" . $this->getSystemid()
            );
            $strReturn = class_element_portal::addPortalEditorCode($strReturn, $objNews->getSystemid(), $arrPeConfig);

            //and count the hit
            $objNews->increaseHits();

            //set the name of the current news to the page-title via class_pages
            class_module_pages_portal::registerAdditionalTitle($objNews->getStrTitle());
        }
        else {
            $strReturn = $this->getLang("commons_error_permissions");
        }
        return $strReturn;
    }

    /**
     * Renders the news category titles
     * @param class_module_news_news $objNews
     *
     * @return string
     */
    private function renderCategoryTitles(class_module_news_news $objNews) {
        if(count(class_module_news_category::getNewsMember($objNews->getSystemid())) == 0)
            return "";

        $strCategories = "";
        foreach(class_module_news_category::getNewsMember($objNews->getSystemid()) as $objCat) {
            $objMapper = new class_template_mapper($objCat);
            $strCategories .= $objMapper->writeToTemplate("/module_news/".$this->arrElementData["news_template"], "categories_category");
        }

        $strWrapper = $this->objTemplate->readTemplate("/module_news/".$this->arrElementData["news_template"], "categories_wrapper");
        return $this->objTemplate->fillTemplate(array("categories" => $strCategories), $strWrapper);
    }

    /**
     * Loads and renders the list of comments provided by the current news-entry
     *
     * @param string $strNewsSystemid
     * @param string $strTemplateSection
     *
     * @return array
     */
    private function loadPostacomments($strNewsSystemid, $strTemplateSection) {
        if($this->isPostacommentOnTemplate($this->arrElementData["news_template"], $strTemplateSection)) {

            $objPacModule = class_module_system_module::getModuleByName("postacomment");

            $arrReturn = array();
            if($objPacModule != null) {
                $arrComments = class_module_postacomment_post::loadPostList(false, "", $strNewsSystemid, $this->getStrPortalLanguage());

                //the rendered list
                $objPacPortal = new class_module_postacomment_portal(array("char1" => "postacomment_ajax.tpl"));
                $objPacPortal->setSystemid($strNewsSystemid);
                $objPacPortal->setStrPagefilter("");
                $strListCode = $objPacPortal->action();

                $arrReturn["nrOfComments"] = count($arrComments);
                $arrReturn["commentList"] = $strListCode;
            }
            else {
                return null;
            }

            return $arrReturn;

        }

        return null;
    }

    /**
     * Checks, if the current template provides placeholders needed to show comments.
     * Otherwise, the postacomment-module won't be even called.
     *
     * @param string $strTemplate
     * @param string $strSection
     *
     * @return bool
     */
    private function isPostacommentOnTemplate($strTemplate, $strSection) {
        $strTemplateID = $this->objTemplate->readTemplate("/module_news/" . $strTemplate, $strSection);
        return $this->objTemplate->containsPlaceholder($strTemplateID, "news_commentlist") || $this->objTemplate->containsPlaceholder($strTemplateID, "news_nrofcomments");
    }
}
