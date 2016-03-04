<?php
/*"******************************************************************************************************
*   (c) 2004-2006 by MulchProductions, www.mulchprod.de                                                 *
*   (c) 2007-2015 by Kajona, www.kajona.de                                                              *
*       Published under the GNU LGPL v2.1, see /system/licence_lgpl.txt                                 *
*-------------------------------------------------------------------------------------------------------*
*	$Id$							*
********************************************************************************************************/

namespace Kajona\Postacomment\Portal;

use Kajona\Pages\Portal\PagesPortaleditor;
use Kajona\Pages\System\PagesPage;
use Kajona\Pages\System\PagesPortaleditorActionEnum;
use Kajona\Pages\System\PagesPortaleditorSystemidAction;
use Kajona\Postacomment\System\Messageproviders\MessageproviderPostacomment;
use Kajona\Postacomment\System\PostacommentPost;
use Kajona\Rating\Portal\RatingPortal;
use Kajona\System\Portal\PortalController;
use Kajona\System\Portal\PortalInterface;
use Kajona\System\System\ArraySectionIterator;
use Kajona\System\System\Link;
use Kajona\System\System\MessagingMessage;
use Kajona\System\System\MessagingMessagehandler;
use Kajona\System\System\Rights;
use Kajona\System\System\SystemModule;
use Kajona\System\System\UserGroup;

/**
 * Portal-class of the postacomment. Handles the printing of postacomment lists / detail
 *
 * @package module_postacomment
 * @author sidler@mulchprod.de
 *
 * @module postacomment
 * @moduleId _postacomment_modul_id_
 */
class PostacommentPortal extends PortalController implements PortalInterface
{

    private $strErrors = "";
    private $strPagefilter = null;

