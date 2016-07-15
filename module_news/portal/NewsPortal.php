<?php
/*"******************************************************************************************************
*   (c) 2004-2006 by MulchProductions, www.mulchprod.de                                                 *
*   (c) 2007-2016 by Kajona, www.kajona.de                                                              *
*       Published under the GNU LGPL v2.1, see /system/licence_lgpl.txt                                 *
*-------------------------------------------------------------------------------------------------------*
*	$Id$									*
********************************************************************************************************/

namespace Kajona\News\Portal;

use Kajona\News\System\NewsCategory;
use Kajona\News\System\NewsNews;
use Kajona\Pages\Portal\PagesPortalController;
use Kajona\Pages\Portal\PagesPortaleditor;
use Kajona\Pages\System\PagesPortaleditorActionEnum;
use Kajona\Pages\System\PagesPortaleditorSystemidAction;
use Kajona\Postacomment\Portal\PostacommentPortal;
use Kajona\Postacomment\System\PostacommentPost;
use Kajona\Rating\Portal\RatingPortal;
use Kajona\System\Portal\PortalController;
use Kajona\System\Portal\PortalInterface;
use Kajona\System\System\ArraySectionIterator;
use Kajona\System\System\Link;
use Kajona\System\System\Objectfactory;
use Kajona\System\System\SystemModule;
use Kajona\System\System\TemplateMapper;

/**
 * Portal-class of the news. Handles the printing of news lists / detail
 *
 * @package module_news
 * @author sidler@mulchprod.de
 *
 * @module news
 * @moduleId _news_module_id_
 */
class NewsPortal extends PortalController implements PortalInterface
{

    /**
     * Constructor
     *
     * @param mixed $arrElementData
     */
    public function __construct($arrElementData = array(), $strSystemid = "")
    {
        parent::__construct($arrElementData, $strSystemid);

        $strAction = $this->getParam("action");

        if ($this->arrElementData["news_view"] != "0" &&
            ($strAction == "newsDetail" || (validateSystemid($this->getSystemid()) && Objectfactory::getInstance()->getObject($this->getSystemid()) instanceof NewsNews))) {
            $this->setAction("newsDetail");
        }
        else {
            $this->setAction("newsList");
        }
    }

    /**
     * Default implementation to avoid mail-spamming.
     *
     * @return void
     */
    protected function actionList()
    {

    }