    /**
     * Returns a list of comments.
     *
     * @return string
     * @permissions view
     */
    protected function actionList()
    {
        $strReturn = "";
        $strPosts = "";
        $strForm = "";
        $strNewButton = "";

        //pageid or systemid to filter?
        $strSystemidfilter = "";
        $strPagefilter = $this->strPagefilter;

        if ($this->getSystemid() != "") {
            $strSystemidfilter = $this->getSystemid();
        }

        if ($strPagefilter === null && PagesPage::getPageByName($this->getPagename()) !== null) {
            $strPagefilter = PagesPage::getPageByName($this->getPagename())->getSystemid();
        }

        $intNrOfPosts = isset($this->arrElementData["int1"]) ? $this->arrElementData["int1"] : 0;

        //Load all posts
        $objArraySectionIterator = new ArraySectionIterator(PostacommentPost::getNumberOfPostsAvailable(true, $strPagefilter, $strSystemidfilter, $this->getStrPortalLanguage()));
        $objArraySectionIterator->setIntElementsPerPage($intNrOfPosts);
        $objArraySectionIterator->setPageNumber((int)($this->getParam("pvPAC") != "" ? $this->getParam("pvPAC") : 1));
        $objArraySectionIterator->setArraySection(
            PostacommentPost::loadPostList(true, $strPagefilter, $strSystemidfilter, $this->getStrPortalLanguage(), $objArraySectionIterator->calculateStartPos(), $objArraySectionIterator->calculateEndPos())
        );


        //params to add?
        $strAdd = "";
        if ($this->getParam("action") != "") {
            $strAdd .= "&action=".$this->getParam("action");
        }
        if ($this->getParam("systemid") != "") {
            $strAdd .= "&systemid=".$this->getParam("systemid");
        }
        if ($this->getParam("pv") != "") {
            $strAdd .= "&pv=".$this->getParam("pv");
        }

        $arrComments = $this->objToolkit->simplePager($objArraySectionIterator, $this->getLang("commons_next"), $this->getLang("commons_back"), "", $this->getPagename(), $strAdd, "pvPAC", "/module_postacomment/".$this->arrElementData["char1"]);
        $strTemplateID = $this->objTemplate->readTemplate("/module_postacomment/".$this->arrElementData["char1"], "postacomment_post");

        if (!$objArraySectionIterator->valid()) {
            $strPosts .= $this->getLang("postacomment_empty");
        }

        //Check rights
        /** @var PostacommentPost $objOnePost */
        foreach ($objArraySectionIterator as $objOnePost) {
            if ($objOnePost->rightView()) {
                $strOnePost = "";
                $arrOnePost = array();
                $arrOnePost["postacomment_post_name"] = $objOnePost->getStrUsername();
                $arrOnePost["postacomment_post_subject"] = $objOnePost->getStrTitle();
                $arrOnePost["postacomment_post_message"] = $objOnePost->getStrComment();
                $arrOnePost["postacomment_post_systemid"] = $objOnePost->getSystemid();
                $arrOnePost["postacomment_post_date"] = timeToString($objOnePost->getIntDate(), true);
                //ratings available?
                if ($objOnePost->getFloatRating() !== null) {
                    /** @var $objRating RatingPortal */
                    $objRating = SystemModule::getModuleByName("rating")->getPortalInstanceOfConcreteModule();
                    $arrOnePost["postacomment_post_rating"] = $objRating->buildRatingBar(
                        $objOnePost->getFloatRating(), $objOnePost->getIntRatingHits(), $objOnePost->getSystemid(), $objOnePost->isRateableByUser(), $objOnePost->rightRight2()
                    );
                }

                $strOnePost .= $this->objTemplate->fillTemplate($arrOnePost, $strTemplateID, false);

                //Add pe code
                $strPosts .= PagesPortaleditor::addPortaleditorContentWrapper($strOnePost, $objOnePost->getSystemid());
                PagesPortaleditor::getInstance()->registerAction(
                    new PagesPortaleditorSystemidAction(PagesPortaleditorActionEnum::EDIT(), Link::getLinkAdminHref($this->getArrModule("module"), "edit", "&systemid={$objOnePost->getSystemid()}"), $objOnePost->getSystemid())
                );
                PagesPortaleditor::getInstance()->registerAction(
                    new PagesPortaleditorSystemidAction(PagesPortaleditorActionEnum::DELETE(), Link::getLinkAdminHref($this->getArrModule("module"), "delete", "&systemid={$objOnePost->getSystemid()}"), $objOnePost->getSystemid())
                );
            }
        }

        //Create form
        if ($this->getObjModule()->rightRight1()) {
            $strTemplateID = $this->objTemplate->readTemplate("/module_postacomment/".$this->arrElementData["char1"], "postacomment_form");
            $arrForm = array();

            if ($this->getParam("comment_name") == "" && $this->objSession->isLoggedin()) {
                $this->setParam("comment_name", $this->objSession->getUsername());
            }

            $arrForm["formaction"] = Link::getLinkPortalHref($this->getPagename(), "", "postComment", "", $this->getSystemid());
            $arrForm["comment_name"] = $this->getParam("comment_name");
            $arrForm["comment_subject"] = $this->getParam("comment_subject");
            $arrForm["comment_message"] = $this->getParam("comment_message");
            $arrForm["comment_template"] = $this->arrElementData["char1"];
            $arrForm["comment_systemid"] = $this->getSystemid();
            $arrForm["comment_page"] = $this->getPagename();
            $arrForm["validation_errors"] = $this->strErrors;

            foreach ($arrForm as $strKey => $strValue) {
                if (uniStrpos($strKey, "comment_") !== false) {
                    $arrForm[$strKey] = htmlspecialchars($strValue, ENT_QUOTES, "UTF-8", false);
                }
            }

            $strForm .= $this->objTemplate->fillTemplate($arrForm, $strTemplateID, false);

            //button to show the form
            $strTemplateNewButtonID = $this->objTemplate->readTemplate("/module_postacomment/".$this->arrElementData["char1"], "postacomment_new_button");
            $strNewButton = $this->objTemplate->fillTemplate(array("comment_systemid" => $this->getSystemid()), $strTemplateNewButtonID, false);
        }
        //add sourrounding list template
        $strTemplateID = $this->objTemplate->readTemplate("/module_postacomment/".$this->arrElementData["char1"], "postacomment_list");


        //link to the post-form & pageview links
        $arrTemplate = array();
        $arrTemplate["postacomment_forward"] = $arrComments["strForward"];
        $arrTemplate["postacomment_pages"] = $arrComments["strPages"];
        $arrTemplate["postacomment_back"] = $arrComments["strBack"];
        $arrTemplate["postacomment_form"] = $strForm;
        $arrTemplate["postacomment_new_button"] = $strNewButton;
        $arrTemplate["postacomment_systemid"] = $this->getSystemid();
        $arrTemplate["postacomment_list"] = $strPosts;

        $strReturn .= $this->fillTemplate($arrTemplate, $strTemplateID);

        return $strReturn;
    }



    /**
     * If you want to set a sepcial page to be used for loading and rendering the portal list, use this
     * setter. Pass the systemid (!) of the page to load.
     *
     * @param string $strPagefilter
     *
     * @return void
     */
    public function setStrPagefilter($strPagefilter)
    {
        $this->strPagefilter = $strPagefilter;
    }

}