    /**
     * Returns a list of news.
     * As defined in the element, this could be an archive or a normal list
     *
     * @return string
     */
    protected function actionNewsList()
    {
        $strReturn = "";
        //Load news using the correct filter
        if ($this->getParam("filterid") != "") {
            $strFilterId = $this->getParam("filterid");
        }
        else {
            $strFilterId = $this->arrElementData["news_category"];
        }

        $strPageview = 1;
        if ($this->getParam("pv") != 1 && $this->getSystemid() == $this->arrElementData["content_id"]) {
            $strPageview = $this->getParam("pv");
        }


        //Load all posts
        $objArraySectionIterator = new ArraySectionIterator(NewsNews::getNewsCountPortal($this->arrElementData["news_mode"], $strFilterId));
        $objArraySectionIterator->setIntElementsPerPage($this->arrElementData["news_amount"]);
        $objArraySectionIterator->setPageNumber((int)$strPageview);
        $objArraySectionIterator->setArraySection(
            NewsNews::loadListNewsPortal($this->arrElementData["news_mode"], $strFilterId, $this->arrElementData["news_order"], $objArraySectionIterator->calculateStartPos(), $objArraySectionIterator->calculateEndPos())
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

        if (!$objArraySectionIterator->valid()) {
            $strReturn .= $this->getLang("news_list_empty");
        }

        foreach ($objArraySectionIterator as $objOneNews) {
            /** @var $objOneNews NewsNews */
            if ($objOneNews instanceof NewsNews && $objOneNews->rightView()) {
                $objMapper = new TemplateMapper($objOneNews);

                //generate a link to the details
                $strDetailspage = $this->arrElementData["news_detailspage"] != "" ? $this->arrElementData["news_detailspage"] : $this->getPagename();
                $objMapper->addPlaceholder(
                    "news_more_link", Link::getLinkPortal($strDetailspage, "", "", $this->getLang("news_mehr"), "", "", $objOneNews->getSystemid(), "", "", $objOneNews->getStrTitle())
                );
                $objMapper->addPlaceholder("news_more_link_href", Link::getLinkPortalHref($strDetailspage, "", "", "", $objOneNews->getSystemid(), "", $objOneNews->getStrTitle()));
                $objMapper->addPlaceholder("news_start_date", dateToString($objOneNews->getObjStartDate(), false));
                $objMapper->addPlaceholder("news_id", $objOneNews->getSystemid());
                $objMapper->addPlaceholder("news_title", $objOneNews->getStrTitle());
                $objMapper->addPlaceholder("news_intro", $objOneNews->getStrIntro());
                $objMapper->addPlaceholder("news_text", $objOneNews->getStrText());

                //reset more link? -> no text, no image and no redirect page
                if (uniStrlen(htmlStripTags($objOneNews->getStrText())) == 0 && uniStrlen($objOneNews->getStrImage()) == 0 && ($objOneNews->getIntRedirectEnabled() == "0" || $objOneNews->getStrRedirectPage() == "")) {
                    $objMapper->addPlaceholder("news_more_link", "");
                }

                //postacomment
                $arrPAC = $this->loadPostacomments($objOneNews->getSystemid(), ($objOneNews->getStrImage() != "" ? "news_list_image" : "news_list"));
                if ($arrPAC != null) {
                    $objMapper->addPlaceholder("news_nrofcomments", $arrPAC["nrOfComments"]);
                    $objMapper->addPlaceholder("news_commentlist", $arrPAC["commentList"]);
                }

                //ratings
                if ($objOneNews->getFloatRating() !== null) {
                    /** @var $objRating RatingPortal */
                    $objRating = SystemModule::getModuleByName("rating")->getPortalInstanceOfConcreteModule();
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
                if ($objOneNews->getStrImage() != "") {
                    $objMapper->addPlaceholder("news_image", urlencode($objOneNews->getStrImage()));
                    $strOneNews = $objMapper->writeToTemplate("/module_news/".$this->arrElementData["news_template"], "news_list_image");
                }
                else {
                    $strOneNews = $objMapper->writeToTemplate("/module_news/".$this->arrElementData["news_template"], "news_list");
                }

                //Add pe code
                $strReturn .= PagesPortaleditor::addPortaleditorContentWrapper($strOneNews, $objOneNews->getSystemid());

                PagesPortaleditor::getInstance()->registerAction(
                    new PagesPortaleditorSystemidAction(PagesPortaleditorActionEnum::EDIT(), Link::getLinkAdminHref($this->getArrModule("module"), "editNews", "&pe=1&systemid={$objOneNews->getSystemid()}"), $objOneNews->getSystemid())
                );
                PagesPortaleditor::getInstance()->registerAction(
                    new PagesPortaleditorSystemidAction(PagesPortaleditorActionEnum::DELETE(), Link::getLinkAdminHref($this->getArrModule("module"), "delete", "&systemid={$objOneNews->getSystemid()}"), $objOneNews->getSystemid())
                );
                PagesPortaleditor::getInstance()->registerAction(
                    new PagesPortaleditorSystemidAction(PagesPortaleditorActionEnum::CREATE(), Link::getLinkAdminHref($this->getArrModule("module"), "newNews", "&pe=1"), $objOneNews->getSystemid())
                );
            }
        }
        $arrWrapperTemplate = array();
        $arrWrapperTemplate["news"] = $strReturn;
        $arrWrapperTemplate["link_forward"] = $arrNews["strForward"];
        $arrWrapperTemplate["link_pages"] = $arrNews["strPages"];
        $arrWrapperTemplate["link_back"] = $arrNews["strBack"];
        $strReturn = $this->objTemplate->fillTemplateFile($arrWrapperTemplate, "/module_news/".$this->arrElementData["news_template"], "news_list_wrapper");

        return $strReturn;
    }

    /**
     * Creates the detailed-view of news
     *
     * @return string
     */
    protected function actionNewsDetail()
    {
        $strReturn = "";
        /** @var $objNews NewsNews */
        $objNews = Objectfactory::getInstance()->getObject($this->getSystemid());
        if ($objNews != null && $objNews instanceof NewsNews && $objNews->rightView() && $objNews->getIntRecordStatus() == "1") {

            //see if we should generate a redirect instead
            if ($objNews->getIntRedirectEnabled() == "1" && $objNews->getStrRedirectPage() != "") {
                $this->portalReload(Link::getLinkPortalHref($objNews->getStrRedirectPage()));
                return "<script type='text/javascript'>window.location.replace('".Link::getLinkPortalHref($objNews->getStrRedirectPage())."');</script>";
            }

            //Load record
            $objMapper = new TemplateMapper($objNews);

            $objMapper->addPlaceholder("news_back_link", "<a href=\"javascript:history.back();\">".$this->getLang("news_zurueck")."</a>");
            $objMapper->addPlaceholder("news_start_date", dateToString($objNews->getObjStartDate(), false));
            $objMapper->addPlaceholder("news_id", $objNews->getSystemid());
            $objMapper->addPlaceholder("news_title", $objNews->getStrTitle());
            $objMapper->addPlaceholder("news_intro", $objNews->getStrIntro());
            $objMapper->addPlaceholder("news_text", $objNews->getStrText());

            //postacomment
            $arrPAC = $this->loadPostacomments($objNews->getSystemid(), ($objNews->getStrImage() != "" ? "news_detail_image" : "news_detail"));
            if ($arrPAC != null) {
                $objMapper->addPlaceholder("news_nrofcomments", $arrPAC["nrOfComments"]);
                $objMapper->addPlaceholder("news_commentlist", $arrPAC["commentList"]);
            }

            //ratings
            if ($objNews->getFloatRating() !== null) {
                /** @var $objRating RatingPortal */
                $objRating = SystemModule::getModuleByName("rating")->getPortalInstanceOfConcreteModule();
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
            if ($objNews->getStrImage() != "") {
                $objMapper->addPlaceholder("news_image", urlencode($objNews->getStrImage()));
                $strReturn .= $objMapper->writeToTemplate("/module_news/".$this->arrElementData["news_template"], "news_detail_image");
            }
            else {
                $strReturn .= $objMapper->writeToTemplate("/module_news/".$this->arrElementData["news_template"], "news_detail");
            }

            //Add pe code
            $strReturn = PagesPortaleditor::addPortaleditorContentWrapper($strReturn, $objNews->getSystemid());
            PagesPortaleditor::getInstance()->registerAction(
                new PagesPortaleditorSystemidAction(PagesPortaleditorActionEnum::EDIT(), Link::getLinkAdminHref($this->getArrModule("module"), "editNews", "&systemid={$this->getSystemid()}"), $this->getSystemid())
            );

            //and count the hit
            $objNews->increaseHits();

            //set the name of the current news to the page-title via class_pages
            PagesPortalController::registerAdditionalTitle($objNews->getStrTitle());
        }
        else {
            $strReturn = $this->getLang("commons_error_permissions");
        }
        return $strReturn;
    }

    /**
     * Renders the news category titles
     *
     * @param NewsNews $objNews
     *
     * @return string
     */
    private function renderCategoryTitles(NewsNews $objNews)
    {
        if (count(NewsCategory::getNewsMember($objNews->getSystemid())) == 0) {
            return "";
        }

        $strCategories = "";
        foreach (NewsCategory::getNewsMember($objNews->getSystemid()) as $objCat) {
            $objMapper = new TemplateMapper($objCat);
            $strCategories .= $objMapper->writeToTemplate("/module_news/".$this->arrElementData["news_template"], "categories_category");
        }

        return $this->objTemplate->fillTemplateFile(array("categories" => $strCategories), "/module_news/".$this->arrElementData["news_template"], "categories_wrapper");
    }

    /**
     * Loads and renders the list of comments provided by the current news-entry
     *
     * @param string $strNewsSystemid
     * @param string $strTemplateSection
     *
     * @return array
     */
    private function loadPostacomments($strNewsSystemid, $strTemplateSection)
    {
        if ($this->isPostacommentOnTemplate($this->arrElementData["news_template"], $strTemplateSection)) {

            $objPacModule = SystemModule::getModuleByName("postacomment");

            $arrReturn = array();
            if ($objPacModule != null) {
                $arrComments = PostacommentPost::loadPostList(false, "", $strNewsSystemid, $this->getStrPortalLanguage());

                //the rendered list
                $objPacPortal = new PostacommentPortal(array("char1" => "postacomment_ajax.tpl"));
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
    private function isPostacommentOnTemplate($strTemplate, $strSection)
    {
        $strTemplateID = $this->objTemplate->readTemplate("/module_news/".$strTemplate, $strSection);
        return $this->objTemplate->containsPlaceholder($strTemplateID, "news_commentlist") || $this->objTemplate->containsPlaceholder($strTemplateID, "news_nrofcomments");
    }
}
